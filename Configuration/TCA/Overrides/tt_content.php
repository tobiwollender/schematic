<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$ll = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:';

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'label' => $ll . 'tt_content.CType.schematic_faq',
        'description' => $ll . 'tt_content.CType.schematic_faq.description',
        'value' => 'schematic_faq',
        'icon' => 'schematic-faq',
        'group' => 'default',
    ],
);

ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'tx_schematic_faq_items' => [
        'label' => $ll . 'tt_content.tx_schematic_faq_items',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_schematic_faq_item',
            'foreign_field' => 'parentid',
            'foreign_sortby' => 'sorting',
            'appearance' => [
                'collapseAll' => true,
                'expandSingle' => true,
                'useSortable' => true,
                'showPossibleLocalizationRecords' => true,
            ],
        ],
    ],
]);

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['schematic_faq'] = 'schematic-faq';
$GLOBALS['TCA']['tt_content']['types']['schematic_faq'] = [
    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            tx_schematic_faq_items,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access',
];
