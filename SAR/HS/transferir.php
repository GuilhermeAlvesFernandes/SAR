<?php


// inclusão de codigo php
    include ("../classes/bdSar_inserir.php");


// codigo php
    $obj = new bd_sar("localhost", "grupo2", "root", "rmj15380");
    $obj->conectar();
    while (true){
        $obj->inserir_dados();
        sleep (5);
    }
    $obj->inserir_dados();
    /*http://dweet.io/get/latest/dweet/for/sar_sistem*/
    /*http://dweet.io/get/dweets/for/sar_sistem*/
?>