<?php

if (file_exists(dirname(__FILE__)."/../DbConn.php")) {
	include_once(dirname(__FILE__)."/../DbConn.php");
} else {
	include_once("/data/include/DbConn.php");
}

class DimecubaRecElectronic {
	var $url = URL_REC;
	var $url_check = URL_CHECK;
	var $user = U_RE;
	var $pass = P_RE;
	var $CountryCode;
	var $OperatorCode;
	var $PhoneNumber;
	var $Amount;
	var $CountryName;
	var $cuc = 0;
	var $cup = 0;
	var $OperatorName;
	var $ConfirmId;
	var $token = "";

	function __construct($datos=null, $token = ""){
		if($datos != null){
			if (!array_key_exists('CC', $datos)) $datos['CC'] = '';
			if (!array_key_exists('OC', $datos)) $datos['OC'] = '';
			if (!array_key_exists('PN', $datos)) $datos['PN'] = '';
			if (!array_key_exists('AMNT', $datos)) $datos['AMNT'] = '';
			if (!array_key_exists('CFID', $datos)) $datos['CFID'] = '';
			if (!array_key_exists('token', $datos)) $datos['token'] = '';

			// Cuenta Nauta cambia URL
			if ($datos['OC'] == "NU" && preg_match("/@/",$datos['PN'])) $this->url = "https://www.mysmscuba.com/p/api/api_rechargeNauta.php";

			$this->CountryCode 	= $datos['CC'];
			$this->OperatorCode	= $datos['OC'];
			$this->PhoneNumber 	= $datos['PN'];
			$this->Amount 		= $datos['AMNT'];
			if (isset( $datos ['CUP'])) {
				$this->cup = $datos['CUP'];
			}
			if (isset( $datos ['CUC'])) {
				$this->cuc = $datos['CUC'];
			}
			$this->ConfirmId	= $datos['CFID'];	// Confirm ID para check
			$this->token = $datos['token'];
		}
	}
	function checkAddFieldAmountDc () {
		$precios_nuevos = $this->getPreciosNuevos();
		$check = $precios_nuevos->checkAddFieldAmountDc ();
		return $check;
	}
	/*
	 * amnt ya tiene que venir convertido en moneda a enviar
	 * return true:false
	 */
	function checkAmountAvailability ($amnt) {
		$precios_nuevos = $this->getPreciosNuevos();
		$check = $precios_nuevos->checkAmountAvailability ($amnt);
		return $check;
	}

	function getCountries() {
		global $db;
		$sql = "select CountryCode, CountryName from ezetop_products";
	}

	function getOperators() {}

	private function getPreciosNuevos () {
		return new PreciosNuevos ('dimecuba');
	}

	function doRecharge() {
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$this->url);
		curl_setopt($ch, CURLOPT_POST, 1);

		$amount = $this->Amount;

		$post_fields = array(
			"api_user" => $this->user,
			"api_pass" => $this->pass,
			"celular" => $this->PhoneNumber,
			"email" => $this->PhoneNumber,
			"cuc" => $amount, //llega en usd
		);
	
		if ($this->checkAddFieldAmountDc ()) {
			if ($this->cup > 0) {
				$post_fields ['amount'] = $this->cup;
			}
			if ($this->cuc > 0) {
				//$post_fields ['cuc'] = $this->cuc;
				unset($post_fields ['cuc']);
			}

			$check_amnt_by_date = $this->checkAmountAvailability ($this->cup);
			if (!$check_amnt_by_date) {
				return array(
					'ResultId' => 0,
					'ResultStr' => 'Invalid Amount',
					'MobilePaymentID' => time(),
					'Amount' => $this->cup,
					'AmountExcludingTax' => $this->cup,
					'CurrencyCode' => 'CUC',
				); 
				exit;
			}
		}


		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (! $d_result = curl_exec($ch)) {
			trigger_error(curl_error($ch)); 
		}

		syslog(LOG_INFO, __FILE__.": d_result_wbs: $d_result, url: $this->url, -{$this->token}- post_fields: ".serialize($post_fields));

		$xml = simplexml_load_string($d_result);
		$json = json_encode($xml);
		$arr = json_decode($json,true);

