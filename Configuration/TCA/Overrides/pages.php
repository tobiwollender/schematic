<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
    '--div--;' . $ll . 'pages.tab, tx_schematic_organization, tx_schematic_entity, tx_schematic_preview',
);
