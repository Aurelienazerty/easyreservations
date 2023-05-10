<?php
/*
Plugin Name: Premium Module
Plugin URI: http://www.easyreservations.org
Description: Some premium functions 
Version: 1.0
Author: Feryaz Beer
Author URI: http://www.easyreservations.org
License:GPL2
*/

	remove_action('er_add_settings_top', 'easyreservations_prem_box_set', 10,  0);
	remove_action('er_set_main_side_top', 'easyreservations_add_warn_notice');

?>