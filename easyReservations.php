<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.easyreservations.org
Description: This powerfull property and reservation management plugin allows you to receive, schedule and handle your bookings easily!
Version: 3.6.4
Author: Feryaz Beer
Author URI: http://www.feryaz.de
License:GPL2
*/

	/**
	*	Install script
	*
	*/

	register_activation_hook(__FILE__, 'easyreservations_install');
	function easyreservations_install(){ // Install Plugin Database

		$emailstandart0="[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]<br>edit your reservation on [editlink]";
		$emailstandart1="New Reservation on Blogname from<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]";
		$emailstandart2="Your Reservation on Blogname has been approved.<br>
[adminmessage]<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]<br>edit your reservation on [editlink]";
		$emailstandart3="Your Reservation on Blogname has been rejected.<br>
[adminmessage]<br> <br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>edit your reservation on [editlink]";
		$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]<br><br>edit your reservation on [editlink]";
		$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
		$emailstandart6="Reservation got edited by Guest.<br><br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]<br><br>[changelog]";
		$emailstandart7="Your reservation got edited by admin.<br><br>
[adminmessage]<br>
New Reservation Details:<br>
ID: [ID]<br>Name: [thename] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [childs] <br>Resource: [resource] <br>Price: [price]<br><br>edit your reservation on [editlink]<br><br>[changelog]";

		$formstandart = '[error]
<h1>Reserve now![show_price style="float:right;"]</h1>
<h2>General information</h2>

<label>Arrival Date
<span class="small">When will you come?</span>
</label><span class="row">[date-from style="width:75px"] [date-from-hour style="width:50px" value="12"]:[date-from-min style="width:50px"]</span>

<label>Departure Date
<span class="small">When will you go?</span>
</label><span class="row">[date-to style="width:75px"] [date-to-hour style="width:50px" value="12"]:[date-to-min style="width:50px"]</span>

<label>Resource
<span class="small">What do you want?</span>
</label>[resources]

<label>Adults
<span class="small">How many guests?</span>
</label>[adults 1 10]

<label>Children&rsquo;s	
<span class="small">With children?</span>
</label>[childs 0 10]

<h2>Personal information</h2>

<label>Name
<span class="small">What is your name?</span>
</label>[thename]

<label>Email
<span class="small">What is your email?</span>
</label>[email]

<label>Country
<span class="small">Your country?</span>
</label>[country]

<label>Captcha
<span class="small">Type in code</span>
</label>[captcha]

