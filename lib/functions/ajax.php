<?php
function easyreservations_send_calendar_callback(){
	global $reservations_settings;
	check_ajax_referer( 'easy-calendar', 'security' );
	$atts = (array) $_POST['atts'];
	$pers = 1; $child = 0; $resev = 0; $last = null;
	$rand = $atts['id'];
	if(isset($_POST['persons'])) $pers = $_POST['persons'];
	if(isset($_POST['childs'])) $child = $_POST['childs'];
	if(isset($_POST['reservated'])) $resev = $_POST['reservated'];
	if(isset($reservations_settings['mergeres'])){
		if(is_array($reservations_settings['mergeres']) && isset($reservations_settings['mergeres']['merge']) && $reservations_settings['mergeres']['merge'] > 0) $room_count = $reservations_settings['mergeres']['merge'];
		elseif(is_numeric($reservations_settings['mergeres']) && $reservations_settings['mergeres'] > 0) $room_count  = $reservations_settings['mergeres'];
	}
	if(!isset($room_count)){
		$room_count = get_post_meta($_POST['room'], 'roomcount', true);
		if(is_array($room_count)){
			$room_count = $room_count[0];
		}
	}
	$month_names = easyreservations_get_date_name(1);
	$day_names = easyreservations_get_date_name(0,2);
	if($atts['req'] == 1) $requirements = get_post_meta($_POST['room'], 'easy-resource-req', TRUE);
	if(isset($_POST['where']) && $_POST['where'] == "widget") $where = 'widget';
	else $where = 'shortcode';
	$divider = 1;
	$monthes = 1;

	if(isset($atts['monthes']) && $where == 'shortcode' && preg_match('/^[0-9]+x{1}[0-9]+$/i', $atts['monthes'])){
		$explode_monthes = explode('x', $atts['monthes']);
		$monthes = $explode_monthes[0] * $explode_monthes[1];
		$divider = $explode_monthes[0];
	}

	if(function_exists('easyreservations_generate_multical') && $where == 'shortcode' && $monthes != 1) $timenows = easyreservations_generate_multical($_POST['date'] + $atts['date'], $monthes);
	else $timenows=array(strtotime("+".($_POST['date']+$atts['date'])." month", strtotime(date("01.m.Y", time()) )));

	if(!isset($timenows[1])) $month = $month_names[date("n", $timenows[0])-1].' '.date("Y", $timenows[0]);
	else {
		$anf =  $timenows[0];
		$end = $timenows[count($timenows)-1];
		if(date("Y", $anf) == date("Y", $end) ){
			$month=$month_names[date("n", $anf)-1].' - '.$month_names[date("n", $end)-1].' '.date("Y", $anf);
		} else {
			$month=$month_names[date("n", $anf)-1].' '.date("y", $anf).' - '.$month_names[date("n", $end)-1].' '.date("y", $end);
		}
	}

	echo '<table class="calendar-table" cellpadding="0" cellspacing="0">';
	echo '<thead><tr class="calendarheader">';
	echo '<th class="calendar-header-month-prev" onClick="easyCalendars['.$rand.'].change(\'date\', \''.($_POST['date']-$atts['interval']).'\');">'.__('prev', 'easyReservations').'</th>';
	echo '<th colspan="5" class="calendar-header-show-month">'.$month.'</th>';
	echo '<th class="calendar-header-month-next" onClick="easyCalendars['.$rand.'].change(\'date\', \''.($_POST['date']+$atts['interval']).'\');">'.__('next', 'easyReservations').'</th>';
	echo '</tr></thead>';
	echo '<tbody style="text-align:center;white-space:nowrap;padding:0px">';
	echo '<tr><td colspan="7" style="white-space:nowrap;padding:0px;margin:0px;border:0px">';
	if(count($timenows) > 1){
		$atts['width'] = ((float) $atts['width']) / $divider;
		$percent = 100 / $divider;
	} else $percent = 100;
	$month_count=0;
	foreach($timenows as $timenow){
		$month_count++;
		$diff=1;
		$setet=0;
		$yearnow=date("Y", $timenow);
		$monthnow=date("m", $timenow);
		$key = $yearnow.$monthnow;
		if(function_exists('cal_days_in_month')) $num = cal_days_in_month(CAL_GREGORIAN, $monthnow, $yearnow); // 31
		else $num = date("d", mktime(0, 0, 0, $monthnow +1, 0, $yearnow));

		if($monthnow-1 <= 0){
			$monthnowFix=13;
			$yearnowFix=$yearnow-1;
		} else {
			$monthnowFix=$monthnow;
			$yearnowFix=$yearnow;
		}

		if(function_exists('cal_days_in_month')) $num2 = cal_days_in_month(CAL_GREGORIAN, $monthnowFix-1, $yearnowFix); // 31
		else $num2 = date("d", mktime(0, 0, 0, $monthnowFix, 0, $yearnowFix));
		//if(count($timenows) > 1 && $divider % 2 != 0) $thewidth = ($percent).'%';
		$thewidth = $percent.'%';
		$float = $month_count % $divider == 0 ? '' : 'float:left';

		echo '<table class="calendar-direct-table '.str_replace(':left', '', $float).'" style="width:'.$thewidth.';margin:0px;'.$float.'">';
		echo '<thead>';
		if($atts['header'] == 1) echo '<tr><th class="calendar-header-month" colspan="7">'.$month_names[date("n", $timenow)-1].'</th></tr>';
		echo '<tr>';
		echo '<th class="calendar-header-cell">'.$day_names[0].'</th>';
		echo '<th class="calendar-header-cell">'.$day_names[1].'</th>';
		echo '<th class="calendar-header-cell">'.$day_names[2].'</th>';
		echo '<th class="calendar-header-cell">'.$day_names[3].'</th>';
		echo '<th class="calendar-header-cell">'.$day_names[4].'</th>';
		echo '<th class="calendar-header-cell">'.$day_names[5].'</th>';
		echo '<th class="calendar-header-cell">'.$day_names[6].'</th>';
		echo '</tr></thead>';
		echo '<tbody style="text-align:center;padding;0px;margin:0px">';
		$row_count=0;
		while($diff <= $num){
			$dateofeachday=strtotime($diff.'.'.$monthnow.'.'.$yearnow);
			$dayindex=date("N", $dateofeachday);
			if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28 || $setet==35){ echo '<tr style="text-align:center">'; $row_count++; }
			if($setet==0 && $diff==1 && $dayindex != "1"){
				echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2).'</span></td>'; $setet++;
				if($setet==1 && $diff==1 && $dayindex != "2"){
					echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
					if($setet==2 && $diff==1 && $dayindex != "3"){
						echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
						if($setet==3 && $diff==1 && $dayindex != "4"){
							echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
							if($setet==4 && $diff==1 && $dayindex != "5"){
								echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
								if($setet==5 && $diff==1 && $dayindex != "6"){
									echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
									if($setet==6 && $diff==1 && $dayindex != "7"){
										echo '<td class="calendar-cell calendar-cell-last"><span>'.($num2-$dayindex+2+$setet).'</span></td>'; $setet++;
									}
								}
							}
						}
					}
				}
			}

			$res = new Reservation(false, array('email' => 'mail@test.com', 'arrival' => $dateofeachday+43200, 'departure' =>  $dateofeachday, 'resource' => (int) $_POST['room'], 'adults' => $pers, 'childs' => $child,'reservated' => time()-($resev*86400)), false);
			try {
				$res->admin = false;
				if($atts['price'] > 0 && is_numeric($atts['price'])){
					$res->Calculate(false, isset($atts['notax']) && $atts['notax'] > 0 ? false : true);
					if($atts['price'] == 1 || $atts['price'] == 2){ $explode = explode('.', $res->price); $res->price = $explode[0]; }
					if($atts['price'] == 1) $formated_price = $res->price.'&'.RESERVATIONS_CURRENCY.';';
					elseif($atts['price'] == 2) $formated_price = $res->price;
					elseif($atts['price'] == 3) $formated_price = easyreservations_format_money($res->price, 1);
					elseif($atts['price'] == 4) $formated_price = easyreservations_format_money($res->price);
					elseif($atts['price'] == 5) $formated_price = '&'.RESERVATIONS_CURRENCY.';'.' '.$res->price;
					$final_price = '<span class="calendar-cell-price">'.$formated_price.'</b>';
				} else $final_price = '';

				$avail = $res->checkAvailability(5);
				if($atts['price'] == "avail") $final_price = '<span class="calendar-cell-price">'.($room_count-$avail[0]).'</b>';

				if($avail[0] >= $room_count) $background_td = " calendar-cell-full";
				elseif($avail[0] > 0) $background_td = " calendar-cell-occupied";
				else $background_td = " calendar-cell-empty";

				$new = $background_td;
				if($last == null){
					$res->arrival -= 86400;
					$lastavail = $res->checkAvailability(5);
					if($lastavail[0] >= $room_count) $last = " calendar-cell-full";
					elseif($lastavail[0] > 0) $last = " calendar-cell-occupied";
					else $last = " calendar-cell-empty";
				}
				if($last !== $new && ($avail[1] > 0) && $atts['half'] == 1){
					$background_td.= $last.'2';
					$background_td.=" calendar-cell-halfend";
				}
				$last = $new;

				if(date("d.m.Y", $dateofeachday) == date("d.m.Y", time())) $background_td.=" today";

				if(isset($atts['style']) && $atts['style'] == 3 && $diff < 10) $show = '0'.$diff;
				else $show = $diff;

				//onclick="easyreservations_click_calendar(this,\''.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'\', \''.$rand.'\', \''.$key.'\');"
				if($dateofeachday > time()-86401 && $atts['select'] > 0) $onclick = 'date="'.date(RESERVATIONS_DATE_FORMAT, $dateofeachday).'"';
				else $onclick ='style="cursor:default"';
				if($atts['req'] == 1 && $requirements && ((isset($requirements['start-on']) && is_array($requirements['start-on']) && $requirements['start-on'] != 0) || (isset($requirements['end-on']) && is_array($requirements['end-on']) && $requirements['end-on'] != 0))){
					$das = true;
					if(isset($requirements['start-on']) && is_array($requirements['start-on']) && $requirements['start-on'] != 0 && !in_array(date("N", $dateofeachday), $requirements['start-on'])){
						$background_td.= " reqstartdisabled reqdisabled";
						$das = false;
					}
					if(isset($requirements['end-on']) && is_array($requirements['end-on']) && $requirements['end-on'] != 0 && !in_array(date("N", $dateofeachday), $requirements['end-on'])){
						$background_td.= " reqenddisabled";
						$das = false;
					}
					if($das) $background_td.= " notreqdisabled";
				}
				echo '<td class="calendar-cell'.$background_td.'" '.$onclick.' id="easy-cal-'.$rand.'-'.$diff.'-'.$key.'" axis="'.$diff.'">'.$show.''.$final_price.'</td>'; $setet++; $diff++;
				if($setet==0 || $setet==7 || $setet==14 || $setet==21 || $setet==28) echo '</tr>';
				$res->destroy();
			} catch(Exception $e){
				return false;
			}
		}

		if(!empty($final_price)) $final_price =  '<span class="calendar-cell-price">&nbsp;</b>';

		if(($diff-1==$num && $setet/7 != $row_count) || $setet < 36){
			if($divider == 1) $calc=($row_count*7)-($setet+1);
			else $calc=42-($setet+1);
			for($countits=0; $countits < $calc+1; $countits++){
				$fix = $countits==0 ? ' calendar-cell-lastfixer' : '';
				if($setet+$countits==35){ echo '</tr><tr>'; $setet++; }
				echo '<td class="calendar-cell calendar-cell-last'.$fix.'"><div>&nbsp;</div><span>'.($countits+1).'</span>'.$final_price.'</td>';
			}
		}
		echo '</tr></tbody></table>';
	}

	echo '</td></tr></tbody></table>';
	exit;
}

