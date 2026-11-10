<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Tests\Unit\Builder;

use Brotkrueml\Schema\Core\Model\TypeInterface;
use Brotkrueml\Schema\JsonLd\Renderer;
use Brotkrueml\Schema\Type\AdditionalPropertiesProvider;
use Brotkrueml\Schema\Type\TypeFactory;
use Brotkrueml\Schema\Type\TypeRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TobiWollender\Schematic\Builder\TypeAssembler;
use TobiWollender\Schematic\Schema\JobPostingAdditionalProperties;

final class TypeAssemblerTest extends TestCase
{
    private const TYPES = [
        'Answer', 'Article', 'BlogPosting', 'Country', 'Event', 'FAQPage', 'JobPosting', 'LocalBusiness',
        'MonetaryAmount', 'NewsArticle', 'Offer', 'Organization', 'Person', 'Place', 'PostalAddress',
        'QuantitativeValue', 'Question', 'VirtualLocation', 'WebSite',
    ];

    private TypeAssembler $assembler;

    protected function setUp(): void
    {
        // EXT:schema fills its registry in a compiler pass; the tests register the types they need by hand.
        $registry = new TypeRegistry();
        foreach (self::TYPES as $type) {
            $registry->addType($type, 'Brotkrueml\\Schema\\Model\\Type\\' . $type);
        }
        $additionalProperties = new AdditionalPropertiesProvider(['JobPosting' => [JobPostingAdditionalProperties::class]]);
        $this->assembler = new TypeAssembler(new TypeFactory($additionalProperties, $registry));
    }

    /**
     * @return array<string, mixed>
     */
    private function render(TypeInterface $type): array
    {
        $renderer = new Renderer();
        $renderer->addType($type);
        return json_decode($renderer->render(), true, 512, \JSON_THROW_ON_ERROR);
    }

