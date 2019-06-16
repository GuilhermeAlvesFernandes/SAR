
// inclusões de bibliotecas
# include "DHT.h"
# include <WiFi.h>
# include "soc/soc.h" 
# include "soc/rtc_cntl_reg.h"

// variaveis do programa
  // codigo do dispositivo
# define codigo 1

  // pinos
# define movimento 16
# define temperaturaUmidade 18
# define gas 27
# define tipoDHT DHT11

  // objetos
DHT dht(temperaturaUmidade, tipoDHT); // objeto de manipulação e inicialização do dth

  // constantes do wifi
char * redessid = "INTELBRAS";
char * redepassword = "13102311";
char* host = "dweet.io";
char * nomecoisa = "sar_sistem";
int porta = 80; 

void setup() {
// sentando dados iniciais
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); // desativa brunnowt
  Serial.begin (115200); // seta valor de monitor serial

  // definição de tipo de pino
  pinMode (gas, INPUT);
  pinMode (movimento, INPUT);
  
  // realiza conecção com wifi
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  WiFi.begin (redessid, redepassword);
  Serial.print ("WIFI Conectando");
  while (WiFi.status () != WL_CONNECTED){
    Serial.print (".");
    delay (500);
  }
  Serial.println ();
  Serial.print ("WIFI Conectado: ");
  Serial.println (WiFi.localIP ());

  // inicializando dht
  dht.begin();
}

void loop() {

  float u = dht.readHumidity();
  float t = dht.readTemperature();
  int m = digitalRead (movimento);
  float g = analogRead (gas);

    // mostra as leituras obtidas
  Serial.print ("Umidade: ");
  Serial.println (u);
  Serial.print ("Temperatura: ");
  Serial.println (t);
  Serial.print ("gas: ");
  Serial.println (g);
  Serial.print  ("movimento: ");
  Serial.println (m);
  
  // realiza conecção com servidor
  WiFiClient client;
  while (true){
    if (client.connect (host, porta)){
      Serial.println ("SERVIDOR conecção realizada com sucesso servidor");
      break;
    }
    else {
      Serial.println (" SERVIDOR falha ao se conectar ao servidor");
    }
 
  }

  // gera url
  String url = "/dweet/for/";
  url += nomecoisa;
  url += "?u=";
  url += u;
  url += "&l=";
  url += codigo;
  url += "&t=";
  url += t;
  url += "&m=";
  url += m;
  url += "&g=";
  url += g;

  Serial.print ("URL: ");
  Serial.println (url);

  // envia dados para o servidor
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
         "Host: " + host + "\r\n" +
         "Connection: close\r\n\r\n");
         
 // resposta do servidor
  while(client.available()) {
    String line = client.readStringUntil('\r');
    Serial.print (line);
  }

  Serial.println();
  Serial.println("Fechando conexao");
  delay (5000);
}
