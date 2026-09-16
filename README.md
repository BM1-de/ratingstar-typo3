# RatingStar für TYPO3 (`ratingstar_seal`)

[RatingStar](https://ratingstar.de) ist das Bewertungstool für Unternehmen:
Kundenbewertungen sammeln, Bewertungen aus Google und anderen Portalen bündeln
und alles auf der eigenen Website zeigen. Diese Extension bringt das nach
TYPO3 — **Siegel und Bewertungs-Widgets** in allen neun Varianten sowie die
**Google-Sterne** (Rich Snippets / `AggregateRating`) einer RatingStar-Filiale.

TYPO3 extension `ratingstar_seal` · Composer `ratingstar/typo3-seal` ·
TYPO3 v13 + v14 · GPL-2.0-or-later

## Funktionsumfang

- **Extension-Konfiguration:** RatingStar-Profil-Slug + Embed-Key, pro Site
  konfigurierbar (Site Settings, Set `ratingstar/seal`)
- **Siegel-Widget — alle Varianten:** Banderole, Rundsiegel, Profil-Karte
  (inline oder schwebend in einer Bildschirmecke), Trust-Bar, Hero-Snippet,
  Featured Quote, Carousel, Wall of Love, Footer-Bar — als Content-Element
  und als Fluid-ViewHelper `<rs:seal>`
- **Per-Embed-Optik:** alle von `seal.js` unterstützten Optionen
  (`EMBED_OVERRIDES`) direkt im Content-Element — je Variante ein eigener
  Formular-Tab (Größe, Breite, Kartenfarbe, Anzahl, Rotation, Sortierung,
  Footer-Bar-Farben, Bewertungs-Filter …). Jedes Feld steht auf „Standard
  (aus Dashboard)" und wird nur ausgegeben, wenn es bewusst gesetzt ist —
  die zentrale Konfiguration bleibt führend. Dieselbe Variante kann so
  mehrfach pro Seite mit unterschiedlichen Einstellungen eingebunden werden.
  Für Experten bzw. künftige Optionen gibt es zusätzlich das
  Passthrough-Feld „Weitere data-Attribute" (Attribut-String aus dem
  Dashboard-Generator, überschreibt gleichnamige Feld-Einstellungen); im
  ViewHelper entsprechend `overrides="{…}"` und `dataAttributes="…"`.
  Ungültige Werte ignoriert `seal.js` automatisch.
- **Google-Sterne:** serverseitiges JSON-LD (`LocalBusiness` +
  `AggregateRating`) über den offiziellen key-basierten RatingStar-Endpunkt —
  site-weit per Middleware oder gezielt als Content-Element/ViewHelper
  `<rs:jsonld>`; genau ein JSON-LD-Block pro Seite

Beispiele:

```html
<rs:seal variant="seal-circle"/>
<rs:seal variant="profile-card" position="bottom-right"/>
<rs:seal variant="carousel" dataAttributes="data-car-count=&quot;4&quot; data-car-width=&quot;s&quot;"/>
```

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
