<?php

$ll = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:';
$tabs = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_schematic_faq_item',
        'label' => 'question',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'versioningWS' => true,
        'sortby' => 'sorting',
        'hideTable' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'schematic-faq',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'parentid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'question' => [
            'label' => $ll . 'question',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'max' => 512,
                'required' => true,
            ],
        ],
        'answer' => [
            'label' => $ll . 'answer',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'required' => true,
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'question, answer,--div--;' . $tabs . 'access, hidden',
        ],
    ],
];
