<?php

// Local DDEV test instance: accept every host so the site works under ddev.site and 127.0.0.1:<port>.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = '*';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = true;
$GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = true;
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'smtp';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'] = 'localhost:1025';
$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor'] = 'ImageMagick';
$GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_path'] = '/usr/bin/';
// German backend: TYPO3 13 and 14 read the active languages from different keys.
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lang']['availableLanguages'] = ['de'];
$GLOBALS['TYPO3_CONF_VARS']['LANG']['availableLocales'] = ['de'];
