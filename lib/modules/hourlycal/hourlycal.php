<?php
/*
Plugin Name: hourlyCalendar Module
Plugin URI: http://easyreservations.org/module/hourlycal/
Version: 1.1.2
Description: 3.3
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!
*/

add_action('wp_ajax_easyreservations_send_hourlycalendar', 'easyreservations_send_hourlycal_callback');
add_action('wp_ajax_nopriv_easyreservations_send_hourlycalendar', 'easyreservations_send_hourlycal_callback');

function easyreservations_send_hourlycal_callback(){
	date_default_timezone_set('UTC');
	easyreservations_load_resources(true);
	global $the_rooms_array, $the_rooms_intervals_array;
	check_ajax_referer( 'easy-hourlycalendar', 'security' );
	$atts = (array) $_POST['atts'];
	$room_count = get_post_meta($_POST['room'], 'roomcount', true);
	if(is_array($room_count)) $room_count = $room_count[0];
	$month_names = easyreservations_get_date_name(1);
	$day_names = easyreservations_get_date_name(0,2);

	$rand = $atts['id'];
	$divider = 1;
	$months = 1;
	$cell_count = 0;
	$resource_display = '';
	$between = $atts['end'] - $atts['start'] + 1;
	$keys = $between;

	if(isset($atts['monthes']) && preg_match('/^[0-9]+x{1}[0-9]+$/i', $atts['monthes'])){
		$explode_monthes = explode('x', $atts['monthes']);
		$months = $explode_monthes[0] * $explode_monthes[1];
		$divider = $explode_monthes[0];
	}

	if(function_exists('easyreservations_generate_multical') && $months != 1) $timenows = easyreservations_generate_multical($_POST['date'], $months, $atts['days']);
	else $timenows = array(strtotime(date("d.m.Y", time()))+($_POST['date']*86400));
	if(!isset($timenows[1])) $end = $timenows[0]+$atts['days']*86400-86400;
	else $end = $timenows[count($timenows)-1]+$atts['days']*86400-86400;

	$anf =  $timenows[0];
	if(date("m", $anf) == date("m", $end) ){
		$month=date("d", $anf).' - '.date("d", $end).'.'.$month_names[date("n", $end)-1].' '.date("Y", $anf);
	} else {
		if(date("y", $anf) == date("y", $end) ){
			$month=date("d", $anf).'.'.$month_names[date("n", $anf)-1].' - '.date("d", $end).'.'.$month_names[date("n", $end)-1].' '.date("Y", $anf);
		} else {
			$month=date("d", $anf).'.'.$month_names[date("n", $anf)-1].' '.date("Y", $anf).' - '.date("d", $end).'.'.$month_names[date("n", $end)-1].' '.date("Y", $end);
		}
	}

	if($atts['resource'] == "display") $resource_display = $the_rooms_array[$_POST['room']]->post_title;
	elseif($atts['resource'] == "select") $resource_display = '<select onchange="jQuery(\'input[name=room],select[name=easyroom]\').val(this.value);easyHourlyCalendars['.$rand.'].change(\'resource\', this.value);">'.easyreservations_resource_options($_POST['room'],0,$atts['exclude']).'</select>';
	elseif($atts['resource'] == "navi"){
		$options = '';
		foreach($the_rooms_array as $room){
			$class = $room->ID == $_POST['room'] ? 'class="active"' : '';
			$options .= '<a '.$class.' onclick="easyHourlyCalendars['.$rand.'].change(\'resource\', '.$room->ID.');">'.$room->post_title.'</a>';
		}
		$resource_display = '<div>'.$options.'</div>';
	}

	echo '<table class="hcalendar-table" cellpadding="0" cellspacing="0">';
		echo '<thead>';
			echo '<tr>';
				echo '<th>'.$month.' <div><a onClick="easyHClick = 0;easyHourlyCalendars['.$rand.'].change(\'date\', \''.($_POST['date']-$atts['interval']).'\');"><</a>';
				echo '<a onClick="easyHClick = 0;easyHourlyCalendars['.$rand.'].change(\'date\', \''.(-1).'\');">0</a><a onClick="easyHClick = 0;easyHourlyCalendars['.$rand.'].change(\'date\', \''.($_POST['date']+$atts['interval']).'\');">></a></div></th>';
				echo '<th style="text-align:right">'.$resource_display.'</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody style="text-align:center;white-space:nowrap;padding:0px">';
				echo '<tr>';
				echo '<td colspan="7" style="white-space:nowrap;padding:0px;margin:0px;">';

	if(count($timenows) > 1){
		$atts['width'] = $atts['width'] / $divider;
		$percent = 100 / $divider;
	} else $percent = 100;
	$month_count=0;

	foreach($timenows as $timenow){
		$month_count++;
		$setet=0;
		$yearnow=date("m", $timenow);
		$monthnow=date("d", $timenow);
		$key = '1'.$yearnow.$monthnow;
		$cell_count = $cell_count;

		/*if($monthnow-1 <= 0){
			$monthnowFix=13;
			$yearnowFix=$yearnow-1;
		} else {
			$monthnowFix=$monthnow;
			$yearnowFix=$yearnow;
		}*/

		$thewidth = $divider % 2 != 0 ? ($atts['width']).'%' : $percent.'%';
		$float = $month_count % $divider == 0 ? '' : 'float:left';

		echo '<table class="hcalendar-direct-table" style="width:'.$thewidth.';margin:0px;'.$float.'">';
			echo '<thead>';
				if($atts['header'] == 1) echo '<tr><th class="hcalendar-header-month" colspan="7">'.$month_names[date("n", $timenow)-1].'</th></tr>';
				echo '<tr>';
					echo '<th class="hcalendar-header-cell" style=""></th>';
					for($i = 0; $i < $atts['days']; $i++){
						echo '<th class="hcalendar-header-cell" style><p>'.date("d", $timenow+($i * 86400)).'</p>'.$day_names[date("N", $timenow+($i * 86400))-1].'</th>';
					}
				echo '</tr>';
			echo '</thead>';
			echo '<tbody style="text-align:center;padding;0px;margin:0px">';
		for($i = $atts['start']; $i <= $atts['end']; $i++){
			if($atts['clock'] < 24){
				if($i > 11){
					if($i == 12) $show = 12;
					else $show = $i -12;
					$am = 'PM';
				} else {
					$show = $i;
					$am = 'AM';
				}
			} else {
				$show = $i;
				$am = '00';
			}
			echo '<tr><td class="hcalendar-hour-cell" style="">'.date("H", $timenow + ($show * 3600)).'<small>'.$am.'</small></td>';
			$cell_count++;
			for($diff = 0; $diff < $atts['days']; $diff++){
				$date_of_day = $timenow + ($diff * 86400) + ($i * 3600);
				$res = new Reservation(false, array('email' => 'mail@test.com', 'arrival' => $date_of_day, 'departure' =>  $date_of_day+3600,'resource' => (int) $_POST['room'], 'adults' => 1, 'childs' => 0,'reservated' => time()), false);
				try {
					$res->interval = 3600;
					if($atts['price'] > 0){
						$res->Calculate();
						if($atts['price'] == 1 || $atts['price'] == 2){ $explode = explode('.', $res->price); $res->price = $explode[0]; }
						if($atts['price'] == 1) $formated_price = $res->price.'&'.RESERVATIONS_CURRENCY.';';
						elseif($atts['price'] == 2) $formated_price = $res->price;
						elseif($atts['price'] == 3) $formated_price = easyreservations_format_money($res->price, 1);
						elseif($atts['price'] == 4) $formated_price = easyreservations_format_money($res->price);
						$final_price = '<span class="hcalendar-cell-price">'.$formated_price.'</b>';
					} else $final_price = '';

					$today_class = date("d.m.Y", $date_of_day) == date("d.m.Y", time()) ? ' today' : '';
					$avail = round($res->checkAvailability(3,true,3600),1);

					if($avail >= $room_count) $background_td = ' hcalendar-cell-full';
					elseif($avail > 0) $background_td = ' hcalendar-cell-occupied';
					else $background_td = ' hcalendar-cell-empty';
					$key = ($keys + ($diff*$between))-($between-$cell_count);

					if($date_of_day > time()){
						$onclick = 'date="'.date(RESERVATIONS_DATE_FORMAT, $date_of_day).'!'.$i.'"';
						//onclick="easyreservations_click_hcalendar(this,\''.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'\', \''.$key.'\', \''.($i).'\')"';
						$today_class.= ' hpast';
					} else $onclick ='style="cursor:default !important"';
					if($atts['select'] == 0) $onclick ='style="cursor:default !important"';
					
					//MODIF AWI
					$indispo = round(floatval($res->checkAvailability(2,true,3600)),1);
					if ($avail <= $room_count && !$indispo) {
						$final_price = $avail . "/" . $room_count;
					}
					if ($indispo) {
						$background_td = ' hcalendar-cell-indispo';
					}
					//FIN MODIF AWI

					echo '<td class="hcalendar-cell'.$today_class.$background_td.'" '.$onclick.' id="easy-hcalcell-'.$rand.'-'.$key.'">'.$final_price.'</td>';
					$setet++;
				} catch(Exception $e){
					return false;
				}
			}
			echo '</tr>';
			if($i == $atts['end']) $keys = $key;
		}
		echo '</td></tr></tbody></table>';
	}
	exit;
}
	function easyreservations_hourlycal_tinymce_html($roomsoptions){
			$custom_calendar_style = '';
			$hours = easyreservations_interval_infos(3600);
			if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/hourly.css')) $custom_calendar_style = '<option value="custom">' . __("Custom Style", "easyReservations") . '</option>';?>
		else if(x == "hourlycalendar"){
			var FieldAdd = '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hcalendar_room"><?php echo __("Resource", "easyReservations"); ?></label></td>';
				FieldAdd += '<td><label><select id="easyreservation_hcalendar_room" name="easyreservation_hcalendar_room" style="width: 100px"><?php echo $roomsoptions; ?></select></label> <?php echo __("Select default resource", "easyReservations"); ?></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hshow_price"><?php echo __("Price", "easyReservations"); ?></label></td>';
				FieldAdd += '<td><label><select id="easyreservation_hshow_price" name="easyreservation_hshow_price" style="width: 100px"><option value="0"><?php echo __("no", "easyReservations"); ?></option><option value="1">150&<?php echo RESERVATIONS_CURRENCY; ?>;</option><option value="2">150</option><option value="3"><?php echo easyreservations_format_money(150,1); ?></option><option value="4"><?php echo easyreservations_format_money(150); ?></option></select></label> <?php echo __("Show price in calendar", "easyReservations"); ?></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hcalendar_width"><?php echo __("Width", "easyReservations"); ?></label></td>';
				FieldAdd += '<td><select name="easyreservation_hcalendar_width" id="easyreservation_hcalendar_width"><?php echo easyreservations_num_options(1,100,100); ?></select> %</td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hcalendar_mode"><?php echo __("Clock", "easyReservations"); ?></label></td>';
				FieldAdd += '<td><label><select type="text" id="easyreservation_hcalendar_mode" name="easyreservation_hcalendar_mode"><option value="12">12 <?php echo $hours ?></option><option value="24" selected>24 <?php $hours ?></option></select> </label></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hcalendar_days"><?php echo easyreservations_interval_infos(86400,1); ?></label></td>';
				FieldAdd += '<td><label><select id="easyreservation_hcalendar_days" name="easyreservation_hcalendar_days"><?php echo easyreservations_num_options(1,50); ?></select></label></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hcalendar_resources"><?php echo __("Resource", "easyReservations"); ?></label></td>';
				FieldAdd += '<td><label><select id="easyreservation_hcalendar_resources" name="easyreservation_hcalendar_resources"><option value="none"><?php echo __("None", "easyReservations"); ?></option><option value="display"><?php echo __("Display", "easyReservations"); ?></option><option value="select"><?php echo __("Select", "easyReservations"); ?></option><option value="navi"><?php echo __("Navigation", "easyReservations"); ?></option></select></label></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_hcalendar_hoursfrom"><?php echo __("Hours", "easyReservations"); ?></label></td>';
				FieldAdd += '<td><label><?php echo __("Display hours from", "easyReservations"); ?> <select id="easyreservation_hcalendar_hoursfrom" name="easyreservation_hcalendar_hoursfrom"><?php echo easyreservations_num_options(0,23,0); ?></select> <?php echo __("to", "easyReservations"); ?> <select id="easyreservation_hcalendar_hoursto" name="easyreservation_hcalendar_hoursto"><?php echo easyreservations_num_options(0,23,23); ?></select></label></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<?php do_action('easy-tinymce-cal', 2); ?>';
			document.getElementById("tiny_Field").innerHTML = FieldAdd;
		}<?php
	}

	add_action('easy-tinymce-add', 'easyreservations_hourlycal_tinymce_html',10,1);

	function easyreservations_hcalendar_save_tinymce(){?>
		else if(y == "hourlycalendar"){
			classAttribs += ' standard="' + document.getElementById('easyreservation_hcalendar_room').value + '"';
			if(document.getElementById('easyreservation_hcalendar_width').value != "") classAttribs += ' width="' + document.getElementById('easyreservation_hcalendar_width').value + '"';
			if(document.getElementById('easyreservation_hshow_price').value != "") classAttribs += ' price="' + document.getElementById('easyreservation_hshow_price').value + '"';
			if(document.getElementById('easyreservation_hcalendar_days').value != "") classAttribs += ' days="' + document.getElementById('easyreservation_hcalendar_days').value + '"';
			if(document.getElementById('easyreservation_hcalendar_resources').value != "") classAttribs += ' resource="' + document.getElementById('easyreservation_hcalendar_resources').value + '"';
			if(document.getElementById('easyreservation_hcalendar_hoursfrom').value != "") classAttribs += ' start="' + document.getElementById('easyreservation_hcalendar_hoursfrom').value + '"';
			if(document.getElementById('easyreservation_hcalendar_hoursto').value != "") classAttribs += ' end="' + document.getElementById('easyreservation_hcalendar_hoursto').value + '"';
			classAttribs += ' clock="' + document.getElementById('easyreservation_hcalendar_mode').value + '"';
			var monthesfield = document.getElementById('easyreservation_calendar_monthesx');
			var intervalfield = document.getElementById('easyreservation_calendar_interval');
			if(intervalfield) classAttribs += ' interval="' + intervalfield.value + '"';
			if(monthesfield){
				classAttribs += ' monthes="' + monthesfield.value + 'x' + document.getElementById('easyreservation_calendar_monthesy').value + '"';
			}
		} <?php
	}

	add_action('easy-tinymce-save', 'easyreservations_hcalendar_save_tinymce');
	function easyreservations_hourlycal_add_tinymce_name(){
		echo '<option value="hourlycalendar">'.__("Hourly Calendar", "easyReservations").'</option>';
	}

	add_action('easy-tinymce-add-name', 'easyreservations_hourlycal_add_tinymce_name');

