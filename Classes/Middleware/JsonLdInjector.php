<?php

declare(strict_types=1);

namespace RatingStar\Seal\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RatingStar\Seal\Configuration\SealSettings;
use RatingStar\Seal\Service\JsonLdProvider;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Injiziert das serverseitige RatingStar-JSON-LD (LocalBusiness + AggregateRating)
 * in den <head> jeder Frontend-HTML-Seite – wenn per Site Setting aktiviert und ein
 * Embed-Key gesetzt ist. Ein-Block-Regel: überspringt, wenn die Seite bereits einen
 * data-rs-jsonld-Block trägt (z. B. via Content-Element/<rs:jsonld>); und seal.js
 * unterlässt seine Client-Injektion, sobald dieser Marker im DOM ist.
 */
final class JsonLdInjector implements MiddlewareInterface
{
    public function __construct(
        private readonly JsonLdProvider $jsonLdProvider,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return $response;
        }

        $settings = SealSettings::fromSite($site);
        if (!$settings->jsonLdEnabled || $settings->key === '') {
            return $response;
        }

        // Only act on HTML documents.
        $contentType = $response->getHeaderLine('Content-Type');
        if ($contentType !== '' && !str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = (string)$response->getBody();

        // Need a closing head, and no JSON-LD block yet (single-block rule).
        if (!str_contains($html, '</head>') || str_contains($html, 'data-rs-jsonld')) {
            return $response;
        }

        $json = $this->jsonLdProvider->fetch($settings->jsonLdUrl(), $settings->key);
        if ($json === null) {
            return $response;
        }

        $script = '<script type="application/ld+json" data-rs-jsonld>' . $json . '</script>';
        $html = str_replace('</head>', $script . '</head>', $html);

        return $response
            ->withBody($this->streamFactory->createStream($html))
            ->withoutHeader('Content-Length');
    }
}
