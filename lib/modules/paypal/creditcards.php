<?php
if(file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/class.creditcard.php")) include_once(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/class.creditcard.php");

function easyreservations_get_credit_cards(){
	$ccoptions =  get_option('reservations_credit_card_options');
	$available = $ccoptions['avail'];
	if(!$available || empty($available) || !array($available)) $available = array('American Express','Diners Club Carte Blanche','Diners Club','Diners Club Enroute','Discover','JCB','Maestro','MasterCard','Solo','Switch','VISA','VISA Electron','LaserCard');
	$ccs = array();
	if(in_array('American Express',$available)) $ccs[] = array ('name' => 'American Express',  'length' => '15', 'prefixes' => '34,37','checkdigit' => true);
	if(in_array('Diners Club Carte Blanche',$available)) $ccs[] = array ('name' => 'Diners Club Carte Blanche', 'length' => '14', 'prefixes' => '300,301,302,303,304,305','checkdigit' => true );
	if(in_array('Diners Club',$available)) $ccs[] = array ('name' => 'Diners Club', 'length' => '14,16','prefixes' => '36,54,55','checkdigit' => true );
	if(in_array('Diners Club Enroute',$available)) $ccs[] = array ('name' => 'Diners Club Enroute', 'length' => '15', 'prefixes' => '2014,2149','checkdigit' => true );
	if(in_array('Discover',$available)) $ccs[] = array ('name' => 'Discover', 'length' => '16', 'prefixes' => '6011,622,64,65','checkdigit' => true );
	if(in_array('JCB',$available)) $ccs[] = array ('name' => 'JCB', 'length' => '16', 'prefixes' => '35','checkdigit' => true );
	if(in_array('Maestro',$available)) $ccs[] = array ('name' => 'Maestro', 'length' => '12,13,14,15,16,18,19', 'prefixes' => '5018,5020,5038,6304,6759,6761,6762,6763','checkdigit' => true );
	if(in_array('MasterCard',$available)) $ccs[] = array ('name' => 'MasterCard', 'length' => '16', 'prefixes' => '51,52,53,54,55','checkdigit' => true );
	if(in_array('Solo',$available)) $ccs[] = array ('name' => 'Solo', 'length' => '16,18,19', 'prefixes' => '6334,6767','checkdigit' => true );
	if(in_array('Switch',$available)) $ccs[] = array ('name' => 'Switch', 'length' => '16,18,19', 'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759','checkdigit' => true );
	if(in_array('VISA',$available)) $ccs[] = array ('name' => 'VISA', 'length' => '16', 'prefixes' => '4','checkdigit' => true );
	if(in_array('VISA Electron',$available)) $ccs[] = array ('name' => 'VISA Electron',  'length' => '16',  'prefixes' => '4026,417500,4508,4844,4913,4917', 'checkdigit' => true);
	if(in_array('LaserCard',$available)) $ccs[] = array ('name' => 'LaserCard', 'length' => '16,17,18,19', 'prefixes' => '6304,6706,6771,6709', 'checkdigit' => true );
	return $ccs;
}

