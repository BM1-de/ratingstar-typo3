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
 *   <rs:seal variant="circle"/>
 *   <rs:seal variant="floating" position="bottom-right"/>
 *   <rs:seal slug="andere-filiale" variant="bar"/>
 *
 * Gibt das offizielle Embed-Markup (<div class="rs-seal" …>) aus und lädt
 * seal.js einmal pro Seite – nur weil tatsächlich ein Siegel gerendert wird.
 * Der Slug stammt standardmäßig aus den Site Settings (Set "ratingstar/seal").
 */
final class SealViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('variant', 'string', 'Widget-Variante: banner, circle, bar, profile-card, floating, wall, carousel, quote, hero.', false, 'banner');
        $this->registerArgument('slug', 'string', 'Profil-Slug der Filiale. Standard: aus den Site Settings.', false, null);
        $this->registerArgument('position', 'string', 'Position für variant="floating": bottom-right, bottom-left, top-right, top-left, inline.', false, null);
    }

    public function render(): string
    {
        $settings = $this->resolveSettings();

        $slug = trim((string)($this->arguments['slug'] ?? '')) ?: ($settings?->slug ?? '');
        if ($slug === '') {
            return '';
        }

        $variant = trim((string)($this->arguments['variant'] ?? '')) ?: 'banner';
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

        $markup = '<div';
        foreach ($attributes as $name => $value) {
            $markup .= ' ' . $name . '="' . htmlspecialchars((string)$value, ENT_QUOTES) . '"';
        }

        return $markup . '></div>';
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
