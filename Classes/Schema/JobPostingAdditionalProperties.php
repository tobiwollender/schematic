<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Schema;

use Brotkrueml\Schema\Core\AdditionalPropertiesInterface;

/**
 * Google recommends directApply, but schema.org still lists it as pending, so EXT:schema does not know it yet.
 */
final class JobPostingAdditionalProperties implements AdditionalPropertiesInterface
{
    public function getType(): string
    {
        return 'JobPosting';
    }

    public function getAdditionalProperties(): array
    {
        return ['directApply'];
    }
}
