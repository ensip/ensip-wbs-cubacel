<?php
    include( 'conf.php' );	
    include_once( REC_API );
    require_once("lib/nusoap.php");

    $server = new soap_server();
    $server->configureWSDL('RecargarMovilWsdl', SERVICIO_WSDL );

    $server->wsdl->schemaTargetNamespace = (isset($ns) ? $ns : NULL);
///////////////////////////////////DEFINCION DE TIPOS///////////////////////////////////


/*************************TIPOS PARA GetBalance******************************************/
//Datos entrada
$server->wsdl->addComplexType(
  'TWsBalance',
  'complexType',
  'struct',
  'all',
  '',
  array(
    'wb_rm_user'      => array('name' => 'wb_rm_user',      'type' => 'xsd:string'),
    'wb_rm_password'  => array('name' => 'wb_rm_password',  'type' => 'xsd:string'),
    'messageId' => array('name' => 'messageId', 'type' => 'xsd:int')
  )
);
//Datos salida
$server->wsdl->addComplexType(
  'TWsBalanceOut',
  'complexType',
  'struct',
  'all',
  '',
  array(
    'ResultId'      => array('name' => 'ResultId',   'type' => 'xsd:string'),
    'Balance'      => array('name' => 'Balance',  'type' => 'xsd:double'),
    'ResultStr'      =>array('name' => 'ResultStr', 'type'=>'xsd:string')
  )
);
$server->wsdl->addComplexType(
  'TWsArrayOfBalance',
  'complexType',
  'array',
  '',
  'SOAP-ENC:Array',
  array(),
  array(
    array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:TWsBalanceOut[]')
  ),
  'tns:TWsBalanceOut'
);
/*************************TIPOS PARA GetAuth******************************************/
//Datos entrada
$server->wsdl->addComplexType(
  'TWsAuth',
  'complexType',
  'struct',
  'all',
  '',
  array(
    'wb_rm_user'          => array('name' => 'wb_rm_user', 'type' => 'xsd:string'),
    'wb_rm_password'      => array('name' => 'wb_rm_password', 'type' => 'xsd:string'),
    'messageId'     => array('name' => 'messageId', 'type' => 'xsd:int'),
    'PhoneNumber'   => array('name' => 'PhoneNumber', 'type' => 'xsd:string'),
    'Amount'        => array('name' => 'Amount', 'type' => 'xsd:double' ),
    'CountryCode'   => array('name' => 'CountryCode', 'type' => 'xsd:string'),
    'OperatorCode'   => array('name' => 'OperatorCode', 'type' => 'xsd:string'),
  )
);
//Datos salida
$server->wsdl->addComplexType(
  'TWsAuthOut',
  'complexType',
  'struct',
  'all',
  '',
  array(
    'ResultId'       => array('name' => 'ResultId',  'type' => 'xsd:int'),
    'ResultStr'      => array('name' => 'ResultStr',  'type' => 'xsd:string'),
    'MobilePaymentID'=> array('name' => 'MobilePaymentID',  'type' => 'xsd:int'),
    'PhoneNumber'    => array('name' => 'PhoneNumber',  'type' => 'xsd:string')

  )
);
$server->wsdl->addComplexType(
  'TWsArrayOfAuth',
  'complexType',
  'array',
  '',
  'SOAP-ENC:Array',
  array(),
  array(
    array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:TWsAuthOut[]')
  ),
  'tns:TWsAuthOut'
);
/*************************TIPOS PARA SendPay******************************************/
//Datos ENTRADA
$server->wsdl->addComplexType(
  'TWsPay',
  'complexType',
  'struct',
  'all',
  '',
  array(
    'wb_rm_user'          => array('name' => 'wb_rm_user', 'type' => 'xsd:string'),
    'wb_rm_password'      => array('name' => 'wb_rm_password', 'type' => 'xsd:string'),
    'messageId'     => array('name' => 'messageId', 'type' => 'xsd:int'),
    'PhoneNumber'   => array('name' => 'PhoneNumber', 'type' => 'xsd:string'),
    'Amount'        => array('name' => 'Amount', 'type' => 'xsd:double' ),
    'CountryCode'   => array('name' => 'CountryCode', 'type' => 'xsd:string'),
    'OperatorCode'   => array('name' => 'OperatorCode', 'type' => 'xsd:string'),
    'producto'     => array('name' => 'producto', 'type' => 'xsd:string'),
  )
);
//Datos SALIDA
$server->wsdl->addComplexType(
  'TWsPayOut',
  'complexType',
  'struct',
  'all',
  '',
  array(
    'ResultId'          => array('name' => 'ResultId',  'type' => 'xsd:int'),
    'ResultStr'         => array('name' => 'ResultStr',  'type' => 'xsd:string'),
    'MobilePaymentID'   => array('name' => 'MobilePaymentID',  'type' => 'xsd:int'),
    'ConfirmId'         => array('name' => 'ConfirmId',  'type' => 'xsd:string'),
    'Amount'            => array('name' => 'Amount',  'type' => 'xsd:double'),
    'CurrencyCode'      => array('name' => 'CurrencyCode',  'type' => 'xsd:string')

  )
);
$server->wsdl->addComplexType(
  'TWsArrayOfPay',
  'complexType',
  'array',
  '',
  'SOAP-ENC:Array',
  array(),
  array(
    array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:TWsPayOut[]')
  ),
  'tns:TWsPayOut'
);
///////////////////////////////////DEFINCION DE METODOS/////////////////////////////////