/**
 *	Callback for the price calculation (here it fakes a reservation and send it to calculation)
 *
 */

function easyreservations_send_form_callback(){
	if(isset($_POST['delete'])){
		if(!empty($_POST['delete'])){
			if(isset($_POST['cancel'])){
				$explode = array($_POST['cancel']);
			} else {
				$explode = explode(',', $_POST['delete']);
				unset($explode[count($explode)]);
			}

			foreach($explode as $id){
				if(is_numeric($id)){
					$res = new Reservation((int) $id);
					$res->deleteReservation();
				}
			}
		}
	} else {
		easyreservations_load_resources(true);
		if (!wp_verify_nonce($_POST['easynonce'], 'easy-user-add' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
		global $the_rooms_intervals_array, $current_user;
		$error = '';

		if(isset($_POST['ids']) && !empty($_POST['ids'])) $ids = (array) $_POST['ids'];
		else $ids = array();
		$id_to_return = false;
		$price_to_return = false;

		if(isset($_POST['thename'])){
			$arrival = isset($_POST['from']) ? EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['from'].' 00:00:00')->getTimestamp() : time();
			$name_form = isset($_POST['thename']) ? $_POST['thename'] : '';
			$persons = isset($_POST['persons']) ? $_POST['persons'] : 1;
			$email = isset($_POST['email']) ? $_POST['email'] : '';
			$children = isset($_POST['childs']) ? $_POST['childs'] : 0;
			$country = isset($_POST['country']) ? $_POST['country'] : '';
			$room = isset($_POST['easyroom']) ? $_POST['easyroom'] : false;

			$arrivalplus = 0;
			if(isset($_POST['date-from-hour'])) $arrivalplus += (int) $_POST['date-from-hour'] * 60;
			else $arrivalplus += 12*60;
			if(isset($_POST['date-from-min'])) $arrivalplus += (int) $_POST['date-from-min'];
			if($arrivalplus > 0) $arrivalplus = $arrivalplus * 60;
			$departureplus = 0;
			if(isset($_POST['date-to-hour'])) $departureplus += (int) $_POST['date-to-hour'] * 60;
			if(isset($_POST['date-to-min'])) $departureplus += (int) $_POST['date-to-min'];
			if($departureplus > 0) $departureplus = $departureplus*60;

			if(isset($_POST['to'])) $departure = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['to'].' 00:00:00')->getTimestamp();
			else {
				$departure = $arrival;
				if(isset($_POST['nights'])){
					$departure = $arrival+((int) $_POST['nights'] * $the_rooms_intervals_array[$_POST['easyroom']]);
					if(!isset($_POST['date-to-hour'])) $departure += $arrivalplus;
				}	elseif($departureplus == 0){
					$departure += $arrivalplus + $the_rooms_intervals_array[$_POST['easyroom']];
				}
			}
			$arrival += $arrivalplus;
			$departure += $departureplus;

			if(isset($_POST['captcha']) && !empty($_POST['captcha'])){
				require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
				$captcha_instance = new easy_ReallySimpleCaptcha();
				$correct = $captcha_instance->check($_POST['captcha_prefix'], $_POST['captcha']);
				$captcha_instance->cleanup(120); // delete all >1h old captchas image & .php file; is the submit a right place for this or should it be in admin?
				if($correct != 1)	$error.=  '<li><label for="easy-form-captcha">'.__( 'Please enter the correct captcha' , 'easyReservations' ).'</label></li>';
			}

			$current_user = wp_get_current_user();
			$array = array(
				'name' => $name_form,
				'email' => $email,
				'arrival' => $arrival,
				'departure' => $departure,
				'resource' => (int) $room,
				'resourcenumber' => 0,
				'country' => $country,
				'adults' => $persons,
				'childs' => $children,
				'reservated' => date('Y-m-d H:i:s', time()),
				'status' => '',
				'user' => $current_user->ID
			);

			$custom = get_custom_submit($array, $error);
			$array['custom'] = maybe_unserialize($custom[0]);
			$array['prices'] = maybe_unserialize($custom[1]);
			$error = $custom[2];

			if(isset($_POST['edit'])){
				$res = new Reservation((int) $_POST['edit'], $array, false);
				try {
					$res->admin = false;
					$theID = $res->editReservation();
					$res->Calculate();
					if(!$theID) echo json_encode(array($res->id, round($res->price,2)));
					else echo 'error';
				} catch(Exception $e){
					echo '<li><label>'.$e->getMessage().'</label></li>';
					exit;
				}
			} else {
				$res = new Reservation(false, $array, false);
				try {
					$res->admin = false;
					$res->coupon = false;
					if(isset($_POST['coupon'])){
						$res->coupon = $_POST['coupon'];
						$res = apply_filters('easy-add-res-ajax', $res);
					}
					$res->fake = false;
					$theID = $res->addReservation(false,false, $ids);
					if($theID){
						foreach($theID as $key => $terror){
							if($key%2==0) $error.=  '<li><label for="'.$terror.'">';
							else $error .= $terror.'</label></li>';
						}
						echo $error;
						exit;
					}
					$ids[] = $res->id;
					$id_to_return = $res->id;
									$res->Calculate();

					$price_to_return = round($res->price,2);
				} catch(Exception $e){
					echo '<li><label>'.$e->getMessage().'</label></li>';
					exit;
				}
			}
		}

		if(!empty($ids)){
			if(isset($_POST['submit'])){
				$prices = 0;
				$final_form = '';
				$atts = (array) $_POST['atts'];
				foreach($ids as $id){
					$new = new Reservation((int) $id);
					$new->Calculate();
					$new->sendMail( 'reservations_email_to_admin', false);
					$new->sendMail( 'reservations_email_to_user', $new->email);
					do_action('reservation_successful_guest', $new);
					$prices += $new->price;
				}
				$payment = $ids;
				$prices = round($prices,2);

				$final_form.= '<div class="easy_form_success" id="easy_form_success">';
				if(!empty($atts['submit'])) $final_form.= '<b class="easy_submit">'.$atts['submit'].'!</b>';
				if(!empty($atts['subsubmit'])) $final_form.= '<span class="easy_subsubmit">'.$atts['subsubmit'].'</span>';
				if($atts['price'] == 1) $final_form.= '<span class="easy_show_price_submit">'.__('Price','easyReservations').': <b>'.easyreservations_format_money($prices, 1).'</b></span>';
				if(function_exists('easyreservation_generate_payment_form') && $atts['payment'] > 0){
					$final_form .= easyreservation_generate_payment_form($payment, $prices, ($atts['payment'] == 2) ? true : false, (is_numeric($atts['discount']) && $atts['discount'] < 100) ? $atts['discount'] : false);
				}
				$final_form.='</div>';
				$script = get_option('easyreservations_successful_script');
				if($script && !empty($script)) $final_form.= '<script type="text/javascript">'.stripslashes($script).'</script>';
				
				echo json_encode(array($id_to_return, $price_to_return, $final_form));
			} else {
				echo json_encode(array($id_to_return, $price_to_return, 2));
			}
		} else {
			echo '<li><label>'.__( 'Add reservation before submit' , 'easyReservations' ).'</label></li>';
		}
	}
	exit;
}

