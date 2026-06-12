# RatingStar für TYPO3

Bindet das RatingStar-Siegel und die Google-Sterne (Rich Snippets) einer
RatingStar-Filiale (https://ratingstar.de) in eine TYPO3-Website ein.

**Status: in Entwicklung** — Funktionsumfang und Roadmap stehen in den
Issues dieses Repos.

## Geplanter Funktionsumfang

- Extension-Konfiguration: RatingStar-Profil-Slug + Embed-Key
- Siegel-Widget (Rundsiegel, Banderole, Floating-Badge) als
  Content-Element / Fluid-ViewHelper
- Google-Sterne: serverseitiges JSON-LD über den offiziellen
  RatingStar-PHP-Snippet-Endpunkt (`/seal/k/<key>.json`)

## Entwicklung

Dieses Repo enthält **nur die Extension** (`ratingstar_seal`). Die lokale
Test-Instanz liegt drumherum: DDEV-Projekt `ratingstar-plugin-typo3`
(https://ratingstar-plugin-typo3.ddev.site, Backend `/typo3`, User `admin`),
eingebunden als Composer-Path-Repository unter `packages/ratingstar_seal`.

```bash
cd ~/Sites/Dev/ratingstar-plugin-typo3
ddev start
ddev composer require ratingstar/typo3-seal:@dev
```
