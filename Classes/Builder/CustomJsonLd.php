<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

/**
 * JSON-LD an admin entered for a page. It replaces the structured data schematic derives for that page.
 */
final class CustomJsonLd
{
    public const FIELD = 'tx_schematic_json';

    /**
     * @param array<string, mixed> $page
     * @return array<mixed>|null
     */
    public static function fromPage(array $page): ?array
    {
        $json = trim((string)($page[self::FIELD] ?? ''));
        if ($json === '' || $json === 'null' || $json === '{}' || $json === '[]') {
            return null;
        }
        try {
            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
        if (!\is_array($data) || $data === []) {
            return null;
        }
        // Snippets from generators usually carry @context; a bare object or a list of objects gets it added.
        if (array_is_list($data)) {
            return ['@context' => 'https://schema.org', '@graph' => $data];
        }
        return isset($data['@context']) ? $data : ['@context' => 'https://schema.org'] + $data;
    }

    /**
     * Re-encoding with JSON_HEX_TAG keeps a "</script>" inside a value from ending the script element.
     *
     * @param array<mixed> $data
     */
    public static function encode(array $data, bool $pretty = false): string
    {
        $flags = \JSON_HEX_TAG | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR;
        return json_encode($data, $pretty ? $flags | \JSON_PRETTY_PRINT : $flags);
    }
}