<div style="text-align:center;">[submit Send]</div>';

		$permission = array('dashboard' => 'edit_posts', 'statistics' => 'edit_posts', 'resources' => 'edit_posts', 'settings' => 'edit_posts');
		add_option('reservations_main_permission', $permission, '', 'yes' );
		add_option( 'reservations_email_to_user', array('msg' => $emailstandart4, 'subj' =>  'Your Reservation on '.get_option('blogname'), 'active' => 1), '', 'no');
		add_option( 'reservations_email_to_userapp', array('msg' => $emailstandart2, 'subj' => 'Your Reservation on '.get_option('blogname').' has been approved', 'active' => 1), '', 'no');
		add_option( 'reservations_email_to_userdel', array('msg' => $emailstandart3, 'subj' =>  'Your Reservation on '.get_option('blogname').' has been rejected', 'active' => 1), '', 'no');
		add_option( 'reservations_email_to_admin', array('msg' => $emailstandart1, 'subj' =>  'New Reservation at '.get_option('blogname'), 'active' => 1), '', 'no');
		add_option( 'reservations_email_to_user_edited', array('msg' => $emailstandart5, 'subj' =>  'Your Reservation on '.get_option('blogname').' got edited', 'active' => 1), '', 'no');
		add_option( 'reservations_email_to_admin_edited', array('msg' =>  $emailstandart6, 'subj' => 'Reservation on '.get_option('blogname').' got edited by user', 'active' => 1), '', 'no');
		add_option( 'reservations_email_to_user_admin_edited', array('msg' => $emailstandart7, 'subj' =>  'Reservation on '.get_option('blogname').' got edited by admin', 'active' => 1), '', 'no');
		add_option( 'reservations_email_sendmail', array('msg' => $emailstandart0, 'subj' => 'Message from '.get_option('blogname'), 'active' => 1), '', 'no');

		add_option( 'reservations_uninstall', '1', '', 'no' );
		add_option( 'reservations_form', $formstandart, '', 'no' );
		add_option( 'reservations_regular_guests', '', '', 'no' );
		add_option( 'reservations_edit_url', '', '', 'yes' );
		add_option( 'reservations_price_per_persons', '1', '', 'yes' );
		add_option( 'reservations_on_page', '10', '', 'no' );
		add_option( 'reservations_support_mail', '', '', 'yes' );
		add_option('reservations_db_version', '2.0', '', 'yes' );
		$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
		$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_fav' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => 0, 'table_custom' => 0, 'table_customp' => 0, 'table_paid' => 0, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1, 'table_reservated' => 0, 'table_status' => 1, 'table_fav' => 1 );
		$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
		add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');
		$edit_options = array( 'login_text' => '', 'edit_text' => '', 'submit_text' => 'Reservation successfully edited',  'table_infos' => array('date', 'status', 'price', 'room'), 'table_status' => array('','yes','no'), 'table_time' => array('past','current','future'), 'table_style' => 1, 'table_more' => 1 );
		add_option('reservations_edit_options', $edit_options, '', 'no');
		add_option('reservations_settings', array( 'style' => "greyfat", 'interval' => 86400, 'currency' => '#36', 'date_format' => 'd.m.Y', 'time' => 1, 'tutorial' => 1 ), '', 'yes');

		global $wpdb;
		$table_name = $wpdb->prefix . "reservations";

		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		id int(10) NOT NULL AUTO_INCREMENT,
		arrival DATETIME NOT NULL,
		departure DATETIME NOT NULL,
		user int(10) NOT NULL,
		name varchar(35) NOT NULL,
		email varchar(50) NOT NULL,
		country varchar(4) NOT NULL,
		approve varchar(3) NOT NULL,
		room varchar(8) DEFAULT NULL,
		roomnumber varchar(8) NOT NULL,
		number int(4) NOT NULL,
		childs int(4) NOT NULL,
		price varchar(20) NOT NULL,
		custom longtext NOT NULL,
		customp longtext NOT NULL,
		reservated DATETIME NOT NULL,
		UNIQUE KEY id (id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$room_args = array( 'post_status' => 'publish|private', 'post_type' => 'easy-rooms', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => 1);
		$roomcategories = get_posts( $room_args );
		if(!$roomcategories){
			$roomOne = array(
				'post_title' => 'Sample Resource One',
				'post_content' => 'This is a Sample Resource.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_type' => 'easy-rooms'
			);

			$roomOne_id = wp_insert_post( $roomOne );
			add_post_meta($roomOne_id, 'roomcount', 4);
			add_post_meta($roomOne_id, 'reservations_groundprice', 120);
			add_post_meta($roomOne_id, 'reservations_child_price', 10);

			$roomTwo = array(
				'post_title' => 'Sample Resource Two',
				'post_content' => 'This is a Sample Resource.',
				'post_status' => 'private',
				'post_author' => 1,
				'post_type' => 'easy-rooms'
			);

			$roomTwo_id = wp_insert_post( $roomTwo );
			add_post_meta($roomTwo_id, 'roomcount', 7);
			add_post_meta($roomTwo_id, 'reservations_groundprice', 250.57);
			add_post_meta($roomTwo_id, 'reservations_child_price', 20);
		}
	}

	/**
	*	Upgrade script
	*
	*/

	add_action('admin_init','easyReservations_upgrade',1);

	function easyReservations_upgrade(){
		global $wpdb;
		$easyReservations_active_ver="3.2.1";
		$easyReservations_installed_ver = get_option("reservations_db_version");
		if($easyReservations_installed_ver != $easyReservations_active_ver ){
			if($easyReservations_installed_ver == 1.2 || $easyReservations_installed_ver == 1.3 || $easyReservations_installed_ver == "1.3.1" || $easyReservations_installed_ver == "1.3.2"){
				$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
				$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => 0, 'table_custom' => 0, 'table_customp' => 0, 'table_paid' => 0, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1, 'table_reservated' => 0, 'table_status' => 1, 'table_fav' => 1 );
				$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '', 'overview_show_avail' => 1 );
				add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');

				$settings_array = array( 'style' => get_option("reservations_style"), 'interval' => 86400, 'currency' => get_option("reservations_currency"), 'date_format' => get_option("reservations_date_format"), 'time' => 1 );
				add_option("reservations_settings", $settings_array);
				delete_option("reservations_style");
				delete_option("reservations_interval");
				delete_option("reservations_currency");
				delete_option("reservations_date_format");
				$wpdb->query( $wpdb->prepare("DROP TABLE ".$wpdb->prefix ."reservations DROP arrivalDate, nights, special, dat, notes"));

				$easyReservations_installed_ver = 1.4;
			}
      if($easyReservations_installed_ver == 1.4 || $easyReservations_installed_ver == "1.4.5"){
        $permission = array('dashboard' => 'edit_posts', 'statistics' => 'edit_posts', 'resources' => 'edit_posts', 'settings' => 'edit_posts');
        update_option( 'reservations_main_permission', $permission );
        add_option( 'reservations_email_to_user', array('msg' => get_option( 'reservations_email_to_user_msg' ), 'subj' => get_option( 'reservations_email_to_user_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_to_userapp', array('msg' => get_option( 'reservations_email_to_userapp_msg' ), 'subj' => get_option( 'reservations_email_to_userapp_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_to_userdel', array('msg' => get_option( 'reservations_email_to_userdel_msg' ), 'subj' => get_option( 'reservations_email_to_userdel_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_to_admin', array('msg' => get_option( 'reservations_email_to_admin_msg' ), 'subj' => get_option( 'reservations_email_to_admin_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_to_user_edited', array('msg' => get_option( 'reservations_email_to_user_edited_msg' ), 'subj' => get_option( 'reservations_email_to_user_edited_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_to_admin_edited', array('msg' => get_option( 'reservations_email_to_admin_edited_msg' ), 'subj' => get_option( 'reservations_email_to_admin_edited_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_to_user_admin_edited', array('msg' => get_option( 'reservations_email_to_user_admin_edited_msg' ), 'subj' => get_option( 'reservations_email_to_user_admin_edited_subj' ), 'active' => 1), '', 'no');
        add_option( 'reservations_email_sendmail', array('msg' => get_option( 'reservations_email_sendmail_msg' ), 'subj' => get_option( 'reservations_email_sendmail_subj' ), 'active' => 1), '', 'no');
        delete_option( 'reservations_email_to_userapp_subj' );
        delete_option( 'reservations_email_to_userapp_msg' );
        delete_option( 'reservations_email_to_userdel_subj' );
        delete_option( 'reservations_email_to_userdel_msg' );
        delete_option( 'reservations_email_to_admin_subj' );
        delete_option( 'reservations_email_to_admin_msg' );
        delete_option( 'reservations_email_to_user_subj' );
        delete_option( 'reservations_email_to_user_msg' );
        delete_option( 'reservations_email_to_user_edited_subj' );
        delete_option( 'reservations_email_to_user_edited_msg' );
        delete_option( 'reservations_email_to_admin_edited_subj' );
        delete_option( 'reservations_email_to_admin_edited_msg' );
        delete_option( 'reservations_email_to_user_admin_edited_subj' );
        delete_option( 'reservations_email_to_user_admin_edited_msg' );
        delete_option( 'reservations_email_sendmail_subj' );
        delete_option( 'reservations_email_sendmail_msg' );
        global $wpdb;
        $wpdb->query( "DELETE FROM ".$wpdb->prefix ."postmeta WHERE meta_key = 'reservations_filter' ");
        $easyReservations_installed_ver = 1.5;
      }
      if($easyReservations_installed_ver == 1.5){
        global $wpdb;
        $room_category = get_option('reservations_room_category');
        if(isset($room_category) && !empty($room_category) && is_numeric($room_category)){
          $args=array( 'category' => $room_category, 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );
          $getids = get_posts($args);
          foreach($getids as $post){
            $id = $post->ID;
            $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."posts SET post_type='easy-rooms' WHERE ID='$id' "));
            $wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."term_relationships WHERE object_id='$id'  ") );
          }
        }
        $offer_category = get_option('reservations_special_offer_cat');
        if(isset($offer_category) && !empty($offer_category) && is_numeric($room_category)){
          $args=array( 'category' => $offer_category, 'post_type' => 'post', 'post_status' => 'publish|private', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts'     => -1 );
          $getids = get_posts($args);
          foreach($getids as $post){
            $id = $post->ID;
            $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."posts SET post_type='easy-offers' WHERE ID='$id' "));
            $wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix ."term_relationships WHERE object_id='$id'"));
          }
        }
        delete_option('reservations_room_category');
        delete_option('reservations_special_offer_cat');
        $wpdb->query($wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations CHANGE custom custom longtext"));
        $wpdb->query($wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations CHANGE customp customp longtext"));
        $reservations = $wpdb->get_results("SELECT id, custom, customp FROM ".$wpdb->prefix ."reservations");
        foreach($reservations as $reservation){
          $id = $reservation->id;
          $customs = $reservation->custom;
          $explode_customs = explode('&;&', $customs);
          $new_customs = array();
          if(isset($explode_customs[1])){
            foreach($explode_customs as $custom){
              if(!empty($custom)){
                $explode_the_custom = explode('&:&', $custom);
                $new_customs[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_custom[1] );
              }
            }
          } elseif(isset($explode_customs[0]) && strlen($explode_customs[0]) > 5){
            $explode_the_custom = explode('&:&', $explode_customs[0]);
            $new_customs[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_custom[1] );
          }
          $customsp = $reservation->customp;
          $explode_customp = explode('&;&', $customsp);
          $new_customp = array();
          if(isset($explode_customp[1])){
            foreach($explode_customp as $customp){
              if(!empty($customp)){
                $explode_the_custom = explode('&:&', $customp);
                $explode_the_price = explode(':', $explode_the_custom[1]);
                $new_customp[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_price[0], 'amount' => $explode_the_price[1] );
              }
            }
          } elseif(isset($explode_customp[0]) && strlen($explode_customp[0]) > 5){
            $explode_the_custom = explode('&:&', $explode_customp[0]);
            $explode_the_price = explode(':', $explode_the_custom[1]);
            $new_customp[] = array( 'type' => 'cstm', 'mode' => 'edit', 'title' => $explode_the_custom[0], 'value' => $explode_the_price[0], 'amount' => $explode_the_price[1] );
          }
          $save_custom = !empty($new_customs) ? maybe_serialize(array($new_customs)) : '';
	        $save_customp = !empty($new_customp) ? maybe_serialize(array($new_customp)) : '';
          $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET custom='$save_custom', customp='$save_customp' WHERE id='$id' "));
          unset($new_customs);
          unset($new_customp);
        }
        $wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD user int(10) NOT NULL"));
        $wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET user='0'"));
        add_option( 'reservations_uninstall', '1', '', 'no' );
        $easyReservations_installed_ver = 1.6;
      }
      if($easyReservations_installed_ver == 1.6){
        $edit_text = get_option('reservations_edit_text');
        $edit_options = array( 'login_text' => stripslashes($edit_text), 'edit_text' => stripslashes($edit_text),  'table_infos' => array('date', 'status', 'price', 'room'), 'table_status' => array('','yes','no'), 'table_time' => array('past','current','future'), 'table_style' => 1, 'table_more' => 1 );
        add_option('reservations_edit_options', $edit_options, '', false);
        add_option('reservations_date_format', 'd.m.Y', '', true);
        delete_option( 'reservations_edit_text' );
        $easyReservations_installed_ver = 1.7;
      }
      if($easyReservations_installed_ver == 1.7){
        $easyReservations_installed_ver = 1.8;
      }
      if($easyReservations_installed_ver == 1.8){
        global $wpdb;
        $wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD arrival datetime NOT NULL"));
        $wpdb->query( $wpdb->prepare("ALTER TABLE ".$wpdb->prefix ."reservations ADD departure datetime NOT NULL"));
        $reservations = $wpdb->get_results($wpdb->prepare("SELECT id, arrivalDate, nights, notes FROM ".$wpdb->prefix ."reservations"));
        foreach($reservations as $reservation){
          $id = $reservation->id;
          $arrivalDate = strtotime($reservation->arrivalDate);
          $nights = $reservation->nights;
          $arrival = date("Y-m-d H:i", $arrivalDate+43200);
          $departure = date("Y-m-d H:i", $arrivalDate+(86400*$nights)+43200);
          $wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix ."reservations SET arrival='$arrival', departure='$departure' WHERE id='$id' "));
        }
			}
			
			$easyReservations_installed_ver = "3.2.1";
			update_option('reservations_db_version', $easyReservations_installed_ver);
		}
	}

	function easyreservations_rmdirr($dirname){
		if(!file_exists($dirname)) return false; // Sanity check
		if(is_file($dirname)) return unlink($dirname);	// Simple delete for a file

		$dir = dir($dirname);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') continue; // Skip pointers
			easyreservations_rmdirr("$dirname/$entry"); // Recurse
		}
 
		$dir->close(); // Clean up
		return rmdir($dirname);
	}

	function easyreservations_copyr($source, $dest){
		if(is_link($source)) return symlink(readlink($source), $dest); // Check for symlinks
		if(is_file($source)) return copy($source, $dest); // Simple copy for a file	
		if(!is_dir($dest)) mkdir($dest, 0777);

		if(is_dir($source) && is_dir($dest)){
			$dir = dir($source);
			while (false !== $entry = $dir->read()) {
				if ($entry == '.' || $entry == '..') continue; // Skip pointers
				easyreservations_copyr("$source/$entry", "$dest/$entry"); // Deep copy directories
			}

			$dir->close(); // Clean up
		}
		return true;
	}

	function easyreservations_backup($a,$arr){
		if(strpos($arr['plugin'],'easyreservations') === false) return;
		$to = dirname(__FILE__)."/../easy_modules_backup/";
		$to2 = dirname(__FILE__)."/../easy_css_backup/";
		$from = dirname(__FILE__)."/lib/modules";
		$from2 = dirname(__FILE__)."/css/custom";
		easyreservations_copyr($from, $to);
		easyreservations_copyr($from2, $to2);
	}

	function easyreservations_recover($a,$arr){
		if(strpos($arr['plugin'],'easyreservations') === false) return;
		$from = dirname(__FILE__)."/../easy_modules_backup/";
		$from2 = dirname(__FILE__)."/../easy_css_backup/";
		$to = dirname(__FILE__)."/lib/modules";
		$to2 = dirname(__FILE__)."/css/custom";
		easyreservations_copyr($from, $to);
		easyreservations_copyr($from2, $to2);
		if(is_dir($from)) easyreservations_rmdirr($from);#http://putraworks.wordpress.com/2006/02/27/php-delete-a-file-or-a-folder-and-its-contents/
		if(is_dir($from2)) easyreservations_rmdirr($from2);
		delete_option('easyreservations-notifier-last-updated');
		$data = get_option('reservations_login');
		if($data && !empty($data)){
			echo '<h1>Update Modules</h1><p>';
			easyreservations_latest_modules_versions(86400, false, true, 'all');
		}
	}

if(!function_exists("strptime")){
	function strptime($sDate, $sFormat){
		$aResult = array(
			'tm_sec'   => 0,
			'tm_min'   => 0,
			'tm_hour'  => 0,
			'tm_mday'  => 1,
			'tm_mon'   => 0,
			'tm_year'  => 0,
			'tm_wday'  => 0,
			'tm_yday'  => 0,
			'unparsed' => $sDate,
		);
		while($sFormat != ""){
			$nIdxFound = strpos($sFormat, '%');
			if($nIdxFound === false){
				$aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
				break;
			}
			$sFormatBefore = substr($sFormat, 0, $nIdxFound);
			$sDateBefore   = substr($sDate,   0, $nIdxFound);
			if($sFormatBefore != $sDateBefore) break;
			$sFormat = substr($sFormat, $nIdxFound);
			$sDate   = substr($sDate,   $nIdxFound);
			$aResult['unparsed'] = $sDate;
			$sFormatCurrent = substr($sFormat, 0, 2);
			$sFormatAfter   = substr($sFormat, 2);
			$nValue = -1;
			$sDateAfter = "";
			switch($sFormatCurrent){
				case '%S': // Seconds after the minute (0-59)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 0) || ($nValue > 59)) return false;
					$aResult['tm_sec']  = $nValue;
					break;
				case '%M': // Minutes after the hour (0-59)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 0) || ($nValue > 59)) return false;
					$aResult['tm_min']  = $nValue;
					break;
				case '%H': // Hour since midnight (0-23)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 0) || ($nValue > 23)) return false;
					$aResult['tm_hour']  = $nValue;
					break;
				case '%d': // Day of the month (1-31)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 1) || ($nValue > 31)) return false;
					$aResult['tm_mday']  = $nValue;
					break;
				case '%m': // Months since January (0-11)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
					if(($nValue < 1) || ($nValue > 12)) return false;
					$aResult['tm_mon']  = ($nValue - 1);
					break;
				case '%Y': // Years since 1900
					sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);
					if($nValue < 1900) return false;
					$aResult['tm_year']  = ($nValue - 1900);
					break;
				default:
					break 2; // Break Switch and while
			}
			$sFormat = $sFormatAfter;
			$sDate   = $sDateAfter;
			$aResult['unparsed'] = $sDate;
		}
		$nParsedDateTimestamp = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'], $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);
		if(($nParsedDateTimestamp === false) ||($nParsedDateTimestamp === -1)) return false;
		$aResult['tm_wday'] = (int) date("N", $nParsedDateTimestamp); // Days since Sunday (0-6)
		$aResult['tm_yday'] = (int) (date("z", $nParsedDateTimestamp) ); // Days since January 1 (0-365)

		return $aResult;
	}
}

	class EasyDateTime extends DateTime {
		public static function createFromFormat($format, $time, $lang = null){
			if(version_compare(PHP_VERSION, '5.3.0') >= 0){
				$date = parent::createFromFormat($format, $time);
				if($date) return new self('@'.$date->format('U'), $date->getTimeZone());
			}
			$format = str_replace(array('d', 'm', 'Y', 'H', 'i', 's'), array('%d', '%m', '%Y', '%H', '%M', '%S'), $format);
			$ugly = strptime($time, $format);
			$ymd = sprintf(
				'%04d-%02d-%02d %02d:%02d:%02d',
				$ugly['tm_year'] + 1900,  // This will be "111", so we need to add 1900.
				$ugly['tm_mon'] + 1,      // This will be the month minus one, so we add one.
				$ugly['tm_mday'],
				$ugly['tm_hour'],
				$ugly['tm_min'],
				$ugly['tm_sec']
			);

			$date = new DateTime($ymd);
			return new self('@'.$date->format('U'), $date->getTimeZone());
		}

		public function getTimestamp(){
			if(version_compare(PHP_VERSION, '5.3.0') >= 0){
				return parent::getTimestamp();
			}
			return $this->format('U');
		}
	}

	add_filter('upgrader_pre_install', 'easyreservations_backup', 10, 2);
	add_filter('upgrader_post_install', 'easyreservations_recover', 10, 2);
	$reservations_settings = get_option("reservations_settings");

	define('RESERVATIONS_VERSION', '3.6.4');
	define('RESERVATIONS_DIR', WP_PLUGIN_DIR.'/easyreservations/');
	define('RESERVATIONS_URL', WP_PLUGIN_URL.'/easyreservations/');
	define('RESERVATIONS_STYLE', $reservations_settings['style']);
	if(!is_array($reservations_settings['currency'])) $sign = $reservations_settings['currency'];
	else $sign = $reservations_settings['currency']['sign'];
	define('RESERVATIONS_CURRENCY', $sign);
	define('RESERVATIONS_DATE_FORMAT', $reservations_settings['date_format']);
	define('RESERVATIONS_USE_TIME', $reservations_settings['time']);
	if(RESERVATIONS_USE_TIME == 1){
		if(isset($reservations_settings['time_format'])) $usetime = ' '.$reservations_settings['time_format'];
		else $usetime = ' H:i';
	} else $usetime = '';
	define('RESERVATIONS_DATE_FORMAT_SHOW', RESERVATIONS_DATE_FORMAT.$usetime);
	if(get_option('timezone_string')) date_default_timezone_set(get_option('timezone_string'));

	function easyreservations_init_language() {
		if(isset($_GET['lang'])){
			global $sitepress;
			if($sitepress && is_object($sitepress)) $sitepress->switch_lang($_GET['lang']);
		}
		load_plugin_textdomain('easyReservations', false, dirname(plugin_basename( __FILE__ )).'/languages/' );
	}

	add_action('init','easyreservations_init_language');
	add_action('admin_menu', 'easyreservations_add_pages');

	require_once(dirname(__FILE__)."/lib/functions/both.php");
	if(file_exists(dirname(__FILE__).'/lib/core/core.php')) require_once(dirname(__FILE__)."/lib/core/core.php");
	require_once(dirname(__FILE__)."/lib/classes/reservation.class.php");

	if(is_admin() || defined( 'EASY_API' )){
		require_once(dirname(__FILE__)."/lib/classes/resource.class.php");
		require_once(dirname(__FILE__)."/pagination.class.php");
		require_once(dirname(__FILE__)."/lib/functions/admin.php");
		if(strpos($_SERVER['SCRIPT_FILENAME'],'admin-ajax.php') !== false) require_once(dirname(__FILE__)."/lib/functions/ajax.php");
		if(isset($_GET['page']) && $_GET['page'] == 'reservations') require_once(dirname(__FILE__)."/easyReservations_admin_main.php");
		if(isset($_GET['page']) && $_GET['page'] == 'reservation-resources') require_once(dirname(__FILE__)."/easyReservations_admin_resources.php");
		if(isset($_GET['page']) && $_GET['page'] == 'reservation-settings') require_once(dirname(__FILE__)."/easyReservations_admin_settings.php");
	} else {
		require_once(dirname(__FILE__)."/lib/functions/front.php");
		require_once(dirname(__FILE__)."/easyReservations_form_shortcode.php");
		require_once(dirname(__FILE__)."/easyReservations_calendar_shortcode.php");

		add_shortcode('easy_calendar', 'reservations_calendar_shortcode');
		add_shortcode('easy_form', 'reservations_form_shortcode');
	}

//	function easyreservations_api_rewrite_rule(){
//		add_rewrite_tag('%api%','(^*)');
//		add_rewrite_rule('^api/([^/]*)/{0,1}([^/]*)/{0,1}([^/]*)/{0,1}?','wp-content/plugins/easyreservations/lib/api/index.php?controller=$1&action=$2&information=$3','top');
//	}
//	add_action('init', 'easyreservations_api_rewrite_rule' );

	require_once(dirname(__FILE__)."/lib/widgets/form_widget.php");
	if(file_exists(dirname(__FILE__).'/lib/modules/premium/premium.php')) require_once(dirname(__FILE__)."/lib/modules/premium/premium.php");
	$reservations_active_modules = get_option('reservations_active_modules');
	if($reservations_active_modules){
		if(easyreservations_is_module('paypal')) include_once(dirname(__FILE__)."/lib/modules/paypal/paypal.php");
		if(easyreservations_is_module('useredit')) include_once(dirname(__FILE__)."/lib/modules/useredit/useredit.php");
		if(easyreservations_is_module('import')) include_once(dirname(__FILE__)."/lib/modules/import/import.php");
		if(easyreservations_is_module('multical')) include_once(dirname(__FILE__)."/lib/modules/multical/multical.php");
		if(easyreservations_is_module('search')) include_once(dirname(__FILE__)."/lib/modules/search/search.php");
		if(easyreservations_is_module('lang')) include_once(dirname(__FILE__)."/lib/modules/lang/lang.php");
		if(easyreservations_is_module('styles')) include_once(dirname(__FILE__)."/lib/modules/styles/styles.php");
		if(easyreservations_is_module('hourlycal')) include_once(dirname(__FILE__)."/lib/modules/hourlycal/hourlycal.php");
		if(easyreservations_is_module('htmlmails')) include_once(dirname(__FILE__)."/lib/modules/htmlmails/htmlmails.php");
		if(easyreservations_is_module('coupons')) include_once(dirname(__FILE__)."/lib/modules/coupons/coupons.php");
		if(easyreservations_is_module('invoice')) include_once(dirname(__FILE__)."/lib/modules/invoice/invoice.php");
		if(easyreservations_is_module('statistics')) include_once(dirname(__FILE__)."/lib/modules/statistics/statistics.php");
		if(easyreservations_is_module('stream')) include_once(dirname(__FILE__)."/lib/modules/stream/stream.php");
		if(easyreservations_is_module('sync')) include_once(dirname(__FILE__)."/lib/modules/sync/sync.php");
	}
	if(is_admin()) if(!isset($reservations_settings['tutorial'] ) || $reservations_settings['tutorial'] == 1) include_once(dirname(__FILE__)."/lib/tutorials/handle.tutorials.php");
