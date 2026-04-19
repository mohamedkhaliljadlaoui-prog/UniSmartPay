/*
  UniSmart Pay - Code ESP32 avec RFID
  Lecteur RFID + Affichage LCD + Communication API
  
  Matériel:
  - ESP32
  - Lecteur RFID RC522
  - Écran LCD 16x2 I2C
  - Boutons de sélection
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>

// ============================================
// CONFIGURATION
// ============================================

// WiFi
const char* SSID = "YOUR_SSID";
const char* PASSWORD = "YOUR_PASSWORD";
const char* API_URL = "http://192.168.1.100:5000";

// RFID (RC522)
#define SS_PIN 5
#define RST_PIN 27
MFRC522 rfid(SS_PIN, RST_PIN);

// LCD I2C (Adresse 0x27, 16x2)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Pins GPIO
#define BUTTON_PAIEMENT 12
#define BUTTON_SOLDE 13
#define BUTTON_RECHARGE 14
#define LED_SUCCES 25
#define LED_ERREUR 26
#define BUZZER 32

// ============================================
// VARIABLES GLOBALES
// ============================================

String lastUID = "";
int mode_actuel = 0; // 0: Attente, 1: Paiement, 2: Solde, 3: Recharge
float montant_paiement = 5.50; // Montant par défaut (sandwich buvette)
int id_terminal = 1;

// ============================================
// SETUP
// ============================================

void setup() {
  Serial.begin(115200);
  
  // Initialiser pins
  pinMode(BUTTON_PAIEMENT, INPUT_PULLUP);
  pinMode(BUTTON_SOLDE, INPUT_PULLUP);
  pinMode(BUTTON_RECHARGE, INPUT_PULLUP);
  pinMode(LED_SUCCES, OUTPUT);
  pinMode(LED_ERREUR, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  
  digitalWrite(LED_SUCCES, LOW);
  digitalWrite(LED_ERREUR, LOW);
  noTone(BUZZER);
  
  // LCD
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("UniSmart Pay");
  lcd.setCursor(0, 1);
  lcd.print("Initialisation...");
  
  // SPI & RFID
  SPI.begin();
  rfid.PCD_Init();
  
  delay(1000);
  
  // WiFi
  connectToWiFi();
  
  // Mode attente
  afficherMenuAttente();
}

// ============================================
// LOOP PRINCIPALE
// ============================================

void loop() {
  // Vérifier boutons
  if (digitalRead(BUTTON_PAIEMENT) == LOW) {
    delay(50);
    if (digitalRead(BUTTON_PAIEMENT) == LOW) {
      mode_actuel = 1;
      afficherModePaiement();
      while (digitalRead(BUTTON_PAIEMENT) == LOW) delay(10);
      delay(200);
    }
  }
  
  if (digitalRead(BUTTON_SOLDE) == LOW) {
    delay(50);
    if (digitalRead(BUTTON_SOLDE) == LOW) {
      mode_actuel = 2;
      afficherModeSolde();
      while (digitalRead(BUTTON_SOLDE) == LOW) delay(10);
      delay(200);
    }
  }
  
  if (digitalRead(BUTTON_RECHARGE) == LOW) {
    delay(50);
    if (digitalRead(BUTTON_RECHARGE) == LOW) {
      mode_actuel = 3;
      afficherModeRecharge();
      while (digitalRead(BUTTON_RECHARGE) == LOW) delay(10);
      delay(200);
    }
  }
  
  // Lire RFID
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    String uid = getUID();
    
    if (uid != lastUID) {
      lastUID = uid;
      traiterCarteRFID(uid);
    }
    
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
  }
  
  delay(100);
}

// ============================================
// FONCTIONS WIFI
// ============================================

void connectToWiFi() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi...");
  
  WiFi.begin(SSID, PASSWORD);
  int tentatives = 0;
  
  while (WiFi.status() != WL_CONNECTED && tentatives < 20) {
    delay(500);
    Serial.print(".");
    tentatives++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi connecté");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi OK");
    delay(1000);
  } else {
    Serial.println("\n❌ Erreur WiFi");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi ERREUR");
    delay(2000);
  }
}

// ============================================
// FONCTIONS RFID
// ============================================

String getUID() {
  String uid = "";
  for (int i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  return uid;
}

void traiterCarteRFID(String uid) {
  switch (mode_actuel) {
    case 1:
      effectuerPaiement(uid);
      break;
    case 2:
      afficherSolde(uid);
      break;
    case 3:
      rechargerCompte(uid);
      break;
  }
}

// ============================================
// FONCTIONS PAIEMENT
// ============================================

void effectuerPaiement(String uid) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Traitement...");
  
  StaticJsonDocument<200> doc;
  doc["uid"] = uid;
  doc["montant"] = montant_paiement;
  doc["id_terminal"] = id_terminal;
  doc["description"] = "Paiement buvette";
  
  String payload;
  serializeJson(doc, payload);
  
  HTTPClient http;
  http.begin(API_URL + String("/api/paiement"));
  http.addHeader("Content-Type", "application/json");
  
  int httpCode = http.POST(payload);
  String response = http.getString();
  http.end();
  
  if (httpCode == 200) {
    Serial.println("✅ Paiement réussi");
    afficherSucces("Paiement OK", montant_paiement);
  } else {
    Serial.println("❌ Paiement échoué");
    afficherErreur("Paiement ECHOUE");
  }
}

void afficherSolde(String uid) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Recherche...");
  
  HTTPClient http;
  http.begin(API_URL + String("/api/solde/1000"));
  
  int httpCode = http.GET();
  String response = http.getString();
  http.end();
  
  if (httpCode == 200) {
    StaticJsonDocument<200> doc;
    deserializeJson(doc, response);
    float solde = doc["solde"];
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Solde:");
    lcd.setCursor(0, 1);
    lcd.print(String(solde) + " EUR");
    
    tone(BUZZER, 1000, 200);
  }
}

void rechargerCompte(String uid) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Recharge non impl.");
  delay(2000);
}

// ============================================
// FONCTIONS AFFICHAGE LCD
// ============================================

void afficherMenuAttente() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Paiement: B1");
  lcd.setCursor(0, 1);
  lcd.print("Solde: B2 | B3");
}

void afficherModePaiement() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MODE: PAIEMENT");
  lcd.setCursor(0, 1);
  lcd.print("Passez carte...");
}

void afficherModeSolde() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MODE: CONSULTER");
  lcd.setCursor(0, 1);
  lcd.print("Passez carte...");
}

void afficherModeRecharge() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MODE: RECHARGE");
  lcd.setCursor(0, 1);
  lcd.print("Passez carte...");
}

void afficherSucces(String titre, float montant) {
  digitalWrite(LED_SUCCES, HIGH);
  tone(BUZZER, 2000, 300);
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(titre);
  lcd.setCursor(0, 1);
  lcd.print("-" + String(montant) + " EUR");
  
  delay(3000);
  
  digitalWrite(LED_SUCCES, LOW);
  afficherMenuAttente();
}

void afficherErreur(String message) {
  digitalWrite(LED_ERREUR, HIGH);
  tone(BUZZER, 500, 500);
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("ERREUR");
  lcd.setCursor(0, 1);
  lcd.print(message);
  
  delay(3000);
  
  digitalWrite(LED_ERREUR, LOW);
  afficherMenuAttente();
}
