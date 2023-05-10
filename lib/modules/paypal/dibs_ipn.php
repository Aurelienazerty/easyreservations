<?php
	require('../../../../../../wp-config.php');
	$wp->init(); $wp->parse_request(); $wp->query_posts();
	$wp->register_globals(); $wp->send_headers();
	$dibsOptions = get_option('reservations_dibs_options');
	if(!$dibsOptions || !is_array($dibsOptions) || !isset($dibsOptions['modus']) || $dibsOptions['modus'] == 'off') exit;
	foreach ($_POST as $key => $value) {
		$array[$key] = $value;
		$value = urlencode(stripslashes($value));
		$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);
		$keys .= $key." = ". $value."\n";
	}
	ksort($array); // Sort the posted values by alphanumeric
	$string = "";
	foreach ($array	 as $key => $value) {
		if ($key != "MAC") { // Don't include the MAC in the calculation of the MAC.
			if (strlen($string) > 0) $string .= "&";
			$string .= "$key=$value"; // create string representation
		}
	}
	$string = str_replace('\"','"',$string);
	$mackey = '';
	foreach (explode("\n", trim(chunk_split($dibsOptions['mackey'], 2))) as $h) $mackey .= chr(hexdec($h));
	$MAC = hash_hmac("sha256",  $string, $mackey);

	$reservation_support_mail = get_option("reservations_support_mail");
	$autosave = get_option('reservations_autoapprove');
	if(isset($_POST['status']) && $_POST['status'] == 'ACCEPTED' && $_POST['merchant'] == $dibsOptions['merchant'] && $_POST['amount'] > 0 && $MAC == $_POST['MAC']){
		if(easyreservations_verify_nonce($_POST['s_custom'], 'easy-pay-submit' )){
			$_POST['amount'] = (float) substr($_POST['amount'],0,-2).'.'.substr($_POST['amount'],-2);
			easyreservations_ipn_callback($_POST['orderId'], $_POST['amount']);
		} else wp_mail($reservation_support_mail, "Error at DIBS Payment Custom", $keys.'MAC: '.$MAC);
	} else wp_mail($reservation_support_mail, "Error at DIBS Payment", $keys.'MAC: '.$MAC);
	exit;
?>