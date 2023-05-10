<?php

function reservation_main_page(){
	wp_enqueue_style( 'datestyle');
	$main_options = get_option("reservations_main_options");
	$custom_fields = get_option('reservations_custom_fields');
	$show = $main_options['show'];
	$overview_options = $main_options['overview'];
	easyreservations_load_resources(true);
	global $wpdb, $easy_errors, $the_rooms_intervals_array, $the_rooms_array;
	if(!isset($easy_errors)) $easy_errors = array();

	if(isset($_GET['more'])) $moreget=$_GET['more'];
	else $moreget = 0;
	if(isset($_GET['perpage'])) update_option("reservations_on_page",$_GET['perpage']);
	if(isset($_GET['sendmail'])) $sendmail=$_GET['sendmail'];
	if(isset($_GET['approve'])) $approve=$_GET['approve'];
	if(isset($_GET['view']))  $view=$_GET['view'];
	if(isset($_GET['delete'])) $delete=$_GET['delete'];
	if(isset($_GET['edit'])) $edit=$_GET['edit'];
	if(isset($_GET['add'])) $add=$_GET['add'];
	if(isset($_POST['room-saver-from'])) $moreget+=round(($_POST['room-saver-from']-strtotime(date("d.m.Y", time())))/86400);
	if(!isset($edit) && !isset($view) && !isset($add) && !isset($approve) && !isset($sendmail)  && !isset($delete)) $nonepage = 0;

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + BULK ACTIONS (trash,delete,undo trash) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
	do_action('easy_dashboard_header_start');
	if(isset($_GET['bulk']) && check_admin_referer( 'easy-main-bulk', 'easy-main-bulk' )){ // GET Bulk Actions
		if(isset($_GET['bulkArr'])) {
			$to=0;
			$listes = $_GET['bulkArr'];
			if($_GET['bulk']==1){ //  If Move to Trash
				foreach($_GET['bulkArr'] as $id){
					$to++;
					$theres = new Reservation($id, array('status' => 'del', 'resource' => false));
					$theres->editReservation(array('status'), false);
					$theres->destroy();
				}
				if($to!==1) $linkundo=implode("&bulkArr[]=", $listes); else $linkundo=$listes;
				if($to==1)  $anzahl=__('Reservation', 'easyReservations'); else $anzahl=$to.' '.__('Reservations', 'easyReservations');
				$easy_errors[] = array( 'updated', $anzahl.' '.__( 'moved to trash' , 'easyReservations' ).'. <a href="'.wp_nonce_url('admin.php?page=reservations&bulkArr[]='.$linkundo.'&bulk=2', 'easy-main-bulk').'">'.__( 'Undo' , 'easyReservations' ).'</a>');
			} elseif($_GET['bulk']=="2"){ //  If Undo Trashing
				if(count($listes)  > "1" ) { 
					foreach($listes as $id){
						$to++;
						$theres = new Reservation($id, array('status' => ''));
						$theres->editReservation(array('status'), false);
						$theres->destroy();
					}
				} else {
					$to++;
					$theres = new Reservation($listes[0], array('status' => '', 'resource' => false));
					$theres->editReservation(array('status'), false);
					$theres->destroy();
				}
				if($to==1) $anzahl=__('Reservation', 'easyReservations'); else $anzahl=$to.' '.__('Reservations', 'easyReservations');
				$easy_errors[] = array( 'updated', $anzahl.' '.__( 'restored from trash' , 'easyReservations' ));
			} elseif($_GET['bulk']=="3"){ //  If Delete Permanently
				if(count($listes)  > "1" ) { 
					foreach($listes as $id){
						$to++;
						$theres = new Reservation($id);
						$theres->deleteReservation();
						$theres->destroy();
					}
				} else {
					$to++;
					$theres = new Reservation($listes[0]);
					$theres->deleteReservation();
					$theres->destroy();
				}
			if($to==1) $anzahl=__('Reservation', 'easyReservations'); else $anzahl=$to.' '.__('Reservations', 'easyReservations');
			$easy_errors[] = array( 'updated', $anzahl.' '.__('deleted permanently', 'easyReservations'));
			}
		}
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE CUSTOM FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_GET['deletecustomfield']) && isset($edit)){
		$res = new Reservation($edit);
		try {
			$res->Customs(array(), false, $_GET['deletecustomfield'], false);
			$return = $res->editReservation(array('custom'), false);
			if(!$return) $easy_errors[] = array( 'updated', __( 'Custom information deleted' , 'easyReservations' ));
			else $easy_errors[] = array( 'error', __( 'Custom information isn\'t existing' , 'easyReservations' ));
		} catch(easyException $e){
			$easy_errors[] = array( 'error' , $e->getMessage());
		}
	}


