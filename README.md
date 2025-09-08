# Achilles PIMS Bestellsystem

Ein internes Bestellsystem für **Pinguin/Achilles**, entwickelt mit **Symfony 6.4**, **Vue 3** und **TailwindCSS**.  
Es verbindet sich mit der **PIMS API** (Order- und Produkt-Schnittstellen) und stellt ein modernes Webfrontend zur Verfügung.

---

## 🚀 Features

- Benutzer-Login (LDAP)
- Übersicht und Verwaltung von PIMS-Bestellungen
- Bestellung von Druckprodukten (API-Anbindung an `pimsOrder.php`, `pimsProduct.php` & `pimsParcel.php`)
- Statusabfrage für Aufträge und Produkte (`pimsOrderStatus.php`)
- Dynamisches Frontend mit Vue.js & Tailwind
- Docker-Setup für lokale Entwicklung

---

## 🛠️ Tech-Stack

- **Backend:** [Symfony 6.4](https://symfony.com/) (PHP 8.2)
- **Frontend:** [Vue 3](https://vuejs.org/), [TailwindCSS](https://tailwindcss.com/)
- **Build-Tools:** Webpack Encore
- **Datenbank:** MariaDB / MySQL
- **Container:** Docker & Docker Compose

---

## 📂 Projektstruktur

```txt
.
├── assets/           # Vue 3 Komponenten, Tailwind CSS
├── config/           # Symfony Konfiguration
├── migrations/       # Doctrine Migrations
├── public/           # Öffentliche Dateien, Entry-Point
├── src/              # Symfony PHP Code
├── templates/        # Twig Templates
├── tests/            # PHPUnit Tests
├── translations/     # Sprachdateien
├── docker/           # Docker Build-Files
├── compose.yaml      # Docker Compose Setup
└── README.md
```

---

## ⚙️ Installation

### Voraussetzungen
- Docker & Docker Compose
- Node.js (>= 18) & npm oder Yarn
- PHP 8.2 (falls lokal ohne Docker)

### Setup

```bash
# Repository klonen
git clone https://github.com/ichbinboese/achilles-pims.git
cd achilles-pims

# Abhängigkeiten installieren
composer install
yarn install   # oder npm install

# Tailwind Build starten
yarn dev       # oder npm run dev

# Docker-Container starten
docker compose up -d
```

---

## 🔑 Umgebungsvariablen

Alle Konfigurationen erfolgen über `.env`.  
**Wichtig:** **niemals echte Zugangsdaten committen** – nutze `.env.local`.

Beispiel `.env.example`:

```env
APP_ENV=dev
APP_SECRET=ChangeMe

# Datenbank
DATABASE_URL="mysql://user:password@127.0.0.1:3306/achilles_pinguin"

# PIMS API
PIMS_API_URL="https://pims-api.stage.printdays.net/v1"
PIMS_API_KEY="your-api-key"
PIMS_API_AUTH="Basic xyz123"
```

---

## ▶️ Entwicklung

```bash
# Lokale Symfony API starten
symfony serve -d

# Frontend Hot-Reload
yarn dev

# Tests ausführen
php bin/phpunit
```

---

## 🧪 Tests & CI

- **PHPUnit** für Backend
- **ESLint & Vue TSC** für Frontend
- GitHub Actions (Beispiel: `.github/workflows/ci.yml`)
    - Build & Lint
    - Unit-Tests
    - Security-Checks (PHPStan, ESLint)

---

## 🛡️ Sicherheit

- `.env`, `.env.local` **nicht committen**
- API-Keys ausschließlich über Secrets (GitHub Actions, Docker ENV)
- Abhängigkeiten regelmäßig mit **Composer audit** & **npm audit** prüfen
- Optional: GitHub Dependabot aktivieren

---

## 📜 Lizenz

Dieses Projekt steht unter der **MIT Lizenz**.  
Siehe [LICENSE](./LICENSE) für Details.

---

## 👥 Autoren

- **Benjamin Böse** – Entwicklung
