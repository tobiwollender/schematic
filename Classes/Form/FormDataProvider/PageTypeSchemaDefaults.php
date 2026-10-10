<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Form\FormDataProvider;

use TobiWollender\Schematic\Builder\EntitySchema;
use TobiWollender\Schematic\Configuration\PageSchemaConfigProviderInterface;
use TobiWollender\Schematic\Model\SchemaTypes;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Adapts the structured data form of a record that belongs to a page.
 *
 * Organizations are maintained once per site, so pages only offer page types. A type an integrator attached
 * to the page type is preselected; locked types offer only that type and hide fields the page type already fills.
 */
final readonly class PageTypeSchemaDefaults implements FormDataProviderInterface
{
    public function __construct(
        private PageSchemaConfigProviderInterface $configProvider,
    ) {}

    public function addData(array $result): array
    {
        if ($result['tableName'] !== 'tx_schematic_entity') {
            return $result;
        }
        $pageUid = ($result['inlineParentTableName'] ?? '') === 'pages'
            ? (int)$result['inlineParentUid']
            : (int)($result['databaseRow']['parentid'] ?? 0);
        $page = $pageUid > 0 ? BackendUtility::getRecord('pages', $pageUid) : null;
        if ($page === null) {
            return $result;
        }
        $this->limitTypes($result, ['', ...SchemaTypes::PAGE_TYPES]);

        $config = $this->configProvider->forPage($page);
        if ($config === null) {
            return $result;
        }

        $currentType = (string)($result['databaseRow']['type'] ?? '');
        if ($config->locked || $currentType === '') {
            $result['databaseRow']['type'] = $config->type;
        }
        if (!$config->locked) {
            return $result;
        }

        $result['processedTca']['columns']['type']['description'] = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:type.locked';
        $this->limitTypes($result, [$config->type]);

        $hidden = EntitySchema::columnsForProperties($config->type, array_keys($config->properties));
        if (isset($result['processedTca']['types'][$config->type]['showitem'])) {
            $result['processedTca']['types'][$config->type]['showitem'] = self::withoutFields($result['processedTca']['types'][$config->type]['showitem'], $hidden);
        }
        foreach ($result['processedTca']['palettes'] ?? [] as $name => $palette) {
            $result['processedTca']['palettes'][$name]['showitem'] = self::withoutFields((string)($palette['showitem'] ?? ''), $hidden);
        }
        return $result;
    }

    /**
     * @param array<string, mixed> $result
     * @param list<string> $types
     */
    private function limitTypes(array &$result, array $types): void
    {
        $result['processedTca']['columns']['type']['config']['items'] = array_values(array_filter(
            $result['processedTca']['columns']['type']['config']['items'] ?? [],
            static fn(array $item): bool => \in_array($item['value'] ?? '', $types, true)
        ));
    }

    /**
     * @param list<string> $fields
     */
    private static function withoutFields(string $showitem, array $fields): string
    {
        $items = array_filter(
            array_map(trim(...), explode(',', $showitem)),
            static fn(string $item): bool => !\in_array(trim(explode(';', $item)[0]), $fields, true)
        );
        return implode(', ', $items);
    }
}
