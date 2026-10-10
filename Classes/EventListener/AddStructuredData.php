<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\EventListener;

use Brotkrueml\Schema\Event\RenderAdditionalTypesEvent;
use TobiWollender\Schematic\Builder\PageContextFactory;
use TobiWollender\Schematic\Builder\SchemaGraphBuilder;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'schematic/add-structured-data')]
final readonly class AddStructuredData
{
    public function __construct(
        private PageContextFactory $pageContextFactory,
        private SchemaGraphBuilder $builder,
    ) {}

    public function __invoke(RenderAdditionalTypesEvent $event): void
    {
        $context = $this->pageContextFactory->fromFrontendRequest($event->getRequest());
        if ($context === null) {
            return;
        }
        $types = $this->builder->build($context)->types;
        if ($types !== []) {
            $event->addType(...$types);
        }
    }
}
