<?php
/*
Plugin Name: easyReservations Payment Module
Plugin URI: http://easyreservations.org/module/paypal/
Version: 1.7.4
Description: 3.4
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!
*/

if(file_exists(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/creditcards.php")) include_once(WP_PLUGIN_DIR."/easyreservations/lib/modules/paypal/creditcards.php");

function easyreservations_get_payment_gateways(){
	$gateways = array(
		'paypal' => array(
			'name' => 'PayPal',
			'options' => 'reservations_paypal_options',
			'form_name' => '_xclick',
			'amount_name' => 'amount'
		),
		'authorize' => array(
			'name' => 'Authorize.net',
			'options' => 'reservations_authorize_options',
			'form_name' => 'authorize',
			'amount_name' => 'x_amount'
		),
		'2checkout' => array(
			'name' => '2checkout.com',
			'options' => 'reservations_2checkout_options',
			'form_name' => 'checkout',
			'amount_name' => 'li_0_price'
		),
		'googlewallet' => array(
			'name' => 'Google Wallet',
			'options' => 'reservations_wallet_options',
			'form_name' => 'googlewallet',
			'amount_name' => 'item_price_1'
		),
		'dibs' => array(
			'name' => 'DIBS payment',
			'options' => 'reservations_dibs_options',
			'form_name' => 'dibs',
			'amount_name' => 'amount'
		),
		'ogone' => array(
			'name' => 'Ogone',
			'options' => 'reservations_ogone_options',
			'form_name' => 'form1',
			'amount_name' => 'AMOUNT'
		)
	);
	return apply_filters( 'reservations_register_gateway', $gateways);
}

if(is_admin()){

	add_action('er_set_tab_add', 'easyreservations_add_settings_tab');

	function easyreservations_add_settings_tab(){ 
		$current = isset($_GET['site']) && $_GET['site'] == "pay"? 'current' : '';
		echo '<li ><a href="admin.php?page=reservation-settings&site=pay" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_URL.'images/dollar.png"> '. __( 'Payment' , 'easyReservations' ).'</a></li>';
	}

	add_action('er_set_save', 'easyreservations_pay_save_settings');

	function easyreservations_pay_save_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "pay" && isset($_POST['action'])){
			if($_POST['action'] == "reservation_pay_settings"){
				if (!wp_verify_nonce($_POST['easy-set-paypal'], 'easy-set-paypal' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
				if(isset($_POST['er_pay_return'])){
					if($_POST['er_pay_return'] == 'useredit') $er_pay_return = 'useredit';
					elseif(isset($_POST['er_pay_own_return_link'])) $er_pay_return = $_POST['er_pay_own_return_link'];
				}

				$er_pay_ssl = isset($_POST['er_pay_ssl']) ? 1 : 0;
				$deposit_activate = isset($_POST['deposit_activate'])?  1 : 0;
				$deposit_payperc = isset($_POST['deposit_payperc'])?  1 : 0;
				$deposit_payfull = isset($_POST['deposit_payfull'])?  1 : 0;
				$deposit_payown = isset($_POST['deposit_payown'])?  1 : 0;
				if(isset($_POST['discount_amount'])){
					$perc_amt_array = array();
					foreach($_POST['discount_amount'] as $key => $value){
						if(!empty($value)){
							if(isset($_POST['discount_type'][$key]) && $_POST['discount_type'][$key] == '%') $perc_amt_array[] = $value.'%';
							else $perc_amt_array[] = $value;
						}
					}
				}
				$deposit = array('on' => $deposit_activate, 'full' => $deposit_payfull, 'own' => $deposit_payown, 'perc' => $deposit_payperc, 'percamt' => $perc_amt_array);

				$options = array( 'modus' => $_POST['er_pay_modus'], 'title' => $_POST['er_pay_title'], 'owner' => $_POST['er_pay_owner'], 'currency' => $_POST['er_pay_curency'], 'button' => $_POST['er_pay_button_url'], 'cancel_url' => $_POST['er_pay_cancel'], 'message' => $_POST['er_pay_message'], 'er_pay_ssl' => $er_pay_ssl, 'er_pay_return' => $er_pay_return, 'language' => $_POST['language'], 'deposit' => $deposit, 'charset' => $_POST['er_pay_charset']);
				update_option('reservations_paypal_options', $options);
				echo '<br><div class="updated"><p>PayPal '.__( 'settings changed' , 'easyReservations').'</p></div>';
			} elseif($_POST['action'] == "reservation_authorize_settings"){
				if(isset($_POST['er_authorize_return'])){
					if($_POST['er_authorize_return'] == 'useredit') $return_url = 'useredit';
					elseif(isset($_POST['er_authorize_own_return_link'])) $return_url = $_POST['er_authorize_own_return_link'];
				}
				$options = array('loginid' => $_POST['er_authorize_loginid'], 'modus' => $_POST['er_authorize_modus'], 'transactionkey' => $_POST['er_authorize_transactionkey'], 'button' => $_POST['er_authorize_button_url'], 'cancel' => $_POST['er_authorize_cancel'], 'cancel_url' => $_POST['er_authorize_cancel_url'], 'return' => $_POST['er_authorize_return_text'],'return_url' => $return_url); 
				update_option('reservations_authorize_options', $options);
				echo '<br><div class="updated"><p>Authorize.Net '.__( 'settings changed' , 'easyReservations').'</p></div>';
			} elseif($_POST['action'] == "reservation_2checkout_settings"){
				if(isset($_POST['er_2checkout_skip'])) $skip = 1;
				else $skip = 0;
				if(isset($_POST['er_2checkout_return'])){
					if($_POST['er_2checkout_return'] == 'useredit') $return_url = 'useredit';
					elseif(isset($_POST['er_2checkout_own_return_link'])) $return_url = $_POST['er_2checkout_own_return_link'];
				}
				$options = array('login' => $_POST['er_2checkout_login'], 'modus' => $_POST['er_2checkout_modus'], 'lang' => $_POST['er_2checkout_lang'], 'button' => $_POST['er_2checkout_button_url'], 'cancel_url' => $_POST['er_2checkout_cancel_url'], 'return_url' => $return_url, 'skip' => $skip, 'url' => $_POST['er_2checkout_routine'], 'name' => $_POST['er_2checkout_name']); 
				update_option('reservations_2checkout_options', $options);
				echo '<br><div class="updated"><p>2checkout '.__( 'settings changed' , 'easyReservations').'</p></div>';
			} elseif($_POST['action'] == "reservation_wallet_settings"){
				if(isset($_POST['er_wallet_skip'])) $skip = 1;
				else $skip = 0;
				if(isset($_POST['er_wallet_return'])){
					if($_POST['er_wallet_return'] == 'useredit') $return_url = 'useredit';
					elseif(isset($_POST['er_wallet_own_return_link'])) $return_url = $_POST['er_wallet_own_return_link'];
				}
				$options = array('merchantid' => $_POST['er_wallet_login'], 'merchantkey' => $_POST['er_wallet_merchantkey'],'modus' => $_POST['er_wallet_modus'], 'lang' => $_POST['er_wallet_lang'], 'cancel_url' => $_POST['er_wallet_cancel_url'], 'return_url' => $return_url, 'skip' => $skip, 'name' => $_POST['er_wallet_name'], 'currency' => $_POST['er_wallet_currency']);
				update_option('reservations_wallet_options', $options);
				echo '<br><div class="updated"><p>Google Wallet '.__( 'settings changed' , 'easyReservations').'</p></div>';
			} elseif($_POST['action'] == "reservations_dibs_options"){ 
				if(isset($_POST['er_dibs_return'])){
					if($_POST['er_dibs_return'] == 'useredit') $return_url = 'useredit';
					elseif(isset($_POST['er_dibs_own_return_link'])) $return_url = $_POST['er_dibs_own_return_link'];
				}
				$options = array('merchant' => $_POST['er_dibs_login'], 'modus' => $_POST['er_dibs_modus'], 'button' => $_POST['er_dibs_button_url'], 'lang' => $_POST['er_dibs_language'], 'cancel_url' => $_POST['er_dibs_cancel_url'], 'return_url' => $return_url, 'currency' => $_POST['er_dibs_currency'], 'mackey' => $_POST['er_dibs_mackey']); 
				update_option('reservations_dibs_options', $options);
				echo '<br><div class="updated"><p>DIBS '.__( 'settings changed' , 'easyReservations').'</p></div>';
			} elseif($_POST['action'] == "reservations_ogone_options"){ 
				if(isset($_POST['er_ogone_return'])){
					if($_POST['er_ogone_return'] == 'useredit') $return_url = 'useredit';
					elseif(isset($_POST['er_ogone_own_return_link'])) $return_url = $_POST['er_ogone_own_return_link'];
				}
				$options = array('pspid' => $_POST['er_ogone_pspid'], 'modus' => $_POST['er_ogone_modus'], 'button' => $_POST['er_ogone_button_url'], 'lang' => $_POST['er_ogone_language'], 'cancel' => $_POST['er_ogone_cancel_url'], 'return_url' => $return_url, 'currency' => $_POST['er_ogone_currency'], 'shapass' => $_POST['er_ogone_shapass'],'shapass_out' => $_POST['er_ogone_shapass_out'], 'logo' => $_POST['er_ogone_logo'], 'buttonbg' => $_POST['er_ogone_buttonbg']); 
				update_option('reservations_ogone_options', $options); 
				echo '<br><div class="updated"><p>Ogone '.__( 'settings changed' , 'easyReservations').'</p></div>';
			} else do_action('reservations_save_gateway_settings');
		}
	}

	add_action('er_set_add', 'easyreservations_pay_add_settings');

	function easyreservations_pay_add_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "pay"){
			$options = get_option('reservations_paypal_options');
			if(!$options || empty($options)) $options = array('message' => 'Add special instructions to merchant', 'title' => '[resource] for [times] days | [arrival] - [departure]', 'owner' => '', 'modus' => 'off', 'currency' => 'USD', 'button' => 'https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif', 'deposit' => array('on' => 0, 'full' => 1, 'own' => 1, 'perc' => 1, 'percamt' => '10,15' )); 
			if(!isset($options['multititle'])) $options['multititle'] = "[nr] Reservations for [adults]+[childs] persons";
			$aoptions = get_option('reservations_authorize_options');
			if(!$aoptions || empty($aoptions)) $aoptions = array('loginid' => '', 'modus' => 'off', 'transactionkey' => '', 'button'); 
			$coptions = get_option('reservations_2checkout_options');
			$goptions = get_option('reservations_wallet_options');
			$doptions = get_option('reservations_dibs_options');
			$ogoptions = get_option('reservations_ogone_options');

			$deposit_options_value = '';
			if(!empty($options['deposit']['percamt'])){
				if(is_array($options['deposit']['percamt'])){
					$array = $options['deposit']['percamt'];
				} else {
					$array = explode(',', $options['deposit']['percamt']);
					foreach($array as $key => $val) $array[$key] = $val.'%';
				}
				foreach($array as $value){
					$deposit_options_value .= '<div><input type="text" name="discount_amount[]" value="'.str_replace('%', '', $value).'">';
					$deposit_options_value .= '<select name="discount_type[]">';
						$deposit_options_value .= '<option value="cur">&'.RESERVATIONS_CURRENCY.';</option>';
						$deposit_options_value .= '<option value="%" '.selected('%', substr($value, -1), false).'>%</option>';
					$deposit_options_value .= '</select>';
					$deposit_options_value .= '<a href="#" onclick="javascript:jQuery(this).parent().remove()">X</a></div>';
				}
			}
			$deposit_options = '<input type="checkbox" name="deposit_activate" style="margin-bottom:2px" '.checked(1, $options['deposit']['on'],false).'> Activate<br><input type="checkbox" name="deposit_payfull" '.checked(1, $options['deposit']['full'],false).'> Guest can choose to pay full price<br><input type="checkbox" name="deposit_payown" '.checked(1, $options['deposit']['own'],false).'> Guest can define the discount<br><input type="checkbox" name="deposit_payperc"  '.checked(1, $options['deposit']['perc'],false).'> Guest can choose to pay: (<a onclick="easy_add_deposit()" style="cursor: pointer">Add</a>)<br><span id="discount_selection" style="margin-left:10px;display:inline-block;">'.$deposit_options_value.'</span>';

			$rows = array(
				__('Title' , 'easyReservations' ) => '<input type="text" name="er_pay_title" id="er_pay_title" style="width:99%;" value="'.$options['title'].'"><br><code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[arrival]\');">[arrival]</code> <code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[departure]\');">[departure]</code><code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[units]\');">[units]</code> <code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[resource]\');">[resource]</code> <code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[persons]\');">[persons]</code><code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[adults]\');">[adults]</code><code style="cursor:pointer" onclick="jQuery(\'#er_pay_title\').val(\'[childs]\');">[childs]</code>',
				__('Title when paying multiple reservations', 'easyReservations' ) => '<input type="text" name="er_pay_multititle" id="er_pay_multititle" style="width:99%;" value="'.$options['multititle'].'"><br><code style="cursor:pointer" onclick="jQuery(\'#er_pay_multititle\').val(\'[nr]\');">[nr]</code> <code style="cursor:pointer" onclick="jQuery(\'#er_pay_multititle\').val(\'[persons]\');">[persons]</code><code style="cursor:pointer" onclick="jQuery(\'#er_pay_multititle\').val(\'[adults]\');">[adults]</code> <code style="cursor:pointer" onclick="jQuery(\'#er_pay_multititle\').val(\'[childs]\');">[childs]</code>',
				__('Deposit' , 'easyReservations' ) => $deposit_options
			);
			$table = easyreservations_generate_table('reservation_general_settings_table', __( 'General payment settings' , 'easyReservations'), $rows, 'style="width:77%;float:left;margin-right:6px"');
			$table .= easyreservations_generate_table('reservations_payment_navi', __( 'Settings' , 'easyReservations'), array('<ul><li>&bull; <a href="#reservation_pay_settings_table">PayPal</a></li><li>&bull; <a href="#reservation_authorize_settings_table">Authorize.Net</a></li><li>&bull; <a href="#reservation_2checkout_settings_table">2checkout</a></li><li>&bull; <a href="#reservation_wallet_settings_table">Google Wallet</a></li><li>&bull; <a href="#reservation_dibs_settings_table">DIBS</a></li><li>&bull; <a href="#reservation_ogone_settings_table">Ogone</a></li><li>&bull; <a href="#reservation_cc_settings">Stripe</a></li><li>&bull; <a href="#reservations_ccards_settings">Credit Card</a></li></ul>'), 'style="margin-left:5px;width:22%;clear:none"');
			$table .= '<div style="width: 100%;clear: left"><input type="submit" value="Save Changes" onclick="jQuery(\'#reservation_pay_settings\').submit(); return false;" style="margin-top:7px" class="easybutton button-primary"></div>';

			// PayPal
			$charset = array('Big5' => 'Big5 (Traditional Chinese in Taiwan)','EUC-JP' => 'EUC-JP','EUC-KR' => 'EUC-KR','EUC-TW' => 'EUC-TW','gb2312' => 'gb2312 (Simplified Chinese)','gbk' => 'gbk','HZ-GB-2312' => 'HZ-GB-2312 (Traditional Chinese in Hong Kong)','ibm-862' => 'ibm-862 (Hebrew with European characters)','ISO-2022-CN' => 'ISO-2022-CN','ISO-2022-JP' => 'ISO-2022-JP','ISO-2022-KR' => 'ISO-2022-KR','ISO-8859-1' => 'ISO-8859-1 (Western European Languages)','ISO-8859-2' => 'ISO-8859-2','ISO-8859-3' => 'ISO-8859-3','ISO-8859-4' => 'ISO-8859-4','ISO-8859-5' => 'ISO-8859-5','ISO-8859-6' => 'ISO-8859-6','ISO-8859-7' => 'ISO-8859-7','ISO-8859-8' => 'ISO-8859-8','ISO-8859-9' => 'ISO-8859-9','ISO-8859-13' => 'ISO-8859-13','ISO-8859-15' => 'ISO-8859-15','KOI8-R' => 'KOI8-R (Cyrillic)','Shift_JIS' => 'Shift_JIS','UTF-7' => 'UTF-7','UTF-8' => 'UTF-8','UTF-16' => 'UTF-16','UTF-16BE' => 'UTF-16BE','UTF-16LE' => 'UTF-16LE','UTF16_PlatformEndian' => 'UTF16_PlatformEndian','UTF16_OppositeEndian' => 'UTF16_OppositeEndian','UTF-32' => 'UTF-32','UTF-32BE' => 'UTF-32BE','UTF-32LE' => 'UTF-32LE','UTF32_PlatformEndian' => 'UTF32_PlatformEndian','UTF32_OppositeEndian' => 'UTF32_OppositeEndian','US-ASCII' => 'US-ASCII','windows-1250' => 'windows-1250','windows-1251' => 'windows-1251','windows-1252' => 'windows-1252','windows-1253' => 'windows-1253','windows-1254' => 'windows-1254','windows-1255' => 'windows-1255','windows-1256' => 'windows-1256','windows-1257' => 'windows-1257','windows-1258' => 'windows-1258','windows-874' => 'windows-874 (Thai)','windows-949' => 'windows-949 (Korean)','x-mac-greek' => 'x-mac-greek','x-mac-turkish' => 'x-mac-turkish','x-mac-centraleurroman' => 'x-mac-centraleurroman','x-mac-cyrillic' => 'x-mac-cyrillic','ebcdic-cp-us' => 'ebcdic-cp-us','ibm-1047' => 'ibm-1047');
			if(!isset($options['charset'])) $options['charset'] = 'UTF-8';
			if(isset($options['er_pay_return'])) $auth_return = $options['er_pay_return']; else $auth_return = 'useredit';
			$rows = array(
				__( 'Mode', 'easyReservations' ) => easyreservations_generate_input_select('er_pay_modus', array('off' => __( 'Off' , 'easyReservations' ),'sandbox' => __( 'Sandbox' , 'easyReservations' ),'on' => __( 'Live' , 'easyReservations' )), $options['modus'] ,'style="width:100px" onchange="if(this.value == \'sandbox\') jQuery(\'#er_pay_modus_help\').val(\''. __( 'The validation only works with SSL in Sandbox' , 'easyReservations' ).'\'); else jQuery(\'#er_pay_modus_help\').val(\'\');"'),
				__( 'Owner', 'easyReservations' ) => '<input type="text" style="width:200px" name="er_pay_owner" value="'.$options['owner'].'"> <i>'.__( 'Paypal email or Secure Merchant ID' , 'easyReservations' ).'</i>',
				__( 'Currency', 'easyReservations' ) => easyreservations_generate_input_select('er_pay_curency',array('EUR'=>'Euro','USD'=>'U. S. Dollar','JPY'=>'Japanese Yen','HUF'=>'Hungarian Forint','GBP'=>'British Pound','HKD'=>'Hong Kong Dollar','AUD'=>'Australian Dollars','CAD'=>'Canadian Dollars','NZD'=>'New Zealand Dollar','CHF'=>'Swiss Franc','SGD'=>'Singapore Dollar','SEK'=>'Swedish Krona','DKK'=>'Danish Krone','PLN'=>'Polish Zloty','NOK'=>'Norwegian Krone','CZK'=>'Czech Koruna','ILS'=>'Israeli New Shekel', 'MXN' => 'Mexican Peso', 'BRL' => 'Brazilian Real', 'MYR' => 'Malaysian Ringgits', 'PHP' => 'Philippine Pesos', 'TWD' => 'Taiwan New Dollars', 'THB' => 'Thai Baht', 'TRY' => 'Turkish Lira' ),$options['currency']),
				__( 'Language', 'easyReservations' ) => easyreservations_generate_input_select('language',array('AU'=>'Australian','AT'=>'Austria','BE'=>'Belgium','BR'=>'Brazil','CA'=>'Canada','CH'=>'Switzerland','CN'=>'Chinese','EN'=>'English','FR'=>'French','DE'=>'German','IT'=>'Italian','JP'=>'Japanese','ES'=>'Spanish','GB'=>'United Kingdom','NL'=>'Netherlands','PL'=>'Poland','PT'=>'Portugal','RU'=>'Russia','US'=>'United States','da_DK'=>'Danish (for Denmark only)','he_IL'=>'Hebrew (all)','id_ID'=>'Indonesian (for Indonesia only)','jp_JP'=>'Japanese (for Japan only)','no_NO'=>'Norwegian (for Norway only)','pt_BR'=>'Brazilian Portuguese (for Portugal and Brazil only)','ru_RU'=>'Russian (for Lithuania, Latvia, and Ukraine only)','sv_SE'=>'Swedish (for Sweden only)','th_TH'=>'Thai (for Thailand only)','tr_TR'=>'Turkish (for Turkey only)','zh_CN'=>'Simplified Chinese (for China only)','zh_HK'=>'Traditional Chinese (for Hong Kong only)','zh_TW'=>'Traditional Chinese (for Taiwan only)'),$options['language']),
				__( 'After success return to' , 'easyReservations' ) => '<select id="er_pay_return" name="er_pay_return" onchange="erReturn(this,\'pay\');"><option value="useredit" '.selected($auth_return, 'useredit', false).'>'.__( 'User Control Panel' , 'easyReservations' ).'</option><option value="page" '.selected(is_numeric($auth_return), true, false).'>'.__( 'Page' , 'easyReservations' ).'</option><option value="own" '.selected((!is_numeric($auth_return) && $auth_return != 'useredit') ? true : false, true, false).'>'.__( 'URL' , 'easyReservations' ).'</option></select><span id="er_pay_return_2nd"></span> Turn on <i>Auto Return for Website Payments</i> in <i>Website Payment Preferences</i> for this feature',
				__( 'Cancel', 'easyReservations' ) => '<input type="text" style="width:300px" name="er_pay_cancel" value="'.$options['cancel_url'].'">',
				__( 'Label above note', 'easyReservations' ) => '<input type="text" style="width:200px" name="er_pay_message" value="'.$options['message'].'"> <i>Replace message above the note field - "Add special instructions to merchant"</i>',
				__( 'Charset', 'easyReservations' ) => easyreservations_generate_input_select('er_pay_charset',$charset,$options['charset']).' for encryption of special signs',
				__( 'Button', 'easyReservations' ) => easyreservations_generate_input_select('er_pay_button',array('own' => __( 'Select' , 'easyReservations' ),'https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif' => 'Pay Now small',	'https://www.paypal.com/en_US/i/btn/btn_paynow_LG.gif' => 'Pay Now middle','https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif' => 'Pay Now big','https://www.paypalobjects.com/en_US/i/bnr/horizontal_solution_PP_old.gif' => 'Old Solution graphics','https://www.paypalobjects.com/en_US/i/bnr/horizontal_solution_PP.gif' => 'New Solution graphics','https://www.paypalobjects.com/en_US/i/btn/btn_xpressCheckout_old.gif' => 'Old Express Checkout',	'https://www.paypalobjects.com/en_US/i/btn/x-click-but23_old.gif' => 'New Express Checkout','https://www.paypalobjects.com/en_US/i/btn/x-click-but23.gif' => 'New Buy Now'),$options['button'],'onchange="changeImg(this,\'pay\');"').'<input onchange="changeImg(this,\'pay\');" type="text" id="er_pay_button_url" name="er_pay_button_url" style="width:400px"  value="'.$options['button'].'"><span id="er-pay-button-img" style="float:right;vertical-align:text-bottom;width:200px;text-align:right;"><img src="'.$options['button'].'"></span>',
				__( 'Instant Payment Notifications', 'easyReservations' ).'<br>('.__( 'Validate payment & set paid amount' , 'easyReservations' ).')' => 'Modus: <b>'.((easyreservations_check_curl()) ? 'cURL' : 'fsockopen' ).'</b><br><input type="checkbox" name="er_pay_ssl" value="1" '.checked((isset($options['er_pay_ssl'])) ? $options['er_pay_ssl'] : 0, 1, false).'> <b>Use SSL</b>',
			);
			$table .= easyreservations_generate_table('reservation_pay_settings_table', 'PayPal '.__( 'settings' , 'easyReservations'), $rows, 'style="margin-top:7px;width:99%"');
			echo easyreservations_generate_form('reservation_pay_settings', 'admin.php?page=reservation-settings&site=pay#reservation_pay_settings', 'post', false, array('easy-set-paypal' => wp_create_nonce('easy-set-paypal'), 'action' => 'reservation_pay_settings'), $table);

			// Authorize.Net
			if(isset($aoptions['return_url'])) $auth_return = $aoptions['return_url']; else $auth_return = 'useredit';
			$rows = array(
				__( 'Mode', 'easyReservations' ) => easyreservations_generate_input_select('er_authorize_modus', array('off' => __( 'Off' , 'easyReservations' ),'sandbox' => __( 'Sandbox' , 'easyReservations' ),'on' => __( 'Live' , 'easyReservations' )), $aoptions['modus'] ,'style="width:100px"'),
				'Login ID' => '<input type="text" style="width:200px" name="er_authorize_loginid" value="'.$aoptions['loginid'].'">',
				'Transaction Key'  => '<input type="text" style="width:200px" name="er_authorize_transactionkey" value="'.$aoptions['transactionkey'].'">',
				__( 'After success return to', 'easyReservations' ) => 'Text: <input type="text" style="width:150px" name="er_authorize_return_text" value="'.$aoptions['return'].'"> URL:<select id="er_authorize_return" name="er_authorize_return" onchange="erReturn(this,\'authorize\');"><option value="useredit" '.selected($auth_return, 'useredit', false).'>'.__( 'User Control Panel' , 'easyReservations' ).'</option><option value="page" '.selected(is_numeric($auth_return), true, false).'>'.__( 'Page' , 'easyReservations' ).'</option><option value="own" '.selected((!is_numeric($auth_return) && $auth_return != 'useredit') ? true : false, true, false).'>'.__( 'URL' , 'easyReservations' ).'</option></select><span id="er_authorize_return_2nd"></span>',
				__( 'Cancel', 'easyReservations' ) => 'Text: <input type="text" style="width:150px" name="er_authorize_cancel" value="'.$aoptions['cancel'].'"> URL: <input type="text" style="width:300px" name="er_authorize_cancel_url" value="'.$aoptions['cancel_url'].'">',
				__( 'Button', 'easyReservations' ) => easyreservations_generate_input_select('er_authorize_button',array('own' => __( 'Select' , 'easyReservations' ),'https://content.authorize.net/images/buy-now-gold.gif' => 'Simple Checkout Gold','https://content.authorize.net/images/buy-now-blue.gif' => 'Simple Checkout Blue'),$aoptions['button'],'onchange="changeImg(this,\'authorize\');"').'<input onchange="changeImg(this,\'authorize\');" type="text" id="er_authorize_button_url" name="er_authorize_button_url" style="width:400px"  value="'.$aoptions['button'].'"><span id="er-authorize-button-img" style="float:right;vertical-align:text-bottom;width:200px;text-align:right;"><img src="'.$aoptions['button'].'"></span>',
			);
			$table = easyreservations_generate_table('reservation_authorize_settings_table', 'Authorize.Net '.__( 'settings' , 'easyReservations'), $rows, 'style="margin-top:7px;width:99%"');
			echo easyreservations_generate_form('reservation_authorize_settings', 'admin.php?page=reservation-settings&site=pay#reservation_authorize_settings', 'post', false, array('easy-set-authorize' => wp_create_nonce('easy-set-authorize'), 'action' => 'reservation_authorize_settings'), $table);

			// 2checkout
			if(isset($coptions['return_url'])) $auth_return = $coptions['return_url']; else $auth_return = 'useredit';
			$rows = array(
				'col' => 'To automatically set the paid amount and approve you have to log-in at <strong>2checkout.com</strong>, go to <strong>Notifications -> Settings</strong>, enable the <strong>Fraud Status Changed</strong> and enter <code>'.RESERVATIONS_URL.'lib/modules/paypal/authorized_ipn.php</code> in the text field.',
				__( 'Mode', 'easyReservations' ) => easyreservations_generate_input_select('er_2checkout_modus', array('off' => __( 'Off' , 'easyReservations' ),'sandbox' => __( 'Sandbox' , 'easyReservations' ),'on' => __( 'Live' , 'easyReservations' )), $coptions['modus'] ,'style="width:100px"'),
				__( 'Routine', 'easyReservations' ) => easyreservations_generate_input_select('er_2checkout_routine', array('https://www.2checkout.com/checkout/purchase' => __( 'Multi-page Payment' , 'easyReservations' ),'https://www.2checkout.com/checkout/spurchase' => __( 'Single Page Payment' , 'easyReservations' )), $coptions['url'],'style="width:100px"'),
				'Login ID' => '<input type="text" style="width:200px" name="er_2checkout_login" value="'.$coptions['login'].'">',
				__( 'Name', 'easyReservations' ) => '<input type="text" style="width:200px" name="er_2checkout_name" value="'.$coptions['name'].'"> Available tag: [resource]',
				__( 'After success return to', 'easyReservations' ) => '<select id="er_2checkout_return" name="er_2checkout_return" onchange="erReturn(this,\'2checkout\');"><option value="useredit" '.selected($auth_return, 'useredit', false).'>'.__( 'User Control Panel' , 'easyReservations' ).'</option><option value="page" '.selected(is_numeric($auth_return), true, false).'>'.__( 'Page' , 'easyReservations' ).'</option><option value="own" '.selected((!is_numeric($auth_return) && $auth_return != 'useredit') ? true : false, true, false).'>'.__( 'URL' , 'easyReservations' ).'</option></select><span id="er_2checkout_return_2nd"></span>',
				__( 'Cancel', 'easyReservations' ) => '<input type="text" style="width:300px" name="er_2checkout_cancel_url" value="'.$coptions['cancel_url'].'">',
				__( 'Language', 'easyReservations' ) => easyreservations_generate_input_select('er_2checkout_lang',array('zh' => 'Chinese', 'da' => 'Danish', 'nl' => 'Dutch', '' => 'English', 'fr' => 'French', 'gr' => 'German', 'el' => 'Greek', 'it' => 'Italian', 'jp' => 'Japanese', 'no' => 'Norwegian', 'pt' => 'Portuguese', 'sl' => 'Slovenian', 'es_ib' => 'Spanish ib', 'es_la' => 'Spanish la', 'sv' => 'Swedish'),$coptions['lang']),
				__( 'Skip review page', 'easyReservations' ) => '<input type="checkbox" name="er_2checkout_skip" value="1" '.checked($coptions['skip'],1,false).'> Skip the order review page of the purchase routine',
				__( 'Button', 'easyReservations' ) => easyreservations_generate_input_select('er_dibs_button',array('own' => __( 'Select' , 'easyReservations' ),RESERVATIONS_URL.'lib/modules/paypal/2checkout.gif' => '2checkout button'),$doptions['button'],'onchange="changeImg(this,\'2checkout\');"').'<input onchange="changeImg(this,\'2checkout\');" type="text" id="er_2checkout_button_url" name="er_2checkout_button_url" style="width:400px"  value="'.$coptions['button'].'"><span id="er-2checkout-button-img" style="float:right;vertical-align:text-bottom;width:200px;text-align:right;"><img src="'.$coptions['button'].'"></span>',
			);
			$table = easyreservations_generate_table('reservation_2checkout_settings_table', '2checkout '.__( 'settings' , 'easyReservations'), $rows, 'style="margin-top:7px;width:99%"');
			echo easyreservations_generate_form('reservation_2checkout_settings', 'admin.php?page=reservation-settings&site=pay#reservation_2checkout_settings', 'post', false, array('easy-set-2checkout' => wp_create_nonce('easy-set-2checkout'), 'action' => 'reservation_2checkout_settings'), $table);

			// Google Wallet
			if(isset($goptions['return_url'])) $auth_return = $goptions['return_url']; else $auth_return = 'useredit';
			$rows = array(
				'col' => 'To automatically set the paid amount and approve you have to log in your google account, go to <strong>Settings -> Integration</strong>, enter <code>'.RESERVATIONS_URL.'lib/modules/paypal/google_ipn.php</code> as API callback URL and select <strong>Notification Serial Number</strong> thereafter.',
				__( 'Mode' , 'easyReservations' ) => easyreservations_generate_input_select('er_wallet_modus', array('off' => __( 'Off' , 'easyReservations' ),'sandbox' => __( 'Sandbox' , 'easyReservations' ),'on' => __( 'Live' , 'easyReservations' )), $goptions['modus'] ,'style="width:100px"'),
				'Merchant ID' => '<input type="text" style="width:200px" name="er_wallet_login" value="'.$goptions['merchantid'].'">',
				'Merchant Key' => '<input type="text" style="width:200px" name="er_wallet_merchantkey" value="'.$goptions['merchantkey'].'">',
				__( 'Name' , 'easyReservations' ) => '<input type="text" style="width:200px" name="er_wallet_name" value="'.$goptions['name'].'"> Available tag: [resource]',
				__( 'After success return to' , 'easyReservations' ) => '<select id="er_wallet_return" name="er_wallet_return" onchange="erReturn(this,\'wallet\');"><option value="useredit" '.selected($auth_return, 'useredit', false).'>'.__( 'User Control Panel' , 'easyReservations' ).'</option><option value="page" '.selected(is_numeric($auth_return), true, false).'>'.__( 'Page' , 'easyReservations' ).'</option><option value="own" '.selected((!is_numeric($auth_return) && $auth_return != 'useredit') ? true : false, true, false).'>'.__( 'URL' , 'easyReservations' ).'</option></select><span id="er_wallet_return_2nd"></span>',
				__( 'Cancel' , 'easyReservations' ) => '<input type="text" style="width:300px" name="er_wallet_cancel_url" value="'.$goptions['cancel_url'].'">',
				__( 'Currency' , 'easyReservations' ) => '<input type="text" style="width:300px" name="er_wallet_currency" value="'.$goptions['currency'].'"> Enter your three-letter <a target="_new" href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 currency code</a>. E.g. USD for US Dollars.',
			);
			$table = easyreservations_generate_table('reservation_wallet_settings_table', 'Google Wallet '.__( 'settings' , 'easyReservations'), $rows, 'style="margin-top:7px;width:99%"');
			echo easyreservations_generate_form('reservation_wallet_settings', 'admin.php?page=reservation-settings&site=pay#reservation_wallet_settings', 'post', false, array('easy-set-dibs' => wp_create_nonce('easy-set-wallet'), 'action' => 'reservation_wallet_settings'), $table);

			// DIBS
			if(isset($doptions['return_url'])) $auth_return = $doptions['return_url']; else $auth_return = 'useredit';
			$rows = array(
				__( 'Mode' , 'easyReservations' ) => easyreservations_generate_input_select('er_dibs_modus', array('off' => __( 'Off' , 'easyReservations' ),'sandbox' => __( 'Sandbox' , 'easyReservations' ),'on' => __( 'Live' , 'easyReservations' )), $doptions['modus'] ,'style="width:100px"'),
				'Merchant ID' => '<input type="text" style="width:200px" name="er_dibs_login" value="'.$doptions['merchant'].'">',
				'Mac Key' => '<input type="text" style="width:200px" name="er_dibs_mackey" value="'.$doptions['mackey'].'">',
				__( 'After success return to' , 'easyReservations' ) => '<select id="er_dibs_return" name="er_dibs_return" onchange="erReturn(this,\'dibs\')"><option value="useredit" '.selected($auth_return, 'useredit', false).'>'.__( 'User Control Panel' , 'easyReservations' ).'</option><option value="page" '.selected(is_numeric($auth_return), true, false).'>'.__( 'Page' , 'easyReservations' ).'</option><option value="own" '.selected((!is_numeric($auth_return) && $auth_return != 'useredit') ? true : false, true, false).'>'.__( 'URL' , 'easyReservations' ).'</option></select><span id="er_dibs_return_2nd"></span>',
				__( 'Cancel' , 'easyReservations' ) => '<input type="text" style="width:300px" name="er_dibs_cancel_url" value="'.$doptions['cancel_url'].'">',
				__( 'Currency' , 'easyReservations' ) => easyreservations_generate_input_select('er_dibs_currency',array('208'=>'Danish Kroner (DKK)','978'=>'Euro (EUR)','840'=>'US Dollar $ (USD)','826'=>'English Pound (GBP)','752'=>'Swedish Kroner (SEK)','036'=>'Australian Dollar (AUD)','124'=>'Canadian Dollar (CAD)','352'=>'Icelandic Kroner (ISK)','392'=>'Japanese Yen (JPY)','554'=>'New Zealand Dollar (NZD)','578'=>'Norwegian Kroner (NOK)','756'=>'Swiss Franc (CHF)','949'=>'Turkish Lire (TRY)'),$doptions['currency']),
				__( 'Language' , 'easyReservations' ) => easyreservations_generate_input_select('er_dibs_language',array('en_US' => 'English (US)', 'en_GB' => 'English (GB)', 'da_DK' => 'Danish', 'sv_SE' => 'Swedish', 'nb_NO' => 'Norwegian (Bokmål)'),$doptions['lang']),
				__( 'Button' , 'easyReservations' ) => easyreservations_generate_input_select('er_dibs_button',array('own' => __( 'Select' , 'easyReservations' ),RESERVATIONS_URL.'lib/modules/paypal/dibs.png' => 'DIBS button'),$doptions['button'],'onchange="changeImg(this,\'dibs\');"').'<input onchange="changeImg(this,\'dibs\');" type="text" id="er_dibs_button_url" name="er_dibs_button_url" style="width:400px"  value="'.$doptions['button'].'"><span id="er-dibs-button-img" style="float:right;vertical-align:text-bottom;width:200px;text-align:right;"><img src="'.$doptions['button'].'"></span>'
			);
			$table = easyreservations_generate_table('reservation_dibs_settings_table', '<a href="http://dibs.dk" target="_new">DIBS '. __( 'settings' , 'easyReservations' ).'</a>', $rows, 'style="margin-top:7px;width:99%"');
			echo easyreservations_generate_form('reservation_dibs_settings', 'admin.php?page=reservation-settings&site=pay#reservation_dibs_settings', 'post', false, array('easy-set-dibs' => wp_create_nonce('easy-set-dibs'), 'action' => 'reservations_dibs_options'), $table);

			//Ogone
			if(isset($ogoptions['return_url'])) $auth_return = $ogoptions['return_url']; else $auth_return = 'useredit';
			$rows = array(
				__( 'Mode' , 'easyReservations' ) => easyreservations_generate_input_select('er_ogone_modus', array('off' => __( 'Off' , 'easyReservations' ),'sandbox' => __( 'Sandbox' , 'easyReservations' ),'on' => __( 'Live' , 'easyReservations' )), $ogoptions['modus'] ,'style="width:100px"'),
				'PSPID' => '<input type="text" style="width:200px" name="er_ogone_pspid" value="'.$ogoptions['pspid'].'">',
				'SHA-IN pass phrase' => '<input type="text" style="width:200px" name="er_ogone_shapass" value="'.$ogoptions['shapass'].'">',
				'SHA-OUT pass phrase' => '<input type="text" style="width:200px" name="er_ogone_shapass_out" value="'.$ogoptions['shapass_out'].'">',
				__( 'After success return to' , 'easyReservations' ) => '<select id="er_ogone_return" name="er_ogone_return" onchange="erReturn(this, \'ogone\')"><option value="useredit" '.selected($auth_return, 'useredit', false).'>'.__( 'User Control Panel' , 'easyReservations' ).'</option><option value="page" '.selected(is_numeric($auth_return), true, false).'>'.__( 'Page' , 'easyReservations' ).'</option><option value="own" '.selected((!is_numeric($auth_return) && $auth_return != 'useredit') ? true : false, true, false).'>'.__( 'URL' , 'easyReservations' ).'</option></select><span id="er_ogone_return_2nd"></span>',
				__( 'Cancel' , 'easyReservations' ) => '<input type="text" style="width:300px" name="er_ogone_cancel_url" value="'.$ogoptions['cancel'].'">',
				__( 'Currency' , 'easyReservations' ) => easyreservations_generate_input_select('er_ogone_currency',array('DKK'=>'Danish Kroner (DKK)','EUR'=>'Euro (EUR)','USD'=>'US Dollar $ (USD)','GBP'=>'English Pound (GBP)','SEK'=>'Swedish Kroner (SEK)','AUD'=>'Australian Dollar (AUD)','CAD'=>'Canadian Dollar (CAD)','ISK'=>'Icelandic Kroner (ISK)','JPY'=>'Japanese Yen (JPY)','NZD'=>'New Zealand Dollar (NZD)','NOK'=>'Norwegian Kroner (NOK)','CHF'=>'Swiss Franc (CHF)', 'AED'=>'AED', 'CNY'=>'CNY', 'CYP'=>'CYP', 'CZK'=>'CZK', 'EEK'=>'EEK', 'HKD'=>'HKD', 'HRK'=>'HRK', 'HUF'=>'HUF', 'ILS'=>'ILS', 'LTL'=>'LTL', 'LVL'=>'LVL', 'MAD'=>'MAD', 'MTL'=>'MTL', 'MXN'=>'MXN', 'PLN'=>'PLN', 'RUR'=>'RUR', 'SGD'=>'SGD', 'SKK'=>'SKK', 'THB'=>'THB', 'TRL'=>'TRL', 'UAH'=>'UAH', 'XAF'=>'XAF', 'XOF'=>'XOF', 'ZAR'=>'ZAR'),$ogoptions['currency']),
				__( 'Language' , 'easyReservations' ) => easyreservations_generate_input_select('er_ogone_language',array('en_US' => 'English (US)', 'en_GB' => 'English (GB)', 'da_DK' => 'Danish', 'sv_SE' => 'Swedish', 'nb_NO' => 'Norwegian (Bokmål)'),$ogoptions['lang']),
				__( 'Logo' , 'easyReservations' ) => '<input type="text" style="width:300px" name="er_ogone_logo" value="'.$ogoptions['logo'].'">',
				__( 'Button' , 'easyReservations' ) => easyreservations_generate_input_select('er_ogone_button',array('own' => __( 'Select' , 'easyReservations' ),RESERVATIONS_URL.'lib/modules/paypal/ogone.png' => 'Ogone button'),$ogoptions['button'],'onchange="changeImg(this,\'ogone\')"').'<input onchange="changeImg(this,\'ogone\');" type="text" id="er_ogone_button_url" name="er_ogone_button_url" style="width:400px"  value="'.$ogoptions['button'].'"><span id="er-ogone-button-img" style="float:right;vertical-align:text-bottom;width:200px;text-align:right;"><img src="'.$ogoptions['button'].'"></span>',
				__( 'Button Background' , 'easyReservations' ) => '<input type="text" style="width:100px" name="er_ogone_buttonbg" value="'.$ogoptions['buttonbg'].'"> HEX Code',
			);
			$table = easyreservations_generate_table('reservation_ogone_settings_table', '<a href="http://ogone.de" target="_new">Ogone '. __( 'settings' , 'easyReservations' ).'</a>', $rows, 'style="margin-top:7px;width:99%"');
			echo easyreservations_generate_form('reservation_ogone_settings', 'admin.php?page=reservation-settings&site=pay#reservation_ogone_settings', 'post', false, array('easy-set-ogone' => wp_create_nonce('easy-set-ogone'), 'action' => 'reservations_ogone_options'), $table);

			do_action('reservations_gateway_settings');
			if(!get_option('reservations_email_to_admin_paypal')) echo '<br><a href="admin.php?page=reservation-settings&site=email" style="color:#ff0000;font-weight:bold;text-decoration:underline">'.__( 'Don\'t forget the new email types!' , 'easyReservations' ).'</a>'; ?>
			<script type="text/javascript">
				function erReturn(t,type){
					var select = t.value;
					if(select == 'useredit'){
						document.getElementById('er_'+type+'_return_2nd').innerHTML = '';
					} else if(select == 'page'){ 
						var pagesel = ''; 
						if(type == 'dibs') pagesel = '<?php echo str_replace(array("'","\r","\n"), array('"', '', ''), wp_dropdown_pages(array('selected' => (isset($doptions['return_url']) && is_numeric($doptions['return_url']) ? $doptions['return_url'] : 0), 'echo' => 0, 'name' => 'er_dibs_own_return_link' )));?>';
						else if(type == 'ogone') pagesel = '<?php echo str_replace(array("'","\r","\n"), array('"', '', ''), wp_dropdown_pages(array('selected' => (isset($ogoptions['return_url']) &&  is_numeric($ogoptions['return_url']) ? $ogoptions['return_url'] : 0), 'echo' => 0, 'name' => 'er_ogone_own_return_link' )));?>';
						else if(type == 'wallet') pagesel = '<?php echo str_replace(array("'","\r","\n"), array('"', '', ''), wp_dropdown_pages(array('selected' => (isset($goptions['return_url']) && is_numeric($goptions['return_url']) ? $goptions['return_url'] : 0), 'echo' => 0, 'name' => 'er_wallet_own_return_link' )));?>';
						else if(type == '2checkout') pagesel = '<?php echo str_replace(array("'","\r","\n"), array('"', '', ''), wp_dropdown_pages(array('selected' => (isset($coptions['return_url']) && is_numeric($coptions['return_url']) ? $coptions['return_url'] : 0), 'echo' => 0, 'name' => 'er_2checkout_own_return_link' )));?>';
						else if(type == 'authorize') pagesel = '<?php echo str_replace(array("'","\r","\n"), array('"', '', ''), wp_dropdown_pages(array('selected' => (isset($aoptions['return_url']) && is_numeric($aoptions['return_url']) ? $aoptions['return_url'] : 0), 'echo' => 0, 'name' => 'er_authorize_own_return_link' )));?>';
						else if(type == 'pay') pagesel = '<?php echo str_replace(array("'","\r","\n"), array('"', '', ''), wp_dropdown_pages(array('selected' => (isset($options['er_pay_return']) && is_numeric($options['er_pay_return']) ? $options['er_pay_return'] : 0), 'echo' => 0, 'name' => 'er_pay_own_return_link' )));?>';
						document.getElementById('er_'+type+'_return_2nd').innerHTML = pagesel;
					} else if(select == 'own'){
						var pageown = '';
						if(type == 'dibs') pageown = '<?php echo (isset($doptions['return_url']) && !is_numeric($doptions['return_url']) && $doptions['return_url'] != 'useredit') ? $doptions['return_url'] : 'http://'; ?>';
						else if(type == 'ogone') pageown = '<?php echo (isset($ogoptions['return_url']) && !is_numeric($ogoptions['return_url']) && $ogoptions['return_url'] != 'useredit') ? $ogoptions['return_url'] : 'http://'; ?>';
						else if(type == 'wallet') pageown = '<?php echo (isset($goptions['return_url']) && !is_numeric($goptions['return_url']) && $goptions['return_url'] != 'useredit') ? $goptions['return_url'] : 'http://'; ?>';
						else if(type == '2checkout') pageown = '<?php echo (isset($coptions['return_url']) && !is_numeric($coptions['return_url']) && $coptions['return_url'] != 'useredit') ? $coptions['return_url'] : 'http://'; ?>';
						else if(type == 'authorize') pageown = '<?php echo (isset($aoptions['return_url']) && !is_numeric($aoptions['return_url']) && $aoptions['return_url'] != 'useredit') ? $aoptions['return_url'] : 'http://'; ?>';
						else if(type == 'pay') pageown = '<?php echo (isset($options['return_url']) && !is_numeric($options['er_pay_return']) && $options['er_pay_return'] != 'useredit') ? $options['er_pay_return'] : 'http://'; ?>';
						document.getElementById('er_'+type+'_return_2nd').innerHTML = '<input type="text" name="er_'+type+'_own_return_link" value="'+pageown+'">';
					}
          return true;
				}
				erReturn(document.getElementById('er_dibs_return'), 'dibs');
				erReturn(document.getElementById('er_ogone_return'), 'ogone');
				erReturn(document.getElementById('er_wallet_return'), 'wallet');
				erReturn(document.getElementById('er_2checkout_return'), '2checkout');
				erReturn(document.getElementById('er_authorize_return'), 'authorize');
				erReturn(document.getElementById('er_pay_return'), 'pay');
				function changeImg(t,type){
					if(t .value != 'own'){
						document.getElementById('er_'+type+'_button_url').value = t .value;
						document.getElementById('er-'+type+'-button-img').innerHTML = '<img src="'+t.value+'">';
					} else {
						document.getElementById('er_'+type+'_button_url').value = '';
						document.getElementById('er-'+type+'-button-img').innerHTML = '';
					}
				}
				function easy_add_deposit(){
					var newfield = '<div><input type="text" name="discount_amount[]" value="">';
					newfield += '<select name="discount_type[]"><option value="cur" selected="selected">&<?php echo RESERVATIONS_CURRENCY; ?>;</option><option value="%">%</option></select>';
					newfield += '<a href="#" onclick="javascript:jQuery(this).parent().remove()">X</a></div>';
					jQuery('#discount_selection').append(newfield);
				}
			</script><?php
		}
	}

	function easyreservations_control_panel_payment_select($settings){
		$select = easyreservations_generate_input_select('er_control_panel_payment', array('0' => 'Disabled', 'paypal' => 'PayPal','authorize' => 'Authorize.net', '2checkout' => '2checkout.com', 'googlewallet' => 'Google Wallet', 'dibs' => 'DIBS payment', 'ogone' => 'Ogone'),$settings['payment']);
		echo '<tr><td><b>'.__( 'Payment button' , 'easyReservations' ).'</b>:</td><td>'.$select.'</td></tr>';
	}
	add_action('easy-control-panel-set', 'easyreservations_control_panel_payment_select', 10, 1);

	function easyreservations_add_auto_approve_setting($rows){
		$autosave = get_option('reservations_autoapprove');
		$rows['<img src="'.RESERVATIONS_URL.'images/auto.png"> <b>'.__( 'Automatically approval' , 'easyReservations' ).'</b>'] = easyreservations_generate_input_select('easy_auto_approve', array(__( 'None' , 'easyReservations' ),__( 'After submit' , 'easyReservations' ),__( 'After payment' , 'easyReservations' ),__( 'Both' , 'easyReservations' )), $autosave).' '.__( 'If there\'s free space in resource' , 'easyReservations' );
		return $rows;
	}
	add_filter('er_add_set_main_table_row', 'easyreservations_add_auto_approve_setting');

	function easyreservations_save_auto_approve_setting(){
		update_option('reservations_autoapprove', $_POST['easy_auto_approve']);
	}
	add_action('er_set_main_save', 'easyreservations_save_auto_approve_setting');

	function easyreservations_add_mails_to_array($emails){
		$newemail[] = array('reservations_email_to_admin_paypal'	=> array('name' => __('Mail to admin after payment'), 'option' => get_option('reservations_email_to_admin_paypal'), 'name_subj' => 'reservations_email_to_admin_paypal_subj', 'name_msg' => 'reservations_email_to_admin_paypal_msg', 'standard' => '8', 'name_active' => 'reservations_email_to_admin_paypal_check'));
		$newemail[] = array('reservations_email_to_user_paypal'	=> array('name' => __('Mail to guest after payment'), 'option' => get_option('reservations_email_to_user_paypal'), 'name_subj' => 'reservations_email_to_user_paypal_subj', 'name_msg' => 'reservations_email_to_user_paypal_msg', 'standard' => '9', 'name_active' => 'reservations_email_to_user_paypal_check'));
		$emails = $emails + $newemail[0] + $newemail[1];
		return $emails;
	}

	add_filter('easy-email-types', 'easyreservations_add_mails_to_array', 10, 1);

	function easyreservations_paypal_load_standards($attr = 'das'){
		$mail_to_admin = "Reservation has been paid - [paid].<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Room: [rooms] <br>Room Number: [roomnumber]<br>Price: [price]<br>Paid: [paid]<br><br>[changelog]";
		$mail_to_user = "Thanks for your payment about [paid].<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Room: [rooms] <br>Room Number: [roomnumber]<br>Price: [price]<br>Paid: [paid]<br><br>[changelog]";
		if($attr  == 'das'){?>
			<input type="hidden" value="<?php echo $mail_to_admin; ?>" name="inputemail8">
			<input type="hidden" value="<?php echo $mail_to_user; ?>" name="inputemail9"><?php
		}
		return array($mail_to_admin, $mail_to_user);
	}

	add_action('easy-htmlmails-footer', 'easyreservations_paypal_load_standards', 1, 8);

	function easyreservations_paypal_mail_settings_save(){
		if(isset($_POST["reservations_email_to_admin_paypal_msg"])){
			if(isset($_POST["reservations_email_to_admin_paypal_check"])) $reservations_email_to_admin_paypal_check = 1; else $reservations_email_to_admin_paypal_check = 0;
			if(is_array($_POST["reservations_email_to_admin_paypal_msg"])) $_POST["reservations_email_to_admin_paypal_msg"] = implode($_POST["reservations_email_to_admin_paypal_msg"]);
			$reservations_email_to_admin_paypal = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_paypal_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_admin_paypal_subj"]),
				'active' => $reservations_email_to_admin_paypal_check
			);
			update_option("reservations_email_to_admin_paypal",$reservations_email_to_admin_paypal);
		}
		if(isset($_POST["reservations_email_to_user_paypal_msg"])){
			if(isset($_POST["reservations_email_to_user_paypal_check"])) $reservations_email_to_user_paypal_check = 1; else $reservations_email_to_user_paypal_check = 0;
			if(is_array($_POST["reservations_email_to_user_paypal_msg"])) $_POST["reservations_email_to_user_paypal_msg"] = implode($_POST["reservations_email_to_user_paypal_msg"]);
			$reservations_email_to_user_paypal = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_paypal_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_user_paypal_subj"]),
				'active' => $reservations_email_to_user_paypal_check
			);
			update_option("reservations_email_to_user_paypal",$reservations_email_to_user_paypal);
		}
	}

	add_action('er_set_email_save', 'easyreservations_paypal_mail_settings_save');
}

	function easyreservation_generate_payment_form($res, $price, $submit = false, $discount = false){
		$options = array();
		$deposit = easyreservation_deposit_function($price, $discount);

		$gateways = easyreservations_get_payment_gateways();
		foreach($gateways as $key => $gateway){
			$payment_form = easyreservations_generate_paypal_button($res, $price,false, false, $discount, $key);
			if(!empty($payment_form)) $options[$key] = $payment_form;
		}

		$creditcard = easyreservations_generate_creditcard_form($res, $price);
		if(!empty($options) && !empty($creditcard)){
			$creditcard = '<span class="creditor"> '.__('or pay with credit card', 'easyReservations').' </span>'.$creditcard;
		}

		$buttons = '';
		if(!empty($options)){
			if(count($options) > 1){
				$select = '<select name="easy_payment_chooser" onchange="easyChangePayment(this.value);"><option value="">'.__('Choose payment gateway', 'easyReservations').'</option>';
				foreach($options as $type => $button){
					if(isset($gateways[$type]['name'])) $name = $gateways[$type]['name'];
					else $name = $type;
					$select .= '<option value="'.$type.'">'.$name.'</option>';
				}
				$select .= '</select>'.$deposit.'<span id="easy_payment_div" style="display:none;"></span>';
				$json_options = json_encode(str_replace('<', '<//as', $options));
				$script = <<<JAVASCRIPT
<script type="text/javascript">
	if(document.getElementById('depositbox') && !jQuery('.stripe-button-el').length) document.getElementById('depositbox').style.display= 'none';
	function easyChangePayment(type){
		jQuery('.depositbox').fadeOut(400);
		jQuery('#easy_payment_div').fadeOut(400, function(){
			if(jQuery('#easy_radio_own').length>0) jQuery('#easy_radio_own').attr('checked', false);
			if(jQuery('#easy_radio_full').length>0) jQuery('#easy_radio_full').attr('checked', false);
			if(jQuery('#easy_radio_perc').length>0) jQuery('#easy_radio_own').attr('checked', false);
			if(jQuery('#easy_deposit_own').length>0) jQuery('#easy_deposit_own').val();
			if(jQuery('#easy_deposit_perc').length>0) jQuery('#easy_deposit_perc').prop('selectedIndex',0);

			if(type != ''){
				var all_buttons = $json_options;
				if(all_buttons[type]){
					var val = all_buttons[type].replace	(/<\/\/as/g, "<");
					jQuery('#easy_payment_div').html(val);
					jQuery('#easy_payment_div,.depositbox').fadeIn(400);
				}
			}
		});
	}
</script>
JAVASCRIPT;
				$buttons = $select.$script;
			} else {
				reset($options);
				$buttons = current($options);
				$buttons = $deposit.$buttons;
				if($submit){
					$key = key($options);
					if(isset($gateways[$key]['form_name'])){
						$buttons .= '<script type="text/javascript">if(document.'.$gateways[$key]['form_name'].') document.'.$gateways[$key]['form_name'].'.submit();</script>';
					}
				}
			}
		} else {
			$buttons = $deposit;
		}
		$return = $buttons.$creditcard ;
		return $return;
	}

	function easyreservations_generate_paypal_button($res = false, $theprice = 0,  $link = false, $button = false, $discount = false, $type = 'paypal'){
		$paypalOptions = get_option('reservations_paypal_options');
		$array = false;
		$return_opt = false;
		if($type == 'paypal'){
			if(!$paypalOptions || empty($paypalOptions) || $paypalOptions['modus'] == 'off') return '';
			$return_opt = $paypalOptions['er_pay_return'];
			$gateway_opt = $paypalOptions;
		} else {
			$gateways = easyreservations_get_payment_gateways();
			if(isset($gateways[$type])){
				if(isset($gateways[$type]['options'])){
					$gateway_opt = get_option($gateways[$type]['options']);
					if(!$gateway_opt || empty($gateway_opt) || $gateway_opt['modus'] == 'off') return '';
					if(isset($gateway_opt) && isset($gateway_opt['return_url'])) $return_opt = $gateway_opt['return_url'];
				}
			}
		}

		easyreservations_load_resources();
		global $the_rooms_array;
		if(is_array($res) && !isset($res[1])) $res = new Reservation((int) $res[0]);
		$taxrate = 0;
		if(is_array($res)){
			$i = 0; $children = 0; $adults = 0; $the_id = '';
			foreach($res as $tid){
				$new = new Reservation((int) $tid);
				if($i == 0) $themail = $res->email;
				$adults +=  $new->adults;
				$children +=  $new->childs;
				$the_id .= $new->id.'-';
				if(isset($new->taxrate)) $taxrate += $new->taxrate;
				$i++;
			}
			$price = round($theprice,2);
			$res = $new;
			$resource = $new->resource;
			$persons = $adults+$children;
			$the_id = substr($the_id,0,-1);
			if(!isset($paypalOptions['multititle'])) $paypalOptions['multititle'] = "[nr] Reservations for [adults]+[childs] persons";
			$theTitle = str_replace(array('[nr]', '[persons]', '[adults]', '[childs]'), array($i,$persons,$adults,$children), $paypalOptions['multititle']);
		} else {
			$res->Calculate();
			if($theprice && $theprice > 0 ) $price = $theprice;
			else $price = $res->price-$res->paid;
			if(isset($res->taxrate)) $taxrate += $res->taxrate;
			$the_id = $res->id;
			$themail = $res->email;
			$resource = $res->resource;
			$theTitle = str_replace(array('[resource]', '[units]', '[times]', '[nights]', '[arrival]', '[departure]', '[persons]', '[adults]', '[childs]'), array( __($the_rooms_array[$resource]->post_title),$res->times,$res->times,$res->times,date(RESERVATIONS_DATE_FORMAT_SHOW, $res->arrival),date(RESERVATIONS_DATE_FORMAT_SHOW, $res->departure),($res->adults+$res->childs),$res->adults,$res->childs), $paypalOptions['title']);
		}
		if($discount && is_numeric($discount) && $discount < 100) $price = round( $price/100*$discount,2);
		$nonce = substr(wp_hash(wp_nonce_tick() .'|easy-pay-submit|0', 'nonce'), -12, 10);

		if($return_opt == 'useredit'){
			$editPageURL = get_option('reservations_edit_url');
			if(isset($editPageURL) && !empty($editPageURL)) $theReturnURL = $editPageURL.'?edit&id='.$res->id.'&email='.$themail.'&ernonce='.substr(wp_hash(wp_nonce_tick().'|easyusereditlink|0', 'nonce'), -12, 10);
			else $theReturnURL = '';
		} elseif(is_numeric($return_opt)) $theReturnURL = get_permalink($return_opt);
		else $theReturnURL = $return_opt;

		if(!empty($price) && $price > 0){
			$return = '<form onSubmit="easyReservationFSubmit = true" ';
			if($type == 'paypal'){
				if($gateway_opt['modus']=='on') $theModusURL = 'https://www.paypal.com/cgi-bin/webscr';
				else $theModusURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				if($link){
					$link = $theModusURL.'?business='.htmlentities($gateway_opt['owner']).'&cmd=_xclick&currency_code='.htmlentities($gateway_opt['currency']).'&custom='.$nonce.'&invoice='.$the_id.'&amount='.$price.'&item_name='.htmlentities(rawurlencode($theTitle)).'&notify_url='.urlencode(stripslashes(WP_PLUGIN_URL.'/easyreservations/lib/modules/paypal/paypal_ipn.php'));
					return esc_url_raw($link, 'mailto');
				} else {
					$return .= 'name="_xclick" action="'.$theModusURL.'" method="post" id="easy_paypal_form">';
					if(!$button) $return.= '<input type="image" src="'.$gateway_opt['button'].'" border="0" name="submit" alt="Pay with Paypal!">';
					else $return.= $button;
					$array = array(
						'cmd' => '_xclick',
						'custom' => $nonce,
						'notify_url' => WP_PLUGIN_URL.'/easyreservations/lib/modules/paypal/paypal_ipn.php',
						'invoice' => $the_id,
						'item_name' => $theTitle,
						'amount' => $price,
						'business' => $gateway_opt['owner'],
						'cancel_return' => $gateway_opt['cancel_url'],
						'currency_code' => $gateway_opt['currency'],
						'return' => $theReturnURL
					);
					if(isset($gateway_opt['message']) && !empty($gateway_opt['message'])) $array['rn'] = $gateway_opt['message'];
					if(isset($gateway_opt['language'])) $array['lc'] = $gateway_opt['language'];
					if(isset($gateway_opt['charset'])) $array['charset'] = $gateway_opt['charset'];
				}
			} elseif($type == 'authorize'){
				if($gateway_opt['modus']=='on'){
					$theModusURL = 'https://secure.authorize.net/gateway/transact.dll';
					$test = 'false';
				} else {
					$theModusURL = 'https://test.authorize.net/gateway/transact.dll';
					$test = 'true';
				}
				$loginID = $gateway_opt['loginid'];
				$transactionKey = $gateway_opt['transactionkey'];
				$sequence	= rand(1, 1000);
				$timeStamp	= time();
				$fingerprint = hash_hmac("md5", $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $price . "^", $transactionKey);

				$return .= 'name="authorize" action="'.$theModusURL.'" method="post" id="easy_authorize_form">';
				if(!$button) $return.= '<input type="image" src="'.$gateway_opt['button'].'" border="0" name="submit">';
				else $return.= $button;
				$array = array(
					'custom' => $nonce,
					'x_login' => $loginID,
					'x_amount' => $price,
					'x_description' => htmlentities($theTitle),
					'x_invoice_num' => $the_id,
					'x_show_form' => 'PAYMENT_FORM',
					'x_test_request' => $test,
					'x_receipt_link_text' => $gateway_opt['return'],
					'x_receipt_link_url' => $theReturnURL,
					'x_cancel_url_text' => $gateway_opt['cancel'],
					'x_cancel_url' => $gateway_opt['cancel_url'],
					'x_relay_response' => 'true',
					'x_relay_url' => WP_PLUGIN_URL.'/easyreservations/lib/modules/paypal/authorize_ipn.php',
					'x_fp_sequence' => $sequence,
					'x_fp_timestamp' => $timeStamp,
					'x_fp_hash' => $fingerprint,
				);
			} elseif($type=='2checkout'){
				$return .= 'action="'.$gateway_opt['url'].'" method="post" name="checkout">';
				if(!$button) $return.= '<input type="image" src="'.$gateway_opt['button'].'" border="0" name="submit" alt="Pay with Paypal!">';
				else $return.= $button;
				$array = array(
					'merchant_order_id' => $nonce,
					'custom' => $nonce,
					'fixed' => 'Y',
					'x_invoice_num' => $the_id,
					'x_amount' => $price,
					'product_id' => $the_id,
					'x_login' => $gateway_opt['login'],
					'x_Receipt_Link_URL' => $theReturnURL,
					'id_type' => '1',
					'c_name' => htmlentities(str_replace('[resource]', __($the_rooms_array[$resource]->post_title),$gateway_opt['name'])),
					'c_description' => htmlentities($theTitle),
					'x_relay_response' => 'true',
					'x_relay_url' => WP_PLUGIN_URL.'/easyreservations/lib/modules/paypal/authorize_ipn.php',
					'li_0_type' => 'product',
					'li_0_quantity' => '1',
					'li_0_tangible' => '1',
					'li_0_name' => $theTitle,
					'li_0_price' => $price
				);
				if($gateway_opt['modus']=='sandbox') $array['demo'] = "Y";
				if(isset($gateway_opt['lang']) && !empty($gateway_opt['lang'])) $array['lang'] = $gateway_opt['lang'];
				if(isset($gateway_opt['skip']) && $gateway_opt['skip'] == 1) $array['skip_landing'] = '1';
			} elseif($type == 'googlewallet'){
				if($gateway_opt['modus']=='on') $theModusURL = 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/';
				else $theModusURL = 'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/';
				$return .= 'action="'.$theModusURL.$gateway_opt['merchantid'].'" id="BB_BuyButtonForm" method="post" name="googlewallet" target="_top">';
				$return .= '<input alt="" src="https://sandbox.google.com/checkout/buttons/buy.gif?merchant_id='.$gateway_opt['merchantid'].'&amp;w=117&amp;h=48&amp;style=white&amp;variant=text&amp;loc=en_US" type="image"/>';
				$array = array(
					'item_name_1' => htmlentities(str_replace('[resource]', __($the_rooms_array[$resource]->post_title),$gateway_opt['name'])),
					'item_description_1' => htmlentities($theTitle),
					'item_merchant_id_1' => $the_id,
					'item_quantity_1' => '1',
					'continue_url' => $theReturnURL,
					'item_currency_1' => $gateway_opt['currency'],
					'_charset_' => 'utf-8'
				);
			} elseif($type == 'ogone'){
				if($gateway_opt['modus']=='on') $theModusURL = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
				else $theModusURL = 'https://secure.ogone.com/ncol/test/orderstandard.asp';
				$return .= 'method="post" action="'.$theModusURL.'" id="form1" name="form1">';
				if(!$button) $return.= '<input type="image" src="'.$gateway_opt['button'].'" border="0" name="submit" alt="Pay with Ogone!">';
				else $return.= $button;
				$array = array(
					'COM' => htmlentities($theTitle),
					'PSPID' => $gateway_opt['pspid'],
					'CURRENCY' => $gateway_opt['currency'],
					'LANGUAGE' => $gateway_opt['lang'],
					'ORDERID' => $the_id,
					'AMOUNT' => str_replace(array('.', ','), '', number_format($price,2)),
					'COMPLUS' => $nonce,
					'DECLINEURL' => $gateway_opt['cancel'],
					'BUTTONBGCOLOR' => $gateway_opt['buttonbg'],
					'LOGO' => $gateway_opt['logo'],
					'ACCEPTURL' => $theReturnURL
				);
				ksort($array, SORT_NATURAL);
				$string = "";
				foreach($array as $key => $value){
					if(empty($value) && $value !== 0) continue;
					$string .= "$key=$value".$gateway_opt['shapass'];
				}
				$sha = sha1($string);
				$array['SHASIGN'] = $sha;
			} elseif($type == 'dibs'){
				$return .= 'action="https://sat1.dibspayment.com/dibspaymentwindow/entrypoint" method="post" name="dibs" target="_top">';
				if(!$button) $return.= '<input type="image" src="'.$gateway_opt['button'].'" border="0" name="submit" alt="Pay with DIBSpayment!">';
				else $return.= $button;
				$array = array(
					'merchant' => $gateway_opt['merchant'],
					'cancelReturnUrl' => $gateway_opt['cancel_url'],
					'callbackUrl' => WP_PLUGIN_URL.'/easyreservations/lib/modules/paypal/dibs_ipn.php',
					'currency' => $gateway_opt['currency'],
					'orderId' => $the_id,
					'amount' => str_replace(array('.', ','), '', number_format($price,2)),
					'language' => $gateway_opt['lang'],
					's_custom' => $nonce,
					'oiTypes' => 'QUANTITY;DESCRIPTION;AMOUNT;VATPERCENT',
					'oiNames' => 'Items;Description;Amount;VatAmount',
					'oiRow1' => '1;'.$theTitle.';'.str_replace(array('.', ','), '', number_format($price*100/($taxrate+100),2)).';'.($taxrate*100),
					'paytype' => 'VISA,MC,AMEX,MTRO,ELEC',
					'acceptReturnUrl' => $theReturnURL
				);
				if($gateway_opt['modus']=='sandbox') $array['test'] = '1';
				ksort($array);
				$string = "";
				foreach($array as $key => $value){
					if ($key != "MAC"){ // Don't include the MAC in the calculation of the MAC.
						if(strlen($string) > 0) $string .= "&";
						$string .= "$key=$value"; // create string representation
					}
				}
				$mackey = '';
				foreach (explode("\n", trim(chunk_split($gateway_opt['mackey'], 2))) as $h) $mackey .= chr(hexdec($h));
				$MAC = hash_hmac("sha256",  $string, $mackey);
				$array['MAC'] = $MAC;
				$array['oiRow1'] = htmlspecialchars($array['oiRow1']);
			} else $return  = apply_filters('reservations_generate_gateway_button', $res, $the_id, $theTitle, $price, $nonce);

			if($array) $return .= easyreservations_generate_hidden_fields($array).'</form><!-- HIERHIER -->';
			return $return;
		}
	}

	add_action( 'er_edit_add_action', 'easyreservations_validate_payment' );

	function easyreservation_deposit_function($price, $discount = false){
		$paypalOptions = get_option('reservations_paypal_options');
		if($discount && is_numeric($discount) && $discount < 100) $price = round( $price/100*$discount,2);
		$deposit = $paypalOptions['deposit'];
		$deposit_html = '';
		$last = false;
		if($deposit['on'] == 1){
			if($deposit['perc'] == 1){
				if(!empty($deposit['percamt'])){
					$new = false;
					if(is_array($deposit['percamt'])){
						$new = true;
						$array = $deposit['percamt'];
					} else $array = explode(',', $deposit['percamt']);
					$perc_options = '';
					foreach($array as $explode){
						if(!empty($explode)){
							if($new){
								if(substr($explode,-1) == '%'){
									$explode = substr($explode, 0, -1);
									$value = $explode.'%';
									$content = $explode.'% - '.easyreservations_format_money($price/100*$explode, 1);
								} else {
									$value = $explode;
									$content = easyreservations_format_money($explode, 1);
								}
							} else{
								$value = $explode.'%';
								$content = $explode.'% - '.easyreservations_format_money($price/100*$explode, 1);
							}
							$perc_options .= '<option value="'.$value.'">'.$content.'</option>';
						}
					}
					if(!empty($perc_options)){
						$last = 'perc';
						$deposit_html .= '<span class="submitrow"><input type="radio" name="easy_deposit_radio[]" id="easy_radio_perc" value="perc" onchange="changePayPalAmount(\'perc\')"> '.__('Pay a deposit of', 'easyReservations').' <select id="easy_deposit_perc" onchange="changePayPalAmount(\'perc\')">'.$perc_options.'</select></span>';
					}
				}
			}
			if($deposit['own'] == 1){
				$last = 'own';
				$deposit_html .= '<span class="submitrow"><input type="radio" name="easy_deposit_radio[]" id="easy_radio_own" value="own" onchange="changePayPalAmount(\'own\')"> '.__('Pay a deposit of', 'easyReservations').' <input type="number" id="easy_deposit_own" style="width:100px;text-align:right" onchange="changePayPalAmount(\'own\')"></span>';
			}
			if($deposit['full'] == 1){
				$last = 'full';
				$deposit_html.= '<span class="submitrow"><input type="radio" name="easy_deposit_radio[]" id="easy_radio_full" value="full" checked="checked"  onchange="changePayPalAmount(\'full\')"> '.__('Pay the full price of', 'easyReservations').' '.easyreservations_format_money($price, 1).'</span>';
			}

			$deposit_html .= '<script type="text/javascript">var easyStartPrice = '.$price.';';
			$deposit_html .= 'function easy_set_deposit_amount(price){';
			foreach(easyreservations_get_payment_gateways() as $key => $gateway){
				if(isset($gateway['form_name'])) $deposit_html .= 'if(document.'.$gateway['form_name'].') document.'.$gateway['form_name'].'.'.$gateway['amount_name'].'.value = ';
				if($key == 'ogone') $deposit_html .= 'price.toFixed(2).replace(".","");';
				else $deposit_html .= 'price;';
			}
			$deposit_html .= "}";
			if($last) $deposit_html .= "changePayPalAmount('$last');";
			$deposit_html .= '</script>';

/*
			NEW CHECK BETTER

			$easyreservations_script .= 'var easyStartPrice = '.$price.';';
			$easyreservations_script .= 'function easy_set_deposit_amount(price){';
			foreach(easyreservations_get_payment_gateways() as $key => $gateway){
				if(isset($gateway['form_name'])) $easyreservations_script .= 'if(document.'.$gateway['form_name'].') document.'.$gateway['form_name'].'.'.$gateway['amount_name'].'.value = ';
				if($key == 'ogone') $easyreservations_script .= 'price.toFixed(2).replace(".","");';
				else $easyreservations_script .= 'price;';
			}
			$easyreservations_script .= "}";
*/
			global $easyreservations_script;
			if($last) $easyreservations_script .= "jQuery(document).ready(function(){changePayPalAmount('$last');});";

			if(!empty($deposit_html)) return '<span id="depositbox" class="depositbox">'.$deposit_html.'</span>';
		}
		return '';
	}

	add_action('easy-form-paypal', 'easyreservation_deposit_function', 10, 1);

	function easyreservations_auto_approve($res, $change = true, $returns = false){
		$roomcount = get_post_meta($res->resource, "roomcount", true);
		if(is_array($roomcount)){
			$roomcount = $roomcount[0];
			$bypersons = true;
		}
		for($i = 1; $i <= $roomcount; $i++){
			$res->resourcenumber = $i;
			$check = $res->checkAvailability(0);
			if($check === false){
				$res->status = 'yes';
				if($change) return $res->editReservation(array('status', 'resourcenumber'), false);
				elseif($returns) return $res;
				else return true;
			} elseif(isset($bypersons)) break;
		}
		if($returns){
			$res->resourcenumber = 0;
			return $res;
		}
		return false;
	}

	function easyreservations_check_autoapprove($res, $where){
		$autosave = get_option('reservations_autoapprove');
		if($autosave == $where || $autosave == 3) easyreservations_auto_approve($res);
	}

	add_action('easy-add-res', 'easyreservations_check_autoapprove', 5, 2);

	function easyreservations_validate_payment(){
		if(isset($_GET['amt'])) echo '<div class="easy_form_success">'.sprintf ( __( 'Payment about %1$s successful' , 'easyReservations' ), easyreservations_format_money( $_GET['amt'],1 )),'</div>';
	}

	function easyreservations_check_curl() {
		if(in_array  ('curl', get_loaded_extensions())) return true;
		else return false;
	}

	function easyreservations_check_openssl() {
		if(in_array  ('openssl', get_loaded_extensions())) return true;
		else	return false;
	}

	function easyreservations_ipn_callback($invoice_nr, $amount){
		$autosave = get_option('reservations_autoapprove');
		$array = array();
		if(strpos($invoice_nr, '-') === false){
			$res = new Reservation((int) $invoice_nr);
			$array[] = 'pricepaid';
			$res->updatePricepaid($amount);
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
			$explode = explode('-', $invoice_nr);
			$total = count($explode) - 1;
			$totalpaid = $amount;
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
	}
?>