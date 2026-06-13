<?php

declare(strict_types=1);

namespace RatingStar\Seal\Configuration;

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Liest die RatingStar-Konfiguration (Profil-Slug + Embed-Key) einer Site aus
 * den Site Settings (Set "ratingstar/seal") und stellt sie als unveränderliches
 * Wertobjekt bereit. Grundlage für Siegel-Widget und Google-Sterne (JSON-LD).
 */
final readonly class SealSettings
{
    public function __construct(
        public string $slug,
        public string $key,
    ) {
    }

    public static function fromSite(Site $site): self
    {
        $settings = $site->getSettings();

        return new self(
            slug: trim((string)$settings->get('ratingstar.slug', '')),
            key: trim((string)$settings->get('ratingstar.key', '')),
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
     * Öffentliche Profil-URL der Filiale auf ratingstar.de.
     */
    public function profileUrl(): string
    {
        return 'https://ratingstar.de/t/' . rawurlencode($this->slug);
    }
}
