#include <WiFi.h>
#include <HTTPClient.h>

// Wi-Fi credentials
const char* ssid = "TOTOLINK_N300RT";
const char* password = "bohubrihi";

// Server details
const char* serverUrl = "https://farmautomation400.site/ping.php"; // Replace with your actual server URL


const int device_id = 1; // Change this ID for each device

void setup() {
  Serial.begin(115200);
  connectToWiFi();
}

void loop() {
  sendPing();
  delay(10000); // Send ping every 10 seconds
}

void connectToWiFi() {
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("Connected!");
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