/**
 *	Callback for the ajax validation (here it checks the values)
 *
 */

function easyreservations_send_validate_callback(){
	check_ajax_referer( 'easy-price', 'security' );
	easyreservations_load_resources(true);
	global $the_rooms_intervals_array;
	$mode = $_POST['mode'];
	$error = array();

	$val_room = $_POST['room'];
	if(!empty($_POST['from'])) $val_from = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['from'].' 00:00:00');
	else $val_from = false;
	if(!$val_from instanceof DateTime){
		header( "Content-Type: application/json" );
		$error[] = 'easy-form-from';
		$error[] =  __( 'Wrong date format' , 'easyReservations' );
		echo json_encode($error);
		exit;
	}
	$real_from =	$val_from->getTimestamp() + (int) $_POST['fromplus'];
	if($_POST['toplus'] == -1) $_POST['toplus'] = 0;
	if(!empty($_POST['to'])){
		$val_to = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['to'].' 00:00:00');
		if(!$val_to instanceof DateTime){
			$error[] = 'easy-form-to';
			$error[] =  __( 'Wrong date format' , 'easyReservations' );
			echo json_encode($error);
			exit;
		}
		$val_to = $val_to->getTimestamp() + (int) $_POST['toplus'];
	} elseif($_POST['nights'] !== '') {
		$val_to = $real_from + ((int)$_POST['nights'] * $the_rooms_intervals_array[$val_room])  + (int) $_POST['toplus'];
	} else {
		if((int) $_POST['toplus'] > 0)
			$val_to = $val_from->getTimestamp() + (int) $_POST['toplus'] + $the_rooms_intervals_array[$val_room];
		else
			$val_to = $real_from + $the_rooms_intervals_array[$val_room];
	}

	if(isset($_POST['id']) && !empty($_POST['id'])) $id = $_POST['id'];
	else $id = false;
	try {
		$res = new Reservation($id, array('name' =>  $_POST['thename'], 'email' => $_POST['email'], 'arrival' => $real_from, 'departure' => $val_to,'resource' => (int) $_POST['room'], 'adults' => (int) $_POST['persons'], 'childs' => (int) $_POST['childs'],'reservated' => time(),'status' => ''), false);
		$res->admin = false;
		if(isset($_POST['ids'])) $ids = $_POST['ids'];
		else $ids = false;
		$error = $res->Validate($mode, 1, false, $ids);
	} catch(Exception $e){
		$error[] = '';
		$error[] = $e->getMessage();
	}

	if($mode == 'send'){
		$explode_customs = explode(',', substr($_POST['customs'],0,-1));
		foreach($explode_customs as $cstm){
			if(!empty($cstm)){
				$error[] = $cstm;
				$error[] =  sprintf(__( '%1$s is required' , 'easyReservations' ), ucfirst(str_replace('easy-custom-req-', '', $cstm)));
			}
		}
		if(!empty($_POST['new_custom'])){
			$explode_customs = explode(',', substr($_POST['new_custom'],0,-1));
			$custom_fields = get_option('reservations_custom_fields');

			foreach($explode_customs as $c_id){
				$c_id = str_replace('easy-new-custom-', '', $c_id);
				if(isset($custom_fields['fields'][$c_id]) && $custom_fields['fields'][$c_id]['required']){
					$error[] = 'easy-new-custom-'.$c_id;
					$error[] =  sprintf(__( '%1$s is required' , 'easyReservations' ), $custom_fields['fields'][$c_id]['title']);
				}
			}
		}
		if($_POST['captcha'] !== 'x!'){
			if(empty($_POST['captcha'])){
				$error[] = 'easy-form-captcha';
				$error[] =  __( 'Captcha is required' , 'easyReservations' );
			} elseif(strlen($_POST['captcha']) != 4){
				$error[] = 'easy-form-captcha';
				$error[] =  __( 'Enter correct captcha' , 'easyReservations' );
			} else {
				require_once(WP_PLUGIN_DIR.'/easyreservations/lib/captcha/captcha.php');
				$captcha_instance = new easy_ReallySimpleCaptcha();
				$correct = $captcha_instance->check($_POST['captcha_prefix'], $_POST['captcha']);
				$captcha_instance->cleanup();
				if($correct != 1){
					$error[] = 'easy-form-captcha';
					$error[] =  __( 'Enter correct captcha' , 'easyReservations' );
				}
			}
		}
	}
	
	//MODIF AWI
	if($val_from instanceof DateTime){
		//Vérification des 48h
		$diff = $real_from - time();
		$nbHeures = $diff / 3600;
		if ($nbHeures < 24){
			header( "Content-Type: application/json" );
			$error[] = 'easy-form-from';
			$error[] =  __( 'Reservation must be made 24h before' , 'easyReservations' );
			echo json_encode($error);
			exit;
		}
	}
	//FIN MODIF AWI

	if( $error != '' ){
		header( "Content-Type: application/json" );
		echo json_encode($error);
	} else echo true;

	exit;
}

