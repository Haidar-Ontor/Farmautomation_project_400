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
const char* serverUrl = "https://farmautomation400.site/ping.php";

// Pin definitions
#define DHTPIN 32
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

const int soilMoisturePin1 = 34; // Soil moisture sensor 1
const int soilMoisturePin2 = 35; // Soil moisture sensor 2
const int waterLevelPin1 = 33;   // Water level sensor 1
const int waterLevelPin2 = 39;   // Water level sensor 2 (ensure not conflicting with WiFi)
const int lightPin = 36;         // Move light sensor to ADC1 Pin (e.g., 36 or 39)

// Control pins
const int pumpF1Pin = 23;
const int pumpF2Pin = 19;
const int pumpF3Pin = 18;
const int fanPin = 5;
const int lightControlPin = 4;
const int device_id = 2;

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  // Initialize sensors
  dht.begin();

  // Initialize control pins
  pinMode(pumpF1Pin, OUTPUT);
  pinMode(pumpF2Pin, OUTPUT);
  pinMode(pumpF3Pin, OUTPUT);
  pinMode(fanPin, OUTPUT);
  pinMode(lightControlPin, OUTPUT);

  // Connect to WiFi
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    sendSensorData();
    fetchControlCommands();
    sendPing();
  }
  delay(5000); // Adjust the delay as needed
}

void sendSensorData() {
  // Read sensor data
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  int soilMoisture1 = analogRead(soilMoisturePin1);
  int soilMoisture2 = analogRead(soilMoisturePin2);
  int waterLevel1 = analogRead(waterLevelPin1);
  int waterLevel2 = analogRead(waterLevelPin2);
  int lightLevel = analogRead(lightPin);

  // Create JSON object
  DynamicJsonDocument jsonDoc(256);
  jsonDoc["C_Temp"] = temperature;
  jsonDoc["C_Humidity"] = humidity;
  jsonDoc["F1_SoilM"] = soilMoisture1;
  jsonDoc["F2_SoilM"] = soilMoisture2;
  jsonDoc["C_WaterL"] = waterLevel1;
  jsonDoc["Pond_Water_Level"] = waterLevel2;
  jsonDoc["C_Light"] = lightLevel;

  // Convert JSON object to string
  String jsonString;
  serializeJson(jsonDoc, jsonString);

  // Send data to server
  HTTPClient http;
  http.begin(sensorDataUrl);
  http.addHeader("Content-Type", "application/json");

  int httpResponseCode = http.POST(jsonString);
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println(jsonString);
    Serial.print("Sensor data sent, response: ");
    Serial.println(response);
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
    Serial.print("Control command response: ");
    Serial.println(response);

    // Parse JSON response
    DynamicJsonDocument jsonDoc(512);
    DeserializationError error = deserializeJson(jsonDoc, response);
    if (error) {
      Serial.printf("Failed to parse JSON, error: %s\n", error.c_str());
      return;
    }

    // Update control statuses based on server response
    int pumpF1Status = jsonDoc["Pump_f1"];
    int pumpF2Status = jsonDoc["Pump_f2"];
    int pumpF3Status = jsonDoc["Pump_f3"];
    int fanStatus = jsonDoc["C_Fan"];
    int lightStatus = jsonDoc["C_Light"];

    // Control devices based on received commands
    digitalWrite(pumpF1Pin, pumpF1Status == 1 ? HIGH : LOW);
    digitalWrite(pumpF2Pin, pumpF2Status == 1 ? HIGH : LOW);
    digitalWrite(pumpF3Pin, pumpF3Status == 1 ? HIGH : LOW);
    digitalWrite(fanPin, fanStatus == 1 ? HIGH : LOW);
    digitalWrite(lightControlPin, lightStatus == 1 ? HIGH : LOW);

  } else {
    Serial.printf("Failed to fetch control commands, error: %s\n", http.errorToString(httpResponseCode).c_str());
  }
  http.end();
}

void sendPing() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    // Convert serverUrl to String and concatenate parameters
    String url = String(serverUrl) + "?device_id=" + String(device_id) + "&action=ping";
    
    http.begin(url);
    int httpResponseCode = http.GET();
    
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Ping Response: " + response);
    } else {
      Serial.println("Error in HTTP request: " + String(httpResponseCode));
    }
    
    http.end();
  } else {
    Serial.println("Not connected to WiFi");
  }
}

