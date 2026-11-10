<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Form\Element;

use Brotkrueml\Schema\JsonLd\Renderer;
use TobiWollender\Schematic\Builder\CustomJsonLd;
use TobiWollender\Schematic\Builder\PageContextFactory;
use TobiWollender\Schematic\Builder\SchemaGraphBuilder;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Shows the JSON-LD the frontend will output for the saved page, so editors see the effect of their input.
 */
final class PreviewElement extends AbstractFormElement
{
    private const LL = 'LLL:EXT:schematic/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        private readonly PageContextFactory $pageContextFactory,
        private readonly SchemaGraphBuilder $builder,
    ) {}

    public function render(): array
    {
        $result = $this->initializeResultArray();
        $row = $this->data['databaseRow'];

        if (!MathUtility::canBeInterpretedAsInteger($row['uid'] ?? '')) {
            $result['html'] = $this->callout('info', $this->label('preview.newPage'));
            return $result;
        }
        $languageId = (int)(\is_array($row['sys_language_uid'] ?? null) ? ($row['sys_language_uid'][0] ?? 0) : ($row['sys_language_uid'] ?? 0));
        $l10nParent = $row['l10n_parent'] ?? 0;
        $l10nParent = (int)(\is_array($l10nParent) ? ($l10nParent[0]['uid'] ?? $l10nParent[0] ?? 0) : $l10nParent);
        // In a workspace the form edits the version; structured data hangs on the live uid.
        $liveUid = (int)($row['t3ver_oid'] ?? 0) ?: (int)$row['uid'];
        $pageUid = $languageId > 0 && $l10nParent > 0 ? $l10nParent : $liveUid;

        $context = $this->pageContextFactory->forBackendPreview($pageUid, $languageId, $GLOBALS['TYPO3_REQUEST']);
        if ($context === null) {
            $result['html'] = $this->callout('warning', $this->label('preview.noSite'));
            return $result;
        }
        $graph = $this->builder->build($context);

        $html = '';
        if ($graph->config !== null) {
            $message = sprintf($this->label($graph->config->locked ? 'preview.locked' : 'preview.preset'), $graph->config->type);
            $html .= $this->callout('info', $message . ' <code>' . htmlspecialchars($graph->config->source) . '</code>', false);
        }
        if ($graph->types === [] && $graph->customJsonLd === null) {
            $result['html'] = $html . $this->callout('notice', $this->label('preview.empty'));
            return $result;
        }

        $html .= '<p class="text-body-secondary">' . htmlspecialchars($this->label('preview.intro')) . '</p>';
        if ($graph->customJsonLd !== null) {
            $html .= $this->callout('warning', $this->label('preview.custom'));
            $html .= $this->codeBlock(CustomJsonLd::encode($graph->customJsonLd, true));
        }
        if ($graph->types !== []) {
            $renderer = new Renderer();
            $renderer->addType(...$graph->types);
            $html .= $this->codeBlock(CustomJsonLd::encode(json_decode($renderer->render(), true, 512, \JSON_THROW_ON_ERROR), true));
        }
        $pageUrl = rawurlencode($this->pageUrl($graph->types));
        $html .= '<p class="mt-2">'
            . '<a href="https://validator.schema.org/#url=' . $pageUrl . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($this->label('preview.validator')) . '</a>'
            . ' · <a href="https://search.google.com/test/rich-results?url=' . $pageUrl . '" target="_blank" rel="noopener noreferrer">'
            . htmlspecialchars($this->label('preview.richResults')) . '</a></p>';

        $result['html'] = '<div class="formengine-field-item t3js-formengine-field-item">' . $html . '</div>';
        return $result;
    }

    /**
     * @param list<\Brotkrueml\Schema\Core\Model\TypeInterface> $types
     */
    private function pageUrl(array $types): string
    {
        foreach ($types as $type) {
            if ($type->hasProperty('url') && \is_string($type->getProperty('url'))) {
                return $type->getProperty('url');
            }
        }
        return '';
    }

    private function codeBlock(string $json): string
    {
        $rows = min(40, substr_count($json, "\n") + 2);
        return '<textarea class="form-control font-monospace mb-2" readonly rows="' . $rows . '" style="font-size: 12px;">'
            . htmlspecialchars($json) . '</textarea>';
    }

    private function callout(string $severity, string $message, bool $escape = true): string
    {
        return '<div class="callout callout-' . $severity . '"><div class="callout-body">'
            . ($escape ? htmlspecialchars($message) : $message) . '</div></div>';
    }

    private function label(string $key): string
    {
        return $this->getLanguageService()->sL(self::LL . $key);
    }
}
