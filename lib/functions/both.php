<?php
	/**
	* 	@functions for admin and frontend 
	*/
	function easyreservations_get_pending(){
		global $wpdb;

		$count = $wpdb->get_var("SELECT COUNT(*) as Num FROM ".$wpdb->prefix ."reservations WHERE approve='' AND arrival > NOW()");
		return $count;
	}

	function easyreservations_load_both_stylesheet(){
		wp_enqueue_style('both', RESERVATIONS_URL . 'css/both.css', false);
	}
	add_action('wp_enqueue_scripts', 'easyreservations_load_both_stylesheet');
	add_action('admin_enqueue_scripts', 'easyreservations_load_both_stylesheet');

	function easyreservation_resource_init() {
		$labels = array(
			'name' => _x('Resources', 'easyReservations'),
			'singular_name' => _x('Resource', 'easyReservations'),
			'add_new' => _x('Add Resource', 'easyReservations'),
			'add_new_item' => __('Add New Resource', 'easyReservations'),
			'edit_item' => __('Edit Resource', 'easyReservations'),
			'new_item' => __('New Resource', 'easyReservations'),
			'all_items' => __('All Resources', 'easyReservations'),
			'view_item' => __('View Resource', 'easyReservations'),
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => array('slug' => 'resource'), 
			'show_in_menu' => false, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => false, 
			'hierarchical' => true,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'categorys', 'page-attributes' )
		); 
		register_post_type('easy-rooms',$args);
		register_post_type('easy-offers');
	}
	add_action( 'init', 'easyreservation_resource_init' );

	function easyreservations_get_time_pattern(){
		$reservations_settings = get_option("reservations_settings");
		if(isset($reservations_settings['time_format'])) return $reservations_settings['time_format'];
		else return 'H:i';
	}

	function easy_init_sessions() {
		if (!session_id() && !headers_sent()) {
			session_start();
		}
	}
	add_action('init', 'easy_init_sessions');

	function easyreservations_admin_bar(){
		if(current_user_can('edit_posts')){
			global $wp_admin_bar;

			$pending_reservations_cnt = easyreservations_get_pending();
			$pending = $pending_reservations_cnt != 0 ? '<span class="ab-label">'.$pending_reservations_cnt.'</span>': '';

			$wp_admin_bar->add_node( array(
				'id' => 'reservations',
				'title' => '<span class="er-adminbar-icon"></span>'.$pending,
				'href' => admin_url( 'admin.php?page=reservations#pending'),
				'meta' => array('class' => 'er-adminbar-item')
			) );
			$wp_admin_bar->add_node( array(
				'parent' => 'reservations',
				'id' => 'reservations-new',
				'title' => 'New',
				'href' => admin_url( 'admin.php?page=reservations&add'),
			) );
			$wp_admin_bar->add_node( array(
				'parent' => 'reservations',
				'id' => 'reservations-pending',
				'title' => 'Pending',
				'href' => admin_url( 'admin.php?page=reservations#pending'),
			) );
			$wp_admin_bar->add_node( array(
				'parent' => 'reservations',
				'id' => 'reservations-nurrent',
				'title' => 'Current',
				'href' => admin_url( 'admin.php?page=reservations#current'),
			) );
		}
	}

	add_action( 'admin_bar_menu', 'easyreservations_admin_bar', 999 );

	function easyreservations_wpml_resources($array){
		$array['easy-rooms'] = get_post_type_object('easy-rooms');
		return $array;
	}
	add_filter('get_translatable_documents', 'easyreservations_wpml_resources', 10, 1);

	$easyreservations_script = '';

	function easyreservations_print_footer_scripts(){
		global $easyreservations_script;
		if(!empty($easyreservations_script)) echo '<script type="text/javascript">'.$easyreservations_script.'</script>';
	}

	add_action('wp_print_footer_scripts', 'easyreservations_print_footer_scripts', 999);
	add_action('admin_print_footer_scripts', 'easyreservations_print_footer_scripts', 999);

