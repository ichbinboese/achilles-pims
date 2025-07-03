# 🐧 Pinguin-Druckbogen-Bestellsystem

Ein webbasiertes System zur Verwaltung und Übertragung von Druckaufträgen an die PIMS API von Pinguin-Druck, basierend auf Symfony 6,4 (PHP 8.3) und Vue 3 mit TailwindCSS.

---

## 🚀 Features

- 🔍 Bestellungen suchen (b7 / easyOrdner)
- ✅ Positionen auswählen und Details einsehen
- 📦 PIMS-Bestellung auslösen (`pimsOrder.php`)
- 📄 Produktdaten zu Bestellung erfassen (`pimsProduct.php`)
- 🔒 Login via Auth Store (optional via Local Storage Token)
- 📂 Datei-Uploads: Vorder- / Rückseite
- 🧠 Validierung + Duplikatvermeidung via `uniqueid` (MD5)
- 🔄 API-Kommunikation mit `axios` + Toast-Meldungen

---

## 🧰 Tech Stack

| Bereich           | Technologie                   |
|------------------|-------------------------------|
| Backend          | Symfony 6.4, Doctrine ORM     |
| Frontend         | Vue 3, Composition API        |
| Styling          | TailwindCSS 3                 |
| Auth / API       | `axios`,                      |
| Build-Tool       | Webpack Encore                |
| DB-Unterstützung | MariaDB + Oracle via Doctrine |

---

## 📦 Installation

```bash
# Backend installieren
composer install

# Frontend installieren
yarn install
yarn dev      # oder yarn build für Produktion
```

> .env Datei kopieren:
> `.env.local` → mit Zugangsdaten zu `DATABASE_URL`, `DATABASE_EASY_URL`, `DATABASE_ORACLE_URL`

---

## 🔗 API-Konfiguration

Die Kommunikation mit PIMS erfolgt via Basic Auth und multipart POST:

```http
Authorization: Basic QmVuamFtaW4uQm9lc2U6LHhLUTFOei4lRFpZTTc/Qw==
Content-Type: multipart/form-data
```

Verwendete Endpunkte:

- `https://pims-api.stage.printdays.net/v1/pimsOrder.php`
- `https://pims-api.stage.printdays.net/v1/pimsProduct.php`
- `https://pims-api.stage.printdays.net/v1/pimsParcel.php`

---

## 🗂 Projektstruktur

```txt
├── src/
│   ├── Entity/
│   │   ├── Main/               # Symfony-Entities (z. B. Bestellungen, Produkte)
│   │   ├── Easy/               # Zusätzliche DB-Verbindung
│   ├── Controller/
│   │   ├── Api/                # JSON-API für Vue
│   │   ├── IndexController.php
│
├── assets/
│   ├── components/             # Vue-Komponenten
│   │   ├── FormInput.vue
│   │   ├── FormInputSelect.vue
│   ├── views/                  # Seitenkomponenten
│
├── templates/
│   └── base.html.twig          # Einbettungspunkt für App.vue
│
├── public/
│   └── build/                  # Webpack Encore Output
```

---

## 🧪 Beispielablauf

1. Suche starten → `/api/bestellung?fiNr=114&bestnr=114BE2501343`
2. Position wählen
3. `uniqueid` generieren aus `bestnr + bestpos + zähler`
4. Bestellung anlegen → `pimsOrder.php`
5. Produkte anfügen → `pimsProduct.php`

---

## ✅ ToDos / Weiterentwicklung

- [ ] Authentifizierung gegen internes ACL-System
- [ ] Fortschrittsanzeige pro Produkt
- [ ] Dateiuploads in Backend verifizieren
- [ ] PDF-Preview nach Upload anzeigen
- [ ] Statusabruf der PIMS-Produkte

---

## 📜 Lizenz

MIT © Werner Achilles GmbH & Co. KG – Benjamin Böse