# 🚀 Guide Node.js RFID Server (Alternative Web Serial Moderne)

## 🎯 Objectif
Créer un serveur **WebSocket moderne** qui:
- ✅ Remplacement complet à Web Serial API
- ✅ Fonctionne dans **TOUS les navigateurs**
- ✅ Temps réel avec WebSocket
- ✅ API REST également disponible
- ✅ Gestion multiple clients simultanés

---

## 📋 Prérequis

1. **Node.js 14+** installé
   ```bash
   # Vérifier:
   node --version
   npm --version
   ```

2. **Fichier node_rfid_server.js** présent dans le projet
   - Chemin: `c:\xampppp\htdocs\UniSmartPay\node_rfid_server.js`

---

## 🔧 Installation (5 min)

### Étape 1: Ouvrir Terminal
- **Windows**: PowerShell ou CMD
- **Mac/Linux**: Terminal

### Étape 2: Naviguer au Dossier du Projet
```bash
cd c:\xampppp\htdocs\UniSmartPay
```

### Étape 3: Installer les Dépendances
```bash
npm install express socket.io serialport
```

**Cela créera:**
- Dossier: `node_modules/`
- Fichier: `package.json`
- Fichier: `package-lock.json`

### Étape 4: Vérifier Installation
```bash
npm list express socket.io serialport
```

Résultat attendu:
```
├── express@4.x.x
├── socket.io@4.x.x
└── serialport@9.x.x
```

---

## 🚀 Lancement du Serveur

### Commande Principale
```bash
node node_rfid_server.js
```

### Résultat Attendu
```
=================================
🚀 RFID Reader Server
=================================
📍 Serveur: http://localhost:3000
🔧 Port sériel: À configurer via interface
👥 API PHP: http://localhost/UniSmartPay/web/api/terminal/log.php

📋 Accès:
  - Web UI: http://localhost:3000
  - Status: http://localhost:3000/api/status

💡 Prêt à recevoir les UIDs des lecteurs RFID!
```

**✅ Serveur démarré avec succès!**

---

## 🌐 Interface Web

### URL
```
http://localhost:3000
```

### Interface Affichée
- ✅ Status de connexion (dot rouge/vert)
- ✅ Console en temps réel des logs
- ✅ Button pour connecter port sériel
- ✅ Affichage UIDs reçus
- ✅ Compteur des clients connectés

### Fonctionnalités
1. **🔌 Connecter Port Sériel**
   - Cliquer sur le bouton
   - Entrer le port: `COM3`, `/dev/ttyUSB0`, etc.
   - Résultat: ✅ "Port sériel connecté"

2. **🎫 Recevoir UIDs**
   - Scannez une carte RFID
   - L'UID s'affiche en grand
   - Console affiche: "📡 UID reçu: 579DF94"

3. **👥 Voir Clients Connectés**
   - Bas de la page: "Clients connectés: X"
   - Augmente à chaque nouvelle connexion web

---

## 💡 Intégration avec UniSmartPay

### 1️⃣ Node Server Reçoit UID
```
ESP32/Arduino Serial → Node.js Server
  ↓
websocket://localhost:3000
  ↓
Navigateur web reçoit UID
```

### 2️⃣ Envoyer à API PHP
```
Node.js → POST /UniSmartPay/web/api/terminal/log.php (type_message=SCAN, uid_carte=XXX)
  ↓
Laravel/PHP traite
  ↓
Sauvegarde en BD
```

### 3️⃣ Web Affiche Résultat
```
Frontend Socket.io → Affiche UID
  ↓
Admin peut assigner à étudiant
```

---

## 🔗 API REST Disponible

### Vérifier Status du Serveur
```bash
# Commande:
curl http://localhost:3000/api/status

# Réponse:
{
    "status": "operational",
    "clients": 2,
    "serial_connected": true,
    "timestamp": "2026-04-11T10:30:45.123Z"
}
```

### Format WebSocket Events

**Événement: `serial:data`**
```json
{
    "uid": "579DF94E",
    "timestamp": "2026-04-11T10:30:45Z"
}
```

**Événement: `serial:connected`**
```json
{
    "port": "/dev/ttyUSB0"
}
```

---

## 🖥️ Ports Sériel Courants

### Windows
```
COM1, COM2, COM3, COM4, COM5, ...
```

### Linux/Raspberry Pi
```
/dev/ttyUSB0      (USB adapter)
/dev/ttyACM0      (UART adapter)
/dev/ttyAMA0      (Raspberry Pi native)
```

### Mac
```
/dev/tty.usbserial-xxxxx
/dev/cu.usbserial-xxxxx
```

---

## 🐛 Dépannage

### ❌ "Module not found: express"
```bash
Solution: npm install
```

### ❌ "Port COM3 not found"
```bash
Solution:
1. Vérifier ESP32 est connecté (USB)
2. Voir Device Manager (Windows) ou lsusb (Linux)
3. Essayer ports courants: COM3, COM4, COM5
```

### ❌ "EACCES: permission denied"
```bash
Solution (Linux/Mac):
sudo node node_rfid_server.js

# Ou (permanent):
sudo chmod 666 /dev/ttyUSB0
```

### ❌ "Server already in use port 3000"
```bash
Solution 1: Changer PORT dans le code
Solution 2: Trouver et tuer processus:
  lsof -i :3000
  kill -9 <PID>
```

