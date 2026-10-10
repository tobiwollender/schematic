<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Model;

use Brotkrueml\Schema\Core\Model\TypeInterface;

final readonly class SchemaGraph
{
    /**
     * @param list<TypeInterface> $types
     */
    public function __construct(
        public array $types,
        public ?PageSchemaConfig $config,
    ) {}
}
