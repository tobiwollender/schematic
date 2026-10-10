<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Configuration;

use TobiWollender\Schematic\Model\PageSchemaConfig;

interface PageSchemaConfigProviderInterface
{
    /**
     * @param array<string, mixed> $page raw page record, needs at least "doktype"
     */
    public function forPage(array $page): ?PageSchemaConfig;
}
