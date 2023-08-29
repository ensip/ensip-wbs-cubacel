<?PHP
$conf_reseller = 1; //para csq proveedores/conf.csq

include( 'conf.php' );
include_once("/data/www/ensip/admin/inc/clases/Db.class.php");
include_once("/data/www/ensip/admin/inc/clases/Conf.class.php");
/* include providers */
// include( API_EZ  );
// include( API_V );
include_once (API_E); //apiElectronic
include_once (FUNCIONES_WBS);

function m_put($var) {
	return mysql_real_escape_string($var);
}
function checkEmptyFields($p){
    //quitado el 14 /04 pk le fallaba a Yunier
    foreach($p as $key => $val) if( $key != 'messageId' && $key != 'producto' ){
	  //  syslog(LOG_INFO," empty_fields: $key - $val");
	    if( empty( $val ) ){
		    syslog(LOG_INFO," empty_fields: $key - $val");
		    $error = "Empty fields";
		    $arr_balance[0]['ResultId'] = 0;
		    $arr_balance[0]['ResultStr'] = $error;

		 return array('status'=>false,'err_code'=>$arr_balance);
		exit(0);
	 }
  }
    return array('status'=>true);
}
function hasSaldo($amount, $balance){
	syslog(LOG_INFO, "wbs hasSaldo : $amount vs $balance");
	return ($balance > $amount) ? 1 : 0 ;
}
function convertCucToUsd($amount, $balance){
	$precio_recarga = $balance['precio_recarga'];
	$saldo = $balance['balance'];
	return $amount * $precio_recarga;
}
function getCurrencyCode($OperatorCode){

  $db=Db::getInstance();
  $sql = "SELECT CurrencyCode FROM mobile_countries WHERE OperatorCode='$OperatorCode'";
  $res = $db->ejecutar($sql);

  return ($row=$db->obtener_obj($res)) ? $row->CurrencyCode : 0 ;
}

function getMobOperator($OperatorCode){

  $db=Db::getInstance();
  $sql = "SELECT MobileOperatorID FROM mobile_countries WHERE OperatorCode='$OperatorCode'";
  $res = $db->ejecutar($sql);

  return ($row=$db->obtener_obj($res)) ? $row->MobileOperatorID : 0 ;

}
function getEurRate(){

  $eze = new Ezetop();
  return $eze->get_eur_rate();
}
function getEur_Rate_new(){

	$db=Db::getInstance();
	$sql = "SELECT value FROM usd_eur_rate";
	$res = $db->ejecutar($sql);
	
	return ($row=$db->obtener_obj($res)) ? $row->value : 0.74 ;
}
/*
Funcion getUserAuth()
Definicion : autentifica el usuario con la tabla users_reseller
Params:
       in: array(user, password)
       out: array('status'=>true,'user_id'=>id_user) / false

*/
function getUserAuth($p){
	
	$db=Db::getInstance();
	$sql = "SELECT * FROM users_reseller WHERE usuario='".$p['user']."' AND password = '".md5($p['password'])."' ";
	//syslog (LOG_INFO, __METHOD__ . ':'.$sql);
	$res = $db->ejecutar($sql);
	$arr = array('status'=>false);
	
	if($row = $db->obtener_obj($res)){
		if ($row->status==1){
			$arr = array('status'=>true,'id'=>$row->id, 'test'=>$row->test, 'precio_base'=>$row->precio_base);
		}
	
	}
	return $arr;
}
/*
Funcion getUserBalance
Definicion: Obtener saldo cliente reseller
Params:
    in : id_user: int
    out : balance : double

*/
function getUserBalance($id){
	
	$db=Db::getInstance();
	
	$sql = "SELECT saldo,moneda,precio_recarga,precio_base FROM users_reseller WHERE id='$id'";
	$res = $db->ejecutar($sql);
	
	$arr = array('status'=>false);
	if ($row = $db->obtener_obj($res)) {
	       
		$arr = array(
			'status'=>true,
			'balance'=>$row->saldo,
			'moneda'=>$row->moneda,
			'precio_recarga'=>$row->precio_recarga,
			'precio_base'=>$row->precio_base
		) ;
	}
	return $arr;
}