/**
	* Format string into money
	*
	* @since 1.3
	*
	* @param int $amout amount of money to format
	* @param int 1 = currency sign | 0 = without
	* @return string formated money
	*/

	function easyreservations_format_money($amount, $mode=0, $dig = 2){
		if($amount == '') $amount = 0;
		$reservations_settings = get_option("reservations_settings");
		$currency_settings = $reservations_settings['currency'];
		if(!is_array($currency_settings)) $currency_settings = array('sign' => $currency_settings, 'place' => 0, 'whitespace' => 1, 'divider1' => '.', 'divider2' => ',', 'decimal' => 1);

		if($amount < 0 || substr($amount,0,1) == '-'){
			$amount = substr($amount, 1);
			$add = '-';
		} else $add = '';

		if($currency_settings['decimal'] == 1) $dig = 2;
		else $dig = 0;
		
		$money = $add.number_format((float)$amount,$dig,$currency_settings['divider2'],$currency_settings['divider1']);
		
		if($mode == 1){
			$white = $currency_settings['whitespace'] == 1 ?  ' ' : '';

			if($currency_settings['place'] == 0) $money = $money.$white.'&'.$currency_settings['sign'].';';
			else $money = '&'.$currency_settings['sign'].';'.$white.$money;
		}
		return $money;
	}

	if(!function_exists('easyreservations_loga')){
		function easyreservations_loga(){
			return false;
		}
	}

	/**
	*	Repair incorrect input, checks if string can be a price (money) -> returns the price or error
	*
	*	$price = a string to check
	*/

	function easyreservations_check_price($price){
		$newPrice = str_replace(",", ".", $price);
		return ( preg_match("/^[\-]{0,1}[0-9]+[\.]?[0-9]*$/", $newPrice)) ? $newPrice : false;
	}
	
	function easyreservations_load_resources($interval = false){
    global $the_rooms_array;
		if(empty($the_rooms_array)) $the_rooms_array = easyreservations_get_rooms();
		if($interval){
			global $the_rooms_intervals_array;
			if(empty($the_rooms_intervals_array)) $the_rooms_intervals_array = easyreservations_get_rooms_intervals();
		}
	}

	function easyreservations_get_rooms($content=false, $check=false, $user = false){
		global $wpdb;
		if($content) $con = ", post_content"; else $con = '';

		$rooms = $wpdb->get_results("SELECT ID, post_title, menu_order $con FROM ".$wpdb->prefix ."posts WHERE post_type='easy-rooms' AND post_status!='auto-draft' ORDER BY menu_order ASC");
		if(function_exists('icl_object_id')){
			if(defined('POLYLANG_VERSION')){
				if(is_admin()){
					$blog_current_lang = !empty($_GET['lang']) && !is_numeric($_GET['lang']) ? $_GET['lang'] :
						(($lg = get_user_meta(get_current_user_id(), 'pll_filter_content', true)) ? $lg : 'all');
				} else $blog_current_lang = pll_current_language();
				$default_lang = pll_default_language();
			} else {
				$wpml_options = get_option( 'icl_sitepress_settings' );
				$default_lang = $wpml_options['default_language'];

				if(defined('ICL_LANGUAGE_CODE')) $blog_current_lang = ICL_LANGUAGE_CODE;
				else {
					$blog_lang = get_option('WPLANG');
					if(!$blog_lang && defined('WPLANG') && WPLANG != '') $blog_lang = WPLANG;
					if(!$blog_lang) $blog_lang = 'en';

					$lang_locales = array( 'en_US' => 'en', 'af' => 'af', 'ar' => 'ar', 'bn_BD' => 'bn', 'eu' => 'eu', 'be_BY' => 'be', 'bg_BG' => 'bg', 'ca' => 'ca', 'zh_CN' => 'zh-hans', 'zh_TW' => 'zh-hant', 'hr' => 'hr', 'cs_CZ' => 'cs', 'da_DK' => 'da', 'nl_NL' => 'nl', 'eo' => 'eo', 'et' => 'et', 'fo' => 'fo', 'fi_FI' => 'fi', 'fr_FR' => 'fr', 'gl_ES' => 'gl', 'ge_GE' => 'ka', 'de_DE' => 'de', 'el' => 'el', 'he_IL' => 'he', 'hu_HU' => 'hu', 'is_IS' => 'is', 'id_ID' => 'id', 'it_IT' => 'it', 'ja' => 'ja', 'km_KH' => 'km', 'ko_KR' => 'ko', 'ku' => 'ku', 'lv' => 'lv', 'lt' => 'lt', 'mk_MK'  => 'mk', 'mg_MG' => 'mg', 'ms_MY' => 'ms', 'ni_ID' => 'ni', 'nb_NO' => 'nb', 'fa_IR' => 'fa', 'pl_PL' => 'pl', 'pt_PT' => 'pt-pt', 'pt_BR' => 'pt-br', 'ro_RO' => 'ro', 'ru_RU' => 'ru', 'sr_RS' => 'sr', 'si_LK' => 'si', 'sk_SK' => 'sk', 'sl_SI' => 'sl', 'es_ES' => 'es', 'su_ID' => 'su', 'sv_SE' => 'sv', 'tg' => 'tg', 'th' => 'th', 'tr' => 'tr', 'uk_UA' => 'uk', 'ug' => 'ug', 'uz_UZ' => 'uz', 'vi' => 'vi', 'cy' => 'cy' );
					if(isset($lang_locales[$blog_lang])) $blog_current_lang = $lang_locales[$blog_lang];
					else {
						$exp = explode('_',$blog_lang);
						$blog_current_lang = $exp[0];
					}
				}
			}

			foreach ($rooms as $key => $id){
				$current_lang_id = icl_object_id($id->ID,'easy-rooms', false, $blog_current_lang);
				$default_lang_id = icl_object_id($id->ID,'easy-rooms', $id->ID, $default_lang);

				if($default_lang_id == $id->ID && !is_null($current_lang_id)){
					$new_room = $wpdb->get_results("SELECT post_title $con FROM ".$wpdb->prefix ."posts WHERE ID='$current_lang_id' AND post_type='easy-rooms' AND post_status!='auto-draft' ORDER BY menu_order ASC");
					if($content) $rooms[$key]->post_content = $new_room[0]->post_content;
					$rooms[$key]->post_title = $new_room[0]->post_title;
				} elseif($default_lang_id !== $id->ID && !is_null($current_lang_id)){
					unset($rooms[$key]);
					continue;
				}
			}
		}

		$resources = array();
		foreach($rooms as $key => $room){
			$resources[$room->ID] = $room;
			if($check){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if(!empty($get_role) && ((!$user && !current_user_can($get_role)) || ($user && !user_can($user, $get_role)))){
					unset($resources[$room->ID]);
				}
			}
		}

		return $resources;
	}

	$the_rooms_array = array();

	function easyreservations_resource_options($selected='', $check=0, $exclude= '', $addslashes = false){
		$rooms = easyreservations_get_rooms(0, $check);
		$rooms_options = '';
		foreach( $rooms as $room ){
			if(empty($exclude) || !in_array($room->ID, $exclude)){
				if($addslashes) $room->post_title = addslashes($room->post_title);
				$select = !empty($selected) && $selected == $room->ID ? ' selected="selected"' : '';
				$rooms_options .= '<option value="'.$room->ID.'"'.$select.'>'.__($room->post_title).'</option>';
			}
		}
		return $rooms_options;
	}

	function easyreservations_get_rooms_intervals(){
		global $the_rooms_array;
		$rooms = $the_rooms_array;
		$room_intervals = array();
		if(!empty($rooms)){
			foreach( $rooms as $room ){
				$meta = get_post_meta($room->ID, 'easy-resource-interval', TRUE);
				if($meta) $room_intervals[$room->ID] = $meta;
				else $room_intervals[$room->ID] = 86400;
			}
		}
		return $room_intervals;
	}
	
	$the_rooms_intervals_array = array();

	function easyreservations_interval_infos($interval= 0, $mode = 0, $singular = 0){
		if($interval == 3600){
			$string = _n('hour', 'hours', $singular, 'easyReservations');
		} elseif($interval == 86400){
			$string = _n('day', 'days', $singular, 'easyReservations');
		} elseif($interval == 604800){
			$string = _n('week', 'weeks', $singular, 'easyReservations');
		} elseif($interval == 2592000){
			$string = _n('month', 'months', $singular, 'easyReservations');
		} else $string = _n('time', 'times', $singular, 'easyReservations');

		if($mode) return ucfirst($string);
		return $string;
	}

	function easyreservations_get_interval($interval = 0, $resourceID = 0, $mode = 0){
		if($interval == 0) $interval = get_post_meta($resourceID, 'easy-resource-interval', TRUE);
		if($mode == 0) return $interval;
		else {
			if($interval == 3600) return 3600;
			else return 86400;
		}
	}

	function easyreservations_get_nights($interval, $arrival, $departure, $mode = 1){
		$number = ($departure-$arrival) / easyreservations_get_interval($interval, 0,  $mode);
		$significance = 0.01;
		return ( is_numeric($number)) ? (ceil(ceil($number/$significance)*$significance)) : false;
	}

	/**
	*	Returns an array of all countrys
	*
	*/
	function easyReservations_country_array(){

		return array( 'AF'=>'Afghanistan', 'AL'=>'Albania', 'DZ'=>'Algeria', 'AS'=>'American Samoa', 'AD'=>'Andorra', 'AO'=>'Angola', 'AI'=>'Anguilla', 'AQ'=>'Antarctica', 'AG'=>'Antigua And Barbuda', 'AR'=>'Argentina', 'AM'=>'Armenia', 'AW'=>'Aruba', 'AU'=>'Australia', 'AT'=>'Austria', 'AZ'=>'Azerbaijan', 'BS'=>'Bahamas', 'BH'=>'Bahrain', 'BD'=>'Bangladesh', 'BB'=>'Barbados', 'BY'=>'Belarus', 'BE'=>'Belgium', 'BZ'=>'Belize', 'BJ'=>'Benin', 'BM'=>'Bermuda', 'BT'=>'Bhutan', 'BO'=>'Bolivia', 'BA'=>'Bosnia And Herzegovina', 'BW'=>'Botswana', 'BV'=>'Bouvet Island', 'BR'=>'Brazil', 'IO'=>'British Indian Ocean Territory', 'BN'=>'Brunei', 'BG'=>'Bulgaria', 'BF'=>'Burkina Faso', 'BI'=>'Burundi', 'KH'=>'Cambodia', 'CM'=>'Cameroon', 'CA'=>'Canada', 'CV'=>'Cape Verde', 'KY'=>'Cayman Islands', 'CF'=>'Central African Republic', 'TD'=>'Chad', 'CL'=>'Chile', 'CN'=>'China', 'CX'=>'Christmas Island', 'CC'=>'Cocos (Keeling) Islands', 'CO'=>'Colombia', 'KM'=>'Comoros', 'CG'=>'Congo', 'CK'=>'Cook Islands', 'CR'=>'Costa Rica', 'CI'=>'Cote D\'Ivorie (Ivory Coast)', 'HR'=>'Croatia (Hrvatska)', 'CU'=>'Cuba', 'CY'=>'Cyprus', 'CZ'=>'Czech Republic', 'CD'=>'Democratic Republic Of Congo (Zaire)', 'DK'=>'Denmark', 'DJ'=>'Djibouti', 'DM'=>'Dominica', 'DO'=>'Dominican Republic', 'TP'=>'East Timor', 'EC'=>'Ecuador', 'EG'=>'Egypt', 'SV'=>'El Salvador', 'GQ'=>'Equatorial Guinea', 'ER'=>'Eritrea', 'EE'=>'Estonia', 'ET'=>'Ethiopia', 'FK'=>'Falkland Islands (Malvinas)', 'FO'=>'Faroe Islands', 'FJ'=>'Fiji', 'FI'=>'Finland', 'FR'=>'France', 'FX'=>'France, Metropolitan', 'GF'=>'French Guinea', 'PF'=>'French Polynesia', 'TF'=>'French Southern Territories', 'GA'=>'Gabon', 'GM'=>'Gambia', 'GE'=>'Georgia', 'DE'=>'Germany', 'GH'=>'Ghana', 'GI'=>'Gibraltar', 'GR'=>'Greece', 'GL'=>'Greenland', 'GD'=>'Grenada', 'GP'=>'Guadeloupe', 'GU'=>'Guam', 'GT'=>'Guatemala', 'GN'=>'Guinea', 'GW'=>'Guinea-Bissau', 'GY'=>'Guyana', 'HT'=>'Haiti', 'HM'=>'Heard And McDonald Islands', 'HN'=>'Honduras', 'HK'=>'Hong Kong', 'HU'=>'Hungary', 'IS'=>'Iceland', 'IN'=>'India', 'ID'=>'Indonesia', 'IR'=>'Iran', 'IQ'=>'Iraq', 'IE'=>'Ireland', 'IL'=>'Israel', 'IT'=>'Italy', 'JM'=>'Jamaica', 'JP'=>'Japan', 'JO'=>'Jordan', 'KZ'=>'Kazakhstan', 'KE'=>'Kenya', 'KI'=>'Kiribati', 'KW'=>'Kuwait', 'KG'=>'Kyrgyzstan', 'LA'=>'Laos', 'LV'=>'Latvia', 'LB'=>'Lebanon', 'LS'=>'Lesotho', 'LR'=>'Liberia', 'LY'=>'Libya', 'LI'=>'Liechtenstein', 'LT'=>'Lithuania', 'LU'=>'Luxembourg', 'MO'=>'Macau', 'MK'=>'Macedonia', 'MG'=>'Madagascar', 'MW'=>'Malawi', 'MY'=>'Malaysia', 'MV'=>'Maldives', 'ML'=>'Mali', 'MT'=>'Malta', 'MH'=>'Marshall Islands', 'MQ'=>'Martinique', 'MR'=>'Mauritania', 'MU'=>'Mauritius', 'YT'=>'Mayotte', 'MX'=>'Mexico', 'FM'=>'Micronesia', 'MD'=>'Moldova', 'MC'=>'Monaco', 'MN'=>'Mongolia', 'MS'=>'Montserrat', 'MA'=>'Morocco', 'MZ'=>'Mozambique', 'MM'=>'Myanmar (Burma)', 'NA'=>'Namibia', 'NR'=>'Nauru', 'NP'=>'Nepal', 'NL'=>'Netherlands', 'AN'=>'Netherlands Antilles', 'NC'=>'New Caledonia', 'NZ'=>'New Zealand', 'NI'=>'Nicaragua', 'NE'=>'Niger', 'NG'=>'Nigeria', 'NU'=>'Niue', 'NF'=>'Norfolk Island', 'KP'=>'North Korea', 'MP'=>'Northern Mariana Islands', 'NO'=>'Norway', 'OM'=>'Oman', 'PK'=>'Pakistan', 'PW'=>'Palau', 'PA'=>'Panama', 'PG'=>'Papua New Guinea', 'PY'=>'Paraguay', 'PE'=>'Peru', 'PH'=>'Philippines', 'PN'=>'Pitcairn', 'PL'=>'Poland', 'PT'=>'Portugal', 'PR'=>'Puerto Rico', 'QA'=>'Qatar', 'RE'=>'Reunion', 'RO'=>'Romania', 'RU'=>'Russia', 'RW'=>'Rwanda', 'SH'=>'Saint Helena', 'KN'=>'Saint Kitts And Nevis', 'LC'=>'Saint Lucia', 'PM'=>'Saint Pierre And Miquelon', 'VC'=>'Saint Vincent And The Grenadines', 'SM'=>'San Marino', 'ST'=>'Sao Tome And Principe', 'SA'=>'Saudi Arabia', 'SN'=>'Senegal', 'SC'=>'Seychelles', 'SL'=>'Sierra Leone', 'SG'=>'Singapore', 'SK'=>'Slovak Republic', 'SI'=>'Slovenia', 'SB'=>'Solomon Islands', 'SO'=>'Somalia', 'ZA'=>'South Africa', 'GS'=>'South Georgia And South Sandwich Islands', 'KR'=>'South Korea', 'ES'=>'Spain', 'LK'=>'Sri Lanka', 'SD'=>'Sudan', 'SR'=>'Suriname', 'SJ'=>'Svalbard And Jan Mayen', 'SZ'=>'Swaziland', 'SE'=>'Sweden', 'CH'=>'Switzerland', 'SY'=>'Syria', 'TW'=>'Taiwan', 'TJ'=>'Tajikistan', 'TZ'=>'Tanzania', 'TH'=>'Thailand', 'TG'=>'Togo', 'TK'=>'Tokelau', 'TO'=>'Tonga', 'TT'=>'Trinidad And Tobago', 'TN'=>'Tunisia', 'TR'=>'Turkey', 'TM'=>'Turkmenistan', 'TC'=>'Turks And Caicos Islands', 'TV'=>'Tuvalu', 'UG'=>'Uganda', 'UA'=>'Ukraine', 'AE'=>'United Arab Emirates', 'UK'=>'United Kingdom', 'US'=>'United States', 'UM'=>'United States Minor Outlying Islands', 'UY'=>'Uruguay', 'UZ'=>'Uzbekistan', 'VU'=>'Vanuatu', 'VA'=>'Vatican City (Holy See)', 'VE'=>'Venezuela', 'VN'=>'Vietnam', 'VG'=>'Virgin Islands (British)', 'VI'=>'Virgin Islands (US)', 'WF'=>'Wallis And Futuna Islands', 'EH'=>'Western Sahara', 'WS'=>'Western Samoa', 'YE'=>'Yemen', 'YU'=>'Yugoslavia', 'ZM'=>'Zambia', 'ZW'=>'Zimbabwe' );

	}
	/**
	*	Returns options for a country select
	*
	*	$sel = (optional) selected country
	*/
	function easyreservations_country_options($sel = ''){

		$countryArray = easyReservations_country_array();
		$country_options = '';
		foreach($countryArray as $short => $country){
			$select = $short == $sel ? ' selected' : '';
			$country_options .= '<option value="'.$short.'"'.$select.'>'.htmlentities($country,ENT_QUOTES).'</option>';
		}

		return $country_options;
	}

	/**
	*	Returns full name of a country
	*
	*	$country = Index of country
	*/

	function easyreservations_country_name($country){
		if(!empty($country)){
			$countryArray = easyReservations_country_array();
			return $countryArray[$country];
		}
		return $country;
	}

	function get_custom_submit($array, $error){
		global $the_rooms_intervals_array;
		if(isset($_POST['formname']))$theForm = stripslashes(get_option('reservations_form_'.$_POST['formname']));
		else $theForm = stripslashes(get_option("reservations_form"));
		if(empty($theForm)) $theForm = stripslashes(get_option("reservations_form"));
		$custom_form = array();

		$theForm = apply_filters( 'easy-form-content', $theForm);
		$tags = easyreservations_shortcode_parser($theForm, true);
		$custom_fields = get_option('reservations_custom_fields');
		$custom_price = array();

		foreach($tags as $fields){
			$field=shortcode_parse_atts( $fields);
			if($field[0]=="custom"){
				if(isset($field["id"])){
					if(isset($_POST['easy-new-custom-'.$field["id"]])){
						$custom = array( 'type' => 'cstm', 'mode' => 'edit', 'id' => $field["id"], 'value' => stripslashes($_POST['easy-new-custom-'.$field["id"]]));
						if(isset($custom_fields['fields'][$field["id"]]['price'])) $custom_price[] = $custom;
						else $custom_form[] = $custom;
					} elseif(isset($custom_fields[$field["id"]]['required'])){
						$error.= '<li>'.sprintf(__( '%s is required', 'easyReservations'), $custom_fields[$field["id"]]['title']).'</li>';
					}
				} else {
					if(isset($_POST['easy-custom-'.$field[2]]) && !empty($_POST['easy-custom-'.$field[2]])){
						$custom_form[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => stripslashes($_POST['easy-custom-'.$field[2]]));
					} else {
						if(end($field)  == "*") $error.= '<li>'.sprintf(__( '%s is required', 'easyReservations'), ucfirst($field[2])).'</li>';
					}
				}
			} elseif($field[0]=="price"){
				if(isset($_POST['custom_price'.$field[2]])){
					$nights = easyreservations_get_nights($the_rooms_intervals_array[$array['resource']], $array['arrival'],$array['departure']);
					$explodeprice = explode(":",$_POST['custom_price'.$field[2]]);
					if(end($field) == 'pp') $theprice = $explodeprice[1] * ($array['adults']+$array['childs']);
					elseif(end($field) == 'pa') $theprice = $explodeprice[1] * $array['adults'];
					elseif(end($field) == 'pan') $theprice = $explodeprice[1] * $array['adults'] * $nights;
					elseif(end($field) == 'pc') $theprice = $explodeprice[1] * $array['childs'];
					elseif(end($field) == 'pcn') $theprice = $explodeprice[1] * $array['childs'] * $nights;
					elseif(end($field)  == 'pn') $theprice = $explodeprice[1] * $nights;
					elseif(end($field)  == 'pb') $theprice = $explodeprice[1] * $nights * ($array['adults']+$array['childs']);
					else $theprice = $explodeprice[1];
					$custom_price[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $field[2], 'value' => stripslashes($explodeprice[0]), 'amount' => $theprice );
				}
			}
		}
		return array($custom_form, $custom_price, $error);
	}

	function easyreservations_generate_custom_field($id, $sel = false, $after = ''){
		$custom_fields = get_option('reservations_custom_fields');
		$form_field = '';
		if(isset($custom_fields['fields'][$id])){
			$custom_field = $custom_fields['fields'][$id];
			if($custom_field['type'] == 'text'){
				$value = $sel ? $sel : '';
				$form_field = '<input type="text" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" value="'.$value.'" '.$after.'>';
			} elseif($custom_field['type'] == 'area'){
				$value = $sel ? $sel : '';
				$form_field = '<textarea name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'"'.$after.'>'.$value.'</textarea>';
			} elseif($custom_field['type'] == 'check'){
				foreach($custom_field['options'] as $opt_id => $option){
					$checked = $sel || (!$sel && isset($option['checked'])) ? ' checked="checked"' : '';
					$form_field .= '<input type="checkbox" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" value="'.$opt_id.'" '.$checked.$after.'>';
				}
			} elseif($custom_field['type'] == 'radio'){
				$form_field .= '<span class="radio">';
				foreach($custom_field['options'] as $opt_id => $option){
					$checked = ($sel && $sel == $opt_id) || (!$sel && $option['checked']) ? ' checked="checked"' : '';
					$form_field .= '<span><input type="radio" name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" value="'.$opt_id.'" '.$checked.$after.'> '.$option['value'].'</span>';
				}
				$form_field .= '</span>';
			} elseif($custom_field['type'] == 'select'){
				$form_field = '<select name="easy-new-custom-'.$id.'" id="easy-new-custom-'.$id.'" '.$after.'>';
				foreach($custom_field['options'] as $opt_id => $option){
					$checked = $sel && $sel == $opt_id ? ' selected="selected"' : '';
					$form_field .= '<option value="'.$opt_id.'"'.$checked.'>'.$option['value'].'</option>';
				}
				$form_field .= '</select>';
			}
		}
		return $form_field;

	}

	/**
	* Return numbered options for selects
	*
	*	$start = first number of options
	*	$end = last number of options
	*	$sel = (optional) selected option
	*/

	function easyreservations_num_options($start,$end,$sel=''){
		$return = '';
		if(is_array($start)){
			$plus = $start[1];
			$start = $start[0];
		}
		for($num = (int) $start; $num <= $end; $num++){
			$num_display = $num;
			$num_option = $num;
			if(strlen($start) == strlen($end) && $start < 10 && $end > 9 && $num < 10){
				$num_display = '0'.$num;
			} elseif(isset($plus)) $num_option += $plus;
			$selected = !empty($sel) && $num_option == $sel ? 'selected="selected"' : '';
			$return .= '<option value="'.$num_option.'" '.$selected.'>'.$num_display.'</option>';
		}
		return $return;
	}

	function easyreservations_time_options($time){
		$reservations_settings = get_option("reservations_settings");
		if(isset($reservations_settings['time_format'])){
			if($reservations_settings['time_format'] == 'H:i') $time_format = 'H';
			else $time_format = 'h a';
		}	else $time_format = 'H';
		$zero = strtotime('20.10.2010 00:00:00');
		$return = '';
		for($i = 0; $i <= 23; $i++){
			/*if($time_format == 'h' && ($i == 0 || $i == 12)){
				if($i == 0) $return .= '<optgroup label="'.__('AM', 'easyReservations').'">';
				else $return .= '</optgroup><optgroup label="'.__('PM', 'easyReservations').'">';
			}*/
			$h = date($time_format, $zero+($i * 3600));
			$return .= '<option value="'.$i.'" '.selected($time, $i, false).'>'.$h.'</option>';
		}
		//if($time_format == 'h') $return .= '</optgroup>';
		return $return;
	}
	
	function easyreservations_shortcode_parser($content, $usepattern = false, $define = false){
		if($usepattern){
			$pattern = '\\[';						 // Opening bracket
			if($define){
				$pattern.= '(\\[?)'					 // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
					.	'('.$define.')';					 // 2: Shortcode name
			}
			$pattern .= '\\b'                        // Word boundary
					. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
					.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
					.     '(?:'
					.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
					.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
					.     ')*?'
					. ')'
					. '(?:'
					.     '(\\/)'                        // 4: Self closing tag ...
					.     '\\]'                          // ... and closing bracket
					. '|'
					.     '\\]'                          // Closing bracket
					.     '(?:'
					.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
					.             '[^\\[]*+'             // Not an opening bracket
					.             '(?:'
					.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
					.                 '[^\\[]*+'         // Not an opening bracket
					.             ')*+'
					.         ')'
					.         '\\[\\/\\2\\]'             // Closing shortcode tag
					.     ')?'
					. ')'
					. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
			preg_match_all( '/'. $pattern .'/s', $content, $match);
			if($define) $return = $match[3];
			else $return = $match[1];
			$return = array_merge($return, array());
		} else {
			preg_match_all( '/\[.*\]/U', $content, $match);
			$return = $match[0];
		}
		$return = str_replace(array('[',']'), '', $return);

		return $return;
	}

	function easyreservations_check_val(){
		if(has_action( 'er_mod_inst', 'easyreservations_add_module_notice') && strlen((string) easyreservations_add_module_notice(true)) == 280) return true;
		else return false;
	}

	function easyreservations_generate_hidden_fields($array, $id = false){
		if($array){
			$return = '';
			$id_str = '';
			foreach($array as $key => $value){
				if($id) $id_str = ' id="'.$key.'" ';
				$return .= '<input type="hidden" name="'.$key.'" value="'.$value.'" '.$id_str.'>';
			}
			return $return;
		}
		return false;
	}

	function easyreservations_register_scripts(){
		wp_register_script('easyreservations_send_calendar', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_calendar.js' , array( "jquery" ), RESERVATIONS_VERSION);
		wp_register_script('easyreservations_send_price', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_price.js' , array( "jquery" ), RESERVATIONS_VERSION);
		wp_register_script('easyreservations_send_validate', WP_PLUGIN_URL.'/easyreservations/js/ajax/send_validate.js' , array( "jquery" ), RESERVATIONS_VERSION);
		wp_register_script('easyreservations_send_form', WP_PLUGIN_URL . '/easyreservations/js/ajax/form.js', array( "jquery" ), RESERVATIONS_VERSION);
		wp_register_script('easyreservations_data', WP_PLUGIN_URL . '/easyreservations/js/ajax/data.js', array( "jquery" ), RESERVATIONS_VERSION);
		easyreservations_load_resources(true);
		global $the_rooms_intervals_array;

		$lang = '';
		if(defined('ICL_LANGUAGE_CODE')) $lang = '?lang=' . ICL_LANGUAGE_CODE;
		elseif(function_exists('qtrans_getLanguage')) $lang = '?lang=' . qtrans_getLanguage();

		wp_localize_script( 'easyreservations_send_calendar', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php'.$lang ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easyreservations_send_price', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php'.$lang ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		wp_localize_script( 'easyreservations_send_validate', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php'.$lang ), 'plugin_url' => WP_PLUGIN_URL, 'interval' => json_encode($the_rooms_intervals_array) ) );
		$reservations_settings = get_option("reservations_settings");
		$reservations_currency = $reservations_settings['currency'];
		if(!is_array($reservations_currency)) $reservations_currency = array('sign' => $reservations_currency, 'place' => 0, 'whitespace' => 1, 'divider1' => '.', 'divider2' => ',', 'decimal' => 1);
		wp_localize_script( 'easyreservations_send_form', 'easyDate', array( 'ajaxurl' => admin_url( 'admin-ajax.php'.$lang ), 'currency' => $reservations_currency,  'easydateformat' => RESERVATIONS_DATE_FORMAT, 'interval' => json_encode($the_rooms_intervals_array) ) );

		wp_register_style('easy-frontend', WP_PLUGIN_URL . '/easyreservations/css/frontend.css', array(), RESERVATIONS_VERSION); // widget form style
		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/form.css')) wp_register_style('easy-form-custom', WP_PLUGIN_URL . '/easyreservations/css/custom/form.css', array(), RESERVATIONS_VERSION); // custom form style override
		wp_register_style('easy-form-little', WP_PLUGIN_URL . '/easyreservations/css/forms/form_little.css', array(), RESERVATIONS_VERSION); // widget form style
		wp_register_style('easy-form-none', WP_PLUGIN_URL . '/easyreservations/css/forms/form_none.css', array(), RESERVATIONS_VERSION);
		wp_register_style('easy-form-blue', WP_PLUGIN_URL . '/easyreservations/css/forms/form_blue.css', array(), RESERVATIONS_VERSION);

		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/calendar.css')) wp_register_style('easy-cal-custom', WP_PLUGIN_URL . '/easyreservations/css/custom/calendar.css', array(), RESERVATIONS_VERSION); // custom form style override
		wp_register_style('easy-cal-1', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_1.css', array(), RESERVATIONS_VERSION);
		wp_register_style('easy-cal-2', WP_PLUGIN_URL . '/easyreservations/css/calendar/style_2.css', array(), RESERVATIONS_VERSION);
	}

	function easyreservations_register_scripts_both(){
		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/datepicker.css')) $form1 = 'custom/datepicker.css'; else $form1 = 'jquery-ui.css';
		wp_register_style('datestyle', WP_PLUGIN_URL . '/easyreservations/css/'.$form1, array(), RESERVATIONS_VERSION);

		wp_register_script('easyreservations_js_both', WP_PLUGIN_URL.'/easyreservations/js/both.js' , array( "jquery" ), RESERVATIONS_VERSION);
		wp_localize_script('easyreservations_js_both', 'easy_both', array('date_format' => RESERVATIONS_DATE_FORMAT, 'time' => time(), 'offset' => date("Z")));
		wp_enqueue_script('easyreservations_js_both');
	}

	add_action('admin_enqueue_scripts', 'easyreservations_register_scripts_both');
	add_action('wp_enqueue_scripts', 'easyreservations_register_scripts_both');
	add_action('wp_enqueue_scripts', 'easyreservations_register_scripts');

	function easyreservations_get_roomname($number, $room, $roomnames = ''){
		$number = $number - 1;
		if(empty($number) && $number < 0) return $number;
		if(empty($roomnames)) $roomnames = get_post_meta($room, 'easy-resource-roomnames', TRUE);
		if(isset($roomnames[$number]) && !empty($roomnames[$number])) return $roomnames[$number];
		else return $number+1;
	}

	/**
	 * Get day or month names
	 * 
	 * @since 1.8
	 * 
	 * @param int $interval 0 for days - 1 for monthes
	 * @param int $substr number of characters to display 0=full
	 * @param int $date number of day/or month to retutn just that string
	 * @return array/string with name of date
	 */

	function easyreservations_get_date_name($interval = 0, $substr = 0, $date = false, $addslashes = false){
		$name = array();
		if($interval == 0){
			$name[] = __( 'Monday' , 'easyReservations' );
			$name[] = __( 'Tuesday' , 'easyReservations' );
			$name[] = __( 'Wednesday' , 'easyReservations' );
			$name[] = __( 'Thursday' , 'easyReservations' );
			$name[] = __( 'Friday' , 'easyReservations' );
			$name[] = __( 'Saturday' , 'easyReservations' );
			$name[] = __( 'Sunday' , 'easyReservations' );
		} else {
			$name[] = __( 'January' , 'easyReservations' );
			$name[] = __( 'February' , 'easyReservations' );
			$name[] = __( 'March' , 'easyReservations' );
			$name[] = __( 'April' , 'easyReservations' );
			$name[] = __( 'May' , 'easyReservations' );
			$name[] = __( 'June' , 'easyReservations' );
			$name[] = __( 'July' , 'easyReservations' );
			$name[] = __( 'August' , 'easyReservations' );
			$name[] = __( 'September' , 'easyReservations' );
			$name[] = __( 'October' , 'easyReservations' );
			$name[] = __( 'November' , 'easyReservations' );
			$name[] = __( 'December' , 'easyReservations' );
		}

		if($substr > 0 && function_exists('mb_internal_encoding')) mb_internal_encoding("UTF-8");
		foreach($name as $key => $day){
			if($substr > 0 && function_exists('mb_substr')) $name[$key] = mb_substr($day, 0, $substr);
			elseif($substr > 0){
				//$name[$key] = substr($day, 0, $substr);
				$name[$key] = htmlentities(substr(html_entity_decode($day), 0, $substr));
			}
			if($addslashes) $name[$key] = addslashes($name[$key]);
		}

		if($date !== false) return $name[$date];
		else return $name;
	}

	/**
	 * Print jQuery Code for Datepicker
	 * @param int $type 0 for standard 1 for frontend
	 */	
	function easyreservations_build_datepicker($type, $instances, $trans = false, $search = false){
		if(function_exists('mb_internal_encoding')){
			mb_internal_encoding("UTF-8");
			$function = 'mb_substr';
		} else $function = 'substr';
		
		$daysnames = easyreservations_get_date_name(0,0,false,true);
		$daynames = '["'.$daysnames[6].'", "'.$daysnames[0].'", "'.$daysnames[1].'", "'.$daysnames[2].'", "'.$daysnames[3].'", "'.$daysnames[4].'", "'.$daysnames[5].'"]';
		$daynamesshort = '["'.$function($daysnames[6],0, 3).'","'.$function($daysnames[0],0, 3).'","'.$function($daysnames[1],0, 3).'","'.$function($daysnames[2],0, 3).'","'.$function($daysnames[3],0, 3).'","'.$function($daysnames[4],0, 3).'","'.$function($daysnames[5],0, 3).'"]';
		$daynamesmin = '["'.$function($daysnames[6],0, 2).'","'.$function($daysnames[0],0, 2).'","'.$function($daysnames[1],0, 2).'","'.$function($daysnames[2],0, 2).'","'.$function($daysnames[3],0, 2).'","'.$function($daysnames[4],0, 2).'","'.$function($daysnames[5],0, 2).'"]';
		$monthes = easyreservations_get_date_name(1,0,false,true);
		$monthnames =  '["'.$monthes[0].'","'.$monthes[1].'","'.$monthes[2].'","'.$monthes[3].'","'.$monthes[4].'","'.$monthes[5].'","'.$monthes[6].'","'.$monthes[7].'","'.$monthes[8].'","'.$monthes[9].'","'.$monthes[10].'","'.$monthes[11].'"]';
		$monthnamesshort =  '["'.$function($monthes[0],0,3).'","'.$function($monthes[1],0,3).'","'.$function($monthes[2],0,3).'","'.$function($monthes[3],0,3).'","'.$function($monthes[4],0,3).'","'.$function($monthes[5],0,3).'","'.$function($monthes[6],0,3).'","'.$function($monthes[7],0,3).'","'.$function($monthes[8],0,3).'","'.$function($monthes[9],0,3).'","'.$function($monthes[10],0,3).'","'.$function($monthes[11],0,3).'"]';
		$translations = <<<EOF
dayNames: $daynames,
			dayNamesShort: $daynamesshort,
			dayNamesMin: $daynamesmin,
			monthNames: $monthnames,
			monthNamesShort: $monthnamesshort,
EOF;
		
		if($search) $search = 1;
		else $search = 2;

		if($trans === true) return $translations;
		elseif($trans) $format = $trans;
		else $format = RESERVATIONS_DATE_FORMAT;

		$jquery = '';
		if(isset($instances[1])) foreach($instances as $instance) $jquery .= '#'.$instance.',';
		else $jquery = '#'.$instances;
		$jquery = substr($jquery, 0, -1);

		if($format == 'Y/m/d') $dateformat = 'yy/mm/dd';
		elseif($format == 'm/d/Y') $dateformat = 'mm/dd/yy';
		elseif($format == 'd-m-Y') $dateformat = 'dd-mm-yy';
		elseif($format == 'Y-m-d') $dateformat = 'yy-mm-dd';
		elseif($format == 'd.m.Y') $dateformat = 'dd.mm.yy';

		if($type == 0){
			$datepicker = <<<EOF
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var dates = jQuery( "$jquery" ).datepicker({
					dateFormat: '$dateformat',
					minDate: 0,
					beforeShowDay: function(date){
						if($search == 2 && window.easydisabledays ){
								return easydisabledays(date, jQuery(this).parents("form:first").find( "[name=easyroom],#room" ).val());
						} else {
							return [true];
						}
					},
					$translations
					firstDay: 1,
					onSelect: function( selectedDate ){
						if(this.id == '$instances[0]'){
							var option = this.id == "$instances[0]" ? "minDate" : "maxDate",
							instance = jQuery( this ).data( "datepicker" ),
							date = jQuery.datepicker.parseDate( instance.settings.dateFormat ||	jQuery.datepicker._defaults.dateFormat,	selectedDate, instance.settings );
							dates.not( this ).datepicker( "option", option, date );
						}
						if(window.easyreservations_send_validate) easyreservations_send_validate(false, 'easyFrontendFormular');
						if(window.easyreservations_send_price) easyreservations_send_price('easyFrontendFormular');
					}
				});
			});
		</script>
EOF;
		} else {
			$datepicker = <<<EOF
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var dates = jQuery( "$jquery" ).datepicker({
					$translations
					dateFormat: '$dateformat',
					firstDay: 1
				});
			});
		</script>
