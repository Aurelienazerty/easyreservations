<?php
function reservation_settings_page(){
	do_action('easy-header');
	global $wpdb;

	if(isset($_GET["deleteform"])) $namtetodelete = $_GET['deleteform'];
	if(isset($_POST["action"])) $action = $_POST['action'];
	if(isset($_GET["site"])) $setting_current_page = $_GET['site'];
	else { $setting_current_page="general"; $ifgeneralcurrent='class="current"'; }
	if($setting_current_page=="about") $ifaboutcurrent='class="current"';

	if(isset($action) && $action == "reservation_clean_database"){
		$wpdb->query( "DELETE FROM ".$wpdb->prefix ."reservations WHERE departure < NOW() AND approve != 'yes' " );
		$prompt = '<div class="update"><p>'.__( 'Database cleaned' , 'easyReservations' ).'</p></div>';
	}

	if(isset($_POST['easy-set-main'])){ //Set Reservation settings
		if(!wp_verify_nonce($_POST['easy-set-main'], 'easy-set-main' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		if(isset($_POST["reservations_uninstall"])) $reservations_uninstall = 1; else $reservations_uninstall = 0;
		if(isset($_POST["reservations_time"])) $reservations_time = 1; else $reservations_time = 0;
		if(isset($_POST["reservations_tutorial"])) $tutorial = 1; else $tutorial = 0;
		if(isset($_POST['reservations_resourcemerge_box'])) $mergeres = $_POST['reservations_resourcemerge'];
		else $mergeres = 0;
		update_option("reservations_uninstall", $reservations_uninstall);
		if(isset($_POST['reservations_currency_whitespace'])) $white = 1;
		else $white = 0;
		$settings_array = array( 'style' => $_POST["reservations_style"], 'currency' => array('sign' => $_POST["reservations_currency"], 'whitespace' => $white, 'decimal' => $_POST["reservations_currency_decimal"], 'divider1' => $_POST["reservations_currency_divider1"], 'divider2' => $_POST["reservations_currency_divider2"], 'place' => $_POST['reservations_currency_place']), 'date_format' => $_POST["reservations_date_format"], 'time_format' => $_POST["reservations_time_format"], 'time' => $reservations_time, 'tutorial' => $tutorial, 'mergeres' => array('merge' => $mergeres,'blockbefore'=>$_POST['blockbefore'], 'blockafter' => $_POST['blockafter']));
		update_option("reservations_settings", $settings_array);
		update_option("reservations_regular_guests", $_POST["regular_guests"]);
		update_option("easyreservations_successful_script", $_POST["javascript"]);
		update_option("reservations_support_mail", $_POST["reservations_support_mail"]);
		do_action( 'er_set_main_save' );
		$prompt = '<div class="updated"><p>'.__( 'General settings saved', 'easyReservations' ).'</p></div>';
	}

	if(isset($action) && $action == "reservations_email_settings"){//Set Reservation Mails
		do_action( 'er_set_email_save' );

		if(isset($_POST["reservations_email_sendmail_msg"])){
			if(isset($_POST["reservations_email_sendmail_check"])) $reservations_email_sendmail_check = 1; else $reservations_email_sendmail_check = 0;
			if(is_array($_POST["reservations_email_sendmail_msg"])) $_POST["reservations_email_sendmail_msg"] = implode($_POST["reservations_email_sendmail_msg"]);
			$reservations_email_sendmail = array(
				'msg' => stripslashes($_POST["reservations_email_sendmail_msg"]),
				'subj' => stripslashes($_POST["reservations_email_sendmail_subj"]),
				'active' => $reservations_email_sendmail_check
			);
			update_option("reservations_email_sendmail",$reservations_email_sendmail);
		}

		if(isset($_POST["reservations_email_to_admin_msg"])){
			if(isset($_POST["reservations_email_to_admin_check"])) $reservations_email_to_admin_check = 1; else $reservations_email_to_admin_check = 0;
			if(is_array($_POST["reservations_email_to_admin_msg"])) $_POST["reservations_email_to_admin_msg"] = implode($_POST["reservations_email_to_admin_msg"]);
			$reservations_email_to_admin = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_admin_subj"]),
				'active' => $reservations_email_to_admin_check
			);
			update_option("reservations_email_to_admin",$reservations_email_to_admin);
		}

		if(isset($_POST["reservations_email_to_userapp_msg"])){
			if(isset($_POST["reservations_email_to_userapp_check"])) $reservations_email_to_userapp_check = 1; else $reservations_email_to_userapp_check = 0;
			if(is_array($_POST["reservations_email_to_userapp_msg"])) $_POST["reservations_email_to_userapp_msg"] = implode($_POST["reservations_email_to_userapp_msg"]);
			$reservations_email_to_userapp = array( 
				'msg' => stripslashes($_POST["reservations_email_to_userapp_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_userapp_subj"]),
				'active' => $reservations_email_to_userapp_check
			);
			update_option("reservations_email_to_userapp",$reservations_email_to_userapp);
		}

		if(isset($_POST["reservations_email_to_userdel_msg"])){
			if(isset($_POST["reservations_email_to_userdel_check"])) $reservations_email_to_userdel_check = 1; else $reservations_email_to_userdel_check = 0;
			if(is_array($_POST["reservations_email_to_userdel_msg"])) $_POST["reservations_email_to_userdel_msg"] = implode($_POST["reservations_email_to_userdel_msg"]);
			$reservations_email_to_userdel = array(
				'msg' => stripslashes($_POST["reservations_email_to_userdel_msg"]), 
				'subj' => stripslashes($_POST["reservations_email_to_userdel_subj"]),
				'active' => $reservations_email_to_userdel_check
			);
			update_option("reservations_email_to_userdel",$reservations_email_to_userdel);
		}

		if(isset($_POST["reservations_email_to_user_msg"])){
			if(isset($_POST["reservations_email_to_user_check"])) $reservations_email_to_user_check = 1; else $reservations_email_to_user_check = 0;
			if(is_array($_POST["reservations_email_to_user_msg"])) $_POST["reservations_email_to_user_msg"] = implode($_POST["reservations_email_to_user_msg"]);
			$reservations_email_to_user = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_user_subj"]),
				'active' => $reservations_email_to_user_check
			);
			update_option("reservations_email_to_user",$reservations_email_to_user);
		}

		if(isset($_POST["reservations_email_to_user_edited_msg"])){
			if(isset($_POST["reservations_email_to_user_edited_check"])) $reservations_email_to_user_edited_check = 1; else $reservations_email_to_user_edited_check = 0;
			if(is_array($_POST["reservations_email_to_user_edited_msg"])) $_POST["reservations_email_to_user_edited_msg"] = implode($_POST["reservations_email_to_user_edited_msg"]);
			$reservations_email_to_user_edited = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_edited_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_user_edited_subj"]),
				'active' => $reservations_email_to_user_edited_check
			);
			update_option("reservations_email_to_user_edited",$reservations_email_to_user_edited);
		}

		if(isset($_POST["reservations_email_to_admin_edited_msg"])){
			if(isset($_POST["reservations_email_to_admin_edited_check"])) $reservations_email_to_admin_edited_check = 1; else $reservations_email_to_admin_edited_check = 0;
			if(is_array($_POST["reservations_email_to_admin_edited_msg"])) $_POST["reservations_email_to_admin_edited_msg"] = implode($_POST["reservations_email_to_admin_edited_msg"]);
			$reservations_email_to_admin_edited = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_edited_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_admin_edited_subj"]),
				'active' => $reservations_email_to_admin_edited_check
			);
			update_option("reservations_email_to_admin_edited",$reservations_email_to_admin_edited);
		}

		if(isset($_POST["reservations_email_to_admin_canceled_msg"])){
			if(isset($_POST["reservations_email_to_admin_canceled_check"])) $reservations_email_to_admin_canceled_check = 1; else $reservations_email_to_admin_canceled_check = 0;
			if(is_array($_POST["reservations_email_to_admin_canceled_msg"])) $_POST["reservations_email_to_admin_canceled_msg"] = implode($_POST["reservations_email_to_admin_canceled_msg"]);
			$reservations_email_to_admin_canceled = array(
				'msg' => stripslashes($_POST["reservations_email_to_admin_canceled_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_admin_canceled_subj"]),
				'active' => $reservations_email_to_admin_canceled_check
			);
			update_option("reservations_email_to_admin_canceled",$reservations_email_to_admin_canceled);
		}

		if(isset($_POST["reservations_email_to_user_admin_edited_msg"])){
			if(isset($_POST["reservations_email_to_user_admin_edited_check"])) $reservations_email_to_user_admin_edited_check = 1; else $reservations_email_to_user_admin_edited_check = 0;
			if(is_array($_POST["reservations_email_to_user_admin_edited_msg"])) $_POST["reservations_email_to_user_admin_edited_msg"] = implode($_POST["reservations_email_to_user_admin_edited_msg"]);
			$reservations_email_to_user_admin_edited = array(
				'msg' => stripslashes($_POST["reservations_email_to_user_admin_edited_msg"]),
				'subj' => stripslashes($_POST["reservations_email_to_user_admin_edited_subj"]),
				'active' => $reservations_email_to_user_admin_edited_check
			);
			update_option("reservations_email_to_user_admin_edited",$reservations_email_to_user_admin_edited);
		}
		$prompt = '<div class="updated"><p>'.__( 'Email settings saved' , 'easyReservations' ).'</p></div>';
	}

	if(isset($action) && $action == "reservations_form_settings"){ // Change a form
		$test = array();
		foreach(explode("<br>\r\n", $_POST['reservations_formvalue']) as $v){
			$test[] = str_replace('<br>', "<br>\r\n", $v);
		}
		$reservations_form_value = implode("<br>\r\n", $test);
		$reservations_form_value = str_replace(array('<br>', '</formtag>'), array("\n", ''), $reservations_form_value);
		$reservations_form_value = preg_replace('/<formtag.*?>/', '', $reservations_form_value);
		$reservations_form_value = html_entity_decode($reservations_form_value);
		$name = isset($_GET["form"]) ? $_GET["form"] : '';
		if($name == "") update_option("reservations_form", $reservations_form_value);
		else update_option('reservations_form_'.$name, $reservations_form_value);
		$prompt = '<div class="updated"><p>'.sprintf(__( 'Form%ssaved' , 'easyReservations' ), '<b> '.$name.' </b>' ).'</p></div>';
	}

	if(isset($action) && $action == "reservation_change_permissions"){ // Change a form
		if(current_user_can('manage_options')){
			$permissions = array('dashboard' => $_POST["easy-permission-dashboard"], 'resources' => $_POST["easy-permission-resources"], 'statistics' => $_POST["easy-permission-statistics"], 'settings' => $_POST["easy-permission-settings"]);
			update_option('reservations_main_permission', $permissions);
			$prompt = '<div class="updated"><p>'.__( 'Permissions changed' , 'easyReservations' ).'</p></div>';
		} else $prompt = '<div class="error"><p>'.__( 'Only admins can change the permissions for' , 'easyReservations' ).' easyReservations</p></div>';
	}

	if(isset($namtetodelete)){
		delete_option('reservations_form_'.$namtetodelete);
		$prompt = '<div class="updated"><p>'.sprintf(__( 'Form %s has been deleted' , 'easyReservations' ), '<b>'.$namtetodelete.'</b>' ).'</p></div>';
	}

	if(isset($action) && $action == "reservations_form_add"){
		if($_POST["formname"]!=""){
			$formname0='reservations_form_'.strtolower(str_replace(' ', '', $_POST["formname"]));
			$formname1=$formname0.'_1';
			$formname2=$formname0.'_2';

			if(get_option($formname0)=="") add_option(''.$formname0.'', ' ', '', 'no' );
			elseif(get_option($formname1)=="") add_option(''.$formname1.'', ' ', '', 'no');
			else add_option(''.$formname2.'', ' ', '', 'no');
			$prompt = '<div class="updated"><p>'.sprintf(__( 'Form %s has been added' , 'easyReservations' ), '<b>'.$_POST["formname"].'</b>' ).'</p></div>';
		} else $prompt = '<div class="error"><p>'.__( 'Please enter a name for the form' , 'easyReservations' ).'</p></div>';
	}

	if($setting_current_page == "form"){//Get current form Options
		$forms = '';
		$ifformcurrent='class="current"';

		$formresult = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM ".$wpdb->prefix ."options WHERE option_name like '%s' ", $wpdb->esc_like("reservations_form_") . '%'));
		foreach($formresult as $result){
			$formcutedname=str_replace('reservations_form_', '', $result->option_name);
			if($formcutedname !== ''){
				if(isset($_GET['form']) && $formcutedname == $_GET['form']) $class = ' class="curr"'; else $class = '';
				$link = 'admin.php?page=reservation-settings&site=form';
				$forms .= ' <li'.$class.'><a href="'.$link.'&form='.$formcutedname.'">'.$formcutedname.'</a> <a href="'.$link.'&deleteform='.$formcutedname.'"><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'images/delete.png"></a></li>';
			}
		}
	}
	do_action( 'er_set_save' );

	if($setting_current_page == "email") $ifemailcurrent='class="current"';
	echo '<h2>'.__( 'Reservations Settings' , 'easyReservations' ).'</h2>';
	if(isset($prompt)) echo $prompt; ?>
