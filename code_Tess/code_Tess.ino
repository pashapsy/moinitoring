#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <DHT.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266WebServer.h>

LiquidCrystal_I2C lcd(0x27, 16, 2);

// Inisialisasi MQ2
#define MQ2PIN A0
#define DHTPIN D5

// Inisialisasi LED
#define LED_MERAH D6  
#define LED_BIRU D7 
#define DHTTYPE DHT22   // DHT 22
DHT dht(DHTPIN, DHTTYPE);  

const char* ssid = "LIFEMEDIA";
const char* password = "lifemediajaya";

unsigned long lastDataSendTime = 0;
const unsigned long dataSendInterval = 20000;  

const char *host = "172.16.100.197";

void connectWiFi() {
  Serial.println("Menghubungkan ke WiFi...");
  WiFi.begin(ssid, password);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 20) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Terhubung!");
    Serial.print("IP ESP8266: ");
    Serial.println(WiFi.localIP());
    digitalWrite(LED_MERAH, LOW);
    digitalWrite(LED_BIRU, HIGH);
  } else {
    Serial.println("\nGagal Terhubung ke WiFi!");
    digitalWrite(LED_MERAH, HIGH);
    digitalWrite(LED_BIRU, LOW);
  }
}

void setup() {
  Serial.begin(115200);
  Wire.begin(D2, D1);
  lcd.begin(16, 2);
  lcd.setBacklight(255);

  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_BIRU, OUTPUT);
  pinMode(MQ2PIN, INPUT);

  dht.begin();

  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_BIRU, LOW);

  lcd.setCursor(0, 0);
  lcd.print("Connecting...");
  connectWiFi();
  delay(2000);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  float suhu = dht.readTemperature();
  int asap = analogRead(MQ2PIN);
  unsigned long currentTime = millis();
  String status = "Aman";

  if (isnan(suhu)) {
    Serial.println("Gagal membaca suhu!");
    suhu = -1;
  }

  // Menentukan status berdasarkan suhu dan kadar asap
  if (suhu > 32 || asap > 300) {
    status = "Bahaya";
  } else {
    status = "Aman";
  }

  Serial.print("Suhu: ");
  Serial.print(suhu);
  Serial.print(" C | Asap: ");
  Serial.print(asap);
  Serial.print(" | Status: ");
  Serial.println(status);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Suhu: ");
  if (suhu != -1) {
    lcd.print(suhu, 1);
    lcd.print(" C");
  } else {
    lcd.print("Error");
  }

  lcd.setCursor(0, 1);
  lcd.print("Asap: ");
  lcd.print(asap);

  if (WiFi.status() != WL_CONNECTED) {
    digitalWrite(LED_MERAH, HIGH);
    digitalWrite(LED_BIRU, LOW);
    Serial.println("WiFi Terputus! Mencoba menyambung kembali...");
    connectWiFi();
  } else {
    digitalWrite(LED_MERAH, LOW);
    digitalWrite(LED_BIRU, HIGH);
  }

  // Kirim data setiap 20 detik
  if (WiFi.status() == WL_CONNECTED && millis() - lastDataSendTime >= dataSendInterval) {
    WiFiClient client;
    HTTPClient http;

    Serial.println("Mengirim data ke server...");
    
    String postData = "suhu=" + String(suhu) + "&kadar_asap=" + String(asap) + "&status=" + status;

    String link = "http://172.16.100.197/rombak_codemonitor/kirim.php"; // Pastikan URL benar dan dapat diakses
   
    http.begin(client, link);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpCode = http.POST(postData);
    String payload = http.getString();

    Serial.println(httpCode);   // Print HTTP return code
    Serial.println(payload);
    http.end();
    lastDataSendTime = millis();
  }

  delay(5000);
}