/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE PRICE FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
	if(isset($_GET['deletepricefield']) && isset($edit)){
		$res = new Reservation($edit);
		try {
			$res->Customs(array(), false, $_GET['deletepricefield'], true);
			$return = $res->editReservation(array('prices'), false);
			if(!$return) $easy_errors[] = array( 'updated', __( 'Custom price deleted' , 'easyReservations' ));
			else $easy_errors[] = array( 'error', __( 'Custom price isn\'t existing' , 'easyReservations' ));
		} catch(easyException $e){
			$easy_errors[] = array( 'error' , $e->getMessage());
		}
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_POST['editthereservation']) && check_admin_referer( 'easy-main-edit', 'easy-main-edit' )){
		if(isset($_POST['from-time-hour'])) $from_hour = ((int) $_POST['from-time-hour']*60)+(int) $_POST['from-time-min']; else $from_hour = 12*60;
		if(isset($_POST['to-time-hour']))  $to_hour = ((int) $_POST['to-time-hour']*60)+(int) $_POST['to-time-min'] ;else $to_hour = 12*60;

		foreach($_POST as $key => $value){
			if(strpos($key, 'customvalue') !== false && $key !== 'customvalue' ){
				$temp_id = str_replace('customvalue', '', $key);
				if(isset($_POST["customvalue".$temp_id]) && isset($_POST["customtitle".$temp_id])){
					$customfields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$temp_id], 'title' => stripslashes($_POST["customtitle".$temp_id]), 'value' => stripslashes($_POST["customvalue".$temp_id]));
				}
			} elseif(strpos($key, 'customPvalue') !== false && $key !== 'customPvalue'){
				$temp_id = str_replace('customPvalue', '', $key);
				if(isset($_POST["customPvalue".$temp_id]) && isset($_POST["customPtitle".$temp_id])){
					if(easyreservations_check_price($_POST["custom_price".$temp_id]) == 'error') $easy_errors[] = array( 'error' , __( 'Wrong money format in custom price' , 'easyReservations' ));
					$custompfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$temp_id], 'title' => $_POST["customPtitle".$temp_id], 'value' => stripslashes($_POST["customPvalue".$temp_id]), 'amount' => easyreservations_check_price($_POST["custom_price".$temp_id]) );
				}
			} elseif(strpos($key, 'easy-new-custom') !== false ){
				$temp_id = str_replace('easy-new-custom-', '', $key);
				if(isset($custom_fields['fields'][$temp_id])){
					$array = array( 'type' => 'cstm', 'mode' => 'edit', 'id' => $temp_id, 'value' => $value);
					if(isset($custom_fields['fields'][$temp_id]['price'])) $custompfields[] = $array;
					else $customfields[] = $array;
				}
			}
		}

		$res = new Reservation($edit, false, true);
		try {
			$res->save = (array) $res;
			$res->name = stripslashes($_POST["name"]);
			$res->email = $_POST["email"];
			$res->resource = (int) $_POST["room"];
			if(isset($_POST["persons"])) $res->adults = (int) $_POST["persons"];
			else $res->adults = 1;
			if(isset($_POST["childs"])) $res->childs = (int) $_POST["childs"];
			else $res->childs = 1;
			$res->country = $_POST["country"];
			$res->status = $_POST["reservation_status"];
			$res->user = (int) $_POST["edit_user"];
			if(isset($_POST["roomexactly"])) $res->resourcenumber = $_POST["roomexactly"];
			else $res->resourcenumber = 0;
			$reservation_date = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["reservation_date"].' 00:00:00');
			$arrival = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["date"].' 00:00:00');
			$departure = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["dateend"].' 00:00:00');
			if($arrival instanceof DateTime && $departure instanceof DateTime && $reservation_date instanceof DateTime){
				$res->reservated = $reservation_date->getTimestamp();
				$res->arrival = $arrival->getTimestamp()+($from_hour*60);
				$res->departure = $departure->getTimestamp()+($to_hour*60);
				if(!empty($customfields))	$res->Customs($customfields, true, false, false, 'cstm');
				if(!empty($custompfields)) $res->Customs($custompfields, true, false, true, 'cstm');
	      $res->getTimes();
				$res = apply_filters('easy-edit-prices', $res);
	      $set_price = '';
	      if(isset($_POST["priceset"]) && (!empty($_POST["priceset"]) || is_numeric($_POST["priceset"]))) $set_price = easyreservations_check_price($_POST["priceset"]);
	      if(!isset($_POST["EDITwaspaid"]) || empty($_POST["EDITwaspaid"]) || $_POST["EDITwaspaid"] < 0 || $_POST["EDITwaspaid"] === null ) $paid = 0;
	      else $paid = easyreservations_check_price($_POST["EDITwaspaid"]);
	      if($set_price !== false && $paid !== false){
	        $res->pricepaid = $set_price.';'.$paid;
	      } else {
	        $easy_errors[] = array( 'error' , __( 'Price couldn\'t be fixed, input isn\'t valid money format' , 'easyReservations' ));
	      }
	      if(isset($_POST["sendthemail"])) $mail = 'reservations_email_to_user_admin_edited'; else $mail = '';
				global $easy_errors;
				if($_POST['copy'] == 'no'){
					$return = $res->editReservation(array('all'), true, $mail, $res->email);
					if(!$return) $easy_errors[] = array( 'updated' , __( 'Reservation edited.' , 'easyReservations' ).'</p><p><a href="admin.php?page=reservations">&#8592; Back to Dashboard</a>');
					else {
						$easy_errors = array_merge_recursive((array) $easy_errors, (array) $return);
						$res->destroy();
					}
				} else {
					$preid = $res->id;
					$res->id = 0;
					$return = $res->addReservation();
					if(!$return){
						$easy_errors[] = array( 'updated' , sprintf(__( 'Reservation #%1$d copied as #%2$d' , 'easyReservations'), $preid, $res->id ));
						?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations&edit=<?php echo $res->id; ?>"><?php
					} else $res->destroy();
				}
			}
		} catch(Exception $e){
			$easy_errors[] = array( 'error' , $e->getMessage());
		}
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_POST['addreservation']) && check_admin_referer( 'easy-main-add', 'easy-main-add' )){
		if(isset($_POST['from-time-hour'])) $from_hour = ((int) $_POST['from-time-hour']*60)+(int) $_POST['from-time-min']; else $from_hour = 12*60;
		if(isset($_POST['to-time-hour']))  $to_hour = ((int) $_POST['to-time-hour']*60)+(int)$_POST['to-time-min'];else $to_hour = 12*60;

		$ADDcustomFields = array();
		$ADDcustomPfields = array();
		foreach($_POST as $key => $value){
			if(strpos($key, 'customvalue') !== false ){
				$temp_id = str_replace('customvalue', '', $key);
				if(!empty($temp_id) && isset($_POST["customvalue".$temp_id]) && isset($_POST["customtitle".$temp_id])){
					$ADDcustomFields[] = array( 'type' => 'cstm', 'mode' => $_POST["custommodus".$temp_id], 'title' => $_POST["customtitle".$temp_id], 'value' => $_POST["customvalue".$temp_id]);
				}
			} elseif(strpos($key, 'customPvalue') !== false ){
				$temp_id = str_replace('customPvalue', '', $key);
				if(!empty($temp_id) && isset($_POST["customPvalue".$temp_id]) && isset($_POST["customPtitle".$temp_id])){
					if(easyreservations_check_price($_POST["custom_price".$temp_id]) == 'error') $easy_errors[] = array( 'error' , __( 'Wrong money format in custom price' , 'easyReservations' ));
					$ADDcustomPfields[] = array( 'type' => 'cstm', 'mode' => $_POST["customPmodus".$temp_id], 'title' => $_POST["customPtitle".$temp_id], 'value' => $_POST["customPvalue".$temp_id], 'amount' => easyreservations_check_price($_POST["custom_price".$temp_id]) );
				}
			} elseif(strpos($key, 'easy-new-custom') !== false ){
				$temp_id = str_replace('easy-new-custom-', '', $key);
				if(isset($custom_fields['fields'][$temp_id])){
					$array = array( 'type' => 'cstm', 'mode' => 'edit', 'id' => $temp_id, 'value' => $value);
					if(isset($custom_fields['fields'][$temp_id]['price'])) $ADDcustomPfields[] = $array;
					else $ADDcustomFields[] = $array;

				}
			}
		}

		if(isset($_POST["roomexactly"])) $resresourcenumber = (int) $_POST["roomexactly"];
		else $resresourcenumber = 0;
		if(isset($_POST["persons"])) $resadults = (int) $_POST["persons"];
		else $resadults = 1;
		if(isset($_POST["childs"])) $reschilds = (int) $_POST["childs"];
		else $reschilds = 1;

		$arrival = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["date"].' 00:00:00');
		$departure = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["dateend"].' 00:00:00');
		$reservation_date = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["reservation_date"].' 00:00:00');
		if(!$reservation_date instanceof DateTime) $reservation_date = time();
		if($arrival instanceof DateTime && $departure instanceof DateTime){
			$res = new Reservation(false, array('name' => $_POST["name"], 'email' => $_POST["email"], 'arrival' => $arrival->getTimestamp()+($from_hour*60),'departure' => $departure->getTimestamp()+($to_hour*60),'resource' => (int) $_POST["room"],'resourcenumber' => $resresourcenumber,'country' => $_POST["country"], 'adults' => $resadults,	'custom' => maybe_unserialize($ADDcustomFields),'prices' => maybe_unserialize($ADDcustomPfields),'childs' => $reschilds,'reservated' => $reservation_date->getTimestamp(),'status' => $_POST["reservationStatus"],'user' => $_POST["edit_user"]), true);
			try {
				$thePriceAdd = '';
				if(isset($_POST["fixReservation"]) && $_POST["fixReservation"] == "on"){
					if($_POST["setChoose"] == "custm")$thePriceAdd = easyreservations_check_price($_POST["priceAmount"]);
					else {
						$res->Calculate();
						$thePriceAdd = easyreservations_check_price($res->price);
					}
				}

				if(!isset($_POST["paidAmount"]) || empty($_POST["paidAmount"]) || $_POST["paidAmount"] < 0 || $_POST["paidAmount"] === null ) $thePricePaid = 0;
				else $thePricePaid = easyreservations_check_price($_POST["paidAmount"]);

				if($thePriceAdd !== false && $thePricePaid !== false) $res->pricepaid = $thePriceAdd.';'.$thePricePaid;
				else $easy_errors[] = array( 'error' , __( 'Price couldn\'t be fixed, input isn\'t valid money format' , 'easyReservations' ));

				$return = $res->addReservation();
				if(!$return){
					$easy_errors[] = array( 'updated' , sprintf(__( 'Reservation #%d added' , 'easyReservations' ), $res->id));
					do_action('easy-add-stream', 'reservation', 'add', '', $res->id);
					?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations&edit=<?php echo $res->id; ?>"><?php
				} else{
					global $easy_errors;
					$easy_errors = array_merge_recursive((array) $easy_errors, (array) $return);
					$res->destroy();
				}
			} catch(Exception $e){
				$easy_errors[] = array( 'error' , $e->getMessage());
			}
		} else $easy_errors[] = array( 'error' , __( 'Wrong date format' , 'easyReservations' ));
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GET INFORMATIONS IF A RESERVATION IS CALLED DIRECTLY (view,edit,approve,reject,sendmail) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($approve)  || isset($delete) || isset($view) || isset($edit) || isset($sendmail)) { //Query of View Reject Edit Sendmail and Approve
		if(isset($edit)) $theid = $edit;
		elseif(isset($approve)) $theid = $approve;
		elseif(isset($view)) $theid = $view;
		elseif(isset($sendmail)) $theid = $sendmail;
		elseif(isset($delete)) $theid = $delete;
		
		if(!isset($res)) $res = new Reservation($theid, false, true);
		try {
			$res->resourcenumbername = easyreservations_get_roomname($res->resourcenumber, $res->resource);
			if(empty($res->custom)) $customs = array(); else $customs=$res->getCustoms($res->custom, 'cstm');
			if(empty($res->prices)) $custom_prices = array(); else $custom_prices=$res->getCustoms($res->prices, 'cstm');
			if(isset($approve)  || isset($delete) || isset($view)) $roomwhere= $res->resource; // For Overview only show date on view
			$resource_name=$the_rooms_array[$res->resource]->post_title;

			$get_role = get_post_meta($res->resource, 'easy-resource-permission', true);
			if(!empty($get_role) && !current_user_can($get_role)) die('You havn\'t the rights to access this reservation');
			$information = '<small>'.__( 'This is how the price would get calculated now. After changing filters, base price or settings or the reservations price it wont match the fixed price anymore.' , 'easyReservations' ).'</small>';
			$pricepaid_explode = explode(";", $res->pricepaid);
		} catch(easyException $e){
			$easy_errors[] = array( 'error' , $e->getMessage());
		}
		
		$global_roomcount = get_post_meta($res->resource, 'roomcount', true);
		if(is_array($global_roomcount)){
			$global_roomcount = $global_roomcount[0];
			$bypers = true;
		} else $bypers = false;

		$moreget+=ceil(($res->arrival-strtotime(date("d.m.Y", time()))-259200)/86400);
	}

	if(isset($res)){
    $res->getTimes();
    $res->Calculate(true);
  }

	if(isset($sendmail) && isset($_POST['thesendmail'])){
		$theres = new Reservation((int) $sendmail, false, true);
		try {
			$test = $theres->sendMail('reservations_email_sendmail', $theres->email);
			$easy_errors[] = array( 'updated' , __( 'Email sent successfully', 'easyReservations' ));
		} catch(Exception $e){
			$easy_errors[] = array( 'error' , $e->getMessage());
		}
	} 
	
	if(isset($_POST['approve']) || isset($_POST['delete'])){
		if(isset($_POST['approve'])){
			$emailformation = 'reservations_email_to_userapp';
			$status = 'yes';
		} else {
			$emailformation = 'reservations_email_to_userdel';
			$status = 'no';
		}

		if(!isset($_POST['sendthemail'])) $emailformation = false;

		$theres = new Reservation($theid, false, true);
		try {
			$theres->Calculate();
			if(isset($_POST['roomexactly'])) $theres->resourcenumber = $_POST['roomexactly'];
			$theres->status = $status;

			if(isset($_POST['hasbeenpayed'])) $theres->pricepaid=$theres->price.';'.$theres->price;
			$return = $theres->editReservation(array('status', 'pricepaid', 'resourcenumber'), true, $emailformation, $theres->email);
			$theres->destroy();

			if(!$return){
				if(isset($_GET['approve'])) $easy_errors[] = array( 'updated' , __( 'Reservation approved', 'easyReservations' ));
				else $easy_errors[] = array( 'updated' , __( 'Reservation rejected', 'easyReservations' ));
				?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
			} else {
				if(is_array($return)) $easy_errors[] = array( 'error' , $return[0]);
				else $easy_errors[] = array( 'error' , $return);
			}
		} catch(Exception $e){
			$easy_errors[] = array( 'error' , $e->getMessage());
		}
	} elseif(isset($_POST['approvelink'])){
		do_action('easy-approvelink');
	}

	do_action('easy_dashboard_header_end'); ?>
<h2>
	<?php echo __( 'Reservations Dashboard' , 'easyReservations' );?>
	<a class="add-new-h2" href="admin.php?page=reservations&add"><?php echo __( 'Add New' , 'easyReservations' );?></a>
</h2>
<script>
	function get_the_select(selected, resourceId){
		var selects = new Array(); <?php
		foreach($the_rooms_array as $roome){
			$roomcount2 = get_post_meta($roome->ID, 'roomcount', true);
			if(!is_array($roomcount2)){
				$select = '<select name="roomexactly" id="roomexactly" onchange="changer();';
				if($overview_options['overview_autoselect'] == 1){ $select .= 'dofakeClick(2);';  }
				$select .= '">';
					$select.= easyreservations_get_roomname_options(1, $roomcount2, $roome->ID);
				$select.= '<option value="0">'.addslashes(__('None', 'easyReservations')).'</option>';
				$select.= '</select>'; ?>
				selects[<?php echo $roome->ID; ?>] = new Array('<?php echo $select; ?>');<?php
			}
		} ?>

		if(selects[resourceId]){
			document.getElementById('the_room_exactly').innerHTML = selects[resourceId];
			if(selected != 0) document.getElementById('roomexactly').selectedIndex = selected-1;
			else document.getElementById('roomexactly').selectedIndex = 0;
		} else document.getElementById('the_room_exactly').innerHTML = '';
	}
