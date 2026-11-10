<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Tests\Unit\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TobiWollender\Schematic\Builder\ValueNormalizer;
use TobiWollender\Schematic\Model\PageContext;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Site\Entity\Site;

final class ValueNormalizerTest extends TestCase
{
    private const TABLE = 'tx_schematic_entity';

    /**
     * @var array<string, mixed>|null
     */
    private ?array $tcaBackup;

    private string $timezoneBackup;

    private ValueNormalizer $normalizer;

    private PageContext $context;

    protected function setUp(): void
    {
        $this->tcaBackup = $GLOBALS['TCA'] ?? null;
        $this->timezoneBackup = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $GLOBALS['TCA'][self::TABLE] = [
            'ctrl' => ['type' => 'type'],
            'columns' => [
                'title' => ['config' => ['type' => 'input']],
                'sameAs' => ['config' => ['type' => 'text']],
                'startDate' => ['config' => ['type' => 'datetime']],
                'datePosted' => ['config' => ['type' => 'datetime']],
                'eventStatus' => ['config' => ['type' => 'select', 'renderType' => 'selectSingle']],
                'employmentType' => ['config' => ['type' => 'select', 'renderType' => 'selectCheckBox']],
                'remote' => ['config' => ['type' => 'check']],
                'salaryMin' => ['config' => ['type' => 'number']],
                'offerPrice' => ['config' => ['type' => 'number', 'nullable' => true]],
            ],
            'types' => [
                // Content Blocks keeps per-type settings such as the date format here.
                'JobPosting' => ['columnsOverrides' => ['datePosted' => ['config' => ['format' => 'date']]]],
            ],
        ];

        $this->normalizer = new ValueNormalizer($this->createStub(FileRepository::class));
        $this->context = new PageContext(1, [], 0, $this->createStub(Site::class), 'https://example.com/', true);
    }

    protected function tearDown(): void
    {
        $GLOBALS['TCA'] = $this->tcaBackup;
        date_default_timezone_set($this->timezoneBackup);
    }

    /**
     * @return iterable<string, array{string, mixed, mixed}>
     */
    public static function values(): iterable
    {
        yield 'text is trimmed' => ['title', '  Open day ', 'Open day'];
        yield 'empty text' => ['title', '  ', null];
        yield 'timestamp with time' => ['startDate', 1795168800, '2026-11-20T10:00:00+00:00'];
        yield 'timestamp 0 is no date' => ['startDate', 0, null];
        yield 'date string is kept' => ['startDate', '2026-11-20T10:00:00+01:00', '2026-11-20T10:00:00+01:00'];
        yield 'zero date string is no date' => ['startDate', '0000-00-00 00:00:00', null];
        yield 'single select' => ['eventStatus', 'EventScheduled', 'EventScheduled'];
        yield 'single select without value' => ['eventStatus', '', null];
        yield 'multiple select from a list' => ['employmentType', 'FULL_TIME, PART_TIME', ['FULL_TIME', 'PART_TIME']];
        yield 'multiple select from an array' => ['employmentType', ['FULL_TIME', ''], ['FULL_TIME']];
        yield 'multiple select without value' => ['employmentType', '', null];
        yield 'checked' => ['remote', 1, true];
        yield 'unchecked' => ['remote', 0, false];
        yield 'number' => ['salaryMin', '15.50', 15.5];
        yield '0 in a plain number field means empty' => ['salaryMin', '0.00', null];
        yield '0 in a nullable number field is a value' => ['offerPrice', '0.00', 0.0];
        yield 'disabled nullable number field' => ['offerPrice', null, null];
    }

    #[Test]
    #[DataProvider('values')]
    public function valueIsNormalizedByItsColumnType(string $column, mixed $value, mixed $expected): void
    {
        self::assertSame($expected, $this->normalizer->normalize(self::TABLE, $column, ['type' => 'Event', $column => $value], $this->context));
    }

    #[Test]
    public function columnsOverridesOfTheRecordTypeApply(): void
    {
        $row = ['datePosted' => 1795168800];

        self::assertSame('2026-11-20', $this->normalizer->normalize(self::TABLE, 'datePosted', ['type' => 'JobPosting'] + $row, $this->context));
        self::assertSame('2026-11-20T10:00:00+00:00', $this->normalizer->normalize(self::TABLE, 'datePosted', ['type' => 'Event'] + $row, $this->context));
    }

    #[Test]
    public function listPropertiesAreSplitByLine(): void
    {
        $row = ['type' => 'Organization', 'sameAs' => "https://social.example/a\r\n\n  https://social.example/b  \n"];

        self::assertSame(
            ['https://social.example/a', 'https://social.example/b'],
            $this->normalizer->normalize(self::TABLE, 'sameAs', $row, $this->context, 'sameAs')
        );
        self::assertNull($this->normalizer->normalize(self::TABLE, 'sameAs', ['sameAs' => "\n"], $this->context, 'sameAs'));
    }

    #[Test]
    public function unknownColumnOrMissingValueGivesNull(): void
    {
        self::assertNull($this->normalizer->normalize(self::TABLE, 'unknown', ['unknown' => 'x'], $this->context));
        self::assertNull($this->normalizer->normalize(self::TABLE, 'title', [], $this->context));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function urls(): iterable
    {
        yield 'absolute url is kept' => ['https://cdn.example.com/logo.png', 'https://cdn.example.com/logo.png'];
        yield 'path with leading slash' => ['/fileadmin/logo.png', 'https://example.com/fileadmin/logo.png'];
        yield 'path without leading slash' => ['fileadmin/logo.png', 'https://example.com/fileadmin/logo.png'];
    }

    #[Test]
    #[DataProvider('urls')]
    public function relativeUrlsGetTheBaseUrl(string $url, string $expected): void
    {
        self::assertSame($expected, ValueNormalizer::absolute($url, $this->context));
    }
}
