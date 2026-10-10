<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Configuration;

use TobiWollender\Schematic\Model\PageSchemaConfig;

final class NullPageSchemaConfigProvider implements PageSchemaConfigProviderInterface
{
    public function forPage(array $page): ?PageSchemaConfig
    {
        return null;
    }
}
