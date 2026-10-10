<?php

declare(strict_types=1);

namespace TobiWollender\Schematic\Frontend;

use TobiWollender\Schematic\Builder\CustomJsonLd;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Outputs custom JSON-LD as its own script element. It bypasses EXT:schema on purpose, because special
 * cases may use types or properties its vocabulary does not know.
 */
final readonly class CustomJsonLdMiddleware implements MiddlewareInterface
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $page = $request->getAttribute('frontend.page.information')?->getPageRecord() ?? [];
        $data = CustomJsonLd::fromPage($page);
        if ($data === null || !str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            return $response;
        }

        $response->getBody()->rewind();
        $contents = str_ireplace(
            '</body>',
            '<script type="application/ld+json">' . CustomJsonLd::encode($data) . '</script>' . "\n" . '</body>',
            $response->getBody()->getContents()
        );
        return $response->withBody($this->streamFactory->createStream($contents));
    }
}
