# 📊 UniSmart Pay - Guide d'Installation Web (XAMPP)

## 🎯 Vue d'ensemble

UniSmart Pay est un système de paiement pour université utilisant:

- **Web:** PHP (PDO) + HTML/CSS/JS
- **DB:** MySQL (XAMPP)
- **Buvette:** scan code-barres via QuaggaJS (caméra navigateur)
- **Terminal RFID (optionnel):** ESP32 + RC522 (endpoints PHP)

---

## 🚀 Installation rapide

### Prérequis

- XAMPP (Apache + MySQL)
- PHP 8.x recommandé

### 1) Copier le projet

```bash
Placer le dossier `UniSmartPay` dans `C:\xampp\htdocs\UniSmartPay`
```

### 2) Configuration DB

Les identifiants MySQL sont dans `web/config/config.php`:

- `DB_NAME = unismart_pay`
- `DB_USER = root`
- `DB_PASS = ''` (par défaut XAMPP)

Importer la base avec `database/unismart_pay_schema.sql` (voir `DATABASE-SETUP.md`).

---

## 🌍 Accès

### Admin Panel
- URL: `http://localhost/UniSmartPay/web/admin/login.php`

### Student Portal
- URL: `http://localhost/UniSmartPay/web/etudiant/login.php`

### Accueil
- URL: `http://localhost/UniSmartPay/web/`

---

## 📁 Structure

Le web utilise la structure suivante:

- `web/config/*` (PDO + config)
- `web/includes/*` (CSRF, helpers, header/footer)
- `web/etudiant/*` (login, dashboard, paiement buvette)
- `web/admin/*` (login, dashboard)
- `web/api/*` (barcode lookup, checkout, endpoints terminal)

---

## 🔐 Sécurité

### Points Importants

1. **Authentification:**
   - Mots de passe hashés (SHA-256)
   - Sessions PHP sécurisées (1 heure)
   - Validation des inputs

2. **Base de Données:**
   - Utiliser HTTPS en production
   - Chiffrer les données sensibles
   - Limiter les accès réseau

3. **CORS/API:**
   - Configurer CORS si vous appelez les endpoints depuis un autre domaine

---

## 🧪 Test des Fonctionnalités

### Tester l'authentification admin

```bash
# 1. Accéder à admin/login.php
# 2. Entrer: 
#    - Username: admin
#    - Password: admin
```

### Tester l'authentification étudiant

```bash
# 1. Accéder à etudiant/login.php
# 2. Ajouter un étudiant depuis admin
# 3. Email: etudiant@example.com
#    Password: password
```

### Tester les opérations

```bash
# 1. Admin Dashboard: Ajouter étudiant ✅
# 2. Admin: Assigner carte RFID ✅
# 3. Admin: Recharger compte ✅
# 4. Étudiant: Vérifier solde ✅
```

---

## 🔌 Terminal RFID (optionnel)

- Endpoint compatible ESP32: `web/api/payment.php`
- Endpoints tokenisés: `web/api/terminal/paiement.php`, `web/api/terminal/solde.php`, `web/api/terminal/log.php`

---

## 🐛 Dépannage

### Problème: "Erreur de connexion MySQL"

```
Solution:
1. Vérifier que MySQL (XAMPP) est démarré
2. Vérifier la config dans web/config/config.php (DB_HOST, DB_NAME, DB_USER, DB_PASS)
3. Vérifier que la base `unismart_pay` est importée via database/unismart_pay_schema.sql
```

### Problème: "Carte RFID introuvable"

```
Solution:
1. Vérifier que la carte est assignée à l'étudiant
2. Vérifier que uid dans CARTES_RFID est correct
3. Vérifier que active = 1
```

### Problème: "Erreur de session"

```
Solution:
1. Vérifier que session_start() est appelé
2. Vérifier les permissions du dossier /tmp
3. Vérifier la configuration de session.save_path dans php.ini
```

---

## 📚 Documentation Complète

- **DATABASE-SETUP.md** - Import MySQL (XAMPP)
- **DEPLOYMENT.md** - Déploiement production
- **SECURITY.md** - Considérations sécurité

---

## 👥 Utilisateurs de Test

### Admin
- **Username:** admin
- **Password:** admin

### Étudiants
Créer via le dashboard admin:
- Nom: Jour Jean
- Email: jean.jour@university.edu
- Mot de passe: password

---

## 📞 Support

Pour les problèmes ou questions:
1. Consulter les logs de sécurité (MySQL): `SELECT * FROM logs_securite ORDER BY date_log DESC;`
2. Vérifier les erreurs Apache/PHP via le panneau XAMPP (Apache Logs)

---

## ✅ Checklist de Déploiement

- [ ] XAMPP (Apache + MySQL) installé
- [ ] MySQL démarré
- [ ] Base `unismart_pay` importée (database/unismart_pay_schema.sql)
- [ ] web/config/config.php avec bons paramètres
- [ ] Admin créé dans la table `admins`
- [ ] Test login admin ✅
- [ ] Test login étudiant ✅
- [ ] Test ajout étudiant ✅
- [ ] Test recharge compte ✅

---

## 🔄 Prochaines Étapes

1. **Table des Mots de Passe Étudiants:**
   - (Optionnel) Ajouter une gestion de reset mot de passe

2. **Terminaux:**
   - Durcir l'auth (rotation token, IP allowlist)

3. **Frontend amélioré:**
   - Navigation et UX

4. **ESP32:**
   - Code microcontrôleur
   - Communication avec API

---

**Version:** 1.0  
**Date:** Avril 2026  
**Statut:** ✅ Prêt pour test
