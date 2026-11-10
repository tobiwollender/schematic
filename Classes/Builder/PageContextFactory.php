<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Builder;

use TobiWollender\Schematic\Model\PageContext;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class PageContextFactory
{
    public function __construct(
        private SiteFinder $siteFinder,
        private Context $context,
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

    /**
     * Builds the context the frontend would see for this page in the given language, so the preview of a
     * translated page shows its translated values and URLs.
     */
    public function forBackendPreview(int $pageUid, int $languageId, ServerRequestInterface $request): ?PageContext
    {
        $page = BackendUtility::getRecordWSOL('pages', $pageUid);
        if ($page === null) {
            return null;
        }
        try {
            $site = $this->siteFinder->getSiteByPageId($pageUid);
        } catch (SiteNotFoundException) {
            return null;
        }
        if ($languageId > 0) {
            $page = GeneralUtility::makeInstance(PageRepository::class, $this->context)
                ->getPageOverlay($page, new LanguageAspect($languageId, $languageId, LanguageAspect::OVERLAYS_MIXED));
        }
        return new PageContext($pageUid, $page, $languageId, $site, $this->baseUrl($site, $request), false);
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
