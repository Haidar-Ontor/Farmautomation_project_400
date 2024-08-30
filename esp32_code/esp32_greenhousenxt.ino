#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h> 
// WiFi credentials
const char* ssid = "TOTOLINK_N300RT";
const char* password = "bohubrihi";

// Server endpoints
const char* sensorDataUrl = "https://farmautomation400.site/sensor_data.php";
const char* controlCommandUrl = "https://farmautomation400.site/control.php";
const char* triggerUrl="https://farmautomation400.site/fetch_sensor.php"

// Pin definitions (ensure these are valid for ESP32)
//const int temperaturePin = 34; // Example analog pin for temperature sensor
//const int humidityPin = 35;    // Example analog pin for humidity sensor
const int soilMoisturePin = 34; // Example analog pin for soil moisture sensor
const int lightPin = 33;       // Example analog pin for light sensor

#define DHTPIN 32
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);  
// Control pins
const int humidifierPin = 12;
const int waterPumpPin = 13;
const int lightControlPin = 14;
const int fanPin = 27;
const int heaterPin = 26;
const int doorPin = 25;


void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  // Initialize control pins
  pinMode(humidifierPin, OUTPUT);
  pinMode(waterPumpPin, OUTPUT);
  pinMode(lightControlPin, OUTPUT);
  pinMode(fanPin, OUTPUT);
  pinMode(doorPin, OUTPUT);
  pinMode(heaterPin, OUTPUT);

  pinMode(lightPin,INPUT);
  pinMode(soilMoisturePin, INPUT);

  dht.begin();
  // Connect to WiFi with timeout
  unsigned long startAttemptTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < 30000) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Failed to connect to WiFi");
  } else {
    Serial.println("Connected to WiFi");
  }
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    sendSensorData();
    fetchControlCommands();
  }
  delay(5000); // Adjust the delay as needed
}

void sendSensorData() {

  int temperatureValue = dht.readTemperature();
  int humidityValue =dht.readHumidity();
 // int temperatureValue = analogRead(temperaturePin);
 // int humidityValue = analogRead(humidityPin);
  int soilMoistureValue = analogRead(soilMoisturePin);
  int lightValue = analogRead(lightPin);
  Serial.println(lightValue);

  int humidifierStatus = digitalRead(humidifierPin);
  int waterPumpStatus = digitalRead(waterPumpPin);
  int lightStatus = digitalRead(lightControlPin);
  int fanStatus = digitalRead(fanPin);
  int doorStatus = digitalRead(doorPin);
  int heaterStatus= digitalRead(heaterPin);

  // Create JSON object
  DynamicJsonDocument jsonDoc(256);
  jsonDoc["temperature"] = temperatureValue;
  jsonDoc["humidity"] = humidityValue;
  jsonDoc["soilMoisture"] = soilMoistureValue;
  jsonDoc["light"] = lightValue;

  jsonDoc["humidifierStatus"] = humidifierStatus;
  jsonDoc["waterPumpStatus"] = waterPumpStatus;
  jsonDoc["lightStatus"] = lightStatus;
  jsonDoc["fanStatus"] = fanStatus;
  jsonDoc["doorStatus"] = doorStatus;
  jsonDoc["heaterStatus"]=heaterStatus;

  String jsonString;
  serializeJson(jsonDoc, jsonString);

  HTTPClient http;
  http.begin(sensorDataUrl);
  http.addHeader("Content-Type", "application/json");

  int httpResponseCode = http.POST(jsonString);
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Sensor data sent, response:");
    Serial.print(response);
  } else {
    Serial.printf("Failed to send sensor data, error: %s\n", http.errorToString(httpResponseCode).c_str());
  }
  http.end();
}

void fetchControlCommands() {
  HTTPClient http;
  http.begin(controlCommandUrl);
  int httpResponseCode = http.GET();

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Control command response:");
    Serial.print(response);

    // Parse JSON response
    DynamicJsonDocument jsonDoc(256);
    DeserializationError error = deserializeJson(jsonDoc, response);
    if (error) {
      Serial.printf("Failed to parse JSON, error: %s\n", error.c_str());
      return;
    }

    bool success = jsonDoc["success"];
    if (success) {
      int humidifierStatus = jsonDoc["humidifier"];
      int waterPumpStatus = jsonDoc["waterPump"];
      int lightStatus = jsonDoc["light"];
      int fanStatus = jsonDoc["fan"];
      int doorStatus = jsonDoc["door"];
      int heaterStatus = jsonDoc["heater"];
     

      digitalWrite(humidifierPin, humidifierStatus == 1 ? HIGH : LOW);
      digitalWrite(waterPumpPin, waterPumpStatus == 1 ? HIGH : LOW);
      digitalWrite(lightControlPin, lightStatus == 1 ? HIGH : LOW);
      digitalWrite(fanPin, fanStatus == 1 ? HIGH : LOW);
      digitalWrite(doorPin, doorStatus == 1 ? HIGH : LOW);
      digitalWrite(heaterPin, heaterStatus == 1 ? HIGH : LOW);
      
    } else {
      String message = jsonDoc["message"];
      Serial.println("Error: " + message);
    }
  } else {
    Serial.printf("Failed to fetch control commands, error: %s\n", http.errorToString(httpResponseCode).c_str());
  }
  http.end();
}
