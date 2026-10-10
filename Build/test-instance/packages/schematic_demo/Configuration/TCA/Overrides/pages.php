<?php

defined('TYPO3') or die();

// Convention: the tabs of a custom page type sit right before "Strukturierte Daten".
// Content Blocks always puts them after "Allgemein", so they are moved here.
(static function (): void {
    $type = '1760100001';
    $ownTabs = '/ContentBlocks/PageTypes/job/';
    $items = array_map(trim(...), explode(',', (string)($GLOBALS['TCA']['pages']['types'][$type]['showitem'] ?? '')));
    $isTab = static fn(string $item): bool => str_starts_with($item, '--div--');

    $start = null;
    foreach ($items as $index => $item) {
        if ($isTab($item) && str_contains($item, $ownTabs)) {
            $start = $index;
            break;
        }
    }
    if ($start === null) {
        return;
    }
    $end = $start + 1;
    while (isset($items[$end]) && (!$isTab($items[$end]) || str_contains($items[$end], $ownTabs))) {
        $end++;
    }
    $segment = array_splice($items, $start, $end - $start);

    $target = count($items);
    foreach ($items as $index => $item) {
        if ($isTab($item) && str_contains($item, 'EXT:schematic/') && str_contains($item, ':pages.tab')) {
            $target = $index;
            break;
        }
    }
    array_splice($items, $target, 0, $segment);
    $GLOBALS['TCA']['pages']['types'][$type]['showitem'] = implode(',', $items);
})();
