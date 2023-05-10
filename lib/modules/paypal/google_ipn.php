<?php
	if(!isset($_POST['serial-number'])) exit;

	require('../../../../../../wp-config.php');
	$wp->init(); $wp->parse_request(); $wp->query_posts();
	$wp->register_globals(); $wp->send_headers();

	$payment = '';
	$googleOptions = get_option('reservations_wallet_options');
	if(!$googleOptions || !is_array($googleOptions) || !isset($googleOptions['modus']) || $googleOptions['modus'] == 'off') exit;
	$authKey = $googleOptions['merchantid'].':'.$googleOptions['merchantkey'];
	if($googleOptions['modus'] == 'on'){
		$url = 'https://'.$authKey.'@checkout.google.com/api/checkout/v2/reports/Merchant/';
		$urlshort = 'http://checkout.google.com/schema/2';
	} else {
		$url = 'https://'.$authKey.'@sandbox.google.com/checkout/api/checkout/v2/reports/Merchant/';
		$urlshort = 'http://sandbox.google.com/schema/2';
	}
	$urlshort = 'http://checkout.google.com/schema/2';
	$url = $url.$googleOptions['merchantid'];
	
	$serial = $_POST['serial-number'];
	if(easyreservations_check_curl()){
		$header_arr = array("Authorization: Basic ".base64_encode($authKey),
                    "Content-Type: application/xml; charset=UTF-8",
            "Accept: application/xml; charset=UTF-8");
		$request='_type=notification-history-request&serial-number='.$serial;
		$xml = '<notification-history-request xmlns="http://checkout.google.com/schema/2"><serial-number>'.$serial.'</serial-number></notification-history-request>';

		$ch = curl_init($url);    // Starts the curl handler
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$res = curl_exec($ch); // run the curl process (and return the result to $result
		$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if (curl_errno($ch)) {
		  $payment .= ', error! :'.curl_error($ch);
		} else {
			$xml = simplexml_load_string( $res );
			$json = json_encode( $xml );
			$payment = json_decode( $json,TRUE );
			$serial_part = explode('-', $serial);
			$serial_part = $serial_part[0];
			if(is_array($payment)){
				$queue = get_option('reservations_google_wallet_queue');
				if(!$queue) $queue = array();
				if(isset($payment['shopping-cart'])){
					$item = $payment['shopping-cart']['items']['item'];
					$id = $item['merchant-item-id'];
					$price = $item['unit_price'];
					$queue[$serial_part] = array($id,$price);
					update_option('reservations_google_wallet_queue', $queue);
				} elseif(isset($payment['new-financial-order-state']) && $payment['new-financial-order-state'] == 'CHARGED'){
					if(isset($queue[$serial_part])){
						unset($queue[$serial_part]);
						update_option('reservations_google_wallet_queue', $queue);
						$autosave = get_option('reservations_autoapprove');
						easyreservations_ipn_callback($queue[$serial_part][0], $queue[$serial_part][1]);
					}
				}
			}
		}
	}

	exit;
?>