# RatingStar für TYPO3 (`ratingstar_seal`)

Bindet das **RatingStar-Siegel** und die **Google-Sterne** (Rich Snippets /
`AggregateRating`) einer [RatingStar](https://ratingstar.de)-Filiale in eine
TYPO3-Website ein.

> **Status: in Entwicklung (alpha).** Funktionsumfang und Roadmap stehen in den
> Issues. Läuft auf TYPO3 13.4 LTS und 14.

## Funktionsumfang (geplant)

- **Extension-Konfiguration:** RatingStar-Profil-Slug + Embed-Key, pro Site
  konfigurierbar
- **Siegel-Widget:** Rundsiegel, Banderole, Floating-Badge — als
  Content-Element und als Fluid-ViewHelper `<rs:seal>`
- **Google-Sterne:** serverseitiges JSON-LD (`AggregateRating`) über den
  offiziellen RatingStar-Endpunkt, per TypoScript aktivierbar

## Installation

```bash
composer require ratingstar/typo3-seal
```

Anschließend Profil-Slug und Embed-Key der Filiale in der
Extension-Konfiguration hinterlegen.

## Anforderungen

- TYPO3 13.4 LTS oder 14
- PHP 8.2+

## Lizenz

GPL-2.0-or-later
