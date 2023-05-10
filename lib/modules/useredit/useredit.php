<?php
/*
Plugin Name: User Control Panel Module
Plugin URI: http://easyreservations.org/module/useredit/
Version: 1.3.6
Description: 3.4
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!.
*/

	function easyreservations_generate_chat($res, $place){
		wp_enqueue_script('easyreservations_send_chat');

		$options = get_option('reservations_chat_options');
		if(empty($options) || !is_array($options)) return false;

		if($place == 'edit') $chats = $res->getCustoms($res->custom, 'chat', 'visible');
		else $chats = $res->getCustoms($res->custom, 'chat');
		$chats_html = '';

		if($options['dummy_user'] == 1){
			$chats_html .= '<p class="chat-entry-h1 chat-admin">';
			$user_info = get_userdata($options['dummy_user']);
			$name = $user_info->display_name;

			if($options['img'] == 1) $chats_html .= get_avatar( $options['dummy_user'], 15 );
			$chats_html .= '<b class="chat-entry-bold">'.ucfirst($name).'</b>';
			if($options['time'] == 1) $chats_html .= '<span class="chat-entry-time">'.human_time_diff( $res->reservated+694 ).' '.__( 'ago', 'easyReservations' ).'</span>';
			$chats_html .= '</p>';
			$chats_html .= '<p class="chat-entry-p" style="padding-left:3px;">'.$options['dummy_message'].'</p>';
		}

		if(!empty($chats)){
			foreach($chats as $key => $chat){
				if($chat['user'][0] == 'g'){
					$chats_html .= '<p class="chat-entry-h1 chat-user">';
					$user = substr($chat['user'],1);
					$user_info = get_userdata($user);
					if($user_info){
						$chats_html .= get_avatar($user, 15 );
						$name = $user_info->display_name;
					} else {
						$name = $res->name;
						if($options['img'] == 1) $chats_html .= '<img class="avatar-15" src="'.$options['guest_img'].'">';
					}
				} else {
					if( $chat['mode'] == 'hidden' ) $style = 'chat-hidden';
					else $style = 'chat-admin';
					$chats_html .= '<p class="chat-entry-h1 '.$style.'">';
					$user_info = get_userdata($chat['user']);
					$name = $user_info->display_name;

					if($options['img'] == 1) $chats_html .= get_avatar( $chat['user'], 15 );
				}
				$chats_html .= '<b class="chat-entry-bold">'.ucfirst($name).'</b>';
				if($options['time'] == 1) $chats_html .= '<span class="chat-entry-time">'.human_time_diff( $chat['time'] ).' '.__( 'ago', 'easyReservations' ).'</span>';
				$chats_html .= '</p><p id="a" class="chat-entry-p" style="padding-left:3px;"><span>'.$chat['message'].'</span>';
				if($place == 'admin' ){
					$chats_html .= '<a onclick="easyreservations_send_chat(\''.$res->id.'\', \''.$name.'\', \''.$chat['user'].'\', \''.$chat['mode'].'\', \'edit\', \''.$key.'\', this);"  href="javascript:"> - '.__( 'edit', 'easyReservations' ).'</a>';
					$chats_html .= '<a onclick="easyreservations_send_chat(\''.$res->id.'\', \''.$name.'\', \''.$chat['user'].'\', \''.$chat['mode'].'\', \'del\', \''.$key.'\', this);"  href="javascript:"> - '.__( 'delete', 'easyReservations' ).'</a>';
				} elseif(($chat['time'] + $options['timetodelete']) > time() && $chat['user'] == 'g'.$res->id) $chats_html .= '<a id="a" onclick="easyreservations_send_chat(\''.$key.'\', this);"  href="javascript:"> - '.__( 'delete', 'easyReservations' ).'</a>';
				$chats_html .= '</p>';
			}
		}

		$return = '<div id="chat-container" class="chat-container">';
			$return .= $chats_html;
			$return .= '<div id="easy-chat-add"></div>';
			$return .= '<h1 style="margin:10px 0px 10px 15px">'.$options['title'].'</h1>';
			if($place == 'admin' ) $return .= '<select id="easy-chat-mode"><option value="visible">'.__( 'Message to guest', 'easyReservations' ).'</option><option value="hidden">'.__( 'Message to admin or Note', 'easyReservations' ).'</option></select>';
			$return .= '<input type="hidden" id="easy-chat-nonce" value="'.wp_create_nonce('easy-send-chat').'">';
			$return .= '<textarea style="width:85%;height:100px;margin:0px 0px 8px 15px;" id="easy-chat-message"></textarea>';
			if($place == 'admin' ) $return .= '<input type="button" style="float:right;margin-right:15px" class="easy-button" value="Submit" onclick="easyreservations_send_chat(\''.$res->id.'\', \'\', \'\', \'0\', \'add\', \'\', this); return false;">';
			else $return .= '<input type="button" style="float:right;margin-right:15px" class="easy-button" value="'.__( 'Send', 'easyReservations' ).'" onclick="easyreservations_send_chat(); return false;">';
		$return .= '</div>';

		if(($place == 'edit' && $options['mode'] == 2) || ($place == 'admin' && $options['mode'] != 0)) return $return;
	}

	add_action( 'er_user_edit_after_form', 'easyreservations_generate_chat' );

	function easyreservations_reg_chat(){
		wp_register_script('easyreservations_send_chat', WP_PLUGIN_URL.'/easyreservations/lib/modules/useredit/send_chat.js' , array( "jquery" ));
		wp_register_style('easy-guest-control', WP_PLUGIN_URL . '/easyreservations/lib/modules/useredit/useredit.css', array(), RESERVATIONS_VERSION);
	}

	add_action('wp_enqueue_scripts', 'easyreservations_reg_chat');
	add_action('wp_ajax_easyreservations_send_chat', 'easyreservations_send_chat_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_chat', 'easyreservations_send_chat_callback');

	function easyreservations_send_chat_callback(){
		check_ajax_referer( 'easy-send-chat', 'security' );
		$user =  $_POST['user'];
		$res_id =  $_POST['id'];
		$name =  $_POST['name'];
		$message =  stripslashes(esc_html( $_POST['message']));
		$mode = $_POST['mode'];
		$edit = ''; $link = '';
		if(isset($_POST['edit'])) $edit = $_POST['edit'];
		$key = $_POST['key'];
		$time = time();
		$res = new Reservation($res_id);
		try {
			$new_chat = array( array( 'type' => 'chat', 'mode' => $mode, 'user' => $user, 'time' => time(), 'message' => $message ) );
			if(empty($key) && !is_numeric($key)){
				$index = $res->Customs( $new_chat, false, false, false, 'chat', $mode );
			} elseif($edit == 'edit') {
				$index = $res->Customs( $new_chat, false, $key, false, 'chat', $mode );
			} else {
				$index = $res->Customs( array(),	 false, $key, false, 'chat', $mode );
			}
			$res->editReservation(array('custom'), false);
		} catch(Exception $e){
			echo $e->getMessage();
		}

		end($index); // move the internal pointer to the end of the array
		$key = key($index);  // fetches the key of the element pointed to by the internal pointer
		$options = get_option('reservations_chat_options');
		if(empty($options) || !is_array($options)) exit;
		if($user[0] == 'g'){
			$style = 'chat-user';
			$avatar = '<img class="avatar-15" src="'.$options['guest_img'].'">';
			if(($time-1+$options['timetodelete']) > time()) $link .= '<a id="a" onclick="easyreservations_send_chat(\''.$key.'\', this);"  href="javascript:"> - '.__( 'delete', 'easyReservations' ).'</a>';
		} else {
			if( $mode == 'hidden' ) $style = 'chat-hidden';
			else $style = 'chat-admin';
			$avatar = get_avatar( $user, 15 );
			$link = '<a onclick="easyreservations_send_chat(\''.$res_id.'\', \''.$name.'\', \''.$user.'\', \''.$mode.'\', \'edit\', \''.$key.'\', this);"  href="javascript:"> - '.__( 'edit', 'easyReservations' ).'</a>';
			$link .= '<a onclick="easyreservations_send_chat(\''.$res_id.'\', \''.$name.'\', \''.$user.'\', \''.$mode.'\', \'del\', \''.$key.'\', this);"  href="javascript:"> - '.__( 'delete', 'easyReservations' ).'</a>';
		}

		$chats_html = '<p class="chat-entry-h1 '.$style.'" style="border-top:none;">';
		if($options['img'] == 1) $chats_html .= $avatar;
		$chats_html .= '<b class="chat-entry-bold">'.ucfirst($name).'</b>';
		if($options['time'] == 1) $chats_html .= '<span class="chat-entry-time">'.human_time_diff( $time+1 ).' '.__( 'ago', 'easyReservations' ).'</span>';
		$chats_html .= '</p><p class="chat-entry-p" style="padding-left:3px;"><span>'.$message.'</span>'.$link.'</p>';
		echo $chats_html;
		exit;
	}

	if(isset($_GET['page']) && isset($_GET['view']) && $_GET['page'] == 'reservations'){
		add_action('admin_head', 'easyreservations_send_chat');
	}

	function easyreservations_send_chat(){
		$nonce = wp_create_nonce( 'easy-send-chat' );
		global $current_user;
		$current_user = wp_get_current_user();
		$name = $current_user->display_name;
		?><script type="text/javascript" >
			var chatClick = 0;
			function easyreservations_send_chat(id, name, user, mode, edit, key, t){
				var message_field = document.getElementById('easy-chat-message');
				if(message_field) var message = message_field.value;
				else message = '';

				if(edit == 'edit' ){
					if(chatClick == 0){
						var chatp = t.previousSibling;
						var chatvalue = chatp.innerHTML;
						chatp.innerHTML = '<textarea>'+chatvalue+'</textarea>';
						chatClick = 1;
						return '';
					} else if(chatClick == 1){
						var chatp = t.previousSibling;
						var message = chatp.firstChild.value;
						chatp.innerHTML = message;
						chatClick = 0;
					}
				}

				if(edit == 'del'){
					var field1 = t.parentNode;
					var field2 = field1.previousSibling;
					field1.parentNode.removeChild(field2);
					field1.parentNode.removeChild(field1);
				} else if(!key) {
					var key = '';
				}

				if(!mode || mode == 0){
					var modefield = document.getElementById('easy-chat-mode');
					if(modefield) var mode = modefield.value;
					else mode =  'visible';
				}
				if(name == '') name = '<?php echo $name;?>';
				if(user == '') user = '<?php echo $current_user->ID;?>';
				var data = {
					action: 'easyreservations_send_chat',
					security: '<?php echo $nonce; ?>',
					message:message,
					mode:mode,
					key:key,
					edit:edit,
					name:name,
					user:user,
					id: id
				};
				jQuery.post(ajaxurl, data, function(response) {
					if(key == '' || edit == ''){
						var element = document.getElementById('easy-chat-add');
						element.innerHTML += response;
					}
				});
			}
		</script><?php
	}

	function easyreservations_return_table_new_chat($res){
		$chat_options = get_option('reservations_chat_options');
		if(empty($chat_options) || !is_array($chat_options)) return '';

		if($chat_options['table'] == 1){
			$chats = $res->getCustoms($res->custom, 'chat');
			if(is_array($chats) && !empty($chats)){
				end($chats);
				$key = key($chats);
				if(isset($chats[$key]['user']) && substr($chats[$key]['user'], 0, 1) == 'g'){
					$nummer = 1;
					for($i=1;$i < 10;$i++){
						if(isset($chats[$key-$i]) && substr($chats[$key]['user'], 0, 1) == 'g') $nummer++;
						else break;
					}
					if($nummer > 0){
						echo '<span class="update-plugins"><a href="admin.php?page=reservations&view='.$res->id.'">'.$nummer.'</span>';
					}
				}
			}
		}
	}
	add_action('er_table_name_custom', 'easyreservations_return_table_new_chat', 10 ,1);

