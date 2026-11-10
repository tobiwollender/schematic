<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Tests\Unit\Builder;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TobiWollender\Schematic\Builder\EntitySchema;

final class EntitySchemaTest extends TestCase
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $tcaBackup;

    protected function setUp(): void
    {
        $this->tcaBackup = $GLOBALS['TCA'] ?? null;
        $columns = array_fill_keys(
            ['type', 'parentid', 'sys_language_uid', 'l10n_parent', 'hidden', 'title', 'description', 'author', 'datePublished', 'startDate', 'endDate'],
            ['config' => ['type' => 'input']]
        );
        $GLOBALS['TCA']['tx_schematic_entity'] = [
            'ctrl' => [
                'languageField' => 'sys_language_uid',
                'transOrigPointerField' => 'l10n_parent',
                'enablecolumns' => ['disabled' => 'hidden'],
            ],
            'columns' => $columns,
            'palettes' => [
                'dates' => ['showitem' => 'startDate, endDate'],
                'language' => ['showitem' => 'sys_language_uid, l10n_parent'],
            ],
            'types' => [
                'Article' => ['showitem' => '--div--;General, type, parentid, title;Headline, description, author, datePublished, notAColumn, --div--;Access, hidden, --palette--;;language'],
                'Event' => ['showitem' => 'type, title, --palette--;Dates;dates'],
            ],
        ];
    }

    protected function tearDown(): void
    {
        $GLOBALS['TCA'] = $this->tcaBackup;
    }

    #[Test]
    public function fieldsOfATypeAreItsFormFieldsWithoutSystemColumns(): void
    {
        self::assertSame(
            ['title' => 'headline', 'description' => 'description', 'author' => 'author', 'datePublished' => 'datePublished'],
            EntitySchema::fieldsForType('Article')
        );
    }

    #[Test]
    public function fieldsInsidePalettesCount(): void
    {
        self::assertSame(['title' => 'name', 'startDate' => 'startDate', 'endDate' => 'endDate'], EntitySchema::fieldsForType('Event'));
    }

    #[Test]
    public function unknownTypeHasNoFields(): void
    {
        self::assertSame([], EntitySchema::fieldsForType('Recipe'));
    }

    #[Test]
    public function columnsForPropertiesResolvesTheLabelProperty(): void
    {
        self::assertSame(['title', 'author'], EntitySchema::columnsForProperties('Article', ['headline', 'author', 'unknown']));
    }
}
