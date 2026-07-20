<?php

declare(strict_types=1);

namespace RatingStar\Seal\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use RatingStar\Seal\Configuration\SealSettings;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Bindet das RatingStar-Siegel an Ort und Stelle ein:
 *
 *   <rs:seal variant="seal-circle"/>
 *   <rs:seal variant="profile-card" position="bottom-right"/>
 *   <rs:seal slug="andere-filiale" variant="bar"/>
 *   <rs:seal variant="carousel" dataAttributes="data-car-count=&quot;4&quot; data-car-width=&quot;s&quot;"/>
 *
 * Gibt das offizielle Embed-Markup (<div class="rs-seal" …>) aus und lädt
 * seal.js einmal pro Seite – nur weil tatsächlich ein Siegel gerendert wird.
 * Der Slug stammt standardmäßig aus den Site Settings (Set "ratingstar/seal").
 *
 * Kanonische Varianten (SealConfig::VARIANT_META der App): seal-circle,
 * seal-circle-banner, profile-card, bar, hero, quote, carousel, wall,
 * footer-bar. Legacy-Werte (banner, circle, card, floating, pro) versteht
 * seal.js weiterhin über sein Alias-Mapping.
 *
 * Per-Embed-Optik (CONTRACT §17) läuft über zusätzliche data-*-Attribute
 * (z. B. data-size, data-pc-color) — im RatingStar-Dashboard unter
 * „Siegel & Widgets" generieren und via dataAttributes durchreichen.
 * seal.js validiert die Werte selbst (Whitelist, Ranges, Enums).
 */