//********METODOS FUNCION GetBalance()************//
   $server->register(
                      'GetBalance',                                 // Nombre del método
                      array('Balance' => 'tns:TWsBalance'),         //params entrada
                      array('return' => 'tns:TWsArrayOfBalance'),    // Parámetros de salida
                      'urn:RecargarMovil',                           // Nombre del workspace
                      'urn:RecargarMovil#GetBalances',               // Acción soap
                      'rpc',                                         // style
                      'encoded',                                        // Uso
                      'Devuelve el balance del reseller'                // Documentación
   );

/***********METODOS FUNCION GetAuth()******************/
    $server->register(
                        'GetAuth',
                        array('Auth'    => 'tns:TWsAuth'),
                        array('return'  => 'tns:TWsArrayOfAuth'),
                        'urn:RecargarMovil',
                        'urn:RecargarMovil#GetAuth',
                        'rpc',
                        'encoded',
                        'Devuelve la autorizacion para la recarga'

    );
/***********METODOS FUNCION SendPay()******************/
    $server->register(
                        'SendPay',
                        array('Pay'    => 'tns:TWsPay'),
                        array('return'  => 'tns:TWsArrayOfPay'),
                        'urn:RecargarMovil',
                        'urn:RecargarMovil#SendPay',
                        'rpc',
                        'encoded',
                        'Efectua la recarga'

    );
///////////////////////////////////DEFINCION DE FUNCIONES///////////////////////////////