### ❌ Navigateur ne se connecte pas
```bash
Solution:
1. Serveur démarré?
2. Firewall bloque port 3000?
3. Core Dump disponible?
4. Essayer: http://127.0.0.1:3000
```

---

## 🔄 Workflow Complet

### 1️⃣ Démarrer Serveur
```bash
node node_rfid_server.js
```

Expected: "Prêt à recevoir les UIDs..."

### 2️⃣ Ouvrir Interface Web
```
http://localhost:3000
```

Expected: Page charge, status dot est rouge (pas de port)

### 3️⃣ Connecter Port Sériel
```
1. Cliquer: 🔌 Connecter Port Sériel
2. Dialog: Entrer port (ex: COM3)
3. Résultat: ✅ Status dot devient vert
```

### 4️⃣ Laisser Server Tourner
```
Terminal reste ouvert
Console affiche chaque UID reçu
logs.txt updated automatiquement
```

### 5️⃣ Recevoir UIDs
```
Scannez carte RFID
  ↓
ESP32/Arduino envoie UID via Serial
  ↓
Node.js reçoit
  ↓
WebSocket envoie à navigateur
  ↓
Interface affiche "UID: 579DF94"
  ↓
Envoyer à API PHP si besoin
```

### 6️⃣ Arrêter Server
```bash
# Dans le terminal:
Ctrl+C

Expected: "Fermeture du serveur... Fermé"
```

---

## 📊 Architecture Vue d'Ensemble

```
┌─────────────────────────────────────┐
│ ESP32 RFID Reader                   │
│ (USB Serial Connection)             │
└──────────────┬──────────────────────┘
               │ UART/Serial
               ↓ 115200 baud
┌─────────────────────────────────────┐
│ Node.js RFID Server                 │
│ (node_rfid_server.js)               │
│ - Port Serial Reader                │
│ - WebSocket Broadcaster             │
│ - REST API Handler                  │
└──────────┬──────────────┬───────────┘
           │ WebSocket    │ REST API
           ↓              ↓
    ┌─────────────┐  ┌──────────────┐
    │ Web Browser │  │ PHP Backend   │
    │ (UI)        │  │ (terminal/log)│
    └─────────────┘  └──────────────┘
```

---

## 🎮 Contrôle du Serveur

### Redémarrer Serveur
```bash
# Stop: Ctrl+C
# Start: node node_rfid_server.js
```

### Changer Port
```bash
# Éditer node_rfid_server.js, linha 9:
const PORT = 3000;  // Changer à 3001, 4000, etc.
```

### Changer Baud Rate
```bash
# Éditer node_rfid_server.js, ligne 12:
const SERIAL_BAUD = 115200;  // Adapter à votre ESP32
```

### Logs Détaillés
```bash
# Ajouter avant server.listen():
console.log('[DEBUG] Serveur en mode verbose');

# Redémarrer serveur
```

---

## 💾 Fichiers Créés

```
UniSmartPay/
├── node_rfid_server.js       [NOUVEAU - Serveur principal]
├── node_modules/             [Créé par npm - Dépendances]
├── package.json              [Créé par npm - Config]
├── package-lock.json         [Créé par npm - Lock]
└── web/
    └── php/
        └── admin/
            └── web/api/terminal/log.php  [API PHP Backend]
```

---

## 📝 Checklist Déploiement

- [ ] Node.js 14+ installé
- [ ] npm install exécuté (pas d'erreurs)
- [ ] node_rfid_server.js existant
- [ ] Première démarrage: `node node_rfid_server.js`
- [ ] Page http://localhost:3000 charge
- [ ] Port sériel connecté (status dot vert)
- [ ] UID s'affiche quand scanned
- [ ] API PHP reçoit les données

---

## 🚀 Production?

### Pour Garder Server Actif (Production)

#### Option 1: PM2 (Recommandé)
```bash
# Installer:
npm install -g pm2

# Démarrer:
pm2 start node_rfid_server.js

# Auto-redémarrer après crash:
pm2 startup
pm2 save
```

#### Option 2: systemd (Linux)
```bash
# Créer: /etc/systemd/system/rfid.service
[Service]
ExecStart=/usr/bin/node /home/user/UniSmartPay/node_rfid_server.js
Restart=always

# Activer:
sudo systemctl enable rfid
sudo systemctl start rfid
```

#### Option 3: Docker
```dockerfile
FROM node:14
WORKDIR /app
COPY . .
RUN npm install
CMD ["node", "node_rfid_server.js"]
```

---

## 📞 Support

- **Erreur Node?** Vérifier `node --version`
- **Port déjà utilisé?** `lsof -i :3000`
- **Serial pas trouvé?** `ls /dev/tty*` (Linux) ou Device Manager (Windows)
- **Serveur crash?** Regarde console avant Ctrl+C

---

## 🎯 TL;DR

```bash
1. npm install express socket.io serialport
2. node node_rfid_server.js
3. Ouvrir http://localhost:3000
4. Connecter port sériel (ex: COM3)
5. Scannez une carte
6. ✅ UID s'affiche!
```

---

## 🔗 Fichiers Associés

- **node_rfid_server.js** - Ce fichier
- **serial_api_modern.php** - Interface web de test
- **web/api/terminal/log.php** - API PHP backend (log des scans/événements)
- **dashboard_fixed.php** - Intégration admin

✨ **Total Setup: 10 minutes max!** ✨
