#include <Servo.h>
#include <NewPing.h> // HC-SR04 kütüphanesi

#define TRIGGER_PIN  12 // HC-SR04 trig pin
#define ECHO_PIN     11 // HC-SR04 echo pin
#define FAN_PIN      9  // Fan pin
#define MAX_DISTANCE 200 // HC-SR04'nin algılayabileceği maksimum mesafe (cm)
#define SETPOINT     1  // Hedef mesafe (cm)
#define ESCMAXSPEED 2000 // ESC Max Speed
#define ESCMINSPEED 1000 // ESC Min Speed

double input, output, setpoint, error, last_error;
bool system_running = false;
bool calibration = false;

NewPing sonar(TRIGGER_PIN, ECHO_PIN, MAX_DISTANCE); // HC-SR04 sensörünü başlat

int current_fan_speed = 0;
double last_input = 0; // Son ölçüm değeri

Servo esc;
int escValue;

void setup() {
  Serial.begin(9600);

  pinMode(FAN_PIN, OUTPUT); // Fan pini çıkış olarak ayarla
  pinMode(LED_BUILTIN, OUTPUT);
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    if (command == "start") {
      system_running = true;
    } else if (command == "stop") {
      system_running = false;
      esc.writeMicroseconds(ESCMINSPEED);
    }
  }

  if (system_running) {
    if(!calibration){
      Serial.println("ESC calibration started......");
      esc.attach(9);  // ESC sinyal pini 9'a bağlıysa, bu pini ayarlayın
      delay(2000);    // Başlamadan önce kısa bir gecikme
      esc.writeMicroseconds(ESCMAXSPEED);  // Maksimum hız için sinyal gönderin (örneğin 2000)
      delay(4000);    // ESC'nin bu sinyali tanıması için yeterli zamanı vermek için bekleyin
      esc.writeMicroseconds(ESCMINSPEED);  // Minimum hız için sinyal gönderin (örneğin 1000)
      delay(4000);    // ESC'nin bu sinyali tanıması için yeterli zamanı vermek için bekleyin
      calibration=true;
      Serial.println("Calibration Done...");
    }

    // HC-SR04 sensöründen mesafe ölçümü al
    int distance = sonar.ping_cm();
    if (distance == 0 || distance > MAX_DISTANCE) {
      // 0 veya maksimum mesafeden fazla bir ölçüm alındıysa geçersiz kabul et
      // Önceki geçerli ölçümü kullanarak devam et
      input = last_input;
    } else {
      // Geçerli bir ölçüm alındıysa kullan
      input = distance;
      last_input = input;
    }

    int escValue = map(distance, 0, 200, 1100, 2000); // Hedef fan hızını ayarla
    escValue = constrain(escValue,1100,2000);
      Serial.print("Mesafe: ");
      Serial.print(distance); // Ölçülen mesafeyi yazdır
      Serial.print(" cm");
      Serial.print("ESC Value: ");
      Serial.println(escValue);
      esc.writeMicroseconds(escValue);
      

    if(distance <= 50) {
      digitalWrite(LED_BUILTIN, LOW);
    } else {
      digitalWrite(LED_BUILTIN, HIGH);   // LED'i söndür
    }

    delay(1); // Ölçüm aralığını belirle
  }
}
