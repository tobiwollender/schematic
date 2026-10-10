<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use Brotkrueml\Schema\Core\Model\TypeInterface;
use TobiWollender\Schematic\Configuration\PageSchemaConfigProviderInterface;
use TobiWollender\Schematic\Model\PageContext;
use TobiWollender\Schematic\Model\PageSchemaConfig;
use TobiWollender\Schematic\Model\SchemaGraph;
use TobiWollender\Schematic\Model\SchemaTypes;

/**
 * Collects all structured data of one page: organization, the page's own type and FAQs.
 *
 * Shared by the frontend and the backend preview so both always show the same result.
 */
final readonly class SchemaGraphBuilder
{
    public function __construct(
        private PageSchemaConfigProviderInterface $configProvider,
        private RecordRepository $records,
        private ValueNormalizer $normalizer,
        private TypeAssembler $assembler,
    ) {}

    public function build(PageContext $context): SchemaGraph
    {
        $types = [];
        $config = $this->configProvider->forPage($context->page);

        $organization = $this->organization($context, $types);

        $mainType = $this->mainType($context, $config, $organization);
        if ($mainType !== null) {
            $types[] = $mainType;
        }

        $faqItems = $this->records->findFaqItems($context);
        if ($faqItems !== []) {
            $types[] = $this->assembler->faqPage($faqItems, $this->pageUrl($context));
        }

        return new SchemaGraph($types, $config);
    }

    /**
     * Adds the full organization and website on the home page and returns a short reference for other types.
     *
     * @param list<TypeInterface> $types
     */
    private function organization(PageContext $context, array &$types): ?TypeInterface
    {
        $row = $this->records->findOrganization($context);
        if ($row === null || !SchemaTypes::isOrganization((string)$row['type'])) {
            return null;
        }
        $type = (string)$row['type'];
        $data = $this->entityData($type, $row, $context);
        $siteUrl = rtrim($context->baseUrl, '/') . '/';
        $data['url'] ??= $siteUrl;
        $id = $siteUrl . '#organization';

        if ($context->isRootPage()) {
            $types[] = $this->assembler->organization($type, $data, $id, true);
            $language = $context->site->getLanguageById($context->languageId);
            $types[] = $this->assembler->webSite(
                $siteUrl . '#website',
                $siteUrl,
                (string)($context->site->getConfiguration()['websiteTitle'] ?? '') ?: (string)($data['name'] ?? ''),
                $id,
                $language->getLocale()->getName(),
            );
        }

        return $this->assembler->organization($type, $data, $id, false);
    }

    private function mainType(PageContext $context, ?PageSchemaConfig $config, ?TypeInterface $organization): ?TypeInterface
    {
        $entity = $this->records->findPageEntity($context);
        $type = $config?->locked ? $config->type : ((string)($entity['type'] ?? '') ?: (string)$config?->type);
        if (!SchemaTypes::isPageType($type)) {
            return null;
        }

        $data = $entity !== null ? $this->entityData($type, $entity, $context) : [];
        foreach ($config?->properties ?? [] as $property => $column) {
            if (str_starts_with($column, '@')) {
                continue;
            }
            $value = $this->normalizer->normalize('pages', $column, $context->page, $context, $property);
            // Values from the page type win, so editors maintain each fact in one place only.
            if ($value !== null) {
                $data[$property] = $value;
            }
        }

        return $this->assembler->pageType($type, $this->withPageFallbacks($type, $data, $context), $organization);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function entityData(string $type, array $row, PageContext $context): array
    {
        $data = [];
        foreach (EntitySchema::fieldsForType($type) as $column => $property) {
            $value = $this->normalizer->normalize('tx_schematic_entity', $column, $row, $context, $property);
            if ($value !== null) {
                $data[$property] = $value;
            }
        }
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withPageFallbacks(string $type, array $data, PageContext $context): array
    {
        $page = $context->page;
        $data[SchemaTypes::titleProperty($type)] ??= trim((string)($page['title'] ?? '')) ?: null;
        $data['description'] ??= trim((string)($page['description'] ?? '')) ?: null;
        $data['url'] ??= $this->pageUrl($context);
        $data['image'] ??= $this->normalizer->normalize('pages', 'media', $page, $context);

        $created = (int)($page['crdate'] ?? 0);
        $modified = (int)($page['SYS_LASTCHANGED'] ?? 0) ?: (int)($page['tstamp'] ?? 0);
        if ($type === 'JobPosting' && $created > 0) {
            $data['datePosted'] ??= date('Y-m-d', $created);
        }
        if (\in_array($type, ['Article', 'BlogPosting', 'NewsArticle'], true)) {
            $data['datePublished'] ??= $created > 0 ? date('c', $created) : null;
            $data['dateModified'] ??= $modified > 0 ? date('c', $modified) : null;
        }
        return $data;
    }

    private function pageUrl(PageContext $context): string
    {
        $uri = (string)$context->site->getRouter()->generateUri($context->pageUid, ['_language' => $context->languageId]);
        return ValueNormalizer::absolute($uri, $context);
    }
}
