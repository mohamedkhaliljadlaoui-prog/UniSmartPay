# MODULE SERIAL MONITOR (Journal temps réel)

Ce projet fournit un "Serial Monitor" professionnel sous deux formes :

1) **Arduino Serial Monitor (ESP32)** : logs au format `[HH:MM:SS] [TYPE] MESSAGE`.
2) **Web Serial Monitor (UI Terminal)** : panneau "Logs du terminal" qui lit les messages stockés dans la table MySQL `code_appareil`.
3) **Web Serial Monitor (USB / COM)** : panneau "Serial Monitor (USB)" qui lit directement le port série (comme Arduino IDE) depuis le navigateur.

> Bonus : l'ESP32 et l'interface web peuvent aussi **pousser des logs en base** via l'API `POST /web/api/terminal/log.php`.

---

## Formats

Format standard :

```
[HEURE] [TYPE] MESSAGE
```

Types recommandés :

- INFO, SUCCESS, ERROR, WARNING
- SCAN, PANIER
- RFID
- PAIEMENT, RESPONSE
- SOLDE
- USER, RESTO

---

## MODE BUVETTE — Serial Monitor

Le système affiche en temps réel :

1. **Scan produits**
   - Nom produit
   - Prix
   - Code barre

2. **Panier**
   - Total actuel après chaque ajout / suppression

3. **Paiement (commande terminal)**
   - Montant total envoyé
   - Statut (SUCCESS / ERROR)
   - Référence si succès

4. **Réponse serveur**
   - Statut
   - Message

Sources de logs :
- Web (scan + panier + création commande)
- ESP32 (RFID + envoi paiement + réponse + solde)

---

## MODE RESTO — Serial Monitor

Le système affiche en temps réel :

1. **RFID**
   - UID détecté

2. **Infos paiement**
   - Prix ticket resto (côté ESP32)

3. **Paiement**
   - Envoi au serveur
   - Réponse

4. **Résultat**
   - SUCCESS ou ERROR
   - Solde après paiement si disponible

---

## APIs

### Lire les logs (Web UI)

- `GET /web/api/terminal_logs.php?terminal_id=1&limit=25`

### Ajouter un log (Web/ESP32)

- `POST /web/api/terminal/log.php`

Payload JSON :

```json
{
  "id_terminal": 1,
  "type_message": "RFID",
  "message": "Carte détectée : AA:BB:CC:DD",
  "uid_carte": "AABBCCDD",
  "donnees_json": {"amount": 3.5, "mode": "RESTO"}
}
```

---

## Notes réseau (ESP32)

Si l'ESP32 affiche `HTTP status: -11` / `read Timeout` :

- Vérifier que l'ESP32 et le PC sont sur le même WiFi
- Vérifier que l'URL `SERVER_URL` est correcte (IP du PC)
- Vérifier que le PC autorise l'entrée sur le port 80 (Firewall Windows)
- Vérifier que Apache est démarré (XAMPP)

---

## Web Serial (USB / COM)

Tu peux lire la sortie série de l'ESP32 **directement dans le site** (comme Arduino IDE) :

- Ouvre le site avec **Chrome ou Edge (desktop)**
- Va sur:
   - `/terminal/resto.php`
   - `/terminal/buvette.php`
- Dans la section **Serial Monitor (USB)**, clique **Connecter** puis choisis le port (COM)

Contraintes :
- Fonctionne sur **HTTPS** ou sur `http://localhost` / `http://127.0.0.1`
- Ne fonctionne pas sur la plupart des téléphones

---

## Migration MySQL (important)

Si ta base existe déjà, il faut **mettre à jour l'ENUM** `code_appareil.type_message` pour accepter les nouveaux types (RFID/PANIER/SUCCESS/...).

Exécuter (dans MySQL Workbench / phpMyAdmin) :

```sql
ALTER TABLE code_appareil
MODIFY type_message ENUM(
   'INFO','SUCCESS','WARNING','ERROR','ERREUR',
   'SCAN','PANIER','RFID',
   'PAIEMENT','RESPONSE',
   'USER','RESTO','SOLDE',
   'SYSTEME','CONNEXION'
) NOT NULL DEFAULT 'INFO';
```
