<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use Brotkrueml\Schema\Core\Model\TypeInterface;
use Brotkrueml\Schema\Type\TypeFactory;

/**
 * Builds schema.org types from flat property data, including the nested types Google expects.
 */
final readonly class TypeAssembler
{
    /**
     * Fields that only feed nested types and must not be passed on as top-level properties.
     */
    private const NESTED_FIELDS = [
        'streetAddress', 'postalCode', 'addressLocality', 'addressRegion', 'addressCountry',
        'salaryMin', 'salaryMax', 'salaryCurrency', 'salaryUnit', 'remote',
        'locationName', 'onlineUrl', 'offerPrice', 'offerCurrency', 'offerUrl',
    ];

    public function __construct(
        private TypeFactory $typeFactory,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function organization(string $type, array $data, string $id, bool $full): TypeInterface
    {
        $organization = $this->typeFactory->create($type)->setId($id);
        $this->set($organization, $data, ['name', 'url', 'logo']);
        if (!$full) {
            return $organization;
        }
        $this->set($organization, $data, ['description', 'image', 'telephone', 'email', 'sameAs']);
        if ($type === 'LocalBusiness') {
            $this->set($organization, $data, ['openingHours']);
        }
        $address = $this->address($data);
        if ($address !== null) {
            $organization->setProperty('address', $address);
        }
        $this->setRemaining($organization, $data);
        return $organization;
    }

    public function webSite(string $id, string $url, string $name, string $organizationId, string $locale): TypeInterface
    {
        $webSite = $this->typeFactory->create('WebSite')
            ->setId($id)
            ->setProperty('url', $url)
            ->setProperty('name', $name);
        if ($organizationId !== '') {
            $webSite->setProperty('publisher', $this->typeFactory->create('Organization')->setId($organizationId));
        }
        if ($locale !== '') {
            $webSite->setProperty('inLanguage', $locale);
        }
        return $webSite;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function pageType(string $type, array $data, ?TypeInterface $organization): TypeInterface
    {
        return match ($type) {
            'JobPosting' => $this->jobPosting($data, $organization),
            'Event' => $this->event($data, $organization),
            default => $this->article($type, $data, $organization),
        };
    }

    /**
     * @param list<array{question: string, answer: string}> $items
     */
    public function faqPage(array $items, string $url): TypeInterface
    {
        $questions = [];
        foreach ($items as $item) {
            $questions[] = $this->typeFactory->create('Question')
                ->setProperty('name', $item['question'])
                ->setProperty('acceptedAnswer', $this->typeFactory->create('Answer')->setProperty('text', self::basicHtml($item['answer'])));
        }
        return $this->typeFactory->create('FAQPage')
            ->setProperty('url', $url)
            ->setProperty('mainEntity', $questions);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jobPosting(array $data, ?TypeInterface $organization): TypeInterface
    {
        $job = $this->typeFactory->create('JobPosting');
        $data['description'] = isset($data['description']) ? self::basicHtml((string)$data['description']) : null;
        $this->set($job, $data, ['title', 'description', 'datePosted', 'validThrough', 'url', 'image']);
        if (!empty($data['employmentType'])) {
            $types = (array)$data['employmentType'];
            $job->setProperty('employmentType', \count($types) === 1 ? $types[0] : $types);
        }
        if ($organization !== null) {
            $job->setProperty('hiringOrganization', $organization);
        }
        $address = $this->address($data);
        if ($address !== null) {
            $job->setProperty('jobLocation', $this->typeFactory->create('Place')->setProperty('address', $address));
        }
        if (!empty($data['remote'])) {
            $job->setProperty('jobLocationType', 'TELECOMMUTE');
            $job->setProperty(
                'applicantLocationRequirements',
                $this->typeFactory->create('Country')->setProperty('name', (string)($data['addressCountry'] ?? 'DE'))
            );
        }
        $salary = $this->salary($data);
        if ($salary !== null) {
            $job->setProperty('baseSalary', $salary);
        }
        $this->setRemaining($job, $data);
        return $job;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function event(array $data, ?TypeInterface $organization): TypeInterface
    {
        $event = $this->typeFactory->create('Event');
        $data['description'] = isset($data['description']) ? strip_tags((string)$data['description']) : null;
        $this->set($event, $data, ['name', 'description', 'image', 'startDate', 'endDate', 'url']);
        foreach (['eventStatus', 'eventAttendanceMode'] as $enumeration) {
            if (!empty($data[$enumeration])) {
                $event->setProperty($enumeration, 'https://schema.org/' . $data[$enumeration]);
            }
        }

        $mode = (string)($data['eventAttendanceMode'] ?? 'OfflineEventAttendanceMode');
        $locations = [];
        if ($mode !== 'OnlineEventAttendanceMode') {
            $place = $this->typeFactory->create('Place');
            $this->set($place, ['name' => $data['locationName'] ?? null], ['name']);
            $address = $this->address($data);
            if ($address !== null) {
                $place->setProperty('address', $address);
            }
            if ($address !== null || !empty($data['locationName'])) {
                $locations[] = $place;
            }
        }
        if ($mode !== 'OfflineEventAttendanceMode' && !empty($data['onlineUrl'])) {
            $locations[] = $this->typeFactory->create('VirtualLocation')->setProperty('url', (string)$data['onlineUrl']);
        }
        if ($locations !== []) {
            $event->setProperty('location', \count($locations) === 1 ? $locations[0] : $locations);
        }
        if ($organization !== null) {
            $event->setProperty('organizer', $organization);
        }
        if (isset($data['offerPrice']) || !empty($data['offerUrl'])) {
            $offer = $this->typeFactory->create('Offer');
            // A ticket link without a price must not claim the event is free.
            if (isset($data['offerPrice'])) {
                $offer->setProperty('price', (string)$data['offerPrice'])
                    ->setProperty('priceCurrency', (string)($data['offerCurrency'] ?? 'EUR'));
            }
            $this->set($offer, ['url' => $data['offerUrl'] ?? null], ['url']);
            $event->setProperty('offers', $offer);
        }
        if (!empty($data['performer'])) {
            $event->setProperty('performer', $this->typeFactory->create('Person')->setProperty('name', (string)$data['performer']));
        }
        $this->setRemaining($event, $data);
        return $event;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function article(string $type, array $data, ?TypeInterface $organization): TypeInterface
    {
        $article = $this->typeFactory->create($type);
        $data['description'] = isset($data['description']) ? strip_tags((string)$data['description']) : null;
        $this->set($article, $data, ['headline', 'description', 'image', 'datePublished', 'dateModified', 'url']);
        if (!empty($data['author'])) {
            $article->setProperty('author', $this->typeFactory->create('Person')->setProperty('name', (string)$data['author']));
        }
        if ($organization !== null) {
            $article->setProperty('publisher', $organization);
        }
        $this->setRemaining($article, $data);
        return $article;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function address(array $data): ?TypeInterface
    {
        $fields = ['streetAddress', 'postalCode', 'addressLocality', 'addressRegion', 'addressCountry'];
        if (array_filter(array_intersect_key($data, array_flip($fields))) === []) {
            return null;
        }
        $address = $this->typeFactory->create('PostalAddress');
        $this->set($address, $data, $fields);
        return $address;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function salary(array $data): ?TypeInterface
    {
        $min = $data['salaryMin'] ?? null;
        $max = $data['salaryMax'] ?? null;
        if ($min === null && $max === null) {
            return null;
        }
        $value = $this->typeFactory->create('QuantitativeValue')
            ->setProperty('unitText', (string)($data['salaryUnit'] ?? 'YEAR'));
        if ($min !== null && $max !== null && $min !== $max) {
            $value->setProperty('minValue', $min)->setProperty('maxValue', $max);
        } else {
            $value->setProperty('value', $min ?? $max);
        }
        return $this->typeFactory->create('MonetaryAmount')
            ->setProperty('currency', (string)($data['salaryCurrency'] ?? 'EUR'))
            ->setProperty('value', $value);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $properties
     */
    private function set(TypeInterface $type, array $data, array $properties): void
    {
        foreach ($properties as $property) {
            $value = $data[$property] ?? null;
            if ($value !== null && $value !== '' && $value !== []) {
                $type->setProperty($property, $value);
            }
        }
    }

    /**
     * Passes on every further field whose name is a property of the type, so a new simple field in a
     * Content Blocks record type needs no PHP. Nested values are built explicitly above.
     *
     * @param array<string, mixed> $data
     */
    private function setRemaining(TypeInterface $type, array $data): void
    {
        foreach (array_diff_key($data, array_flip(self::NESTED_FIELDS)) as $property => $value) {
            if ($value === null || $value === false || $value === '' || $value === []) {
                continue;
            }
            if (!$type->hasProperty($property) || $type->getProperty($property) !== null) {
                continue;
            }
            $isScalarList = \is_array($value) && array_filter($value, static fn($item): bool => !\is_scalar($item)) === [];
            if (\is_scalar($value) || $isScalarList) {
                $type->setProperty($property, $value);
            }
        }
    }

    /**
     * Google accepts a small set of HTML tags in descriptions and answers.
     */
    private static function basicHtml(string $html): string
    {
        return trim(strip_tags($html, '<p><br><ul><ol><li><strong><b><em><i><a><h2><h3><h4>'));
    }
}
