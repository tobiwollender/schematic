<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\ContentBlocks;

use TobiWollender\Schematic\Configuration\PageSchemaConfigProviderInterface;
use TobiWollender\Schematic\Model\PageSchemaConfig;
use TobiWollender\Schematic\Model\SchemaTypes;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Reads schema.yaml next to the config.yaml of a Content Blocks page type.
 *
 * A separate file keeps `content-blocks:lint` green, because config.yaml rejects unknown keys.
 * Both Content Blocks classes used here are marked internal, so all access stays in this adapter.
 */
final class ContentBlocksPageSchemaConfigProvider implements PageSchemaConfigProviderInterface
{
    private const FILE_NAME = 'schema.yaml';

    /**
     * @var array<string, PageSchemaConfig|null>
     */
    private array $configs = [];

    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
        private readonly TableDefinitionCollection $tableDefinitionCollection,
    ) {}

    public function forPage(array $page): ?PageSchemaConfig
    {
        if (!isset($page['doktype'])) {
            return null;
        }
        $doktype = (string)$page['doktype'];
        if (!\array_key_exists($doktype, $this->configs)) {
            $this->configs[$doktype] = $this->load($doktype);
        }
        return $this->configs[$doktype];
    }

    private function load(string $doktype): ?PageSchemaConfig
    {
        $contentBlock = $this->contentBlockRegistry->getFromRawRecord('pages', ['doktype' => $doktype]);
        if ($contentBlock === null) {
            return null;
        }
        $file = GeneralUtility::getFileAbsFileName($contentBlock->getExtPath() . '/' . self::FILE_NAME);
        if ($file === '' || !is_file($file)) {
            return null;
        }
        $settings = Yaml::parseFile($file);
        $type = (string)($settings['type'] ?? '');
        if (!SchemaTypes::isPageType($type)) {
            return null;
        }

        $columns = $this->columnsByIdentifier((int)$doktype);
        $properties = [];
        $valueMaps = [];
        foreach ((array)($settings['properties'] ?? []) as $property => $mapping) {
            $property = SchemaTypes::normalizeProperty($type, (string)$property);
            // Short form "property: field", long form with "field" and "values" for select fields whose
            // stored values differ from what schema.org expects.
            $field = (string)(\is_array($mapping) ? ($mapping['field'] ?? '') : $mapping);
            if ($field === '') {
                continue;
            }
            // Field identifiers from config.yaml are prefixed in the database; core page fields are not.
            $properties[$property] = str_starts_with($field, '@') ? $field : ($columns[$field] ?? $field);
            if (\is_array($mapping) && \is_array($mapping['values'] ?? null)) {
                foreach ($mapping['values'] as $from => $to) {
                    $valueMaps[$property][(string)$from] = $to;
                }
            }
        }

        return new PageSchemaConfig($type, (bool)($settings['locked'] ?? true), $properties, $contentBlock->getName(), $valueMaps);
    }

    /**
     * @return array<string, string> field identifier from config.yaml => database column
     */
    private function columnsByIdentifier(int $doktype): array
    {
        if (!$this->tableDefinitionCollection->hasTable('pages')) {
            return [];
        }
        $table = $this->tableDefinitionCollection->getTable('pages');
        if (!$table->contentTypeDefinitionCollection->hasType($doktype)) {
            return [];
        }
        $columns = [];
        foreach ($table->contentTypeDefinitionCollection->getType($doktype)->getColumns() as $column) {
            if ($table->tcaFieldDefinitionCollection->hasField($column)) {
                $columns[$table->tcaFieldDefinitionCollection->getField($column)->identifier] = $column;
            }
        }
        return $columns;
    }
}
