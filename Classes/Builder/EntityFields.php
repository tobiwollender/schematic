<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use TobiWollender\Schematic\Model\SchemaTypes;

/**
 * Which column of tx_schematic_entity feeds which schema.org property, per type.
 *
 * Keys that are not schema.org properties (street, salaryMin, …) are assembled into nested types later.
 */
final class EntityFields
{
    private const ADDRESS = [
        'street' => 'streetAddress',
        'postal_code' => 'postalCode',
        'locality' => 'addressLocality',
        'region' => 'addressRegion',
        'country' => 'addressCountry',
    ];

    /**
     * @return array<string, string> column => property
     */
    public static function forType(string $type): array
    {
        $fields = [
            'title' => SchemaTypes::titleProperty($type),
            'description' => 'description',
            'image' => 'image',
        ];

        return $fields + match ($type) {
            'Article', 'BlogPosting', 'NewsArticle' => [
                'author_name' => 'author',
                'date_published' => 'datePublished',
            ],
            'JobPosting' => [
                'date_posted' => 'datePosted',
                'valid_through' => 'validThrough',
                'employment_type' => 'employmentType',
                'remote' => 'remote',
                'salary_min' => 'salaryMin',
                'salary_max' => 'salaryMax',
                'salary_currency' => 'salaryCurrency',
                'salary_unit' => 'salaryUnit',
            ] + self::ADDRESS,
            'Event' => [
                'start_date' => 'startDate',
                'end_date' => 'endDate',
                'event_status' => 'eventStatus',
                'attendance_mode' => 'eventAttendanceMode',
                'location_name' => 'locationName',
                'online_url' => 'onlineUrl',
                'offer_price' => 'offerPrice',
                'offer_currency' => 'offerCurrency',
                'offer_url' => 'offerUrl',
            ] + self::ADDRESS,
            'Organization', 'LocalBusiness' => [
                'url' => 'url',
                'logo' => 'logo',
                'telephone' => 'telephone',
                'email' => 'email',
                'same_as' => 'sameAs',
                'opening_hours' => 'openingHours',
            ] + self::ADDRESS,
            default => [],
        };
    }

    /**
     * @param list<string> $properties
     * @return list<string>
     */
    public static function columnsForProperties(string $type, array $properties): array
    {
        return array_keys(array_intersect(self::forType($type), $properties));
    }
}
