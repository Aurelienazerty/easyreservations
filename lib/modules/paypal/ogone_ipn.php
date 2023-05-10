<?php
require('../../../../../../wp-config.php');
$wp->init(); $wp->parse_request(); $wp->query_posts();
$wp->register_globals(); $wp->send_headers();
$ogoneOptions = get_option('reservations_ogone_options');
$string = '';
if(!$ogoneOptions || !is_array($ogoneOptions) || !isset($ogoneOptions['modus']) || $ogoneOptions['modus'] == 'off') exit;
$data = array_change_key_case($_POST, CASE_UPPER);
ksort($data, SORT_STRING);
foreach($data as $key => $value){
  if($key != 'SHASIGN' && strlen($value) !== 0){
    $string .= sprintf('%s=%s%s', $key, $value, $ogoneOptions['shapass_out']);
  }
}
//$string = str_replace('\"','"',$string);
$MAC = strtoupper(sha1($string));

foreach ($_POST as $key => $value) {
  $value = urlencode(stripslashes($value));
  $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);
  $keys .= $key." = ". $value."\n";
}

$reservation_support_mail = get_option("reservations_support_mail");
$autosave = get_option('reservations_autoapprove');
if(isset($_POST['STATUS']) && ($_POST['STATUS'] == 5 || $_POST['STATUS'] == 51 || $_POST['STATUS'] == 9) && $_POST['amount'] > 0 && $MAC == $_POST['SHASIGN']){
  if(easyreservations_verify_nonce($_POST['COMPLUS'], 'easy-pay-submit' )){
	  easyreservations_ipn_callback($_POST['orderID'], $_POST['amount']);
  } else wp_mail($reservation_support_mail, "Error at DIBS Payment", $keys.'MAC: '.$MAC.' MAC2: '.$MAC2.' STRING: '.$string);
} else wp_mail($reservation_support_mail, "Error at DIBS Payment", $keys.'MAC: '.$MAC.' MAC2: '.$MAC2.' STRING: '.$string);
exit;
?>