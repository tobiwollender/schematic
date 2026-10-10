<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use TobiWollender\Schematic\Model\PageContext;
use TYPO3\CMS\Core\Resource\FileRepository;

/**
 * Turns a raw database value into a value schema.org understands, based on the TCA of its column.
 */
final readonly class ValueNormalizer
{
    private const LIST_PROPERTIES = ['sameAs', 'openingHours'];

    public function __construct(
        private FileRepository $fileRepository,
    ) {}

    /**
     * @param array<string, mixed> $row
     */
    public function normalize(string $table, string $column, array $row, PageContext $context, string $property = ''): mixed
    {
        $config = $this->columnConfig($table, $column, $row);
        if ($config === null || !\array_key_exists($column, $row)) {
            return null;
        }
        $value = $row[$column];

        $normalized = match ($config['type'] ?? '') {
            'datetime' => $this->dateTime($value, ($config['format'] ?? '') === 'date'),
            'select' => $this->select($value, $config),
            'check' => (bool)$value,
            'number' => (float)$value > 0 ? (float)$value : null,
            'file' => $this->fileUrl($table, $column, $row, $context),
            default => trim((string)$value),
        };

        if (\is_string($normalized) && \in_array($property, self::LIST_PROPERTIES, true)) {
            $normalized = array_values(array_filter(array_map(trim(...), preg_split('/\R/', $normalized) ?: [])));
        }

        return $normalized === '' || $normalized === [] ? null : $normalized;
    }

    /**
     * Content Blocks keeps field settings such as the date format in the columnsOverrides of its record type.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private function columnConfig(string $table, string $column, array $row): ?array
    {
        $config = $GLOBALS['TCA'][$table]['columns'][$column]['config'] ?? null;
        if ($config === null) {
            return null;
        }
        $typeField = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? '';
        $recordType = $typeField !== '' ? (string)($row[$typeField] ?? '') : '';
        $override = $GLOBALS['TCA'][$table]['types'][$recordType]['columnsOverrides'][$column]['config'] ?? [];
        return array_replace_recursive($config, $override);
    }

    private function dateTime(mixed $value, bool $dateOnly): ?string
    {
        if (is_numeric($value)) {
            $timestamp = (int)$value;
            if ($timestamp === 0) {
                return null;
            }
            return $dateOnly ? date('Y-m-d', $timestamp) : date('c', $timestamp);
        }
        $value = (string)$value;
        return $value === '' || str_starts_with($value, '0000-00-00') ? null : $value;
    }

    /**
     * @param array<string, mixed> $config
     * @return string|list<string>|null
     */
    private function select(mixed $value, array $config): string|array|null
    {
        $multiple = \in_array($config['renderType'] ?? '', ['selectCheckBox', 'selectMultipleSideBySide'], true)
            || (int)($config['maxitems'] ?? 1) > 1;
        if (!$multiple) {
            return trim((string)$value);
        }
        $values = \is_array($value) ? $value : explode(',', (string)$value);
        return array_values(array_filter(array_map(static fn($item): string => trim((string)$item), $values)));
    }

    /**
     * @param array<string, mixed> $row
     */
    private function fileUrl(string $table, string $column, array $row, PageContext $context): ?string
    {
        // Overlaid records keep the default uid; their own file references hang on the overlay uid.
        $uid = (int)($row['_LOCALIZED_UID'] ?? $row['_PAGES_OVERLAY_UID'] ?? $row['uid'] ?? 0);
        if ($uid === 0) {
            return null;
        }
        $references = $this->fileRepository->findByRelation($table, $column, $uid);
        $url = isset($references[0]) ? (string)$references[0]->getPublicUrl() : '';
        return $url === '' ? null : self::absolute($url, $context);
    }

    public static function absolute(string $url, PageContext $context): string
    {
        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }
        return rtrim($context->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
