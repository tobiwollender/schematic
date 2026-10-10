<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use TobiWollender\Schematic\Model\SchemaTypes;

/**
 * Reads which columns of tx_schematic_entity belong to a schema.org type from the TCA Content Blocks generates.
 *
 * Field identifiers in ContentBlocks/RecordTypes are named after schema.org properties, so a new simple field
 * needs no PHP. The only exception is "title", which schema.org names differently per type.
 */
final class EntitySchema
{
    private const TABLE = 'tx_schematic_entity';
    private const NOT_A_PROPERTY = ['type', 'parentid'];

    /**
     * @return array<string, string> column => schema.org property (or internal key for nested types)
     */
    public static function fieldsForType(string $type): array
    {
        $tca = $GLOBALS['TCA'][self::TABLE] ?? [];
        $ctrl = $tca['ctrl'] ?? [];
        $systemColumns = array_merge(
            self::NOT_A_PROPERTY,
            array_values(array_filter([
                $ctrl['languageField'] ?? null,
                $ctrl['transOrigPointerField'] ?? null,
                $ctrl['transOrigDiffSourceField'] ?? null,
                $ctrl['translationSource'] ?? null,
            ])),
            array_values($ctrl['enablecolumns'] ?? []),
        );

        $fields = [];
        foreach (self::showitemColumns((string)($tca['types'][$type]['showitem'] ?? ''), $tca['palettes'] ?? []) as $column) {
            if (isset($tca['columns'][$column]) && !\in_array($column, $systemColumns, true)) {
                $fields[$column] = $column === 'title' ? SchemaTypes::titleProperty($type) : $column;
            }
        }
        return $fields;
    }

    /**
     * @param list<string> $properties
     * @return list<string>
     */
    public static function columnsForProperties(string $type, array $properties): array
    {
        return array_keys(array_intersect(self::fieldsForType($type), $properties));
    }

    /**
     * @param array<string, array{showitem?: string}> $palettes
     * @return list<string>
     */
    private static function showitemColumns(string $showitem, array $palettes): array
    {
        $columns = [];
        foreach (explode(',', $showitem) as $item) {
            $parts = array_map(trim(...), explode(';', $item));
            if ($parts[0] === '--palette--') {
                $columns = [...$columns, ...self::showitemColumns((string)($palettes[$parts[2] ?? '']['showitem'] ?? ''), [])];
            } elseif ($parts[0] !== '' && !str_starts_with($parts[0], '--')) {
                $columns[] = $parts[0];
            }
        }
        return $columns;
    }
}
