<?php
	$req = 'cmd=_notify-validate';
	require('../../../../../../wp-config.php');
	$wp->init(); $wp->parse_request(); $wp->query_posts();
	$wp->register_globals(); $wp->send_headers();
	$reservation_support_mail = get_option("reservations_support_mail");
	$paypalOptions = get_option('reservations_paypal_options');
	if(!$paypalOptions || !is_array($paypalOptions) || !isset($paypalOptions['modus']) || $paypalOptions['modus'] == 'off') exit;
	$autosave = get_option('reservations_autoapprove');
	if($paypalOptions['modus'] == 'sandbox') $host = 'sandbox.paypal';
	else $host = 'paypal';
	$keys = '';

	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);
		$req .= "&$key=$value";
		$keys .= $key." = ". $value."\n";
	}

	if(easyreservations_check_curl()){
		if($paypalOptions['er_pay_ssl'] == 1) $url = "https://www.".$host.".com/cgi-bin/webscr";
		else $url = "http://www.".$host.".com/cgi-bin/webscr";

		$ch = curl_init($url);    // Starts the curl handler
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: ".strlen($req)));
		curl_setopt($ch, CURLOPT_HEADER , 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		$res = curl_exec($ch); // run the curl process (and return the result to $result
		$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		if (strcmp ($res, "VERIFIED") !== false) {
			$_POST['owner'] = str_replace( '%40', '@',$_POST['owner'] );
			$_POST['receiver_email'] = str_replace( '%40', '@',$_POST['receiver_email'] );
			$_POST['receiver_id'] = str_replace( '%40', '@',$_POST['receiver_id'] );
			if(($_POST['receiver_email'] == $paypalOptions['owner'] || $_POST['business'] == $paypalOptions['owner'] || $_POST['receiver_id'] == $paypalOptions['owner'] ) && $_POST['mc_currency'] == $paypalOptions['currency'] && $_POST['payment_status'] == 'Completed'){
				if(easyreservations_verify_nonce($_POST['custom'], 'easy-pay-submit' )){
					$array = array();
					if(strpos($_POST['invoice'], '-') === false){
						$res = new Reservation((int) $_POST['invoice']);
						$array[] = 'pricepaid';
						$res->updatePricepaid($_POST['mc_gross']);
						if($autosave == 2 || $autosave == 3){
							$approve = easyreservations_auto_approve($res, false);
							if($approve){
								$res->status = 'yes';
								$array[] = 'status';
								$array[] = 'resourcenumber';
							}
						}
						$res->editReservation($array, false, array('reservations_email_to_admin_paypal', 'reservations_email_to_user_paypal'), array(false, $res->email));
					} else {
						$explode = explode('-', $_POST['invoice']);
						$total = count($explode) - 1;
						$totalpaid = $_POST['mc_gross'];
						foreach($explode as $key => $id){
							$res = new Reservation((int) $id);
							$res->Calculate();
							if($res->price <= $totalpaid){
								if($key == $total) $paid = $totalpaid;
								$paid = $res->price;
							} elseif($totalpaid > 0) $paid = $totalpaid;
							else $paid = 0;
							$totalpaid -= $res->price;
							$array[] = 'pricepaid';
							$res->updatePricepaid($paid);
							if($res->status != 'yes' && ($autosave == 2 || $autosave == 3)){
								$approve = easyreservations_auto_approve($res, false);
								if($approve){
									$res->status = 'yes';
									$array[] = 'status';
									$array[] = 'resourcenumber';
								}
							}
							$res->editReservation($array, false, array('reservations_email_to_admin_paypal', 'reservations_email_to_user_paypal'), array(false, $res->email));
						}
					}
					header( 'Content-Type:' );
				}  else wp_mail($reservation_support_mail, "Error with PayPal IPN Callback", "Wrong nonce: ".$_POST['custom']);
			} else wp_mail($reservation_support_mail, "Error with PayPal Payment", "Could be wrong owner, wrong currency or an uncomplete payment. \n Details \n".$keys);
		} else  wp_mail($reservation_support_mail, "Error at PayPals Validation", "Status: $status -  :$error");
	} else {
		//$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
		//$fp = fsockopen ('ssl://ipnpb.paypal.com', 443, $errno, $errstr, 30);
		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		if($paypalOptions['er_pay_ssl'] == 1) $fp = fsockopen ( 'ssl://www.'.$host.'.com', "443", $err_num, $err_str, 60 );
		else $fp = fsockopen ('www.'.$host.'.com', "80", $errno, $errstr, 30);

		if (!$fp) {
		// HTTP ERROR
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") !== false){
					$_POST['owner'] = str_replace( '%40', '@',$_POST['owner'] );
					$_POST['receiver_email'] = str_replace( '%40', '@',$_POST['receiver_email'] );
					$_POST['receiver_id'] = str_replace( '%40', '@',$_POST['receiver_id'] );
					if (!easyreservations_verify_nonce($_POST['custom'], 'easy-pay-submit' )) die('');
					if(($_POST['receiver_email'] == $paypalOptions['owner'] || $_POST['business'] == $paypalOptions['owner'] ) && $_POST['mc_currency'] == $paypalOptions['currency'] && $_POST['payment_status'] == 'Completed'){
						easyreservations_ipn_callback($_POST['invoice'], $_POST['mc_gross']);
					} else wp_mail($reservation_support_mail, "Error with PayPal Payment", "Could be wrong owner, wrong currency or an uncomplete payment.\n\nDetails:\n\n".$keys);
					header( 'Content-Type:' );
				}
			}
			fclose ($fp);
		}
	}?>