/**
 *	Callback for the price calculation (here it fakes a reservation and send it to calculation)
 *
 */

function easyreservations_send_price_callback(){
	easyreservations_load_resources(true);
	check_ajax_referer( 'easy-price', 'security' );
	global $the_rooms_intervals_array;
	if(!isset($_POST['from']) || empty($_POST['from'])) $stop = 1;
	$room = $_POST['room'];
	$val_from = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['from'].' 00:00:00');
	if(!$val_from instanceof DateTime) $stop = 1;
	$real_from = $val_from->getTimestamp() + (int) $_POST['fromplus'] ;
	if($_POST['toplus'] == -1) $_POST['toplus'] = 0;
	if(!empty($_POST['to'])){
		$val_to = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['to'].' 00:00:00');
		$val_to = $val_to->getTimestamp() + (int) $_POST['toplus'] ;
	} elseif($_POST['nights'] !== 0){
		$val_to = $val_from->getTimestamp() + ($_POST['nights'] * $the_rooms_intervals_array[$room])  + (int) $_POST['toplus'];
	} else {
		$val_to = $val_from->getTimestamp() + (int) $_POST['toplus'] + $the_rooms_intervals_array[$room];
	}
	if(isset($stop)){
		echo json_encode(array(easyreservations_format_money(0,1), 0));
		exit;
	}

	$email = isset($_POST['email']) ? $_POST['email']: 'test@test.de';
	$coupon = isset($_POST['coupon']) ? $_POST['coupon']: '';
	$customp = isset($_POST['customp']) ? str_replace("!", "&", $_POST['customp']): '';
	$reserved = isset($_POST['reserved']) && !empty($_POST['reserved']) ?  $_POST['reserved'] : time();
	$persons = isset($_POST['persons']) && !empty($_POST['persons']) ?  $_POST['persons'] : 1;
	$children = isset($_POST['childs']) && !empty($_POST['childs']) ?  $_POST['childs'] : 0;

	$res_array = array('name' => 'abv', 'email' => $email, 'arrival' => $real_from,'departure' => $val_to,'resource' => (int) $room, 'adults' => (int) $persons, 'childs' => $children, 'status' => '', 'prices' => (float) $customp, 'coupon' => $coupon, 'reservated' => $reserved);
	if(!empty($_POST['new_custom'])) $res_array['new_custom'] = $_POST['new_custom'];
	$res = new Reservation(false, $res_array, false);
	try {
		$res->Calculate();
		if(isset($_POST['priceper']) && !empty($_POST['priceper'])){
			if($_POST['priceper'] == 'unit'){
				$res->price = round($res->price/$res->times,2);
			} elseif($_POST['priceper'] == 'person' || $_POST['priceper'] == 'pers'){
				$res->price = round($res->price/($res->adults+$res->childs),2);
			} elseif($_POST['priceper'] == 'both'){
				$res->price = round($res->price/($res->adults+$res->childs)/$res->times,2);
			}
		}
		echo json_encode(array(easyreservations_format_money($res->price,1), round($res->price,2)));
	} catch(Exception $e){
		echo 'Error:'. $e->getMessage();
	}

	exit;
}

add_action('wp_ajax_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');
add_action('wp_ajax_nopriv_easyreservations_send_calendar', 'easyreservations_send_calendar_callback');

add_action('wp_ajax_easyreservations_send_form', 'easyreservations_send_form_callback');
add_action('wp_ajax_nopriv_easyreservations_send_form', 'easyreservations_send_form_callback');

add_action('wp_ajax_easyreservations_send_price', 'easyreservations_send_price_callback');
add_action('wp_ajax_nopriv_easyreservations_send_price', 'easyreservations_send_price_callback');

add_action('wp_ajax_easyreservations_send_validate', 'easyreservations_send_validate_callback');
add_action('wp_ajax_nopriv_easyreservations_send_validate', 'easyreservations_send_validate_callback');



/**
 *
 *	Table ajax callback
 *
 */

add_action('wp_ajax_easyreservations_send_table', 'easyreservations_send_table_callback');