    private function organization(): TypeInterface
    {
        return $this->assembler->organization('Organization', ['name' => 'Example Ltd'], 'https://example.com/#organization', false);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, array<string, mixed>|null}>
     */
    public static function eventOffers(): iterable
    {
        yield 'no price, no link' => [[], null];
        yield 'link without price does not claim the event is free' => [
            ['offerUrl' => 'https://example.com/tickets'],
            ['@type' => 'Offer', 'url' => 'https://example.com/tickets'],
        ];
        yield 'entered 0 means free' => [
            ['offerPrice' => 0.0],
            ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ];
        yield 'price with currency and link' => [
            ['offerPrice' => 12.5, 'offerCurrency' => 'CHF', 'offerUrl' => 'https://example.com/tickets'],
            ['@type' => 'Offer', 'price' => '12.5', 'priceCurrency' => 'CHF', 'url' => 'https://example.com/tickets'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $expected
     */
    #[Test]
    #[DataProvider('eventOffers')]
    public function eventOfferFollowsPriceAndLink(array $data, ?array $expected): void
    {
        $event = $this->render($this->assembler->pageType('Event', ['name' => 'Open day'] + $data, null));

        self::assertSame($expected, $event['offers'] ?? null);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, mixed}>
     */
    public static function eventLocations(): iterable
    {
        $address = ['@type' => 'PostalAddress', 'addressLocality' => 'Regensburg'];
        yield 'on-site without name and address' => [[], null];
        yield 'name only' => [['locationName' => 'Town hall'], ['@type' => 'Place', 'name' => 'Town hall']];
        yield 'address only' => [['addressLocality' => 'Regensburg'], ['@type' => 'Place', 'address' => $address]];
        yield 'online ignores the place' => [
            ['eventAttendanceMode' => 'OnlineEventAttendanceMode', 'locationName' => 'Town hall', 'onlineUrl' => 'https://example.com/live'],
            ['@type' => 'VirtualLocation', 'url' => 'https://example.com/live'],
        ];
        yield 'mixed lists place and stream' => [
            ['eventAttendanceMode' => 'MixedEventAttendanceMode', 'locationName' => 'Town hall', 'onlineUrl' => 'https://example.com/live'],
            [['@type' => 'Place', 'name' => 'Town hall'], ['@type' => 'VirtualLocation', 'url' => 'https://example.com/live']],
        ];
        yield 'on-site ignores the stream' => [
            ['locationName' => 'Town hall', 'onlineUrl' => 'https://example.com/live'],
            ['@type' => 'Place', 'name' => 'Town hall'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[Test]
    #[DataProvider('eventLocations')]
    public function eventLocationFollowsAttendanceMode(array $data, mixed $expected): void
    {
        $event = $this->render($this->assembler->pageType('Event', ['name' => 'Open day'] + $data, null));

        self::assertSame($expected, $event['location'] ?? null);
    }

    #[Test]
    public function eventGetsEnumerationUrlsOrganizerAndPerformer(): void
    {
        $event = $this->render($this->assembler->pageType('Event', [
            'name' => 'Open day',
            'description' => '<p>Tours and <strong>talks</strong>.</p>',
            'startDate' => '2026-11-20T10:00:00+00:00',
            'eventStatus' => 'EventScheduled',
            'eventAttendanceMode' => 'OfflineEventAttendanceMode',
            'performer' => 'Sam Example',
        ], $this->organization()));

        self::assertSame('Tours and talks.', $event['description']);
        self::assertSame('https://schema.org/EventScheduled', $event['eventStatus']);
        self::assertSame('https://schema.org/OfflineEventAttendanceMode', $event['eventAttendanceMode']);
        self::assertSame(['@type' => 'Person', 'name' => 'Sam Example'], $event['performer']);
        self::assertSame('https://example.com/#organization', $event['organizer']['@id']);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, array<string, mixed>|null}>
     */
    public static function salaries(): iterable
    {
        yield 'no salary' => [[], null];
        yield 'range' => [
            ['salaryMin' => 15.0, 'salaryMax' => 17.0, 'salaryUnit' => 'HOUR'],
            ['@type' => 'QuantitativeValue', 'maxValue' => 17.0, 'minValue' => 15.0, 'unitText' => 'HOUR'],
        ];
        yield 'single value defaults to a yearly salary' => [
            ['salaryMin' => 50000.0],
            ['@type' => 'QuantitativeValue', 'unitText' => 'YEAR', 'value' => 50000.0],
        ];
        yield 'equal bounds are one value' => [
            ['salaryMin' => 3000.0, 'salaryMax' => 3000.0, 'salaryUnit' => 'MONTH'],
            ['@type' => 'QuantitativeValue', 'unitText' => 'MONTH', 'value' => 3000.0],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $expected
     */
    #[Test]
    #[DataProvider('salaries')]
    public function jobPostingBuildsBaseSalary(array $data, ?array $expected): void
    {
        $job = $this->render($this->assembler->pageType('JobPosting', ['title' => 'Working student'] + $data, null));

        self::assertEquals($expected, $job['baseSalary']['value'] ?? null);
        if ($expected !== null) {
            self::assertSame('EUR', $job['baseSalary']['currency']);
        }
    }

    #[Test]
    public function jobPostingBuildsNestedTypesAndKeepsHelperFieldsOutOfTheTopLevel(): void
    {
        $job = $this->render($this->assembler->pageType('JobPosting', [
            'title' => 'Working student',
            'description' => '<p>Tasks:</p><ul><li>Newsletter</li></ul><script>alert(1)</script><div>Team</div>',
            'employmentType' => ['PART_TIME'],
            'streetAddress' => 'Example Street 1',
            'addressLocality' => 'Regensburg',
            'addressCountry' => 'DE',
            'remote' => true,
            'salaryMin' => 15.0,
            'salaryCurrency' => 'EUR',
            'identifier' => 'MG-2026-014',
            'directApply' => true,
            'unknownField' => 'ignored',
        ], $this->organization()));

        self::assertSame('<p>Tasks:</p><ul><li>Newsletter</li></ul>alert(1)Team', $job['description']);
        self::assertSame('PART_TIME', $job['employmentType']);
        self::assertSame('Regensburg', $job['jobLocation']['address']['addressLocality']);
        self::assertSame('TELECOMMUTE', $job['jobLocationType']);
        self::assertSame(['@type' => 'Country', 'name' => 'DE'], $job['applicantLocationRequirements']);
        self::assertSame('https://example.com/#organization', $job['hiringOrganization']['@id']);
        self::assertSame('MG-2026-014', $job['identifier']);
        // EXT:schema renders booleans as schema.org enumeration values.
        self::assertSame('https://schema.org/True', $job['directApply']);
        foreach (['streetAddress', 'addressLocality', 'remote', 'salaryMin', 'salaryCurrency', 'unknownField'] as $field) {
            self::assertArrayNotHasKey($field, $job);
        }
    }

    #[Test]
    public function jobPostingListsSeveralEmploymentTypes(): void
    {
        $job = $this->render($this->assembler->pageType('JobPosting', ['title' => 'Editor', 'employmentType' => ['FULL_TIME', 'PART_TIME']], null));

        self::assertSame(['FULL_TIME', 'PART_TIME'], $job['employmentType']);
    }

    #[Test]
    #[DataProvider('articleTypes')]
    public function articleGetsAuthorAndPublisher(string $type): void
    {
        $article = $this->render($this->assembler->pageType($type, [
            'headline' => 'Structured data in TYPO3',
            'description' => '<p>Without <em>JSON</em>.</p>',
            'author' => 'Alex Example',
            'datePublished' => '2026-09-15T00:00:00+00:00',
        ], $this->organization()));

        self::assertSame($type, $article['@type']);
        self::assertSame('Structured data in TYPO3', $article['headline']);
        self::assertSame('Without JSON.', $article['description']);
        self::assertSame(['@type' => 'Person', 'name' => 'Alex Example'], $article['author']);
        self::assertSame('https://example.com/#organization', $article['publisher']['@id']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function articleTypes(): iterable
    {
        yield 'Article' => ['Article'];
        yield 'BlogPosting' => ['BlogPosting'];
        yield 'NewsArticle' => ['NewsArticle'];
    }

    #[Test]
    public function organizationReferenceCarriesOnlyNameUrlAndLogo(): void
    {
        $data = [
            'name' => 'Example Ltd',
            'url' => 'https://example.com/',
            'logo' => 'https://example.com/logo.png',
            'telephone' => '+49 941 1234560',
            'streetAddress' => 'Example Street 1',
            'sameAs' => ['https://social.example/example'],
            'openingHours' => ['Mo-Fr 08:00-17:00'],
        ];

        $reference = $this->render($this->assembler->organization('LocalBusiness', $data, 'https://example.com/#organization', false));
        $full = $this->render($this->assembler->organization('LocalBusiness', $data, 'https://example.com/#organization', true));

        self::assertSame(['@context', '@type', '@id', 'logo', 'name', 'url'], array_keys($reference));
        self::assertSame('+49 941 1234560', $full['telephone']);
        self::assertSame(['@type' => 'PostalAddress', 'streetAddress' => 'Example Street 1'], $full['address']);
        self::assertSame(['https://social.example/example'], $full['sameAs']);
        self::assertSame(['Mo-Fr 08:00-17:00'], $full['openingHours']);
    }

    #[Test]
    public function openingHoursBelongToLocalBusinessOnly(): void
    {
        $organization = $this->render($this->assembler->organization('Organization', ['name' => 'Example Ltd', 'openingHours' => ['Mo-Fr 08:00-17:00']], 'https://example.com/#organization', true));

        self::assertArrayNotHasKey('openingHours', $organization);
    }

    #[Test]
    public function webSiteReferencesItsPublisher(): void
    {
        $webSite = $this->render($this->assembler->webSite('https://example.com/#website', 'https://example.com/', 'Example', 'https://example.com/#organization', 'de-DE'));

        self::assertSame('Example', $webSite['name']);
        self::assertSame('de-DE', $webSite['inLanguage']);
        self::assertSame(['@type' => 'Organization', '@id' => 'https://example.com/#organization'], $webSite['publisher']);
    }

    #[Test]
    public function faqPageKeepsBasicHtmlInAnswers(): void
    {
        $faq = $this->render($this->assembler->faqPage([
            ['question' => 'Where are you?', 'answer' => '<p>At <a href="https://example.com/">Example Street</a>.</p><img src="x.png">'],
            ['question' => 'When are you open?', 'answer' => '<p>Monday to Friday.</p>'],
        ], 'https://example.com/faq'));

        self::assertSame('https://example.com/faq', $faq['url']);
        self::assertCount(2, $faq['mainEntity']);
        self::assertSame('Where are you?', $faq['mainEntity'][0]['name']);
        self::assertSame('<p>At <a href="https://example.com/">Example Street</a>.</p>', $faq['mainEntity'][0]['acceptedAnswer']['text']);
    }
}
