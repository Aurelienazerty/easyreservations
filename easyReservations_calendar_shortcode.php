<?php
	function reservations_calendar_shortcode($atts) {
		global $easyreservations_script;
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui');
		wp_enqueue_script( 'easyreservations_send_calendar' );

		$atts = shortcode_atts(array(
			'room' => 0,
			'date' => 0,
			'resource' => 0,
			'width' => '300',
			'style' => 1,
			'half' => 1,
			'price' => 0,
			'header' => 0,
			'req' => 0,
			'interval' => 1,
			'monthes' => 1,
			'select' => 2,
			'notax' => 0,
			'id' => rand(1,99999)
		), $atts);

		$atts['width'] = (float) $atts['width'];
		if($atts['width'] > 100 || $atts['width'] < 3) $atts['width'] = 100;
		if(!is_numeric($atts['resource']) || $atts['resource'] < 1)	$atts['resource'] = $atts['room'];
		if(isset($_POST['easyroom']) && is_numeric($_POST['easyroom'])) $atts['resource'] = $_POST['easyroom'];

		if (wp_style_is('easy-cal-'.$atts['style'], 'registered')) wp_enqueue_style('easy-cal-'.$atts['style'], false, array(), false, 'all');
		else wp_enqueue_style('easy-form-none' , false, array(), false, 'all');

		$return = '<form name="CalendarFormular" id="CalendarFormular-'.$atts['id'].'" style="width:'.$atts['width'].'%;margin:0px;padding:0px;display:inline-block;">';
			$return .= '<div id="showCalender"></div>';
		$return .= '</form><!-- Provided by easyReservations free Wordpress Plugin http://www.easyreservations.org -->';

		$cal = 'new easyCalendar("'.wp_create_nonce( 'easy-calendar' ).'", '.json_encode($atts).', "shortcode");';
		if(!function_exists('wpseo_load_textdomain')) $easyreservations_script .= 'if(window.easyCalendar) '.$cal.' else ';
		$easyreservations_script .= 'jQuery(window).ready(function(){'.$cal.'});';

		return $return;
	}
?>