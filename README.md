# 🎓 UniSmart Pay (MySQL / PHP PDO)

UniSmart Pay est un système de paiement universitaire basé sur **MySQL (XAMPP)** et une interface **PHP (PDO)**.

Fonctionnalités actuellement en place:
- Étudiant: connexion (matricule), dashboard (solde), historique
- Buvette: scan code-barres (QuaggaJS) → panier → paiement (transaction atomique)
- Terminal RFID (optionnel): endpoints PHP (paiement/solde/log) + endpoint compatible ESP32

## ✅ Installation (rapide)

1) Importer la base (un seul fichier)
- Script officiel: `database/unismart_pay_schema.sql`
- Guide: `DATABASE-SETUP.md`

2) Démarrer XAMPP
- Apache + MySQL en “Running”

3) Ouvrir l’application
- Accueil: `http://localhost/UniSmartPay/web/`

Comptes démo (créés par le script SQL):
- Admin: `admin@unismart.tn` / `Admin@1234`
- Étudiant: `2024/FST/0001` / `Etudiant@1234`

## 🔌 Endpoints API PHP

- Barcode lookup: `web/api/barcode_lookup.php?code=...`
- Checkout buvette: `web/api/buvette_checkout.php`

Terminal RFID:
- ESP32 (legacy): `web/api/payment.php` (JSON: `card_uid`, `amount`, `terminal_id`)
- Tokenisés: `web/api/terminal/paiement.php`, `web/api/terminal/solde.php`, `web/api/terminal/log.php`

## 📄 Docs utiles

- `DATABASE-SETUP.md`
- `web/INSTALLATION.md`
- `NODE_RFID_SERVER.md`