final class SealViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * Im Passthrough gesperrte Attribute: slug/variant/position kommen aus
     * den nativen Argumenten, data-no-richsnippet steuert die Ein-Block-Regel
     * (CONTRACT §7) und data-preview-config ist Admin-only (CONTRACT §17).
     */
    private const RESERVED_DATA_ATTRIBUTES = [
        'data-slug',
        'data-variant',
        'data-position',
        'data-no-richsnippet',
        'data-preview-config',
    ];

    /**
     * Von seal.js unterstützte per-Embed-Optionen (EMBED_OVERRIDES in
     * seal-embed.js, CONTRACT §17) — camelCase-Key → data-kebab-Attribut.
     * Nur diese Keys werden aus dem overrides-Array übernommen.
     */
    private const OVERRIDE_KEYS = [
        'size',
        'pcPosition', 'pcWidth', 'pcShowCount', 'pcColor',
        'barShowCount', 'barShowVerified',
        'heroShowCount', 'heroShowVerified',
        'quotePick', 'quoteWidth',
        'carCount', 'carRotate', 'carMin', 'carWidth',
        'wallCols', 'wallTotal', 'wallMin', 'wallSort', 'wallWidth',
        'footerBarBg', 'footerBarText', 'footerBarLinkColor', 'footerBarLinkHover', 'footerBarPosition',
        'reviewsOnlyNamed', 'reviewsOnlyWithComment',
    ];

    public function initializeArguments(): void
    {
        $this->registerArgument('variant', 'string', 'Widget-Variante (kanonisch): seal-circle, seal-circle-banner, profile-card, bar, hero, quote, carousel, wall, footer-bar.', false, 'seal-circle-banner');
        $this->registerArgument('slug', 'string', 'Profil-Slug der Filiale. Standard: aus den Site Settings.', false, null);
        $this->registerArgument('position', 'string', 'Position der Profil-Karte: inline, bottom-right, bottom-left, top-right, top-left.', false, null);
        $this->registerArgument('dataAttributes', 'string', 'Zusätzliche data-*-Attribute für per-Embed-Optik, roh aus dem Dashboard-Generator, z. B. data-size="240" data-pc-color="weiss".', false, null);
        $this->registerArgument('overrides', 'array', 'Per-Embed-Optionen als [camelCase-Key => Wert] gemäß EMBED_OVERRIDES (z. B. carCount, pcColor, footerBarBg). Leere Werte bedeuten „Standard aus dem Dashboard" und werden nicht ausgegeben.', false, []);
    }

    public function render(): string
    {
        $settings = $this->resolveSettings();

        $slug = trim((string)($this->arguments['slug'] ?? '')) ?: ($settings?->slug ?? '');
        if ($slug === '') {
            return '';
        }

        $variant = trim((string)($this->arguments['variant'] ?? '')) ?: 'seal-circle-banner';
        $position = trim((string)($this->arguments['position'] ?? ''));

        // Load seal.js once per page – only because a seal is actually rendered.
        // URL is built from the configured base origin (default ratingstar.de).
        GeneralUtility::makeInstance(AssetCollector::class)->addJavaScript(
            'ratingstar_seal',
            $settings?->sealJsUrl() ?? (SealSettings::DEFAULT_BASE_URL . '/seal.js'),
            ['async' => 'async'],
            ['external' => true],
        );

        $attributes = [
            'class' => 'rs-seal',
            'data-slug' => $slug,
            'data-variant' => $variant,
        ];
        if ($position !== '') {
            $attributes['data-position'] = $position;
        }

        // Single-block rule (CONTRACT #7): when the site serves JSON-LD itself
        // (site-wide middleware or a <rs:jsonld> element), every .rs-seal must
        // carry data-no-richsnippet so seal.js suppresses its own client-side
        // rich snippet – exactly one JSON-LD block per page.
        if ($settings !== null && $settings->jsonLdEnabled && $settings->key !== '') {
            $attributes['data-no-richsnippet'] = '1';
        }

        // Per-Embed-Optik (CONTRACT §17), zwei Quellen: native Optionen
        // (overrides-Array, z. B. aus den FlexForm-Feldern des CE) und der
        // rohe Experten-String aus dem Dashboard-Generator — letzterer
        // überschreibt gleichnamige native Optionen, nie aber die
        // reservierten Kern-Attribute.
        $attributes += $this->collectOverrides((array)($this->arguments['overrides'] ?? []));
        $attributes = array_replace($attributes, $this->parseDataAttributes((string)($this->arguments['dataAttributes'] ?? '')));

        $markup = '<div';
        foreach ($attributes as $name => $value) {
            $markup .= ' ' . $name . '="' . htmlspecialchars((string)$value, ENT_QUOTES) . '"';
        }

        return $markup . '></div>';
    }

    /**
     * Übersetzt whitelisted camelCase-Optionen in data-kebab-Attribute
     * (pcColor → data-pc-color). Leere Werte heißen „Standard aus dem
     * Dashboard" und erzeugen bewusst kein Attribut, damit die zentrale
     * seal_config nicht von Formular-Defaults überschrieben wird.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, string>
     */
    private function collectOverrides(array $overrides): array
    {
        $attributes = [];
        foreach (self::OVERRIDE_KEYS as $key) {
            $value = trim((string)($overrides[$key] ?? ''));
            if ($value === '') {
                continue;
            }
            $name = 'data-' . strtolower((string)preg_replace('/[A-Z]/', '-$0', $key));
            $attributes[$name] = $value;
        }

        return $attributes;
    }

    /**
     * Zerlegt einen rohen Attribut-String (data-size="240" data-pc-color='weiss'
     * data-car-count=4) in ein data-* Attribut-Array. Nur data-*-Namen werden
     * akzeptiert — alles andere (class, onclick, …) fällt weg; die Werte-
     * Validierung übernimmt seal.js (EMBED_OVERRIDES-Whitelist).
     *
     * @return array<string, string>
     */
    private function parseDataAttributes(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $attributes = [];
        preg_match_all(
            '/(data-[a-z][a-z0-9-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))/i',
            $raw,
            $matches,
            PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL
        );
        foreach ($matches as $match) {
            $name = strtolower($match[1]);
            if (in_array($name, self::RESERVED_DATA_ATTRIBUTES, true)) {
                continue;
            }
            $attributes[$name] = $match[2] ?? $match[3] ?? $match[4] ?? '';
        }

        return $attributes;
    }

    private function resolveSettings(): ?SealSettings
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return null;
        }

        $site = $request->getAttribute('site');

        return $site instanceof Site ? SealSettings::fromSite($site) : null;
    }
}