function easyreservations_send_table_callback() {
	easyreservations_load_resources();
	global $wpdb,$the_rooms_array;
	check_ajax_referer( 'easy-table', 'security' );
	$zeichen = "AND departure > NOW() ";

	if(isset($_POST['typ'])) $typ=$_POST['typ'];
	else $typ = 'active';
	$custom_fields = get_option('reservations_custom_fields');

	$orderby = isset($_POST['orderby']) && !empty($_POST['orderby']) ? $_POST['orderby'] : '';
	$order = isset($_POST['order']) && !empty($_POST['order']) ? $_POST['order'] : '';
	$search = isset($_POST['search']) && !empty($_POST['search']) ? $_POST['search'] : '';
	$per_page = isset($_POST['perpage']) && !empty($_POST['perpage']) ? $_POST['perpage'] : get_option("reservations_on_page");
	$main_options = get_option("reservations_main_options");

	$table_options =  $main_options['table'];
	$regular_guest_explodes = explode(",", str_replace(" ", "", get_option("reservations_regular_guests")));
	foreach( $regular_guest_explodes as $regular_guest) $regular_guest_array[]=$regular_guest;

	$selectors = '';
	if(!isset($table_options['table_fav']) || $table_options['table_fav'] == 1){
		global $current_user;
		$current_user = wp_get_current_user();
		$user = $current_user->ID;
		$favourite = get_user_meta($user, 'reservations-fav', true);
		if($favourite && !empty($favourite) && is_array($favourite)) $favourite_sql = 'id in('.implode(",", $favourite).')';
		else $favourite = array();
	}

	if($_POST['monthselector'] > 0){
		$month_selector = date("Y-m-d", strtotime($_POST['monthselector']));
		$selectors .= "AND MONTH('$month_selector') BETWEEN MONTH(arrival) AND MONTH(departure) ";
	}
	if($_POST['roomselector'] > 0){
		$room_selector = (int) $_POST['roomselector'];
		$selectors .= "AND room=$room_selector ";
	}
	if(isset($_POST['statusselector'] ) && !is_numeric($_POST['statusselector'])){
		$status_selector=$_POST['statusselector'];
		$selectors.="AND approve='$status_selector' ";
	}

	if($_POST['searchdate'] != ''){
		$search_date = $_POST['searchdate'];
		$search_date_mysql = EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $search_date.' 00:00:00')->format('Y-m-d');
		$selectors .= "AND ('$search_date_mysql' BETWEEN arrival AND departure OR DATE('$search_date_mysql') = DATE(arrival) OR DATE('$search_date_mysql') = DATE(departure)) ";
	}
	$rooms_sql  = ''; $permission_selectors = '';
	if(!current_user_can('manage_options')) $rooms_sql = easyreservations_get_allowed_rooms_mysql();

	if(!empty($rooms_sql)) $permission_selectors.= ' AND room in '.$rooms_sql;
	$orders = 'ASC';
	$orders_by = 'arrival';
	$search_str = '';

	if(!empty($search)){
		$explus = explode('+', $search);
		$exor = explode('|', $search);
		$st = 0;
		if(isset($explus[1])){
			$searches = $explus;
			$search_str .= 'AND (';
			$search_sign = 'AND';
		} elseif(isset($exor[1])){
			$searches = $exor;
			$search_str .= 'AND (';
			$search_sign = 'OR';
		} else {
			$search_str .= 'AND ';
			$searches = array($search);
		}

		foreach($searches as $search_res){
			if($st > 0)
				$search_str .= ' '.(isset($search_sign) ? $search_sign : '').' ';
			if(preg_match('/^[0-9]+$/i', $search_res))
				$search_str .= " id = $search_res";
			else {
				$room_ids = '';
				foreach($the_rooms_array as $room){
					if(strpos(strtoupper($room->post_title), strtoupper($search_res)) !== false) $room_ids .= $room->ID.', ';
				}
				$roomsearch = !empty($room_ids) ? ' OR room in ('.substr($room_ids,0,-2).')' : '';
				$search_str .= "(name like %s OR email like %s OR custom like %s$roomsearch)";
			}
			$st++;

		}
		if(isset($search_sign)) $search_str .= ')';
	}

	$esc_like = '%' . $wpdb->esc_like($search) . '%';
	$items1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' $zeichen $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	$items2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' $zeichen $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	$items3 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' $zeichen $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	$items4 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure < NOW() $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	$items5 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='del' $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	$items7 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND NOW() BETWEEN arrival AND departure $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	$items6 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE 1=1 $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	if(isset($favourite_sql)) $countfav = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $favourite_sql $selectors $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
	else $favourite_sql = ' 1 = 1 ';
	if(!isset($typ) || $typ=='active' || $typ=='') { $type="approve='yes'"; $items=$items1; $orders="ASC";  $zeichen = "AND departure > NOW() "; } // If type is actice
	elseif($typ=="current") { $type="approve='yes'"; $items=$items7; $orders="ASC"; $zeichen ="AND NOW() BETWEEN arrival AND departure "; } // If type is current
	elseif($typ=="pending") { $type="approve=''"; $items=$items3; $orders_by='id'; $orders="DESC"; } // If type is pending
	elseif($typ=="deleted") { $type="approve='no'"; $items=$items2; } // If type is rejected
	elseif($typ=="old") { $type="approve='yes'"; $items=$items4; $zeichen="AND departure < DATE(NOW())";  } // If type is old
	elseif($typ=="trash") { $type="approve='del'"; $items=$items5; $zeichen=''; } // If type is trash
	elseif($typ=="all") { $type="1=1"; $items=$items6; $zeichen=''; } // If type is all
	elseif($typ=="favourite") { $type=$favourite_sql; $items=$countfav; $zeichen=''; } // If type is all
	if($order=="ASC") $orders="ASC";
	elseif($order=="DESC") $orders="DESC";
	if($orderby=="date") $orders_by="arrival";
	if($orderby=="persons") $orders_by="number+(childs*0.5)";
	if($orderby=="status") $orders_by="approve";
	elseif($orderby=="name") $orders_by="name";
	elseif($orderby=="room"){
		$orders_by = "room";
		$orders.=", roomnumber ".$orders;
	}
	elseif($orderby=="reservated") $orders_by="reservated";
	if(empty($orderby) && $typ=="pending") { $orders_by="id"; $orders="DESC"; }
	if(empty($orderby) && $typ=="old") { $orders_by="arrival"; $orders="DESC"; }
	if(empty($orderby) && $typ=="all") { $orders_by="arrival"; $orders="DESC"; }
	if(isset($month_selector) || isset($room_selector) || isset($status_selector)){
		$variableitems = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE $type $selectors $zeichen $search_str $permission_selectors", $esc_like, $esc_like, $esc_like));
		$items=$variableitems;
	}
	if(!isset($room_selector)) $room_selector = '';
	if(!isset($status_selector)) $status_selector=0;
	$pagei = 1;
	if(isset($items) && $items > 0) {
		$p = new easy_pagination;
		$p->items($items);
		$p->limit($per_page); // Limit entries per page
		$p->target($typ);
		$pagination = 0;
		$p->currentPage($pagination); // Gets and validates the current page
		$p->calculate(); // Calculates what to show
		$p->first(1);
		$p->last(1);
		$p->numbers(0);
		$p->field(array(1, __('of', 'easyReservations')));
		$p->parameterName('paging');
		$p->adjacents(1); //No. of page away from the current page
		if(isset($_POST['paging'])) $pagei = $_POST['paging']; else $pagei = 1;
		$p->page = $pagei;
		$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
	} else $limit = 'LIMIT 0'; ?>