EOF;
		}
		if($type == 0 && function_exists("easyreservations_header_datepicker_script")){
			easyreservations_header_datepicker_script();
		}
		echo $datepicker;
	}
	
	function easyreservations_generate_restrict($identifier_array){
		$return = '';
		foreach($identifier_array as $identifier) $return .= easyreservations_restrict_inputs((is_array($identifier) ? $identifier[0] : $identifier), (isset($identifier[1]) ? $identifier[1] : false), (isset($identifier[2]) ? $identifier[2] : false));
		if(!empty($return)) $return = '<script type="text/javascript">'.$return.'</script>';
		echo $return;
	}

	function easyreservations_restrict_inputs($identifier, $percent = false, $minus = false){
		if($percent) $percent = ' || (e.shiftKey && e.keyCode == 53)';
		else $percent = '';
		if($minus) $minus = ' || (e.keyCode == 189';
		else $minus = '';
		return 'jQuery(\''.$identifier.'\').keydown(function(e){if(e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 45 || (e.keyCode == 190 && !e.shiftKey) || (e.keyCode == 110 && !e.shiftKey) || (e.keyCode == 109 && !e.shiftKey) || (e.keyCode == 189 && !e.shiftKey)'.$percent.') return; else if(e.shiftKey || (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105 )) {e.preventDefault();}});';
	}

	function easyreservations_verify_nonce($nonce, $action = -1) {
		$i = wp_nonce_tick();
		// Nonce generated 0-12 hours ago
		if ( hash_equals(substr(wp_hash($i .'|'.$action . '|0', 'nonce'), -12, 10), $nonce) )
			return 1;
		// Nonce generated 12-24 hours ago
		if ( hash_equals( substr(wp_hash(($i - 1) .'|'.$action . '|0', 'nonce'), -12, 10) , $nonce ) )
			return 2;
		// Invalid nonce
		return false;
	}

	function easyreservations_calculate_out_summertime($timestamp, $begin){
		$diff = 0;
		if(version_compare(PHP_VERSION, '5.3.0') >= 0 && is_numeric($timestamp)){
			$timezone = new DateTimeZone(date_default_timezone_get ());
			$transitions = $timezone->getTransitions($begin, $timestamp);
			if(isset($transitions[1]) && $transitions[0]['offset'] != $transitions[1]['offset']){
				$diff = $transitions[1]['offset'] - $transitions[0]['offset'];
				//if($transitions[0]['offset'] < $transitions[1]['offset']) $diff = $transitions[0]['offset'] - $transitions[1]['offset'];
				//else $diff = $transitions[1]['offset'] - $transitions[0]['offset'];
			}
		}
		return ($timestamp-$diff);
	}



?>