if(!is_admin()){

	function easyreservations_hourlycal_shortcode($atts){
		global $easyreservations_script;
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'easyreservations_send_hourlycalendar' );

		$atts = shortcode_atts(array(
			'standard' => 0,
			'width' => 100,
			'style' => 1,
			'price' => 0,
			'req' => 0,
			'interval' => 1,
			'exclude' => '',
			'header' => 0,
			'start' => 0,
			'end' => 23,
			'days' => 10,
			'clock' => 24,
			'monthes' => 1,
			'resource' => 'navi',
			'select' => 2,
			'id' => rand(1,99999),
		), $atts);

		$atts['width'] = (float) $atts['width'];
		if($atts['width'] > 100) $atts['width'] = 100;
		if(wp_style_is( 'easy-hcal-'.$atts['style'], 'registered')) wp_enqueue_style('easy-hcal-'.$atts['style'], false, array(), false, 'all');
		else wp_enqueue_style('easy-hcal-1' , false, array(), false, 'all');
		if(isset($_POST['room']) && is_numeric($_POST['room'])) $atts['standard'] = $_POST['room'];
			$return = '<form name="HourlyCalendarFormular" id="HourlyCalendarFormular-'.$atts['id'].'">';
			$return .= '<div id="showHourlyCalendar" style="width:'.$atts['width'].'%"></div>';
		$return .= '</form><!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org -->';
		$cal = 'new easyHourlyCalendar("'.wp_create_nonce( 'easy-hourlycalendar' ).'", '.json_encode($atts).');';
		if(!function_exists('wpseo_load_textdomain')) $easyreservations_script .= 'if(window.easyCalendar) '.$cal.' else ';
		$easyreservations_script .= 'jQuery(window).ready(function(){'.$cal.'});';

		return $return;
	}
	add_shortcode('easy_hourlycalendar', 'easyreservations_hourlycal_shortcode');

	function easyreservations_register_hourlycal_script(){
		$lang = '';
		if(function_exists('icl_object_id')) $lang = '?lang=' . ICL_LANGUAGE_CODE;
		elseif(function_exists('qtrans_getLanguage')) $lang = '?lang=' . qtrans_getLanguage();

		wp_register_style('easy-hcal-1', WP_PLUGIN_URL . '/easyreservations/lib/modules/hourlycal/css/style_1.css');
		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/hourly.css')) wp_register_style('easy-hcal-custom', WP_PLUGIN_URL . '/easyreservations/css/custom/hourly.css'); // custom form style override

		wp_register_script('easyreservations_send_hourlycalendar', WP_PLUGIN_URL.'/easyreservations/lib/modules/hourlycal/send_hourlycal.js' , array( "jquery" ));	
		wp_localize_script('easyreservations_send_hourlycalendar', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php'.$lang ), 'plugin_url' => WP_PLUGIN_URL ) );
	}
	add_action('wp_enqueue_scripts', 'easyreservations_register_hourlycal_script');
}?>