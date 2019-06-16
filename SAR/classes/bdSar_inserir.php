<?php
    class processa_dados {

        // variaveis
        private $local;
        private $umidade;
        private $presenca;
        private $temperatura;
        private $gas;

        private $dic;

        private function obter (){
            do {
                $conexao = curl_init("http://dweet.io/get/dweets/for/sar_sistem");
                curl_setopt($conexao, CURLOPT_RETURNTRANSFER, true);
                $resultado = curl_exec($conexao);
                $dado = json_decode($resultado);
                curl_close($conexao);
            }while (!isset ($dado -> with)&&$dado -> with != 403);

            $this -> local = $dado -> with[0] -> content -> l;
            $this -> umidade = $dado -> with[0] -> content -> u;
            $this -> presenca = $dado -> with[0] -> content -> m;
            $this ->temperatura = $dado -> with[0] -> content -> t;
            $this -> gas = $dado -> with[0] -> content -> g;
        }

        private function gerar (){
            $this -> dic = array (
                "local" => $this -> local,
                "umidade" => $this -> umidade,
                "presenca" => $this -> presenca,
                "temperatura" => $this -> temperatura,
                "gas" => $this -> gas
            );

        }

        private function tratar (){

        }

        protected function retornar (){
            $this -> obter ();
            $this -> tratar ();
            $this -> gerar ();
            return $this -> dic;
        }
    }

    class bd_sar extends processa_dados{

    // variaveis da classe
        /*dados do banco*/
        private $host;
        private $dname;
        private $user;
        private $password;
        private $tipo = "mysql";

        /*banco*/
        private $seguro;
        private $banco;

    // destruct e construct
        function __construct ($xhost, $xdname, $xuser, $xpassword){
            $this -> host = $xhost;
            $this -> dname = $xdname;
            $this -> user = $xuser;
            $this -> password = $xpassword;
        }

        function __destruct (){
            unset ($this -> host);
            unset ($this -> dname);
            unset ($this -> user);
            unset ($this -> password);
        }

    // funções privadas de uso interno
        private function conectar_db (){
            $this -> banco = new PDO (
                $this -> tipo.":host=".$this -> host.";dbname=".$this -> dname, $this -> user, $this -> password
            );
        }

        private function existe ($cod){
            $this -> seguro = $this -> banco -> prepare ("select * from dispositivos where codigo ='{$cod}'");
            $this -> seguro -> execute ();
            $linha = $this -> seguro -> fetchAll (PDO::FETCH_ASSOC);
            return $linha;
        }


        private function inserir_dados_bd (){
            $val = $this -> retornar();
            $array_banco = $this -> existe ($val["local"]);
            if (count ($array_banco) > 0){
                $this -> seguro = $this -> banco -> prepare ("    
                    INSERT INTO 
                    dados
                        (
                            et_dispositivo, 
                            umidade, 
                            temperatura, 
                            presenca, 
                            gas
                        )
                    VALUES
                    (
                        :id,
                        :umi,
                        :tem,
                        :pre,
                        :gas
                    )
                ");
                $this -> seguro -> bindValue (":id", $array_banco[0]["id_dispositivo"]);
                $this -> seguro -> bindValue (":umi", $val["umidade"]);
                $this -> seguro -> bindValue (":tem", $val["temperatura"]);
                $this -> seguro -> bindValue (":pre", $val["presenca"]);
                $this -> seguro -> bindValue (":gas", $val["gas"]);
                $this -> seguro -> execute ();
            }
        }

    // funções publicaas de acesso externo
        public function conectar (){
            $this -> conectar_db();;
        }

        public function inserir_dados (){
            $this -> inserir_dados_bd();
        }


    }
?>