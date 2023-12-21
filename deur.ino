
#include "HardwareSerial.h"

#define PIN_SW  12 // PIN_D7
#define PIN_DBG LED_BUILTIN

#define OPEN_MILLIS 1000

String _recv;

void setup() {
  Serial.begin(9600);
  pinMode(PIN_SW, OUTPUT);
  pinMode(PIN_DBG, OUTPUT);
  digitalWrite(PIN_DBG, HIGH);
}

void loop() {
  if(Serial.available() > 0) {
    // digitalWrite(PIN_DBG, LOW);
    _recv = Serial.readStringUntil('\n');
    // if(_recv == "open") {
      Serial.print(_recv);
      Serial.println(" << OPEN RECEIVED");
      digitalWrite(PIN_SW, HIGH);
      delay(OPEN_MILLIS);
      digitalWrite(PIN_SW, LOW);
    // }
  }
}
