<?php
// Retrieve user input from the form
$ssid = $_POST['ssid'];
$password = $_POST['password'];
$authCode = $_POST['authCode'];
$templateName = $_POST['templateName'];

// Create the code files with the provided values
$pcloudCppContent = <<<EOD
#include "pcloud.h"

pCloud::pCloud(const char* ssid, const char* password, const char* authCode) {
  _ssid = ssid;
  _password = password;
  _authCode = authCode;
  _host = "pcloud.princekakkad.tech";
  _path = "/api/internal/get";
}

void pCloud::setup() {
  Serial.begin(115200);
  WiFi.begin(_ssid, _password);
  Serial.println();
  Serial.print("Connecting...");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println(R"(
  ____             ____   _        ___    _   _   ____  
 |  _ \           / ___| | |      / _ \  | | | | |  _ \ 
 | |_) |  _____  | |     | |     | | | | | | | | | | | |
 |  __/  |_____| | |___  | |___  | |_| | | |_| | | |_| |
 |_|              \____| |_____|  \___/   \___/  |____/ 

)");
  Serial.println("SERVER: pcloud.princekakkad.tech ");
  Serial.print("WIFI SSID: ");
  Serial.println(_ssid);
  Serial.print("IP ADDRESS: ");
  Serial.println(WiFi.localIP());


  pinMode(_PinD0, OUTPUT);
  pinMode(_PinD1, OUTPUT);
  pinMode(_PinD2, OUTPUT);
  pinMode(_PinD3, OUTPUT);
  pinMode(_PinD4, OUTPUT);
  pinMode(_PinD5, OUTPUT);
  pinMode(_PinD6, OUTPUT);
  pinMode(_PinD7, OUTPUT);
  pinMode(_PinD8, OUTPUT);

  _client.setInsecure();
}

void pCloud::loop() {
  updateHardwareStatus();
}

void pCloud::updateHardwareStatus() {
  String url = "https://" + String(_host) + String(_path) + "?token=" + String(_authCode) + "&update=true";

  HTTPClient https;
  https.setReuse(true); // Enable HTTP/1.1 persistent connection

  if (https.begin(_client, url)) {
    int httpCode = https.GET();

    if (httpCode > 0) {
      String response = https.getString();
      Serial.println(response);

      // Parse the response JSON to get the pin statuses
      DynamicJsonDocument doc(512);
      DeserializationError error = deserializeJson(doc, response);

      if (error) {
        Serial.print("deserializeJson() failed: ");
        Serial.println(error.c_str());
      } else {
        // Check if the "data" field is present and is an object
        if (doc.containsKey("data") && doc["data"].is<JsonObject>()) {
          JsonObject data = doc["data"];
          // Update the pin states based on the received data
          int status;

          status = data["D0"].as<int>();
          digitalWrite(_PinD0, status == 0 ? HIGH : LOW);

          status = data["D1"].as<int>();
          digitalWrite(_PinD1, status == 0 ? HIGH : LOW);

          status = data["D2"].as<int>();
          digitalWrite(_PinD2, status == 0 ? HIGH : LOW);

          status = data["D3"].as<int>();
          digitalWrite(_PinD3, status == 0 ? HIGH : LOW);

          status = data["D4"].as<int>();
          digitalWrite(_PinD4, status == 0 ? HIGH : LOW);

          status = data["D5"].as<int>();
          digitalWrite(_PinD5, status == 0 ? HIGH : LOW);

          status = data["D6"].as<int>();
          digitalWrite(_PinD6, status == 0 ? HIGH : LOW);

          status = data["D7"].as<int>();
          digitalWrite(_PinD7, status == 0 ? HIGH : LOW);

          status = data["D8"].as<int>();
          digitalWrite(_PinD8, status == 0 ? HIGH : LOW);
        } else {
          Serial.println("Invalid data format in the response.");
        }
      }
    }

    https.end();
  } else {
    Serial.println("Pcloud is Unable to connect");
  }
}

EOD;

$pcloudHContent = <<<EOD
#ifndef PCLOUD_H
#define PCLOUD_H

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

class pCloud {
  public:
    pCloud(const char* ssid, const char* password, const char* authCode);
    void setup();
    void loop();

  private:
    const char* _ssid;
    const char* _password;
    const char* _authCode;
    const char* _host;
    const char* _path;
    WiFiClientSecure _client;