<input type="hidden" id="easy-table-order" value="<?php echo $order;?>">
<input type="hidden" id="easy-table-orderby" value="<?php echo $orderby;?>">
<table style="width:99%;">
  <tr> <!-- Type Chooser //-->
    <td style="white-space:nowrap;width:auto" class="no-select" nowrap>
      <ul id="easy-table-navi" class="subsubsub" style="float:left;white-space:nowrap">
        <li><a onclick="easyreservation_send_table('active', 1)" <?php if(!isset($typ) || (isset($typ) && $typ == 'active')) echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Upcoming' , 'easyReservations' ));?><span class="count"> (<?php echo $items1; ?>)</span></a> |</li>
        <li><a onclick="easyreservation_send_table('current', 1)" <?php if(isset($typ) && $typ == 'current') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Current' , 'easyReservations' ));?><span class="count"> (<?php echo $items7; ?>)</span></a> |</li>
        <li><a onclick="easyreservation_send_table('pending', 1)" <?php if(isset($typ) && $typ == 'pending') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Pending' , 'easyReservations' ));?><span class="count"> (<?php echo $items3; ?>)</span></a> |</li>
        <li><a onclick="easyreservation_send_table('deleted', 1)" <?php if(isset($typ) && $typ == 'deleted') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Rejected' , 'easyReservations' ));?><span class="count"> (<?php echo $items2; ?>)</span></a> |</li>
        <li><a onclick="easyreservation_send_table('all', 1)" <?php if(isset($typ) && $typ == 'all') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'All' , 'easyReservations' ));?><span class="count"> (<?php echo $items6; ?>)</span></a> |</li>
        <li><a onclick="easyreservation_send_table('old', 1)" <?php if(isset($typ) && $typ == 'old') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Old' , 'easyReservations' ));?><span class="count"> (<?php echo $items4; ?>)</span></a></li>
				<?php if( $items5 > 0 ){ ?>| <li><a onclick="easyreservation_send_table('trash', <?php echo $pagei; ?>)" <?php if(isset($typ) && $typ == 'trash') echo 'class="current"'; ?> style="cursor:pointer"><?php printf ( __( 'Trash' , 'easyReservations' ));?><span class="count"> (<?php echo $items5; ?>)</span></a></li><?php } ?>
				<?php if( isset($countfav) && $countfav > 0 ){ ?><li>| <a onclick="easyreservation_send_table('favourite', <?php echo $pagei; ?>)" style="cursor:pointer"><img style="vertical-align:text-bottom" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/css/images/star_full<?php if(isset($typ) && $typ == 'favourite') echo '_hover'; ?>.png"><span class="count"> (<span  id="fav-count"><?php echo $countfav; ?></span>)</span></a></li><?php } ?>
      </ul>
    </td>
    <td style="width:22px"><span style="float:left;" id="er-table-loading"></span></td>
    <td style="text-align:center; font-size:12px;" id="idstatusbar" nowrap><!-- Begin of Filter //-->
			<?php if($table_options['table_filter_offer'] == 1){?>
        <select name="statusselector" id="easy-table-statusselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all statuses' , 'easyReservations' ));?></option><option value="yes" <?php selected('yes', $status_selector) ?>><?php printf ( __( 'Approved' , 'easyReservations' ));?></option><option value=" <?php selected('', $status_selector) ?>"><?php printf ( __( 'Pending' , 'easyReservations' ));?></option><option value="no" <?php selected('no', $status_selector) ?> ><?php printf ( __( 'Rejected' , 'easyReservations' ));?></option><option value="del" <?php selected('del', $status_selector) ?>><?php printf ( __( 'Trashed' , 'easyReservations' ));?></option></select>
			<?php } if($table_options['table_filter_month'] == 1){ ?>
        <select name="monthselector"  id="easy-table-monthselector" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'Show all monthes' , 'easyReservations' ));?></option><!-- Filter Months //-->
					<?php
					$posts = "SELECT DISTINCT DATE_FORMAT(arrival, '%Y-%m') AS yearmonth FROM ".$wpdb->prefix ."reservations GROUP BY yearmonth ORDER BY yearmonth ";
					$results = $wpdb->get_results($posts);
					$datenames = easyreservations_get_date_name(1);

					foreach( $results as $result ){
						$dat=$result->yearmonth;
						$zerst = explode("-",$dat);
						$selected = isset($_POST['monthselector']) && $_POST['monthselector'] == $dat ? 'selected="selected"' : '';
						echo '<option value="'.$dat.'" '.$selected.'>'.$datenames[$zerst[1]-1].' '.__($zerst[0]).'</option>';
					} ?>
        </select>
			<?php } if($table_options['table_filter_room'] == 1){ ?>
        <select name="roomselector" id="easy-table-roomselector" class="postform" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"><option value="0"><?php printf ( __( 'View all Resources' , 'easyReservations' ));?></option><?php echo easyreservations_resource_options($room_selector); ?></select>
			<?php } if($table_options['table_filter_days'] == 1){ ?><input size="1px" type="text" id="easy-table-perpage-field" name="perpage" value="<?php echo $per_page; ?>" maxlength="3" onchange="easyreservation_send_table('<?php echo $typ; ?>', 1)"></input>
        <img src=" <?php echo RESERVATIONS_URL; ?>images/list.png" style="vertical-align:text-bottom;cursor:pointer" onclick="easyreservation_send_table('all', 1)">
			<?php } ?>
    </td>
    <td style="width:33%; margin-left: auto; margin-right:0px; text-align:right;" nowrap>
      <img id="easy-table-refreshimg" src="<?php echo RESERVATIONS_URL; ?>images/refresh.png" style="vertical-align:text-bottom" onclick="resetTableValues()">
			<?php if($table_options['table_search'] == 1){ ?>
        <input type="text" onchange="easyreservation_send_table('all', 1)" style="width:77px;text-align:center" id="easy-table-search-date" value="<?php if(isset($search_date)) echo $search_date; ?>">
        <input type="text" onchange="easyreservation_send_table('all', 1)" style="width:130px;" id="easy-table-search-field" name="search" value="<?php if(isset($search)) echo $search;?>" class="all-options">
        <input class="button" type="submit" value="<?php  printf ( __( 'Search' , 'easyReservations' )); ?>" onclick="easyreservation_send_table('all', 1)">
			<?php } ?>
    </td>
  </tr>
