# Achilles PIMS

Ein modernes Symfony 6.4 + Vue 3 + Tailwind Projekt für Bestellverwaltung und Schnittstellen-Integration.

---

## Inhaltsverzeichnis

- [Features](#features)
- [Systemvoraussetzungen](#systemvoraussetzungen)
- [Installation](#installation)
- [Konfiguration](#konfiguration)
- [Frontend-Entwicklung (Vue + Tailwind)](#frontend-entwicklung-vue--tailwind)
- [API & Authentifizierung](#api--authentifizierung)
- [Eigene UI-Komponenten (Best Practice)](#eigene-ui-komponenten-best-practice)
- [Tests](#tests)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Mitwirkende](#mitwirkende)
- [Lizenz](#lizenz)

---

## Features

- **Symfony 6.4 Backend** mit REST-APIs (Bestellungen, Produkte, Authentifizierung)
- **Vue 3 Frontend:** Moderne Komponentenstruktur, Routing, State-Management
- **TailwindCSS** für einheitliches, responsives UI-Design
- **API Authentifizierung** via Bearer-Token
- **Wiederverwendbare UI-Komponenten** für Buttons, Cards, Links etc.
- **Dark-Mode-Unterstützung**
- **Datenbank:** Doctrine ORM (MySQL/PostgreSQL/SQLite)

---

## Systemvoraussetzungen

- PHP >= 8.3
- Composer
- Node.js >= 18, npm/yarn
- Datenbank (MySQL, PostgreSQL oder SQLite)
- Git

---

## Installation

```bash
git clone https://github.com/ichbinboese/achilles-pims.git
cd achilles-pims

# Abhängigkeiten installieren
composer install
npm install

# .env anpassen (z.B. DB-Zugang, APP_API_TOKEN)
cp .env .env.local

# Datenbank anlegen & Migrationen ausführen
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Assets/Vue/Tailwind bauen
npm run dev   # für Entwicklung
npm run build # für Produktion

# Lokalen Webserver starten (z.B. symfony cli oder PHP built-in)
symfony serve
# oder
php -S localhost:8000 -t public
```
## Frontend-Entwicklung (Vue + Tailwind)

Das Frontend liegt unter `/assets` und ist in folgende Bereiche strukturiert:

- `/assets/components/elements/` – für Buttons, Cards, AppLink, usw.
- `/assets/components/pages/` – für Seiten-Views
- `/assets/styles/` – globale Styles, eigene Tailwind-Klassen mit `@apply` (z.B. `.btn-orange`)

### Entwicklung starten

```bash
npm run dev
```
Öffne dann deinen Browser unter: http://localhost:8000

## Eigene Komponenten verwenden
Beispiel für einen eigenen Button als Vue-Komponente:

```html
<!-- assets/components/elements/Button.vue -->
<template>
  <button class="px-4 py-2 rounded-2xl shadow bg-blue-600 text-white hover:bg-blue-700 transition font-semibold">
    <slot />
  </button>
</template>
```
Mehr Beispiele findest du unter /assets/components/elements/.

## API & Authentifizierung
### Authentifizierung per Bearer-Token
Alle schreibenden API-Requests benötigen einen gültigen Auth-Token im Header:
```dotenv
Authorization: Bearer DEIN_API_TOKEN
```
Neue Tokens kannst du per Symfony-Command, Admin-Endpoint oder direkt in der Datenbank erzeugen.

Beispiel (fetch in JS):

```js
fetch('/api/pims-bestellungen', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer DEIN_API_TOKEN'
  },
  body: JSON.stringify(data)
})
```

#### Beispiel: API-Endpunkt Bestellungen anlegen
```php
#[Route('/api/pims-bestellungen', name: 'pims_bestellungen_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
{
    // ... Auth-Token-Prüfung und Logik siehe src/Controller/...
}
```

## Eigene UI-Komponenten (Best Practice)
- Wiederverwendbare Komponenten (z.B. `<Button>`, `<AppLink>`, `<Card>`) liegen unter `/assets/components/elements/`
- Styling wird zentral in diesen Komponenten gepflegt.
- Erweiterbar mit Props (Farbe, Größe, etc.)
- Siehe Beispiele im Code-Repository.

## Tests
- Backend: PHPUnit (tests/)
- Frontend: Optionale Unit-Tests mit z.B. Vitest/Jest

Beispiel Backend-Test:
```bash
php bin/phpunit
```

## Deployment

Production Build:

```bash
npm run build
php bin/console cache:clear --env=prod
```

Empfohlen:

- .env.local mit Produktionsdaten ausstatten
- Webserver auf /public zeigen lassen
- HTTPS erzwingen

## Troubleshooting

- **Fehlende Abhängigkeiten:** <br>Prüfe, ob alle PHP- und JS-Abhängigkeiten installiert sind.
- **Tailwind Klassen werden nicht angewendet:** <br>Prüfe, ob npm run dev/build ohne Fehler läuft.

- **API gibt 401 oder 403:** <br>Prüfe Auth-Token in .env.local und Request-Header.

- **Probleme mit Datenbank:** <br>Prüfe Datenbankzugang und führe ggf. Migrationen erneut aus.

## Mitwirkende
- Benjamin Böse

## Lizenz
Dieses Projekt steht unter der MIT-Lizenz – siehe LICENSE.