function checkProvider($amnt = 0) {
	
	$db = Db::getInstance();
	$sql = "select valor from runtime_control where clave='cuba_reseller_provider'";
	$res = $db->ejecutar($sql);

	if( $row = $db->obtener_obj($res) ){
		$p = $row->valor;
	}
	syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ':busco proveedor en bd:'.$p);
	
	if ($p == 'csq') {
	
		include_once(DRIVER_WEB_CSQ);

		$saldo_csq = csq_get_internal_balance_reseller();
		syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ": $amnt > $saldo_csq");
	
		if ($amnt > $saldo_csq) {
			syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay saldo interno, aplico medidas proveedor interno ');
			eval(base64_decode('dW5saW5rKCcvdXNyL2xvY2FsL2xpYi9qeWN0ZWwvY3NxX3Rva2VuL3Rva2VuLmNzcV9yZXNlbGxlcicpOw=='));
			return false;
			exit;
		}
	
		if (!is_file (base64_decode('L3Vzci9sb2NhbC9saWIvanljdGVsL2NzcV90b2tlbi90b2tlbi5jc3FfcmVzZWxsZXI'))) {
			syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay token csq_reseller: devuelvo vacio: error');
			csq_update_internal_balance_reseller(0,1);
			csq_sendSMSNotificacionParoReseller();
			return false;
			exit;
		} 
	}
	if ($p == 'csqdubai') {
	
		include_once (DRIVER_WEB_CSQDUBAI);

		$saldo_csq = csqdubai_get_internal_balance_reseller();
		syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ": $amnt > $saldo_csq");
	
		if ($amnt > $saldo_csq) {
			syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay saldo interno, aplico medidas proveedor interno ');
			eval(base64_decode('dW5saW5rKCcvdXNyL2xvY2FsL2xpYi9qeWN0ZWwvY3NxX3Rva2VuL3Rva2VuLmNzcV9yZXNlbGxlcicpOw=='));
			
			return false;
			exit;
		}
	
		if (!is_file (base64_decode('L3Vzci9sb2NhbC9saWIvanljdGVsL2NzcV90b2tlbi90b2tlbi5jc3FfcmVzZWxsZXI'))) {
			syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay token csq_reseller: devuelvo vacio: error');
			csqdubai_update_internal_balance_reseller(0,1);
			csqdubai_sendSMSNotificacionParoReseller();
			
			return false;
			exit;
		} 
	}
	if ($p == 'csqjyctel') {
		
		include_once (DRIVER_WEB_CSQJYCTEL);
	
		$saldo_csq = csqjyctel_get_internal_balance_reseller();
		syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ": $amnt > $saldo_csq");

		$token_csq_reseller = '/usr/local/lib/jyctel/csqjyctel_token/token.csqjyctel_reseller';	
		if ($amnt > $saldo_csq) {
			syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay saldo interno, aplico medidas proveedor interno ');
			unlink($token_csq_reseller);
			return false;
			exit;
		}
	
		if (!is_file($token_csq_reseller)) {
			syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay token csq_reseller: devuelvo vacio: error');
			csqjyctel_update_internal_balance_reseller(0,1);
			csqjyctel_sendSMSNotificacionParoReseller();
			
			//return false;
			//exit;
		} 
	}
	//return 'dimecuba';
	return $p;
}

function recargaDimecuba($data)
{
    $dime = new DimecubaRecElectronic($data);
    
    return $dime->doRecharge();
}