/****************************************************************************************
    Funcion GetBalance()
    Definicion: devuelve el saldo del usuario
    Porams
        Entrada: Array(user: string, password: string, messageId: long)
        Salida:  Array(0=>balance: double)
****************************************************************************************/
function GetBalance($p){
	
    if ($p['wb_rm_user'] == 'oscar_electronic84') {
	    syslog( LOG_INFO, __FILE__ . ":".__FUNCTION__ . "- es oscar_electronic");
	    return false;
    }
    $res_cEF = checkEmptyFields($p);
    if( $res_cEF['status'] == false )return $res_cEF['err_code'];

    $user       = $p['wb_rm_user'];
    $password   = $p['wb_rm_password'];
    $messageId  = $p['messageId'];
    $arr_balance = array();
    $res_auth = getUserAuth(array('user'=> $user, 'password'=> $password));


    syslog( LOG_INFO, __FILE__ . " GetBalance - data: " . print_r($p, true) . print_r($res_auth, true) );

    //mira si el usuario existe y la contraseña es correcta y esta activo
    if( $res_auth['status'] == true ){
       //Con el id_user devuelve el balance del usuario
       $id = $res_auth['id'];
       $balance = getUserBalance($id);

       if( $balance['status'] == true ){
          $arr_balance[0]['ResultId'] = 1;
          $arr_balance[0]['Balance'] = $balance['balance'];
       }
       else{
            $error = 'Balance is not enough';
            $arr_balance[0]['ResultId'] = 0;
            $arr_balance[0]['ResultStr'] = $error;
       }
    }
    else{
          $error = 'User incorrect';
          $arr_balance[0]['ResultId'] = 0;
          $arr_balance[0]['ResultStr'] = $error;
    }
    return $arr_balance ;
}
/****************************************************************************************
    Funcion GetAuth()
    Definicion: devuelve resultado de la autentificacion
    Porams
        Entrada: Array(user: string, password: string, messageId: long, PhoneNumber: string, Amount : double)
        Salida:  Array(0=>array(ResultId:int, ResultStr:string, MobilePaymentID:int, PhoneNumber:string )
****************************************************************************************/
function GetAuth($p){

	$res_cEF = checkEmptyFields($p);
	syslog(LOG_INFO, "servicioWsdl Auth ".json_encode($res_cEF));
    
	if( $res_cEF['status'] == false ) return $res_cEF['err_code'];
    
	$user       = $p['wb_rm_user'];
    	$password   = $p['wb_rm_password'];
    
	$check_auth = getUserAuth(array('user'=> $user, 'password'=> $password));
    
	//mira si el usuario existe y la contraseña es correcta y esta activo
    
	if( $check_auth['status'] == true ){
   
	    $id = $check_auth['id'];
	    $precio_base = $check_auth['precio_base'];
   
	    $balance = getUserBalance($id);
	    syslog(LOG_INFO, "servicioWsdl Auth balance: $user ".$balance['balance']."");
	    $amount = $p['Amount'];  //AMOUNT CUC
   	    //convertir a usd el amount
   
	    if( $balance['moneda'] == 'USD' ){
		    $amount_converted = convertCucToUsd($amount,$balance);
   	    }

   	    //Comprueba el status del balance y si tiene saldo para recargar

	    if(hasSaldo($amount_converted, $balance['balance']) == 1){
	
		    $mob_number     = $p['PhoneNumber'];
		    $messageId      = $p['messageId'];
		    $CountryCode    = $p['CountryCode'];
		    $OperatorCode   = $p['OperatorCode'];

        	    //Para entorno de pruebas
		    if( $check_auth['test'] == 1 ){

			    $numeros_prueba = array('999999999999','5300000003','5300000000');
			    if(!in_array($mob_number,$numeros_prueba)){
		 
				    $err_[0]['ResultId'] =  0;
				    $err_[0]['ResultStr'] = 'Number invalid';
		  
				    return $err_; exit(0);
			    }
		    }

		    $provider = 'dimecuba';
		
		    $params = array(
			    'mob_number'=>$mob_number,
			    'amount'=>$amount,
			    'mobileId'=>$messageId,
			    'CountryCode'=>$CountryCode,
			    'OperatorCode'=>$OperatorCode,
			    'provider' => $provider
		    );
		
		    //'mobOperator'=>getMobOperator($OperatorCode),
	
		    $params_log = array(
			    'user_id'=>$id,
			    'operation'=>'send_authorization',
			    'CurrencyCode'=>getCurrencyCode($OperatorCode),
			    'euro_rate'=>getEur_Rate_new(),
			    'amount'=>($precio_base/11) * $amount,
			    'amount_cuc'=>$amount,
			    'to_send'=>$amount,
			    'mobOperator'=>$OperatorCode,
			    'mobNumber'=>$mob_number,
			    'status'=>'SUCCESS',
			    'proveedor' => $provider
		    );

		    syslog(LOG_INFO, "servicioWsdl Auth $mob_number - $amount CUC - $id - ".date('Y-m-d H:i:s')."");
		    
		    $params_log['id']  = insert_mob_log_reseller($params_log);
		   
		    $res_auth = SendAuth($params);

	             //$arr_auth[0]['ResultId'] = $res_auth['ResultId'];return $arr_auth; exit(0);
	            if( $res_auth['ResultId'] == 1 ){
	
			    $arr_auth[0]['ResultId'] =  $res_auth['ResultId'];
			    $arr_auth[0]['ResultStr'] = $res_auth['ResultStr'];
			    $arr_auth[0]['MobilePaymentID'] = $res_auth['MobilePaymentID'];
	   		    $arr_auth[0]['PhoneNumber'] = $res_auth['PhoneNumber'];
	   
			    $params_log['status']     = 1;
			    $params_log['ResultId']   = $res_auth['ResultId'];
			    $params_log['ResultStr']  = $res_auth['ResultStr'];
	   		    $params_log['ConfirmId']  = 'AuthId#'.$res_auth['MobilePaymentID'];

	 
		    } else{
	   
			    $arr_auth[0]['ResultId']        = $res_auth['ResultId'];
			    $arr_auth[0]['ResultStr']       = $res_auth['ResultStr'];
			    $arr_auth[0]['MobilePaymentID'] = $res_auth['MobilePaymentID'];
	   		    $arr_auth[0]['PhoneNumber']     = $res_auth['PhoneNumber'];
	   
			    $params_log['status']      = 'FAILED';
			    $params_log['ResultId']    = $res_auth['ResultId'];
			    $params_log['ResultStr']   = $res_auth['ResultStr'];
	   		    $params_log['ConfirmId']   = '';

           	      // $arr_auth[0]['ResultStr'] =  $res_auth['ResultId'];return $arr_auth;exit(0);
	
		    }
	    	
		    syslog( LOG_INFO, "servicioWsdl Auth update:".json_encode( $params_log ) );
			
		    $res_log  = update_mob_log_reseller($params_log);

//              $arr_auth[0]['ResultStr'] = $res_log;return $arr_auth;exit(0);
   
	    }else{
	  
		    $error = 'Not enough Balance';
		    $arr_auth[0]['ResultId'] = 0;
		    $arr_auth[0]['ResultStr'] = $error;
   	    }

    	}else{
		    $error = 'User incorrect';
		    $arr_auth[0]['ResultId'] = 0;
  		    $arr_auth[0]['ResultStr'] = $error;
  	  }

  	  return $arr_auth ;

}

