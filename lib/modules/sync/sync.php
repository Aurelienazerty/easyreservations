<?php
/*
Plugin Name: Sync Module
Plugin URI: http://easyreservations.org/module/sync/
Version: 1.0.4
Description: 3.4
Author: Feryaz Beer
License:GPL2
*/

	function easyreservations_sync_check(){

	}

	if(is_admin()){
		function easyreservations_sync_admin(){
			if(isset($_GET['site']) && $_GET['site'] == "sync"){
				do_action('er_sync_settings_top');

				$option = get_option('reservations_woocommerce');
				if(isset($_POST['activate_woocommerce'])){
					$option['modus'] = $_POST['activate_woocommerce'];
					update_option('reservations_woocommerce', $option);
				} elseif(!$option){
					$option = array('modus' => 0);
				}
				$value = __('This function will add reservations to the woocommerce cart.', 'easyReservations').'<br>';
				$value .= __('After first activation save the settings of each resource once.', 'easyReservations');
				$activate = '<input type="radio" name="activate_woocommerce" value="1" '.checked(1,$option['modus'],false).'> '.__('On', 'easyReservations').'<br>';
				$activate .= '<input type="radio" name="activate_woocommerce" value="0" '.checked(0,$option['modus'],false).'> '.__('Off', 'easyReservations');
				$rows = array('col' => $value, __('Mode', 'easyReservations') => $activate);
				$table = easyreservations_generate_table('reservation_woo', __( 'WooCommerce compatibility' , 'easyReservations'), $rows, 'style="margin-top:7px;width: 99%;"');
				echo easyreservations_generate_form('reservation_woo_form', 'admin.php?page=reservation-settings&site=sync#reservation_woo', 'post', false, false, $table);
			/*
				$option = get_option('reservations_googlecal');
				if(isset($_POST['activate_googlecal'])){
					include_once(dirname(__FILE__)."/google.cal.php");
					$option['modus'] = $_POST['activate_googlecal'];
					update_option('reservations_googlecal', $option);
		 		} elseif(!$option){
					$option = array('modus' => 0);
				}
				$value = __('Visit https://code.google.com/apis/console?api=calendar to generate your client id, client secret, and to register your redirect uri.', 'easyReservations');
				$explain = '<input type="radio" name="activate_googlecal" value="1" '.checked(1,$option['modus'],false).'> '.__('On', 'easyReservations').' ';
				$explain .= '<input type="radio" name="activate_googlecal" value="0" '.checked(0,$option['modus'],false).'> '.__('Off', 'easyReservations');
				$rows = array('col' => $value,__('Modus', 'easyReservations') => $explain);
				if($option && $option['modus'] == 1) $rows = apply_filters('easyreservations_google_setting_rows', $rows);
				$table = easyreservations_generate_table('reservation_googlecal', __( 'Google Calendar Synchronisation' , 'easyReservations'), $rows, 'style="margin-top:7px;"');
				echo easyreservations_generate_form('reservation_googelecal_form', 'admin.php?page=reservation-settings&site=sync#reservation_googlecal', 'post', false, false, $table);
			*/
			}
		}
		add_action('er_set_add', 'easyreservations_sync_admin');

		function easyreservations_add_sync_settings_tab(){
			$current = isset($_GET['site']) && $_GET['site'] == "sync" ? 'current' : '';
			echo '<li><a href="admin.php?page=reservation-settings&site=sync" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_URL.'images/reload.png"> '. __( 'Sync' , 'easyReservations' ).'</a></li>';
		}
		add_action('er_set_tab_add', 'easyreservations_add_sync_settings_tab');
	}

$woo_option = get_option('reservations_woocommerce');
if($woo_option && $woo_option['modus'] == 1) include_once(dirname(__FILE__)."/woo.sync.php");

$google_option = get_option('reservations_googlecal');
if($google_option && $google_option['modus'] == 1) include_once(dirname(__FILE__)."/google.cal.php");

?>