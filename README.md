# RatingStar für TYPO3 (`ratingstar_seal`)

[RatingStar](https://ratingstar.de) ist das Bewertungstool für Unternehmen:
Kundenbewertungen sammeln, Bewertungen aus Google und anderen Portalen bündeln
und alles auf der eigenen Website zeigen. Diese Extension bringt das nach
TYPO3 — **Siegel und Bewertungs-Widgets** in allen neun Varianten sowie die
**Google-Sterne** (Rich Snippets / `AggregateRating`) einer RatingStar-Filiale.

TYPO3 extension `ratingstar_seal` · Composer `ratingstar/typo3-seal` ·
TYPO3 v13 + v14 · GPL-2.0-or-later

## Funktionsumfang

- **Site Settings:** RatingStar-Profil-Slug + Embed-Key, pro Site
  konfigurierbar (Set `ratingstar/seal`)
- **Siegel-Widget — alle Varianten:** Banderole, Rundsiegel, Profil-Karte
  (inline oder schwebend in einer Bildschirmecke), Bewertungsleiste,
  Gesamtnote, Zitat, Karussell, Bewertungswand, Fußleiste — als
  Content-Element und als Fluid-ViewHelper `<rs:seal>`; Bewertungsleiste,
  Gesamtnote, Zitat, Karussell und Bewertungswand wahlweise hell oder dunkel
  (Feld „Darstellung", Attribut `data-theme`)
- **Per-Embed-Optik:** alle von `seal.js` unterstützten Optionen
  (`EMBED_OVERRIDES`) direkt im Content-Element — je Variante ein eigener
  Formular-Tab (Größe, Breite, Kartenfarbe, Anzahl, Rotation, Sortierung,
  Fußleisten-Farben, Bewertungs-Filter …). Jedes Feld steht auf „Standard
  (aus Dashboard)" und wird nur ausgegeben, wenn es bewusst gesetzt ist —
  die zentrale Konfiguration bleibt führend. Dieselbe Variante kann so
  mehrfach pro Seite mit unterschiedlichen Einstellungen eingebunden werden.
  Für Experten bzw. künftige Optionen gibt es zusätzlich das
  Passthrough-Feld „Weitere data-Attribute" (Attribut-String aus dem
  Dashboard-Generator, überschreibt gleichnamige Feld-Einstellungen); im
  ViewHelper entsprechend `overrides="{…}"` und `dataAttributes="…"`.
  Ungültige Werte ignoriert `seal.js` automatisch.
- **Google-Sterne:** serverseitiges JSON-LD (`AggregateRating`) über den offiziellen key-basierten RatingStar-Endpunkt —
  site-weit per Middleware oder gezielt als Content-Element/ViewHelper
  `<rs:jsonLd>` (mit großem L); genau ein JSON-LD-Block pro Seite

Beispiele:

```html
<rs:seal variant="seal-circle"/>
<rs:seal variant="profile-card" position="bottom-right"/>
<rs:seal variant="carousel" dataAttributes="data-car-count=&quot;4&quot; data-car-width=&quot;s&quot;"/>
```

## Installation

```bash
composer require ratingstar/typo3-seal
vendor/bin/typo3 extension:setup
```

**Datenbank-Schema aktualisieren (Pflicht):** `extension:setup` legt die
Cache-Tabellen `cache_ratingstar_seal` und `cache_ratingstar_seal_tags` an, in
denen die Extension die Google-Sterne (JSON-LD) zwischenspeichert. Ohne
Kommandozeile geht dasselbe im Backend-Modul *Maintenance* über „Analyze
Database Structure" (TYPO3 13: *Admin Tools → Maintenance*, TYPO3 14:
*System → Maintenance*). Fehlen die Tabellen, gibt die Extension keine
Google-Sterne aus und schreibt bei jedem Seitenaufruf einen Fehler ins
TYPO3-Log; die Siegel-Widgets funktionieren trotzdem.

Danach zwei Schritte je Site:

1. **Set `ratingstar/seal` als Abhängigkeit der Site eintragen (Pflicht).**
   Ohne das Set rendern die Content-Elemente nicht. Im Backend unter
   *Site Management → Sites* die Site bearbeiten und bei „Sets for this
   Site" das Set „RatingStar Seal" hinzufügen — oder in
   `config/sites/<site>/config.yaml`:

   ```yaml
   dependencies:
     - ratingstar/seal
   ```

2. **Profil-Slug und Embed-Key der Filiale in den Site Settings hinterlegen**
   (*Site Management → Settings*, Kategorie „RatingStar Seal") — oder in
   `config/sites/<site>/settings.yaml`:

   ```yaml
   ratingstar:
     slug: meine-filiale
     key: <Embed-Key aus dem RatingStar-Dashboard>
   ```

### Ohne Composer (Classic Mode)

Die Extension steht im TER als
[`ratingstar_seal`](https://extensions.typo3.org/extension/ratingstar_seal).
Im Backend-Modul *Extensions* nach `ratingstar_seal` suchen und installieren
oder das ZIP aus dem TER hochladen, die Extension aktivieren und danach wie
oben das Datenbank-Schema aktualisieren. Die beiden Schritte je Site gelten
genauso.

## Domain freigeben

Sobald im RatingStar-Dashboard eine Domain per DNS bestätigt wurde, liefert
RatingStar die Siegel-Daten nur noch an freigegebene Domains aus. Die eigene
Website deshalb im RatingStar-Dashboard unter **„Siegel & Widgets → Domains &
Statistik"** (Abschnitt „Freigegebene Domains") eintragen und per
`TXT _ratingstar.<domain>` bestätigen. Fehlt die Freigabe, bleibt das Siegel
auf dieser Domain leer. Die Google-Sterne laufen über den Embed-Key und sind
davon nicht betroffen.

## Zwischenspeicher

Die Google-Sterne (JSON-LD) speichert die Extension 6 Stunden im TYPO3-Cache
`ratingstar_seal`. Nach einer Änderung im RatingStar-Dashboard oder einem
neuen Embed-Key *Alle Caches leeren* (oder `vendor/bin/typo3 cache:flush`),
dann greift die Änderung sofort. Die Siegel selbst lädt `seal.js` immer
aktuell von RatingStar.

## Datenschutz

Die Siegel-Widgets laden `seal.js`, `seal.css`, die Bewertungsdaten und einen
Zähl-Pixel von der RatingStar-Domain (Standard `ratingstar.de`). Der Browser
der Besucher verbindet sich dabei mit diesem Host. Die RatingStar-Domain
deshalb in der Datenschutzerklärung nennen und im Consent-Tool berücksichtigen.
Die Google-Sterne holt die Extension serverseitig; dafür baut der Browser der
Besucher keine Verbindung zu RatingStar auf.

## Anforderungen

- TYPO3 13.4 LTS oder 14
- PHP 8.2+

## Lizenz

GPL-2.0-or-later