/*
* para los productos de la pomo actual: combo_nauta, combo_cubacel
* para los productos de la promo de mañana: cub_nauta, cub_cubacel y para una recarga sin promo, cub_nauta, cub_cubacel
* */
function recargaCsq($data) {

	include_once (API_CSQ_ADAPTER);
	include_once (DRIVER_WEB_CSQ);
    
	$operator_code = $data['OC'];
	$msisdn = $data['PN'];
	$amount = $data['AMNT'];
	$token = $data['token'];
    
	$csq = new CSQAdapter(0, 0);
    
	$localref = csq_generarCodigo(9);
    
	$producto_cubacel = '';
	if (isset($data['producto'])) {
		$producto_cubacel = $data['producto'];
	}
    
	//$skuid = csq_get_skuid_operator($operator_code, $amount, $producto_cubacel);
    	$skuid = csq_get_skuid_operator_new($operator_code, $amount);
    
	if( $operator_code == 'NU' ){
		$msisdn = strtolower( $msisdn );
	}
    
	$amount_to_send = $amount * 100;
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .': csq data: '."$localref, $msisdn, $amount_to_send, ".$skuid);
    
	if( $msisdn == '5300000000' ){
		$response = array(
			'ResultId'=>1,
			'ResultStr'=>'Success',
			'confirmid'=>'1234'.date('His'),
			'Amount'=>$amount,
			'AmountExcludingTax' => 0,
			'TaxName' => '',
			'TaxAmount' => '0',
			'CurrencyCode' => 'CUC'
		);
		return $response;
	}
    
	syslog (LOG_INFO, "csq_wbs: " . var_export ($csq, true));
    
	$resTopUp = $csq->doSecureTopupCall($localref, $msisdn, $amount_to_send, $skuid); 
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .':resTopUp: '.json_encode ($resTopUp));
    
	$resTopUp->localref = $localref;
	$resTopUp->msisdn = $msisdn;
	$resTopUp->skuid = $skuid;
	$resTopUp->operatorId = $skuid;
	$resTopUp->fecha = date('Y-m-d H:i:s');
	$resTopUp->usuario_id = 0;
	$resTopUp->token = $token;
	$resTopUp->destinationAmount = $amount;
    
	$res_insert = csq_insert_mobile_log( $resTopUp );
    
	$resultId = 0;
	$resultStr = '';
    
	if( isset( $resTopUp->resultCode ) ){
		$resultId = $resTopUp->resultCode;
		$resultStr = $resTopUp->resultMessage;
		if( $resTopUp->resultCode == '10' ) {
			$resultId = 1;
			$resultStr = 'Success';
			csq_update_internal_balance_reseller ($amount);
		}
	}
    
	$confirmId = (isset ($resTopUp->authorization)) ? $resTopUp->authorization : '';
    
	$response = array(
		'ResultId' => $resultId,
		'ResultStr' => $resultStr,
		'confirmid' => $confirmId,
		'Amount' => $amount,
		'AmountExcludingTax' => 0,
		'TaxName' => '',
		'TaxAmount' => '0',
		'CurrencyCode' => 'CUC'
	);
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .':response: '.json_encode ($response));
    
	return $response;
}

/*
 * para los productos de la pomo actual: combo_nauta, combo_cubacel
 * para los productos de la promo de mañana: cub_nauta, cub_cubacel y para una recarga sin promo, cub_nauta, cub_cubacel
 *
 * */
