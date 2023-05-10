<?php
	require('../../../../../../wp-config.php');
	$wp->init(); $wp->parse_request(); $wp->query_posts();
	$wp->register_globals(); $wp->send_headers();
	
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);
		$keys .= $key." = ". $value."\n";
	}

	$reservation_support_mail = get_option("reservations_support_mail");
	$authOptions = get_option('reservations_authorize_options');
	if(!$authOptions || !is_array($authOptions) || !isset($authOptions['modus']) || $authOptions['modus'] == 'off') exit;
	$autosave = get_option('reservations_autoapprove');
	if(!isset($_POST['x_response_code']) && isset($_POST['vendor_order_id']) && !empty($_POST['vendor_order_id'])){
		$service = '2Checkout';
		$_POST['custom'] = $_POST['vendor_order_id'];
		if(isset($_POST['item_id_1']) && !empty($_POST['item_id_1'])) $_POST['x_invoice_num'] = $_POST['item_id_1'];
		if(isset($_POST['invoice_list_amount']) && is_numeric($_POST['invoice_list_amount'])) $_POST['x_invoice_num'] = $_POST['item_id_1'];
	} else $service = 'Authorize.Net';
	if(((isset($_POST['x_response_code']) && $_POST['x_response_code'] == 1) || (isset($_POST['message_type']) && $_POST['message_type'] == 'FRAUD_STATUS_CHANGED')) && $_POST['x_amount'] > 0){
		if(easyreservations_verify_nonce($_POST['custom'], 'easy-pay-submit' )){
			easyreservations_ipn_callback($_POST['x_invoice_num'], $_POST['x_amount']);
			echo '<html><body>Correct <meta http-equiv="refresh" content="0; url='.$authOptions['return_url'].'"></body></html>';
			exit;
		} else wp_mail($reservation_support_mail, "Error at $service Payment Custom", $keys);
	} else wp_mail($reservation_support_mail, "Error at $service Payment", $keys);
	echo '<html><body>False <meta http-equiv="refresh" content="0; url='.$authOptions['cancel_url'].'"></body></html>';
	exit;
?>