<div id="wrap">
<div class="tabs-box" style="width:99%">
	<ul class="tabs">
		<li><a <?php if(isset($ifgeneralcurrent)) echo $ifgeneralcurrent; ?> href="admin.php?page=reservation-settings"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/pref.png"> <?php printf ( __( 'General' , 'easyReservations' ));?></a></li>
		<li><a <?php if(isset($ifformcurrent)) echo $ifformcurrent; ?> href="admin.php?page=reservation-settings&site=form"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/form.png"> <?php printf ( __( 'Form' , 'easyReservations' ));?></a></li>
		<li><a <?php if($setting_current_page == 'custom') echo 'class="current"'; ?> href="admin.php?page=reservation-settings&site=custom">
				<img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/list.png"> <?php printf ( __( 'Custom' , 'easyReservations' ));?>
		</a></li>
		<li><a <?php if(isset($ifemailcurrent)) echo $ifemailcurrent; ?> href="admin.php?page=reservation-settings&site=email"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/email.png"> <?php printf ( __( 'Emails' , 'easyReservations' ));?></a></li>
		<?php do_action( 'er_set_tab_add' ); ?>
		<li><a <?php if(isset($ifaboutcurrent)) echo $ifaboutcurrent; ?> href="admin.php?page=reservation-settings&site=about"><img style="vertical-align:text-bottom ;" src="<?php echo RESERVATIONS_URL; ?>images/logo.png"> <?php printf ( __( 'About' , 'easyReservations' ));?></a></li>
	</ul>
</div>
<?php do_action( 'er_add_settings_top' );

