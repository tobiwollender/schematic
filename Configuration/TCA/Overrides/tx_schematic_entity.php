<?php

defined('TYPO3') or die();

// The record types themselves are Content Blocks in ContentBlocks/RecordTypes/.

// Content Blocks enables versioning only when EXT:workspaces is installed, but as an inline child of pages
// the core treats the table as workspace aware anyway and logs a deprecation otherwise.
$GLOBALS['TCA']['tx_schematic_entity']['ctrl']['versioningWS'] = true;

// Content Blocks names a shared table after one of its types; the table holds all of them.
$GLOBALS['TCA']['tx_schematic_entity']['ctrl']['title'] = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:tx_schematic_entity';

// A new record starts without a type, so editors make a conscious choice instead of inheriting the first one.
$GLOBALS['TCA']['tx_schematic_entity']['columns']['type']['label'] = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:type';
$GLOBALS['TCA']['tx_schematic_entity']['columns']['type']['onChange'] = 'reload';
$GLOBALS['TCA']['tx_schematic_entity']['columns']['type']['config']['default'] = '';
array_unshift(
    $GLOBALS['TCA']['tx_schematic_entity']['columns']['type']['config']['items'],
    ['label' => 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:type.choose', 'value' => '']
);
$GLOBALS['TCA']['tx_schematic_entity']['types']['0'] = [
    'showitem' => 'type',
];

// Content Blocks adds the types in load order; editors expect a stable, grouped order.
$order = ['', 'Article', 'BlogPosting', 'NewsArticle', 'Event', 'JobPosting', 'Organization', 'LocalBusiness'];
usort(
    $GLOBALS['TCA']['tx_schematic_entity']['columns']['type']['config']['items'],
    static fn(array $a, array $b): int => (array_search($a['value'], $order, true) ?: 99) <=> (array_search($b['value'], $order, true) ?: 99)
);
