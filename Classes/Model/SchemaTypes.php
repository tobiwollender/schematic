<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Model;

final class SchemaTypes
{
    public const PAGE_TYPES = ['Article', 'BlogPosting', 'NewsArticle', 'Event', 'JobPosting'];
    public const ORGANIZATION_TYPES = ['Organization', 'LocalBusiness'];

    public static function isPageType(string $type): bool
    {
        return \in_array($type, self::PAGE_TYPES, true);
    }

    public static function isOrganization(string $type): bool
    {
        return \in_array($type, self::ORGANIZATION_TYPES, true);
    }

    /**
     * schema.org names the main label differently per type.
     */
    public static function titleProperty(string $type): string
    {
        return match ($type) {
            'JobPosting' => 'title',
            'Article', 'BlogPosting', 'NewsArticle' => 'headline',
            default => 'name',
        };
    }

    /**
     * Lets integrators write "name", "title" or "headline" for every type.
     */
    public static function normalizeProperty(string $type, string $property): string
    {
        return \in_array($property, ['name', 'title', 'headline'], true) ? self::titleProperty($type) : $property;
    }
}