if($setting_current_page == "general"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GENERAL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Get current Options
	$reservations_settings = get_option("reservations_settings");
	$reservations_currency = $reservations_settings['currency'];
	if(!is_array($reservations_currency)) $reservations_currency = array('sign' => $reservations_currency, 'place' => 0, 'whitespace' => 1, 'divider1' => '.', 'divider2' => ',', 'decimal' => 1);
	$reservations_date_format = $reservations_settings['date_format'];
	if(isset($reservations_settings['time_format'])) $reservations_time_format = $reservations_settings['time_format'];
	else $reservations_time_format = 'H:i';
	$easyReservationSyle=$reservations_settings['style'];
	$reservation_support_mail = get_option("reservations_support_mail");
	$reservations_regular_guests = get_option('reservations_regular_guests');
	$reservations_javascript = get_option('easyreservations_successful_script');
	$permission_options=get_option("reservations_main_permission");
	$reservations_uninstall=get_option("reservations_uninstall");
	if(isset($reservations_settings['mergeres']) && is_array($reservations_settings['mergeres'])){
		$blockbefore = $reservations_settings['mergeres']['blockbefore'];
		$blockafter = $reservations_settings['mergeres']['blockafter'];
		$reservations_settings['mergeres'] = $reservations_settings['mergeres']['merge'];
	} else {
		$blockbefore = 0;
		$blockafter = 0;
	}
	if(!isset($reservations_settings['tutorial'])) $reservations_settings['tutorial'] = 1;?>
<div id="ieerror" class="error" style="display:none;">
	<p>
		The administration part of easyReservations is not tested in Internet Explorer. Please use a mordern browser like Firefox, Chrome, Safari or Opera.
	</p>
</div>
<table cellspacing="0" style="width:99%">
	<tr cellspacing="0">
		<td style="width:70%;" valign="top" >
		<?php
			$currencys = array('#8364' => 'Euro','#36' => 'Dollar','#165' => 'Yen','#162' => 'Cent','#402' => 'Florin','#163' => 'Pound','#8356' => 'Lire','#20803' => 'Hongkong Dollar','#x20b8' => 'Tenge','#8365' => 'Laos Kip','#8353' => 'Colon','#8370' => 'Guarani','#70;&#116' => 'Hungary Forint','#8369' => 'Uruguay Peso','#8360' => 'Indian Rupee','#8377' => 'Indian Rupee 2nd','#2547' => 'Bengali Rupee','#2801' => 'Gujarati  Rupee','#3065' => 'Tamil Rupee','#3647' => 'Thai Baht','#6107' => 'Khmer Riel','#66;&#90;&#36' => 'Belize Dollar','#36;&#98' => 'Bolivia Boliviano','#75;&#77' => 'Bosnia and Herzegovina Marka', '#77;&#88;&#36;' => 'Mexican Pesos', '#80' => 'Botswana Pula','#1083;&#1074' => 'Bulgaria Lev','#6107' => 'Cambodia Riel','#20803' => 'China Yuan','#8371' => 'Austral','#8372' => 'Hryvnia','#81' => 'Guatemala Quetzal','#8373' => 'Cedi','#8366' => 'Tugril','#84;&#76' => 'Turkish Lira','#8367' => 'Drachma','#76' => 'Honduras Lempira','#8363' => 'Vietnam Dong','#8358' => 'Naira','#1084;&#1072;&#1085' => 'Azerbaijan New Manat','#83;&#71;&#68' => 'Singapore Dollar',	'#1076;&#1077;&#1085' => 'Macedonia Denar','#8366' => 'Mongolia Tughrik','#1547' => 'Afghanistan Afghani','#8354' => 'Cruzeiro','#65020' => 'Omani Rial','#65510' => 'Won','#82&#112;&#46' => 'Indonesian rupiah sign','#73&#68;&#82' => 'Indonesian rupiah ISO','#608' => 'Philippine Peso','#77;&#65;&#68' => 'Moroccan dirham','#80;&#104;&#11' => 'Philippine Peso 2nd','#82;&#36' => 'Brazilian Real','#66;&#82;&#76' => 'Brazilian Real BRL','#77;&#84' => 'Nicaragua Cordoba','#82;&#77' => 'Malaysia Ringgit','#1083;&#1074' => 'Kazakhstan Tenge','#74;&#36' => 'Jamaica Dollar','#75;&#269' => 'Czech Koruna','#107;&#114' => 'Danish Krone','#83;&#69;&#75' => 'Swedish Krone','#107;&#110' => 'Croatia Kuna','#122;&#322' => 'Polish Zloty','#8362' => 'Israeli Sheqel','#66;&#47;&#46' => 'Panamanian Balboa','#82;&#68;&#36' => 'Dominican Republic Peso','#78;&#79;&#75' => 'Norwegian Krone','#67;&#72;&#70' => 'Switzerland Franc','#108;&#101;&#105' => 'Romanian Leu', '#78;&#90;&#68' => 'New Zealand dollar', '#1088;&#1091' => 'Russian Rouble','#82' => 'South African ZAR','#67;&#79;&#80' => 'Colombian peso','#66;&#115;&#46' => 'Venezuelan Bolivares', '#76;&#84;&#76' => 'Lithuanian litas', '#67;&#36' => 'Canadian Dollar');
 			asort($currencys);
			$divider = array('.' => '.', ',' => ',', ' ' => 'whitespace', '' => '');
			$styles = array('widefat' =>__( 'Wordpress' , 'easyReservations' ),'greyfat' =>__( 'Grey' , 'easyReservations' ));
			if(file_exists(WP_PLUGIN_DIR . '/easyreservations/lib/modules/styles/admin/style_premium.css')) $styles['premium'] = __( 'Premium' , 'easyReservations' );
			$date_formats = array('Y/m/d' => date('Y/m/d'),'Y-m-d' => date('Y-m-d'),'m/d/Y' => date('m/d/Y'),'d-m-Y' => date('d-m-Y'),'d.m.Y' => date('d.m.Y'));
			$time_formats = array('H:i' => date('H:i',time()),'h:i a' => date('h:i a'));
			if(isset($reservations_settings['mergeres']) && $reservations_settings['mergeres'] > 0) $thenum = $reservations_settings['mergeres'];
			else $thenum = 0;
			$hours = easyreservations_interval_infos(3600, 0, 2);
			$days = easyreservations_interval_infos(86400, 0, 2);
			$minutes = __('minutes', 'easyReservations');
			$timearray = array(0 => '0 '.$minutes,5 => '5 '.$minutes,10 =>'10 '. $minutes,15 => '15 '.$minutes,30 => '30 '. $minutes,45 =>'45 '. $minutes,60=>'1 '.easyreservations_interval_infos(3600,0,1),90=>'1.5 '.$hours,120=>'2 '.$hours,150=>'2.5 '.$hours,180=>'3 '.$hours,240=>'4 '.$hours,300=>'5 '.$hours,360=>'6 '.$hours,600=>'10 '.$hours,720=>'12 '.$hours,1080=>'18 '.$hours,1440=>'1 '.easyreservations_interval_infos(86400,0,1),2160=>'1.5 '.$days,2880=>'2 '.$days,4320=>'3 '.$days,5760=>'4 '.$days,7200=>'5 '.$days,8640=>'6 '.$days,10080=>'7 '.$days,20160=>'14 '.$days,40320=>'1 '.easyreservations_interval_infos(2592000,0,1));

			$rows = array(
				'<img src="'.RESERVATIONS_URL.'images/email.png"> <b>'.__( 'Support email', 'easyReservations' ).'</b>' => '<input type="text" name="reservations_support_mail" value="'.$reservation_support_mail.'" style="width:50%">',
				'<img src="'.RESERVATIONS_URL.'images/dollar.png"> <b>'.__( 'Money format', 'easyReservations' ).'</b>' => array('currency_settings',easyreservations_generate_input_select('reservations_currency', $currencys, $reservations_currency['sign'], '', true).' '.easyreservations_generate_input_select('reservations_currency_place', array(__( 'after' , 'easyReservations' ),__( 'before' , 'easyReservations' )), $reservations_currency['place']).' '.__( 'Price' , 'easyReservations' ).' <input type="checkbox" name="reservations_currency_whitespace" '.checked($reservations_currency['whitespace'],1,false).'> '.__( 'Whitespace between price and currency sign' , 'easyReservations' ).'<br>'.__( 'Th. seperator' , 'easyReservations' ).': '.easyreservations_generate_input_select('reservations_currency_divider1', $divider, $reservations_currency['divider1']).' '.__( 'Dec. seperator' , 'easyReservations' ).': '.easyreservations_generate_input_select('reservations_currency_divider2', $divider, $reservations_currency['divider2']).' '.easyreservations_generate_input_select('reservations_currency_decimal',array('1' =>__('show decimals','easyReservations'), '0' =>  __( 'round' , 'easyReservations' )), $reservations_currency['decimal']).' '.__( 'Example' , 'easyReservations' ).':</strong> <span id="reservations_currency_example"></span>'),
				'<img src="'.RESERVATIONS_URL.'images/day.png"> <b>'.__( 'Date format', 'easyReservations' ).'</b>' => easyreservations_generate_input_select('reservations_date_format',$date_formats,$reservations_date_format).' '.easyreservations_generate_input_select('reservations_time_format',$time_formats,$reservations_time_format),
				'<img src="'.RESERVATIONS_URL.'images/clock.png"> <b>'.__( 'Time', 'easyReservations' ).'</b>' => '<input type="checkbox" name="reservations_time" '.checked($reservations_settings['time'],1,false).'> '.__( 'Enable display of time and that the time gets used to calculate the billing units instead of only the date', 'easyReservations' ),
				'<img src="'.RESERVATIONS_URL.'images/background.png"> <b>'.__( 'Admin Style', 'easyReservations' ).'</b>' => easyreservations_generate_input_select('reservations_style',$styles,$easyReservationSyle),
				'<img src="'.RESERVATIONS_URL.'images/house.png"> <b>'.__( 'Merge resources', 'easyReservations' ).'</b>' => '<input type="checkbox" id="checkmerge" name="reservations_resourcemerge_box" value="1" '.((isset($reservations_settings['mergeres']) && $reservations_settings['mergeres'] > 0) ? 'checked="checked"' : '').'> '.sprintf(__( 'Only allow %s reservations at the same time in all resources regardless of the resource counts' , 'easyReservations' ), '<select name="reservations_resourcemerge" onclick="document.getElementById(\'checkmerge\').checked = true;">'.easyreservations_num_options(0,99,$thenum).'</select>'),
				'<img src="'.RESERVATIONS_URL.'images/lock.png"> <b>'.__( 'Block time', 'easyReservations' ).'</b>' => __( 'Block' , 'easyReservations' ).' '.easyreservations_generate_input_select('blockbefore', $timearray, $blockbefore).' '.__( 'before and' , 'easyReservations' ).' '.easyreservations_generate_input_select('blockafter', $timearray, $blockafter).' '.__( 'after reservations' , 'easyReservations' ),
				'<img src="'.RESERVATIONS_URL.'images/help.png"> <b>'.__( 'Tutorial', 'easyReservations' ).'</b>' => '<input type="checkbox" name="reservations_tutorial" value="1" '.checked($reservations_settings['tutorial'],1,false).'> '.__( 'Enable tutorial mode' , 'easyReservations' ).' <a class="button" href="admin.php?page=reservation-settings&tutorial_histoy=0"> '.__( 'Reset' , 'easyReservations' ).'</a>',
				'<img src="'.RESERVATIONS_URL.'images/database.png"> <b>'.__( 'Uninstall', 'easyReservations' ).'</b>' => '<input type="checkbox" name="reservations_uninstall" value="1" '.checked($reservations_uninstall, 1,false).'> '.__( 'Delete settings, reservations and resources' , 'easyReservations' ),
			);
			$rows = apply_filters('er_add_set_main_table_row', $rows);
			$rows['<img src="'.RESERVATIONS_URL.'css/images/star_full.png"> <b>'.__( 'Important guests', 'easyReservations' ).'</b>'] =  '<i>'.__( 'Enter emails of important guests; saperated by comma. Reservations with this email will be highlighted.' , 'easyReservations' ).'</i><textarea name="regular_guests" style="width:100%;height:80px;margin-top:5px;">'.$reservations_regular_guests.'</textarea>';
			$rows['<img src="'.RESERVATIONS_URL.'images/lightning.png"> <b>'.__( 'Execute scripts', 'easyReservations' ).'</b>'] =  '<i>'.__( 'After successful reservation.' , 'easyReservations' ).'</i><textarea name="javascript" style="width:100%;height:100px;margin-top:5px;">'.stripslashes($reservations_javascript).'</textarea>';
			$table = easyreservations_generate_table('reservation_main_settings_table', __( 'General Settings', 'easyReservations' ).'<input type="submit" value="'. __( 'Save Changes' , 'easyReservations' ).'" onclick="document.getElementById(\'er_main_set\').submit(); return false;" class="easybutton button-primary" style="float:right" >', $rows);
			echo easyreservations_generate_form('er_main_set', 'admin.php?page=reservation-settings', 'post', false, array('easy-set-main' => wp_create_nonce('easy-set-main')), $table);
			 do_action( 'er_set_main_out' ); ?>
			</td><td style="width:1%;" valign="top">
			</td><td style="width:29%;" valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-bottom:7px;" cellspacing="0" cellpadding="0" style="background:#fff;">
					<thead>
						<tr>
							<th colspan="2"> <?php printf ( __( 'Status' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="font-weight:bold;padding:10px;text-align:center"><span style="width:20%;display: inline-block">Version: <?php echo RESERVATIONS_VERSION; ?></span><span style="width:30%;display: inline-block">Last update: 23.09.2017</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
						</tr>
						<tr class="alternate">
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/" target="_blank" id="iddocumentation"><?php echo __( 'Documentation' , 'easyReservations' );?></a></td>
						</tr>
						<tr>
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/bug-reports/" target="_blank" id="idbugreport"><?php echo __( 'Report bug' , 'easyReservations' );?></a></td>
						</tr>
						<tr class="alternate"> 
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/premium/" target="_blank" id="idpremium"><?php echo __( 'Premium' , 'easyReservations' );?></a></td>
						</tr>
						<tr>
							<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/" target="_blank" id="idrate"><?php echo __( 'Rate the Plugin' , 'easyReservations' );?>, please!</a></td>
						</tr>
					</tbody>
				</table>
				<?php do_action( 'er_set_main_side_top' ); ?>
				<form method="post" action="admin.php?page=reservation-settings" id="reservation_clean_database">
					<input type="hidden" name="action" value="reservation_clean_database" id="reservation_clean_database">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th> <?php printf ( __( 'Clean Database' , 'easyReservations' ));?><input type="button" onclick="document.getElementById('reservation_clean_database').submit(); return false;" style="float:right;" title="" class="button" value="<?php printf ( __( 'Clean' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/database.png"> <?php printf ( __( 'Delete all unapproved old reservations' , 'easyReservations' ));?>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
				<form method="post" action="admin.php?page=reservation-settings" id="reservation_change_permissions">
					<input type="hidden" name="action" value="reservation_change_permissions" id="reservation_change_permissions">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-top:7px;" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th colspan="2" id="idpermission"> <?php printf ( __( 'Change Permissions' , 'easyReservations' ));?><input type="submit" onclick="document.getElementById('reservation_change_permissions').submit(); return false;" class="easybutton button-primary" style="float:right;" value="<?php printf ( __( 'Set' , 'easyReservations' ));?>"></th>
							</tr>
						</thead>
						<tbody>
								<tr class="alternate">
									<td><?php echo __( 'Dashboard' , 'easyReservations' );?></td>
									<td><select name="easy-permission-dashboard" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['dashboard']); ?></select></td>
								</tr>
								<tr>
									<td><?php printf ( __( 'Resources' , 'easyReservations' ));?></td>
									<td><select name="easy-permission-resources" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['resources']); ?></select></td>
								</tr>
								<tr class="alternate">
									<td><?php printf ( __( 'Statistics' , 'easyReservations' ));?></td>
									<td><select name="easy-permission-statistics" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['statistics']); ?></select></td>
								</tr>
								<tr valign="top">
									<td><?php printf ( __( 'Settings' , 'easyReservations' ));?></td>
									<td><select name="easy-permission-settings" style="float:right"><?php echo easyreservations_get_roles_options($permission_options['settings']); ?></select></td>
								</tr>
						</tbody>
					</table>
				</form>
				<?php do_action( 'er_set_main_side_out' ); ?>
		</td>
	</tr>
