# Images à déposer dans ce dossier

Ce dossier est volontairement vide dans le dépôt. Les fichiers LaTeX affichent un **placeholder** si une image manque (le PDF compile quand même).

## 1) Logos (page de garde)
- `logo_utm.png` : logo de l’université/école
- `logo_unismartpay.png` : logo du projet (si vous avez)

Conseil : image PNG avec fond transparent si possible.

## 2) Schémas (conception)
- `architecture.png`
  - But: montrer la vue globale *Navigateur (Admin/Étudiant/Terminal) → PHP/API → MySQL* + *ESP32 → API → MySQL*.
  - Recommandé: un schéma simple, 4–6 blocs maximum.

- `cablage_esp32_rc522.jpg`
  - But: prouver le câblage matériel (ESP32 ↔ RC522 en SPI).
  - Recommandé: photo nette ou schéma (Fritzing) avec noms des pins.

## 3) Captures IHM (rapport + slides)
Ces captures servent à montrer que l’application existe et fonctionne.

- `ui_admin_login.png` : page login admin
- `ui_student_login.png` : page login étudiant
- `ui_admin_dashboard.png` : dashboard admin (gestion étudiants/produits/cartes/recharge)
- `ui_student_dashboard.png` : dashboard étudiant (solde + historique)
- `ui_terminal_resto.png` : page terminal RESTO
- `ui_terminal_buvette.png` : page terminal BUVETTE (scan + panier)
- `ui_payment_resto.png` : résultat paiement RESTO (succès/échec)
- `ui_serial_monitor.png` : Web Serial Monitor (logs ESP32)
- `ui_student_card.png` : détails carte RFID côté étudiant

Captures supplémentaires (optionnelles) si vous voulez détailler l’admin :
- `ui_admin_add_student.png` : formulaire « Ajouter étudiant »
- `ui_admin_assign_card_recharge.png` : bloc « Carte RFID + Recharge »
- `ui_admin_add_product.png` : formulaire « Ajouter produit »

Capture supplémentaire (optionnelle) pour l’introduction :
- `ui_home.png` : page d’accueil / page de choix (accès + terminaux)

Conseils:
- masquer les infos sensibles si nécessaire
- privilégier des captures lisibles (zoom navigateur 110–125%)

## 4) Séquence RFID (3 images)
Ces images expliquent le scénario principal “carte → traitement → résultat”.

- `rfid_step1_read_uid.png`
  - But: montrer la lecture UID (ex: capture Serial Monitor)
- `rfid_step2_api_check.png`
  - But: montrer la vérification côté serveur (réponse API / log / BD)
- `rfid_step3_led_feedback.png`
  - But: montrer le feedback utilisateur (LED verte/rouge)

## 5) Logos

Le rapport attend 2 logos sur la page de garde :
- `logo_utm.png`
- `logo_unismartpay.png`

Si vous n’avez pas de logo projet, vous pouvez utiliser un logo de votre faculté (ex: FST) comme second logo en le renommant en `logo_unismartpay.png`.