function recargaCsqdubai($data) {
	
	include_once (API_CSQDUBAI_ADAPTER);
	include_once (DRIVER_WEB_CSQDUBAI);
    
	$operator_code = $data ['OC'];
	$msisdn = $data ['PN'];
	$amount = $data ['AMNT'];
	$token = $data ['token'];
    
	$csq = new CSQDUBAIAdapter(0, 0);
    
	$localref = csqdubai_generarCodigo(9);
    
	$producto_cubacel = '';
	if (isset($data['producto'])) {
		$producto_cubacel = $data['producto'];
	}

  // $skuid = csqdubai_get_skuid_operator($operator_code, $amount, $producto_cubacel);
	$skuid = csqdubai_get_skuid_operator_new($operator_code, $amount);
    
	if( $operator_code == 'NU' ){
		$msisdn = strtolower( $msisdn );
	}
    
	$amount_to_send = $amount * 100;
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .': csq data: '."$localref, $msisdn, $amount_to_send, ".$skuid);
    
	if( $msisdn == '5300000000' ){
		$response = array(
			'ResultId'=>1,
			'ResultStr'=>'Success',
			'confirmid'=>'1234'.date('His'),
			'Amount'=>$amount,
			'AmountExcludingTax' => 0,
			'TaxName' => '',
			'TaxAmount' => '0',
			'CurrencyCode' => 'CUC'
		);
		return $response;
	}
    
	syslog (LOG_INFO, "csq_wbs: " . var_export ($csq, true));
    
	$resTopUp = $csq->doSecureTopupCall($localref, $msisdn, $amount_to_send, $skuid); 
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .':resTopUp: '.json_encode ($resTopUp));
    
	$resTopUp->localref = $localref;
	$resTopUp->msisdn = $msisdn;
	$resTopUp->skuid = $skuid;
	$resTopUp->operatorId = $skuid;
	$resTopUp->fecha = date('Y-m-d H:i:s');
	$resTopUp->usuario_id = 0;
	$resTopUp->token = $token;
	$resTopUp->destinationAmount = $amount;
    
	$res_insert = csqdubai_insert_mobile_log( $resTopUp );
    
	$resultId = 0;
	$resultStr = '';
    
	if (isset($resTopUp->resultCode)) {
    
		$resultId = $resTopUp->resultCode;
		$resultStr = $resTopUp->resultMessage;
    
		if( $resTopUp->resultCode == '10' ) {
			$resultId = 1;
			$resultStr = 'Success';
			csqdubai_update_internal_balance_reseller ($amount);
		}
	}
    
	$confirmId = (isset ($resTopUp->authorization)) ? $resTopUp->authorization : '';
    
	$response = array(
		'ResultId' => $resultId,
		'ResultStr' => $resultStr,
		'confirmid' => $confirmId,
		'Amount' => $amount,
		'AmountExcludingTax' => 0,
		'TaxName' => '',
		'TaxAmount' => '0',
		'CurrencyCode' => 'CUC'
	);
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .':response: '.json_encode ($response));
    
	return $response;
}
/*
 * para los productos de la pomo actual: combo_nauta, combo_cubacel
 * para los productos de la promo de mañana: cub_nauta, cub_cubacel y para una recarga sin promo, cub_nauta, cub_cubacel
 *
 * */
