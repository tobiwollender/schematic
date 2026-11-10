<?php

use TobiWollender\Schematic\Frontend\CustomJsonLdMiddleware;

return [
    'frontend' => [
        'tobiwollender/schematic/custom-json-ld' => [
            'target' => CustomJsonLdMiddleware::class,
            'before' => [
                'typo3/cms-core/cache-timeout',
            ],
            'after' => [
                'typo3/cms-adminpanel/renderer',
                'typo3/cms-frontend/content-length-headers',
            ],
        ],
    ],
];
