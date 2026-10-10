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
     */
    public function __construct(
        public string $type,
        public bool $locked,
        public array $properties,
        public string $source,
    ) {}
}