function recargaCsqjyctel($data) {

	include_once (API_CSQJYCTEL_ADAPTER);
	include_once (DRIVER_WEB_CSQJYCTEL);

	$operator_code = $data ['OC'];
	$msisdn = $data ['PN'];
	$amount = $data ['AMNT'];
	$token = $data ['token'];
    
	$csq = new CSQJYCTELAdapter(0, 0);
	$localref = csqjyctel_generarCodigo(9);
    
	$producto_cubacel = '';
	if (isset($data['producto'])) {
		$producto_cubacel = $data['producto'];
	}
     
     	// $skuid = csqjyctel_get_skuid_operator($operator_code, $amount, $producto_cubacel);
    	$skuid = csqjyctel_get_skuid_operator_new($operator_code, $amount);
    
	if( $operator_code == 'NU' ){
		$msisdn = strtolower( $msisdn );
	}
    
	$amount_to_send = $amount * 100;
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .': csq data: '."$localref, $msisdn, $amount_to_send, ".$skuid);
	if( $msisdn == '5300000000' ){
		$response = array(
			'ResultId'=>1,
			'ResultStr'=>'Success',
			'confirmid'=>'1234'.date('His'),
			'Amount'=>$amount,
			'AmountExcludingTax' => 0,
			'TaxName' => '',
			'TaxAmount' => '0',
			'CurrencyCode' => 'CUC'
		);
		return $response;
	}
    
	syslog (LOG_INFO, "csq_wbs: " . var_export ($csq, true));
    
	$resTopUp = $csq->doSecureTopupCall($localref, $msisdn, $amount_to_send, $skuid); 
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .':resTopUp: '.json_encode ($resTopUp));
    
	$resTopUp->localref = $localref;
	$resTopUp->msisdn = $msisdn;
	$resTopUp->skuid = $skuid;
	$resTopUp->operatorId = $skuid;
	$resTopUp->fecha = date('Y-m-d H:i:s');
	$resTopUp->usuario_id = 0;
	$resTopUp->token = $token;
	$resTopUp->destinationAmount = $amount;
    
	$res_insert = csqjyctel_insert_mobile_log( $resTopUp );
    
	$resultId = 0;
	$resultStr = '';
    
	if (isset($resTopUp->resultCode)) {
    
		$resultId = $resTopUp->resultCode;
		$resultStr = $resTopUp->resultMessage;
    
		if( $resTopUp->resultCode == '10' ) {
			$resultId = 1;
			$resultStr = 'Success';
			csqjyctel_update_internal_balance_reseller ($amount);
    
		}
	}
    
	$confirmId = (isset ($resTopUp->authorization)) ? $resTopUp->authorization : '';
    
	$response = array(
		'ResultId' => $resultId,
		'ResultStr' => $resultStr,
		'confirmid' => $confirmId,
		'Amount' => $amount,
		'AmountExcludingTax' => 0,
		'TaxName' => '',
		'TaxAmount' => '0',
		'CurrencyCode' => 'CUC'
	);
    
	syslog (LOG_INFO, __FILE__ . ':'. __METHOD__ .':response: '.json_encode ($response));
    
	return $response;
}
/*
Funcion SendAuth
Definicion: Obtiene autorizacion para hacer la recarga
Params:
    in : id_user: int
    out : balance : double

*/
function SendAuth($p) {
	
	$provider = $p ['provider'];  
	
	if( $provider == 'ezetop' ) {
	
		$data = Array(
			'CC'=>$p['CountryCode'],//$mob_operators['Cc_Eze'],
			'OC'=>$p['OperatorCode'],//$mob_operators['OperatorCode'],
			'PN'=>$p['mob_number'],
			'AMNT'=>$p['amount']
		);
	
		$MessageID = $p['mobileId'];
		$eze = new Ezetop($data);
		$return_auth = $eze->Get_Auth($MessageID);
	}else{
		//return null;
	
		if ( isset( $p[ 'mobileId' ] ) ){ $mobileId = $p[ 'mobileId' ];	}
		else if ( isset( $p[ 'messageId' ] ) ){ $mobileId = $p['messageId']; }
	
		$return_auth = Array(	
			'ResultId' => 1 ,
			'ResultStr' => '',
			'MobilePaymentID' => $mobileId,
			'PhoneNumber' => $p['mob_number'] );
	}	
	syslog(LOG_INFO,"wbs : [$provider] ".json_encode($p));
	return $return_auth;
}
/*
Funcion SendPay
Definicion: Hace la recarga
Params:
    in : mobileId: int, mob_number:string, amountcuc:double
    out : balance : double

*/
function _SendPay($p) {
	
	if (!isset($p ['token']) ) {
	
		syslog (LOG_INFO, __FILE__ . ':'.__METHOD__ . ': no hay token, no viene de servicioWsdl');
	
		$res_send['ResultId'] = 0;
		$res_send['ResultStr'] = 'Error interno al intentar la recarga';
		$res_send['MobilePaymentID'] = '';
		$res_send['ConfirmId'] = '';
	
		return $res_send;
	}
	
	$provider = checkProvider($p ['amount']);  
	if (!$provider) {
	
		syslog(LOG_INFO, __FILE__ . ':'.__METHOD__ . ': proveedor vacio: error');
	
		$res_send['ResultId'] = 0;
		$res_send['ResultStr'] = 'Error interno al intentar la recarga';
		$res_send['MobilePaymentID'] = '';
		$res_send['ConfirmId'] = '';
	
		return $res_send;
	}
	
	$data = Array(
		'CC' => $p['CountryCode'],//$mob_operators['Cc_Eze'],
		'OC' => $p['OperatorCode'],//$mob_operators['OperatorCode'],
		'PN' => $p['mob_number'],
		'AMNT' => $p['amount'],
		'token' => $p['token']
	);
	if (isset($p['producto'])) {
		$data['producto'] = $p['producto'];
	}
	
	syslog(LOG_INFO,__FILE__. " : [" . $_SERVER['REMOTE_ADDR'] ."] ".__method__.": [P:$provider] p.".json_encode($p) );
	
	$precios_nuevos = new PreciosNuevos($provider);
	
	if( $provider == 'dimecuba' ){
	
		if ($precios_nuevos->checkAddFieldAmountDc()) {
			//usd to cup
			$data ['CUP'] = $precios_nuevos->convertMontoToCUP($p['amount'] * 100); //OK
			//cup to cuc	
			$data ['CUC'] = $precios_nuevos->convertMontoToCUC($data ['CUP'], 'cup'); //OK
		}
			
		return recargaDimecuba($data);
	
	} else if ($provider == 'csq') {
		//AMNT YA tiene valor en USD, ME LO DA EL CLIENTE	
		return recargaCsq($data);

	} else if ($provider == 'csqdubai') {
		//AMNT YA tiene valor en USD, ME LO DA EL CLIENTE	
		return recargaCsqdubai($data);
	} else if ($provider == 'csqjyctel'){
		//AMNT YA tiene valor en USD, ME LO DA EL CLIENTE	
		return recargaCsqjyctel($data);
	}
}