</table>
<form action="admin.php?page=reservations" method="get" name="frmAdd" id="frmAdd"><?php wp_nonce_field('easy-main-bulk','easy-main-bulk'); ?>
	<table  class="reservationTable <?php echo RESERVATIONS_STYLE; ?>" style="width:99%;"> <!-- Main Table //-->
		<thead> <!-- Main Table Header //-->
			<tr><?php $countrows = 1;
				if($table_options['table_bulk'] == 1){ $countrows++; ?>
        <th style="text-align:center"><input type="hidden" name="page" value="reservations"><input type="checkbox" name="themainbulk" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')" style="margin-top:2px"></th>
			<?php } if($table_options['table_from'] == 1){ $countrows++; ?>
        <th colspan="2"><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
					<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
			<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ $countrows++; ?>
        <th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
					<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_custom'] == 1){ $countrows++; ?>
        <th><?php printf ( __( 'Custom fields' , 'easyReservations' )); ?></th>
			<?php }  if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ $countrows++; ?>
        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )">
					<?php } elseif($order=="DESC" and $orderby=="reservated") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reservated' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )"><?php } ?><?php printf ( __( 'Reserved' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_status'] == 1){ $countrows++; ?>
        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="status") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )">
					<?php } elseif($order=="DESC" and $orderby=="status") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'status' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )"><?php } ?><?php printf ( __( 'Status' , 'easyReservations' ));?></a></th>
			<?php } if($table_options['table_email'] == 1){ $countrows++; ?>
        <th><?php printf ( __( 'Email' , 'easyReservations' ));?></th>
			<?php } if($table_options['table_persons'] == 1){ $countrows++; ?>
        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="persons") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )">
					<?php } elseif($order=="DESC" and $orderby=="persons") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'persons' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )"><?php } ?><?php printf ( __( 'Persons' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){ $countrows++; ?>
        <th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'room' )">
					<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )"><?php } ?><?php printf ( __( 'Resource' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_country'] == 1){ $countrows++; ?>
        <th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
			<!-- Modif AWI passé en L792 -->
			<?php }  if($table_options['table_customp'] == 1){ $countrows++; ?>
        <th><?php printf ( __( 'Custom prices' , 'easyReservations' )); ?></th>
			<?php }  if($table_options['table_price'] == 1){ $countrows++; ?>
        <th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
			<?php } ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<?php if($table_options['table_bulk'] == 1){ ?>
        <th style="text-align:center"><input type="hidden" name="page" value="reservations" style="text-align:center"><input type="checkbox" id="bulkArr[]" onclick="checkAllController(document.frmAdd,this,'bulkArr')"></th>
			<?php } if($table_options['table_from'] == 1){ ?>
        <th colspan="2"><?php if($order=="ASC" and $orderby=="date") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'date' )">
					<?php } elseif($order=="DESC" and $orderby=="date") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'date' )"><?php } ?><?php printf ( __( 'Date' , 'easyReservations' ));?></a></th>
			<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
        <th><?php if($order=="ASC" and $orderby=="name") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'name' )">
					<?php } elseif($order=="DESC" and $orderby=="name") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'name' )"><?php } ?><?php printf ( __( 'Name' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_custom'] == 1){ ?>
        <th><?php printf ( __( 'Custom fields' , 'easyReservations' )); ?></th>
			<?php } if($table_options['table_reservated'] == 1 || $table_options['table_reservated'] == 1){ ?>
        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="reservad") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )">
					<?php } elseif($order=="DESC" and $orderby=="reservated") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'reservated' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'reservated' )"><?php } ?><?php printf ( __( 'Reserved' , 'easyReservations' ));?></a></th>
			<?php } if($table_options['table_status'] == 1){ ?>
        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="status") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )">
					<?php } elseif($order=="DESC" and $orderby=="status") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'status' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'status' )"><?php } ?><?php printf ( __( 'Status' , 'easyReservations' ));?></a></th>
			<?php } if($table_options['table_email'] == 1){ ?>
        <th><?php printf ( __( 'Email' , 'easyReservations' ));?></th>
			<?php } if($table_options['table_persons'] == 1){ ?>
        <th style="text-align:center"><?php if($order=="ASC" and $orderby=="persons") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )">
				<?php } elseif($order=="DESC" and $orderby=="persons") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'persons' )">
				<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'persons' )"><?php } ?><?php printf ( __( 'Persons' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){ ?>
        <th><?php if($order=="ASC" and $orderby=="room") { ?><a class="asc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'DESC', 'room' )">
					<?php } elseif($order=="DESC" and $orderby=="room") { ?><a class="desc2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )">
					<?php } else { ?><a class="stand2" onclick="easyreservation_send_table('<?php echo $typ; ?>', 1, 'ASC', 'room' )"><?php } ?><?php printf ( __( 'Resource' , 'easyReservations' ));?></a></th>
			<?php }  if($table_options['table_country'] == 1){ ?>
        <th><?php printf ( __( 'Country' , 'easyReservations' )); ?></th>
			<!-- Modif AWI passé L834 -->
				<?php }  if($table_options['table_customp'] == 1){ ?>
        <th><?php printf ( __( 'Custom prices' , 'easyReservations' )); ?></th>
			<?php }  if($table_options['table_price'] == 1){ ?>
        <th style="text-align:right"><?php printf ( __( 'Price' , 'easyReservations' ));?></th>
			<?php } ?>
		</tr>
	</tfoot>
	<tbody>
		<?php
		$nr=0;
		$export_ids = '';
		$sql = "SELECT id, arrival, departure, name, email, number, childs, room, roomnumber, country, approve, price, custom, customp, reservated FROM ".$wpdb->prefix ."reservations
						WHERE $type $selectors $zeichen $search_str $permission_selectors ORDER BY $orders_by $orders $limit";  // Main Table query
		$result = $wpdb->get_results( $wpdb->prepare($sql, $esc_like, $esc_like, $esc_like));

		if(count($result) > 0 ){
			foreach($result as $res){
				$res = new Reservation($res->id, (array) $res);
				$res->Calculate();
				$class= $nr%2==0 ? 'alternate' : '';
				$nr++;
				$highlightClass = in_array($res->email, $regular_guest_array) ? 'highlighter' : '';
				$export_ids .= $res->id.', ';

				if(time() - $res->arrival > 0 && time() - $res->departure > 0) $sta = "er_res_old";
				elseif(time() - $res->arrival > 0 && time() - $res->departure <= 0) $sta = "er_res_now";
				else $sta = "er_res_future";
				if(isset($favourite)){
					if(in_array($res->id, $favourite)){
						$favclass = ' easy-fav';
						$favid = 'fav-'.$res->id;
						if($typ != 'favourite')$highlightClass = 'highlighter';
					} else {
						$favclass = ' easy-unfav';
						$favid = 'unfav-'.$res->id;
					}
				} ?>
				<tr class="<?php echo $class.' '.$highlightClass; ?>" height="47px"><!-- Main Table Body //-->
					<?php if($table_options['table_bulk'] == 1 || isset($favourite)){ ?>
            <td width="2%" style="text-align:center;vertical-align:middle;">
							<?php if($table_options['table_bulk'] == 1){ ?><input name="bulkArr[]" id="bulkArr[]" type="checkbox" style="margin-left: 8px;" value="<?php echo $res->id;?>"><?php } ?>
							<?php if(isset($favourite)){ ?><div class="easy-favourite <?php echo $favclass; ?>" id="<?php echo $favid; ?>" onclick="easyreservations_send_fav(this)"> </div><?php } ?>
            </td>
					<?php } if($table_options['table_from'] == 1){
						if(date('Y', $res->arrival) != date('Y') || date('Y', $res->departure) != date('Y')) $year = true; else $year = false; ?>
            <td class="<?php echo $sta; ?>" style="width:24px;text-align: right;">
              <div style="margin-bottom:5px;">
                <span style="font-weight: bold;font-size: 11px;text-align:right;">
	                <?php $round = round(($res->arrival-time())/86400, 0); if($round > 0) $round = '+'.$round; if($round == 0) echo ' 0'; else echo $round;?>
                </span>
              </div>
              <div>
                <span style="font-weight: bold;font-size: 11px;text-align:right;">
	                <?php $round = round(($res->departure-time())/86400, 0); if($round > 0) $round = '+'.$round; if($round == 0) echo ' 0'; else echo $round; ?>
                </span>
              </div>
            </td>
            <td class="<?php echo $sta; ?>" style="padding-left:0px;padding-right:0px;width:70px;white-space: nowrap;">
              <div style="margin-bottom:5px;">
                <span style="color:#444;font-weight: bold;font-size:15px;"><?php echo date('d', $res->arrival); ?></span>
                <span style="color:#777;font-weight: bold;font-size: 13px;"><?php echo date('M', $res->arrival); ?></span>
								<?php if($year){ ?><span style="color:#777;font-weight: bold;font-size: 14px;"><?php echo date('Y', $res->arrival); ?></span><?php } ?>
								<?php if(RESERVATIONS_USE_TIME == 1){ ?><span style="color:#999;font-weight: bold;font-size: 11px;"><?php echo date('H:i', $res->arrival); ?></span><?php } ?>
              </div>
              <div>
                <span style="color:#444;font-weight: bold;font-size: 15px;"><?php echo date('d', $res->departure); ?></span>
                <span style="color:#777;font-weight: bold;font-size: 13px;"><?php echo date('M', $res->departure); ?></span>
								<?php if($year){ ?><span style="color:#777;font-weight: bold;font-size: 14px;"><?php echo date('Y', $res->departure); ?></span><?php } ?>
								<?php if(RESERVATIONS_USE_TIME == 1){ ?><span style="color:#999;font-weight: bold;font-size: 11px;"><?php echo date('H:i', $res->departure); ?></span><?php } ?>
              </div>
            </td>
					<?php } if($table_options['table_name'] == 1 || $table_options['table_id'] == 1){ ?>
            <td valign="top" class="row-title test" valign="top" nowrap>
              <b style="font-weight: bold">
								<?php if($table_options['table_name'] == 1){ ?>
                  <a href="admin.php?page=reservations&view=<?php echo $res->id;?>"><?php echo $res->name;?> / 
                  	<?php 
                  	$customs = $res->getCustoms($res->custom, 'cstm');
                  	foreach($customs as $custom){
                  		if ($custom['id'] == 2) {
                  			echo $res->getCustomsValue($custom);
                  		}
                  	}
                  	?></a>
								<?php } if($table_options['table_id'] == 1) echo ' (#'.$res->id.')'; ?>
              </b>
							<?php do_action('er_table_name_custom', $res); ?>
              <div class="test2" style="margin:5px 0 0px 0;">
                <a href="admin.php?page=reservations&edit=<?php echo $res->id;?>"><?php printf ( __( 'Edit' , 'easyReservations' ));?></a>
								<?php if(isset($typ) && ($typ=="deleted" || $typ=="pending")) { ?>| <a style="color:#28a70e;" href="admin.php?page=reservations&approve=<?php echo $res->id;?>"><?php printf ( __( 'Approve' , 'easyReservations' ));?></a>
								<?php } if(!isset($typ) || (isset($typ) && ($typ=="active" || $typ=="pending"))) { ?> | <a style="color:#bc0b0b;" href="admin.php?page=reservations&delete=<?php echo $res->id;?>"><?php printf ( __( 'Reject' , 'easyReservations' ));?></a>
								<?php } if(isset($typ) && $typ=="trash") { ?>| <a href="admin.php?page=reservations&bulkArr[]=<?php echo $res->id;?>&bulk=2&easy-main-bulk=<?php echo wp_create_nonce('easy-main-bulk'); ?>"><?php printf ( __( 'Restore' , 'easyReservations' ));?></a> |
								<a style="color:#bc0b0b;" href="admin.php?page=reservations&easy-main-bulk=&bulkArr[]=<?php echo $res->id;?>&bulk=3&easy-main-bulk=<?php echo wp_create_nonce('easy-main-bulk'); ?>"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></a>
								<?php } ?> |
	              <a href="admin.php?page=reservations&sendmail=<?php echo $res->id;?>"><?php echo __( 'Mail' , 'easyReservations' );?></a>
              </div>
            </td>
          <?php }  if($table_options['table_custom'] == 1){ ?>
            <td><?php $customs = $res->getCustoms($res->custom, 'cstm');
							if(!empty($customs)){
								foreach($customs as $custom){
									if(isset($custom['id'])) $custom['title'] = $custom_fields['fields'][$custom['id']]['title'];
									echo '<b>'.$custom['title'].':</b> '.$res->getCustomsValue($custom).'<br>';
								}
							}?></td>
					<?php } if($table_options['table_reservated'] == 1){ ?>
            <td style="text-align:center"><?php echo human_time_diff( $res->reservated ).' '.__('ago', 'easyReservations');?></td>
					<?php } if($table_options['table_status'] == 1){ ?>
            <td style="text-align:center;vertical-align: middle"><span class="table-status-<?php echo $res->status; ?>"><?php echo $res->getStatus(false); ?></span></td>
					<?php } if($table_options['table_email'] == 1){ ?>
            <td><a href="admin.php?page=reservations&sendmail=<?php echo $res->id; ?>"><?php echo $res->email;?></a></td>
					<?php } if($table_options['table_persons'] == 1){ ?>
            <td style="text-align:center;color:#777;font-weight: bold !important;font-size:14px"><?php echo $res->adults; ?> +<?php echo $res->childs; ?></td>
					<?php }  if($table_options['table_room'] == 1 || $table_options['table_exactly'] == 1){  ?>
            <td nowrap><?php if($table_options['table_room'] == 1) echo '<a href="admin.php?page=reservation-resources&room='.$res->resource.'">'.__($the_rooms_array[$res->resource]->post_title).'</a> '; if($table_options['table_exactly'] == 1 && isset($res->resourcenumber)) echo '<b>'.easyreservations_get_roomname($res->resourcenumber, $res->resource).'</b>'; ?></td>
					<?php }  if($table_options['table_country'] == 1){  ?>
            <td nowrap><?php echo easyreservations_country_name( $res->country); ?></td>
					<!-- Modif AWI passé L946-->
					<?php }  if($table_options['table_customp'] == 1){ ?>
            <td><?php $customs = $res->getCustoms($res->prices, 'cstm');
							if(!empty($customs)){
								foreach($customs as $custom){
									if(isset($custom['id'])){
										$custom_field = $custom_fields['fields'][$custom['id']];
										$custom['title'] = $custom_field['title'];
										$custom['amount'] = $res->calculateCustom($custom['id'], $custom['value'], $custom_fields);
										$custom['value'] = $custom_field['options'][$custom['value']]['value'];
									}
									if(!isset($custom['amount']))
										$custom['amount'] = 0;
									echo '<b>'.$custom['title'].':</b> '.$custom['value'].' - '.easyreservations_format_money($custom['amount'], 1).'<br>';
								}
							}?></td>
					<?php }  if($table_options['table_price'] == 1){ ?>
            <td nowrap style="text-align:right">
              <div style="margin-bottom:6px;">
                <span style="font-weight: bold;font-size:12px;color:#555;;"><?php echo $res->formatPrice(true, 1); ?></span>
              </div>
              <div>
                <span style="font-weight: bold !important;font-size:12px;">
	                <?php if($res->price == 0) echo 100; else echo round(100/$res->price*$res->paid, 0); ?>% <?php printf ( __( 'Paid' , 'easyReservations' ));?>
                </span>
              </div>
            </td>
					<?php } ?>
        </tr>
			<?php }
		} else { ?> <!-- if no results form main quary !-->
     <tr>
        <td colspan="<?php echo $countrows; ?>"><b><?php printf ( __( 'No Reservations found' , 'easyReservations' ));?></b></td> <!-- Mail Table Body if empty //-->
					<tr>
		<?php } ?>
		</tbody>
	</table>
	<table  style="width:99%;">
		<tr>
			<td style="width:33%;"><!-- Bulk Options //-->
				<?php if($table_options['table_bulk'] == 1){ ?><select name="bulk" id="bulk"><option select="selected" value="0"><?php echo __( 'Bulk Actions' ); ?></option><?php if((isset($typ) AND $typ!="trash") OR !isset($typ)) { ?><option value="1"><?php printf ( __( 'Move to Trash' , 'easyReservations' ));?></option><?php }  if(isset($typ) AND $typ=="trash") { ?><option value="2"><?php printf ( __( 'Restore' , 'easyReservations' ));?></option><option value="3"><?php printf ( __( 'Delete Permanently' , 'easyReservations' ));?></option><?php } ;?></select>  <input class="button" type="submit" value="<?php printf ( __( 'Apply' , 'easyReservations' ));?>" /></form><?php } ?>
			</td>
			<td style="width:33%;" nowrap> <!-- Pagination  //-->
				<?php if($items > 0) { ?><div class="tablenav" style="text-align:center; margin:0 115px 4px 0;"><div class='tablenav-pages'><?php echo $p->show(); ?></div></div><?php } ?>
			</td>
			<td style="width:33%;margin-left: auto; margin-right: 0pt; text-align: right;"> <!-- Num Elements //-->
			   <span class="displaying-nums"><?php echo $nr;?> <?php printf ( __( 'Elements' , 'easyReservations' ));?></span>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">
  jQuery(document).ready(function(){
		createTablePickers();
  });
  if(document.getElementById('easy-export-id-field')) document.getElementById('easy-export-id-field').value = '<?php echo $export_ids; ?>';