if(is_admin()){
	function easyreservations_add_useredit_mails_to_array($emails){
		$newemail = array();
		$newemail[] = array('reservations_email_to_user_edited' => array('name' => __('Mail to guest after guest edited'), 'option' => get_option('reservations_email_to_user_edited'), 'name_subj' => 'reservations_email_to_user_edited_subj', 'name_msg' => 'reservations_email_to_user_edited_msg', 'standard' => '5', 'name_active' => 'reservations_email_to_user_edited_check'));
		$newemail[] = array('reservations_email_to_admin_edited'	 => array('name' => __('Mail to admin after guest edited'), 'option' => get_option('reservations_email_to_admin_edited'), 'name_subj' => 'reservations_email_to_admin_edited_subj', 'name_msg' => 'reservations_email_to_admin_edited_msg', 'standard' => '6', 'name_active' => 'reservations_email_to_admin_edited_check'));
		$newemail[] = array('reservations_email_to_admin_canceled' => array('name' => __('Mail to admin after guest canceled'), 'option' => get_option('reservations_email_to_admin_canceled'), 'name_subj' => 'reservations_email_to_admin_canceled_subj', 'name_msg' => 'reservations_email_to_admin_canceled_msg', 'standard' => '10', 'name_active' => 'reservations_email_to_admin_canceled_check'));
		$emails = $emails + $newemail[0] + $newemail[1];
		return $emails;
	}

	add_filter('easy-email-types', 'easyreservations_add_useredit_mails_to_array', 11, 1);

	function easyreservations_add_userpc_settings_tab(){
		$current = isset($_GET['site']) && $_GET['site'] == "userpc" ? 'current' : '';
		$tab = '<li ><a href="admin.php?page=reservation-settings&site=userpc" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_URL.'images/user.png"> '. __( 'User Control Panel' , 'easyReservations' ).'</a></li>';
		echo $tab;
	}

	add_action('er_set_tab_add', 'easyreservations_add_userpc_settings_tab');

	function easyreservations_save_usercp_settings(){
		if(isset($_POST['easy-chat-modus'])){
			$modus = isset($_POST['easy-chat-modus']) ? $_POST['easy-chat-modus'] : 0;
			$dummy_user = isset($_POST['easy-chat-dummy-user']) ? $_POST['easy-chat-dummy-user'] : 0;
			$dummy_message = isset($_POST['easy-chat-dummy-message']) ? $_POST['easy-chat-dummy-message'] : 0;
			$timetodelete = isset($_POST['easy-chat-timetodelete']) ? $_POST['easy-chat-timetodelete'] : 0;
			$guest_img = isset($_POST['easy-chat-guest-img']) ? $_POST['easy-chat-guest-img'] : 0;
			$img = isset($_POST['easy-chat-img']) ? 1 : 0;
			$time = isset($_POST['easy-chat-time']) ? 1 : 0;
			$dummy = isset($_POST['easy-chat-dummy']) ? 1 : 0;
			$table = isset($_POST['easy-chat-table']) ? 1 : 0;
			$title = isset($_POST['easy-chat-title']) ? $_POST['easy-chat-title'] : '';
			$option = array( 'mode' => $modus, 'img' => $img, 'time' => $time, 'title' => $title, 'table' => $table, 'timetodelete' => $timetodelete, 'dummy' => $dummy, 'dummy_user' => $dummy_user, 'dummy_message' => $dummy_message, 'guest_img' => $guest_img );
			update_option('reservations_chat_options', $option);
		}
		if(isset($_POST["reservations_edit_url"])){
			update_option("reservations_edit_url", $_POST["reservations_edit_url"]);
			if(isset( $_POST["reservations_edit_table_infos"])) $table_infos = $_POST["reservations_edit_table_infos"]; else $table_infos = array();
			if(isset( $_POST["reservations_edit_table_status"])) $table_status = $_POST["reservations_edit_table_status"]; else $table_status = array();
			if(isset( $_POST["er_control_panel_payment"])) $table_payment = $_POST["er_control_panel_payment"]; else $table_payment = 0;
			if(isset( $_POST["reservations_edit_table_time"])) $table_time = $_POST["reservations_edit_table_time"]; else $table_time = array();
			if(isset( $_POST['reservations_cancel_checkbox'])) $cancel = $_POST["easy_cancel_days"]; else $cancel = 0;
			if(isset( $_POST["reservations_edit_table_style"])) $table_style = 1; else $table_style = 0;
			if(isset( $_POST["reservations_edit_table_more"])) $table_more = 1; else $table_more = 0;
			$edit_options = array( 'login_text' => stripslashes($_POST["reservations_edit_login_text"]), 'edit_text' => stripslashes($_POST["reservations_edit_text"]), 'submit_text' => stripslashes($_POST["reservations_submit_text"]), 'table_infos' => $table_infos, 'table_status' => $table_status, 'table_time' => $table_time, 'table_style' => $table_style, 'table_more' => $table_more, 'payment' => $table_payment, 'cancel' => $cancel );
			update_option('reservations_edit_options', $edit_options);
			echo '<div class="updated"><p>'.__( 'User Control Panel settings saved' , 'easyReservations' ).'</p></div>';
		}
	}

	function easyreservations_generate_usercp_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "userpc"){
		$reservations_edit_url=get_option("reservations_edit_url");
		$reservations_edit_options=get_option("reservations_edit_options");
		if(!$reservations_edit_options) $reservations_edit_options = array(); ?>
		<form method="post" action ="admin.php?page=reservation-settings&site=userpc" id="er_usercp_set">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-top:7px">
				<thead>
					<tr>
						<th colspan="2"> <?php printf ( __( 'User Control Panel settings' , 'easyReservations' ));?> </th>
					</tr>
				</thead>
				<tbody>
					<tr class="alternate">
						<td style="width:45%">
							<b><?php printf ( __( 'URL of page with shortcode' , 'easyReservations' ));?> [easy_edit]</b>:
						</td>
						<td>
							<input type="text" name="reservations_edit_url" value="<?php echo $reservations_edit_url;?>" style="width:50%">
						</td>
					</tr>
					<tr>
						<td>
							<b><?php echo __( 'Cancel' , 'easyReservations' );?></b>:
						</td>
						<td>
							 <input type="checkbox" name="reservations_cancel_checkbox" <?php checked(($reservations_edit_options['cancel'] !== 0) ? true : false, true); ?>> <?php echo sprintf(__( 'Let the guest cancel his reservation till %s days before arrival' , 'easyReservations' ), '<select name="easy_cancel_days"><option value="1">0</option>'.easyreservations_num_options(array(1,1),365,$reservations_edit_options['cancel']).'</select>'); ?>
						</td>
					</tr>
					<tr class="alternate">
						<td>
							<b><?php printf ( __( 'Text after Submit' , 'easyReservations' ));?></b>:
						</td>
						<td>
							<input type="text" name="reservations_submit_text" value="<?php echo $reservations_edit_options['submit_text'];?>" style="width:50%">
						</td>
					</tr>
					<?php do_action('easy-control-panel-set', $reservations_edit_options); ?>
					<tr>
						<td>
							&nbsp;<i><?php printf ( __( 'Text over login area - optional' , 'easyReservations' ));?>:</i>
							<textarea name="reservations_edit_login_text" style="width:100%;height:80px;margin-top:4px"><?php echo $reservations_edit_options['login_text']; ?></textarea>
						</td>
						<td>
							&nbsp;<i><?php echo __( 'Text over edit area - optional' , 'easyReservations' ); ?>:</i>
							<textarea name="reservations_edit_text" style="width:100%;height:80px;margin-top:4px"><?php echo $reservations_edit_options['edit_text']; ?></textarea>
						</td>
					</tr>
					<tr id="easy-edit-table-cols" class="alternate">
						<td colspan="2">
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Table Settings' , 'easyReservations' ); ?></b><br>
								<i><?php echo __( 'Table of other reservations from the same email' , 'easyReservations' ); ?></i><br>
							</span>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Information' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="id" <?php checked(in_array('id', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'ID' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="name" <?php checked(in_array('name', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Name' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="date" <?php checked(in_array('date', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Date' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="persons" <?php checked(in_array('persons', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Persons' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="reservated" <?php checked(in_array('reservated', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Reservated' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="status" <?php checked(in_array('status', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Status' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="room" <?php checked(in_array('room', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Resource' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="roomn" <?php checked(in_array('roomn', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Resource Number' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_infos[]" value="price" <?php checked(in_array('price', $reservations_edit_options['table_infos']), true); ?>> <?php echo __( 'Price' , 'easyReservations' ); ?></label>
							</span>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Status' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="yes" <?php checked(in_array('yes', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'approved' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="" <?php checked(in_array('', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'pending' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="no" <?php checked(in_array('no', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'rejected' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_status[]" value="del" <?php checked(in_array('del', $reservations_edit_options['table_status']), true); ?>> <?php echo __( 'trashed' , 'easyReservations' ); ?></label><br>
								<b><?php echo __( 'Time' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_time[]" value="past" <?php checked(in_array('past', $reservations_edit_options['table_time']), true); ?>> <?php echo __( 'Past' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_time[]" value="current" <?php checked(in_array('current', $reservations_edit_options['table_time']), true); ?>> <?php echo __( 'Current' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_time[]" value="future" <?php checked(in_array('future', $reservations_edit_options['table_time']), true); ?>> <?php echo __( 'Future' , 'easyReservations' ); ?></label><br>
							</span>
							<span style="width:25%;float:left" >
								<b><?php echo __( 'Settings' , 'easyReservations' ); ?></b><br>
								<label><input type="checkbox" name="reservations_edit_table_style" <?php checked($reservations_edit_options['table_style'], 1); ?>> <?php echo __( 'Use style for table' , 'easyReservations' ); ?></label><br>
								<label><input type="checkbox" name="reservations_edit_table_more" value="" <?php checked($reservations_edit_options['table_more'], 1); ?>> <?php echo __( 'Only display table if guest reserved more than once' , 'easyReservations' ); ?></label><br>
							</span>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="button" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>" onclick="document.getElementById('er_usercp_set').submit(); return false;" style="margin-top:7px;" class="easybutton button-primary" style="margin-top:4px" >
			<?php
		$chat_options = get_option('reservations_chat_options');
		if(!empty($chat_options) && is_array($chat_options)) $options = $chat_options;
		else $options = array('mode' => 2, 'img' => 1, 'time' => 1, 'title' => 'Speak with us!', 'table' => 1, 'timetodelete' => 3600, 'name' => 1, 'dummy' => 1, 'dummy_user' => 1, 'dummy_message' => 'Thank you for reserving in our hotel. If you have any question feel free to ask.', 'guest_img' => RESERVATIONS_URL.'images/guest.png' ); ?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;margin-top:7px">
				<thead>
					<tr>
						<th colspan="2"><?php echo __( 'Guest contact settings' , 'easyReservations' );?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="width:20%;font-weight:bold"><?php echo __( 'Mode' , 'easyReservations' );?></td>
						<td>
							<select name="easy-chat-modus">
								<option value="0" <?php echo selected($options['mode'], 0); ?>><?php echo __( 'Off' , 'easyReservations' );?></option>
								<option value="1" <?php echo selected($options['mode'], 1); ?>><?php echo __( 'Only admin' , 'easyReservations' );?></option>
								<option value="2" <?php echo selected($options['mode'], 2); ?>><?php echo __( 'User Control Panel and admin' , 'easyReservations' );?></option>
							</select>
						</td>
					</tr>
					<tr class="alternate">
						<td style="vertical-align:top;font-weight:bold"><?php echo __( 'Title' , 'easyReservations' );?></td>
						<td>
							<input type="text" name="easy-chat-title" value="<?php echo $options['title']; ?>"> <?php echo __( 'Title over textarea' , 'easyReservations' );?><br>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top;font-weight:bold"><?php echo __( 'Information' , 'easyReservations' );?></td>
						<td>
							<input type="checkbox" name="easy-chat-img" value="1" <?php echo checked($options['img'], 1); ?>> <?php echo __( 'Show avatars' , 'easyReservations' );?><br>
							<input type="checkbox" name="easy-chat-time" value="1" <?php echo checked($options['time'], 1); ?>> <?php echo __( 'Show time ago' , 'easyReservations' );?><br>
							<input type="checkbox" name="easy-chat-table" value="1" <?php echo checked($options['table'], 1); ?>> <?php echo __( 'Show unanswered messages in table' , 'easyReservations' );?>
						</td>
					</tr>
					<tr class="alternate">
						<td style="font-weight:bold"><?php echo __( 'Guest Avatar' , 'easyReservations' );?></td>
						<td>
							<input type="text" name="easy-chat-guest-img" value="<?php echo $options['guest_img']; ?>" style="width:200px" > <?php echo __( 'Image as avatar for guests' , 'easyReservations' );?> <img style="width:15px;vertical-align:text-bottom" src="<?php echo $options['guest_img']; ?>">
						</td>
					</tr>
					<tr>
						<td style="font-weight:bold"><?php echo __( 'Dummy' , 'easyReservations' );?></td>
						<td>
							<input type="checkbox" onclick="easy_chat_dummy()" name="easy-chat-dummy" id="easy-chat-dummy" <?php echo checked($options['dummy'], 1); ?>> <?php echo __( 'Show dummy' , 'easyReservations' );?>
						</td>
					</tr>
					<tr class="<?php if($options['dummy'] != 1) echo 'hidden'; ?> alternate" id="easy-chat-dummy-user">
						<td style="font-weight:bold"><?php echo __( 'Dummy user' , 'easyReservations' );?></td>
						<td><select style="margin:3px" name="easy-chat-dummy-user"><?php echo easyreservations_get_user_options($options['dummy_user']); ?></select> <?php echo __( 'User of dummy' , 'easyReservations' );?></td>
					</tr>
					<tr class="<?php if($options['dummy'] != 1) echo 'hidden'; ?>" id="easy-chat-dummy-message">
						<td style="vertical-align:top;font-weight:bold"><?php echo __( 'Dummy message' , 'easyReservations' );?></td>
						<td>
							<textarea name="easy-chat-dummy-message" style="width:99%;height:44px"><?php echo $options['dummy_message']; ?></textarea>
						</td>
					</tr>
					<tr class="alternate">
						<td style="font-weight:bold"><?php echo __( 'Time to delete' , 'easyReservations' );?></td>
						<td>
							<select name="easy-chat-timetodelete">
								<option value="0" <?php echo selected($options['timetodelete'], 0); ?>><?php echo __( 'Not at all' , 'easyReservations' );?></option>
								<option value="300" <?php echo selected($options['timetodelete'], 300); ?>>5 <?php echo __( 'Minutes' , 'easyReservations' );?></option>
								<option value="900" <?php echo selected($options['timetodelete'], 900); ?>>15 <?php echo __( 'Minutes' , 'easyReservations' );?></option>
								<option value="1800" <?php echo selected($options['timetodelete'], 1800); ?>>30 <?php echo __( 'Minutes' , 'easyReservations' );?></option>
								<option value="3600" <?php echo selected($options['timetodelete'], 3600); ?>>1 <?php echo __( 'Hour' , 'easyReservations' );?></option>
								<option value="10800" <?php echo selected($options['timetodelete'], 10800); ?>>3 <?php echo easyreservations_interval_infos(3600,1)?></option>
								<option value="43200" <?php echo selected($options['timetodelete'], 43200); ?>>12 <?php echo easyreservations_interval_infos(3600,1)?></option>
								<option value="86400" <?php echo selected($options['timetodelete'], 86400); ?>>1 <?php echo easyreservations_interval_infos(86400,1,1); ?></option>
								<option value="604800" <?php echo selected($options['timetodelete'], 604800); ?>>1 <?php echo easyreservations_interval_infos(604800,1,1); ?></option>
								<option value="2419200" <?php echo selected($options['timetodelete'], 2419200); ?>>1 <?php echo easyreservations_interval_infos(2592000,1,1); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<script>
				function easy_chat_dummy(){
					if(document.getElementById('easy-chat-dummy').checked == true){
						document.getElementById('easy-chat-dummy-user').className = 'alternate';
						document.getElementById('easy-chat-dummy-message').className = '';
					} else {
						document.getElementById('easy-chat-dummy-user').className = 'hidden alternate';
						document.getElementById('easy-chat-dummy-message').className = 'hidden';
					}
				}
			</script>
			<input type="button" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>" onclick="document.getElementById('er_usercp_set').submit(); return false;" style="margin-top:7px;" class="easybutton button-primary" style="margin-top:4px" >
		</form>
		<?php
		}
	}

	add_action('er_set_add', 'easyreservations_generate_usercp_settings');
	add_action('er_set_save', 'easyreservations_save_usercp_settings');

} else {

	function easyreservations_start_session() {
		@session_cache_limiter('private, must-revalidate'); //private_no_expire
		@session_cache_expire(0);
		@session_start();
	}

	function easyreservations_edit_shortcode($atts){
		if(!session_id()) easyreservations_start_session();
		global $current_user, $easyreservations_script;
		wp_enqueue_script('easy-guest-control');
		wp_enqueue_style('easy-form-none' , false, array(), false, 'all');
		wp_enqueue_style('easy-frontend' , false, array(), false, 'all');
		wp_enqueue_style('easy-useredit', WP_PLUGIN_URL.'/easyreservations/lib/modules/useredit/useredit.css', false);
		$current_user = wp_get_current_user();

		$atts = shortcode_atts(array(
			'credit' => __( 'Your reservation is complete' , 'easyReservations' ),
			'subcredit' => '',
			'multiple' => 0,
		), $atts);

		if(isset($_GET['logout'])){
			session_destroy();
			unset($_SESSION['easy-user-edit-id'] );
			unset($_SESSION['easy-user-edit-email'] );
		}
		$the_link = get_option("reservations_edit_url");
		$edit_options = get_option("reservations_edit_options");
		if(strpos(get_the_content(), '[easy_calendar') !== false) $isCalendar = true;
		else $isCalendar = false;

		if(!isset($_SESSION['easy-user-edit-id']) || !isset($_SESSION['easy-user-edit-email']) || (isset($_SESSION['easy-user-edit-id']) && (!is_numeric($_SESSION['easy-user-edit-id']) || $_SESSION['easy-user-edit-id'] < 1))){
			if(isset($_POST['email']) && isset($_POST['editID'])){
				if(!wp_verify_nonce($_POST['easy-user-edit-login'], 'easy-user-edit-login' ) && !wp_verify_nonce($_POST['easy-user-edit'], 'easy-user-edit' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
				$theMail = (string) $_POST['email'];
				$theID = (int) $_POST['editID'];
				$_SESSION['easy-user-edit-id'] =  $theID;
				$_SESSION['easy-user-edit-email'] =  $theMail;
				if(isset($_POST['captcha_value'])){
					include_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
					$prefix = $_POST['captcha_prefix'];
					$captcha_instance = new easy_ReallySimpleCaptcha();
					$correct = $captcha_instance->check($prefix, $_POST['captcha_value']);
					$captcha_instance->remove($prefix);
					$captcha_instance->cleanup(); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?
					if($correct != 1 || empty($_POST['captcha_value'])) return '<div style="text-align:center;">'.__(  'Please enter the correct captcha code' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
				}
			} elseif(isset($_GET['email']) && isset($_GET['id']) && isset($_GET['ernonce'])){
				if(!easyreservations_verify_nonce($_GET['ernonce'], 'easyusereditlink' )) return '<div style="text-align:center;">'.__('Link is only 24h valid', 'easyReservations').' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
				$theMail = (string) $_GET['email'];
				$theID = (int) $_GET['id'];
				$_SESSION['easy-user-edit-id'] =  $theID;
				$_SESSION['easy-user-edit-email'] =  $theMail;
			}
		} else {
			$theMail = $_SESSION['easy-user-edit-email'];
			$theID = (int) $_SESSION['easy-user-edit-id'];
		}

		if(isset($_GET['newid'])){
			if(!wp_verify_nonce($_GET['wpnonce'], 'easy-change-id' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
			if(is_numeric($_GET['newid'])){
				$theID = (int) $_GET['newid'];
				$_SESSION['easy-user-edit-id'] = $theID;
			}
		}
		$custom_fields = get_option('reservations_custom_fields');

		if(isset($theMail) && !empty($theMail) && isset($theID) && is_numeric($theID) && $theID > 0){
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_style('datestyle' , false, array(), false, 'all');
			wp_enqueue_style('easy-frontend');
			wp_enqueue_script('easyreservations_data');
			wp_enqueue_script('easyreservations_send_validate');
			wp_enqueue_script('easyreservations_send_form');
			$formid = 'easy-useredit-'.rand(0,99999);

			if(isset($atts['price'])){
				wp_enqueue_script( 'easyreservations_send_price' );
				$easyreservations_script .= "easyreservations_send_price('$formid');";
			}
			if(!isset($atts['roomname']) || empty($atts['roomname'])) $atts['roomname'] = __('Resource', 'easyReservations');
			else $atts['roomname'] = __($atts['roomname']);
			if(isset($_POST['thename'])){
				$error = '';
				if(!wp_verify_nonce($_POST['easy-user-edit'], 'easy-user-edit' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
				$fromplus = 0;
				if(isset($_POST['date-from-hour'])) $fromplus = (int) $_POST['date-from-hour'] * 60;
				else $fromplus += 12*60;
				if(isset($_POST['date-from-min'])) $fromplus += (int) $_POST['date-from-min'];
				if($fromplus > 0) $fromplus *= 60;
				$toplus = 0;
				if(isset($_POST['date-to-hour'])) $toplus += (int) $_POST['date-to-hour'] * 60;
				else $toplus += 12*60;
				if(isset($_POST['date-to-min'])) $toplus += (int) $_POST['date-to-min'];
				if($toplus > 0) $toplus *= 60;
				$customfields = array();
				$custompfields = array();

				for($theCount = 0; $theCount < 500; $theCount++){
					if(isset($_POST["custom_value_".$theCount]) && isset($_POST["custom_title_".$theCount])){
						$customfields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$theCount], 'title' => $_POST["custom_title_".$theCount], 'value' => stripslashes($_POST["custom_value_".$theCount]));
					}
					if(isset($_POST["customPvalue".$theCount]) && isset($_POST["customPtitle".$theCount])){
						$custompfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$theCount], 'title' => $_POST["customPtitle".$theCount], 'value' => stripslashes($_POST["customPvalue".$theCount]), 'amount' => $_POST["customPprice".$theCount]);
					}
					if(isset($_POST['easy-new-custom-'.$theCount])){
						$custom = array( 'type' => 'cstm', 'mode' => 'edit', 'id' => $theCount, 'value' => stripslashes($_POST['easy-new-custom-'.$theCount]));
						if(isset($custom_fields['fields'][$theCount]['price'])) $custompfields[] = $custom;
						else $customfields[] = $custom;
					}
				}

				$arrival = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['from'].' 00:00:00')->getTimestamp()+($fromplus);
				$departure = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['to'].' 00:00:00')->getTimestamp()+($toplus);

				$res = new Reservation((int)$theID, false, false);
				try {
					$res->save = (array) $res;
					$res->name = $_POST['thename'];
					$old_email = $res->email;
					$res->email = $_POST['email'];
					$res->resource = (int) $_POST["easyroom"];
					$res->adults = (int) $_POST["persons"];
					$res->childs = (int) $_POST["childs"];
					$res->country = $_POST['country'];
					$res->status = '';
					$res->arrival = $arrival;
					$res->departure = $departure;
					$res->getTimes(0);
					$res->Customs($customfields, true, false, false, 'cstm', 'edit');
					$res->Customs($custompfields, true, false, true, 'cstm', 'edit');
					if(isset($_POST['coupon'])) $res = apply_filters('easy-edit-res-ajax', $res);
					$returns = $res->editReservation(array('all'), true, array('reservations_email_to_admin_edited', 'reservations_email_to_user_edited', 'reservations_email_to_user_edited'), array(false, $res->email, $old_email));
					if($returns){
						foreach($returns as $key => $zerror){
							if($key%2==0) $error.= '<li><label for="'.$returns[$key].'">'.$returns[$key+1].'</label></li>';
						}
					}
					$res->destroy();
				} catch(Exception $e){
					$error .= '<li><label>'.$e->getMessage().'</label></li>';
				}
			} elseif(isset($_GET['cancel']) && isset($edit_options['cancel']) && $edit_options['cancel'] > 0){
				if(!wp_verify_nonce($_GET['_wpnonce'], 'easy-user-cancel' )) return '<div style="text-align:center;">'.__(  'An error occurred, please try again' , 'easyReservations' ).' - <a href="'.$the_link.'">'.__( 'back' , 'easyReservations' ).'</a></div>';
				$res = new Reservation((int) $theID);
				try {
					$daybetween = ($res->arrival - time())/86400;
					if($daybetween >= $edit_options['cancel']-1){
						$res->status = 'no';
						$res->editReservation(array('status'),false,array('reservations_email_to_admin_canceled'), array(false));
						return '<div id="easy_cancel_message" class="easy_cancel_message">'.__('Reservation canceled', 'easyReservations').' <a href="'.$the_link.'">'.__('back', 'easyReservations').'</a></div>';
					} else $error = '<li><label>'.__('Too late to cancel', 'easyReservations').'</label></li>';
				} catch(Exception $e){
					$error =  '<li><label>'.$e->getMessage().'</label></li>';
				}
			}

			$return = '';
			if(isset($_POST['thename']) && empty($error)){ //When Check gives no error Insert into Database and send mail

				if(isset($edit_options['submit_text']) && !empty($edit_options['submit_text'])) $submit = $edit_options['submit_text'];
				else $submit = __( 'Your Reservation was edited' , 'easyReservations' );
				$return .= '<div class="easy_form_success">'.$submit.'</div>';
			}

			do_action( 'er_edit_add_action' );
			add_action('wp_print_footer_scripts', 'easyreservations_make_datepicker_edit');

			if(isset($atts['daysbefore'])) $daysbeforearival = $atts['daysbefore'];
			else $daysbeforearival = 10;
			if(isset($atts['invoice'])) $use_invoice = $atts['invoice'];
			else $use_invoice = 0;
			if(isset($atts['table'])) $show_table = $atts['table'];
			else $show_table = 1;
			easyreservations_load_resources();
			global $the_rooms_array, $wpdb;

			if($show_table == 1){
				if(isset($edit_options['table_status'])) $status_sql = 'AND approve IN(\''.substr(implode("', '", $edit_options['table_status']), 0, -5).')';
				else $status_sql = '';
				if(isset($edit_options['table_time'])){
					$time_sql = '';
					if(in_array('past', $edit_options['table_time'])) $time_sql .= "OR departure < NOW() ";
					if(in_array('current', $edit_options['table_time'])) $time_sql .= "OR NOW() BETWEEN arrival AND departure ";
					if(in_array('future', $edit_options['table_time'])) $time_sql .= "OR (arrival > NOW() AND NOW() NOT BETWEEN arrival AND departure)";
					if(!empty($time_sql)){
						$time_sql = ' AND ('.substr($time_sql, 2).')';
					}
				}  else $time_sql = '';
				$other_query = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix ."reservations WHERE email='%s' $status_sql $time_sql ORDER BY arrival DESC", array( $theMail )));
				if(!isset($edit_options['table_more']) || !is_numeric($edit_options['table_more'])) $edit_options['table_more'] = 1;
				if(isset($other_query[$edit_options['table_more']])){
					$in_id = in_array('id', $edit_options['table_infos']);
					$in_date = in_array('date', $edit_options['table_infos']);
					$in_name = in_array('name', $edit_options['table_infos']);
					$in_status = in_array('status', $edit_options['table_infos']);
					$in_persons = in_array('persons', $edit_options['table_infos']);
					$in_room = in_array('room', $edit_options['table_infos']);
					$in_reservated = in_array('reservated', $edit_options['table_infos']);
					$in_price = in_array('price', $edit_options['table_infos']);

					$class = isset($edit_options['table_style']) && $edit_options['table_style'] == 1 ? 'class="easy-front-table"' : '';
					$return .= '<table id="edittable" '.$class.'><thead><tr>';
					if($in_id) $return .= '<th>'.__( 'ID' , 'easyReservations' ).'</th>';
					if($in_date) $return .= '<th>'.__( 'Date' , 'easyReservations' ).'</th>';
					if($in_name) $return .= '<th>'.__( 'Name' , 'easyReservations' ).'</th>';
					if($in_status) $return .= '<th class="center">'.__( 'Status' , 'easyReservations' ).'</th>';
					if($in_persons) $return .= '<th class="center">'.__( 'Persons' , 'easyReservations' ).'</th>';
					if($in_room) $return .= '<th>'.$atts['roomname'].'</th>';
					if($in_reservated) $return .= '<th>'.__( 'Reserved' , 'easyReservations' ).'</th>';
					if($in_price) $return .= '<th class="right">'.__( 'Price' , 'easyReservations' ).'</th>';
					$return .= '<th></th></tr></thead><tbody>';
					foreach($other_query as $thereservation){
						$reservation = new Reservation($thereservation->id, (array) $thereservation);
						try {
							$reservation->Calculate();
							$class = $theID == $reservation->id ? 'class="current"' : '';
							$return .= '<tr '.$class.'>';
								if($in_id) $return .= '<td>'.$reservation->id.'</td>';
								if($in_date) $return .= '<td>'.date(RESERVATIONS_DATE_FORMAT, $reservation->arrival).' - '.date(RESERVATIONS_DATE_FORMAT, $reservation->departure).'</td>';
								if($in_name) $return .= '<td>'.($reservation->name).'</td>';
								if($in_status) $return .= '<td class="center">'.$reservation->getStatus(true).'</td>';
								if($in_persons) $return .= '<td class="center">'.($reservation->adults+$reservation->childs).'</td>';
								if($in_room){
									if(isset($the_rooms_array[$reservation->resource])) $return .= '<td>'.__($the_rooms_array[$reservation->resource]->post_title).'</td>';
									else $return .= '<td>'.__('Unknown', 'easyReservations').'</td>';
								}
								if($in_reservated) $return .= '<td>'.human_time_diff($reservation->reservated, time()).' '.__( 'ago' , 'easyReservations' ).'</td>';
								if($in_price) $return .= '<td class="right" style="white-space:nowrap">'.$reservation->formatPrice(true).'</td>';
								$return .= '<td><a  href="'.$the_link.'?edit&newid='.$reservation->id.'&wpnonce='.wp_create_nonce( 'easy-change-id' ).'"><img src="'.RESERVATIONS_URL.'images/book.png" style="vertical-align:text-bottom"></a></td>';
							$return .= '</tr>';
						} catch(Exception $e){

						}
					}
					$return .= '</tbody></table>';
				}
			}
			$invoice = false;

			if(isset($_GET['invoice'])) $invoice = true;
			try {
				$res = new Reservation((int) $theID);
				if($res->email !== $theMail) return __('Wrong email', 'easyReservations').' <a href="'.$the_link.'?logout">'.__('back', 'easyReservations').'</a>';
				$res->Calculate();
				if(time() > $res->arrival+(86400*$daysbeforearival)){
					$resPast = 1;
					$pastError = '<li>'.__( 'Please contact us to edit your reservation' , 'easyReservations' ).'</li>';
				} else {
					$resPast = 0;
					$pastError = '';
				}

				$left = $res->price - $res->paid; $paypal = ''; $paypal_overlay = '';

				if(function_exists('easyreservations_generate_paypal_button') && $left > 0 && isset($edit_options['payment']) && $edit_options['payment'] !== 0 ){
					$paypal = '<a href="javascript:" onclick="jQuery(\'#easyFormOverlay,#easyFormInnerlay\').fadeIn(\'fast\');" class="paypal">'.__( 'Pay now' , 'easyReservations' ).'</a>';
					//$paypal = easyreservations_generate_paypal_button($res, $left, false, $paypal_button, false,(isset($edit_options['payment']) && $edit_options['payment'] !== 0 && !empty($edit_options['payment'])) ? $edit_options['payment'] : 'paypal');
					$paypal_overlay .= '<div id="easyFormOverlay" class="full"><div id="easyFormInnerlay" class="easy_form_success" style="350px;">';
					$paypal_overlay .= easyreservation_generate_payment_form($res, $left, false).'</div></div>';
					$paypal_overlay .= '<script>jQuery(document).keyup(function(e){if(e.keyCode == 27) jQuery(\'#easyFormOverlay,#easyFormInnerlay\').fadeOut(\'fast\');});</script>';
					$easyreservations_script.= str_replace(array("\n","\r"), '', trim('var easyReservationAtts = '.json_encode($atts).';'));
				}

				if(!empty($edit_options['edit_text'])) $return .= '<div style="margin-left:auto;text-align:center;margin-right:auto;margin: 0px 5px;padding:5px 5px;">'.$edit_options['edit_text'].'</div>';
				$return .= $paypal_overlay.'<div class="easy-edit-status">';
				$class = !$invoice ? 'current' : '';
				$return .= '<a href="'.$the_link.'?edit" class="'.$class.'">'.__( 'Information' , 'easyReservations' ).'</a>';
				if($use_invoice > 0&& function_exists('easyreservations_generate_invoice')){
					$class = $invoice ? 'current' : $class = '';
					if($invoice) $return .= '<a href="javascript:" class="'.$class.'" onclick="document.getElementById(\'invoicesave\').value = document.getElementById(\'invoice\').innerHTML;document.getElementById(\'generatepdf\').submit();">'.__( 'Download' , 'easyReservations' ).'</a>';
					else $return .= '<a href="'.$the_link.'?edit&invoice" class="'.$class.'">'.__( 'Invoice' , 'easyReservations' ).'</a>';
				}
				if(isset($atts['status'])) $return .= ' '.ucfirst($res->getStatus(true));
				if(isset($atts['price'])) $return .= ' '.__( 'Price' , 'easyReservations' ).': <b>'.easyreservations_format_money($res->price, 1).'</b> | '.__( 'Paid' , 'easyReservations' ).': <b>'.easyreservations_format_money($res->paid, 1).'</b>';
				$return .= '<a style="color:#ff0000;text-decoration:underline;border-right:0px !important;border-left:1px solid #EAEAEA;float:right;" href="'.$the_link.'?edit&logout">'.__( 'logout' , 'easyReservations' ).'</a>';
				$return .= $paypal;
				$return .= '</div>';

				if($invoice && $use_invoice > 0){
					if(function_exists('easyreservations_generate_invoice')){
						$theinvoice = easyreservations_generate_invoice($res);
						$return .= '<div style="width:98%;margin-left:auto;margin-right:auto;border: 1px solid #EAEAEA;border-top:0px;" id="invoice">'.(str_replace('contenteditable="true"', '', $theinvoice['content'])).'</div>
							<form id="generatepdf" action="'.WP_PLUGIN_URL.'/easyreservations/lib/modules/invoice/generate.php" method="post" style="display:hidden">'.wp_nonce_field('easy-invoice', 'easy-invoice').'<input type="hidden" id="invoicesave" name="invoice" value="">
							<input type="hidden" id="invoice_preview" name="invoice_preview" value=""><input type="hidden" id="filename" name="filename" value="'.$theinvoice['filename'].'"><input type="hidden" id="id" name="id" value="'.$res->id.'"></form>';
					}
				} else {
					$return .= '<form onsubmit="easyreservations_send_validate(\'send\', \''.$formid.'\'); return false;" method="post" style="width:98%;margin-left:auto;margin-right:auto;border: 1px solid #EAEAEA;border-top:0px;" id="easyFrontendFormular" name="easyFrontendFormular">';
					if(function_exists('easyreservations_generate_chat')) $chat = easyreservations_generate_chat( $res, 'edit' );
					else $chat = false;

					if($chat){
						$padding = '60px';
						$return .= $chat.'<div id="'.$formid.'" class="usereditdiv" style="width:60%;padding:15px 0px;">';
					} else {
						$return .= '<div id="'.$formid.'" class="usereditdiv" style="width:400px;margin-left:auto;margin-right:auto;padding:30px 0px;">';
						$padding = '40px';
					}
					$v_action = 'easyreservations_send_validate(false,\''.$formid.'\');';
					$p_action = '';
					if(isset($atts['price'])) $p_action = 'easyreservations_send_price(\''.$formid.'\');';

					if(isset($error)) $pastError = $pastError.$error;
					if(isset($pastError) && !empty($pastError)) $hideclass= '';
					else $hideclass = ' hide-it';

					$return .= '<div id="easy-show-error-div" class="easy-show-error-div'.$hideclass.'"><ul id="easy-show-error">'.$pastError.'</ul></div>';
					$return .= '<input name="pricenonce" type="hidden" value="'.wp_create_nonce('easy-price').'">';
					$return .= '<input name="editID" id="editID" type="hidden" value="'.$theID.'">';
					$return .= '<input name="userID" id="userID" type="hidden" value="'.$current_user->ID.'">';
					$return .= '<input name="old_email" type="hidden" value="'.$res->email.'">';
					$return .= '<input name="reserved" type="hidden" value="'.$res->reservated.'">';
					$return .= '<input name="easy-user-edit" type="hidden" value="'.wp_create_nonce('easy-user-edit').'">';
					$return .= '<label>'.__( 'Name' , 'easyReservations' ).'<span class="small">'.__( 'Your name' , 'easyReservations' ).'</span></label><input type="text" name="thename" id="easy-form-thename" onchange="'.$v_action.'" value="'.$res->name.'">';
					$return .= '<label>'.__( 'Email' , 'easyReservations' ).'<span class="small">'.__( 'Your email' , 'easyReservations' ).'</span></label><input type="text" name="email" id="easy-form-email" onchange="'.$p_action.$v_action.'" value="'.$res->email.'">';
					$return .= '<label>'.__( 'From' , 'easyReservations' ).'<span class="small">'.__( 'The arrival date' , 'easyReservations' ).'</span></label><span class="row"><input type="text" name="from" style="width:75px" onchange="'.$p_action.$v_action.'" id="easy-form-from" value="'.date(RESERVATIONS_DATE_FORMAT, $res->arrival).'"><select id="date-from-hour" name="date-from-hour" style="width:auto" onchange="'.$p_action.$v_action.'">'.easyreservations_time_options(date('G', $res->arrival)).'</select>:<select id="date-from-min" name="date-from-min" style="width:48px" onchange="'.$p_action.$v_action.'">'.easyreservations_num_options("00", 59, date('i', $res->arrival)).'</select></span>';
					$return .= '<label>'.__( 'To' , 'easyReservations' ).'<span class="small">'.__( 'The departure date' , 'easyReservations' ).'</span></label><span class="row"><input type="text" name="to" style="width:75px" onchange="'.$p_action.$v_action.'" id="easy-form-to" value="'.date(RESERVATIONS_DATE_FORMAT, $res->departure).'"><select id="date-to-hour" name="date-to-hour" style="width:auto"  onchange="'.$p_action.$v_action.'">'.easyreservations_time_options(date('G', $res->departure)).'</select>:<select id="date-to-min" name="date-from-min" style="width:48px" onchange="'.$p_action.$v_action.'">'.easyreservations_num_options("00", 59, date('i', $res->departure)).'</select></span>';
					$return .= '<label>'.__( 'Persons' , 'easyReservations' ).'<span class="small">'.__( 'Amount of persons' , 'easyReservations' ).'</span></label><select name="persons" id="easy-form-persons" onchange="'.$p_action.$v_action.'">'.easyreservations_num_options(1,50,$res->adults).'</select>';
					$return .= '<label>'.__( 'Children\'s' , 'easyReservations' ).'<span class="small">'.__( 'Amount of children' , 'easyReservations' ).'</span></label><select name="childs" onchange="'.$p_action.$v_action.'">'.easyreservations_num_options(0,50,$res->childs).'</select>';
					$return .= '<label>'.__( 'Country' , 'easyReservations' ).'<span class="small">'.__( 'Select your country' , 'easyReservations' ).'</span></label><select name="country">'.easyreservations_country_options($res->country).'</select>';
					if($isCalendar) $calendar_js = 'document.CalendarFormular.easyroom.value=this.value;easyreservations_send_calendar(\'shortcode\');';
					else $calendar_js = '';
					$return .= '<label>'.$atts['roomname'].'<span class="small">'.__( 'Choose the' , 'easyReservations' ).' '.$atts['roomname'].'</span></label><select  name="easyroom" id="room" onChange="'.$calendar_js.$p_action.$v_action.'">'.easyreservations_resource_options($res->resource).'</select>';
					if(!empty($res->custom)){
						$customs=$res->getCustoms($res->custom, 'cstm', 'edit');
						if(!empty($customs)){
							foreach($customs as $key => $custom){
								if(isset($custom['id'])){
									if($custom['mode'] == 'visible' || $custom['mode'] == 'edit'){
										$return .= '<label>'.__($custom_fields['fields'][$custom['id']]['title']).'<span class="small">'.__( "Type in information" , "easyReservations" ).'</span></label>';
										if($custom['mode'] == 'edit') $return .= easyreservations_generate_custom_field($custom['id'], $custom['value']);
										else $return .= '<input type="hidden" name="easy-new-custom-'.$custom['id'].'" value="'.$custom['value'].'">';
									} else {
										$return .= '<input type="hidden" name="easy-new-custom-'.$custom['id'].'" value="'.$custom['value'].'">';
									}
								} else {
									if($custom['mode'] == 'visible' || $custom['mode'] == 'edit'){
										$return .= '<label>'.__($custom['title']).'<span class="small">'.__( "Type in information" , "easyReservations" ).'</span></label>';
										$return .= '<input type="hidden" name="custom_title_'.$key.'" value="'.$custom['title'].'">';
										if($custom['mode'] == 'edit') $return .= '<input type="text" name="custom_value_'.$key.'" value="'.$custom['value'].'"><input type="hidden" value="edit" name="custommodus'.$key.'">';
										else $return .= '<span style="display:inline-block;min-width:150px;min-height:40px;margin-left:10px">'.$custom['value'].'<input type="hidden" name="custommodus'.$key.'" value="visible"><input type="hidden" name="custom_value_'.$key.'" value="'.$custom['value'].'"></span>';
									}
								}
							}
						}
					}
					if(!empty($res->prices)){
						$customps=$res->getCustoms($res->prices, 'cstm', 'edit');
						if(!empty($customps)){
							foreach($customps as $thenumber2 => $customp){
								if(isset($customp['id'])){
									if($customp['mode'] == 'visible' || $customp['mode'] == 'edit'){
										$return .= '<label>'.__($custom_fields['fields'][$customp['id']]['title']).'<span class="small">'.__( "Extra service" , "easyReservations" ).'</span></label>';
										if($customp['mode'] == 'edit') $return .= easyreservations_generate_custom_field($customp['id'], $customp['value'], 'onchange="'.$p_action.'"');
										else $return .= '<input type="hidden" name="easy-new-custom-'.$customp['id'].'" value="'.$customp['value'].'">'.$custom_fields['fields'][$customp['id']]['options'][$customp['value']]['value'].': '.easyreservations_format_money($res->calculateCustom($customp['id'], $customp['value']),1);
									} else {
										$return .= '<input type="hidden" name="easy-new-custom-'.$customp['id'].'" value="'.$customp['value'].'">';
									}
								} else {
									if($customp['mode'] == 'visible' || $customp['mode'] == 'edit'){
										$return .= '<label>'.__($customp['title']).'<span class="small">'.__( "Extra service" , "easyReservations" ).'</span></label><span class="formblock" style="width:50%"><b>'.$customp['value'].':</b> '.easyreservations_format_money($customp['amount'], 1);
										if($customp['mode'] == 'edit') $return .= '<input type="checkbox"  id="custom_price'.$thenumber2.'" value="test:'.$customp['amount'].'" onchange="'.$p_action.'" checked ><input name="customPmodus'.$thenumber2.'" type="hidden" value="edit">';
										else $return .= '<input type="hidden" name="customPmodus'.$thenumber2.'" value="visible"><input type="hidden" id="custom_price'.$thenumber2.'" value="test:'.$customp['amount'].'">';
										$return .= '<input type="hidden" name="customPtitle'.$thenumber2.'" value="'.$customp['title'].'"><input type="hidden" name="customPvalue'.$thenumber2.'" value="'.$customp['value'].'"><input type="hidden" name="customPprice'.$thenumber2.'" value="'.$customp['amount'].'"></span>';
									}
								}
							}
						}
						$coupons = $res->getCustoms($res->prices, 'coup');
						if(!empty($coupons)){
							foreach($coupons as $key => $coupon){
								$return .= '<label>'.__( 'Coupon' , 'easyReservations' ).'<span class="small">'.__( 'Discount Code' , 'easyReservations' ).'</span></label><input type="text" name="coupon[]" id="easy-form-coupon[]" onchange="'.$p_action.'" value="'.$coupon['value'].'">';
							}
						}
					}
					$return .= '<div style="text-align:center;padding-left:'.$padding.'">';
					if(isset($atts['price'])) $return .='<div class="showPrice" style="margin-bottom:5px;margin-top:5px">'.__( 'Price' , 'easyReservations' ).': <span id="showPrice" style="font-weight:bold;"><b>'.easyreservations_format_money(0,1).'</b></span></div>';
					if($resPast == 0){
						$return .= '<input type="submit" class="easy-button" value="'.__( 'Submit' , 'easyReservations' ).'">';
						if(isset($edit_options['cancel']) && $edit_options['cancel'] > 0 && $res->status != 'no') $return .= '<br><a href="'.wp_nonce_url($the_link.'?edit&cancel='.$theID,'easy-user-cancel').'" style="display:block;margin-top:5px;font-style:italic;font-size:12px">'.__( 'Cancel reservation' , 'easyReservations' ).'</a>';
					}
					$return .= '</div>';
					$return .= '</div></form><style>label.easy-show-error { min-width:0px !important; }</style>';
				}

				return $return;
			} catch(Exception $e) {
				session_destroy();
				unset($_SESSION['easy-user-edit-id'] );
				unset($_SESSION['easy-user-edit-email'] );
				return '<div style="text-align:center;">'.$e->getMessage().' - '.__(  'Wrong ID or email' , 'easyReservations' ).' - <a href="'.$the_link.'?logout">'.__( 'back' , 'easyReservations' ).'</a></div>';
			}
		} else {
			include_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
			$captcha_instance = new easy_ReallySimpleCaptcha();
			$word = $captcha_instance->generate_random_word();
			$prefix = mt_rand();
			$url = $captcha_instance->generate_image($prefix, $word);
			$return = '';
			if(!isset($atts['id_label'])) $atts['id_label'] = __( 'ID' , 'easyReservations' );
			if(!isset($atts['id_sublabel'])) $atts['id_sublabel'] = __( 'ID of your reservation' , 'easyReservations' );
			if(!isset($atts['email_label'])) $atts['email_label'] = __( 'Email' , 'easyReservations' );
			if(!isset($atts['email_sublabel'])) $atts['email_sublabel'] = __( 'Your email' , 'easyReservations' );
			if(!isset($atts['captcha_label'])) $atts['captcha_label'] = __( 'Captcha' , 'easyReservations' );
			if(!isset($atts['captcha_sublabel'])) $atts['captcha_sublabel'] = __( 'Type in code' , 'easyReservations' );
			if(!isset($atts['submit_label'])) $atts['submit_label'] = __( 'Submit' , 'easyReservations' );

			if(!empty($edit_options['login_text'])) $return .= '<div style="text-align:center;" class="easy-edit-login-text">'.$edit_options['login_text'].'</div>';
			$return .= '<form id="easyFrontendFormular" method="post" style="padding-left:106px;margin-top:5px" name="easyFrontendFormular">';
				$return .= '<input type="hidden" value="'.$prefix.'" name="captcha_prefix">';
				$return .= '<input name="easy-user-edit-login" type="hidden" value="'.wp_create_nonce('easy-user-edit-login').'">';
				$return .= '<label>'.$atts['id_label'].'<span class="small">'.$atts['id_sublabel'].'</span></label><input name="editID" type="text"><br>';
				$return .= '<label>'.$atts['email_label'].'<span class="small">'.$atts['email_sublabel'].'</span></label><input name="email" type="text"><br>';
				$return .= '<label>'.$atts['captcha_label'].'<span class="small">'.$atts['captcha_sublabel'].'</span></label><input type="text" name="captcha_value" style="width:40px;"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'lib/captcha/tmp/'.$url.'">';
				$return .= '<input type="submit" class="easy-button" value="'.$atts['submit_label'].'">';
			$return .= '</form>';
			return $return;
		}
	}
	function easyreservations_make_datepicker_edit(){
		easyreservations_build_datepicker(0, array('easy-form-from', 'easy-form-to'));
	}
	add_shortcode('easy_edit', 'easyreservations_edit_shortcode');

	function easyreservations_useredit_add_tinymce(){?>
		else if(x == "edit"){
			var FieldAdd = '<tr>';
				FieldAdd += '<td colspan="2" nowrap="nowrap" valign="top"><label for="easyreservation_edit_daysback"><?php echo addslashes(__("Reservations are editable till", "easyReservations")); ?> ';
				FieldAdd += '<select id="easyreservation_edit_daysback" name="easyreservation_edit_daysback" style="width: 55px"><?php echo easyreservations_num_options(-100,100,1); ?></select> <?php echo addslashes(__("days before their arrival", "easyReservations")); ?></label></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_edit_table"><?php echo addslashes(__("Table", "easyReservations")); ?></label></td>';
				FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_edit_table" name="easyreservation_edit_table" checked></label> <?php echo addslashes(__("Show table with other reservations by the same email", "easyReservations")); ?></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_status"><?php echo addslashes(__("Status", "easyReservations")); ?></label></td>';
				FieldAdd += '<td><label><input type="checkbox" id="easyreservation_show_status" name="easyreservation_show_status" checked></label> <?php echo addslashes(__("Show status", "easyReservations")); ?></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php addslashes(__("Price", "easyReservations")); ?></label></td>';
				FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_show_price" name="easyreservation_show_price" checked></label> <?php echo addslashes(__("Show price", "easyReservations")); ?></td>';
				FieldAdd += '</tr>';
			<?php if(function_exists('easyreservations_generate_invoice')){ ?>
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_invoice"><?php echo addslashes(__("Invoice", "easyReservations")); ?></label></td>';
				FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_invoice" name="easyreservation_invoice" checked></label> <?php echo addslashes(__("Let your guests view and download their invoices by invoice guest template", "easyReservations")); ?></td>';
				FieldAdd += '</tr>';
			<?php } ?>
				FieldAdd += '<tr>';
				FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_edit_roomname"><?php echo addslashes(__("Name for resource", "easyReservations")); ?></label></td>';
				FieldAdd += '<td><label><input type="text"  id="easyreservation_edit_roomname" name="easyreservation_edit_roomname" value="Room"></label> <?php echo addslashes(__("e.g. Apartment", "easyReservations")); ?></td>';
				FieldAdd += '</tr>';
				FieldAdd += '<tr><td colspan="2"><?php echo addslashes(__("This shortcode adds the function for your guests to edit their reservations afterwards", "easyReservations")); ?>. <?php echo addslashes(__("You have to copy the URL of this site to the easyReservations general settings", "easyReservations")); ?>.<br><b><?php echo addslashes(__("Only add the edit-form on one page or post", "easyReservations")); ?>.</b></td></tr>';

			document.getElementById("tiny_Field").innerHTML = FieldAdd;
		}<?php
	}

	add_action('easy-tinymce-add', 'easyreservations_useredit_add_tinymce');

	function easyreservations_useredit_save_tinymce(){?>
		else if(y == "edit"){
			classAttribs += ' daysbefore="' + document.getElementById('easyreservation_edit_daysback').value + '"';
			if(document.getElementById('easyreservation_show_status').checked == true) classAttribs += ' status="1"';
			if(document.getElementById('easyreservation_show_price').checked == true) classAttribs += ' price="1"';
			if(document.getElementById('easyreservation_edit_table').checked == true) classAttribs += ' table="1"';
			if(document.getElementById('easyreservation_invoice') && document.getElementById('easyreservation_invoice').checked == true) classAttribs += ' invoice="1"';
			classAttribs += ' roomname="' + document.getElementById('easyreservation_edit_roomname').value + '"';
		}<?php
	}

	add_action('easy-tinymce-save', 'easyreservations_useredit_save_tinymce');

	function easyreservations_useredit_add_tinymce_option(){
		echo '<option value="edit">'.addslashes(__("User Control Panel", "easyReservations")).'</option>';
	}

	add_action('easy-tinymce-add-name', 'easyreservations_useredit_add_tinymce_option');
}
?>