<?php
    require_once('lib/nusoap.php');

    $wsdl="https://ensip.com/wbs/servicioWsdl.php?wsdl";
    $client=new nusoap_client($wsdl, 'wsdl');
    $client->soap_defencoding = 'UTF-8';

    /* Datos usuario conexion API .data */
    $usuario = ''; //rellenar con usuario api   
    $password = ''; //rellenar con password api

    /* Llamada para obtener el Balance del usuario */
    $datos_balance = array('wb_rm_user'=>$usuario,'wb_rm_password'=>$password,'messageId'=>time());
    $res = $client->call('GetBalance',array($datos_balance));
    echo"<pre>Get Balance: ";
        print_r($res);   
    echo"</pre>";

    /* Datos para la recarga */
    $amount = 10;
    // numeros_prueba = array('999999999999','5300000003','5300000000','test@nauta.com.cu', 'test@nauta.co.cu')
    $number = '5300000000';
    
    $datos_send = array(
        'wb_rm_user'=>$usuario,
        'wb_rm_password'=>$password,
        'messageId'=> time(),
        'PhoneNumber'=>$number,
        'Amount'=>$amount,
        'CountryCode'=>'CU',
        'OperatorCode'=>'CU' //CU NU
     );

    /* Llamada para enviar la recarga */
    $res_send = $client->call('SendPay',array($datos_send));

    /* Parseo resultados */
    echo"Datos SendPay: <pre>"; print_r($res_send); echo"</pre>";