function easyreservations_create_save_file($content = 'save password'){
	if(!file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.php")){
		$string ='<?php $cckey = "'.str_replace(array("'", '"'), '', $content).'"; ?>';
		$fh = fopen(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.php", 'w+');
		if($fh){
			fwrite($fh, $string);
			fclose($fh);
			easyreservations_get_credit_card_pass();
		} else {
			return false;
		}
	}
}

function easyreservations_get_credit_card_pass(){
	if(file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.txt") && $handle = fopen(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.txt", "r")){
		$pass = fread($handle, 32);
		if(!empty($pass)){
			easyreservations_create_save_file($pass);
			fclose($handle);
			unlink(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.txt");
		}
	} else {
		if(file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.php")) include(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.php");
		if(isset($cckey) && !empty($cckey)) return $cckey;
		easyreservations_create_save_file();
	}
	return "easysecure pass";
}

function easyreservations_get_credit_card_infos($res){
	if(!empty($res->custom)){
		$creditcard = $res->getCustoms($res->custom, 'credit');
		if($creditcard && !empty($creditcard)){
			foreach($creditcard as $value){
				if(!empty($value) && isset($value['value'])){
					if(is_array($value['value'])){
						$name = $value['value'][1];
						$value['value'] = $value['value'][0];
					} else $name = '';
					$cc = new CreditCardFreezer();
					try {
						$cc->setPassKey(easyreservations_get_credit_card_pass())->set(CreditCardFreezer::SECURE_STORE, $value['value'], true);
					} catch(Exception $e){
						echo '<div class="error">'.$e->getMessage().'</div>';
					}
					return array('number' => $cc->get(CreditCardFreezer::NUMBER),'month' => $cc->get(CreditCardFreezer::EXPIRE_MONTH), 'year' => $cc->get(CreditCardFreezer::EXPIRE_YEAR),  'name' => $name); // 1234123412341234
				}
			}
		}
	}
	return false;
}

function easyreservations_save_credit_card_infos($res, $info, $save = false){
	$cc = new CreditCardFreezer();
	try {
		$secure = $cc->set(CreditCardFreezer::NUMBER,$info['number'])->set(CreditCardFreezer::EXPIRE_MONTH, $info['month'])->set(CreditCardFreezer::EXPIRE_YEAR, $info['year'])->setPassKey(easyreservations_get_credit_card_pass())->get();
		if(isset($_POST['ccname']) && !empty($_POST['ccname'])) $secure = array($secure, $_POST['ccname']);
		$res->Customs(array('type' => 'credit', 'value' => $secure), true, false, false, 'credit');
		if($save) $res->editReservation(array('custom'));
		return $res;
	} catch(CreditCardFreezer_Exception $e){
		var_dump($e->getMessage());
		return false;
	}
}

if(is_admin()){
	function easyreservations_delete_credit_card_infos(){
		global $easy_errors;
		if(isset($_GET['deletecc']) && isset($_GET['edit']) && is_numeric($_GET['edit'])){
			if(!wp_verify_nonce($_GET['_wpnonce'], 'easy-delete-cc')) die('Security check'); 
			$res = new Reservation((int) $_GET['edit']);
			$before = $res->custom;
			$res->Customs(array(), true, false, false, 'credit');
			if($before != $res->custom){
				$res->editReservation(array('custom'));
				$easy_errors[] = array( 'updated', __( 'Credit card data deleted' , 'easyReservations' ));
			} else $easy_errors[] = array( 'error', __( 'Credit card data couldn\'t be deleted' , 'easyReservations' ));
		}
	}
	add_action('easy_dashboard_header_start','easyreservations_delete_credit_card_infos');

	function easyreservations_delete_all_credit_card_infos(){
		if(isset($_GET['deleteallcc']) && isset($_GET['site']) && $_GET['site'] == "pay" && current_user_can('delete_plugins')){
			if(!wp_verify_nonce($_GET['_wpnonce'], 'easy-delete-all-cc')) die('Security check');
			global $wpdb;
			$all_reservations = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."reservations");
			foreach($all_reservations as $reservation){
				$res = new Reservation($reservation->id, (array) $reservation);
				$before = $res->custom;
				$res->Customs(array(), true, false, false, 'credit');
				if($before != $res->custom){
					$res->editReservation(array('custom'));
				}
			}
			echo '<br><div class="updated"><p>'.__( 'All credit cards deleted' , 'easyReservations').'</p></div>';
		}
	}
	add_action('er_set_add', 'easyreservations_delete_all_credit_card_infos', 16);

	function easyreservations_check_credit_card($cardnumber, $cardname, $cvv){
		$cards = easyreservations_get_credit_cards();
		$ccErrors[0] = array('type',__("Unknown card type", "easyReservations"));
		$ccErrors[1] = array('number',__("No card number provided", "easyReservations"));
		$ccErrors[2] = array('number',__("Credit card number has invalid format", "easyReservations"));
		$ccErrors[3] = array('number',__("Credit card number is invalid", "easyReservations"));
		$ccErrors[4] = array('number',__("Credit card number is wrong length", "easyReservations"));
		$ccErrors[5] = array('easyccname',__("Please enter credit cards owner name", "easyReservations"));

		$cardType = -1;
		for($i=0; $i<sizeof($cards); $i++){
			if(strtolower($cardname) == strtolower($cards[$i]['name'])){
				$cardType = $i;
				break;
			}
		}

		if($cardType == -1){
			return $ccErrors[0];
		}

		if(strlen($cardnumber) == 0){
			return $ccErrors[1];
		}

		$cardNo = str_replace (' ', '', $cardnumber);  
		if(!preg_match("/^[0-9]{13,19}$/",$cardNo)){
			return $ccErrors[2];
		}

		if($cards[$cardType]['checkdigit']){
			$checksum = 0;
			$j = 1;
			for ($i = strlen($cardNo) - 1; $i >= 0; $i--){
				$calc = $cardNo[$i] * $j;
				if($calc > 9){
					$checksum = $checksum + 1;
					$calc = $calc - 10;
				}
				$checksum = $checksum + $calc;
				if($j ==1) $j = 2;
				else $j = 1;
			}

			if($checksum % 10 != 0){
				$errornumber = 3;     
				$errortext = $ccErrors[$errornumber];
				return $errortext; 
			}
		}

		$prefix = explode(',',$cards[$cardType]['prefixes']);
		$PrefixValid = false; 
		for ($i=0; $i<sizeof($prefix); $i++){
			$exp = '/^' . $prefix[$i] . '/';
			if(preg_match($exp,$cardNo)){
				$PrefixValid = true;
				break;
			}
		}

		if(!$PrefixValid){
			return $ccErrors[3];
		}

		$LengthValid = false;
		$lengths = explode(',',$cards[$cardType]['length']);
		for ($j=0; $j<sizeof($lengths); $j++){
			if(strlen($cardNo) == $lengths[$j]){
				$LengthValid = true;
				break;
			}
		}

		if(!$LengthValid){
			return $ccErrors[4];
		}
		
		if(isset($_POST['ccname']) && empty($_POST['ccname'])){
			return $ccErrors[5];
		} elseif(isset($_POST['ccname']) && $_POST['ccname'] == 'no'){
			$_POST['ccname'] = '';
		}

		if($cardname == 'American Express'){
			if(!preg_match('/^[0-9]{4}$/', $cvv)) return array('cvv',__("Enter correct CVV code", "easyReservations"));
		} elseif(!preg_match('/^[0-9]{3}$/', $cvv)) return array('cvv',__("Enter correct CVV code", "easyReservations"));

		return true;
	}

	add_filter('easy-res-view-table-bottom', 'easyreservations_credit_card_view', 9, 1);

	function easyreservations_credit_card_view($res){
		$credits = easyreservations_get_credit_card_infos($res);
		if($credits){
			?><tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/card.png"> <?php printf ( __( 'Credit Card' , 'easyReservations' ));?></td> 
					<td><?php if($credits['name'] && !empty($credits['name'])) echo '<b>'.__( 'Name' , 'easyReservations' ).'</b> '.$credits['name'].' '; ?><b><?php echo __( 'Number' , 'easyReservations' ).': '.$credits['number']; ?></b> <b><?php echo __( 'Expires' , 'easyReservations' ).': '.$credits['month']; ?>/<?php echo $credits['year']; ?></b></td>
				</tr>
			<?php
		}
	}

	add_action('easy-dash-edit-side-middle', 'easyreservations_credit_card_edit_box', 10, 1);

	function easyreservations_credit_card_edit_box($res){
		$ccoptions =  get_option('reservations_credit_card_options');
		$credits = easyreservations_get_credit_card_infos($res);
		$delete = '<a href="'.wp_nonce_url( 'admin.php?page=reservations&edit='.$res->id.'&deletecc', 'easy-delete-cc' ).'">'.__( 'Delete' , 'easyReservations' ).'</a>';
		if(!$credits){
			$credits = array('number' => '', 'month' => date('m'), 'year' => date('Y'), 'name' => false);
			$delete = '';
		}
		?><table class="<?php echo RESERVATIONS_STYLE; ?>" id="easy_edit_creditcard" style="min-width:320px;width:320px;margin-bottom:4px">
				<thead>
					<tr>
						<th><?php echo __( 'Credit Card' , 'easyReservations' );?><span style="float:right;color:#ff0000;font-weight:bold;"><?php echo $delete;?></span></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php if(isset($ccoptions['name']) && $ccoptions['name'] == 1){ ?><b style="width:50px; display: inline-block"><?php echo __( 'Name' , 'easyReservations' );?></b> <input type="text" name="ccname" value="<?php echo $credits['name']; ?>"><br><?php } ?>
							<b style="width:50px; display: inline-block"><?php echo __( 'Number' , 'easyReservations' );?></b> <input type="text" name="ccnumber" value="<?php if(isset($credits['number'])) echo $credits['number']; ?>"><br>
							<b style="width:50px; display: inline-block"><?php echo __( 'Month' , 'easyReservations' );?></b> <select name="ccmonth"><?php echo easyreservations_num_options('01','12', $credits['month']); ?></select> 
							<b style="width:50px; text-align: right; display: inline-block"><?php echo __( 'Year' , 'easyReservations' );?></b> <select name="ccyear"><?php echo easyreservations_num_options(2000,2035, $credits['year']); ?></select>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}
	add_filter('easy-edit-prices', 'easyreservations_admin_add_creditcard_to_res', 9, 1);

	function easyreservations_admin_add_creditcard_to_res($res){
		if(isset($_POST['ccnumber']) && !empty($_POST['ccnumber'])){
			$_POST['ccnumber'] = preg_replace("/[^0-9]/","",$_POST['ccnumber']);
			if(strlen($_POST['ccnumber']) > 16 || strlen($_POST['ccnumber']) < 12){
				global $easy_errors;
				$easy_errors[] = array( 'error' , __( 'Credit Card Number must be a number and between 12 and 16 characters long', 'easyReservations' ));
			}
			$res = easyreservations_save_credit_card_infos($res, array('number' =>$_POST['ccnumber'], 'month' => $_POST['ccmonth'], 'year' => $_POST['ccyear']));
		} else {
			$res->Customs(false,true,false,false,'credit');
		}
		return $res;
	}
	
	add_action('er_set_save', 'easyreservations_cc_save_settings');

	function easyreservations_cc_save_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "pay" && isset($_POST['action']) && $_POST['action'] == "reservation_cc_settings"){
			if (!wp_verify_nonce($_POST['easy-set-ccc'], 'easy-set-ccc' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
			if(isset($_POST['avail'])){
				if(isset($_POST['stripe_address'])) $address = 1;
				else $address = 0;
				if(isset($_POST['ccname'])) $name = 1;
				else $name = 0;
				$stripe = array('modus' => $_POST['stripe_modus'], 'pubkey' => $_POST['stripe_pubkey'], 'seckey' => $_POST['stripe_seckey'], 'desc' => $_POST['stripe_desc'], 'currency' => $_POST['stripe_currency'], 'address' => $address, 'label' => $_POST['stripe_label'], 'sublabel' => $_POST['stripe_sublabel']);
				if(isset($_POST['status'])) $status = 1; else $status = 0;
				if(isset($_POST['approve'])) $approve = 1; else $approve = 0;
				$options = array('status' => $status, 'approve' => $approve, 'avail' => $_POST['avail'], 'name' => $name,'stripe' => $stripe);
				update_option('reservations_credit_card_options', $options);
				if($_POST['type'] == 'cc') echo '<br><div class="updated"><p>'.__( 'Credit Cards settings changed' , 'easyReservations').'</p></div>';
				else echo '<br><div class="updated"><p>Stripe '.__( 'settings changed' , 'easyReservations').'</p></div>';
			}
		}
	}
	add_action('er_set_add', 'easyreservations_cc_add_settings', 15);

	function easyreservations_cc_add_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "pay"){
			$ccoptions =  get_option('reservations_credit_card_options');
			if(file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.php")){
				include_once(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/cckey.php");
				if(isset($cckey) && !empty($cckey)){
					if($cckey == "save password") $error = sprintf(__( 'Before using open the file %1$s and enter a long random password' , 'easyReservations' ), '<b>/wp-content/plugins/easyreservations/lib/modules/paypal/cckey.php</b>');
				} else $error = sprintf(__( 'Can\'t open the file %1$s with the security key. Try to give it and it\'s folder CHMOD 755 or higher.' , 'easyReservations' ), 'cckey.php', '<b>/wp-content/plugins/easyreservations/lib/modules/paypal/</b>');
			} elseif(easyreservations_create_save_file()) $error = sprintf(__( 'Before using create a file named %1$s in %2$s and enter %3$s' , 'easyReservations' ),'<b>cckey.php</b>', '<b>/wp-content/plugins/easyreservations/lib/modules/paypal/</b>', '<code><&quest;php $cckey = "your long password"; &quest;></code>');
			$all_cards = array('American Express','Diners Club Carte Blanche','Diners Club','Diners Club Enroute','Discover','JCB','Maestro','MasterCard','Solo','Switch','VISA','VISA Electron','LaserCard');
			if(!$ccoptions) $ccoptions = array('status' => 0,'approve' => 0, 'avail' => $all_cards, 'stripe' => array('modus' => 0));
				?><form method="post" action="admin.php?page=reservation-settings&site=pay"  id="reservation_cc_settings" name="reservation_cc_settings" style="margin-top:5px">
					<?php wp_nonce_field('easy-set-ccc','easy-set-ccc'); ?>
					<input type="hidden" name="action" value="reservation_cc_settings">
					<input type="hidden" name="type" value="cc">

					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%">
						<thead>
							<tr>
								<th colspan="2">Stripe <?php echo __( 'settings' , 'easyReservations' );?></th>
							</tr>
						</thead>
						<tbody>
							<tr class="alternate">
								<td style="font-weight:bold"><?php echo __( 'Mode' , 'easyReservations' );?></td>
								<td>
									<select name="stripe_modus" onchange="if(jQuery(this).val() == 2) jQuery('#stripe_popup').fadeIn('slow'); else jQuery('#stripe_popup').fadeOut('slow');">
										<option <?php selected( $ccoptions['stripe']['modus'], 0 ); ?> value="0"><?php echo __( 'Off' , 'easyReservations' );?></option>
										<option <?php selected( $ccoptions['stripe']['modus'], 1 ); ?> value="1"><?php echo __( 'Normal credit card form get send to stripe' , 'easyReservations' ); ?></option>
										<option <?php selected( $ccoptions['stripe']['modus'], 2 ); ?> value="2"><?php echo __( 'Stripe button with blue style popup form' , 'easyReservations' );?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td style="font-weight:bold">Secret Key</td>
								<td><input type="text" name="stripe_seckey" value="<?php echo $ccoptions['stripe']['seckey']; ?>"></td>
							</tr>
							<tr class="alternate">
								<td style="font-weight:bold">Publishable Key</td>
								<td><input type="text" name="stripe_pubkey" value="<?php echo $ccoptions['stripe']['pubkey']; ?>"></td>
							</tr>
							<tr>
								<td style="font-weight:bold"><?php echo __( 'Description' , 'easyReservations' );?></td>
								<td>
									<?php echo __( 'Attach transaction to' , 'easyReservations' );?> 
									<select name="stripe_desc">
										<option <?php selected( $ccoptions['stripe']['desc'], 'email' ); ?> value="email"><?php echo __( 'Email' , 'easyReservations' );?></option>
										<option <?php selected( $ccoptions['stripe']['desc'], 'name' ); ?> value="name"><?php echo __( 'Name' , 'easyReservations' ); ?></option>
									</select> 
									<?php echo __( 'in Stripes system' , 'easyReservations' );?>
								</td>
							</tr>
							<tr id="stripe_popup" style="<?php if($ccoptions['stripe']['modus'] != 2) echo 'display:none'; ?>" class="alternate">
								<td style="font-weight:bold"><?php echo __( 'Stripe button' , 'easyReservations' );?></td>
								<td>
									<input type="checkbox" name="stripe_address" <?php selected( $ccoptions['stripe']['address'], 1 ); ?>> <?php echo __( 'Ask for address in popup form' , 'easyReservations' );?><br>
									<?php echo __( 'Title in popup' , 'easyReservations' );?>: <input name="stripe_label" type="text" value="<?php if(isset($ccoptions['stripe']['label'])) echo $ccoptions['stripe']['label']; ?>"><br>
									<?php echo __( 'Subtitle in popup' , 'easyReservations' );?>: <input name="stripe_sublabel" type="text" value="<?php if(isset($ccoptions['stripe']['sublabel'])) echo $ccoptions['stripe']['sublabel']; ?>">
								</td>
							</tr>
							<tr>
								<td style="font-weight:bold"><?php echo __( 'Currency' , 'easyReservations' );?></td>
								<td>
									<select name="stripe_currency">
									<?php $array = array('United Arab Emirates Dirham' => 'aed', 'Albanian Lek' => 'all', 'Netherlands Antillean Gulden' => 'ang', 'Argentine Peso' => 'ars', 'Australian Dollar' => 'aud', 'Aruban Florin' => 'awg', 'Barbadian Dollar' => 'bbd', 'Bangladeshi Taka' => 'bdt', 'Burundian Franc' => 'bif', 'Bermudian Dollar' => 'bmd', 'Brunei Dollar' => 'bnd', 'Bolivian Boliviano' => 'bob', 'Brazilian Real' => 'brl', 'Bahamian Dollar' => 'bsd', 'Botswana Pula' => 'bwp', 'Belize Dollar' => 'bzd', 'Canadian Dollar' => 'cad', 'Swiss Franc' => 'chf', 'Chilean Peso' => 'clp', 'Chinese Renminbi Yuan' => 'cny', 'Colombian Peso' => 'cop', 'Costa Rican Col&#243;n' => 'crc', 'Cape Verdean Escudo' => 'cve', 'Czech Koruna' => 'czk', 'Djiboutian Franc' => 'djf', 'Danish Krone' => 'dkk', 'Dominican Peso' => 'dop', 'Algerian Dinar' => 'dzd', 'Egyptian Pound' => 'egp', 'Ethiopian Birr' => 'etb', 'Euro' => 'eur', 'Fijian Dollar' => 'fjd', 'Falkland Islands Pound' => 'fkp', 'British Pound' => 'gbp', 'Gibraltar Pound' => 'gip', 'Gambian Dalasi' => 'gmd', 'Guinean Franc' => 'gnf', 'Guatemalan Quetzal' => 'gtq', 'Guyanese Dollar' => 'gyd', 'Hong Kong Dollar' => 'hkd', 'Honduran Lempira' => 'hnl', 'Croatian Kuna' => 'hrk', 'Haitian Gourde' => 'htg', 'Hungarian Forint' => 'huf', 'Indonesian Rupiah' => 'idr', 'Israeli New Sheqel' => 'ils', 'Indian Rupee' => 'inr', 'Icelandic Kr&#243;na' => 'isk', 'Jamaican Dollar' => 'jmd', 'Japanese Yen' => 'jpy', 'Kenyan Shilling' => 'kes', 'Cambodian Riel' => 'khr', 'Comorian Franc' => 'kmf', 'South Korean Won' => 'krw', 'Cayman Islands Dollar' => 'kyd', 'Kazakhstani Tenge' => 'kzt', 'Lao Kip' => 'lak', 'Lebanese Pound' => 'lbp', 'Sri Lankan Rupee' => 'lkr', 'Liberian Dollar' => 'lrd', 'Moroccan Dirham' => 'mad', 'Moldovan Leu' => 'mdl', 'Mongolian T&ouml;gr&ouml;g' => 'mnt', 'Macanese Pataca' => 'mop', 'Mauritanian Ouguiya' => 'mro', 'Mauritian Rupee' => 'mur', 'Maldivian Rufiyaa' => 'mvr', 'Malawian Kwacha' => 'mwk', 'Mexican Peso' => 'mxn', 'Malaysian Ringgit' => 'myr', 'Namibian Dollar' => 'nad', 'Nigerian Naira' => 'ngn', 'Nicaraguan C&#243;rdoba' => 'nio', 'Norwegian Krone' => 'nok', 'Nepalese Rupee' => 'npr', 'New Zealand Dollar' => 'nzd', 'Panamanian Balboa' => 'pab', 'Peruvian Nuevo Sol' => 'pen', 'Papua New Guinean Kina' => 'pgk', 'Philippine Peso' => 'php', 'Pakistani Rupee' => 'pkr', 'Polish Z?oty' => 'pln', 'Paraguayan Guaran&#237;' => 'pyg', 'Qatari Riyal' => 'qar', 'Russian Ruble' => 'rub', 'Saudi Riyal' => 'sar', 'Solomon Islands Dollar' => 'sbd', 'Seychellois Rupee' => 'scr', 'Swedish Krona' => 'sek', 'Singapore Dollar' => 'sgd', 'Saint Helenian Pound' => 'shp', 'Sierra Leonean Leone' => 'sll', 'Somali Shilling' => 'sos', 'S&#227;o Tom&#233; and Pr&#237;ncipe Dobra' => 'std', 'Salvadoran Col&#243;n' => 'svc', 'Swazi Lilangeni' => 'szl', 'Thai Baht' => 'thb', 'Tongan Pa?anga' => 'top', 'Trinidad and Tobago Dollar' => 'ttd', 'New Taiwan Dollar' => 'twd', 'Tanzanian Shilling' => 'tzs', 'Ukrainian Hryvnia' => 'uah', 'Ugandan Shilling' => 'ugx', 'United States Dollar' => 'usd', 'Uruguayan Peso' => 'uyu', 'Uzbekistani Som' => 'uzs', 'Vietnamese ??ng' => 'vnd', 'Vanuatu Vatu' => 'vuv', 'Samoan Tala' => 'wst', 'Central African Cfa Franc' => 'xaf', 'West African Cfa Franc' => 'xof', 'Cfp Franc' => 'xpf', 'Yemeni Rial' => 'yer', 'South African Rand' => 'zar', 'Afghan Afghani' => 'afn', 'Armenian Dram' => 'amd', 'Angolan Kwanza' => 'aoa', 'Azerbaijani Manat' => 'azn', 'Bosnia & Herzegovina Convertible Mark' => 'bam', 'Bulgarian Lev' => 'bgn', 'Congolese Franc' => 'cdf', 'Georgian Lari' => 'gel', 'Kyrgyzstani Som' => 'kgs', 'Lesotho Loti' => 'lsl', 'Malagasy Ariary' => 'mga', 'Macedonian Denar' => 'mkd', 'Mozambican Metical' => 'mzn', 'Romanian Leu' => 'ron', 'Serbian Dinar' => 'rsd', 'Rwandan Franc' => 'rwf', 'Surinamese Dollar' => 'srd', 'Tajikistani Somoni' => 'tjs', 'Turkish Lira' => 'try', 'East Caribbean Dollar' => 'xcd', 'Zambian Kwacha' => 'zmw');
										foreach($array as $key => $currency){
											$sel = $ccoptions['stripe']['currency'] == $currency ? 'selected="selected"' : '';
											echo '<option value="'.$currency.'" '.$sel.'>'.$key.'</option>';
										}?>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>" onclick="document.reservation_cc_settings.value = 'stripe'; document.getElementById('reservation_cc_settings').submit(); return false;" style="margin-top:7px;" class="easybutton button-primary">
					<table id="reservations_ccards_settings" class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%">
						<thead>
							<tr>
								<th colspan="2"><?php echo __( 'Credit Card settings' , 'easyReservations' );?><span style="float:right"><a href="<?php echo wp_nonce_url( 'admin.php?page=reservation-settings&site=pay&deletecc', 'easy-delete-cc' ); ?>"><?php echo __( 'Delete all credit cards' , 'easyReservations' ); ?></a></span></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="2">
									This function saves the credit card information's in the database. That is a heavy security risk and it's always recommend to use a payment gateway instead.<br>
									They cover the transaction and more important they take the <strong>legal responsibility</strong> to secure the information’s. If you're using this function you are responsible.<br>
									Only use this function with <strong><a href="http://wordpress.org/extend/plugins/wordpress-https/" target="_blank">HTTPS</a></strong> (SSL Certificate required) and delete the credit card information's directly to reduce the possible damage. The CCV's aren't allowed to be saved at all.<br>
									<?php if(isset($error) && !empty($error)){?>
										<span style="color:#f00"><?php echo $error; ?></span>
									<?php } ?>
								</td>
							</tr>
							<tr class="alternate">
								<td style="font-weight:bold"><?php echo __( 'Active' , 'easyReservations' );?></td>
								<td>
									<input type="checkbox" name="status" value="1" <?php checked($ccoptions['status'], 1); ?>> <?php echo __( 'Check to give your guests the opportunity to enter their credit card information after submit' , 'easyReservations' );?>
								</td>
							</tr>
							<tr>
								<td style="font-weight:bold"><?php echo __( 'Owner' , 'easyReservations' );?></td>
								<td>
									<input type="checkbox" name="ccname" value="1" <?php checked($ccoptions['name'], 1); ?>> <?php echo __( 'Ask for name of card holder' , 'easyReservations' );?>
								</td>
							</tr>
							<tr class="alternate">
								<td style="font-weight:bold"><?php echo __( 'Automatically approval' , 'easyReservations' );?></td>
								<td>
									<input type="checkbox" name="approve" value="1" <?php checked($ccoptions['approve'], 1); ?>> <?php echo __( 'Auto-approve after entered correctly credit card information' , 'easyReservations' );?>
								</td>
							</tr>
							<tr>
								<td style="font-weight:bold"><?php echo __( 'Available Credit Cards' , 'easyReservations' );?></td>
								<td style="vertical-align: top">
									<?php foreach($all_cards as $card) echo '<input type="checkbox" name="avail[]" value="'.$card.'" '.checked(in_array($card,$ccoptions['avail']), true, false).'"> '.$card.'<br>'; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<input type="button" value="<?php printf ( __( 'Save Changes' , 'easyReservations' ));?>" onclick="document.getElementById('reservation_cc_settings').submit(); return false;" style="margin-top:7px;" class="easybutton button-primary" >
				</form>
			<?php
		}
	}
}

	function easyreservations_generate_creditcard_form($ids, $price, $discount = false){
		$ccoptions = get_option('reservations_credit_card_options'); $stripe = false;
		if($discount && is_numeric($discount) && $discount < 100) $price =round( $price/100*$discount,2);
		if(!$ccoptions || !isset($ccoptions['status']) || ($ccoptions['status'] == 0 && $ccoptions['stripe']['modus'] == 0)) return '';
		if(is_object($ids)) $id = $ids->id;
		else $id = implode('-', $ids);
		if(isset($ccoptions['stripe']) && !empty($ccoptions['stripe']['pubkey']) && $ccoptions['stripe']['modus'] > 0){
			$stripe = true;
			$stripekey = $ccoptions['stripe']['pubkey'];
		}
		$nonce = wp_create_nonce('easy-credit-nonce');
		$ccform = '<form method="post" id="easycreditform" name=easycreditform" onsubmit="submitcreditform('.$stripe.'); return false;"><input name="easycreditnonce" id="easycreditnonce" type="hidden" value="'.$nonce.'"><input name="easyccid" id="easyccid" type="hidden" value="'.$id.'">';
		$ccform .= '<span id="easyccerror"></span>';
		if(!$stripe) $ccform .= '<span class="submitrow"><label for="easycctype">'.__('Card Type', 'easyReservations').'</label><select name="easycctype" id="easycctype">'. easyreservations_get_credit_cards_options().'</select></span>';
		if($ccoptions['name'] && $ccoptions['name'] == 1 && !$stripe) $ccform .= '<span class="submitrow"><label for="easycctype">'.__('Holders name', 'easyReservations').'</label><input type="text" name="easyccname" id="easyccname"></span>';
		$ccform .= '<span class="submitrow"><label for="easyccnumber">'.__('Card Number', 'easyReservations').'</label><input type="text" name="easyccnumber" id="easyccnumber" length="16"></span>';
		$ccform .= '<span class="submitrow"><label for="easyccmonth">'.__('Expires', 'easyReservations').'</label><select name="easyccmonth" id="easyccmonth">'.easyreservations_num_options('01','12', date('m')).'</select> / <select name="easyccyear" id="easyccyear">'.easyreservations_num_options(2000,2025, date('Y')).'</select></span>';
		$ccform .= '<span class="submitrow"><label for="easycccvv">'.__('CCV', 'easyReservations').'</label><input type="text" class="cvv" name="easycccvv" id="easycccvv" length="4"><input type="hidden" name="easyccamount" id="easyccamount" value="'.$price.'"></span>';
		$ccform .= '<span style="display:block;width:100%;padding-left:90px;"><input type="submit" id="easyccsubmit" class="easy-button" style="width:100px;" value="Send"></span>';
		$ccform .= '</form>';
		if($stripe){
			if($ccoptions['stripe']['modus'] == 1){
				$ccform .=<<<JAVASCRIPT
<script type="text/javascript">
	jQuery.getScript('https://js.stripe.com/v2/', function(){
		Stripe.setPublishableKey('$stripekey');
	});

	function stripeResponseHandler(status, response){
		if(response.error){
			jQuery('#easyccerror').text(response.error.message);
			jQuery('#easyccerror').css('display', 'inline-block');
			jQuery('#easyccsubmit').removeAttr("disabled");
		} else {
			var form = jQuery("#easycreditform");
			var token = response['id'];
			form.append('<input type="hidden" name="stripeToken" id="stripeToken" value="' + token + '">');
			submitcreditform(false);
		}
		jQuery('#easyccloading').remove();
	}
</script>
JAVASCRIPT;
			} else {
				$label = __('Pay', 'easyReservations'); $sublabel = __('Secure', 'easyReservations');
				$currency = $ccoptions['stripe']['currency'];
				if(isset($ccoptions['stripe']['label']) && !empty($ccoptions['stripe']['label'])) $label = __($ccoptions['stripe']['label']);
				if(isset($ccoptions['stripe']['sublabel']) && !empty($ccoptions['stripe']['sublabel'])) $sublabel = __($ccoptions['stripe']['sublabel']);
				$ccform = '<script src="https://checkout.stripe.com/checkout.js" data-amount="'.str_replace(array(',','.'), '',  number_format($price,2,'.','.')).'"></script>';
				$ccform .= '<button id="customButton" type="submit" class="stripe-button-el" style="visibility: visible;"><span style="display: block; min-height: 30px; margin:0;">'.__('Pay with Card', 'easyReservations').'</button></span>';
				$ccform .=<<<JAVASCRIPT
<script type="text/javascript">
var readyStateCheckIntervalTwo = setInterval(function() {
  if(typeof(StripeCheckout) === "object"){

    var handler = StripeCheckout.configure({
	    key: '$stripekey',
	    currency: '$currency',
	    locale: 'auto',
	    token: function(token) {
	      // Use the token to create the charge with a server-side script.
	      // You can access the token ID with `token.id`
	      var data = {
					action: "easyreservations_send_credit",
					security: "$nonce",
					id:"$id",
					token: token.id
				}

				var deposit = jQuery('input[name="easy_deposit_radio[]"]:checked').val();
				if(deposit){
					data['deposit'] = deposit;
					switch(deposit){
						case "own":
							data['deposit-own'] = jQuery('#easy_deposit_own').val();
							break;
						case "perc":
							data['deposit-own'] = jQuery('#easy_deposit_perc').val();
							break;
					}
				}

				jQuery('#easyFormInnerlay').fadeOut("slow", function(){
					jQuery('#easyFormOverlay').addClass('easyloading');
				});

				jQuery.post(easyAjax.ajaxurl, data, function(response){
					response = jQuery.parseJSON(response);
					jQuery("#easyFormInnerlay").fadeIn("fast");
					jQuery("#easyFormOverlay").removeClass('easyloading');
					if(response[0] == "correct"){
						jQuery('.easy_form_success').html('<span id="easyccsubmited"><b>'+easyReservationAtts['credit']+'</b>'+easyReservationAtts['subcredit']+'</span>');
					} else {
						jQuery('#easyccerror').css('display', 'inline-block');
						jQuery('#easyccerror').html(response[0]);
					}
					jQuery('#easyccloading').remove();
				});
	    }
    });

	 jQuery('#customButton').on('click', function(e) {
	    // Open Checkout with further options
	    handler.open({
	      name: '$label',
	      description: '$sublabel',
	      amount: jQuery('script[data-amount]').attr('data-amount')
	    });
	    e.preventDefault();
	  });
    clearInterval(readyStateCheckIntervalTwo);
  }
}, 10);
 </script>
 <style>
  .stripe-button-el{overflow:hidden;display:inline-block;visibility:visible !important;background-image:-webkit-linear-gradient(#28a0e5,#015e94);background-image:-moz-linear-gradient(#28a0e5,#015e94);background-image:-ms-linear-gradient(#28a0e5,#015e94);background-image:-o-linear-gradient(#28a0e5,#015e94);background-image:-webkit-linear-gradient(#28a0e5,#015e94);background-image:-moz-linear-gradient(#28a0e5,#015e94);background-image:-ms-linear-gradient(#28a0e5,#015e94);background-image:-o-linear-gradient(#28a0e5,#015e94);background-image:linear-gradient(#28a0e5,#015e94);-webkit-font-smoothing:antialiased;border:0;padding:1px;text-decoration:none;-webkit-border-radius:5px;-moz-border-radius:5px;-ms-border-radius:5px;-o-border-radius:5px;border-radius:5px;-webkit-box-shadow:0 1px 0 rgba(0,0,0,0.2);-moz-box-shadow:0 1px 0 rgba(0,0,0,0.2);-ms-box-shadow:0 1px 0 rgba(0,0,0,0.2);-o-box-shadow:0 1px 0 rgba(0,0,0,0.2);box-shadow:0 1px 0 rgba(0,0,0,0.2);-webkit-touch-callout:none;-webkit-tap-highlight-color:transparent;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;-o-user-select:none;user-select:none;cursor:pointer}.stripe-button-el::-moz-focus-inner{border:0;padding:0}.stripe-button-el span{display:block;position:relative;padding:0 12px;height:30px;line-height:30px;background:#1275ff;background-image:-webkit-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-moz-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-ms-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-o-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-webkit-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-moz-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-ms-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:-o-linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);background-image:linear-gradient(#7dc5ee,#008cdd 85%,#30a2e4);font-size:14px;color:#fff;font-weight:bold;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;text-shadow:0 -1px 0 rgba(0,0,0,0.25);-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,0.25);-moz-box-shadow:inset 0 1px 0 rgba(255,255,255,0.25);-ms-box-shadow:inset 0 1px 0 rgba(255,255,255,0.25);-o-box-shadow:inset 0 1px 0 rgba(255,255,255,0.25);box-shadow:inset 0 1px 0 rgba(255,255,255,0.25);-webkit-border-radius:4px;-moz-border-radius:4px;-ms-border-radius:4px;-o-border-radius:4px;border-radius:4px}.stripe-button-el:not(:disabled):active,.stripe-button-el.active{background:#005d93}.stripe-button-el:not(:disabled):active span,.stripe-button-el.active span{color:#eee;background:#008cdd;background-image:-webkit-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-moz-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-ms-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-o-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-webkit-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-moz-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-ms-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:-o-linear-gradient(#008cdd,#008cdd 85%,#239adf);background-image:linear-gradient(#008cdd,#008cdd 85%,#239adf);-webkit-box-shadow:inset 0 1px 0 rgba(0,0,0,0.1);-moz-box-shadow:inset 0 1px 0 rgba(0,0,0,0.1);-ms-box-shadow:inset 0 1px 0 rgba(0,0,0,0.1);-o-box-shadow:inset 0 1px 0 rgba(0,0,0,0.1);box-shadow:inset 0 1px 0 rgba(0,0,0,0.1)}.stripe-button-el:disabled,.stripe-button-el.disabled{background:rgba(0,0,0,0.2);-webkit-box-shadow:none;-moz-box-shadow:none;-ms-box-shadow:none;-o-box-shadow:none;box-shadow:none}.stripe-button-el:disabled span,.stripe-button-el.disabled span{color:#999;background:#f8f9fa;text-shadow:0 1px 0 rgba(255,255,255,0.5)}
 </style>
JAVASCRIPT;
				return $ccform;
			}
		}
		$ccform .=<<<JAVASCRIPT
<script type="text/javascript">
function submitcreditform(stripe){
	jQuery('#easyccerror').css('display', 'none');
	jQuery('#easyccsubmit').attr("disabled", "disabled");
	jQuery('#easycreditform .easy-button').after(' <img id="easyccloading" style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading.gif">');
	if(stripe){
		Stripe.createToken({
			number: jQuery('#easyccnumber').val(),
			cvc: jQuery('#easycccvv').val(),
			exp_month: jQuery('#easyccmonth').val(),
			exp_year: jQuery('#easyccyear').val()
		}, stripeResponseHandler);
	} else {
		if(jQuery('#stripeToken').length > 0){
			var data = {
				action: 'easyreservations_send_credit',
				security:document.getElementById('easycreditnonce').value,
				id:document.getElementById('easyccid').value,
				token:document.getElementById('stripeToken').value
			}
		} else {
			var ccname = 'no';
			if(document.getElementById('easyccname')) ccname = document.getElementById('easyccname').value;
			var data = {
				action: 'easyreservations_send_credit',
				security:document.getElementById('easycreditnonce').value,
				id:document.getElementById('easyccid').value,
				ccname:ccname,
				type:document.getElementById('easycctype').value,
				number:document.getElementById('easyccnumber').value,
				month:document.getElementById('easyccmonth').value,
				year:document.getElementById('easyccyear').value,
				cvv:document.getElementById('easycccvv').value
			}
		}
		var deposit = jQuery('input[name="easy_deposit_radio[]"]:checked').val();
		if(deposit){
			data['deposit'] = deposit;
			switch(deposit){
				case "own":
					data['deposit-own'] = jQuery('#easy_deposit_own').val();
					break;
				case "perc":
					data['deposit-own'] = jQuery('#easy_deposit_perc').val();
					break;
			}
		}
		jQuery.post(easyAjax.ajaxurl, data, function(response){
			response = jQuery.parseJSON(response);
			jQuery("#easyFormOverlay").removeClass('easyloading');
			if(response[0] == "correct"){
				jQuery('.easy_form_success').html('<span id="easyccsubmited"><b>'+easyReservationAtts['credit']+'</b>'+easyReservationAtts['subcredit']+'</span>');
			} else {
				if(!response[1]){
					response[1] = response[0];
					response[0] = '';
				}
				if(response[0] == "number"){
					jQuery('#easyccnumber').addClass('form-error');
				} else if(response[0] == "cvv"){
					jQuery('#easycccvv').addClass('form-error');
				} else if(response[0] == "type"){
					jQuery('#easycctype').addClass('form-error');
				}
				jQuery('#easyccerror').css('display', 'inline-block');
				jQuery('#easyccerror').html(response[1]);
				jQuery('#easyccloading').remove();
				jQuery('#easyccsubmit').removeAttr("disabled");
			}
		});
	}
	return false;
}
var readyStateCheckInterval = setInterval(function() {
  if(document.readyState === "complete"){
		jQuery('#easycreditform input').change(function(){
			jQuery('#easyccerror').fadeOut('slow');
			jQuery('#easycreditform input').removeClass('form-error');
		});
    clearInterval(readyStateCheckInterval);
  }
}, 10);
</script>
JAVASCRIPT;
		return $ccform;
	}

	function easyreservations_get_credit_cards_options($sel = false){
		$cards = easyreservations_get_credit_cards();
		$options = '';
		foreach($cards as $card){
			$selected = $sel == $card ? 'selected="selected"' : '';
			$options .= '<option value="'.$card['name'].'" '.$selected.'>'.$card['name'].'</option>';
		}
		return $options;
	}

	function easyreservations_credit_card_callback(){
		check_ajax_referer( 'easy-credit-nonce', 'security' );
		$cc_options =  get_option('reservations_credit_card_options');
		$check = false;
		$final_price = 0;
		$price = 0;
		if(isset($_POST['token'])){
			require_once(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/stripe/init.php");
			try {
				\Stripe\Stripe::setApiKey($cc_options['stripe']['seckey']);
				$token = $_POST['token'];
				$ids = explode('-', $_POST['id']);
				foreach($ids as $id){
					$res = new Reservation((int) $id);
					$price += $res->Calculate();
					$price -= $res->paid;
				}

				if(isset($_POST['deposit'])){
					if($_POST['deposit'] == 'perc'){
						if(substr($_POST['deposit-own'],-1) == '%'){
							$price = $price / 100 * floatval(substr($_POST['deposit-own'], 0, -1));
						}	else {
							$price = floatval($_POST['deposit-own']);
						}
					} elseif($_POST['deposit'] == 'own') {
						$price = floatval($_POST['deposit-own']);
					}
				}

				$final_price = (int) str_replace(array(',','.'), '', number_format($price,2,'.','.'));

				if($cc_options['stripe']['desc'] == 'email') $desc = $res->email;
				else $desc = $res->name;
				// create the charge on Stripe's servers - this will charge the user's card
				try {
					$charge = \Stripe\Charge::create(array(
							"amount" => $final_price, // amount in cents, again
							"currency" => $cc_options['stripe']['currency'],
							"source" => $token,
							"description" => $desc
					));
					$check = true;
				} catch(\Stripe\Error\Card $e) {
					$check = false;
					echo json_encode(array($e->getMessage()));
					// The card has been declined
				}

			} catch(Exception $e){
				echo json_encode(array($e->getMessage()));
				exit;
			}
		} else {
			$check = easyreservations_check_credit_card($_POST['number'], $_POST['type'], $_POST['cvv']);
		}
		if($check === true){
			foreach(explode('-', $_POST['id']) as $id){
				$res = new Reservation((int) $id);
				try {
					$edit = array();
					if($cc_options['approve'] == 1 && $res->status != 'yes'){
						$edit[] = 'status';
						$edit[] = 'resourcenumber';
						$res = easyreservations_auto_approve($res, false, true);
					}
					if(isset($_POST['token']) && $price > 0){
						$res->updatePricepaid($price);
						$edit[] = 'pricepaid';
					} elseif(!isset($_POST['token'])){
						$res = easyreservations_save_credit_card_infos($res, array('number' => $_POST['number'], 'month' => $_POST['month'], 'year' => $_POST['year']), false);
						$edit[] = 'custom';
					}
					$res->editReservation($edit, true, array('reservations_email_to_admin_paypal', 'reservations_email_to_user_paypal'), array(false, $res->email));
				} catch(Exception $e){
					echo json_encode(array('error', $e->getMessage()));
					exit;
				}
			}
			echo json_encode(array('correct'));
		} else echo json_encode($check);
		exit;
	}

add_action('wp_ajax_easyreservations_send_credit', 'easyreservations_credit_card_callback');
add_action('wp_ajax_nopriv_easyreservations_send_credit', 'easyreservations_credit_card_callback');

?>