</script>
<?php
easyreservations_dashboard_message();
if($show['show_overview']==1){ //Hide Overview completly
	if(RESERVATIONS_STYLE == 'widefat'){
		$ovBorderColor='#9E9E9E';
		$ovBorderStatus='dotted';
	} elseif(RESERVATIONS_STYLE == 'greyfat'){
		$ovBorderColor='#777777';
		$ovBorderStatus='dashed';
	}
?>
<script>
	function generateXMLHttpReqObjThree(){
		var resObjektTwo = null;
		try {
			resObjektThree = new ActiveXObject("Microsoft.XMLHTTP");
		} catch(Error){
			try {
				resObjektThree = new ActiveXObject("MSXML2.XMLHTTP");
			} catch(Error){
				try {
					resObjektThree = new XMLHttpRequest();
				} catch(Error){
					alert("AJAX error");
				}
			}
		}
		return resObjektThree;
	}

	function generateAJAXObjektThree(){
		this.generateXMLHttpReqObjThree = generateXMLHttpReqObjThree;
	}

	var xxy = new generateAJAXObjektThree();
	var resObjektThree = xxy.generateXMLHttpReqObjThree();
	var save = 0;
	var countov = 0;
	var the_ov_interval = 86400;

	function easyRes_sendReq_Overview(x,y,daystoshow, interval){
		jQuery('#jqueryTooltip').remove();
		the_ov_interval = interval;
		var string = '';
		if(x && x != 'no') string += 'more=' + x;
		if(y && y != 'no') string +=  '&dayPicker=' + y;
		var reservationNights = '<?php if(isset($res->times)) echo $res->times; ?>';
		if(reservationNights != '') string += '&reservationNights=' + reservationNights;
		var roomwhere = '<?php if(isset($roomwhere)) echo $roomwhere; ?>';
		if(roomwhere != '') string += '&roomwhere=' + roomwhere;
		var add = '<?php if(isset($add)) echo '1'; ?>';
		if(add != '') string += '&add=' + add;
		var edit = '<?php if(isset($edit)) echo $edit; ?>';
		if(edit != '') string += '&edit=' + edit;
		var app = '<?php if(isset($approve)) echo $approve; ?>';
		if(app != '') string += '&approve=' + app;
		var id = '<?php if(isset($res->id) && $res->id) echo $res->id; ?>';
		if(id != '') string += '&id=' + id;
		var res_date_from_stamp = '<?php if(isset($res->arrival)) echo $res->arrival.'-'.$res->departure; ?>';
		if(res_date_from_stamp != '') string += '&res_date_from_stamp=' + res_date_from_stamp;
		var nonepage = '<?php if(isset($nonepage)) echo $nonepage; ?>';
		if(nonepage != '') string += '&nonepage=' + nonepage;
		if(daystoshow) string += '&daysshow=' + daystoshow;
		else string += '&daysshow=' + <?php if(isset($overview_options['overview_show_days']) && !empty($overview_options['overview_show_days'])) echo $overview_options['overview_show_days']; else echo 30; ?>;

		if((y != "" || x != "") && save == 0){
			save = 1;
			resObjektThree.open('post', '<?php echo WP_PLUGIN_URL; ?>/easyreservations/overview.php?rand=<?php echo rand(1,999); ?>', true);
			resObjektThree.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			resObjektThree.onreadystatechange = handleResponseValidate;
			resObjektThree.send(string + '&interval=' + interval);
			if(document.getElementById('pickForm')) document.getElementById('pickForm').innerHTML = '<img style="vertical-align:text-bottom;margin-left:12px" src="<?php echo RESERVATIONS_URL; ?>images/loading1.gif">';
		}
	}

	function handleResponseValidate(){
		var text="";
		if(resObjektThree.readyState == 4){
			document.getElementById("theOverviewDiv").innerHTML = resObjektThree.responseText;
			jQuery(document).ready(function(){
				createPickers();
			});
			Click = 0;
			var from = document.getElementById('datepicker');
			var to = document.getElementById('datepicker2');
			if(countov != 0 && window.dofakeClick && from && from.value != '<?php if(isset($res->arrival)) echo date(RESERVATIONS_DATE_FORMAT, $res->arrival); ?>' && to && to != '<?php if(isset($res->departure)) echo date(RESERVATIONS_DATE_FORMAT, $res->departure); ?>'){
				dofakeClick(2);
			}
			countov++;
			save = 0;
			jQuery.holdReady(false);
		}
	}

	function createPickers(){
		jQuery("#dayPicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'dd.mm.yy',
			firstDay: 1,
			buttonText: '<?php echo __( 'choose date' , 'easyReservations' ); ?>',
			showOn: 'button',
			buttonImage: '<?php echo RESERVATIONS_URL; ?>images/day.png',
			buttonImageOnly: true,
			defaultDate: +10,
			onSelect: function(){
				easyRes_sendReq_Overview('no', document.getElementById("dayPicker").value, '',<?php echo 86400; ?>);
			}
		});

		jQuery.fn.column = function(i) {
			if(i) return jQuery('tr td:not(.ov-days-hours):nth-child('+(i)+'), tr td.ov-days-hours:nth-child('+(i-1)+')', this);
		}

		jQuery(function() {
			jQuery("#overview td").hover(function() {
				var curCol = jQuery(this).attr("axis") ;
				if(curCol){
					jQuery('#overview').column(curCol).addClass("hover");
					jQuery('#overview').addClass("hover");
				}
			}, function() {
				var curCol = jQuery(this).attr("axis") ;
				if(curCol) jQuery('#overview').column(curCol).removeClass("hover"); 
			});
		});
		jQueryTooltip();
	}

	function formDate(str){
		if(str < 2082585600) str = parseFloat(str) * 1000;
		var date = new Date(str);
		var retjurn = (( date.getDate() < 10) ? '0'+ date.getDate() : date.getDate()) + '.' +(( parseFloat(date.getMonth()+1) < 10) ? '0'+ parseFloat(date.getMonth()+1) : parseFloat(date.getMonth()+1)) + '.' + (( date.getYear() < 999) ? date.getYear() + 1900 : date.getYear());
		return retjurn;
	}

	var Click = 0;
	function clickOne(t,d,color,mode){
		deletecActiveRes();
		if(Click == 0 && t){
			if(color) var color = color; else var color = "black";
			document.getElementById("hiddenfieldclick").value=t.id;
      if(!d) d = jQuery(t).attr('date');
      if(mode == 1) t.style.background='url("<?php echo RESERVATIONS_URL; ?>images/'+ color +'_middle.png") repeat-x';
			else t.style.background='url("<?php echo RESERVATIONS_URL; ?>images/'+ color +'_start.png") right top no-repeat, '+t.abbr;
			<?php if(isset($edit) || isset($add)){ ?>document.getElementById('datepicker').value=easyFormatDate(d);<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-from').value=d;<?php } ?>
			if(document.getElementById('from-time-hour')){
				var theDate = easyTimestampToDate(d*1000);
				document.getElementById('from-time-hour').selectedIndex = theDate.getHours();
			}
			if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML='<img src="<?php echo RESERVATIONS_URL; ?>images/refreshBlack.png" style="vertical-align:bottom;cursor:pointer;" onclick="resetSet()">';
			Click = 1;

		}
	}

	function clickTwo(t,d,color,todo){
		if( Click == 1){
			var Last = document.getElementById("hiddenfieldclick").value;
			if(t) var way = 0;
			else {
				var way = 1;
				var last_div = document.getElementById(Last);
				if(last_div) t = last_div.parentNode.lastChild;
				else {
					resetSet();
					return;
				}
			}
			var Celle = t.id;
			if(color) color = color; else var color = "black";
			var lastDiv = document.getElementById(Last);

			if(lastDiv && Last <= Celle && t.parentNode.id==lastDiv.parentNode.id){
				document.getElementById("hiddenfieldclick2").value=Celle;
				if(way == 0) t.style.background='url("<?php echo RESERVATIONS_URL; ?>images/'+ color +'_end.png") left top no-repeat, '+t.abbr;
				else t.style.background='url("<?php echo RESERVATIONS_URL; ?>images/'+ color +'_middle.png") repeat-x';
				jQuery(t).addClass('ov-no-border');
        if(!d) d = jQuery(t).attr('date');
				<?php if(isset($edit) || isset($add)){ ?>document.getElementById('datepicker2').value=easyFormatDate(d);<?php } elseif(isset($nonepage)){ ?>document.getElementById('room-saver-to').value=d;<?php } ?>
				if(document.getElementById('to-time-hour')){
          var theDate = easyTimestampToDate(d*1000);
					document.getElementById('to-time-hour').selectedIndex = theDate.getHours();
				}
				var theid= '';
				var work = 1;
				if( Last == Celle ) t.style.background = '#000';
				else {
					while(theid != Last){
						if(jQuery(t).is('.er_overview_cell') && jQuery(t).is('td[name="activeres"]') === false && color == "black"){
							resetSet();
							if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML += "<?php echo addslashes(__( 'full' , 'easyReservations' )); ?>!";
							var field = document.getElementById('datepicker2');
							if(field && field.type == "text" ){
								jQuery('input[name="date"],input[name="dateend"],#room,#from-time-hour,#to-time-hour,select[name="from-time-min"],select[name="to-time-min"]').css("border-color", "#F20909");
							}
							work = 0;
							break; 
						}
						t=t.previousSibling;
						theid=t.id;
						if(theid && theid != Last){
							jQuery(t).addClass('ov-no-border');
							t.style.background='url("<?php echo RESERVATIONS_URL; ?>images/'+ color +'_middle.png") repeat-x';
						}
					}
				}
				Click = 2;
				if(work == 1){
					<?php if(isset($add) || isset($edit)) echo "easyreservations_send_price_admin();"; ?>
					if(color == "black" && !todo){ <?php if(isset($nonepage)){ ?>document.roomsaver.submit();<?php } ?>}
				}
			}
		}
	}

	function changer(){
		var field = document.getElementById('datepicker2');
		if(field && field.type == "text" ){
			jQuery('input[name="date"],input[name="dateend"],#room,#from-time-hour,#to-time-hour,select[name="from-time-min"],select[name="to-time-min"],#roomexactly:first').css("border-color", "");
		}
		if( Click == 2 ){
			resetSet();
		}
	}

	function fakeClick(from, to, room, exactly,color){
		var x = parseFloat(document.getElementById("timesx").value);
		var y = parseFloat(document.getElementById("timesy").value);
		var mode = 0;

		if(x && from < y && to > x){
			var daysbetween = Math.round((from - x) / the_ov_interval)+1;
			if(daysbetween < 10 && daysbetween >= 0) daysbetween = '0' + daysbetween;
			if(daysbetween <= 1){ daysbetween = '01'; var mode = 1; }

			var daysbetween2 = Math.round((to - x) / the_ov_interval) +1;
			if(daysbetween2 < 10) daysbetween2 = '0' + daysbetween2;

			var id = room + '-' + exactly + '-' + daysbetween;
			var id2 = room + '-' + exactly + '-' + daysbetween2;

			clickOne(document.getElementById(id),from,color, mode);
			clickTwo(document.getElementById(id2),to,color);
		}
	}

	function resetSet(){
		var First = document.getElementById("hiddenfieldclick").value;
		var Last = document.getElementById("hiddenfieldclick2").value;

		if(Click == 2 || Last != '' ){
			t=document.getElementById(Last);
			if(t){
				t.style.background=t.abbr;
				if(t.className != "er_overview_cell") jQuery(t).removeClass('ov-no-border');
				var theid= '';
				if(First != Last){
					while(theid != First){
						t=t.previousSibling;
						if(t && t.id){
							theid=t.id;
							if(t.className != "er_overview_cell") jQuery(t).removeClass('ov-no-border');
							t.style.background=t.abbr;
						}
					}
					var testa = document.getElementById(First);
					if(testa.className != "er_overview_cell") jQuery(testa).removeClass('ov-no-border');
					testa.style.background=t.abbr;

					Click = 0;
					if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML='';
					jQuery("#hiddenfieldclick2,#hiddenfieldclick").val('');
				} else Click = 0;
			} else Click = 0;
		} else if(Click == 1){
			var First = document.getElementById("hiddenfieldclick").value;
			var t = document.getElementById(First);
			if(t){
				if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML='';
				t.style.background=t.abbr;
			}
			Click = 0;
		}
	}

	function overviewSelectDate(date){
		var table_date_field = document.getElementById("easy-table-search-date");
		if(table_date_field){
			table_date_field.value = date;
			easyreservation_send_table('all', 1);
		}
	}

	function setVals2(roomid,roomex){
		<?php if(isset($edit) || isset($add)){ ?>
			var x = document.getElementById("room");
			var y = document.getElementById("roomexactly");
			get_the_select(roomex, roomid);
			jQuery('#room').val(roomid);
			jQuery('#roomexactly').val(roomex);
		<?php } elseif(isset($nonepage)){ ?>
			document.getElementById("room").value=roomid;
			document.getElementById("roomexactly").value=roomex;
		<?php } ?>
	}

	<?php if($overview_options['overview_onmouseover'] == 1){ ?>
	function hoverEffect(t,d){
    if(!d) d = easyFormatDate(jQuery(t).attr('date'));
    if(d == 0) document.getElementById("ov_datefield").innerHTML = ""; else document.getElementById("ov_datefield").innerHTML = ' (' + d + ')';
		if(Click == 1){
			var Last = document.getElementById("hiddenfieldclick").value;
			var Now = t.id;
			var Lastinfos = Last.split("-");
			var Nowinfos = Now.split("-");
			if(Nowinfos[2] >= Lastinfos[2]){
				var rightid = Lastinfos[0] + '-' + Lastinfos[1] + '-' + Nowinfos[2];
				var t = document.getElementById(rightid);
				if(t){
					document.getElementById("hiddenfieldclick2").value = rightid;
					var y=t;
					if(Nowinfos[2] != Lastinfos[2]){
						t.style.background='url("<?php echo RESERVATIONS_URL; ?>images/black_end.png") left top no-repeat, '+t.abbr;
						jQuery(t).addClass('ov-no-border');

						var x=t;

						var theidx= 0;
						var theidy= 0;
						while(theidx != Last){
							x=x.previousSibling;
							theidx=x.id;
							if(theidx && theidx != Last){
								jQuery(x).addClass('ov-no-border');
								x.style.background='url("<?php echo RESERVATIONS_URL; ?>images/black_middle.png") repeat-x';
							}
						}
					}
					if(y !=  y.parentNode.lastChild){
						while(theidy != y.parentNode.lastChild.id){
							y=y.nextSibling;
							theidy=y.id;
							if(theidy && theidy != y.parentNode.lastChild.id){
								if(y.className != "er_overview_cell") jQuery(y).removeClass('ov-no-border');
								y.style.background=y.abbr;
							}
						}
						if(y.parentNode.lastChild.className != "er_overview_cell"){
							y.parentNode.lastChild.style.background=y.abbr;
							jQuery(y.parentNode.lastChild).removeClass('ov-no-border');
						}
					}
				}
			}
		}
	}
	<?php } ?>
	function deletecActiveRes(){
		var activres = document.getElementsByName('activeres');
		if(activres[0]){
			var ares = document.getElementById(activres[0].id);
			var firstDate = <?php if(isset($res->arrival)) echo $res->arrival; else echo 0; ?>;
			var idbefor = ares.previousSibling;

			if(ares.getAttribute("colSpan") == null){
				var splitidbefor=ares.id.split("-");
				ares.setAttribute("onclick", "changer();clickTwo(this,'"+firstDate+"'); clickOne(this,'"+firstDate+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");
				idbefor = ares;
				ares = ares.nextSibling;
				if(ares.id == null) ares = ares.nextSibling;
			}
			var i = 0;
			if(idbefor.className == 'roomhead'){
				var splitidbefor = activres[0].id.split("-");
				splitidbefor[2] = + parseFloat(splitidbefor[2]) -1;
			} else var splitidbefor = idbefor.id.split("-");

			var Colspan = ares.colSpan;
			var next = ares.nextSibling;
			var Parent = ares.parentNode;

			if(!Colspan || Colspan < 1) Colspan = 1;
			jQuery('td[name="activeres"]').removeAttr('class');

			ares.setAttribute("colSpan", "1");
			ares.removeAttribute("class");
			ares.removeAttribute("onclick");
			ares.removeAttribute("name");
			if(ares.firstChild) ares.removeChild(ares.firstChild);
			ares.setAttribute("onclick", "changer();clickTwo(this,'"+firstDate+"'); clickOne(this,'"+firstDate+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");

			while(i != Colspan){
				firstDate += 86400;
				var clone = ares.cloneNode(true);
				var newid = +splitidbefor[2] + i + 1;
				if(newid < 10) newid = '0' + newid;
				clone.setAttribute("onclick", "changer();clickTwo(this,'"+firstDate+"');clickOne(this,'"+firstDate+"');setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');");
				clone.setAttribute("id", splitidbefor[0] + '-' + splitidbefor[1] + '-' + newid);
				Parent.insertBefore(clone, ares);
				i++;
			}
			Parent.removeChild(ares);
		}
	}

	function nextSave(next, i){
		if(!i) i = 0;
		i++;
		if(i < 10){
			if(next && next !== null && next.id) return next;
			else next = next.nextSibling;
		} else return false;
		nextSave(next);
	}
<?php if($overview_options['overview_autoselect'] == 1 && (isset($add) || isset($edit))){ ?>
	function dofakeClick(order){
		var from = document.getElementById("datepicker").value;
		var to = document.getElementById("datepicker2").value;
		var now = <?php echo strtotime(date("d.m.Y", time())); ?> - (the_ov_interval*3);

		deletecActiveRes();
		var explodeFrom = from.split(".");
		var timestampFrom = parseFloat(Date.UTC(explodeFrom[2],explodeFrom[1]-1,explodeFrom[0]))/1000;
		if (document.getElementById("from-time-hour")!=null) timestampFrom = timestampFrom + parseFloat(document.getElementById("from-time-hour").value) * 3600;
		if(order == 1) easyRes_sendReq_Overview(((timestampFrom-now)/the_ov_interval)-4,'', '', the_ov_interval);

		var explodeTo = to.split(".");
		var timestampTo = parseFloat(Date.UTC(explodeTo[2],explodeTo[1]-1,explodeTo[0])) / 1000;
		if (document.getElementById("to-time-hour")!=null) timestampTo = timestampTo + parseFloat(document.getElementById("to-time-hour").value) * 3600;
		var room = document.getElementById("room").value;
		var roomexactly = '';
		if(document.getElementById("roomexactly")) roomexactly = document.getElementById("roomexactly").value;

		//alert("from:"+timestampFrom+" | to:"+timestampTo+" | room:"+room+" | roomexactly:"+roomexactly+" | order:"+order+" | from:"+from+" | to:"+to);

		if(from && to && room && roomexactly && from != "" && to != "" && room != "" && roomexactly != "" && (order == 2) && timestampFrom < timestampTo){
			fakeClick(timestampFrom,timestampTo,room,roomexactly,"black");
		}
	}
	<?php } ?>
</script>
<div id="theOverviewDiv"></div>
<script type="text/javascript">
	jQuery.holdReady(true);<?php if(isset($main_options['overview']['overview_hourly_stand']) && $main_options['overview']['overview_hourly_stand'] == 1){ ?> the_ov_interval = 3600;<?php } ?>
	jQuery(window).load(function(){
		easyRes_sendReq_Overview('<?php echo $moreget; ?>','no', '',the_ov_interval);
	});
</script><?php
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!isset($approve) && !isset($delete) && !isset($view) && !isset($edit) && !isset($sendmail) && !isset($add)){
	if(!isset($show['show_statistics']) || $show['show_statistics'] == 1) do_action('easy-dashboard-between');
	if($show['show_table']==1){ ?>
	<div id="showError"></div>
	<div id="easy-table-div"></div>
	<script>
		jQuery(window).load(function(){
			easyreservation_send_table('', 1);
		});

		function createTablePickers(context){
			var easydateformat = '<?php echo RESERVATIONS_DATE_FORMAT; ?>';
			var dateformate = 'dd.mm.yy';
			if(easydateformat == 'Y/m/d') dateformate = 'yy/mm/dd';
			else if(easydateformat == 'm/d/Y') dateformate = 'mm/dd/yy';
			else if(easydateformat == 'Y-m-d') dateformate = 'yy-mm-dd';
			else if(easydateformat == 'd-m-Y') dateformate = 'dd-mm-yy';
			else if(easydateformat == 'd.m.Y') dateformate = 'dd.mm.yy';

			jQuery("#easy-table-search-date", context || document).datepicker({
				changeMonth: true,
				changeYear: true,
				showOn: 'both',
				firstDay: 1,
				buttonText: '<?php echo addslashes(__( 'choose date' , 'easyReservations' )); ?>',
				buttonImage: '<?php echo RESERVATIONS_URL; ?>images/day.png',
				buttonImageOnly: true,
				dateFormat: dateformate,
				onSelect: function(dateText){
					easyreservation_send_table('all', 1);
				},
				<?php echo easyreservations_build_datepicker(0,0,true); ?>
				defaultDate: +10
			});
		}

		function resetTableValues(){
			var search = document.getElementById('easy-table-search-field');
			var date = document.getElementById('easy-table-search-date');
			var rooms = document.getElementById('easy-table-roomselector');
			var month = document.getElementById('easy-table-monthselector');
			var status = document.getElementById('easy-table-statusselector');
			var order = document.getElementById('easy-table-order');
			var orderby = document.getElementById('easy-table-orderby');
			
			if(order) order.value = '';
			if(orderby) orderby.value = '';
			if(search) search.value = '';
			if(date) date.value = '';
			if(rooms) rooms.selectedIndex = 0;
			if(month) month.selectedIndex = 0;
			if(status) status.selectedIndex = 0;
			easyreservation_send_table('active', 1);
		}
	</script>
	<form name="roomsaver" method="post" action="admin.php?page=reservations&add">
		<input type="hidden" id="room" name="room">
		<input type="hidden" id="roomexactly" name="roomexactly">
		<input type="hidden" name="room-saver-from" id="room-saver-from">
		<input type="hidden" name="room-saver-to" id="room-saver-to">
	</form>
	<?php } ?>
		<?php if( $show['show_new'] == 1 || $show['show_upcoming'] == 1 ) require_once(dirname(__FILE__)."/easyReservations_admin_main_stats.php"); ?>
		<?php if($show['show_upcoming']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px; float:left;margin:0px 10px 10px 0px;clear:none;white-space:nowrap">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Upcoming reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px;padding:0px;background:#fff">
						<div id="container" style="margin:5px 0px 0px 0px;padding:0px;background:#fff; height:300px;f"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } if($show['show_new']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:10%; min-width:400px;min-height: 200px;float:left;margin:0px 10px 10px 0px;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'New reservations' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:0px;background-color:#fff">
						<div id="container2" style="margin:5px 0px 0px 0px;height:300px;"></div>
					</td>
				</tr>
			</tbody>
		</table><?php
		} if($show['show_export']==1){ 
			do_action('easy-add-export-widget');
		} if($show['show_today']==1){ ?>
		<?php
			$rooms = 0;
			foreach ( $the_rooms_array as $theresource ) {
				$roomcount = get_post_meta($theresource->ID, 'roomcount', true);
				if(is_array($roomcount)) $roomcount = $roomcount[0];
				$rooms += $roomcount;
			}
			$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE NOW() BETWEEN arrival AND departure AND approve='yes'"); // Search query 
		?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;clear:none;margin:0px 10px 10px 0px">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'What\'s happening today' , 'easyReservations' ); ?><span style="float:right;font-family:Georgia;font-size:16px;vertical-align:middle" title="<?php echo __( 'workload today' , 'easyReservations' ); ?>"><?php if($rooms > 0) echo round((100/$rooms)*count($queryDepartures)); ?><span id="idworkload" style="font-size:22px;vertical-align:middle">%<span></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="background-color:#fff;padding:0">
						<table class="little_table">
							<thead>
								<tr>
									<th colspan="4"><?php echo __( 'Arrival today' , 'easyReservations' ); ?></th>
								</tr>
								<?php $little_head = '<tr><th>'.__( 'Name' , 'easyReservations' ).'</th>	<th>'.__( 'Resource' , 'easyReservations' ).'</th><th style="text-align:center;">'.__( 'Persons' , 'easyReservations' ).'</th><th style="text-align:right;">'.__( 'Price' , 'easyReservations' ).'</th></tr>'; echo $little_head;?>
							</thead>
							<tbody>
							<?php
								$queryArrivalers = $wpdb->get_results("SELECT id, name, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(arrival) = DATE(NOW())"); // Search query
								$count = 0;

								foreach($queryArrivalers as $arrivler){
									$count++;
									$depature = new Reservation($arrivler->id, false, true);
									$depature->Calculate();
									if($count % 2 == 0) $class="odd";
									else $class="even";?>
									<tr class="<?php echo $class; ?>">
										<td><b><a href="admin.php?page=reservations&edit=<?php echo $depature->id; ?>"><?php echo $depature->name; ?></a></b></td>
										<td><?php echo $the_rooms_array[$depature->resource]->post_title; ?></td>
										<td style="text-align:center;"><?php echo $depature->adults; ?> (<?php echo $depature->childs; ?>)</td>
										<td style="text-align:right;"><?php echo $depature->formatPrice(true); ?></td>
									</tr><?php 
									$depature->destroy();
								}
								if($count == 0) echo '<tr><td>'.__('None' ,'easyReservations').'</td></tr>'; ?>
							</tbody>
							<thead>
								<tr>
									<th colspan="4"><?php echo __( 'Departure today' , 'easyReservations' ); ?></th>
								</tr>
								<?php echo $little_head; ?>
							</thead>
							<tbody><?php 
							$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(departure) = DATE(NOW()) "); // Search query
							$count = 0;
							foreach($queryDepartures as $depaturler){
								$count++;
								$depature = new Reservation($depaturler->id, false, true);
								$depature->Calculate();
								if($count % 2 == 0) $class="odd";
								else $class="even";?>
								<tr class="<?php echo $class; ?>">
									<td><b><a href="admin.php?page=reservations&edit=<?php echo $depature->id; ?>"><?php echo $depature->name; ?></a></b></td>
									<td><?php echo $the_rooms_array[$depature->resource]->post_title; ?></td>
									<td style="text-align:center;"><?php echo $depature->adults; ?> (<?php echo $depature->childs; ?>)</td>
									<td style="text-align:right;"><?php echo $depature->formatPrice(true); ?></td>
								</tr><?php 
								$depature->destroy();
							}
							if($count == 0) echo '<tr><td>'.__('None' ,'easyReservations').'</td></tr>'; ?>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
	<script>
		jQuery('select[name="export_tech"]').bind('change', function(){
			if(jQuery(this).value == 'xls') exportExcelCharset();
			else jQuery('#charset').html('');
		});
		function exportExcelCharset(){
			var charset = '<select name="charset">';
			jQuery.each(['UTF-8', 'UCS-4', 'UCS-4BE', 'UCS-4LE', 'UCS-2', 'UCS-2BE', 'UCS-2LE', 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP',
			'ASCII', 'EUC-JP', 'SJIS', 'eucJP-win', 'SJIS-win', 'ISO-2022-JP', 'ISO-2022-JP-MS', 'CP932', 'CP51932', 'SJIS-mac', 'SJIS-Mobile#DOCOMO', 'SJIS-Mobile#KDDI', 'SJIS-Mobile#SOFTBANK',
			'UTF-8-Mobile#DOCOMO', 'UTF-8-Mobile#KDDI-A', 'UTF-8-Mobile#KDDI-B', 'UTF-8-Mobile#SOFTBANK', 'ISO-2022-JP-MOBILE#KDDI', 'JIS', 'JIS-ms', 'CP50220', 'CP50220raw', 'CP50220', 'CP50222',
			'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15',
			'byte2be', 'byte2le', 'byte4be', 'byte4le', 'BASE64', 'HTML-ENTITIES', '7bit', '8bit', 'EUC-CN', 'CP936', 'GB18030', 'HZ', 'EUC-TW', 'CP950', 'BIG-5', 'EUC-KR', 'UHC', 'ISO-2022-KR',
			'Windows-1251', 'Windows-1252', 'CP866', 'KOI8-R'], function(k,v){
          charset += '<option value="'+v+'">'+v+'</option>';
			});
      charset += '</select>';
			jQuery('#charset').html('Charset: '+charset);
		}
		function exportSelect(x){
			if(x == "sel"){
				var ExportOptions = '<div class="fakehr"></div><span style="float:left;width:100px;"><b><?php echo addslashes(__( 'Type' , 'easyReservations' ));?></b><br><input type="checkbox" name="approved" checked> <?php echo addslashes(__( 'Approved' , 'easyReservations' ));?><br><input type="checkbox" name="pending" checked> <?php echo addslashes(__( 'Pending' , 'easyReservations' ));?><br><input type="checkbox" name="rejected" checked> <?php echo addslashes(__( 'Rejected' , 'easyReservations' ));?><br><input type="checkbox" name="trashed" checked> <?php echo addslashes(__( 'Trashed' , 'easyReservations' )); ?></span>';
				ExportOptions += '<span><b><?php echo addslashes(__( 'Time' , 'easyReservations' ));?></b><br><input type="checkbox" name="past" checked> <?php echo addslashes(__( 'Past' , 'easyReservations' ));?><br><input type="checkbox" name="present" checked> <?php echo addslashes(__( 'Present' , 'easyReservations' ));?><br><input type="checkbox" name="future" checked> <?php echo addslashes(__( 'Future' , 'easyReservations' ));?></span><br>';
				ExportOptions += '<br>';
				document.getElementById("exportDiv").innerHTML = ExportOptions;
			} else document.getElementById("exportDiv").innerHTML = '';
		}
		function checkAllController(theForm,obj,checkName){
			if(obj.checked==true){
				var eleArr=theForm.elements[checkName+'[]'];
				for (i=0;i<eleArr.length;i++){eleArr[i].checked= true;}
			}else{
				var eleArr=theForm.elements[checkName+'[]'];
				for (i=0;i<eleArr.length;i++){eleArr[i].checked= false;}
			}
		}
	</script>
<?php }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + VIEW RESERVATION + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(isset($approve) || isset($delete) || isset($view) || isset($sendmail)){ ?>
  <table style="width:99%;margin-top:8px" cellspacing="0"><tr><td style="width:30%;" valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?> withicons">
			<thead>
				<tr>
					<th colspan="2">
						<?php if(isset($approve)) { echo __( 'Approve' , 'easyReservations' ); } elseif(isset($delete)) { echo __( 'Reject' , 'easyReservations' );  } elseif(isset($view)) { echo __( 'View' , 'easyReservations' ); } echo ' '.__( 'Reservation' , 'easyReservations' ); ?> <span class="headerlink"><a href="admin.php?page=reservations&edit=<?php echo $res->id; ?>">#<?php echo $res->id; ?></a></span>
						<span style="float:right">
							<a href="admin.php?page=reservations&edit=<?php echo $res->id; ?>" class="button"><?php echo __( 'Edit' , 'easyReservations' ); ?></a>
							<?php do_action('easy-view-title-right', $res); ?>
						</span>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if(isset($view)){ ?>
					<tr>
						<td colspan="2" nowrap><?php echo easyreservations_reservation_info_box($res, 'view', $res->status); ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td nowrap style="width:40%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td> 
					<td nowrap style="width:60%"><b><?php echo $res->name;?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap style="width:40%"><img style="vertical-align:text-bottom;width:16px;height:16px;" src="<?php echo RESERVATIONS_URL; ?>images/day.png"> <?php printf ( __( 'Date' , 'easyReservations' ));?></td>
					<td><b><?php echo date(RESERVATIONS_DATE_FORMAT_SHOW,$res->arrival); ?> - <?php echo date(RESERVATIONS_DATE_FORMAT_SHOW, $res->departure);?>
							<small>(<?php echo $res->times.' '.easyreservations_interval_infos($the_rooms_intervals_array[$res->resource], 0, $res->times); ?>)</small></b></td>
				</tr>

				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/email.png"> <?php printf ( __( 'Email' , 'easyReservations' ));?></td> 
					<td><b><?php echo $res->email;?></b></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/persons.png"> <?php echo __( 'Persons' , 'easyReservations' );?></td>
					<td><?php echo __( 'Adults' , 'easyReservations' );?>: <b><?php echo $res->adults;?></b> <?php echo __( 'Children' , 'easyReservations' );?>: <b><?php echo $res->childs;?></b></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/room.png"> <?php printf ( __( 'Resource' , 'easyReservations' ));?></td> 
					<td><b><?php echo __($resource_name); if(!$bypers && $res->resourcenumbername) echo ' - '.$res->resourcenumbername; ?></b></td>
				</tr>
				<?php if(!empty($res->country)){ ?>
					<tr class="alternate">
						<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?></td>
						<td><b><?php echo easyreservations_country_name($res->country); ?></b></td>
					</tr>
				<?php }
				do_action('easy-res-view-table-bottom', $res);
				$c_rows = 0;
				if(!empty($customs) && is_array($customs)){
					foreach($customs as $custom){
						if(isset($custom['id'])) $custom['title'] = $custom_fields['fields'][$custom['id']]['title'];
						$class = $c_rows%2==0 ? '' : 'alternate';

						echo '<tr class="'.$class.'">';
						echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'images/message.png"> '.__($custom['title']).'</b></td>';
						echo '<td><b>'.$res->getCustomsValue($custom).'</b></td></tr>';
						$c_rows++;
					}
				}
				if(!empty($custom_prices)){
					foreach($custom_prices as $custom_price){
						if(isset($custom_price['id'])){
							$custom_field = $custom_fields['fields'][$custom_price['id']];
							$custom_price['title'] = $custom_field['title'];
							$custom_price['amount'] = $res->calculateCustom($custom_price['id'], $custom_price['value'], $custom_prices);
							$custom_price['value'] = $custom_field['options'][$custom_price['value']]['value'];
						}
						$class = $c_rows%2==0 ? '' : 'alternate';
						echo '<tr class="'.$class.'">';
						echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'images/money.png"> '.__($custom_price['title']).'</b></td>';
						echo '<td><b>'.$custom_price['value'].'</b>: <b>'.easyreservations_format_money($custom_price['amount'], 1).'</b></td></tr>';
						$c_rows++;
					}
				}
				?>
			</tbody>
		</table>
		<br>
		<div><?php echo easyreservations_detailed_price($res->history, $res->resource); ?></div>
		</td>
		<td style="width:1%;"></td>
		<td style="width:35%;" valign="top" style="vertical-align:top;">
		<?php if(isset($view) && function_exists('easyreservations_generate_chat')){ ?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;margin-top: 0px;">
				<thead>
					<tr>
						<th><?php echo __( 'GuestContact' , 'easyReservations' );?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="margin:0px;padding:0px">
							<?php echo easyreservations_generate_chat( $res, 'admin' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($edit)){
	easyreservations_build_datepicker(1,array('datepicker','datepicker2', 'reservation_date'), RESERVATIONS_DATE_FORMAT);
	add_action('admin_print_footer_scripts','easyreservations_restrict_input_dash');
	$customfields = '';
	$number_0=0;
	$number_1=0;

	if(!empty($customs)){
		foreach($customs as $key => $custom){
			$class = $number_0%2==0 ? '' : 'alternate';
			$number_0++;
			$number_1++;
			if(isset($custom['id'])){
				$customfields .= '<tr class="'.$class.'">';
				$customfields .= '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'/images/message.png"> <b>'.__($custom_fields['fields'][$custom['id']]['title']).'</b> ('.$custom['mode'].') <a href="admin.php?page=reservations&edit='.$edit.'&deletecustomfield='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'/images/delete.png"></a> <input type="hidden" name="customtitle'.$key.'" value="'.$custom['title'].'"></td>';
				$customfields .= '<td>'.easyreservations_generate_custom_field($custom['id'], $custom['value']).'</td></tr>';
			} else {
				$customfields .= '<tr class="'.$class.'">';
				$customfields .= '<td style="vertical-align:text-bottom;text-transform: capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'/images/message.png"> <b>'.__($custom['title']).'</b> ('.$custom['mode'].') <a href="admin.php?page=reservations&edit='.$edit.'&deletecustomfield='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'/images/delete.png"></a> <input type="hidden" name="customtitle'.$key.'" value="'.$custom['title'].'"></td>';
				$customfields .= '<td><input type="text" name="customvalue'.$key.'" value="'.$custom['value'].'"><input type="hidden" name="custommodus'.$key.'" value="'.$custom['mode'].'"></td></tr>';
			}
		}
	}
	$number_2=0;
	if(!empty($custom_prices)){
		foreach($custom_prices as $key => $custom_price){
			$class = $number_0%2==0 ? '' : 'alternate';
			$number_0++;
			$number_2++;
			if(isset($custom_price['id'])){
				$customfields .= '<tr class="'.$class.'">';
				$customfields .= '<td style="vertical-align:text-bottom;text-transform:capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'/images/money.png"> <b>'.__($custom_fields['fields'][$custom_price['id']]['title']).'</b> ('.$custom_price['mode'].') <a href="admin.php?page=reservations&edit='.$edit.'&deletepricefield='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'/images/delete.png"></a></td>';
				$customfields .= '<td>'.easyreservations_generate_custom_field($custom_price['id'], $custom_price['value'], 'onchange="easyreservations_send_price_admin();"').'</td></tr>';
			} else {
				$customfields .= '<tr class="'.$class.'">';
				$customfields .= '<td style="vertical-align:text-bottom;text-transform:capitalize;" nowrap><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'/images/money.png"> <b>'.__($custom_price['title']).'</b> ('.$custom_price['mode'].') <a href="admin.php?page=reservations&edit='.$edit.'&deletepricefield='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'/images/delete.png"></a> <input type="hidden" name="customPtitle'.$key.'" value="'.$custom_price['title'].'"></td>';
				$customfields .= '<td><input type="text" name="customPvalue'.$key.'" value="'.$custom_price['value'].'" style="width:200px"><input type="text" name="custom_price'.$key.'" id="custom_price'.$key.'" onchange="easyreservations_send_price_admin();" value="'.$custom_price['amount'].'" style="width:70px;"> &'.RESERVATIONS_CURRENCY.';<input type="hidden" name="customPmodus'.$key.'" value="'.$custom_price['mode'].'"></td></tr>';
			}
		}
	}
	?><script type="text/javascript">
	function setPrice(){
		if( document.editreservation.fixReservation.checked == true ){
			var string = '<input type="text" value="<?php echo $pricepaid_explode[0]; ?>" name="priceset" style="width:60px;text-align:right;"><?php echo ' &'.RESERVATIONS_CURRENCY.';';?>';
			document.getElementById("priceSetter").innerHTML += string;
		} else if( document.editreservation.fixReservation.checked == false ){
			document.getElementById("priceSetter").innerHTML = '';
		}
	}
</script>
<form id="editreservation" name="editreservation" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>">
<?php wp_nonce_field('easy-main-edit','easy-main-edit'); ?>
<input type="hidden" name="editthereservation" id="editthereservation" value="editthereservation">
<input type="hidden" name="reserved" id="reserved" value="<?php echo $res->reservated; ?>">
<input type="hidden" name="reseasdrved" id="resasderved" value="<?php echo $res->arrival; ?>">
<input type="hidden" name="copy" id="copy" value="no">
	<table  style="width:99%;margin-top:8px" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width:550px;" valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?> withicons" style="width:99%; margin-bottom:10px;">
					<thead>
						<tr>
							<th colspan="2">
								<?php printf ( __( 'Edit reservation' , 'easyReservations' ));?> <span class="headerlink"><a href="admin.php?page=reservations&view=<?php echo $edit; ?>">#<?php echo $edit; ?></a></span>
								<span style="float:right">
									<?php if($res->status == 'del'){ ?>
	                  <a class="button" onClick="if(confirm('<?php echo __( 'Really delete this reservation permanently?' , 'easyReservations'); ?>')) { window.location = '<?php echo 'admin.php?page=reservations&bulkArr[]='.$res->id.'&bulk=3&easy-main-bulk='.wp_create_nonce('easy-main-bulk'); ?>'; }" title="<?php echo __( 'delete' , 'easyReservations' ); ?>"><?php echo ucfirst(__( 'delete' , 'easyReservations' ));?></a>
									<?php } else { ?>
										<a class="button" onClick="if(confirm('<?php echo __( 'Really move this reservation to trash?' , 'easyReservations'); ?>')) { window.location = '<?php echo 'admin.php?page=reservations&bulkArr[]='.$res->id.'&bulk=1&easy-main-bulk='.wp_create_nonce('easy-main-bulk'); ?>'; }" title="<?php echo __( 'trash' , 'easyReservations' ); ?>"><?php echo ucfirst(__( 'trash' , 'easyReservations' ));?></a>
									<?php }
									do_action('easy-edit-title-right', $res); ?>
								</span>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2" nowrap><?php echo easyreservations_reservation_info_box($res, 'edit', $res->status); ?></td>
						</tr>
						<tr>
							<td nowrap style="min-width:35%;width:35%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td> 
							<td nowrap style="min-width:65%;width:65%"><input type="text" name="name" value="<?php echo $res->name;?>"></td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;width:16px;height:16px;" src="<?php echo RESERVATIONS_URL; ?>images/day.png"> <?php printf ( __( 'From' , 'easyReservations' ));?>:</td>
							<td>
								<input type="text" id="datepicker" style="width:80px" name="date" value="<?php echo date(RESERVATIONS_DATE_FORMAT,$res->arrival); ?>" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>">
								<select name="from-time-hour" id="from-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyreservations_time_options(date("G",$res->arrival)); ?></select>:
								<select name="from-time-min"><?php echo easyreservations_num_options("00",59,date("i",$res->arrival)); ?></select>

							</td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/to.png"> <?php printf ( __( 'To' , 'easyReservations' ));?>:</td> 
							<td>
								<input type="text" id="datepicker2" style="width:80px" name="dateend" value="<?php echo date(RESERVATIONS_DATE_FORMAT,$res->departure); ?>" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>">
								<select name="to-time-hour" id="to-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyreservations_time_options(date("G",$res->departure)); ?></select>:
								<select name="to-time-min"><?php echo easyreservations_num_options("00",59,date("i",$res->departure)); ?></select></td>
						</tr>
						<tr class="alternate" id="easy_edit_persons">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/persons.png"> <?php echo __( 'Persons' , 'easyReservations' );?></td> 
							<td>
								<?php printf ( __( 'Adults' , 'easyReservations' ));?>:
								<select name="persons" onchange="easyreservations_send_price_admin();"><?php echo easyreservations_num_options(1,50,$res->adults); ?></select>
								<?php printf ( __( 'Children' , 'easyReservations' ));?>:
								<select name="childs" onchange="easyreservations_send_price_admin();"><?php echo easyreservations_num_options(0,50,$res->childs); ?></select>
							</td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/room.png"> <?php printf ( __( 'Resource' , 'easyReservations' ));?></td> 
							<td>
								<select  name="room" id="room"  onchange="easyreservations_send_price_admin();changer();get_the_select(1, this.value);<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo easyreservations_resource_options($res->resource,1); ?></select> 
								<span id="the_room_exactly"></span>
							</td>
						</tr>
						<tr class="alternate">
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/email.png"> <?php printf ( __( 'Email' , 'easyReservations' ));?></td> 
							<td><input type="text" name="email" value="<?php echo $res->email;?>" onchange="easyreservations_send_price_admin();"></td>
						</tr>
						<tr>
							<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));?></td> 
							<td><select name="country" style="width:200px;"><option value="" <?php if($res->country=='') echo 'selected="selected"'; ?>><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyreservations_country_options($res->country); ?></select></td>
						</tr>
						<?php echo $customfields; ?>
					</tbody>
					<tbody id="testit">
					</tbody>
					<tbody id="customPrices">
					</tbody>
				</table>
				<input type="submit" onclick="document.getElementById('editreservation').submit(); return false;" class="easybutton button-primary" value="<?php printf ( __( 'Edit reservation' , 'easyReservations' ));?>"><input type="submit" onclick="document.getElementById('copy').value = 'yes';document.getElementById('editreservation').submit(); return false;" class="button" value="<?php printf ( __( 'Copy' , 'easyReservations' ));?>"><span class="showPrice" style="float:right;"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b><?php echo easyreservations_format_money(0,1); ?></b></span></span></div>
				<div style="width:99%;margin-top:10px;"><?php echo easyreservations_detailed_price($res->history, $res->resource); ?><?php echo $information; ?></div>
			</td>
			<td style="width:1%"></td>
			<td valign="top">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:4px;">
					<thead>
						<tr>
							<th colspan="2"><?php printf ( __( 'Status & Price' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="alternate">
							<td nowrap><?php printf ( __( 'Status' , 'easyReservations' ));?></td>
							<td nowrap style="text-align:right"><select name="reservation_status" style="width:99%;float:right"><option value="" <?php if(empty($res->status)) echo 'selected'; ?>><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="yes" <?php if($res->status == 'yes') echo 'selected'; ?>><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value="no" <?php if($res->status == 'no') echo 'selected'; ?>><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option><option value="del" <?php if($res->status == 'del') echo 'selected'; ?>><?php printf ( __( 'Trashed' , 'easyReservations' ));?></option></select></td>
						</tr>
						<tr>
							<td nowrap><?php echo __( 'Reserved' , 'easyReservations' );?></td>
							<td nowrap style="text-align:right"><input type="text" name="reservation_date" id="reservation_date" style="width:80px" value="<?php echo date(RESERVATIONS_DATE_FORMAT, $res->reservated); ?>"></td>
						</tr>
						<tr class="alternate">
							<td nowrap><?php echo __( 'Assign user' , 'easyReservations' );?></td>
							<td nowrap style="text-align:right"><select name="edit_user"><option value="0"><?php echo __( 'None' , 'easyReservations' );?></option>
							<?php 
								echo easyreservations_get_user_options($res->user);
							?>
							</select></td>
						</tr>
						<tr>
							<td nowrap><?php printf ( __( 'Fixed Price' , 'easyReservations' ));?></td>
							<td nowrap style="text-align:right"><input type="checkbox" onclick="setPrice()" name="fixReservation" <?php if($pricepaid_explode[0] != '') echo 'checked'; ?>> <span id="priceSetter"><?php if($pricepaid_explode[0] != ''){ ?><input type="text" value="<?php echo $pricepaid_explode[0]; ?>" name="priceset" style="width:60px;text-align: right;"><?php echo ' &'.RESERVATIONS_CURRENCY.';'; } ?></span></td>
						</tr>
						<tr class="alternate">
							<td nowrap><?php printf ( __( 'Paid' , 'easyReservations' ));?></td>
							<td nowrap style="text-align:right"><input type="text" name="EDITwaspaid" value="<?php if(isset($pricepaid_explode[1])) echo $pricepaid_explode[1]; ?>" style="width:60px;text-align:right"> <?php echo ' &'.RESERVATIONS_CURRENCY.';';?></td>
						</tr>
					</tbody>
				</table>
				<?php do_action('easy-dash-edit-side-middle', $res);?>
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:4px">
					<thead>
						<tr>
							<th><?php printf ( __( 'Send mail' , 'easyReservations' ));?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="alternate">
							<td nowrap> &nbsp;<input type="checkbox" name="sendthemail" value="on"> <i><?php printf ( __( 'Send mail to user on edit' , 'easyReservations' ));?></i></td>
						</tr>
						<?php do_action('easy-mail-add-input'); ?>
						<tr>
							<td><textarea type="text" name="approve_message" id="approve_message" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Message') this.value = '';" onblur="if (this.value == '') this.value = 'Message';">Message</textarea></td>
						</tr>
					</tbody>
				</table>
				<?php echo easyreservations_generate_admin_custom_add(); ?>
				<?php do_action('easy-dash-edit-side-bottom', $res);?>
			</td>
	</table>
</tr>
</form>
<?php do_action('easy-after-edit', $res); ?>
<script type="text/javascript">easyreservations_send_price_admin();
get_the_select('<?php echo $res->resourcenumber; ?>', '<?php echo $res->resource; ?>');</script>
<?php
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($add)){
	easyreservations_build_datepicker(1,array('datepicker','datepicker2', 'reservation_date'), RESERVATIONS_DATE_FORMAT);
	add_action('admin_print_footer_scripts','easyreservations_restrict_input_dash');
?> <!-- // Content will only show on edit Reservation -->
	<script>
	var Add = 0;
	function addtoForm(){ // Add field to the Form
		Add += 1;
		document.getElementById("testit").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/message.png"> '+document.getElementById("customtitle").value+' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.style.display = \'none\'" src="<?php echo RESERVATIONS_URL; ?>images/delete.png"></td><td>'+document.getElementById("customvalue").value+'<input type="hidden" name="customtitle'+Add+'" value="'+document.getElementById("customtitle").value+'"><input type="hidden" name="customvalue'+Add+'" value="'+document.getElementById("customvalue").value+'"><input type="hidden" name="custommodus'+Add+'" value="'+document.getElementById("custommodus").value+'"></td></tr>';
	}

	var PAdd = 0;
	function addPtoForm(){ // Add field to the Form
		PAdd += 1;
		document.getElementById("customPrices").innerHTML += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/money.png"> '+document.getElementById("customPtitle").value+' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.style.display = \'none\'" src="<?php echo RESERVATIONS_URL; ?>images/delete.png"></td><td>'+document.getElementById("customPvalue").value+': '+document.getElementById("customPamount").value+'<input name="customPtitle'+PAdd+'" value="'+document.getElementById("customPtitle").value+'" type="hidden"><input name="customPvalue'+PAdd+'" value="'+document.getElementById("customPvalue").value+'" type="hidden"><input name="custom_price'+PAdd+'" id="custom_price'+PAdd+'" value="'+document.getElementById("customPamount").value+'" type="hidden"><input type="hidden" name="customPmodus'+PAdd+'" value="'+document.getElementById("customPmodus").value+'"></td></tr>';
		easyreservations_send_price_admin();
	}

	function delfromForm(add,x,y){
		var vormals = document.getElementById("testit").innerHTML;
		var string = '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/message.png"> '+x+' <img style="vertical-align:middle;" onclick="delfromForm('+add+',\''+x+'\',\''+y+'\')" src="<?php echo RESERVATIONS_URL; ?>images/delete.png"></td><td>'+y+'<input name="customtitle'+add+'" value="'+x+'" type="hidden"><input name="customvalue'+add+'" value="'+y+'" type="hidden"></td></tr>';
		var jetzt = vormals.replace(string, "");
		document.getElementById("testit").innerHTML = jetzt;
	}

	function delPfromForm(add,x,y,z){
		var vormals = document.getElementById("customPrices").innerHTML;
		var string = '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/money.png"> '+x+' <img style="vertical-align:middle;" onclick="delPfromForm('+add+',\''+x+'\',\''+y+'\',\''+z+'\')" src="<?php echo RESERVATIONS_URL; ?>images/delete.png"></td><td>'+y+': '+z+'<input name="customPtitle'+add+'" value="'+x+'" type="hidden"><input name="customPvalue'+add+'" value="'+y+'" type="hidden"><input name="custom_price'+add+'" id="custom_price'+add+'" value="'+z+'" type="hidden"></td></tr>';
		var jetzt = vormals.replace(string, "");
		document.getElementById("customPrices").innerHTML = jetzt;
		easyreservations_send_price_admin();
	}
	function setPrice(){
		if( document.editreservation.fixReservation.checked == true ){
			var string = '<tr><td colspan="2"><p><input name="setChoose" type="radio" value="custm"> <?php printf ( __( 'set price' , 'easyReservations' ));?> <input name="priceAmount" type="text" style="width:50px;height:20px"> <?php echo '&'.RESERVATIONS_CURRENCY.';'; ?></p>';
			string += '<div style="margin-top:10px;"><input name="setChoose" type="radio" value="calc" checked> <?php printf ( __( 'fix the sum of the normal calculation' , 'easyReservations' ));?></div></td></tr>';
			string += '<tr><td><?php printf ( __( 'Paid' , 'easyReservations' ));?></td><td><span style="float:right"><input name="paidAmount" type="text"value="0" style="width:50px;height:20px;"> <?php echo '&'.RESERVATIONS_CURRENCY.';'; ?></span></td></tr>';
			document.getElementById("priceCell").innerHTML += string;
		} else if( document.editreservation.fixReservation.checked == false ){
			document.getElementById("priceCell").innerHTML = '';
		}
	}
</script>
<form id="editreservation" name="editreservation" method="post" action=""> 
<?php wp_nonce_field('easy-main-add','easy-main-add'); ?>
<input type="hidden" name="addreservation" id="addreservation" value="addreservation">
<table  style="width:99%;margin-top:8px" cellspacing="0">
	<tr>
	<td style="width:350px;" valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?> withicons">
			<thead>
				<tr>
					<th colspan="2"><?php printf ( __( 'Add Reservation' , 'easyReservations' ));?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td nowrap style="width:45%"><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/user.png"> <?php printf ( __( 'Name' , 'easyReservations' ));?></td> 
					<td><input type="text" name="name" value="<?php if(isset($_POST['name'])) echo $_POST['name']; ?>" align="middle"></td>
				</tr>
				<tr class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;width:16px;height:16px;" src="<?php echo RESERVATIONS_URL; ?>images/day.png"> <?php printf ( __( 'From' , 'easyReservations' ));
					if(isset($_POST['from-time-hour'])) $fromtimeh = $_POST['from-time-hour']; else $fromtimeh = 12;
					if(isset($_POST['from-time-min'])) $fromtimem = $_POST['from-time-min']; else $fromtimem = 0;
					?>:</td> 
					<td><input type="text" id="datepicker" style="width:80px" name="date" value="<?php if(isset($_POST['date'])) echo $_POST['date']; ?>" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>"> <select name="from-time-hour" id="from-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyreservations_time_options($fromtimeh); ?></select>:<select name="from-time-min"><?php echo easyreservations_num_options("00",59,$fromtimem); ?></select></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/to.png"> <?php printf ( __( 'To' , 'easyReservations' ));
					if(isset($_POST['to-time-hour'])) $totimeh = $_POST['to-time-hour']; else $totimeh = 12;
					if(isset($_POST['to-time-min'])) $totimem = $_POST['to-time-min']; else $totimem = 00;
					?>:</td> 
					<td><input type="text" id="datepicker2" style="width:80px" name="dateend" value="<?php if(isset($_POST['dateend'])) echo $_POST['dateend']; ?>" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"> <select name="to-time-hour" id="to-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo easyreservations_time_options($totimeh); ?></select>:<select name="to-time-min"><?php echo easyreservations_num_options("00",59,$totimem); ?></select></td>
				</tr>
				<tr valign="top" class="alternate">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/persons.png"> <?php printf ( __( 'Persons' , 'easyReservations' ));?></td> 
					<td>
						<?php printf ( __( 'Adults' , 'easyReservations' ));  if(isset($_POST['persons'])) $pers = $_POST['persons']; else $pers = 1; ?>:
						<select name="persons" onchange="easyreservations_send_price_admin();"><?php echo easyreservations_num_options(1,50, $pers); ?></select>
						<?php printf ( __( 'Children' , 'easyReservations' )); if(isset($_POST['childs'])) $childs = $_POST['childs']; else $childs = 0; ?>:
						<select name="childs" onchange="easyreservations_send_price_admin();"><?php echo easyreservations_num_options(0,50, $childs); ?></select>
					</td>
				</tr>
				<tr valign="top">
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/room.png"> <?php printf ( __( 'Resource' , 'easyReservations' )); 
					$reso = isset($_POST['room']) ? $_POST['room'] : '';
					$resoex = isset($_POST['roomexactly']) ?  $_POST['roomexactly'] : 1;?></td>
					<td>
						<select id="room" name="room" onchange="easyreservations_send_price_admin();changer();get_the_select(1,this.value);<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><?php echo easyreservations_resource_options($reso, 1); ?></select>
						<span id="the_room_exactly"></span>
					</td>
				</tr>
				<tr  class="alternate" >
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/email.png"> <?php printf ( __( 'Email' , 'easyReservations' ));?></td> 
					<td><input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" onchange="easyreservations_send_price_admin();"></td>
				</tr>
				<tr>
					<td nowrap><img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL; ?>images/country.png"> <?php printf ( __( 'Country' , 'easyReservations' ));  if(isset($_POST['country'])) $count = $_POST['country']; else $count = ''; ?></td>
					<td><select name="country"><option value=""><?php echo __( 'Unknown' , 'easyReservations' );?></option><?php echo easyreservations_country_options($count); ?></select></td>
				</tr>
			</tbody>
			<tbody id="testit">
			</tbody>
			<tbody id="customPrices">
			</tbody>
		</table>
		<br><input type="button" onclick="document.getElementById('editreservation').submit(); return false;" class="easybutton button-primary" value="<?php printf ( __( 'Add reservation' , 'easyReservations' ));?>"><span class="showPrice" style="float:right;"><?php echo __( 'Price' , 'easyReservations' ); ?>: <span id="showPrice" style="font-weight:bold;"><b><?php echo easyreservations_format_money(0,1); ?></b></span></span></div>
		</td><td style="width:4px"></td>
		<td valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:4px;">
				<thead>
					<tr>
						<th colspan="2"><?php printf ( __( 'Status & Price' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap><?php printf ( __( 'Status' , 'easyReservations' ));?></td>
						<td nowrap><select name="reservationStatus" style="width:99%"><option value=""><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="yes"><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value="no"><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option></select></td>
					</tr>
					<tr>
						<td nowrap><?php echo __( 'Assign user' , 'easyReservations' );?></td>
							<td nowrap style="text-align:right"><select name="edit_user"><option value="0"><?php echo __( 'None' , 'easyReservations' );?></option>
							<?php 
								echo easyreservations_get_user_options();
							?>
							</select></td>
					</tr>
					<tr>
						<td nowrap><?php printf ( __( 'Reserved' , 'easyReservations' ));?></td>
						<td nowrap style="text-align:right"><input type="text" name="reservation_date" id="reservation_date" style="width:80px" value="<?php echo date(RESERVATIONS_DATE_FORMAT, time()); ?>"></td>
					</tr>
					<tr>
						<td nowrap><?php printf ( __( 'Price' , 'easyReservations' ));?></td>
						<td nowrap><input type="checkbox" onclick="setPrice();" name="fixReservation"> <?php printf ( __( 'Fix Price' , 'easyReservations' ));?> <br></td>
					</tr>
				</tbody>
				 <tbody id="priceCell">
				 </tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:4px">
				<thead>
					<tr>
						<th><?php printf ( __( 'Add custom field' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap>
						<select name="custommodus" style="margin-bottom:4px" id="custommodus"><option value="edit"><?php printf ( __( 'Editable' , 'easyReservations' ));?></option><option value="visible"><?php printf ( __( 'Visible' , 'easyReservations' ));?></option><option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?></option></select> <?php printf ( __( 'for Guest' , 'easyReservations' ));?><br>
						<input type="text" name="customtitle" id="customtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><textarea type="text" name="customvalue" id="customvalue" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';">Value</textarea>
						<br><input type="button" onclick="addtoForm();" style="margin-top:3px" class="button" value="<?php printf ( __( 'Add custom Field' , 'easyReservations' ));?>"></td>
					</tr>
				</tbody>
			</table>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;">
				<thead>
					<tr>
						<th><?php printf ( __( 'Add custom price field' , 'easyReservations' ));?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td nowrap>
						<select name="customPmodus" style="margin-bottom:4px" id="customPmodus"><option value="edit"><?php printf ( __( 'Selectable' , 'easyReservations' ));?></option><option value="visible"><?php printf ( __( 'Visible' , 'easyReservations' ));?></option><option value="hidden"><?php printf ( __( 'Hidden' , 'easyReservations' ));?></option></select> <?php printf ( __( 'for Guest' , 'easyReservations' ));?><br>
						<input type="text" name="customPtitle" id="customPtitle" style="width:260px" value="Title" onfocus="if (this.value == 'Title') this.value = '';" onblur="if (this.value == '') this.value = 'Title';"><br><input type="text" name="customPvalue" id="customPvalue" value="Value" style="width:190px;margin-top:2px;" value="Value" onfocus="if (this.value == 'Value') this.value = '';" onblur="if (this.value == '') this.value = 'Value';"><input type="text" name="customPamount" id="customPamount" style="width:60px;margin-top:2px;text-align:right;" value="Amount" onfocus="if (this.value == 'Amount') this.value = '';" onblur="if (this.value == '') this.value = 'Amount';"><?php echo '&'.RESERVATIONS_CURRENCY.';'; ?>
						<br><input type="button" onclick="addPtoForm();" class="button" value="<?php printf ( __( 'Add custom Price Field' , 'easyReservations' ));?>"></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
</form>
<script>get_the_select(<?php echo $resoex; ?>, document.getElementById('room').value);</script>
<?php if(isset($_POST['room-saver-to'])){ ?><script>jQuery(document).ready(function(){ fakeClick('<?php echo $_POST['room-saver-from']; ?>','<?php echo $_POST['room-saver-to']; ?>','<?php echo $_POST['room']; ?>','<?php echo $_POST['roomexactly']; ?>', '');setVals2(<?php echo $_POST['room'].','.$_POST['roomexactly']; ?>);document.getElementById('datepicker').value='<?php echo date(RESERVATIONS_DATE_FORMAT, $_POST['room-saver-from']); ?>';document.getElementById('datepicker2').value='<?php echo date(RESERVATIONS_DATE_FORMAT, $_POST['room-saver-to']); ?>';easyreservations_send_price_admin();});</script><?php } //Set Room and Roomexactly after click on Overview and redirected to add
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + APPROVE / REJECT - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($approve) || isset($delete)) {
	if(isset($delete)){ $delorapp=$delete; $delorapptext='reject'; } elseif(isset($approve)){ $delorapp=$approve; $delorapptext='approve'; } ?>  <!-- Content will only show on delete or approve Reservation //--> 
	<form method="post" action="admin.php?page=reservations<?php if(isset($approve)) echo "&approve=".$approve ;  if(isset($delete)) echo "&delete=".$delete ;?>"  id="reservation_approve" name="reservation_approve">
		<input type="hidden" name="action" value="reservation_approve"/>
		<input type="hidden" name="<?php if(isset($approve)) echo 'approve'; else echo 'delete' ?>" value="yes" />
		<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php if(isset($approve)) {  printf ( __( 'Approve the reservation' , 'easyReservations' ));  }  if(isset($delete)) {  printf ( __( 'Reject the reservation' , 'easyReservations' ));  } ?><input type="submit" onclick="document.getElementById('reservation_approve').submit(); return false;"  class="easybutton button-primary" value="<?php if(isset($approve)) echo __( 'Approve' , 'easyReservations' ); else echo __( 'Reject' , 'easyReservations' );?>" style="float:right"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td nowrap><?php echo easyreservations_reservation_info_box($res, $delorapptext, $res->status); ?></td>
				</tr>
				<?php if(isset($approve)){ ?>
					<tr>
						<td><?php printf ( __( 'Resource' , 'easyReservations' ));?>: <?php echo __($resource_name);?> # <span id="the_room_exactly"></span>
          </tr>
				<?php } do_action('easy-mail-add-input'); ?>
				<tr>
					<td>
						<p><input type="checkbox" name="sendthemail" checked><small> <?php printf ( __( 'Send mail to guest' , 'easyReservations' ));  ?></small> <input type="checkbox" name="hasbeenpayed"><small>  <?php printf ( __( 'Has been paid' , 'easyReservations' ));  ?></small></p>
						<p><?php printf ( __( 'To' , 'easyReservations' ));?> <?php if(isset($approve)) { printf ( __( 'Approve' , 'easyReservations' )); } if(isset($delete)) printf ( __( 'Reject' , 'easyReservations' ));?> <?php printf ( __( 'the reservation, write a message and press send' , 'easyReservations' ));?> &amp; <?php if(isset($approve)) echo "Approve"; if(isset($delete)) echo "reject"; ?>. <?php printf ( __( 'The Guest will recieve that message in an email' , 'easyReservations' ));?>.</p>
						<p class="label"><strong>Text:</strong></p>
						<textarea cols="60" rows="4" name="approve_message" class="er-mail-textarea" width="100px"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		
	</form><?php if(isset($approve)) { if($res->resourcenumber < 1) $ex = 1; else $ex = $res->resourcenumber;?><script>get_the_select(<?php echo $ex; ?>, <?php echo $res->resource; ?>);<?php do_action('easy-approve-script'); ?></script><?php } ?>
<?php  }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + SEND MAIL - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($sendmail)) {
	?><form method="post" action=""  id="reservation_sendmail" name="reservation_sendmail">
		<input type="hidden" name="thesendmail" value="thesendmail"/>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php echo __( 'Send mail to guest' , 'easyReservations' ); ?><input type="submit" onclick="document.getElementById('reservation_sendmail').submit(); return false;" class="easybutton button-primary" value="<?php echo __( 'Send' , 'easyReservations' ); ?>" style="float:right"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td nowrap><?php echo easyreservations_reservation_info_box($res, 'sendmail', $res->status); ?></td>
				</tr>
				<?php do_action('easy-mail-add-input'); ?>
				<tr>
					<td>
						<textarea cols="60" rows="4" name="approve_message" class="er-mail-textarea" width="100px"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<p style="float:right"></p>
	</form>
<?php }
	if(isset($approve) || isset($delete) || isset($view) || isset($sendmail)) echo '</td></tr></table>';
}

function easyreservations_restrict_input_dash(){
	easyreservations_generate_restrict(array(array('#customPamount,input[name^="custom_price"]', true), array('input[name="priceset"],input[name="EDITwaspaid"],input[name="ccnumber"]', false)));
}?>