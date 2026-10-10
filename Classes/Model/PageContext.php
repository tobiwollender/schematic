<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Model;

use TYPO3\CMS\Core\Site\Entity\Site;

final readonly class PageContext
{
    /**
     * @param array<string, mixed> $page page record, overlaid for the language in the frontend
     */
    public function __construct(
        public int $pageUid,
        public array $page,
        public int $languageId,
        public Site $site,
        public string $baseUrl,
        public bool $frontend,
    ) {}

    public function isRootPage(): bool
    {
        return $this->pageUid === $this->site->getRootPageId();
    }
}
