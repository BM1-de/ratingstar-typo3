<?php

declare(strict_types=1);

namespace RatingStar\Seal\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * Holt das serverseitige JSON-LD (LocalBusiness + AggregateRating) einer Filiale
 * vom key-basierten RatingStar-Endpunkt und cached es. Die App baut das komplette
 * Schema selbst (inkl. Tarif-Gate) – diese Klasse ruft es nur ab und puffert es.
 */
final class JsonLdProvider
{
    private const CACHE_ID = 'ratingstar_seal';
    private const TTL_OK = 21600;   // 6 h – spiegelt das Cache-Control des Endpunkts
    private const TTL_FAIL = 900;   // 15 min – negativer Cache, damit Fehler nicht hämmern

    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly CacheManager $cacheManager,
    ) {
    }

    /**
     * Liefert das rohe JSON-LD-Objekt (als JSON-String) für die gegebene URL,
     * oder null, wenn der Key leer ist, der Abruf scheitert oder die Antwort
     * unbrauchbar ist. Ergebnis ~6 h gecached (Fehler 15 min).
     */
    public function fetch(string $jsonLdUrl, string $key): ?string
    {
        if ($key === '') {
            return null;
        }

        $cache = $this->cacheManager->getCache(self::CACHE_ID);
        $cacheId = 'jsonld_' . md5($jsonLdUrl);

        $cached = $cache->get($cacheId);
        if (is_string($cached)) {
            return $cached !== '' ? $cached : null;
        }

        $json = $this->request($jsonLdUrl);
        // Negatives Ergebnis als leeren String cachen (kürzere TTL).
        $cache->set($cacheId, (string)$json, [], $json !== null ? self::TTL_OK : self::TTL_FAIL);

        return $json;
    }

    private function request(string $url): ?string
    {
        try {
            $response = $this->requestFactory->request($url, 'GET', [
                'headers' => ['Accept' => 'application/ld+json, application/json'],
                'timeout' => 5,
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $body = trim((string)$response->getBody());

            // Plausibilität: muss ein schema.org-Objekt sein (gegen Fehlerseiten).
            if ($body === '' || !str_contains($body, '"@type"') || !str_contains($body, 'schema.org')) {
                return null;
            }

            return $body;
        } catch (\Throwable) {
            return null;
        }
    }
}