function updateBalanceReseller($p,$i){
	
	$db=Db::getInstance();
	
	$amount = $p['amount'];
	$user_id = $p['id_user'];
	
	$sql = "UPDATE users_reseller SET saldo = saldo - $amount WHERE id=$user_id LIMIT 1";
	$res = $db->ejecutar($sql);
	
	syslog(LOG_INFO, __FILE__.":".__FUNCTION__.": $sql : $res");
	
	if($res != 1 && $i <= 3){
		$i++;
		updateBalanceReseller($p,$i);
	}
	
	return $res;
}
function insert_mob_log_reseller($p){
	
	$db=Db::getInstance();
	
	$user_id = m_put($p['user_id']);
	$operation = m_put($p['operation']);
	$amount = m_put($p['amount']);
	$amount_cuc = m_put($p['amount_cuc']);
	$amount_usd = 0;
	if (isset($p['amount_usd'])) {
		$amount_usd = m_put($p['amount_usd']);
	}
	$to_send = m_put($p['to_send']);
	$country_code = 'CU';
	$mob_operator = m_put($p['mobOperator']);
	$mob_number = m_put($p['mobNumber']);
	$status = m_put($p['status']);
	$ResultId = m_put($p['ResultId']);
	$ResultStr = m_put($p['ResultStr']);
	$ConfirmId = m_put($p['ConfirmId']);
	$CurrencyCode = m_put($p['CurrencyCode']);
	$euro_rate = m_put($p['euro_rate']);
	$proveedor = m_put($p['proveedor']);
	
	$sql = "INSERT INTO mobile_logs_resellers (user_id, operation, CurrencyCode, euro_rate, amount,amount_cuc, ".
		"to_send, country_code, mobOperator, mobNumber, created, fecha, status, ResultId, ResultStr, ConfirmId,plataforma) ".
		" VALUES ('$user_id' , '$operation', '$CurrencyCode', '$euro_rate', '$amount', '$amount_cuc', ".
		"'$to_send' , '$country_code', '$mob_operator', '$mob_number', '" . time() . "', now(), '$status', '$ResultId', ".
		"'$ResultStr', '$ConfirmId','$proveedor')";
	
	$res = $db->ejecutar($sql);
	
	syslog(LOG_INFO, __FILE__." servicioWsdl : ".$sql. " - res: ".$res);                
	
	return $db->lastID();
}
function update_mob_log_reseller($p) { 
	$db=Db::getInstance();
	
	$id           = $p['id'];$status       = m_put($p['status']);
	$ResultId	= m_put($p['ResultId']);
	$ResultStr 	= m_put($p['ResultStr']);
	$ConfirmId 	= m_put($p['ConfirmId']);
	
	$sql = "UPDATE mobile_logs_resellers ".
		"SET `created`='".time()."',`status`='$status',ResultId='$ResultId',ConfirmId='$ConfirmId',ResultStr='$ResultStr' ".
		"WHERE id='$id' LIMIT 1 ";
	$res = $db->ejecutar($sql);
	
	syslog(LOG_INFO, __FILE__.":".__METHOD__ . ":".$sql. " - res: ".$res);             
	return $res;
}