		curl_close ($ch);

		/* Siempre Ok, datos fijos, para depurar
		return (array(
			'ResultId' => 0/1,
			'ResultStr' => '',
			'MobilePaymentID' => time(),
			'ConfirmId' => time(),
			'Amount' => $this->Amount,
			'AmountExcludingTax' => $this->Amount,
			'CurrencyCode' => 'CUC',
		)); // Siempre Ok*/

		/* <Result><ID>0</ID><Message>Success</Message><ConfirmID>236836603</ConfirmID><OperatorCode></OperatorCode><TransaccionID>1992400008</TransaccionID></Result>, arr: a:5:{s:2:"ID";s:1:"0";s:7:"Message";s:7:"Success";s:9:"ConfirmID";s:9:"236836603";s:12:"OperatorCode";a:0:{}s:13:"TransaccionID";s:10:"1992400008";} */
		/* FALLO:  d_result: <Result><ID>-1</ID><Message>Ha ocurrido un error</Message><ConfirmID></ConfirmID><OperatorCode></OperatorCode><TransaccionID>1477714998</TransaccionID></Result>, response: a:9:{s:2:"ID";s:2:"-1";s:7:"Message";s:20:"Ha ocurrido un error";s:9:"ConfirmID";s:1:"-";s:12:"OperatorCode";a:0:{}s:13:"TransaccionID";s:10:"1477714998";s:9:"ResultStr";s:20:"Ha ocurrido un error";s:9:"ConfirmId";s:1:"-";s:12:"CurrencyCode";s:3:"CUC";s:8:"ResultId";i:0;} */

		// Dimecuba usa ResultId con 0 ok y -1 fallo, mientras que esperamos los mismo que Ezetop, 1 ok, 0 fallo. Transformamos aquí.
		$response = $arr;
		$response['ResultStr'] = $response['Message'];
		$response['MobilePaymentID'] = $response['TransaccionID'];
		$response['Amount'] = $this->Amount;
		
		if (is_array($response['ConfirmID'])) 
		{
			$response['confirmid'] = "-";
		}
		
		$response['confirmid'] = $response['ConfirmID'];
		$response['CurrencyCode'] = "CUC";
		$response['ResultId'] = 0;

		if (isset($response['ID']) && $response['ID'] == '0') 
		{
			$response['ResultId'] = 1;
		}
		else if ($response['Message'] == "Recarga en proceso" || 
			strpos( $d_result, '504 Gateway Time-out') !== false )
		{
			// En proceso (simulamos ok)
			$response['ID'] = 0;
			$response['ResultId'] = 1;
			$response['ResultStr'] = 'pendiente_revision';
			$sql = "insert into dc_ptes (fecha, result_id, result_msg, result, transaccion_id, token) values (".
				"now(), 
				'".$response['ID']."', 
				'".$response['ResultStr']."',
				'".$this->bbdd_escapar(serialize($d_result))."',
				'{$this->PhoneNumber}',
				'".$this->token."')";
			$dbconn_e = new DbConn("ensip_jycteladm");
			$dbconn_e->query($sql);
			
		}
		syslog(LOG_INFO, __FILE__.": d_result: $d_result, Response: ".serialize($response)." -{$this->token}-");

		return $response;
	}

	function checkRecharge( $customerid = '' ) {

		$field = 'number';
		$val = $this->PhoneNumber;
		if( $customerid != '' ){
			$field = 'transactionId';
			$val = $customerid;
		}

		$post_fields = array("api_User" => $this->user, "api_Pass" => $this->pass,$field => $val);
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->url_check);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res_curl = curl_exec($ch);
		syslog(LOG_INFO, __FILE__." : customerid: ".$customerid." -".$this->url_check."  arr: $field ".serialize($post_fields));
		syslog(LOG_INFO, __FILE__." : customerid: ".$customerid." - arr: $field ".serialize($res_curl));

		curl_close ($ch);

		return $res_curl;
	}

	public function bbdd_escapar($texto) {
		$texto = trim($texto);
		$texto = addslashes($texto);
		$texto = preg_replace("/;/", "\;", $texto);
	}
	function setPhone($phone) {
		return $this->PhoneNumber = $phone;
	}
}
?>