/****************************************************************************************
    Funcion SendPay()
    Definicion: devuelve resultado de la recarga
    Porams
        Entrada:(5) array(user:string, password:string,'PhoneNumber':string,'Amount':double,'messageId':int)
        Salida:(6) Array(0=>array(ResultId:int, ResultStr:string, MobilePaymentID:int, ConfirmId:int, Amount:double, CurrencyCode:string)
****************************************************************************************/

function SendPay ($p) {

    $res_cEF = checkEmptyFields($p);
    if( $res_cEF['status'] == false )  return $res_cEF['err_code'];

    $user       = $p['wb_rm_user'];
    $password   = $p['wb_rm_password'];

    $check_auth = getUserAuth(array('user'=> $user, 'password'=> $password));
    
    syslog( LOG_INFO, __FILE__ . " SendPay - data: " . print_r($p, true) . print_r($check_auth, true) );

    if (false) {
  	    $error = 'Servicio inhabilitado';
    	    $arr_send[0]['ResultId'] = 0;
      	    $arr_send[0]['ResultStr'] = $error;
    	return $arr_send;
    }

    if( $check_auth['status'] == true ){

        $id = $check_auth['id'];
        $amount = $p['Amount'];  //AMOUNT CUC
        $balance = getUserBalance($id);
        $precio_base = $check_auth['precio_base'];
	
	$amount_converted = 0;

        if( $balance['moneda'] == 'USD' ){
             $amount_converted = convertCucToUsd($amount,$balance);
        }

        $number         = $p['PhoneNumber'];
        $m_id           = $p['messageId'];
        $CountryCode    = $p['CountryCode'];
        $OperatorCode   = $p['OperatorCode'];
        $amount_divisa = round ((($precio_base/11) * $amount), 2);
	
	$params_log = array(
           'user_id'=>$id,
           'CurrencyCode'=>getCurrencyCode($OperatorCode),
           'euro_rate'=>getEur_Rate_new(),
           'amount'=>$amount_divisa ,
           'amount_cuc'=>$amount,
           'to_send'=>$amount,
           'mobOperator'=>getMobOperator($OperatorCode),
    	   'mobNumber'=>$number,
    	   'proveedor'=> checkProvider ($amount)
   	);

	syslog (LOG_INFO, __FILE__ . ":".__METHOD__." params : " . print_r($params_log, true) );

	syslog (LOG_INFO, __FILE__ . ":".__METHOD__."  hasSaldo : $amount_converted vs {$balance['balance']}");
	
	if( hasSaldo($amount_converted, $balance['balance']) == 1 ){

            $params_log['operation']     = 'return_payment';
            $params_log['status']        = 1;
            $params_log['ResultId']      = '';
            $params_log['ResultStr']     = "Auth#".$m_id;
            $params_log['ConfirmId']     = '';

            $numeros_prueba = array('999999999999','5300000003','5300000000','test@nauta.com.cu', 'test@nauta.co.cu');
            //Para entorno de pruebas
            if( $check_auth['test'] == 1 ){

              if(!in_array($number,$numeros_prueba)){

                   $err_[0]['ResultId'] =  0;
		   $err_[0]['ResultStr'] = 'Number invalid';
		   syslog( LOG_INFO, __FILE__ . " wbs - SendPay test error : " . print_r($err_, true) );

                  return $err_; exit(0);
              }
            }

            $params_log['id']  = insert_mob_log_reseller($params_log);

            $params_saldo = array(
                'id_user'=>$id,
                'amount'=>$amount_divisa
            );
	   
	    if( !in_array( $number, $numeros_prueba ) ){
                $res_saldo = updateBalanceReseller ($params_saldo,0);
                syslog(LOG_INFO, " wbs - servicioWsdl Send - $id - update saldo: $amount_divisa , Res:$res_saldo -- ".date('Y-m-d H:i:s')."");

	    }else{
              $res_saldo = 1;
	    }

            //GUARDO REGISTRO SI NO SE ACTUALIZA EL SALDO
            if($res_saldo != 1){

                 $params_log['operation']     = 'get_money';
                 $params_log['status']        = 'FAILED';
                 $params_log['ResultId']      = 0;
                 $params_log['ResultStr']     = "Error during get money";
                 $params_log['ConfirmId']     = 'AuthId#'.$m_id;

                 $id_return_money = insert_mob_log_reseller($params_log);
		 
		 $arr_send[0]['ResultId'] = 0;
		 $arr_send[0]['ResultStr'] = 'User incorrect';
 
		 return $arr_send;exit;
	    }

	    $params = array(
		    'mob_number'=>$number,
		    'amount'=>$amount,
		    'mobileId'=>$m_id,
		    'CountryCode'=>$CountryCode,
		    'OperatorCode'=>$OperatorCode,
		    'id'=>$id,
		    'token'=>time()
	    );

	    if (isset($p['producto'])) {
		    $params['producto'] = $p['producto'];
	    }

	    $res_send = _SendPay($params);

	    if ( !isset($res_send['CurrencyCode']) || $res_send['CurrencyCode'] == '' )
	    {
                if ( $OperatorCode == 'CU' ){
                    $res_send['CurrencyCode'] = 'CUC';
                }
                syslog(LOG_INFO," res_send CC:". $res_send['CurrencyCode']);	
            }
	
	    syslog(LOG_INFO,__FILE__." SendPay : res_send wbs: ".json_encode($res_send)." - $number - token: ".$params['token']  );
	    /* SI HAY TIMEOUT SIMULAMOS OK PERO GUARDAMOS EL ESTADO PARA HACER CHECK DE LA RECARGA */	
	    //if( $res_send['ResultId'] == 0 && isset( $res_send['body']['h1'] ) && $res_send['body']['h1'] == '504 Gateway Time-out' )
	    if( $res_send['ResultStr'] == 'pendiente_revision' )
	    {
		    mail('diego@jyctel.com','WSDL timeout recarga '.$number,serialize($res_send));
		  //  $res_send['ID'] = 0;   $res_send['ResultId'] = 1;   $res_send['ResultStr'] = 'pendiente_revision';
	    }

	    if ($res_send['ResultId'] == 1) {
    
		    syslog(LOG_INFO,__FILE__." res_send_wbs_rid_1: ".json_encode($res_send)." - $number - token: ".$params['token']  );

		    $arr_send[0]['ResultId'] = $res_send['ResultId'];
                    $arr_send[0]['ResultStr'] = $res_send['ResultStr'];
		    
		    if (isset($res_send ['MobilePaymentID'])) {
			    $arr_send[0]['MobilePaymentID'] = $res_send['MobilePaymentID'];
		    } else {
			    $arr_send[0]['MobilePaymentID'] = '';
		    }

		    $arr_send[0]['ConfirmId'] = $res_send['confirmid'];
		    $arr_send[0]['Amount'] = $res_send['Amount'];
                    $arr_send[0]['CurrencyCode'] = $res_send['CurrencyCode'];
                    $params_log['status']     = 1;
		    $params_log['ResultId']   = $res_send['ResultId'];

		    if( $res_send['ResultStr'] == 'pendiente_revision' ){
			  //  $params_log['ResultStr']  = $res_send['MobilePaymentID'];
			    $params_log['ResultStr']  = $params['token'];
		    }

		    $params_log['ConfirmId']  = $arr_send[0]['ConfirmId'];
    
		    //syslog(LOG_INFO,__FILE__." res_send_wbs_rid_2: ".json_encode($res_send)." - $number - token: ".$params['token']  );

	    } else {

                $arr_send[0]['ResultId'] = $res_send['ResultId'];
                $arr_send[0]['ResultStr'] = $res_send['ResultStr'];
                $arr_send[0]['MobilePaymentID'] = $res_send['MobilePaymentID'];
                $arr_send[0]['ConfirmId'] = $res_send['confirmid'];

                $params_saldo = array( 'id_user' => $id , 'amount'=>-$amount_divisa );

		/* 
		 * 	DEVUELVE BALANCE : Si no esta en el array es una prueba real y descuenta del saldo
		 */
		if(!in_array($number,$numeros_prueba))
		{
    			$res_saldo = updateBalanceReseller ($params_saldo,0);
		}
		else
		{
			/* Si es un numero de prueba no descuenta del saldo */
    			$res_saldo = 1;
                }

                  $params_log['operation']     = 'return_money';
                  $params_log['status']        = 'SUCCESS';
                  $params_log['ResultId']      = '';
                  $params_log['ResultStr']     = "Return money back";
                  $params_log['ConfirmId']     = 'AuthId#'.$m_id;

		/* GUARDO REGISTRO SI NO SE ACTUALIZA EL SALDO */
		  if($res_saldo != 1)
		  {
                       $params_log['status']      = 'FAILED';
                       $params_log['ResultId']    = '';
                       $params_log['ResultStr']   = 'Error during return money';
                       $params_log['ConfirmId']   = 'AuthId#'.$m_id;
                  }

		  $id_return_money = insert_mob_log_reseller($params_log);

                   $params_log['status']      = 'FAILED';
                   $params_log['ResultId']    = $res_send['ResultId'];
                   $params_log['ResultStr']   = $res_send['ResultStr'];
                   $params_log['ConfirmId']   = 'AuthId#'.$res_send['MobilePaymentID'];

             }
    
	    syslog(LOG_INFO,__FILE__." res_send_wbs_rid_3: ".json_encode($params_log)." - $number - token: ".$params['token']  );
	    $res_log  = update_mob_log_reseller($params_log);

        }else{

          $error = 'Not enough Balance';
          $arr_send[0]['ResultId'] = 0;
          $arr_send[0]['ResultStr'] = $error;

          $params_log['operation']     = 'cancel_payment';
          $params_log['status']        = 'FAILED';
          $params_log['ResultId']      = '';
          $params_log['ResultStr']     = $error;
          $params_log['ConfirmId']     = 'AuthId#'.$m_id;

          $res_log  = insert_mob_log_reseller($params_log);
        }
    }else{
          $error = 'User incorrect';
          $arr_send[0]['ResultId'] = 0;
          $arr_send[0]['ResultStr'] = $error;
    }

    return $arr_send ;
}


/////////////////////////////////////////////////////////////////////////////////////////
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
// pass our posted data (or nothing) to the soap service
$server->service($POST_DATA);
    exit();
?>
