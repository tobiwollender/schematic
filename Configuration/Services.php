<?php

declare(strict_types=1);

use TobiWollender\Schematic\Configuration\PageSchemaConfigProviderInterface;
use TobiWollender\Schematic\ContentBlocks\ContentBlocksPageSchemaConfigProvider;
use TobiWollender\Schematic\Form\Element\PreviewElement;
use TobiWollender\Schematic\Form\FormDataProvider\PageTypeSchemaDefaults;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('TobiWollender\\Schematic\\', __DIR__ . '/../Classes/')
        ->exclude([__DIR__ . '/../Classes/Model/']);

    // FormEngine creates nodes and data providers via makeInstance(), which needs public services.
    $services->set(PreviewElement::class)->public();
    $services->set(PageTypeSchemaDefaults::class)->public();

    $services->alias(PageSchemaConfigProviderInterface::class, ContentBlocksPageSchemaConfigProvider::class);
};
