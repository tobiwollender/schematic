<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Model;

/**
 * schema.org settings an integrator attached to a page type.
 */
final readonly class PageSchemaConfig
{
    /**
     * @param array<string, string> $properties schema.org property => pages column or token such as "@organization"
     * @param array<string, array<string, mixed>> $valueMaps schema.org property => project value => schema.org value
     */
    public function __construct(
        public string $type,
        public bool $locked,
        public array $properties,
        public string $source,
        public array $valueMaps = [],
    ) {}
}
