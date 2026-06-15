<?php

declare(strict_types=1);

namespace RatingStar\Seal\Configuration;

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Liest die RatingStar-Konfiguration einer Site aus den Site Settings
 * (Set "ratingstar/seal") und stellt sie als unveränderliches Wertobjekt bereit.
 * Grundlage für Siegel-Widget und Google-Sterne (JSON-LD). Alle URLs werden aus
 * der einen Base-Origin gebaut – kein Host wird hartkodiert.
 */
final readonly class SealSettings
{
    public const DEFAULT_BASE_URL = 'https://ratingstar.de';

    public function __construct(
        public string $slug,
        public string $key,
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public bool $jsonLdEnabled = true,
    ) {
    }

    public static function fromSite(Site $site): self
    {
        $settings = $site->getSettings();

        $baseUrl = rtrim(trim((string)$settings->get('ratingstar.baseUrl', '')), '/');

        return new self(
            slug: trim((string)$settings->get('ratingstar.slug', '')),
            key: trim((string)$settings->get('ratingstar.key', '')),
            baseUrl: $baseUrl !== '' ? $baseUrl : self::DEFAULT_BASE_URL,
            jsonLdEnabled: (bool)$settings->get('ratingstar.jsonLdEnabled', true),
        );
    }

    /**
     * True, wenn Slug und Key gesetzt sind – die Filiale also eingebunden werden kann.
     */
    public function isConfigured(): bool
    {
        return $this->slug !== '' && $this->key !== '';
    }

    /**
     * URL des Embed-Scripts.
     */
    public function sealJsUrl(): string
    {
        return $this->baseUrl . '/seal.js';
    }

    /**
     * Key-basierter JSON-LD-Endpunkt – rename-fest und auf allen Tarifen offen.
     * Die App baut das LocalBusiness-Schema selbst (inkl. Tarif-Gate).
     */
    public function jsonLdUrl(): string
    {
        return $this->baseUrl . '/seal/k/' . rawurlencode($this->key) . '.jsonld';
    }

    /**
     * Öffentliche Profil-URL der Filiale.
     */
    public function profileUrl(): string
    {
        return $this->baseUrl . '/t/' . rawurlencode($this->slug);
    }
}
