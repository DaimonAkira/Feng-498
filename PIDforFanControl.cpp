#include <NewPing.h> // HC-SR04 kütüphanesi
#include <PID_v1.h> // PID kütüphanesi

#define TRIGGER_PIN  12 // HC-SR04 trig pin
#define ECHO_PIN     11 // HC-SR04 echo pin
#define FAN_PIN      9  // Fan pin
#define MAX_DISTANCE 200 // HC-SR04'nin algılayabileceği maksimum mesafe (cm)
#define SETPOINT     1  // Hedef mesafe (cm)

double input, output, setpoint, error, last_error;
bool system_running = false;

NewPing sonar(TRIGGER_PIN, ECHO_PIN, MAX_DISTANCE); // HC-SR04 sensörünü başlat
PID pid(&error, &output, &setpoint, 1, 0.1, 0.05, DIRECT); // PID kontrolcüsü

int current_fan_speed = 0;
double last_input = 0; // Son ölçüm değeri

void setup() {
  Serial.begin(9600);
  pid.SetMode(AUTOMATIC); // PID'yi otomatik modda başlat
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
      analogWrite(FAN_PIN, 0);
    }
  }

  if (system_running) {
    // HC-SR04 sensöründen mesafe ölçümü al
    unsigned int distance = sonar.ping_cm();
    if (distance == 0 || distance > MAX_DISTANCE) {
      // 0 veya maksimum mesafeden fazla bir ölçüm alındıysa geçersiz kabul et
      // Önceki geçerli ölçümü kullanarak devam et
      input = last_input;
    } else {
      // Geçerli bir ölçüm alındıysa kullan
      input = distance;
      last_input = input;
    }

    setpoint = SETPOINT; // Hedef mesafeyi belirle

    error = setpoint - input; // Hata değerini hesapla

    pid.Compute(); // PID hesaplamalarını yap

    int target_fan_speed = map(output, -255, 255, 0, 255); // Hedef fan hızını ayarla
    if (target_fan_speed < 0) target_fan_speed = 0; // Negatif hızları önle

    // Yumuşak geçiş yap
    if (target_fan_speed > current_fan_speed) {
      for (int i = current_fan_speed; i <= target_fan_speed; i++) {
        analogWrite(FAN_PIN, i);
        delay(10);
      }
    } else if (target_fan_speed < current_fan_speed) {
      for (int i = current_fan_speed; i >= target_fan_speed; i--) {
        analogWrite(FAN_PIN, i);
        delay(10);
      }
    }
    current_fan_speed = target_fan_speed;
    if(distance<=100)
    {
      digitalWrite(LED_BUILTIN, LOW);
    }else{
      digitalWrite(LED_BUILTIN, HIGH);   // LED'i söndür
    }
    Serial.print("Distance: ");
    Serial.print(input);
    Serial.print(" cm, Fan Speed: ");
    Serial.println(current_fan_speed);


    delay(100); // Ölçüm aralığını belirle
  }
}