    const int _PinD0 = 16;
    const int _PinD1 = 5;
    const int _PinD2 = 4;
    const int _PinD3 = 0;
    const int _PinD4 = 2;
    const int _PinD5 = 14;
    const int _PinD6 = 12;
    const int _PinD7 = 13;
    const int _PinD8 = 15;

    void updateHardwareStatus();
};

#endif

EOD;

$pcloudInoContent = <<<EOD
/*
Project Name: $templateName Template
Description:  This project demonstrates how to connect an ESP8266 device to a cloud server using the pCloud library. 
              It allows controlling the state of multiple relays remotely through the cloud server.
Author: Prince Kakkad
License: MIT License

Disclaimer: If you have downloaded this project using the download option in pCloud for your template, then no changes are required.
            If the ESP8266 device is not connecting to your Wi-Fi network, please check the SSID and password again.

©2023 princekakkad.tech. All Rights Reserved.
*/

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include "pcloud.h"

const char* ssid = "$ssid";
const char* password = "$password";
const char* authCode = "$authCode";

pCloud cloud(ssid, password, authCode);

void setup() {
  cloud.setup();
}

void loop() {
  cloud.loop();
}
EOD;

$readmeContent = <<<EOD
# Project Name: $templateName

## Description
This project demonstrates how to connect an ESP8266 device to a cloud server using the pCloud library. It allows controlling the state of multiple relays remotely through the cloud server.

## Prerequisites
- ESP8266 board
- Arduino IDE
- Libraries:
  - ESP8266WiFi
  - ESP8266HTTPClient
  - WiFiClientSecure
  - ArduinoJson

## Installation
1. Clone or download this project repository.
2. Open the project in the Arduino IDE.
3. Install the required libraries mentioned in the "Prerequisites" section.
4. Connect your ESP8266 device to your computer.
5. Configure the following variables in the `pcloud.ino` file:
   - `ssid`: The SSID of your Wi-Fi network.
   - `password`: The password of your Wi-Fi network.
   - `authCode`: The authentication code provided by the cloud server.

   Note: If you have downloaded this using download in pcloud for your template then no need to change all this.

6. Upload the code to your ESP8266 device.

## Usage
1. Power up your ESP8266 device.
2. The device will connect to the Wi-Fi network and the cloud server.
3. The relays can now be controlled remotely through the cloud server.

## Troubleshooting
  - If you have downloaded this project using the download option in pCloud for your template, then you do not need to make any changes to the files or code.

  - In case your ESP8266 device is not connecting to your Wi-Fi network, please follow these steps:

  - Double-check the ssid and password in the pcloud.ino file. Make sure they match the credentials of your Wi-Fi network.
  - Ensure that your ESP8266 device is within the range of your Wi-Fi router.
  - Verify that your Wi-Fi router is functioning correctly and has an active internet connection.

## License
This project is licensed under the [MIT License].

EOD;

// Create a temporary directory for the code files
$tempDir = uniqid('switch_', true);
mkdir($tempDir);

// Generate the pcloud directory path
$pcloudDir = $tempDir . '/pcloud';

// Create the pcloud directory
mkdir($pcloudDir);

// Save the code files in the pcloud directory
file_put_contents($pcloudDir.'/pcloud.cpp', $pcloudCppContent);
file_put_contents($pcloudDir.'/pcloud.h', $pcloudHContent);
file_put_contents($pcloudDir.'/pcloud.ino', $pcloudInoContent);
file_put_contents($pcloudDir.'/README.md', $readmeContent); 

// Generate the ZIP file
$zipFilename = $templateName.'.zip';
$zip = new ZipArchive();
$zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Add the pcloud directory and its contents to the ZIP file
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($pcloudDir)
);
foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = 'pcloud/' . basename($filePath);
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

// Set the appropriate headers for downloading the ZIP file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$zipFilename.'"');
header('Content-Length: ' . filesize($zipFilename));

// Send the ZIP file to the user
readfile($zipFilename);

// Clean up the temporary directory and files
unlink($pcloudDir.'/pcloud.cpp');
unlink($pcloudDir.'/pcloud.h');
unlink($pcloudDir.'/pcloud.ino');
unlink($pcloudDir.'/README.md');
rmdir($pcloudDir);
rmdir($tempDir);
unlink($zipFilename);
?>