<?php

use TobiWollender\Schematic\Form\Element\PreviewElement;
use TobiWollender\Schematic\Form\FormDataProvider\PageTypeSchemaDefaults;
use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue;
use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1760100100] = [
    'nodeName' => 'schematicPreview',
    'priority' => 40,
    'class' => PreviewElement::class,
];

// Runs before the record type is evaluated so a type preset by the page type drives the form.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][PageTypeSchemaDefaults::class] = [
    'depends' => [
        DatabaseRowDefaultValues::class,
    ],
    'before' => [
        DatabaseRecordTypeValue::class,
    ],
];
