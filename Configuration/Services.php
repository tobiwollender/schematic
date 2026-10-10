<?php

declare(strict_types=1);

use TobiWollender\Schematic\Configuration\NullPageSchemaConfigProvider;
use TobiWollender\Schematic\Configuration\PageSchemaConfigProviderInterface;
use TobiWollender\Schematic\ContentBlocks\ContentBlocksPageSchemaConfigProvider;
use TobiWollender\Schematic\Form\Element\PreviewElement;
use TobiWollender\Schematic\Form\FormDataProvider\PageTypeSchemaDefaults;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('TobiWollender\\Schematic\\', __DIR__ . '/../Classes/')
        ->exclude([
            __DIR__ . '/../Classes/ContentBlocks/',
            __DIR__ . '/../Classes/Model/',
        ]);

    // FormEngine creates nodes and data providers via makeInstance(), which needs public services.
    $services->set(PreviewElement::class)->public();
    $services->set(PageTypeSchemaDefaults::class)->public();

    // Content Blocks is optional, so its internal services may only be referenced when it is installed.
    if (class_exists(ContentBlockRegistry::class)) {
        $services->set(ContentBlocksPageSchemaConfigProvider::class);
        $services->alias(PageSchemaConfigProviderInterface::class, ContentBlocksPageSchemaConfigProvider::class);
    } else {
        $services->alias(PageSchemaConfigProviderInterface::class, NullPageSchemaConfigProvider::class);
    }
};