</script><?php
	exit;
}

add_action('wp_ajax_easyreservations_send_fav', 'easyreservations_send_fav_callback');

function easyreservations_send_fav_callback(){
	check_ajax_referer( 'easy-favourite', 'security' );
	if(isset( $_POST['id'])){
		global $current_user;
		$current_user = wp_get_current_user();
		$user = $current_user->ID;
		$favourites = get_user_meta($user, 'reservations-fav', true);
		$save = $favourites;
		$id = $_POST['id'];
		$mode = $_POST['mode'];
		if(is_array($favourites) && $mode == 'add' && !in_array($id, $favourites)){
			$favourites[] = $id;
		} elseif(is_array($favourites) && $mode == 'del' && in_array($id, $favourites)){
			$key = array_search($id, $favourites);
			unset($favourites[$key]);
		}

		if(!is_array($favourites)) $favourites[] = $id;
		update_user_meta($user, 'reservations-fav', $favourites, $save);
	}
	die();
}

add_action('wp_ajax_easyreservations_get_custom', 'easyreservations_get_custom');

function easyreservations_get_custom(){
	//check_ajax_referer( 'easy-custom', 'security' );
	$custom_fields = get_option('reservations_custom_fields');
	$field = easyreservations_generate_custom_field($_POST['id']);
	echo json_encode(array($field, $custom_fields['fields'][$_POST['id']]));
	die();
}