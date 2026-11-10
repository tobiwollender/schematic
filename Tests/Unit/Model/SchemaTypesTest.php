<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TobiWollender\Schematic\Model\SchemaTypes;

final class SchemaTypesTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function labelProperties(): iterable
    {
        yield 'job: name' => ['JobPosting', 'name', 'title'];
        yield 'job: headline' => ['JobPosting', 'headline', 'title'];
        yield 'article: title' => ['Article', 'title', 'headline'];
        yield 'blog posting: name' => ['BlogPosting', 'name', 'headline'];
        yield 'news article: title' => ['NewsArticle', 'title', 'headline'];
        yield 'event: title' => ['Event', 'title', 'name'];
        yield 'organization: headline' => ['Organization', 'headline', 'name'];
        yield 'other properties stay' => ['JobPosting', 'employmentType', 'employmentType'];
    }

    #[Test]
    #[DataProvider('labelProperties')]
    public function labelPropertyIsNamedAsSchemaOrgNamesItForTheType(string $type, string $property, string $expected): void
    {
        self::assertSame($expected, SchemaTypes::normalizeProperty($type, $property));
    }

    #[Test]
    public function organizationsAreNoPageTypes(): void
    {
        self::assertTrue(SchemaTypes::isPageType('Event'));
        self::assertFalse(SchemaTypes::isPageType('LocalBusiness'));
        self::assertTrue(SchemaTypes::isOrganization('LocalBusiness'));
        self::assertFalse(SchemaTypes::isOrganization('Event'));
        self::assertFalse(SchemaTypes::isPageType(''));
    }
}