</table>
<script type="text/javascript">
	function easyreservations_currency_example(){
		var divider1 = jQuery('select[name=reservations_currency_divider1]').val();
		var divider2 = jQuery('select[name=reservations_currency_divider2]').val();
		var decimal = jQuery('select[name=reservations_currency_decimal]').val();
		var place = jQuery('select[name=reservations_currency_place]').val();
		var sign = jQuery('select[name=reservations_currency]').val();

		var price = 54+divider1;
		if(decimal == 1) price+= 847+divider2+99;
		else price += 848;
		if(place == 0){
			if(jQuery('input[name=reservations_currency_whitespace]').is(":checked")) price+= ' ';
			price += '&'+sign+';';
		} else {
			var white = '';
			if(jQuery('input[name=reservations_currency_whitespace]').is(":checked")) white = ' ';
			price = '&'+sign+';'+white+price;
		}
		jQuery('#reservations_currency_example').html(price);
	}
	jQuery('#currency_settings input,#currency_settings select').bind('change',function(){
		easyreservations_currency_example();
	});
	easyreservations_currency_example();
</script><?php
} elseif($setting_current_page == "form"){
	if(isset($_GET["form"])){
		$formnameget = $_GET['form'];
		$reservations_form = get_option("reservations_form_".$formnameget);
	} else {
		$formnameget = '';
		$reservations_form = get_option("reservations_form");
	}
	$custom_fields = get_option('reservations_custom_fields');
	$custom_fields_array = array();
	if($custom_fields){
		foreach($custom_fields['fields'] as $id => $custom){
			$custom_fields_array[$id] = $custom['title'];
		}
	} ?>
		<div class="formnavigation" style="width:99%;height: 29px">
			<ul class="navtabs">
			<?php if($formnameget == ""){ ?>
	      <li class="curr"><a href="admin.php?page=reservation-settings&site=form"><?php printf ( __( 'Standard' , 'easyReservations' ));?></a></li>
			<?php } else { ?>
	      <li><a href="admin.php?page=reservation-settings&site=form"><?php printf ( _e( 'Standard' , 'easyReservations' ));?></a></li>
			<?php } echo $forms; ?>
	    </ul>
	    <div style="float:right">
	      <form method="post" action="admin.php?page=reservation-settings&site=form" id="reservations_form_add">
	        <input type="hidden" name="action" value="reservations_form_add">
	        <input name="formname" type="text" style="width:200px;height:25px;">
	        <input type="button" onclick="document.getElementById('reservations_form_add').submit(); return false;" class="easybutton button-primary" value="<?php echo __( 'Add' , 'easyReservations' );?>">
	      </form>
	    </div>
		</div>
		<script type="text/javascript">
		function submitForm(){
			jQuery('*[name="reservations_formvalue"]').val(jQuery('#formcontainer').html());
			jQuery('#easyform').submit();
		}

		function generateHiddenOptions(tag){
			var value = '<h4><?php echo addslashes(__( 'Type' , 'easyReservations' ));?></h4><p><select id="hiddentype" name="1" onchange="changeHiddenOption()">';
      jQuery.each({
        xxx: "<?php echo addslashes(__( 'Type' , 'easyReservations' ));?>",
        resource: "<?php echo addslashes(__( 'Resource' , 'easyReservations' ));?>",
        from: "<?php echo addslashes(__( 'Arrival date' , 'easyReservations' ));?>",
        "date-from-hour": "<?php echo addslashes(__( 'Arrival hour' , 'easyReservations' ));?>",
        "date-from-min": "<?php echo addslashes(__( 'Arrival minute' , 'easyReservations' ));?>",
        to: "<?php echo addslashes(__( 'Departure date' , 'easyReservations' ));?>",
        "date-to-hour": "<?php echo addslashes(__( 'Departure hour' , 'easyReservations' ));?>",
        "date-to-min": "<?php echo addslashes(__( 'Departure minute' , 'easyReservations' ));?>",
        units: "<?php echo addslashes(__( 'Billing units' , 'easyReservations' ));?>",
        adults: "<?php echo addslashes(__( 'Adults' , 'easyReservations' ));?>",
        childs: "<?php echo addslashes(__( 'Children' , 'easyReservations' ));?>"
      }, function(ok,ov){
        var selected = '';
        if(tag && tag[1] == ok) selected = 'selected="selected"';
        value += '<option value="'+ok+'" '+selected+'>'+ov+'</option>';
      });
      value += '</select></p><span id="the_hidden_value">';
			if(tag) value += changeHiddenOption(tag, tag[1]);
      value += '</span>';
			return value;
    }

    function changeHiddenOption(tag,typ){
			if(typ) var type = typ;
			else var type = jQuery('#hiddentype').val();
		  var field = false;
		  if(type == 'resource'){
	      if(!tag || !tag[2]) tag = {2:''}
	      field = generateResourceSelect(tag[2],'2');
		  } else if(type == "from" || type == "to"){
        if(!tag || !tag[2]) tag = {2:'<?php echo RESERVATIONS_DATE_FORMAT; ?>'}
			  field = '<input type="text" name="2" value="'+tag[2]+'">'
		  } else if(type == "date-from-hour" || type == "date-to-hour"){
        if(!tag || !tag[2]) tag = {2:12}
        field = '<select name="2">'+generateOptions('0-23',tag[2])+'</select>'
		  } else if(type == "date-from-min" || type == "date-to-min"){
        if(!tag || !tag[2]) tag = {2:30}
        field = '<select name="2">'+generateOptions('0-59',tag[2])+'</select>'
		  } else if(type == "adults" || type == "units"){
        if(!tag || !tag[2]) tag = {2:2}
        field = '<select name="2">'+generateOptions('1-100',tag[2])+'</select>'
		  } else if(type == "childs"){
        if(!tag || !tag[2]) tag = {2:1}
        field = '<select name="2">'+generateOptions('0-100',tag[2])+'</select>'
		  }
      if(field){
        field = '<h4><?php echo addslashes(__( 'Value' , 'easyReservations' ));?></h4><p>'+field+'</p>'
			  if(typ) return field;
			  else jQuery('#the_hidden_value').html(field);
      }
		}

		function resourceSelect(tag){
			if(!tag) tag = {value:''};
			else if(!tag['value']) tag['value'] = '';
			return generateResourceSelect(tag['value'],'value');
		}

		function generateResourceSelect(sel,name){
			var resources = <?php easyreservations_load_resources(); global $the_rooms_array; echo str_replace('\\"', '"', addslashes(json_encode($the_rooms_array))); ?>;
			var value = '<select name="'+name+'">';
			jQuery.each(resources, function(k,v){
        var selected = '';
        if(sel && sel == k) selected = 'selected="selected"';
        value += '<option value="'+k+'" '+selected+'>'+v['post_title']+'</option>';
			});
			return value+'</select>';
		}

		function generateInfoboxImage(tag){
			var checked = '', img_y = '100', img_x = '100';
			if(tag && tag['img'] && tag['img'] == 'yes') checked = ' checked="checked"';
      if(tag && tag['img_y']) img_y = tag['img_y'];
      if(tag && tag['img_x']) img_x = tag['img_x'];
      var value = '<input type="checkbox" name="img" value="yes"'+checked+'> <?php echo addslashes(__( 'Display featured image' , 'easyReservations' ));?><br>';
			value += '<?php echo addslashes(__( 'Width' , 'easyReservations' ));?>: <input type="text" name="img_y" class="not" value="'+img_y+'" style="width:100px"> px<br>';
			return value + '<?php echo addslashes(__( 'Height' , 'easyReservations' ));?>: <input type="text" name="img_x" class="not" value="'+img_x+'" style="width:100px"> px';
		}

		function generateInfoboxContent(tag){
			var checked = '';
			if(tag && tag['content']) checked = ' checked="checked"';
			else if(tag) tag['content'] = 400;
      else tag = {content: 400};
			return '<input type="checkbox" name="content" class="not" value="yes"'+checked+'> <?php echo addslashes(__( 'Display content with' , 'easyReservations' )); ?> <input type="text" name="content_value" class="not" value="'+tag['content']+'" style="width:80px"> <?php echo addslashes(__( 'characters' , 'easyReservations' )); ?>';
		}

		function generateInfoboxExcerpt(tag){
      var checked = '';
      if(tag && tag['excerpt']) checked = ' checked="checked"';
      else if(tag) tag['excerpt'] = 400;
			else tag = {excerpt: 400};
      return '<input type="checkbox" name="excerpt" class="not" value="yes"'+checked+'> <?php echo addslashes(__( 'Display excerpt with' , 'easyReservations' )); ?> <input type="text" name="excerpt_value" class="not" value="'+tag['excerpt']+'" style="width:80px"> <?php echo addslashes(__( 'characters' , 'easyReservations' )); ?>';
		}

		function generateInfobox(){
			var tag = '';
			if(jQuery('*[name="img"]').attr('checked')) tag += 'img_y="'+jQuery('*[name="img_y"]').val()+'" img_x="'+jQuery('*[name="img_x"]').val()+'" ';
			if(jQuery('*[name="content"]').attr('checked')) tag += 'content="'+jQuery('*[name="content_value"]').val()+'" ';
			if(jQuery('*[name="excerpt"]').attr('checked')) tag += 'excerpt="'+jQuery('*[name="excerpt_value"]').val()+'" ';
			return tag;
		}

		function customRequired(tag){
			var sel = '', checked = '';
			if(tag && tag[Object.keys(tag)[Object.keys(tag).length - 1]]) sel = tag[Object.keys(tag)[Object.keys(tag).length - 1]];
      if(sel == '*') checked = ' checked="checked"';
			var value = '<input type="checkbox" name="*" value="*"'+checked+'> <?php echo addslashes(__( 'Required' , 'easyReservations' )); ?><br>';
			return value;
		}

		function priceCheckBoxes(tag){
			var sel = '';
			if(tag && tag[Object.keys(tag)[Object.keys(tag).length - 1]]) sel = tag[Object.keys(tag)[Object.keys(tag).length - 1]];
      var checked = '';
      if(sel == 'pn' || sel == 'pb') checked = ' checked="checked"';
			var value = '<input type="checkbox" name="pn" class="not" value="yes"'+checked+'> <?php echo addslashes(__( 'Price per billing unit' , 'easyReservations' )); ?><br>';
			checked = '';
      if(sel == 'pp' || sel == 'pb') checked = ' checked="checked"';
      value += '<input type="checkbox" name="pp" class="not" value="yes"'+checked+'> <?php echo addslashes(__( 'Price per person' , 'easyReservations' )); ?>';
			return value;
    }

		function generatePrice(){
			var tag = '';
      if(jQuery('*[name="pn"]').attr('checked')) tag = 'pn ';
			if(jQuery('*[name="pp"]').attr('checked')){
					if(tag != '') tag = 'pb ';
					else tag = 'pp ';
			}
			return tag;
		}

    var style = {
	        title: '<?php echo addslashes(__( 'Style' , 'easyReservations' ));?>',
	        input: 'text'
	      },
	      title = {
	          title: '<?php echo addslashes(__( 'Title' , 'easyReservations' ));?>',
	          input: 'text'
	      },
	      maxlength = {
	          title: '<?php echo addslashes(__( 'Max-length' , 'easyReservations' ));?>',
	          input: 'select',
	          options: '0-100',
	          default: 50
	      },
	      disabled = {
	          title: '<?php echo addslashes(__( 'Disabled' , 'easyReservations' ));?>',
	          input: 'check',
	          default: 'disabled'
	      },
	      fields = {
	          error: {
	              name: '<?php echo addslashes(__( 'Errors' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Shows the warning messages in form. Is required for the multiple reservations form function.' , 'easyReservations' ));?>',
	              options: {
	                  error_title: {
	                      title: '<?php echo addslashes(__( 'Title' , 'easyReservations' ));?>',
	                      input: 'text',
	                      default: 'Errors found in the form'
	                  },
	                  error_message: {
	                      title: '<?php echo addslashes(__( 'Message' , 'easyReservations' ));?>',
	                      input: 'textarea',
	                      default: 'There is a problem with the form, please check and correct the following:'
	                  },
	                  style: style,
	                  title: title
	              }
	          },
	          "date-from": {
	              name: '<?php echo addslashes(__( 'Arrival date' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Field with datepicker for the arrival date. Is required in any form.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Value' , 'easyReservations' ));?>',
	                      input: 'text',
	                      default: '+14'
	                  },
	                  maxlength: maxlength,
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          "date-to": {
	              name: '<?php echo addslashes(__( 'Departure date' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Field with datepicker for the departure date. Can be replaced by billing units selection or deleted so that every reservation lasts one billing unit.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Value' , 'easyReservations' ));?>',
	                      input: 'text',
	                      default: '+21'
	                  },
	                  maxlength: maxlength,
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          "date-from-hour": {
	              name: '<?php echo addslashes(__( 'Arrival hour' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Select for arrival hour. Can be replaced by a hidden field and defaults to 12:00 if not in form.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '0-23',
	                      default: '12'
	                  },
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          "date-to-hour": {
	              name: '<?php echo addslashes(__( 'Departure hour' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Select for departure hour. Can be replaced by a hidden field and defaults to 12:00 if not in form.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '0-23',
	                      default: '12'
	                  },
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          "date-from-min": {
	              name: '<?php echo addslashes(__( 'Arrival minute' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Select for arrival minute.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '0-59',
	                      default: '0'
	                  },
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          "date-to-min": {
              name: '<?php echo addslashes(__( 'Departure minute' , 'easyReservations' ));?>',
              desc: '<?php echo addslashes(__( 'Select for departure minute.' , 'easyReservations' ));?>',
              options: {
                value: {
                    title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
                    input: 'select',
                    options: '0-59',
                    default: '0'
                },
                style: style,
                title: title,
                disabled:disabled
              }
	          },
	          units: {
              name: '<?php echo addslashes(__( 'Billing units' , 'easyReservations' ));?>',
              desc: '<?php echo addslashes(__( 'Select of billing units to define the length of stay. Can be replaced by depature date field or defaults to one billing unit if not in form.' , 'easyReservations' ));?>',
              options: {
                1: {
                  title: '<?php echo addslashes(__( 'Min' , 'easyReservations' ));?>',
                  input: 'select',
                  options: '1-100',
                  default: '1'
                },
                2: {
                  title: '<?php echo addslashes(__( 'Max' , 'easyReservations' ));?>',
                  input: 'select',
                  options: '1-100',
                  default: '10'
                },
                value: {
                    title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
                    input: 'select',
                    options: '1-100',
                    default: '7'
                },
                style: style,
                title: title,
                disabled:disabled
              }
	          },
	          resources: {
	              name: '<?php echo addslashes(__( 'Resources' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Select of resources. Is required and can only be replaced by hidden field. You can exclude resources with comma saperated IDs.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: resourceSelect

	                  },
	                  exclude: {
	                      title: '<?php echo addslashes(__( 'Exclude' , 'easyReservations' ));?>',
	                      input: 'text',
	                      default: ''
	                  },
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          adults: {
	              name: '<?php echo addslashes(__( 'Adults' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Select of adults. Is required and can only be replaced by hidden field.' , 'easyReservations' ));?>',
	              options: {
	                  1: {
	                      title: '<?php echo addslashes(__( 'Min' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '1-100',
	                      default: '1'
	                  },
	                  2: {
	                      title: '<?php echo addslashes(__( 'Max' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '1-100',
	                      default: '10'
	                  },
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '1-100',
	                      default: '3'
	                  },
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          childs: {
	              name: '<?php echo addslashes(__( 'Children\'s' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Select of children\'s. Can be replaced by hidden field or deleted.' , 'easyReservations' ));?>',
	              options: {
	                  1: {
	                      title: '<?php echo addslashes(__( 'Min' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '0-100',
	                      default: '0'
	                  },
	                  2: {
	                      title: '<?php echo addslashes(__( 'Max' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '1-100',
	                      default: '10'
	                  },
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: '0-100',
	                      default: '0'
	                  },
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          thename: {
	              name: '<?php echo addslashes(__( 'Name' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Text field for name. Is required in any form.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Value' , 'easyReservations' ));?>',
	                      input: 'text',
	                      default: ''
	                  },
	                  maxlength: maxlength,
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          email: {
	              name: '<?php echo addslashes(__( 'Email' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Text field for email. Is required in any form.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Value' , 'easyReservations' ));?>',
	                      input: 'text',
	                      default: ''
	                  },
	                  maxlength: maxlength,
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          country: {
	              name: '<?php echo addslashes(__( 'Country' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Text field for email. Is required in any form.' , 'easyReservations' ));?>',
	              options: {
	                  value: {
	                      title: '<?php echo addslashes(__( 'Selected' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: <?php echo str_replace('\\"', '"', addslashes(json_encode(easyReservations_country_array()))); ?>,
	                      default: 'US'
	                  },
	                  maxlength: maxlength,
	                  style: style,
	                  title: title,
	                  disabled:disabled
	              }
	          },
	          hidden: {
	              name: '<?php echo addslashes(__( 'Hidden' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Date and information fields can be replaced by hidden fields to force the selection without the guest choosing or seeing it. They are helpful for special offers or forms for just one resource.' , 'easyReservations' ));?>',
	              options: generateHiddenOptions
	          },
	          infobox: {
	              name: '<?php echo addslashes(__( 'Resources infobox' , 'easyReservations' ));?>',
	              desc: '<?php echo addslashes(__( 'Shows the information title, image, content and excerpt of the currently selected resource.' , 'easyReservations' ));?>',
	              generate: generateInfobox,
			          options: {
	                  theme: {
	                      title: '<?php echo addslashes(__( 'Size' , 'easyReservations' ));?>',
	                      input: 'select',
	                      options: {big:"<?php echo addslashes(__( 'Big' , 'easyReservations' ));?>", medium:"<?php echo addslashes(__( 'Medium' , 'easyReservations' ));?>"},
	                      default: 'big'
	                  },
	                  img: {
	                      title: '<?php echo addslashes(__( 'Featured image' , 'easyReservations' ));?>',
	                      input: generateInfoboxImage
	                  },
	                  title: {
	                      title: '<?php echo addslashes(__( 'Show title' , 'easyReservations' ));?>',
	                      input: 'check',
		                    default: 'yes'
	                  },
	                  content: {
	                      title: '<?php echo addslashes(__( 'Content' , 'easyReservations' ));?>',
	                      input: generateInfoboxContent
	                  },
                    excerpt: {
	                      title: '<?php echo addslashes(__( 'Excerpt' , 'easyReservations' ));?>',
	                      input: generateInfoboxExcerpt
	                  }
	              }
	          },
			      captcha: {
                name: '<?php echo addslashes(__( 'Captcha' , 'easyReservations' ));?>',
                desc: '<?php echo addslashes(__( 'Text field for email. Is required in any form.' , 'easyReservations' ));?>',
					      options: {
							      color: {
                        title: '<?php echo addslashes(__( 'Color of code' , 'easyReservations' ));?>',
                        input: 'select',
												options: {black: "<?php echo addslashes(__( 'Black' , 'easyReservations' ));?>", white: "<?php echo addslashes(__( 'White' , 'easyReservations' ));?>"},
                        default: 'black'
							      },
                    style: style,
                    title: title
                }
			      },
			      "show_price": {
                name: '<?php echo addslashes(__( 'display price' , 'easyReservations' ));?>',
                desc: '<?php echo addslashes(__( 'Shows the price as of selections.' , 'easyReservations' ));?>',
                options: {
                    before: {
                        title: '<?php echo addslashes(__( 'Text before price' , 'easyReservations' ));?>',
                        input: 'text',
                        default: 'Price:'
                    },
                    style: style,
                    title: title
                }
			      },
            submit: {
                name: '<?php echo addslashes(__( 'Submit' , 'easyReservations' ));?>',
                desc: '<?php echo addslashes(__( 'Button to submit the form.' , 'easyReservations' ));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__( 'Value' , 'easyReservations' ));?>',
                        input: 'text',
                        default: 'Submit'
                    },
                    style: style,
                    title: title
                }
            },
			      custom: {
              name: '<?php echo addslashes(__( 'Custom' , 'easyReservations' ));?>',
              desc: '<?php echo addslashes(sprintf( __( 'Can be any form element, can have an impact on the price and are used to get more information. Define them %s first' , 'easyReservations' ), '<a href="admin.php?page=reservation-settings&site=custom">here</a>'));?>',
							options: {
								id: {
                    title: '<?php echo addslashes(__( 'Select field' , 'easyReservations' ));?>',
                    input: 'select',
                    options: <?php if(!isset($custom_fields_array)) $custom_fields_array = array(); echo json_encode($custom_fields_array);?>
								},
                style: style,
                title: title
              }
            }
	      };
			<?php do_action('easy-form-js-before'); ?>
	</script><?php
	$new_form = '';
	foreach(explode("\r\n", ($reservations_form)) as $v){
		$new_form .= nl2br(htmlspecialchars($v, ENT_COMPAT));
	}
	$tags = easyreservations_shortcode_parser($new_form, true);
	foreach($tags as &$v){
		$explode = explode(' ', $v);
		$new_form = str_replace('['.$v.']', '<formtag attr="'.$explode[0].'">['.$v.']</formtag>', $new_form);
	}
	wp_enqueue_script('jquery-ui-accordion');
	wp_enqueue_script('form-editor', RESERVATIONS_URL.'js/functions/form.editor.js');
	$textfield = '<div id="formcontainer" style="min-height:500px;width:63%;float:left;background:#fff;border:1px solid #CCC;padding:4px;font-size:13px;font-family: Consolas, Monaco, monospace" contenteditable="true">';
	$textfield .= stripslashes($new_form);
	$textfield .= '</div>';

	$accordion = '<div id="accordion_container">';
		$accordion .= '<div id="accordion">';
			$accordion .= '<h3>'.__('Date fields','easyReservations').'</h3>';
			$accordion .= '<div class="table">';
				$accordion .= '<table class="formtable">';
					$accordion .= '<thead>';
						$accordion .= '<tr>';
							$accordion .= '<th></th>';
							$accordion .= '<th>'.__('Type','easyReservations').'</th>';
							$accordion .= '<th>'.__('Default','easyReservations').'</th>';
						$accordion .= '</tr>';
					$accordion .= '</thead>';
					$accordion .= '<tbody>';
						$accordion .= '<tr attr="date-from">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/day.png);"></td>';
							$accordion .= '<td><strong>'.__('Arrival date','easyReservations').'</strong><br><i>'.__('Text field with datepicker','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="date-from-hour">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/clock.png);"></td>';
							$accordion .= '<td><strong>'.__('Arrival hour','easyReservations').'</strong><br><i>'.__('Select field as of the time pattern selection','easyReservations').'</i></td>';
							$accordion .= '<td>12</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="date-from-min">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/hour.png);"></td>';
							$accordion .= '<td><strong>'.__('Arrival minute','easyReservations').'</strong><br><i>'.__('Select field','easyReservations').' 00-59</i></td>';
							$accordion .= '<td>12</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="date-to">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/day.png);"></td>';
							$accordion .= '<td><strong>'.__('Departure date','easyReservations').'</strong><br><i>'.__('Text field with datepicker','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="units">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/units.png);"></td>';
							$accordion .= '<td><strong>'.__('Billing units','easyReservations').'</strong><br><i>'.__('Select field to choose length of stay','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="date-to-hour">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/clock.png);"></td>';
							$accordion .= '<td><strong>'.__('Departure hour','easyReservations').'</strong><br><i>'.__('Select field as of the time pattern selection','easyReservations').'</i></td>';
							$accordion .= '<td>12</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="date-to-min">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/hour.png);"></td>';
							$accordion .= '<td><strong>'.__('Departure minute','easyReservations').'</strong><br><i>'.__('Select field','easyReservations').' 00-59</i></td>';
							$accordion .= '<td>12</td>';
						$accordion .= '</tr>';
					$accordion .= '</tbody>';
				$accordion .= '</table>';
			$accordion .= '</div>';
			$accordion .= '<h3>'.__('Information fields','easyReservations').'</h3>';
			$accordion .= '<div class="table">';
				$accordion .= '<table class="formtable">';
					$accordion .= '<thead>';
						$accordion .= '<tr>';
							$accordion .= '<th></th>';
							$accordion .= '<th>'.__('Type','easyReservations').'</th>';
							$accordion .= '<th>'.__('Default','easyReservations').'</th>';
						$accordion .= '</tr>';
					$accordion .= '</thead>';
					$accordion .= '<tbody>';
						$accordion .= '<tr attr="resources">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/house.png);"></td>';
							$accordion .= '<td><strong>'.__('Resources','easyReservations').'</strong><br><i>'.__('Select of resource','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="adults">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/user.png);"></td>';
							$accordion .= '<td><strong>'.__('Adults','easyReservations').'</strong><br><i>'.__('Select field for adults','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="childs">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/persons.png);"></td>';
							$accordion .= '<td><strong>'.__('Children','easyReservations').'</strong><br><i>'.__('Select field for children\'s','easyReservations').'</i></td>';
							$accordion .= '<td>0</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="thename">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/day.png);"></td>';
							$accordion .= '<td><strong>'.__('Name','easyReservations').'<br><i></strong>'.__('Text field for name','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="email">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/email.png);"></td>';
							$accordion .= '<td><strong>'.__('Email','easyReservations').'</strong><br><i>'.__('Text field for mail','easyReservations').'</i></td>';
							$accordion .= '<td>&#10008;</td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="country">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/country.png);"></td>';
							$accordion .= '<td><strong>'.__('Country','easyReservations').'</strong><br><i>'.__('Select field of country\'s','easyReservations').'</i></td>';
							$accordion .= '<td></td>';
						$accordion .= '</tr>';
					$accordion .= '</tbody>';
				$accordion .= '</table>';
			$accordion .= '</div>';
			$accordion .= '<h3>'.__('Special fields','easyReservations').'</h3>';
			$accordion .= '<div class="table">';
				$accordion .= '<table class="formtable">';
					$accordion .= '<thead>';
						$accordion .= '<tr>';
							$accordion .= '<th></th>';
							$accordion .= '<th>'.__('Type','easyReservations').'</th>';
						$accordion .= '</tr>';
					$accordion .= '</thead>';
					$accordion .= '<tbody>';
						$accordion .= '<tr attr="hidden">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/lock.png);"></td>';
							$accordion .= '<td><strong>'.__('Hidden','easyReservations').'</strong><br><i>'.__('Fix information and hide from guest','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="custom">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/form.png);"></td>';
							$accordion .= '<td><strong>'.__('Custom','easyReservations').'</strong><br><i>'.__('Custom form elements to get more information','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="infobox">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/house.png);"></td>';
							$accordion .= '<td><strong>'.__('Resources infobox','easyReservations').'<br><i></strong>'.__('Displays information of currently selected resource','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion = apply_filters('easy-form-list', $accordion);
						$accordion .= '<tr attr="captcha">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/user.png);"></td>';
							$accordion .= '<td><strong>'.__('Captcha','easyReservations').'</strong><br><i>'.__('To verify only humans use the form','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="show_price">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/invoice.png);"></td>';
							$accordion .= '<td><strong>'.__('Show price','easyReservations').'</strong><br><i>'.__('Display price live','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="error">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/delete.png);"></td>';
							$accordion .= '<td><strong>'.__('Error','easyReservations').'</strong><br><i>'.__('Displays errors','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr attr="submit">';
							$accordion .= '<td style="background-image:url('.RESERVATIONS_URL.'images/lightning.png);"></td>';
							$accordion .= '<td><strong>'.__('Submit button','easyReservations').'</strong><br><i>'.__('Button to submit the form','easyReservations').'</i></td>';
						$accordion .= '</tr>';
					$accordion .= '</tbody>';
				$accordion .= '</table>';
			$accordion .= '</div>';
			$accordion .= '<h3>'.__('Format','easyReservations').'</h3>';
			$accordion .= '<div class="table">';
				$accordion .= '<table class="formtable">';
					$accordion .= '<tbody>';
						$accordion .= '<tr bttr="label">';
							$accordion .= '<td><strong>'.__('Label','easyReservations').' <tag>&lt;label&gt;</tag></strong><br><i>'.__('Used for description of tags. Should be before the tag.','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr bttr="span">';
							$accordion .= '<td><strong>'.__('Sub-label','easyReservations').' <tag>&lt;span class="small"&gt;</tag><br><i></strong>'.__('Small sub-label. Should be used inside labels.','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr bttr="row">';
							$accordion .= '<td><strong>'.__('Row','easyReservations').' <tag>&lt;span class="row"&gt;</tag><br><i></strong>'.__('To use multiple elements in one row. It may be nesecarry to define their width\'s.','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr bttr="b">';
							$accordion .= '<td><strong>'.__('Bold','easyReservations').' <tag>&lt;strong&gt;</tag></strong><br><i>'.__('Bold text','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr bttr="i">';
							$accordion .= '<td><strong>'.__('Italic','easyReservations').' <tag>&lt;i&gt;</tag></strong><br><i>'.__('Italic text','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr bttr="h1">';
							$accordion .= '<td><strong>'.__('Headline','easyReservations').' <tag>&lt;h1&gt;</tag></strong><br><i>'.__('Big headline.','easyReservations').'</i></td>';
						$accordion .= '</tr>';
						$accordion .= '<tr bttr="h2">';
							$accordion .= '<td><strong>'.__('Sub-headline','easyReservations').' <tag>&lt;h2&gt;</tag></strong><br><i>'.__('Smaller headline to divide the form.','easyReservations').'</i></td>';
						$accordion .= '</tr>';
					$accordion .= '</tbody>';
				$accordion .= '</table>';
			$accordion .= '</div>';
		$accordion .= '</div>';
	$accordion .= '</div>';
	$accordion .= '<a href="javascript:submitForm();" class="easybutton button-primary" style="margin:5px;">'.__( 'Submit' , 'easyReservations' ).'</a>';
	$accordion .= '<a href="javascript:;" class="button" style="margin:5px 5px 5px 0px;">'.__( 'Reset' , 'easyReservations' ).'</a>';
	$accordion .= '<a href="javascript:resetToDefault();" class="button" style="margin:5px 5px 5px 0px;">'.__( 'Default' , 'easyReservations' ).'</a>';
	$accordion .= '<form id="easyform" method="post">';
		$accordion .= '<input type="hidden" name="action" value="reservations_form_settings">';
		$accordion .= '<input type="hidden" name="reservations_formvalue" value="">';
	$accordion .= '</form>';

	echo $textfield.$accordion;

} elseif($setting_current_page=="custom"){
	wp_enqueue_script('custom-fields', RESERVATIONS_URL.'js/functions/custom.settings.js');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style( 'datestyle' );
	easyreservations_load_resources();

	global $the_rooms_array;
	$resources_array = array();
	foreach($the_rooms_array as $key => $resource){
		$resources_array[$key] = $resource->post_title;
	}

	$custom_fields = get_option('reservations_custom_fields', array());
	if(isset($_POST['custom_name'])){
		$custom = array();
		$custom["title"] = $_POST['custom_name'];
		$custom["type"] = $_POST['custom_field_type'];
		$custom["unused"] = $_POST['custom_field_unused'];
		if($custom["type"] == 'text' || $custom["type"] == 'area'){
			$custom["value"] = $_POST['custom_field_value'];
		} else {
			$custom['options'] = array();
			$get_id = array();
			foreach($_POST['id'] as $nr => $id){
				$final_id = $id;
				if(is_numeric($id)){
					$uid = uniqid($id);
					$get_id[$id] = $uid;
					$final_id = $uid;
				}
				$custom['options'][$final_id] = array();
				$custom['options'][$final_id]["value"] = $_POST['value'][$nr];
				if(isset($_POST['price'])) $custom['options'][$final_id]["price"] = $_POST['price'][$nr];
				if(isset($_POST['checked'][$nr]) && $_POST['checked'][$nr] == 1) $custom['options'][$final_id]['checked'] = 1;
			}

			if(isset($_POST['if_option'])){
				foreach($_POST['if_option'] as $nr => $opt_id){
					if(is_numeric($opt_id)) $opt_id = $get_id[$opt_id];
					$option = array();
					$option['type'] = $_POST['if_cond_type'][$nr];
					$option['operator'] = $_POST['if_cond_operator'][$nr];
					$option['cond'] = $_POST['if_cond'][$nr];
					if($_POST['if_cond_happens'][$nr] == "price") $option['price'] = $_POST['if_cond_amount'][$nr];
					else $option['price'] = $_POST['if_cond_happens'][$nr];
					$option['mult'] = $_POST['if_cond_mult'][$nr];
					$custom['options'][$opt_id]['clauses'][] = $option;
				}
			}
		}
		if(isset($_POST['custom_price_field'])) $custom['price'] = 1;
		if(isset($_POST['custom_field_required'])) $custom['required'] = 1;
		if(isset($_POST['custom_id'])){
			$custom_id = $_POST['custom_id'];
			$prompt = '<div class="updated"><p>'.__( 'Custom field edited', 'easyReservations' ).'</p></div>';
		} else {
			if(isset($custom_fields['id'])) $custom_fields['id'] = $custom_fields['id'] + 1;
			else $custom_fields['id'] = 1;
			$custom_id = $custom_fields['id'];
			$prompt = '<div class="updated"><p>'.__( 'Added custom field', 'easyReservations' ).'</p></div>';
		}
		if(!isset($custom_fields['fields'])) $custom_fields['fields'] = array();
		$custom_fields['fields'][$custom_id] = $custom;
		update_option('reservations_custom_fields', $custom_fields);
	} elseif(isset($_GET['delete'])){
		$prompt = '<div class="updated"><p>'.__( 'Custom field deleted', 'easyReservations' ).'</p></div>';
		unset($custom_fields['fields'][$_GET['delete']]);
		update_option('reservations_custom_fields', $custom_fields);
	}
	if(isset($prompt)) echo $prompt;

	$creator = '<div style="margin:5px;">This function is not completely finished, but should be usable and needs testing. With it you can define custom and price fields more convenient and their form element can be generated in the whole plugin.<br>Add custom fields to the form with [custom id="*"] and to emails with [custom id="*" show="title|value|amount"].<br>If you\'ve further suggestions or find any bugs please post in the forum.</div>';


	$creator = '<form name="custom_creator" id="custom_creator" method="post" method="post" style="float:right;margin-right: 15px;width:628px">';
		$creator .= '<table id="custom_fields_table" class="'.RESERVATIONS_STYLE.'">';
			$creator .= '<thead>';
				$creator .= '<tr>';
					$creator .= '<th colspan="2" style="text-align: left">';
						$creator .= __('Add and edit custom fields', 'easyReservations');
					$creator .= '</th>';
				$creator .= '</tr>';
			$creator .= '</thead>';
			$creator .= '<tbody>';
				$creator .= '<tr class="alternate">';
					$creator .= '<td>';
						$creator .= __('Title', 'easyReservations');
					$creator .= '</td>';
					$creator .= '<td>';
						$creator .= '<input type="text" name="custom_name" id="custom_name">';
					$creator .= '</td>';
				$creator .= '</tr>';
				$creator .= '<tr>';
					$creator .= '<td>';
						$creator .= __('Price field', 'easyReservations');
					$creator .= '</td>';
					$creator .= '<td>';
						$creator .= '<input id="custom_price_field" name="custom_price_field" type="checkbox"> ';
						$creator .= __('Field has influence on price', 'easyReservations');
					$creator .= '</td>';
				$creator .= '</tr>';
				$creator .= '<tr id="custom_type_tr" class="alternate">';
					$creator .= '<td>';
						$creator .= __('Form Element', 'easyReservations');
					$creator .= '</td>';
					$creator .= '<td>';
						$creator .= '<select name="custom_field_type" id="custom_field_type">';
						$creator .= '</select>';
					$creator .= '</td>';
				$creator .= '</tr>';
				$creator .= '<tr>';
					$creator .= '<td colspan="2" id="custom_field_extras">';
					$creator .= '</td>';
				$creator .= '</tr>';
				$creator .= '<tr>';
					$creator .= '<td colspan="2">';
						$creator .= '<input type="submit" value="'.__('Submit', 'easyReservations').'" class="button">';
					$creator .= '</td>';
				$creator .= '</tr>';
			$creator .= '</tbody>';
		$creator .= '</table>';
	$creator .= '</form>';
	$creator .= '<script>var plugin_url = "'.WP_PLUGIN_URL.'";var currency = "'.RESERVATIONS_CURRENCY.'";var custom_nonce = "'.wp_create_nonce('easy-custom').'";var all_custom_fields = '.json_encode(isset($custom_fields['fields']) ? $custom_fields['fields'] : array());
	$creator .= ';var resources = '.str_replace('\\"', '"', addslashes(json_encode($resources_array))).';</script>';
	echo $creator;

	$table = '<table id="custom_fields_table" class="'.RESERVATIONS_STYLE.'" style="min-width:40%;width:62%;margin:0px 5px 5px 0px;clear:none">';
	$table .= '<thead>';
	$table .= '<tr>';
	$table .= '<th>'.__('ID', 'easyReservations').'</th>';
	$table .= '<th>'.__('Title', 'easyReservations').'</th>';
	$table .= '<th>'.__('Type', 'easyReservations').'</th>';
	$table .= '<th>'.__('Value', 'easyReservations').'</th>';
	$table .= '<th colspan="2">'.__('Else', 'easyReservations').'</th>';
	$table .= '</tr>';
	$table .= '</thead>';
	$table .= '<tbody>';

	if($custom_fields && !empty($custom_fields)){
		$c = 0;
		foreach($custom_fields['fields'] as $key => $custom_field){
			$c++;
			$class = '';
			if($c%2!==0) $class = ' class="alternate"';
			$table .= '<tr '.$class.'>';
			$table .= '<td>'.$key.'</td>';
			$table .= '<td>'.$custom_field['title'].'</td>';
			$table .= '<td>'.$custom_field['type'].'</td>';
			$table .= '<td>';
			if($custom_field['type'] == 'select' || $custom_field['type'] == 'radio' ){
				$table .= '<ul class="options">';
				foreach($custom_field['options'] as $opt_id => $option){
					//if($opt_id == $customfield['value']) $class = ' class="selectedoption"';
					$class = '';
					$table .= '<li'.$class.'>'.$option['value'];
					if(isset($option['price'])) $table .= ' '.easyreservations_format_money($option['price'], true);
					if(isset($option['clauses'])) $table.= ' ('.count($option['clauses']).' '._n('condition', 'conditions', count($option['clauses']), 'easyReservations').')';
					$table .= '</li>';
				}
				$table .= '</ul>';
			} else {
				if(isset($custom_field['value']))
					$table .= $custom_field['value'];
			}
			$table .= '</td><td>'.$custom_field['unused'].'</td><td>';
			$table .= '<a href="javascript:custom_edit(\''.$key.'\');"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'/images/edit.png"></a>';
			$table .= '<a href="'.wp_nonce_url('admin.php?page=reservation-settings&site=custom&delete='.$key, 'easy-delete-custom').'"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'/images/delete.png"></a>';
			$table .= '</td></tr>';
		}
	} else $table .= '<tr><td colspan="5 ">'.__('No custom fields defined', 'easyReservations').'</td></tr>';
	$table .= '</tbody>';
	$table .= '</table>';

	echo $table;

} elseif($setting_current_page=="email"){
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EMAIL SETTINGS + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>edit your reservation on [editlink]";
$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource]<br>Price: [price]<br>";
$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br>edit your reservation on [editlink]";
$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource]<br><br>Price: [price]<br>edit your reservation on [editlink]";
$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource]<br>Resource Number: [resourcenumber]<br>Price: [price]<br><br>edit your reservation on [editlink]";
$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
$emailstandart6="Reservation got edited by guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br><br>[changelog]";
$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
$emailstandart10="Reservation got canceled by guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Resource Number: [resourcenumber]<br>Price: [price]<br><br>[changelog]";
?><script type="text/javascript">
function addtextforemail(nr){
	if(nr == 0) document.reservations_email_settings.reservations_email_sendmail_msg.value = document.reservations_email_settings.inputemail0.value;
	else if(nr == 1) document.reservations_email_settings.reservations_email_to_admin_msg.value = document.reservations_email_settings.inputemail1.value;
	else if(nr == 2) document.reservations_email_settings.reservations_email_to_userapp_msg.value = document.reservations_email_settings.inputemail2.value;
	else if(nr == 3) document.reservations_email_settings.reservations_email_to_userdel_msg.value = document.reservations_email_settings.inputemail3.value;
	else if(nr == 4) document.reservations_email_settings.reservations_email_to_user_msg.value = document.reservations_email_settings.inputemail4.value;
	else if(nr == 5) document.reservations_email_settings.reservations_email_to_user_edited_msg.value = document.reservations_email_settings.inputemail5.value;
	else if(nr == 6) document.reservations_email_settings.reservations_email_to_admin_edited_msg.value = document.reservations_email_settings.inputemail6.value;
	else if(nr == 7) document.reservations_email_settings.reservations_email_to_user_admin_edited_msg.value = document.reservations_email_settings.inputemail7.value;
	else if(nr == 8) document.reservations_email_settings.reservations_email_to_admin_paypal_msg.value = document.reservations_email_settings.inputemail8.value;
	else if(nr == 9) document.reservations_email_settings.reservations_email_to_user_paypal_msg.value = document.reservations_email_settings.inputemail9.value;
	else if(nr == 10) document.reservations_email_settings.reservations_email_to_admin_canceled_msg.value = document.reservations_email_settings.inputemail10.value;
}
</script>
<form method="post" action="admin.php?page=reservation-settings&site=email"  id="reservations_email_settings" name="reservations_email_settings">
	<input type="hidden" name="action" value="reservations_email_settings"/>
	<input type="hidden" value="<?php echo $emailstandart0; ?>" name="inputemail0">
	<input type="hidden" value="<?php echo $emailstandart1; ?>" name="inputemail1">
	<input type="hidden" value="<?php echo $emailstandart2; ?>" name="inputemail2">
	<input type="hidden" value="<?php echo $emailstandart3; ?>" name="inputemail3">
	<input type="hidden" value="<?php echo $emailstandart4; ?>" name="inputemail4">
	<input type="hidden" value="<?php echo $emailstandart5; ?>" name="inputemail5">
	<input type="hidden" value="<?php echo $emailstandart6; ?>" name="inputemail6">
	<input type="hidden" value="<?php echo $emailstandart7; ?>" name="inputemail7">
	<input type="hidden" value="<?php echo $emailstandart10; ?>" name="inputemail10">
	<?php if(!function_exists('easyreservations_generate_email_settings')){ ?>
	<table style="width:99%;" cellspacing="0">
		<tr style="width:60%;" cellspacing="0">
			<td valign="top">
				<?php 
					$emails = easyreservations_get_emails();
					foreach($emails as $key => $email){
						echo '<table class="'.RESERVATIONS_STYLE.'" style="margin-bottom:5px;">';
							echo '<thead>';
								echo '<tr>';
									echo '<th>';
										echo $email['name'];
										echo '<span style="float:right">'.__( 'Active' , 'easyReservations' ).': <input type="checkbox" id="idactive" value="1" name="'.$key.'_check" '.checked(1, $email['option']['active'],false).'> &nbsp;';
										echo '<input type="button" onclick="document.getElementById(\'reservations_email_settings\').submit(); return false;" class="easybutton button-primary" value="'.__( 'Save Changes' , 'easyReservations' ).'"></span>';
									echo '</th>';
								echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
								echo '<tr valign="top">';
									echo '<td>';
										echo '<input id="idsubj" type="text" name="'.$key.'_subj" style="width:60%;" value=\''.stripslashes($email['option']['subj']).'\'> '.__( 'Subject' , 'easyReservations' );
										echo '<input type="button" onclick="addtextforemail('.($email['default'] ? $email['default'] : '').');" style="float:right" class="button" value="'.__( 'Default' , 'easyReservations' ).'">';
									echo '</td>';
								echo '</tr>';
								echo '<tr valign="top">';
									echo '<td>';
										echo '<textarea id="idmsg" name="'.$key.'_msg" style="width:99%;height:120px;">'.stripslashes($email['option']['msg']).'</textarea>';
									echo '</td>';
								echo '</tr>';
							echo '</tbody>';
						echo '</table>';
					} ?>
		</td>
		<td style="width:1%;"></td>
		<td style="width:350px;" valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th id="idtags"> <?php printf ( __( 'Tags' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;">
						<div class="explainbox">
							<p><code class="codecolor">&lt;br&gt;</code> <i><?php printf ( __( 'wordwrap' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[adminmessage]</code> <i><?php printf ( __( 'message from admin' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[ID]</code> <i><?php printf ( __( 'ID' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[thename]</code> <i><?php printf ( __( 'name' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[email]</code> <i><?php printf ( __( 'email' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[arrival]</code> <i><?php printf ( __( 'arrival date' , 'easyReservations' ));?></i></p>								
							<p><code class="codecolor">[departure]</code> <i><?php printf ( __( 'departure date' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[units]</code> <i><?php echo __( 'amount of billing units', 'easyReservations' )?></i></p>
							<p><code class="codecolor">[persons]</code> <i><?php printf ( __( 'amount of adults and children' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[adults]</code> <i><?php printf ( __( 'amount of adults' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[childs]</code> <i><?php printf ( __( 'amount of children' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[country]</code> <i><?php printf ( __( 'country of guest' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[resource]</code> <i><?php printf ( __( 'name of resource' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[resource-number]</code> <i><?php printf ( __( 'name of resource number' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[price]</code> <i><?php printf ( __( 'price of reservation' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[paid]</code> <i><?php printf ( __( 'paid amount' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor" id="idtagcustom">[custom id="*"]</code> <i><?php printf ( __( 'custom fields' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[changlog]</code> <i><?php printf ( __( 'show changes after edits' , 'easyReservations' ));?></i></p>
							<p><code class="codecolor">[editlink]</code> <i><?php printf ( __( 'link to user control panel' , 'easyReservations' ));?></i></p>
							<?php do_action('easy-email-list'); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		</td></tr>
	</table>
	<?php } do_action('easy-email-settings'); ?>
</form>
<?php } 
	if($setting_current_page=="about"){ ?>
	<table style="width:99%;" cellspacing="0"><tr><td style="width:60%;" style="width:49%;"  valign="top">
		<table id="changelog" class="<?php echo RESERVATIONS_STYLE; ?>" >
			<thead>
				<tr>
					<th> <?php printf ( __( 'Changelog' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:100%;" align="left">
						<style> 
							#changelog ul {
								list-style: disc !important;
							} #changelog li {
								list-style-type: circle;
							} #changelog ul {
								padding-left: 30px;
							}
						</style> 
						<?php include('changelog.html');?>
					</td>
				</tr>	
			</tbody>
		</table>
		</td><td style="width:1%;"></td><td style="width:39%;" style="width:49%;"  valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%;margin-bottom:7px;" cellspacing="0" cellpadding="0" style="background:#fff;">
				<thead>
					<tr>
						<th colspan="2"> <?php printf ( __( 'Status' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="font-weight:bold;padding:10px;text-align:center"><span style="width:20%;display: inline-block">Version: <?php echo RESERVATIONS_VERSION; ?></span><span style="width:30%;display: inline-block">Last update: 23.09.2017</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
					</tr>
					<tr class="alternate">
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/" target="_blank" id="iddocumentation"><?php echo __( 'Documentation' , 'easyReservations' );?></a></td>
					</tr>
					<tr>
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/bug-reports/" target="_blank" id="idbugreport"><?php echo __( 'Report bug' , 'easyReservations' );?></a></td>
					</tr>
					<tr class="alternate"> 
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/premium/" target="_blank" id="idpremium"><?php echo __( 'Premium' , 'easyReservations' );?></a></td>
					</tr>
					<tr>
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/" target="_blank" id="idrate"><?php echo __( 'Rate the Plugin' , 'easyReservations' );?>, please!</a></td>
					</tr>
					<tr class="alternate">
						<td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/chrome/" target="_blank" id="idrate"><b style="font-weight: bold !important; color:#ff0000;">*NEW*</b> <?php echo __( 'Chrome Extension' , 'easyReservations' );?> <b style="font-weight: bold !important; color:#ff0000;">*NEW*</b></a></td>
					</tr>
				</tbody>
			</table>
	</tr>
</table>
<?php } elseif($setting_current_page=="chrome"){
	$countactive = 0;
	$countpending = 0;
	easyreservations_load_resources();
	global $the_rooms_array;
	echo '<table><tr><td id="idvers">'.RESERVATIONS_VERSION.'</td></tr></table><div id="idtable1">';
	$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '".date('Y-m-d H:i')."' >= arrival AND '".date('Y-m-d H:i')."' <= departure"); // Search query 
	if(!empty($queryDepartures)){
		echo '<table class="reservations"><thead><tr><th>Date</th><th>Name</th><th>Resource</th><th>Pers</th><th>Price</th><th></th></tr></thead><tbody>';
		foreach($queryDepartures as $id){
			$countactive++;
			$res = new Reservation($id->id);
			$res->Calculate();
			if($res->interval == 3600) $dateformat = 'd.m H:00';
			else $dateformat = 'd.m';
			echo '<tr>';
				echo '<td>'.date($dateformat, $res->arrival).' - '.date($dateformat, $res->departure).'</td><td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
				echo '<td>'.__($the_rooms_array[$res->resource]->post_title).' '.easyreservations_get_roomname($res->resourcenumber, $res->resource).'</td><td>'.$res->adults.'/'.$res->childs.'</td><td>'.$res->formatPrice(true, true, 0).'</td>';
				echo '<td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&approve='.$res->id.'">REJECTIMG</a> <a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&edit='.$res->id.'">EDITIMG</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div><div id="idtable2">';
	$queryPendings = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '".date('Y-m-d H:i')."' <= arrival"); // Search query 
	if(!empty($queryPendings)){
		echo '<table class="reservations"><thead><tr><th>Date</th><th>Name</th><th>Resource</th><th>Pers</th><th>Price</th><th></th></tr></thead><tbody>';
		foreach($queryPendings as $id){
			$countpending++;
			$res = new Reservation($id->id);
			$res->Calculate();
			if($res->interval == 3600) $dateformat = 'd.m H:00';
			else $dateformat = 'd.m';
			echo '<tr>';
				echo '<td>'.date($dateformat, $res->arrival).' - '.date($dateformat, $res->departure).'</td><td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
				echo '<td>'.__($the_rooms_array[$res->resource]->post_title).' '.easyreservations_get_roomname($res->resourcenumber, $res->resource).'</td><td>'.$res->adults.'/'.$res->childs.'</td><td>'.$res->formatPrice(true, true, 0).'</td>';
				echo '<td><a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&approve='.$res->id.'">APPRIMG</a> <a target="_blank" href="'.site_url().'/wp-admin/admin.php?page=reservations&edit='.$res->id.'">EDITIMG</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div><div id="idactive">'.$countactive.'</div><div id="idpending">'.$countpending.'</div>';
}
do_action( 'er_set_add' );?>
</div>
<?php } ?>