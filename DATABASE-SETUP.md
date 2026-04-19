# 📊 UniSmart Pay - Setup Base de Données (MySQL / XAMPP)

Ce projet utilise **MySQL** (XAMPP) et un seul script officiel:

- `database/unismart_pay_schema.sql`

Ce script crée la base `unismart_pay`, toutes les tables, et des données de démonstration (admins, étudiants, produits, comptes, terminaux).

## 🔧 Prérequis

- XAMPP (Apache + MySQL)
- phpMyAdmin ou MySQL Workbench

## ✅ Installation

### Option A — MySQL Workbench

1. Ouvrir MySQL Workbench
2. File → Open SQL Script
3. Sélectionner `database/unismart_pay_schema.sql`
4. Exécuter (⚡)

### Option B — phpMyAdmin

1. Ouvrir `http://localhost/phpmyadmin`
2. Onglet **Importer**
3. Importer `database/unismart_pay_schema.sql`

### Option C — Ligne de commande

```bash
mysql -u root -p < database/unismart_pay_schema.sql
```

## 🔍 Vérifications

```sql
SHOW DATABASES;
USE unismart_pay;
SHOW TABLES;

SELECT email, role, actif FROM admins;
SELECT matricule, actif FROM etudiants;
SELECT nom, prix, stock FROM produits;
```

## 🔐 Comptes de démo

- Admin: `admin@unismart.tn` / `Admin@1234`
- Étudiant: `2024/FST/0001` / `Etudiant@1234`

---

**Version:** 2.0 (MySQL/PDO)
