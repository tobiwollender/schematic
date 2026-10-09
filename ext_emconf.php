<?php

// Only read in classic (non-Composer) mode; Composer installs use composer.json.
$EM_CONF[$_EXTKEY] = [
    'title' => 'Structured data for editors',
    'description' => 'Editor-friendly schema.org structured data, built on EXT:schema',
    'category' => 'fe',
    'state' => 'alpha',
    'author' => 'Tobi Wollender',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
            'schema' => '4.4.0-4.99.99',
        ],
    ],
];
