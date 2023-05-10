<?php
function reservations_form_shortcode($atts){
	global $post,$easyreservations_script;
	$final_form = '';
	$error = '';
	$info_box = false;
	if(isset($atts[0])){
		$theForm = stripslashes(get_option('reservations_form_'.$atts[0]));
		$form_name='<input type="hidden" name="formname" value="'.$atts[0].'">';
	} else {
		$theForm = stripslashes (get_option("reservations_form"));
		$form_name = '';
	}
	$formid = 'easy-form-'.rand(0,99999);
	if(empty($theForm)) $theForm = stripslashes(get_option("reservations_form"));

	$atts = shortcode_atts(array(
		'room' => 0,
		'resource' => 0,
		'price' => 1,
		'multiple' => 0,
		'resourcename' => __( 'Room' , 'easyReservations' ),
		'cancel' => __( 'Cancel' , 'easyReservations' ),
		'credit' => __( 'Your reservation is complete' , 'easyReservations' ),
		'submit' => __( 'Your reservation was sent' , 'easyReservations' ),
		'validate' =>__( 'Reservation was validated succesfully' , 'easyReservations' ),
		'subcredit' => '',
		'discount' => 100,
		'subsubmit' => '',
		'subvalidate' => '',
		'reset' => 1,
		'style' => 'none',
		'width' => '',
		'bg' => '#fff',
		'pers' => 0,
		'payment' => 1,
		'datefield' => ''
	), $atts);
	$atts['width'] = (float) $atts['width'];
	if($atts['width'] > 100 || $atts['width'] < 3) $atts['width'] = 100;
	if($atts['room'] > 0) $atts['resource'] = $atts['room'];
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('datestyle' , false, array(), false, 'all');
	wp_enqueue_style('easy-frontend' , false, array(), false, 'all');
	wp_enqueue_script('easyreservations_send_form');
	wp_enqueue_script('easyreservations_data');

	if(wp_style_is( 'easy-form-'.$atts['style'], 'registered')) wp_enqueue_style('easy-form-'.$atts['style'] , false, array(), false, 'all');
	else wp_enqueue_style('easy-form-none' , false, array(), false, 'all');	

	if(strpos($theForm, '[error') !== false){
		$validate_action = 'easyreservations_send_validate(false,\''.$formid.'\');';
		wp_enqueue_script( 'easyreservations_send_validate' );
	} else {
		$validate_action = '';
	}

	if(strpos($theForm, '[show_price') !== false){
		$price_action = 'easyreservations_send_price(\''.$formid.'\');';
		wp_enqueue_script( 'easyreservations_send_price' );
	} else $price_action = '';

	if(strpos(get_the_content($post->ID), '[easy_calendar') !== false) $isCalendar = true;
	else $isCalendar = false;

	if(isset($_POST['easynonce'])){ // Check and Set the Form Inputs
		if (!wp_verify_nonce($_POST['easynonce'], 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		easyreservations_load_resources(true);
		global $the_rooms_intervals_array, $current_user;

		//if(isset($_POST['captcha_value'])) $captcha = array( 'captcha_prefix' => $_POST['captcha_prefix'], 'captcha_value' => $_POST['captcha_value'] );
		//else $captcha = '';
		$name_form = isset($_POST['thename']) ? $_POST['thename'] : '';
		$email = isset($_POST['email']) ? $_POST['email'] : '';
		$country = isset($_POST['country']) ? $_POST['country'] : '';
		$persons = isset($_POST['persons']) ? $_POST['persons'] : 1;
		$children = isset($_POST['childs']) ? $_POST['childs'] : 0;
		$room = isset($_POST['easyroom']) ? $_POST['easyroom'] : false;

		if(isset($_POST['from'])) $arrival = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['from'].' 00:00:00')->getTimestamp();
		else $arrival = time();

		$departure = 0;
		$arrivalplus = 0;
		if(isset($_POST['date-from-hour'])) $arrivalplus += (int) $_POST['date-from-hour'] * 60;
		else $arrivalplus += 12*60;
		if(isset($_POST['date-from-min'])) $arrivalplus += (int) $_POST['date-from-min'];
		if($arrivalplus > 0) $arrivalplus = $arrivalplus * 60;
		$departureplus = 0;
		if(isset($_POST['date-to-hour'])) $departureplus += (int) $_POST['date-to-hour'] * 60;
		if(isset($_POST['date-to-min'])) $departureplus += (int) $_POST['date-to-min'];
		if($departureplus > 0) $departureplus = $departureplus*60;

		if(isset($_POST['to'])){
			$departure = EasyDateTime ::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['to'].' 00:00:00')->getTimestamp();
		} else {
			$departure = $arrival;
			if(isset($_POST['nights'])){
				$departure = $arrival+((int) $_POST['nights'] * $the_rooms_intervals_array[$_POST['easyroom']]);
				if(!isset($_POST['date-to-hour'])) $departure += $arrivalplus;
			}	elseif($departureplus == 0){
				$departure += $arrivalplus + $the_rooms_intervals_array[$_POST['easyroom']];
			}
		}

		$arrival += $arrivalplus;
		$departure += $departureplus;

		$current_user = wp_get_current_user();
		$array = array('name' => $name_form, 'email' => $email, 'arrival' => $arrival,'departure' => $departure,'resource' => (int) $room,'resourcenumber' => 0,'country' => $country, 'adults' => $persons, 'childs' => $children,'reservated' => date('Y-m-d H:i:s', time()),'status' => '','user' => $current_user->ID);
		$custom = get_custom_submit($array, $error);
		$array['custom'] = maybe_unserialize($custom[0]);
		$array['prices'] = maybe_unserialize($custom[1]);
		$error = $custom[2];

		$res = new Reservation(false, $array, false);
		try {
			$res->fake = false;
			$res->admin = false;
			if(isset($_POST['coupon'])){
				$res->coupon = $_POST['coupon'];
				$res = apply_filters('easy-add-res-ajax', $res);
			}
			$theID = $res->addReservation(array('reservations_email_to_admin', 'reservations_email_to_user'), array(false, $res->email));
			if($theID){
				foreach($theID as $key => $terror){
					if($key%2==0) $error.=  '<li><label for="'.$terror.'">';
					else $error .= $terror.'</label></li>';
				}
			}
		} catch(Exception $e){
			$error.=  '<li><label>'.$e->getMessage().'</label></li>';
		}

		if(empty($error) && isset($arrival)){ //When Check gives no error Insert into Database and send mail
			do_action('reservation_successful_guest', $res);
			$final_form .= '<div class="easy_form_success" id="easy_form_success">';
			if(!empty($atts['submit'])) $final_form .= '<b class="easy_submit">'.$atts['submit'].'</b>';
			if(!empty($atts['subsubmit'])) $final_form .= '<span class="easy_subsubmit">'.$atts['subsubmit'].'</span>';
			$res->Calculate(true);
			if($atts['price'] == 1) $final_form .= '<span class="easy_show_price_submit">'.__('Price','easyReservations').': <b>'.easyreservations_format_money($res->price, 1).'</b></span>';
			if(function_exists('easyreservation_generate_payment_form') && $atts['payment'] > 0){
				$final_form .= easyreservation_generate_payment_form($res, $res->price, ($atts['payment'] == 2) ? true : false, (is_numeric($atts['discount']) && $atts['discount'] < 100) ? $atts['discount'] : false);
			}
			$easyreservations_script .= 'jQuery("#showCalender").remove();window.location.hash = \'easy_form_success\';';
			$final = $final_form.'</div>';
			$script = get_option('easyreservations_successful_script');
			if($script && !empty($script)) $easyreservations_script.= stripslashes($script);
		}
	}

	$theForm = stripslashes($theForm);
	$theForm = apply_filters( 'easy-form-content', $theForm);
	$roomfield = 0;
	$tofield = false;
	$customPrices  = 0;

	$tags = easyreservations_shortcode_parser($theForm, true);
	foreach($tags as $fields){
		$field=shortcode_parse_atts( $fields);
		$value = isset($field['value']) ? $field['value'] : '';
		$style = isset($field['style']) ? $field['style'] : '';
		$title = isset($field['title']) ? $field['title'] : '';
		$maxlength = isset($field['maxlength']) ? $field['maxlength'] : '';
		if(isset($field['disabled'])){
			$array = array('units', 'nights', 'times', 'persons', 'adults', 'childs', 'country', 'resources', 'rooms');
			if(in_array($field[0], $array) || (($field[0] == "custom" || $field[0] == "price") && in_array($field[1], array("check", "checkbox", "radio", "select")))) $disabled = 'disabled="disabled"';
			else $disabled = 'readonly="readonly"';
		} else $disabled = '';
		if($field[0]=="date-from"){
			if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, time()+86400);
			elseif(preg_match('/\+{1}[0-9]+/i', $value)){
				$cutplus = str_replace('+', '',$value);
				$value = date(RESERVATIONS_DATE_FORMAT, time()+($cutplus*86400));
			}
			$theForm=str_replace('['.$fields.']', '<input id="easy-form-from" type="text" name="from" value="'.$value.'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="date-to"){
			$tofield = true;
			if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, time()+172800);
			elseif(preg_match('/\+{1}[0-9]+/i', $value)){
				$cutplus = str_replace('+', '',$value);
				$value = date(RESERVATIONS_DATE_FORMAT, time()+((int) $cutplus*86400));
			}
			$theForm=str_replace('['.$fields.']', '<input id="easy-form-to" type="text" name="to" value="'.$value.'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0]=="date-from-hour" || $field[0]=="date-to-hour"){
			$theForm=str_replace('['.$fields.']', '<select id="'.$field[0].'" name="'.$field[0].'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_time_options($value).'</select>', $theForm);
		} elseif($field[0]=="date-from-min" || $field[0]=="date-to-min"){
			$theForm=str_replace('['.$fields.']', '<select id="'.$field[0].'" name="'.$field[0].'" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options("00", 59, $value).'</select>', $theForm);
		} elseif($field[0]=="units" || $field[0]=="nights" || $field[0]=="times"){
			$tofield = true;
			$start = 1;
			if(isset($field[1])) $start = $field[1]; 
			if(isset($field[2])) $end = $field[2]; else $end = 6;
			$theForm=str_replace('['.$fields.']', '<select id="easy-form-units" name="nights" '.$disabled.' title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options($start, $end, $value).'</select>', $theForm);
		} elseif($field[0]=="persons" || $field[0]=="adults"){
			$start = 1;
			if(isset($field[1])) $start = $field[1]; 
			if(isset($field[2])) $end = $field[2]; else $end = 6;
			$theForm=preg_replace('/\['.$fields.'\]/', '<select id="easy-form-persons" name="persons" '.$disabled.' style="'.$style.'" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options($start,$end,$value).'</select>', $theForm);
		} elseif($field[0]=="childs"){ //CHILDRENS
			$start = 0;
			if(isset($field[1])) $start = $field[1]; 
			if(isset($field[2])) $end = $field[2]; else $end = 6;
			$theForm=preg_replace('/\['.$fields.'\]/', '<select name="childs" '.$disabled.' style="'.$style.'" title="'.$title.'" onchange="'.$price_action.$validate_action.'">'.easyreservations_num_options($start,$end,$value).'</select>', $theForm);
		} elseif($field[0] == "thename"){ //NAME
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-form-thename" name="thename" '.$disabled.' value="'.$value.'" style="'.$style.'" title="'.$title.'" onchange="'.$validate_action.'">', $theForm);
		} elseif($field[0] == "error"){
			if(strlen($error) > 3){
				$form_error = $error;
				$class = '';
			} else {
				$form_error = '';
				$class = ' hide-it';
			}
			if(isset($field['error_title'])) $error_title = $field['error_title'];
			else $error_title='Errors found in the form';
			if(isset($field['error_message'])) $error_message = $field['error_message'];
			else $error_message='There is a problem with the form, please check and correct the following:';
			$theForm=preg_replace('/\['.$fields.'\]/', '<div class="easy-show-error-div'.$class.'" id="easy-show-error-div" style="'.$style.'"><h2>'.$error_title.'</h2>'.$error_message.'<ul id="easy-show-error">'.$form_error.'</ul></div>', $theForm);
		} elseif($field[0]=="infobox"){ //INFOBOX
			$resource_block = '<div id="resource_infobox" style="'.$style.'" title="'.$title.'"></div>';
			$theForm=preg_replace('/\['.$fields.'\]/', $resource_block, $theForm);
			$info_box = $field;
		} elseif($field[0] == "email"){
			$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-form-email" name="email" '.$disabled.' value="'.$value.'" title="'.$title.'" style="'.$style.'" onchange="'.$price_action.$validate_action.'">', $theForm);
		} elseif($field[0] == "country"){
			$theForm=str_replace('['.$fields.']', '<select id="easy-form-country" name="country" '.$disabled.' title="'.$title.'" style="'.$style.'">'.easyreservations_country_options($value).'</select>', $theForm);
		} elseif($field[0] == "show_price"){
			if(isset($field['before'])) $before = $field['before'];
			else $before = '';
			if(isset($field['price']) && $field['price'] !== 'res' && $field['price'] !== 'reservation') $after = '<input type="hidden" name="easypriceper" id="easypriceper" value="'.$field['price'].'">';
			else $after = '';
			$theForm=preg_replace('/\['.$fields.'\]/', '<span class="easy-form-price" title="'.$title.'" style="'.$style.'">'.$before.'<span id="showPrice"><b>'.easyreservations_format_money(0,1).'</b></span></span>'.$after, $theForm);
		} elseif($field[0]=="captcha"){
			require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
			$captcha = new easy_ReallySimpleCaptcha();
			if(isset($field['color']) && $field['color'] == 'white') $captcha->fg = array( 255, 255, 255 );
			$prefix = mt_rand();
			$url = $captcha->generate_image($prefix, $captcha->generate_random_word());
			$theForm=preg_replace('/\['.$fields.'\]/', '<span class="row"><input type="text" title="'.$title.'" name="captcha_value" id="easy-form-captcha" style="width:40px;'.$style.'" ><span class="captcha-image"><img id="easy-form-captcha-img"	style="vertical-align:middle;margin-top: -5px;" src="'.RESERVATIONS_URL.'lib/captcha/tmp/'.$url.'"></span><input type="hidden" value="'.$prefix.'" name="captcha_prefix"></span>', $theForm);
		} elseif($field[0]=="hidden"){
			if($field[1] == "room" || $field[1] == "resource"){
				$roomfield=1;
				if(!isset($field[2]) && !is_numeric($field[2])) $field[2] = $atts['resource'];
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="easyroom" value="'.$field[2].'">', $theForm);
			} elseif($field[1] == "from"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="from" value="'.$field[2].'">', $theForm);
			} elseif($field[1] == "to"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden"  name="to" value="'.$field[2].'">', $theForm);
			} elseif($field[1] == "units" || $field[1]=="times"){
				$tofield = true;
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" id="easy-form-units" name="nights" value="'.$field[2].'">', $theForm);
			} elseif($field[1] == "persons" || $field[1]=="adults"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="persons" value="'.$field[2].'">', $theForm);
			} elseif($field[1] == "childs"){
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="childs" value="'.$field[2].'">', $theForm);
			} else {
				$theForm=preg_replace('/\['.$fields.'\]/', '<input type="hidden" name="'.$field[1].'" id="'.$field[1].'" value="'.$field[2].'">', $theForm);
			}
		} elseif($field[0] == "rooms" || $field[0]=="resources"){
			$roomfield=1;
			if(isset($field['exclude'])) $exclude = explode(',', $field['exclude']); else $exclude = '';
			$theForm=str_replace('['.$fields.']', '<select name="easyroom" style="'.$style.'" id="form_room" '.$disabled.' onchange="'.$price_action.$validate_action.'">'.easyreservations_resource_options(($value == '') ? $atts['resource'] : $value, 0, $exclude).'</select>', $theForm);
		} elseif($field[0] == "custom"){
			if(isset($field['id'])){
				$custom_fields = get_option('reservations_custom_fields');
				$form_field = '';
				if(isset($custom_fields['fields'][$field['id']])){
					$custom_field = $custom_fields['fields'][$field['id']];
					$onchange = '';
					$style = isset($field['style']) ? ' style="'.$field['style'].'"' : '';
					if(isset($custom_field['required'])) $onchange = $validate_action.';';
					if(isset($custom_field['price'])) $onchange .= $price_action;
					if(!empty($onchange)) $onchange = ' onchange="'.$onchange.'"';
					$form_field = easyreservations_generate_custom_field($field['id'], false, $style.$onchange);
				}
				$theForm=str_replace($fields, $form_field, $theForm);
			} else {
				if(isset($field[3])) $value_field = str_replace('"', '', $field[3]);
				if(end($field) == "*"){
					$req = 'req';
					$onchange = 'onchange="'.$validate_action.'"';
				} else {
					$req = '';
					$onchange = '';
				}
				if($field[1]=="text"){
					$theForm=str_replace('['.$fields.']', '<input title="'.$title.'" style="'.$style.'" '.$disabled.' type="text" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" '.$onchange.' value="'.$value.'">', $theForm);
				} elseif($field[1]=="textarea"){
					$theForm=str_replace('['.$fields.']', '<textarea title="'.$title.'" style="'.$style.'" '.$disabled.' name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" '.$onchange.' value="'.$value.'"></textarea>', $theForm);
				} elseif($field[1]=="check" || $field[1]=="checkbox"){
					$checked = isset($field['checked']) ? ' checked="'.$field['checked'].'"' : '';
					if(!empty($disabled)) $theForm=str_replace('['.$fields.']', '<input type="hidden" title="'.$title.'" '.$checked.' style="'.$style.'" name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">', $theForm);
					else $theForm=str_replace('['.$fields.']', '<input type="checkbox" title="'.$title.'" '.$checked.' style="'.$style.'" name="easy-custom-'.$field[2].'" '.$onchange.' id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value_field.'">', $theForm);
				} elseif($field[1]=="radio"){
					if(preg_match("/^[a-zA-Z0-9_]+$/", $value_field)){
						$theForm=str_replace('['.$fields.']', '<span class="radio"><input type="radio" title="'.$title.'" '.$disabled.' style="'.$style.'" name="easy-custom-'.$field[2].'" '.$onchange.' id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value_field.'"> '.$value_field.'</span>', $theForm);
					} elseif(preg_match("/^[a-zA-Z0-9_ \\,\\t]+$/", $value_field)){
						$valueexplodes=explode(",", $value_field);
						$custom_radio = '';
						foreach($valueexplodes as $value){
							if($value != '') $custom_radio .= '<span class="radio"><input type="radio" title="'.$title.'" '.$disabled.' style="'.$style.'" '.$onchange.' name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'" value="'.$value.'"> '.$value.'</span>';
						}
						$theForm=str_replace($fields, $custom_radio, $theForm);
					}
				} elseif($field[1]=="select"){
					if(preg_match("/^[0-9]+$/", $value_field)){
						$theForm=preg_replace('/\['.$fields.'\]/', '<select title="'.$title.'" style="'.$style.'" '.$disabled.'  name="easy-custom-'.$field[2].'" '.$onchange.' id="easy-custom-'.$req.'-'.$field[2].'">'.easyreservations_num_options(1,$value_field).'</select>', $theForm);
					} elseif(preg_match("/^[a-zA-Z0-9_]+$/", $value_field)){
						$theForm=preg_replace('/\['.$fields.'\]/', '<select title="'.$title.'" style="'.$style.'" '.$disabled.'  name="easy-custom-'.$field[2].'" '.$onchange.' id="easy-custom-'.$req.'-'.$field[2].'"><option value="'.$value_field.'">'.$field[3].'</option></select>', $theForm);
					} elseif(strstr($value_field,",")) {
						$valueexplodes=explode(",", $value_field);
						$custom_select = '';
						foreach($valueexplodes as $value){
							if($value != '') $custom_select .= '<option value="'.$value.'">'.$value.'</option>';
						}
						$theForm=str_replace($fields, '<select title="'.$title.'" style="'.$style.'" '.$disabled.' '.$onchange.' name="easy-custom-'.$field[2].'" id="easy-custom-'.$req.'-'.$field[2].'">'.$custom_select.'</select>', $theForm);
					}
				}
			}
		} elseif($field[0]=="price"){
			$value_field = str_replace('"', '', $field[3]);
			if(isset($field[4]) && $field[4] == 'pp' ){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':1';
			} elseif(isset($field[4]) && $field[4] == 'pn'){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':2';
			} elseif(isset($field[4]) && $field[4] == 'pb'){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':3';
			} elseif(isset($field[4]) && $field[4] == 'pa'){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':4';
			} elseif(isset($field[4]) && $field[4] == 'pc'){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':5';
			} elseif(isset($field[4]) && $field[4] == 'pcn'){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':6';
			} elseif(isset($field[4]) && $field[4] == 'pan'){
				$personfield = 'class="'.$field[4].'"';
				$addcontent = ':7';
			} else {
				$personfield = '';
				$addcontent = '';
			}
			if($field[1] == "check" || $field[1]=="checkbox"){
				$checked = isset($field['checked']) ? ' checked="'.$field['checked'].'"' : '';
				if(isset($field['disabled'])){
					if($field['disabled'] == "hidden") $disabled = 'type="hidden"';
					else $disabled = 'type="checkbox"';
				} else $disabled = 'type="checkbox"';
				$theForm = str_replace('['.$fields.']', '<input title="'.$title.'" style="'.$style.'" id="custom_price'.$customPrices.'" '.$personfield.' '.$disabled.' '.$checked.' onchange="'.$price_action.'" name="custom_price'.$field[2].'" value="'.$value_field.$addcontent.'">', $theForm);
			} elseif($field[1]=="radio"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $value_field)){
					$explodeprice = explode(":", $value_field);
					if(!isset($field['noprice']) && strpos($value_field, '>') === false) $showprice = ': '.easyreservations_format_money($explodeprice[1], 1);
					else $showprice = '';
					$theForm=preg_replace('/\['.$fields.'\]/', '<span class="radio"><input title="'.$title.'" style="'.$style.'" '.$disabled.' id="custom_price'.$customPrices.'" '.$personfield.' type="radio" onchange="'.$price_action.'" name="custom_price'.$field[2].'" value="'.$explodeprice[0].':'.$explodeprice[1].$addcontent.'"> '.$explodeprice[0].$showprice.'</span>', $theForm);
				} elseif(strstr($value_field,",")){
					$valueexplodes = explode(",", $value_field);
					$custom_radio = '<pre>';
					foreach($valueexplodes as $value){
						$explodeprice = explode(":", $value);
						if(!isset($field['noprice']) && strpos($value_field, '>') == false) $showprice = ': '.easyreservations_format_money($explodeprice[1], 1);
						else $showprice = '';
						if($value != '') $custom_radio .= '<span class="radio"><input id="custom_price'.$customPrices.'" '.$disabled.' title="'.$title.'" style="'.$style.'" type="radio" '.$personfield.' name="custom_price'.$field[2].'" onchange="'.$price_action.'" value="'.$explodeprice[0].':'.$explodeprice[1].$addcontent.'"> '.$explodeprice[0].$showprice.'</span>';
						$customPrices++;
					}
					$theForm = preg_replace('/\['.$fields.'\]/', $custom_radio.'</pre>', $theForm);
				}
			} elseif($field[1]=="select"){
				if(preg_match("/^[a-zA-Z0-9_]+$/", $value_field)){
					$explodeprice=explode(":", $value_field);
					if(!isset($field['noprice']) && strpos($value_field, '>') === false) $showprice = ': '.easyreservations_format_money($explodeprice[1], 1);
					else $showprice = '';
					$theForm=preg_replace('/\['.$fields.'\]/', '<select id="custom_price'.$customPrices.'" '.$personfield.' '.$disabled.' name="custom_price'.$field[2].'" title="'.$title.'" style="'.$style.'" onchange="'.$price_action.'"><option value="'.$explodeprice[0].':'.$explodeprice[1].$addcontent.'">'.$explodeprice[0].$showprice.'</option></select>', $theForm);
				} elseif(preg_match("/^[a-zA-Z0-9].+$/", $value_field)){
					$valueexplodes=explode(",", $value_field);
					$custom_select = '';
					foreach($valueexplodes as $value){
						$explodeprice = explode(":", $value);
						if(!isset($field['noprice']) && strpos($value_field, '>') === false) $showprice = ': '.easyreservations_format_money($explodeprice[1], 1);
						else $showprice = '';
						if($value != '') $custom_select .= '<option value="'.$explodeprice[0].':'.$explodeprice[1].$addcontent.'">'.$explodeprice[0].$showprice.'</option>';
					}
					$theForm = str_replace($fields, '<select  '.$personfield.' style="'.$style.'" title="'.$title.'" id="custom_price'.$customPrices.'" '.$disabled.' onchange="'.$price_action.'" name="custom_price'.$field[2].'">'.$custom_select.'</select>', $theForm);
				}
			}
			$customPrices++;
		} elseif($field[0] == "submit"){
			if(isset($field['value'])) $value = $field['value'];
			elseif(isset($field[1])) $value = $field[1];
			$action = '';
			if(!empty($validate_action)) $action .= 'easyreservations_send_validate(\'send\',\''.$formid.'\'); return false';
			$theForm = preg_replace('/\['.$fields.'\]/', '<input type="submit" title="'.$title.'" style="'.$style.'" class="easy-button" value="'.$value.'" '.$disabled.' onclick="'.$action.'"><span id="easybackbutton"></span>', $theForm);
		} else {
			$theForm = apply_filters('easy-form-tag', $theForm, $fields, $formid);
		}
	}

	if($roomfield == 0 && isset($atts['resource']) && $atts['resource'] > 0) $theForm .= '<input type="hidden" name="easyroom" value="'.$atts['resource'].'">';
	elseif($roomfield == 0 && isset($_POST['easyroom'])) $theForm .= '<input type="hidden" name="easyroom" value="'.$_POST['easyroom'].'">';
	//if(!$tofield) $theForm .= '<input type="hidden" name="nights" id="easy-form-units" value="0">';
	$finalformedgesremoved = str_replace(array('[', ']'), '', $theForm);
	if($final_form == '') $final_form.='<div class="easyFrontendFormular" id="'.$formid.'" style="width:'.$atts['width'].'%"><form  method="post" id="easyFrontendFormular" name="easyFrontendFormular">'.$form_name.'<input name="easynonce" type="hidden" value="'.wp_create_nonce('easy-user-add').'"><input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">'.$finalformedgesremoved.'<!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org --></form></div>';
	if(isset($_POST) && !empty($_POST))	$easyreservations_script .= 'var posted_array = '.json_encode($_POST).';for(var i in posted_array){ if(jQuery("*[name="+i+"]").attr("type") == "checkbox") jQuery("*[name="+i+"]").attr("checked", "checked");  else jQuery("*[name="+i+"]").val(posted_array[i]); } ';
	if(!empty($price_action)){
		if(!function_exists('wpseo_load_textdomain')) $easyreservations_script .= 'if(window.easyreservations_send_price) easyreservations_send_price(\''.$formid.'\'); else ';
		$easyreservations_script .= 'jQuery(document).ready(function(){easyreservations_send_price(\''.$formid.'\');});';
	}

	$popuptemplate = '<span class="easy_validate_message">'.$atts['validate'].'</span>';
	if(!empty($atts['subvalidate'])) $popuptemplate.= '<span class="easy_validate_message_sub">'.$atts['subvalidate'].'</span>';
	$popuptemplate.= '<table id="easy_overlay_table"><thead><tr>';
	$popuptemplate.= '<th>'.__('Time', 'easyReservations').'</th>';
	$popuptemplate.= '<th>'.__($atts['resourcename']).'</th>';
	if($atts['pers'] && $atts['pers'] == 1) $popuptemplate.= '<th>'.__('Persons', 'easyReservations').'</th>';
	$popuptemplate.= '<th>'.__('Price', 'easyReservations').'</th>';
	$popuptemplate.= '<th></th></tr></thead><tbody id="easy_overlay_tbody"></tbody></table>';
	$popuptemplate.= '<input onclick="easyAddAnother();" type="button" value="'.__('Add another reservation', 'easyReservations').'">';
	$popuptemplate.= '<input class="easy_overlay_submit"  type="button" onclick="easyFormSubmit(1);" value="'.__('Submit all reservations', 'easyReservations').'">';

	$easyreservations_script.= str_replace(array("\n","\r"), '', trim('var easyReservationAtts = '.json_encode($atts).';var easyInnerlayTemplate = "'.addslashes($popuptemplate).'";'));;
	if(!empty($atts['datefield'])) define('EASYDATEFIELD', $atts['datefield']);
	add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker');
	if(isset($final)) return $final;

	if($info_box !== false){
		$all_rooms = easyreservations_get_rooms(true);
		if(isset($info_box['img']) && ($info_box['img'] == "yes" || $info_box['img'] == "true")){
			if(isset($info_box['img_y']) && is_numeric($info_box['img_y'])) $width = $info_box['img_y'];
			if(isset($info_box['img_x']) && is_numeric($info_box['img_x'])) $height = $info_box['img_x'];
			if(isset($width) && isset($height)) $size = array($width,$height);
			else $size = 'post-thumbnail';
			$image = 1;
		} else $image = 0;

		if(isset($info_box['title']) && ($info_box['title'] == "yes" || $info_box['title'] == "true")) $title = 1;
		else $title = 0;

		if(isset($info_box['content']) && $info_box['content'] != "off" && $info_box['content'] > 0) $content = 1;
		else $content = 0;

		if(isset($info_box['excerpt']) && is_numeric($info_box['excerpt'])) $excerpt = 1;
		else $excerpt = 0;

		if($excerpt == 0 && $title == 0 && $content == 0){
			$class = ' only-image';
			$only = 1;
		} else {
			$class = '';
			$only = 0;
		}

		if(isset($info_box['theme'])){
			$class .= ' '.$info_box['theme'];
			$theme = $info_box['theme'];
		} else $theme = 'medium';
		if(isset($info_box['style'])) $style = $info_box['style'];

		foreach($all_rooms as $key => $resource){
			$all_rooms[$key] = (array) $resource;
			if($image == 1) $all_rooms[$key]['thumb'] = get_the_post_thumbnail($resource->ID, $size,array('class' => 'easy_infobox_thumb'));
			if($content == 1 && is_numeric($info_box['content'])) $all_rooms[$key]['post_content'] = html_entity_decode(strip_tags(substr( $all_rooms[$key]['post_content'], 0 , $info_box['content'])));
			if($excerpt == 1){
				$content_post = get_post($resource->ID);
				$all_rooms[$key]['excerpt'] = $content_post->post_excerpt;	
			}
		}
		$all_rooms = json_encode($all_rooms);
		$easyreservations_script.= <<<JAVASCRIPT
		var all_resoures_array=$all_rooms;
		function easy_load_infobox(){
			var res = jQuery("#easyFrontendFormular *[name='easyroom']").val();
			if(res == ''){
				res = document.getElementsByName('easyroom');
				res = res[0].value;
			}
			var res_info = all_resoures_array[res];
			var start = '<\div class="easy_infobox_border$class" style="$style">';
			var content = "";
			if($only == 1) jQuery("#resource_infobox").addClass("only-image");
			if($title == 1) content += '<span class="easy_infobox_title">'+res_info["post_title"]+'</span>';
			if(res_info["thumb"] && "$theme" == "big") content += res_info["thumb"].replace('\"', '');
			else if(res_info["thumb"]) content = res_info["thumb"]+content;
			if(res_info["excerpt"]) content += '<span class="easy_infobox_excerpt">'+res_info["excerpt"]+'</span>';
			if($content == 1) content += '<span class="easy_infobox_content">'+res_info["post_content"]+'</span>';
			content+='</\div>';
			jQuery("#resource_infobox").css("display","none");
			jQuery("#resource_infobox").html(start+content);
			jQuery("#resource_infobox").fadeIn("slow");
		}
		jQuery(document).ready(function(){
			easy_load_infobox();
			jQuery("*[name=easyroom]").bind("change", false, function(){ easy_load_infobox(); });
		});
JAVASCRIPT;
	} else {
		easyreservations_load_resources();
		global $the_rooms_array;
		$rooms = array();
		foreach($the_rooms_array as $key => $resource){
			$rooms[$key] = array( 'post_title' => __($resource->post_title));
		}
		$easyreservations_script .= 'var all_resoures_array='.json_encode($rooms).';';
	}
	return $final_form;
}

function easyreservations_make_datepicker(){
	$array = array('easy-form-from', 'easy-form-to');

	if(defined('EASYDATEFIELD')){
		$newfields = explode(',', EASYDATEFIELD);
		$array = array_merge($array, $newfields);
	}
	easyreservations_build_datepicker(0, $array);
}
?>