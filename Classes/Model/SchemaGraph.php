<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Model;

use Brotkrueml\Schema\Core\Model\TypeInterface;

final readonly class SchemaGraph
{
    /**
     * @param list<TypeInterface> $types
     * @param array<mixed>|null $customJsonLd replaces the page's own type and FAQ when set
     */
    public function __construct(
        public array $types,
        public ?PageSchemaConfig $config,
        public ?array $customJsonLd = null,
    ) {}
}
