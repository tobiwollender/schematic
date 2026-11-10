<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use TobiWollender\Schematic\Model\PageContext;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DefaultRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Loads the records structured data is built from, with the visibility rules of the current context.
 *
 * In a workspace every row is replaced by its workspace version. Versions keep pointing to the live uid of
 * their parent, so all lookups use live uids.
 */
final readonly class RecordRepository
{
    public function __construct(
        private ConnectionPool $connectionPool,
        private Context $context,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function findPageEntity(PageContext $pageContext): ?array
    {
        $queryBuilder = $this->queryBuilder('tx_schematic_entity', $pageContext);
        $rows = $queryBuilder
            ->select('*')
            ->from('tx_schematic_entity')
            ->where(
                $queryBuilder->expr()->eq('parentid', $queryBuilder->createNamedParameter($pageContext->pageUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->in('sys_language_uid', [0, -1]),
            )
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        // A row deleted in the workspace may sit next to a new one, so the first visible row counts.
        foreach ($rows as $row) {
            $row = $this->overlay('tx_schematic_entity', $row, $pageContext);
            if ($row !== null) {
                return $row;
            }
        }
        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findOrganization(PageContext $pageContext): ?array
    {
        $rootPage = $this->connectionPool->getConnectionForTable('pages')
            ->select(['uid', 'pid', 't3ver_oid', 't3ver_state', 'tx_schematic_organization'], 'pages', ['uid' => $pageContext->site->getRootPageId()])
            ->fetchAssociative();
        // Only the selected organization is needed here, whether the root page itself is visible or not.
        $this->pageRepository()->versionOL('pages', $rootPage, false, true);
        $uid = (int)($rootPage['tx_schematic_organization'] ?? 0);
        if ($uid === 0) {
            return null;
        }

        $queryBuilder = $this->queryBuilder('tx_schematic_entity', $pageContext);
        $row = $queryBuilder
            ->select('*')
            ->from('tx_schematic_entity')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $this->overlay('tx_schematic_entity', $row, $pageContext);
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function findFaqItems(PageContext $pageContext): array
    {
        $queryBuilder = $this->queryBuilder('tt_content', $pageContext);
        $elements = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageContext->pageUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('schematic_faq')),
                $queryBuilder->expr()->in('sys_language_uid', [0, -1]),
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $items = [];
        foreach ($elements as $element) {
            $element = $this->overlay('tt_content', $element, $pageContext);
            if ($element === null) {
                continue;
            }
            $parent = (int)($element['_LOCALIZED_UID'] ?? $element['uid']);
            $itemQuery = $this->queryBuilder('tx_schematic_faq_item', $pageContext);
            $rows = $itemQuery
                ->select('*')
                ->from('tx_schematic_faq_item')
                ->where($itemQuery->expr()->eq('parentid', $itemQuery->createNamedParameter($parent, Connection::PARAM_INT)))
                ->orderBy('sorting')
                ->executeQuery()
                ->fetchAllAssociative();
            $rows = array_filter(array_map(fn(array $row): ?array => $this->versionOverlay('tx_schematic_faq_item', $row), $rows));
            // Workspace versions carry their own sorting.
            usort($rows, static fn(array $a, array $b): int => (int)$a['sorting'] <=> (int)$b['sorting']);
            foreach ($rows as $row) {
                $items[] = ['question' => (string)$row['question'], 'answer' => (string)$row['answer']];
            }
        }
        return $items;
    }

    private function queryBuilder(string $table, PageContext $pageContext): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $workspaceId = (int)$this->context->getPropertyFromAspect('workspace', 'id', 0);
        if ($workspaceId > 0) {
            // versionOL() checks hidden, start and end on the version, so the live row must not be filtered by them.
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspaceId));
            return $queryBuilder;
        }
        $queryBuilder->setRestrictions(
            $pageContext->frontend
                ? GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $this->context)
                // The default container knows no workspaces and would return versions as if they were live.
                : GeneralUtility::makeInstance(DefaultRestrictionContainer::class)->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, 0))
        );
        return $queryBuilder;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null null if the row is deleted or not visible in the workspace
     */
    private function versionOverlay(string $table, array $row): ?array
    {
        $this->pageRepository()->versionOL($table, $row);
        return \is_array($row) ? $row : null;
    }

    private function pageRepository(): PageRepository
    {
        return GeneralUtility::makeInstance(PageRepository::class, $this->context);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function overlay(string $table, array $row, PageContext $pageContext): ?array
    {
        $row = $this->versionOverlay($table, $row);
        if ($row === null || $pageContext->languageId === 0) {
            return $row;
        }
        // The frontend context already carries the site language with its fallback rules; the backend
        // preview has none, so it asks for the language of the edited page explicitly.
        $languageAspect = $pageContext->frontend
            ? null
            : new LanguageAspect($pageContext->languageId, $pageContext->languageId, LanguageAspect::OVERLAYS_MIXED);
        return $this->pageRepository()->getLanguageOverlay($table, $row, $languageAspect);
    }
}
