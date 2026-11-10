<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Schema;

use Brotkrueml\Schema\Core\AdditionalPropertiesInterface;

/**
 * Properties Google uses for job postings that EXT:schema does not know: directApply is still pending at
 * schema.org, applicantLocationRequirements is required for remote jobs.
 */
final class JobPostingAdditionalProperties implements AdditionalPropertiesInterface
{
    public function getType(): string
    {
        return 'JobPosting';
    }

    public function getAdditionalProperties(): array
    {
        return ['applicantLocationRequirements', 'directApply'];
    }
}
