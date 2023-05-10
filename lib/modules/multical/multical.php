<?php
/*
Plugin Name: extentedCalendar Module
Plugin URI: http://easyreservations.org/module/multical/
Version: 1.1.8
Description: 3.3
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!.
*/

	function easyreservations_generate_multical($date, $months, $day = 0){
		$months_starts = array();
		for($i = 0; $i < $months; $i++){
			if($day == 0) $months_starts[] =  mktime(0, 0, 0, date("m", time())+$date+$i, 1, date("Y", time()));
			else $months_starts[] = strtotime(date("d.m.Y", time()))+($date*86400)+($i*$day*86400);
		}
		return $months_starts;
	}

	function easyreservations_add_multical_options($where = 1){
		echo '<tr><td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_monthes">'.__("Display months", "easyReservations").'</label></td>';
		echo '<td>Col: <select id="easyreservation_calendar_monthesx" name="easyreservation_calendar_monthesx" style="width:70px">'.easyreservations_num_options(1,25).'</select> * Row: <select id="easyreservation_calendar_monthesy" name="easyreservation_calendar_monthesy" style="width:70px">'.easyreservations_num_options(1,25).'</select> = <span id="easyreservation_calendar_monthes_count">1 '.__("Month", "easyReservations").'</span></td></tr>';
		echo '<tr><td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_interval">'.__("Interval", "easyReservations").'</label></td>';
		echo '<td><label><select id="easyreservation_calendar_interval" name="easyreservation_calendar_interval" style="width:70px"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="9">9</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select> months</label></td></tr>';
		if($where == 1){
			echo '<tr><td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_header">'.__("Display month name", "easyReservations").'</label></td>';
			echo '<td><label><input id="easyreservation_calendar_header" name="easyreservation_calendar_header" type="checkbox"></label></td></tr>';
		}
	}

	add_action('easy-tinymce-cal', 'easyreservations_add_multical_options', 10 ,1);

	function easyreservations_add_boxes_cal_style(){
		echo '<option value="3">'.__("boxes", "easyReservations").'</option>';
	}
	
	add_action('easy-tinymce-add-style-cal', 'easyreservations_add_boxes_cal_style', 11);
	
	
	function easyreservations_register_calendar_style(){
		wp_register_style('easy-cal-3', WP_PLUGIN_URL . '/easyreservations/lib/modules/multical/css/style_3.css');
	}

	add_action('wp_enqueue_scripts', 'easyreservations_register_calendar_style');

?>