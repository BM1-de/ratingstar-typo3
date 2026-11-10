<?php

declare(strict_types=1);

namespace RatingStar\Seal\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * Holt das serverseitige JSON-LD (LocalBusiness + AggregateRating) einer Filiale
 * vom key-basierten RatingStar-Endpunkt und cached es. Die App baut das komplette
 * Schema selbst (inkl. Tarif-Gate) – diese Klasse ruft es ab, prüft es, kodiert
 * es für die Ausgabe in einem <script>-Block neu und puffert das Ergebnis.
 */
final class JsonLdProvider
{
    private const CACHE_ID = 'ratingstar_seal';
    private const TTL_OK = 21600;   // 6 h – spiegelt das Cache-Control des Endpunkts
    private const TTL_FAIL = 900;   // 15 min – negativer Cache, damit Fehler nicht hämmern

    /**
     * Präfix der Cache-Einträge. "v2": Bis 1.3.0 lag unter "jsonld_" die
     * Remote-Antwort als Rohtext – diese Einträge dürfen nicht mehr ausgeliefert
     * werden und laufen ungenutzt aus.
     */
    private const CACHE_PREFIX = 'jsonld_v2_';

    /**
     * JSON_HEX_* schreibt <, >, &, ' und " als \uXXXX. Das JSON bleibt für
     * schema.org-Auswerter gültig, aber ein Firmenname oder Bewertungstext mit
     * "</script>" kann den umgebenden <script>-Block nicht mehr beenden.
     */
    private const ENCODE_FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly CacheManager $cacheManager,
    ) {
    }

    /**
     * Liefert das JSON-LD-Objekt der gegebenen URL als JSON-String, der gefahrlos
     * in einen <script type="application/ld+json">-Block gesetzt werden kann –
     * oder null, wenn der Key leer ist, der Abruf scheitert oder die Antwort kein
     * LocalBusiness-Objekt ist. Ergebnis ~6 h gecached (Fehler 15 min).
     */
    public function fetch(string $jsonLdUrl, string $key): ?string
    {
        if ($key === '') {
            return null;
        }

        $cache = $this->cacheManager->getCache(self::CACHE_ID);
        $cacheId = self::CACHE_PREFIX . md5($jsonLdUrl);

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

            return $this->reencode((string)$response->getBody());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Die Remote-Antwort wird nie als Rohtext weitergereicht: dekodieren, auf ein
     * LocalBusiness-Objekt prüfen und neu kodieren. Alles andere (Fehlerseite,
     * kaputtes JSON, fremder Typ) ergibt null, also keine Ausgabe.
     *
     * @throws \JsonException
     */
    private function reencode(string $body): ?string
    {
        // Als Objekt dekodieren, damit leere Objekte ({}) nicht zu Listen ([]) werden.
        $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

        if (!$data instanceof \stdClass || ($data->{'@type'} ?? null) !== 'LocalBusiness') {
            return null;
        }

        return json_encode($data, self::ENCODE_FLAGS | JSON_THROW_ON_ERROR);
    }
}
