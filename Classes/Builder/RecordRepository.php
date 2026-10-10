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
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Loads the records structured data is built from, with the visibility rules of the current context.
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
        $row = $queryBuilder
            ->select('*')
            ->from('tx_schematic_entity')
            ->where(
                $queryBuilder->expr()->eq('parentid', $queryBuilder->createNamedParameter($pageContext->pageUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->in('sys_language_uid', [0, -1]),
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $this->overlay('tx_schematic_entity', $row, $pageContext);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findOrganization(PageContext $pageContext): ?array
    {
        $rootPage = $this->connectionPool->getConnectionForTable('pages')
            ->select(['tx_schematic_organization'], 'pages', ['uid' => $pageContext->site->getRootPageId()])
            ->fetchAssociative();
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
                ->select('question', 'answer')
                ->from('tx_schematic_faq_item')
                ->where($itemQuery->expr()->eq('parentid', $itemQuery->createNamedParameter($parent, Connection::PARAM_INT)))
                ->orderBy('sorting')
                ->executeQuery()
                ->fetchAllAssociative();
            foreach ($rows as $row) {
                $items[] = ['question' => (string)$row['question'], 'answer' => (string)$row['answer']];
            }
        }
        return $items;
    }

    private function queryBuilder(string $table, PageContext $pageContext): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(
            $pageContext->frontend
                ? GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $this->context)
                : GeneralUtility::makeInstance(DefaultRestrictionContainer::class)
        );
        return $queryBuilder;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function overlay(string $table, array $row, PageContext $pageContext): ?array
    {
        if ($pageContext->languageId === 0) {
            return $row;
        }
        // The frontend context already carries the site language with its fallback rules; the backend
        // preview has none, so it asks for the language of the edited page explicitly.
        $languageAspect = $pageContext->frontend
            ? null
            : new LanguageAspect($pageContext->languageId, $pageContext->languageId, LanguageAspect::OVERLAYS_MIXED);
        return GeneralUtility::makeInstance(PageRepository::class, $this->context)->getLanguageOverlay($table, $row, $languageAspect);
    }
}
