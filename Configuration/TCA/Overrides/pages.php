<?php

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

$ll = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:';

ExtensionManagementUtility::addTCAcolumns('pages', [
    'tx_schematic_entity' => [
        'label' => $ll . 'pages.tx_schematic_entity',
        'description' => $ll . 'pages.tx_schematic_entity.description',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_schematic_entity',
            'foreign_field' => 'parentid',
            'maxitems' => 1,
            'appearance' => [
                'collapseAll' => false,
                'newRecordLinkTitle' => $ll . 'pages.tx_schematic_entity.new',
                'showPossibleLocalizationRecords' => true,
            ],
        ],
    ],
    'tx_schematic_organization' => [
        'label' => $ll . 'pages.tx_schematic_organization',
        'description' => $ll . 'pages.tx_schematic_organization.description',
        'displayCond' => 'FIELD:is_siteroot:REQ:true',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'tx_schematic_entity',
            'foreign_table_where' => 'AND {#tx_schematic_entity}.{#type} IN (\'Organization\', \'LocalBusiness\') AND {#tx_schematic_entity}.{#sys_language_uid} IN (0, -1)',
            'items' => [
                ['label' => '', 'value' => 0],
            ],
            'default' => 0,
        ],
    ],
    'tx_schematic_json' => [
        // Exclude field: only admins and groups explicitly granted it may enter raw JSON-LD.
        'exclude' => true,
        'label' => $ll . 'pages.tx_schematic_json',
        'description' => $ll . 'pages.tx_schematic_json.description',
        'config' => [
            'type' => 'json',
            'enableCodeEditor' => true,
        ],
    ],
    'tx_schematic_preview' => [
        'label' => $ll . 'pages.tx_schematic_preview',
        'config' => [
            'type' => 'none',
            'renderType' => 'schematicPreview',
        ],
    ],
]);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;' . $ll . 'pages.tab, tx_schematic_organization, tx_schematic_entity, tx_schematic_json, tx_schematic_preview',
);

(static function (): void {
    try {
        $hide = (bool)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('schematic', 'hideWebPageTypeField');
    } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
        $hide = true;
    }
    if (!$hide) {
        return;
    }

    // schematic derives the type from the structured data record, so the web page type of EXT:schema
    // only confuses editors. The column stays, existing values keep working.
    $field = 'tx_schema_webpagetype';
    $palette = 'tx_schema_structureddata';
    $withoutField = static fn(string $showitem, string $name): string => implode(',', array_filter(
        explode(',', $showitem),
        static fn(string $item): bool => trim(explode(';', $item)[0]) !== $name
            && !(str_starts_with(trim($item), '--palette--') && trim(explode(';', $item)[2] ?? '') === $name)
    ));

    if (isset($GLOBALS['TCA']['pages']['palettes'][$palette]['showitem'])) {
        $remaining = $withoutField($GLOBALS['TCA']['pages']['palettes'][$palette]['showitem'], $field);
        $GLOBALS['TCA']['pages']['palettes'][$palette]['showitem'] = $remaining;
        // An empty palette would still render its headline.
        if (trim(str_replace('--linebreak--', '', $remaining), " ,\t\n") === '') {
            foreach ($GLOBALS['TCA']['pages']['types'] as $type => $configuration) {
                $GLOBALS['TCA']['pages']['types'][$type]['showitem'] = $withoutField((string)($configuration['showitem'] ?? ''), $palette);
            }
        }
    }
    foreach ($GLOBALS['TCA']['pages']['types'] as $type => $configuration) {
        $GLOBALS['TCA']['pages']['types'][$type]['showitem'] = $withoutField((string)($configuration['showitem'] ?? ''), $field);
    }
})();
