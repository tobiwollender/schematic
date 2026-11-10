<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Tests\Unit\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TobiWollender\Schematic\Builder\CustomJsonLd;

final class CustomJsonLdTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed}>
     */
    public static function valuesWithoutData(): iterable
    {
        yield 'field missing' => [null];
        yield 'empty string' => [''];
        yield 'whitespace' => ["  \n"];
        yield 'json null' => ['null'];
        yield 'empty object' => ['{}'];
        yield 'empty list' => ['[]'];
        yield 'scalar' => ['"text"'];
        yield 'invalid json' => ['{"@type": "Product"'];
    }

    #[Test]
    #[DataProvider('valuesWithoutData')]
    public function pageWithoutUsableJsonGivesNull(mixed $value): void
    {
        $page = $value === null ? [] : [CustomJsonLd::FIELD => $value];

        self::assertNull(CustomJsonLd::fromPage($page));
    }

    #[Test]
    public function objectWithoutContextGetsTheSchemaOrgContext(): void
    {
        $data = CustomJsonLd::fromPage([CustomJsonLd::FIELD => '{"@type": "Product", "name": "Chair"}']);

        self::assertSame(['@context' => 'https://schema.org', '@type' => 'Product', 'name' => 'Chair'], $data);
    }

    #[Test]
    public function existingContextIsKept(): void
    {
        $data = CustomJsonLd::fromPage([CustomJsonLd::FIELD => '{"@context": "https://schema.org/", "@type": "Product"}']);

        self::assertSame(['@context' => 'https://schema.org/', '@type' => 'Product'], $data);
    }

    #[Test]
    public function listBecomesAGraph(): void
    {
        $data = CustomJsonLd::fromPage([CustomJsonLd::FIELD => '[{"@type": "Product"}, {"@type": "Recipe"}]']);

        self::assertSame(['@context' => 'https://schema.org', '@graph' => [['@type' => 'Product'], ['@type' => 'Recipe']]], $data);
    }

    #[Test]
    public function encodedValueCannotCloseTheScriptElement(): void
    {
        $json = CustomJsonLd::encode(['name' => '</script><script>alert(1)</script>', 'url' => 'https://example.com/ä']);

        self::assertStringNotContainsString('<', $json);
        self::assertStringContainsString('https://example.com/ä', $json);
        self::assertSame('</script><script>alert(1)</script>', json_decode($json, true)['name']);
    }
}
