<?php

$ll = 'LLL:EXT:schematic/Resources/Private/Language/locallang_db.xlf:';
$tabs = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:';

$common = 'type, title, description';
$closing = ',--div--;' . $tabs . 'language, sys_language_uid, l10n_parent,--div--;' . $tabs . 'access, hidden';

$article = $common . ', image, author_name, date_published' . $closing;
$organization = $common . ', url, logo, image, --palette--;;contact, --palette--;' . $ll . 'palette.address;address, same_as';

return [
    'ctrl' => [
        'title' => $ll . 'tx_schematic_entity',
        'label' => 'title',
        'label_alt' => 'type',
        'label_alt_force' => true,
        'type' => 'type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'schematic-entity',
        ],
        'security' => [
            // Records attached to content pages live on those pages, not in a folder.
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'columns' => [
        'parentid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'type' => [
            'label' => $ll . 'type',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'type.choose', 'value' => ''],
                    ['label' => $ll . 'type.group.content', 'value' => '--div--'],
                    ['label' => $ll . 'type.Article', 'value' => 'Article'],
                    ['label' => $ll . 'type.BlogPosting', 'value' => 'BlogPosting'],
                    ['label' => $ll . 'type.NewsArticle', 'value' => 'NewsArticle'],
                    ['label' => $ll . 'type.Event', 'value' => 'Event'],
                    ['label' => $ll . 'type.JobPosting', 'value' => 'JobPosting'],
                    ['label' => $ll . 'type.group.organization', 'value' => '--div--'],
                    ['label' => $ll . 'type.Organization', 'value' => 'Organization'],
                    ['label' => $ll . 'type.LocalBusiness', 'value' => 'LocalBusiness'],
                ],
                'default' => '',
            ],
        ],
        'title' => [
            'label' => $ll . 'title',
            'description' => $ll . 'title.description',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
            ],
        ],
        'description' => [
            'label' => $ll . 'description',
            'description' => $ll . 'description.description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ],
        'url' => [
            'label' => $ll . 'url',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['url'],
            ],
        ],
        'image' => [
            'label' => $ll . 'image',
            'description' => $ll . 'image.description',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'logo' => [
            'label' => $ll . 'logo',
            'description' => $ll . 'logo.description',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'telephone' => [
            'label' => $ll . 'telephone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 64,
            ],
        ],
        'email' => [
            'label' => $ll . 'email',
            'config' => [
                'type' => 'email',
            ],
        ],
        'same_as' => [
            'label' => $ll . 'same_as',
            'description' => $ll . 'same_as.description',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'placeholder' => "https://www.linkedin.com/company/…\nhttps://www.instagram.com/…",
            ],
        ],
        'opening_hours' => [
            'label' => $ll . 'opening_hours',
            'description' => $ll . 'opening_hours.description',
            'config' => [
                'type' => 'text',
                'rows' => 3,
                'placeholder' => "Mo-Fr 08:00-17:00\nSa 09:00-12:00",
            ],
        ],
        'street' => [
            'label' => $ll . 'street',
            'config' => [
                'type' => 'input',
                'size' => 40,
            ],
        ],
        'postal_code' => [
            'label' => $ll . 'postal_code',
            'config' => [
                'type' => 'input',
                'size' => 10,
            ],
        ],
        'locality' => [
            'label' => $ll . 'locality',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'region' => [
            'label' => $ll . 'region',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'country' => [
            'label' => $ll . 'country',
            'description' => $ll . 'country.description',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'placeholder' => 'DE',
            ],
        ],
        'author_name' => [
            'label' => $ll . 'author_name',
            'config' => [
                'type' => 'input',
                'size' => 40,
            ],
        ],
        'date_published' => [
            'label' => $ll . 'date_published',
            'description' => $ll . 'date_published.description',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
            ],
        ],
        'date_posted' => [
            'label' => $ll . 'date_posted',
            'description' => $ll . 'date_posted.description',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
            ],
        ],
        'valid_through' => [
            'label' => $ll . 'valid_through',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
            ],
        ],
        'employment_type' => [
            'label' => $ll . 'employment_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectCheckBox',
                'items' => [
                    ['label' => $ll . 'employment_type.FULL_TIME', 'value' => 'FULL_TIME'],
                    ['label' => $ll . 'employment_type.PART_TIME', 'value' => 'PART_TIME'],
                    ['label' => $ll . 'employment_type.CONTRACTOR', 'value' => 'CONTRACTOR'],
                    ['label' => $ll . 'employment_type.TEMPORARY', 'value' => 'TEMPORARY'],
                    ['label' => $ll . 'employment_type.INTERN', 'value' => 'INTERN'],
                    ['label' => $ll . 'employment_type.VOLUNTEER', 'value' => 'VOLUNTEER'],
                    ['label' => $ll . 'employment_type.OTHER', 'value' => 'OTHER'],
                ],
            ],
        ],
        'remote' => [
            'label' => $ll . 'remote',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
            ],
        ],
        'salary_min' => [
            'label' => $ll . 'salary_min',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'size' => 10,
            ],
        ],
        'salary_max' => [
            'label' => $ll . 'salary_max',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'size' => 10,
            ],
        ],
        'salary_currency' => [
            'label' => $ll . 'currency',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'max' => 3,
                'default' => 'EUR',
            ],
        ],
        'salary_unit' => [
            'label' => $ll . 'salary_unit',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'salary_unit.HOUR', 'value' => 'HOUR'],
                    ['label' => $ll . 'salary_unit.MONTH', 'value' => 'MONTH'],
                    ['label' => $ll . 'salary_unit.YEAR', 'value' => 'YEAR'],
                ],
                'default' => 'YEAR',
            ],
        ],
        'start_date' => [
            'label' => $ll . 'start_date',
            'config' => [
                'type' => 'datetime',
            ],
        ],
        'end_date' => [
            'label' => $ll . 'end_date',
            'config' => [
                'type' => 'datetime',
            ],
        ],
        'event_status' => [
            'label' => $ll . 'event_status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'event_status.EventScheduled', 'value' => 'EventScheduled'],
                    ['label' => $ll . 'event_status.EventPostponed', 'value' => 'EventPostponed'],
                    ['label' => $ll . 'event_status.EventRescheduled', 'value' => 'EventRescheduled'],
                    ['label' => $ll . 'event_status.EventCancelled', 'value' => 'EventCancelled'],
                ],
                'default' => 'EventScheduled',
            ],
        ],
        'attendance_mode' => [
            'label' => $ll . 'attendance_mode',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'attendance_mode.OfflineEventAttendanceMode', 'value' => 'OfflineEventAttendanceMode'],
                    ['label' => $ll . 'attendance_mode.OnlineEventAttendanceMode', 'value' => 'OnlineEventAttendanceMode'],
                    ['label' => $ll . 'attendance_mode.MixedEventAttendanceMode', 'value' => 'MixedEventAttendanceMode'],
                ],
                'default' => 'OfflineEventAttendanceMode',
            ],
        ],
        'location_name' => [
            'label' => $ll . 'location_name',
            'displayCond' => 'FIELD:attendance_mode:!=:OnlineEventAttendanceMode',
            'config' => [
                'type' => 'input',
                'size' => 40,
            ],
        ],
        'online_url' => [
            'label' => $ll . 'online_url',
            'displayCond' => 'FIELD:attendance_mode:!=:OfflineEventAttendanceMode',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['url'],
            ],
        ],
        'offer_price' => [
            'label' => $ll . 'offer_price',
            'description' => $ll . 'offer_price.description',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'size' => 10,
            ],
        ],
        'offer_currency' => [
            'label' => $ll . 'currency',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'max' => 3,
                'default' => 'EUR',
            ],
        ],
        'offer_url' => [
            'label' => $ll . 'offer_url',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['url'],
            ],
        ],
    ],
    'palettes' => [
        'contact' => [
            'showitem' => 'telephone, email',
        ],
        'address' => [
            'showitem' => 'street, --linebreak--, postal_code, locality, --linebreak--, region, country',
        ],
        'jobDates' => [
            'showitem' => 'date_posted, valid_through',
        ],
        'salary' => [
            'label' => $ll . 'palette.salary',
            'showitem' => 'salary_min, salary_max, --linebreak--, salary_currency, salary_unit',
        ],
        'eventDates' => [
            'showitem' => 'start_date, end_date',
        ],
        'offer' => [
            'label' => $ll . 'palette.offer',
            'showitem' => 'offer_price, offer_currency, --linebreak--, offer_url',
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'type' . $closing,
        ],
        'Article' => [
            'showitem' => $article,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.headline'],
            ],
        ],
        'BlogPosting' => [
            'showitem' => $article,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.headline'],
            ],
        ],
        'NewsArticle' => [
            'showitem' => $article,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.headline'],
            ],
        ],
        'Event' => [
            'showitem' => $common . ', image, --palette--;;eventDates, event_status, attendance_mode, location_name, --palette--;' . $ll . 'palette.address;address, online_url, --palette--;;offer' . $closing,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.event'],
            ],
        ],
        'JobPosting' => [
            'showitem' => $common . ', --palette--;;jobDates, employment_type, --palette--;' . $ll . 'palette.jobLocation;address, remote, --palette--;;salary' . $closing,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.job'],
                'description' => [
                    'config' => [
                        'enableRichtext' => true,
                    ],
                ],
            ],
        ],
        'Organization' => [
            'showitem' => $organization . $closing,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.name', 'description' => '', 'config' => ['required' => true]],
                'description' => ['description' => ''],
            ],
        ],
        'LocalBusiness' => [
            'showitem' => $organization . ', opening_hours' . $closing,
            'columnsOverrides' => [
                'title' => ['label' => $ll . 'title.name', 'description' => '', 'config' => ['required' => true]],
                'description' => ['description' => ''],
            ],
        ],
    ],
];
