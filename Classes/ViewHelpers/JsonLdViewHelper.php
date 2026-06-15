<?php

declare(strict_types=1);

namespace RatingStar\Seal\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use RatingStar\Seal\Configuration\SealSettings;
use RatingStar\Seal\Service\JsonLdProvider;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Gibt das serverseitige RatingStar-JSON-LD (LocalBusiness + AggregateRating) an
 * Ort und Stelle aus – für gezielte Platzierung statt der site-weiten Middleware:
 *
 *   <rs:jsonld/>
 *
 * Der Marker data-rs-jsonld sorgt für die Ein-Block-Regel: die Middleware
 * injiziert dann nicht zusätzlich, und seal.js unterlässt seine Client-Injektion.
 *
 * Hinweis: Der JsonLdProvider wird in render() per makeInstance geholt, nicht per
 * Konstruktor-Injektion. Fluid instanziiert ViewHelper bereits zur Parse-Zeit
 * (Argument-Introspektion) über den ViewHelperResolver, der dort keine
 * Konstruktor-Abhängigkeiten auflösen kann – ein injizierter Konstruktor würde
 * mit ArgumentCountError scheitern. makeInstance zieht den (public) Service zur
 * Render-Zeit aus dem vollständigen DI-Container.
 */
final class JsonLdViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function render(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return '';
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return '';
        }

        $settings = SealSettings::fromSite($site);
        if ($settings->key === '') {
            return '';
        }

        $json = GeneralUtility::makeInstance(JsonLdProvider::class)
            ->fetch($settings->jsonLdUrl(), $settings->key);
        if ($json === null) {
            return '';
        }

        return '<script type="application/ld+json" data-rs-jsonld>' . $json . '</script>';
    }
}
