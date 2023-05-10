<?php
/**
 * Created by PhpStorm.
 * User: feryaz
 * Date: 14.08.15
 * Time: 09:28
 */

function redsys_settings_page(){
	include_once 'settings.php';
}
function register_my_redsys_gateway($gateways){
	$gateways['redsys_gateway'] = array(
		'name' => 'REDSYS Gateway',
		'form_name' => 'redsys_payment_form',
		'amount_name' => 'Ds_Merchant_Amount'
	);
	return $gateways;
}
add_filter('reservations_register_gateway', 'register_my_redsys_gateway', 10, 1);



function generate_redsys_payment_form($res,$id,$title,$price,$nonce){
	//Recuperamos los datos de config.

	$entorno      = get_option( 'redsys_gateway_entorno' );
	$nombre       = get_option( 'redsys_gateway_name' );
	$fuc                    = get_option( 'redsys_gateway_fuc' );
	$tipopago       = get_option( 'redsys_gateway_tipopago' );
	$clave                  = get_option( 'redsys_gateway_clave' );
	$terminal     = get_option( 'redsys_gateway_terminal' );
	$firma          = get_option( 'redsys_gateway_firma' );
	$moneda                 = get_option( 'redsys_gateway_moneda' );
	$trans          = get_option( 'redsys_gateway_trans' );
	$recargo        = get_option( 'redsys_gateway_recargo' );
	$idioma         = get_option( 'redsys_gateway_idioma' );

	//Callback
	$urltienda = WP_PLUGIN_URL.'/apartamentos/redsys_ipn.php';

	$transaction_amount = floatval($price);

	$numpedido = $id;


	// Obtenemos el valor de la config del idioma
	if($idioma=="no"){
		$idiomaFinal="0";
	}
	else {
		$idioma_web = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
		switch ($idioma_web) {
			case 'es':
				$idiomaFinal='001';
				break;
			case 'en':
				$idiomaFinal='002';
				break;
			case 'ca':
				$idiomaFinal='003';
				break;
			case 'fr':
				$idiomaFinal='004';
				break;
			case 'de':
				$idiomaFinal='005';
				break;
			case 'nl':
				$idiomaFinal='006';
				break;
			case 'it':
				$idiomaFinal='007';
				break;
			case 'sv':
				$idiomaFinal='008';
				break;
			case 'pt':
				$idiomaFinal='009';
				break;
			case 'pl':
				$idiomaFinal='011';
				break;
			case 'gl':
				$idiomaFinal='012';
				break;
			case 'eu':
				$idiomaFinal='013';
				break;
			default:
				$idiomaFinal='002';
		}
	}


	// Generamos la firma
	// Cálculo del SHA1 $trans . $urltienda
	if($firma=='completa'){
		$mensaje = $transaction_amount . $numpedido . $codigo . $moneda . $clave;
	}else{
		$mensaje = $transaction_amount . $numpedido. $codigo . $moneda . $trans .$urltienda . $clave;

	}
	$firmaFinal = strtoupper(sha1($mensaje));

	$resys_args = array(
		'Ds_Merchant_Amount' => $transaction_amount,
		'Ds_Merchant_Currency' => $moneda,
		'Ds_Merchant_Order' => $numpedido,
		'Ds_Merchant_MerchantCode' => $codigo,
		'Ds_Merchant_Terminal' => $terminal,
		'Ds_Merchant_TransactionType' => $trans,
		'Ds_Merchant_Titular' =>'Sin nombre',
		'Ds_Merchant_MerchantName' => $nombre,
		'Ds_Merchant_MerchantData' => sha1($urltienda),
		'Ds_Merchant_MerchantURL' => $urltienda,
		'Ds_Merchant_ProductDescription' => $title,
		'Ds_Merchant_UrlOK' => home_url(),
		'Ds_Merchant_UrlKO' => home_url(),
		'Ds_Merchant_MerchantSignature' => $firmaFinal,
		'Ds_Merchant_ConsumerLanguage' => $idiomaFinal,
		'Ds_Merchant_PayMethods' => $tipopago,
	);

	//Se establecen los input del formulario con los datos del pedido y la redirección
	$resys_args_array = array();
	foreach($resys_args as $key => $value){
		$resys_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
	}

	//Se establece el entorno del SIS
	if($entorno=="Sis-d"){
		$action="http://sis-d.redsys.es/sis/realizarPago";
	}
	else if($entorno=="Sis-i"){
		$action="https://sis-i.redsys.es:25443/sis/realizarPago";
	}
	else if($entorno=="Sis-t"){
		$action="https://sis-t.redsys.es:25443/sis/realizarPago";
	}
	else{
		$action="https://sis.redsys.es/sis/realizarPago";
	}

	return '<form action="'.$action.'" method="post" id="redsys_payment_form">
           <input type="text"
           <input type="image" src="'.WP_PLUGIN_URL.'/apartamentos/redsys_ipn.php" border="0" name="submit" alt="">
       ' . implode('', $resys_args_array) . '

        </form>';
}


add_filter('reservations_generate_gateway_button', 'generate_redsys_payment_form', 10, 5);
