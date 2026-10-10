<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use TobiWollender\Schematic\Model\PageContext;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

final readonly class PageContextFactory
{
    public function __construct(
        private SiteFinder $siteFinder,
    ) {}

    public function fromFrontendRequest(ServerRequestInterface $request): ?PageContext
    {
        $pageInformation = $request->getAttribute('frontend.page.information');
        $site = $request->getAttribute('site');
        if ($pageInformation === null || !$site instanceof Site) {
            return null;
        }
        return new PageContext(
            $pageInformation->getId(),
            $pageInformation->getPageRecord(),
            $request->getAttribute('language')?->getLanguageId() ?? 0,
            $site,
            $this->baseUrl($site, $request),
            true,
        );
    }

    public function forBackendPreview(int $pageUid, ServerRequestInterface $request): ?PageContext
    {
        $page = BackendUtility::getRecord('pages', $pageUid);
        if ($page === null) {
            return null;
        }
        try {
            $site = $this->siteFinder->getSiteByPageId($pageUid);
        } catch (SiteNotFoundException) {
            return null;
        }
        return new PageContext($pageUid, $page, 0, $site, $this->baseUrl($site, $request), false);
    }

    /**
     * Sites with a relative base ("/") take scheme and host from the current request.
     */
    private function baseUrl(Site $site, ServerRequestInterface $request): string
    {
        $base = $site->getBase();
        if ($base->getHost() !== '') {
            return rtrim((string)$base, '/') . '/';
        }
        $normalizedParams = $request->getAttribute('normalizedParams') ?? NormalizedParams::createFromRequest($request);
        return rtrim($normalizedParams->getSiteUrl(), '/') . '/' . ltrim($base->getPath(), '/');
    }
}
