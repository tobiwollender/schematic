<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Tests\Unit\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TobiWollender\Schematic\Builder\SchemaGraphBuilder;

/**
 * Covers the value maps of schema.yaml. The builder itself needs a database, so the mapping is called directly.
 */
final class SchemaGraphBuilderValueMapTest extends TestCase
{
    private const MAP = ['vollzeit' => 'FULL_TIME', 'teilzeit' => 'PART_TIME', 'minijob' => 'PART_TIME', '1' => 'yes', '0' => 'no'];

    /**
     * @return iterable<string, array{mixed, mixed}>
     */
    public static function values(): iterable
    {
        yield 'mapped value' => ['vollzeit', 'FULL_TIME'];
        yield 'unmapped value is dropped' => ['werkstudent', null];
        yield 'checkbox on' => [true, 'yes'];
        yield 'checkbox off' => [false, 'no'];
        yield 'list is mapped per value' => [['vollzeit', 'teilzeit'], ['FULL_TIME', 'PART_TIME']];
        yield 'list drops unmapped values and duplicates' => [['teilzeit', 'werkstudent', 'minijob'], ['PART_TIME']];
        yield 'list without any mapped value' => [['werkstudent'], null];
    }

    #[Test]
    #[DataProvider('values')]
    public function storedValuesAreTranslated(mixed $value, mixed $expected): void
    {
        $mapValue = new \ReflectionMethod(SchemaGraphBuilder::class, 'mapValue');

        self::assertSame($expected, $mapValue->invoke(null, $value, self::MAP));
    }
}
