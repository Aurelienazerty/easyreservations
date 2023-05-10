<?php
require('../../../wp-load.php');
global $reservations_settings;

if(isset($_POST['more'])) $days_after_present = $_POST['more'];
$main_options = get_option("reservations_main_options");
$overview_options = $main_options['overview'];
if(isset($_POST['interval'])) $interval = $_POST['interval'];
else $interval = 86400;
if($interval == 3600)	$date_pat = "d.m.Y H";
else $date_pat = "d.m.Y";

if(isset($_POST['dayPicker'])){
	$dayPicker=$_POST['dayPicker'];
	$daysbetween=(strtotime($dayPicker)-strtotime(date("d.m.Y", time())))/$interval;
	$days_after_present=round($daysbetween/$interval*$interval)+2;
	$main_options = get_option("reservations_main_options");
	$overview_options = $main_options['overview'];
}

$monthes = easyreservations_get_date_name(1);
$days = easyreservations_get_date_name(0,2);

if(isset($_POST['roomwhere']) && !empty($_POST['roomwhere'])) $roomwhere = $_POST['roomwhere'];
if(isset($_POST['approve'])) $approve = $_POST['approve'];
if(isset($_POST['add']) && !empty($_POST['add'])) $add = $_POST['add'];
if(isset($_POST['edit']) && !empty($_POST['edit'])) $edit = $_POST['edit'];
if(isset($_POST['nonepage']) && $_POST['nonepage'] == 0) $nonepage = $_POST['nonepage'];
if(isset($_POST['id']) && !empty($_POST['id'])) $id = $_POST['id'];
if(isset($_POST['res_date_from_stamp'])){
	$explode_time = explode("-", $_POST['res_date_from_stamp']);
	$reservation_arrival_stamp = $explode_time[0];
	$reservation_departure_stamp = $explode_time[1];
}
$date_style = isset($nonepage) ? 'ov-days-hover' : '';

if(isset($_POST['daysshow'])) $days_to_show = $_POST['daysshow'];
else $days_to_show = $overview_options['overview_show_days']; //How many Days to Show
$reservations_show_rooms = $overview_options['overview_show_rooms'];

if(!isset($reservations_show_rooms) || empty($reservations_show_rooms)) $show_rooms=easyreservations_get_rooms(0,1);
else {
	global $wpdb;
	$show_rooms = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE ID in($reservations_show_rooms) ORDER BY menu_order");
}

/* - - - - - - - - - - - - - - - - *\
|
|	Calculate Overview
|
/* - - - - - - - - - - - - - - - - */
if(!isset($days_after_present)) $days_after_present=0;

$timevariable = strtotime(date("d.m.Y 00:00:00", time()))-($interval*3); //Timestamp of first Second of today

$timesx=easyreservations_calculate_out_summertime($timevariable+$interval*$days_after_present, $timevariable); // Timestamp of Startdate of Overview
$timesy=$timesx+$interval*$days_to_show; // Timestamp of Enddate of Overview
$dateshow=date("d. ", $timesx).$monthes[date("n", $timesx)-1].date(" Y", $timesx).' - '.date("d. ", $timesy-$interval).$monthes[date("n", $timesy-$interval)-1].date(" Y", $timesy-$interval);
$stardate=date("Y-m-d H:i", $timesx); // Formated Startdate
$enddate=date("Y-m-d H:i", $timesy-$interval); // Formated Enddate
if(!isset($daysbetween)) $daysbetween = ($timesx/$interval)-(strtotime(date("d.m.Y", time()))/$interval);

if(isset($reservation_arrival_stamp)){
	$numberhighstart=ceil(($reservation_arrival_stamp-$timesx)/$interval);
	$numberlaststart=ceil(($reservation_departure_stamp-$timesx)/$interval);
	if($numberlaststart<10) $numberlaststart='0'.$numberlaststart;
	if($numberhighstart<10) $numberhighstart='0'.$numberhighstart;
}


if(RESERVATIONS_STYLE == 'widefat'){
	$ovBorderColor='#9E9E9E';
	$ovBorderStatus='dotted';
} elseif(RESERVATIONS_STYLE == 'greyfat'){
	$ovBorderColor='#777777';
	$ovBorderStatus='dashed';
}

$days_string = easyreservations_interval_infos(86400,1);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + OVERVIEW + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?><input type="hidden" id="hiddenfieldclick" name="hiddenfieldclick"><input type="hidden" id="timesx" name="timesx" value="<?php echo $timesx; ?>">
<input type="hidden" id="hiddenfieldclick2" name="hiddenfieldclick2"><input type="hidden" id="timesy" name="timesy" value="<?php echo $timesy; ?>">
<input type="hidden" id="getmore" name="getmore" value="<?php echo $days_after_present; ?>">
<table class="<?php echo RESERVATIONS_STYLE; ?> overview" cellspacing="0" cellpadding="0" id="overview" style="width:99%;" onmouseout="document.getElementById('ov_datefield').innerHTML = '';">
<thead>
<tr>
    <th colspan="<?php echo $days_to_show+1; ?>"  class="overviewHeadline">
        <span id="pickForm"><input name="dayPicker" id="dayPicker" type="hidden" value="<?php if(isset($dayPicker)) echo $dayPicker; ?>"></span> &nbsp;<b class="overviewDate"><?php echo $dateshow; ?></b><span id="ov_datefield" style="padding-left:6px;width:300px;display:inline-block"> </span>
					<span style="float:right">
						<?php if($interval == 3600){ ?>
              <input name="daybutton" class="button" value="Days" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','no','<?php echo $days_to_show; ?>',86400);resetSet();">
						<?php } else { ?>
              <input name="daybutton" class="button" value="Hours" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','no','<?php echo $days_to_show; ?>',3600);resetSet();">
						<?php } ?>
              <input name="settimes" class="button" value="15" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','',15,<?php echo $interval; ?>);resetSet();">
						<input name="settimes" class="button" value="30" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','',30,<?php echo $interval; ?>);resetSet();">
						<input name="settimes" class="button" value="45" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','',45,<?php echo $interval; ?>);resetSet();">
					</span>
    </th>
</tr>
<tr id="overviewTheadTr">
    <td class="h1overview" <?php if($interval == 3600) echo 'rowspan="2"'; ?>>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present-($days_to_show);?>','no', '<?php echo $days_to_show; ?>', <?php echo $interval; ?>);" title="-<?php echo ($days_to_show).' '.$days_string; ?>" style="cursor:pointer;"><b style="letter-spacing:-4px">&lsaquo; &lsaquo; &lsaquo; &nbsp;&nbsp;</b></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present-round($days_to_show/2);?>','no', '<?php echo $days_to_show; ?>',<?php echo $interval; ?>);" title="-<?php echo round($days_to_show/2).' '.$days_string; ?>" style="cursor:pointer;"><b>&laquo;</b></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present-round($days_to_show/3);?>','no', '<?php echo $days_to_show; ?>',<?php echo $interval; ?>);" title="-<?php echo round($days_to_show/3).' '.$days_string; ?>" style="cursor:pointer;"><b>&lsaquo;</b></a>
        <span id="easy-overview-loading"><a onclick="easyRes_sendReq_Overview('0','no', '<?php echo $days_to_show; ?>',<?php echo $interval; ?>);" title="<?php echo __( 'Present' , 'easyReservations' ); ?>" style="cursor:pointer;"><b>&omicron;</b></a></span>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present+round($days_to_show/3);?>','no', '<?php echo $days_to_show; ?>',<?php echo $interval; ?>);" title="+<?php echo round($days_to_show/3).' '.$days_string; ?>" style="cursor:pointer;"><b>&rsaquo;</b></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present+round($days_to_show/2);?>','no', '<?php echo $days_to_show; ?>',<?php echo $interval; ?>);" title="+<?php echo round($days_to_show/2).' '.$days_string; ?>" style="cursor:pointer;"><b>&raquo;</b></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present+($days_to_show);?>','no', '<?php echo $days_to_show; ?>',<?php echo $interval; ?>);" title="+<?php echo ($days_to_show).' '.$days_string; ?>" style="cursor:pointer;"><b style="letter-spacing:-4px">&rsaquo; &rsaquo; &rsaquo; &nbsp;&nbsp;</b></a>
    </td>
	<?php
	$co=0;
	$last_date = 0;
	while($co < $days_to_show){
		$current_date = easyreservations_calculate_out_summertime($timesx+($interval*$co), $timesx);
		if($interval == 86400 || date("d.m.Y", $last_date) != date("d.m.Y", $current_date)){
			if($interval == 3600){
				$tomorrow = strtotime(date("d.m.Y", $current_date))+86400;
				$diff = round(($tomorrow - $current_date)/$interval);
			} else{
				$diff = 1;
				if(isset($reservation_arrival_stamp) && $current_date >=  $reservation_arrival_stamp-$interval && $current_date <= $reservation_departure_stamp) $background_highlight='backgroundhighlight';
			}
			if(date("d.m.Y", $current_date) ==  date("d.m.Y", time())) $background_highlight='backgroundtoday';
			elseif(!isset($background_highlight)) $background_highlight='backgroundnormal';?>
        <td colspan="<?php echo $diff; ?>" class="<?php echo  $background_highlight; ?> ov-days <?php echo $date_style; ?>" style="vertical-align:middle;padding:1px 0px;<?php if($interval == 86400) echo 'min-width:20px;'; ?>" onclick="overviewSelectDate('<?php echo date(RESERVATIONS_DATE_FORMAT,$current_date); ?>');">
					<?php if($interval == 86400) echo date("j",$current_date).'<br><small>'.$days[date("N",$current_date)-1]; else echo '<small style="overflow:hidden;display:inline-block;">'.date(RESERVATIONS_DATE_FORMAT, $current_date); echo '</small>'; ?>
        </td><?php
			unset($background_highlight);
		}
		$co++;
		$last_date = $current_date;
	} ?>
</tr>
<?php if($interval == 3600){ ?><tr><?php
	$co=0;
	while($co < $days_to_show){
		$current_date=$timesx+($interval*$co);
		if(isset($reservation_arrival_stamp) && $current_date >= $reservation_arrival_stamp && $current_date <= $reservation_departure_stamp) $background_highlight='backgroundhighlight';
		else {
			$background_highlight='ov-days-hours';
			if(date("H", $current_date) == 00) $background_highlight.=' first';
		}?>
      <td  class="<?php echo  $background_highlight; ?> ov-days <?php echo $date_style; ?>" style="vertical-align:middle;min-width:23px" onclick="overviewSelectDate('<?php echo date(RESERVATIONS_DATE_FORMAT,$current_date); ?>');">
				<?php echo '<small>'.date("H", $current_date).'</small>'; ?>
      </td><?php $co++;
	} ?>
</tr>
	<?php } ?>
</thead>
<tfoot>
<tr>
    <th colspan="<?php echo $days_to_show+1; ?>" class="overviewFooter">
        <span style="vertical-align:middle;" id="resetdiv"></span>
			<span style="float:right;">
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'images/blue_dot.png'; ?>">&nbsp;<small><?php echo __( 'Past' , 'easyReservations' ); ?></small>
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'images/green_dot.png'; ?>">&nbsp;<small><?php echo __( 'Present' , 'easyReservations' ); ?></small>
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'images/red_dot.png'; ?>">&nbsp;<small><?php echo __( 'Future' , 'easyReservations' ); ?></small>
				<?php if(isset($id)){ ?> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'images/yellow_dot.png'; ?>">&nbsp;<small><?php echo __( 'Active' , 'easyReservations' ); ?></small><?php } ?>
			</span>
    </th>
</tr>
</tfoot>
<tbody>
<?php
if(isset($roomwhere)) $all_resources = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE ID='$roomwhere'");
else $all_resources = $show_rooms;

foreach( $all_resources as $key => $resource){ /* - + - FOREACH ROOM - + - */
	$res = new Reservation(false, array('dontclean', 'arrival' => $timesx, 'departure' => $timesx+$interval, 'resource' => (int) $resource->ID ));
	$res->interval = $interval;
	$roomID=$resource->ID;
	if(isset($resource_number)) unset($resource_number);
	if(isset($reservations_settings['mergeres'])){
		if(is_array($reservations_settings['mergeres']) && isset($reservations_settings['mergeres']['merge']) && $reservations_settings['mergeres']['merge'] > 0) $resource_number = $reservations_settings['mergeres']['merge'];
		elseif(is_numeric($reservations_settings['mergeres']) && $reservations_settings['mergeres'] > 0) $resource_number  = $reservations_settings['mergeres'];
	}
	$roomcount = get_post_meta($roomID, 'roomcount', true);
	if(is_array($roomcount)){
		if(!isset($resource_number)) $resource_number = $roomcount[0];
		$avail_by_pers = true;
	} else {
		$avail_by_pers = false;
		if(!isset($resource_number)) $resource_number = $roomcount;
	}
	$resource_names = get_post_meta($roomID, 'easy-resource-roomnames', TRUE);
	$row_count=0;
	$resource_sql = $wpdb->get_results($wpdb->prepare("SELECT id, name, departure, arrival, roomnumber, number+childs as persons FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND room='$roomID' AND (arrival BETWEEN '%s' AND '$enddate' OR departure BETWEEN '%s' AND '$enddate' OR '%s' BETWEEN arrival AND departure) ORDER BY room ASC, roomnumber ASC, arrival ASC", array($stardate,$stardate,$stardate)));
	if(isset($reservations)) unset($reservations);
	foreach($resource_sql as $resourc){
		if($avail_by_pers){
			$reservations[1][] = $resourc;
		} elseif(!empty($resourc->roomnumber)){
			$reservations[$resourc->roomnumber][] = $resourc;
			$co=0;
		}
	} ?>
<tr class="ov_resource_row" style="background:#EAE8E8">
    <td nowrap><span>&nbsp;<a href="admin.php?page=reservation-resources&room=<?php echo $resource->ID; ?>" title="<?php __( $resource->post_title); ?>" style="color: #6B6B6B;"><?php echo substr(__( $resource->post_title),0,20); ?></a></td>
	<?php
	$co=0;
	while($co < $days_to_show){
		if($overview_options['overview_show_avail'] == 1){
			$res->arrival = $timesx+($co*$interval)+($interval-1);
			$res->departure = $res->arrival+$interval;
			$roomDayPersons=round($resource_number-$res->checkAvailability(3),1);
			if($roomDayPersons <= 0) $textcolor='#FF3B38'; else $textcolor='#118D18';
		} else $textcolor = '';
		?><td axis="<?php echo $co+2;?>" style="color:<?php echo $textcolor; ?>" ><?php if($overview_options['overview_show_avail'] == 1)  echo '<small>'.$roomDayPersons.'</small>'; ?></small></td><?php
		$co++;
	} ?>
</tr><?php
	while($resource_number > $row_count){  /* - + - FOREACH EXACTLY ROOM - + - */
		if($avail_by_pers){
			if($row_count > 0) break;
			$name = __( $resource->post_title);
		} else {
			if(isset($resource_names[$row_count]) && !empty($resource_names[$row_count])) $name = $resource_names[$row_count];
			else $name = '#'.($row_count+1);
		}
		$row_count++;

		if($timesx < time()) $bg_color_last='#2A78D8';
		else $bg_color_last='#CC3333';
		if($row_count == $resource_number) $borderbottom=0;
		else $borderbottom=1; ?>
			<tr id="room<?php echo $row_count.'-'.$roomID; ?>">
				<td class="roomhead" style="color:#8C8C8C;" onclick="<?php if(isset($edit)){ ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$reservation_arrival_stamp); ?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$reservation_departure_stamp); ?>';setVals2(<?php echo $roomID; ?>,<?php echo $row_count; ?>);<?php } if(isset($edit) || isset($approve)){ ?>changer();clickOne(document.getElementById('<?php echo $roomID.'-'.$row_count.'-'.$numberhighstart; ?>'),'<?php echo $reservation_arrival_stamp; ?>');clickTwo(document.getElementById('<?php echo $roomID.'-'.$row_count.'-'.$numberlaststart; ?>'),'<?php echo $reservation_departure_stamp; ?>');<?php } if(isset($approve)){ ?>document.reservation_approve.roomexactly.selectedIndex=<?php echo $row_count-1; ?>;<?php } ?>"  nowrap>
            &nbsp;<?php echo $name; ?>
        </td><?php
		$CoutResNights2=0; $CoutResNights3=0; $CountNumberOfAdd=0; $wasFull=0; $countdifferenz=0; $itIS=0; $cell_count=0; $datesHalfOccupied = array(); $personsOccupied = array();
		if(isset($reservations[$row_count])){
			foreach($reservations[$row_count] as $reservation){
				$res_id=$reservation->id;
				$res_name=$reservation->name;
				$res_arrival = strtotime($reservation->arrival);
				$res_departure= strtotime($reservation->departure);
				if(date($date_pat, $res_departure) == date($date_pat, $res_arrival)){
					$res_nights = 0;
					if($interval == 3600) $temp_date_pat = $date_pat.':i';
					else $temp_date_pat = $date_pat;
					$round = round((strtotime(date($temp_date_pat,$res_arrival))+($interval/2)-$timesx)/$interval);
					if($avail_by_pers){
						if(isset($datesHalfOccupied[$round]['i'])) $datesHalfOccupied[$round]['i'] += $reservation->persons;
						else $datesHalfOccupied[$round]['i'] = $reservation->persons;
					} else {
						if(isset($datesHalfOccupied[$round]['i'])) $datesHalfOccupied[$round]['i'] += 1;
						else $datesHalfOccupied[$round]['i'] = 1;
					}
					if(isset($datesHalfOccupied[$round]['v'])) $datesHalfOccupied[$round]['v'] .= date('d.m H:i', $res_arrival).' - '.date('d.m H:i', $res_departure).' <b>'.$res_name.'</b> (#'.$res_id.')<br>';
					else $datesHalfOccupied[$round]['v'] = date('d.m H:i', $res_arrival).' - '.date('d.m H:i', $res_departure).' <b>'.$res_name.'</b> (#'.$res_id.')<br>';
					$datesHalfOccupied[$round]['id'][] = $res_id;
					if(isset($personsOccupied[date($date_pat, $round+$interval)])) $personsOccupied[date($date_pat, $round+$interval)] += $reservation->persons;
					else $personsOccupied[date($date_pat, $round+$interval)] = $reservation->persons;
				} else {
					$date_pattern = $date_pat;
					if($interval == 3600) $date_pattern.=":00";
					$res_nights = round((strtotime(date($date_pattern,$res_departure)) - strtotime(date($date_pattern,$res_arrival))) / $interval);
					for($i=0; $i <= $res_nights; $i++){
						if($timesx <= $res_arrival+($i*$interval) && $res_nights >= 1){
							$daysOccupied[]=date($date_pat, easyreservations_calculate_out_summertime($res_arrival+($i*$interval)+$interval, $res_arrival));
							$numberOccupied[]=$countdifferenz;
							if($avail_by_pers){
								if(isset($personsOccupied[date($date_pat, $res_arrival+($i*$interval)+$interval)])) $personsOccupied[date($date_pat, $res_arrival+($i*$interval)+$interval)] += $reservation->persons;
								else $personsOccupied[date($date_pat, $res_arrival+($i*$interval)+$interval)] = $reservation->persons;
							}
						}
					}
				}
				$reservation_array[]=array( 'name' =>$res_name, 'ID' =>$res_id, 'departure' => $res_departure, 'arDate' => $res_arrival, 'nights' => $res_nights );
				$countdifferenz++;
			}
		}
		$showdatenumber_start=0+$days_after_present;
		$showdatenumber_end=$days_to_show+$days_after_present;

		while($showdatenumber_start < $showdatenumber_end){
			$cell_count++;
			$showdatenumber_start++;
			$dateToday = easyreservations_calculate_out_summertime($timevariable+($interval*$showdatenumber_start), $timevariable);
			$wasFullTwo = 0;
			$border_side = 1;
			$onClick = 0;
			$table_click = '';
			if($cell_count < 10) $preparedCellcount='0'.$cell_count;
			else $preparedCellcount=$cell_count;
			if($dateToday < time()) $bg_pattern="url(".RESERVATIONS_URL ."images/patbg.png?cond=".time().") repeat";
			else $bg_pattern = '';
			$res->arrival = $dateToday-$interval;
			$avail = $res->availFilter($resource_number, 0, (int) $interval);

			if($avail > 0) $bg_color_free='#FFEDED';
			elseif(date($date_pat, $dateToday-$interval)==date($date_pat, time())) $bg_color_free = '#EDF0FF';
			elseif(date("N", $dateToday-$interval)==6 OR date("N", $dateToday-$interval)==7) $bg_color_free = '#FFFFEB';
			else $bg_color_free='#FFFFFF';
			if($avail_by_pers){
				$res_day_count = 0;
				if(isset($datesHalfOccupied[$cell_count])) $res_day_count += $datesHalfOccupied[$cell_count]['i'];
				if(isset($personsOccupied[date($date_pat, $dateToday)])) $res_day_count += $personsOccupied[date($date_pat, $dateToday)];
				$title = '';
				if($res_day_count > 0){
					$table_click = 'jQuery(\'#easy-table-roomselector\').val('.$roomID.');document.getElementById(\'easy-table-search-date\').value = \''.date(RESERVATIONS_DATE_FORMAT, $dateToday-$interval).'\';easyreservation_send_table(\'all\', 1);';
					$percent = 100/$resource_number*$res_day_count;
					if($percent > 95) $bg_color_occ = '#cc3433';
					elseif($percent > 70) $bg_color_occ = '#ff7b00';
					elseif($percent > 35) $bg_color_occ = '#128d18';
					else $bg_color_occ = '#8FD996'; ?>
        <td id="<?php echo $roomID.'-'.$row_count.'-'.$preparedCellcount; ?>"  title="<?php echo $title; ?>" <?php if($border_side == 0) echo 'class="er_overview_cell"'; ?> onclick="<?php if(isset($nonepage) && !empty($table_click)) echo $table_click; elseif((isset($edit) || isset($add)) && $onClick==0){ ?>changer();clickTwo(this);clickOne(this);<?php if(!isset($nonepage)){ ?>setVals2('<?php echo $roomID; ?>','<?php echo $row_count; ?>');<?php }}?>" style="background: <?php echo $bg_color_occ;?>;cursor:pointer" abbr="<?php echo $bg_color_occ;?>" date="<?php echo $dateToday; ?>" axis="<?php echo $cell_count+1; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this);"<?php } ?>><?php echo $res_day_count; ?></td><?php
				} else { ?>
        <td id="<?php echo $roomID.'-'.$row_count.'-'.$preparedCellcount; ?>" title="<?php echo $title; ?>" <?php if(isset($edit) || isset($add) || isset($nonepage)){ ?>onclick="changer();clickTwo(this);clickOne(this);setVals2('<?php echo $roomID; ?>','<?php echo $row_count; ?>');"<?php } ?> style="background:<?php echo $bg_pattern.' '.$bg_color_free;?>" abbr="<?php echo $bg_pattern.' '.$bg_color_free;?>" date="<?php echo $dateToday; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this);"<?php } ?> axis="<?php echo $cell_count+1; ?>"><?php echo '0'; ?></td><?php
				}
				continue;
			}
			if(isset($daysOccupied)){

				if(in_array(date($date_pat, $dateToday), $daysOccupied)){
					if($numberOccupied[$CoutResNights3] != $CountNumberOfAdd && $cell_count != 1) $CountNumberOfAdd++;
					//if(isset($reservation_array[$CountNumberOfAdd]['nights']) && $reservation_array[$CountNumberOfAdd]['nights'] < 1) while($reservation_array[$CountNumberOfAdd]['nights'] < 1) $CountNumberOfAdd++;
					$arrival = $reservation_array[$CountNumberOfAdd]['arDate'];
					$departure = $reservation_array[$CountNumberOfAdd]['departure'];
					$nights = $reservation_array[$CountNumberOfAdd]['nights'];

					if(isset($daysOccupied[$CoutResNights3+1]) && isset($numberOccupied[$CoutResNights3-1]) && $numberOccupied[$CoutResNights3-1] !== $daysOccupied[$CoutResNights3] && $numberOccupied[$CoutResNights3-1] !== $numberOccupied[$CoutResNights3])$wasFullTwo=1;

					if(($CoutResNights2 == 0 && $cell_count != 1) || ($wasFullTwo == 1 && $cell_count !== 1) || $dateToday - $arrival <= $interval){
						$bg_color_occ="url(".RESERVATIONS_URL ."images/1REPLACE_start.png) right top no-repeat, ".$bg_pattern." ".$bg_color_free;
						$itIS=0;
					} elseif($CoutResNights2 != 0 || $cell_count == 1 || (isset($daysOccupied[$CoutResNights3]) && $lastDay==$daysOccupied[$CoutResNights3])){
						$bg_color_occ="url(".RESERVATIONS_URL ."images/1REPLACE_middle.png) top repeat-x";
						if($cell_count != 1) $border_side=0;
						$itIS++;
					}

					if(isset($daysOccupied[$CoutResNights3+1]) && $daysOccupied[$CoutResNights3] !== $daysOccupied[$CoutResNights3+1] && $numberOccupied[$CoutResNights3] !== $numberOccupied[$CoutResNights3+1]){
						$bg_color_occ="url(".RESERVATIONS_URL ."images/1REPLACE_end.png) left top no-repeat, ".$bg_pattern." ".$bg_color_free;
						$itIS=0;

					}
					if(isset($daysOccupied[$CoutResNights3+1]) && $daysOccupied[$CoutResNights3] === $daysOccupied[$CoutResNights3+1] && array_key_exists($CoutResNights3+1, $daysOccupied)){
						$bg_color_occ='url('.RESERVATIONS_URL .'images/1REPLACE_cross.png) left top no-repeat 2REPLACE';
						$CoutResNights2=0;
						$CoutResNights3++;
						$CountNumberOfAdd++;
						$arrival = $reservation_array[$CountNumberOfAdd]['arDate'];
						$departure = $reservation_array[$CountNumberOfAdd]['departure'];
						$nights = $reservation_array[$CountNumberOfAdd]['nights'];
						$itIS=0;
						$onClick=1;

					}
					if(!in_array(date($date_pat, $dateToday+$interval), $daysOccupied) || isset($datesHalfOccupied[$dateToday-$interval])){
						$bg_color_occ="url(".RESERVATIONS_URL ."images/1REPLACE_end.png) left top no-repeat, ".$bg_pattern." ".$bg_color_free;
						$itIS=0;
						if(isset($datesHalfOccupied[$dateToday-$interval])){
							$exp = explode(",", $bg_color_occ);
							$bg_color_occ = $exp[0];
							$bg_color_occ.=", url(".RESERVATIONS_URL ."images/1REPLACE_start.png) right top no-repeat, ".$bg_pattern." ".$bg_color_free;
						}
					}

					$CoutResNights2++;
					$CoutResNights3++;
					$addname=" ";
					$lastDay=$daysOccupied[$CoutResNights3-1];
					if(isset($id) && $reservation_array[$CountNumberOfAdd]['ID'] == $id){
						$bg_color_occ=str_replace("1REPLACE", "yellow", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_back='#FFE400';
						$addname=' name="activeres"';
					} elseif($arrival < time() && $departure > time()){
						$bg_color_occ=str_replace("1REPLACE", "green", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_back='#118D18';
					} elseif($arrival > time()){
						$bg_color_occ=str_replace("1REPLACE", "red", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_back='#CC3333';
					} else {
						$bg_color_occ=str_replace("1REPLACE", "blue", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_back='#2A78D8';
					}

					$minusdays=0;
					$nightsproof=$nights;
					if($arrival < $timesx){
						$daybetween=($timesx-$arrival)/$interval;
						$minusdays=ceil($daybetween)-1;
						$nightsproof=$nights-$minusdays;
					}
					if($departure > $timesy) {
						$daybetween=($timesy/$interval)-(($arrival/$interval)+$nights);
						$minusdays+=substr(round($daybetween), 1, 10);
						$nightsproof=$nights-$minusdays;
					}

					$title_one = 	date('d.m H:i', $arrival).' - '.date('d.m H:i', $departure).' <b>'.$reservation_array[$CountNumberOfAdd]['name'].'</b> (#'.$reservation_array[$CountNumberOfAdd]['ID'].')<br>';

					if($itIS===1){
						?><td id="<?php echo $roomID.'-'.$row_count.'-'.$preparedCellcount; ?>"<?php echo $addname; ?> title="<?php echo $title_one; ?>" colspan="<?php echo $nights-1-$minusdays; ?>" class="er_overview_cell" date="<?php echo $dateToday;?>" onclick="<?php echo "location.href = 'admin.php?page=reservations&edit=".$reservation_array[$CountNumberOfAdd]['ID']."';"; ?>" style="background: <?php echo $bg_color_occ;?>;cursor: pointer;text-decoration:none;padding:0px;font: normal 11px Arial, sans-serif;vertical-align:middle;; overflow:hidden;"  abbr="<?php echo $bg_color_occ;?>" title="<?php echo $reservation_array[$CountNumberOfAdd]['name']; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this,'<?php echo date($date_pattern, $arrival+$interval).' - '.date($date_pattern, $departure-$interval); ?>');"<?php } ?>>
						<?php echo substr($reservation_array[$CountNumberOfAdd]['name'], 0, ($nights-1-$minusdays)*3); ?>
          </td><?php
					} elseif($itIS==$nightsproof+1 || $itIS==$nightsproof || $itIS==0) {
						$value = ''; $title = '';
						if($border_side == 0 ) $title .= $title_one;
						if(isset($datesHalfOccupied[$cell_count])){
							$value = $datesHalfOccupied[$cell_count]['i'];
							$title .= $datesHalfOccupied[$cell_count]['v'];
							$table_click = 'document.getElementById(\'easy-table-search-field\').value = \''.$reservation_array[$CountNumberOfAdd]['ID'].','.implode(',', $datesHalfOccupied[$cell_count]['id']).'\';easyreservation_send_table(\'all\', 1);';
							if(isset($id) && in_array($id, $datesHalfOccupied[$cell_count]['id'])) $bg_color_free = '#FCEA74';
							elseif(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", time())) $bg_color_free = '#6ECC72';
							elseif($dateToday-$interval > time()) $bg_color_free = '#E07B7B';
							else $bg_color_free = '#6F9DD6';
							$bg_color_occ = substr($bg_color_occ, 0, -7).$bg_color_free;
						}
						if($border_side == 1 ) $title .= $title_one;?>
          <td id="<?php echo $roomID.'-'.$row_count.'-'.$preparedCellcount; ?>"  title="<?php echo $title; ?>" <?php if($border_side == 0) echo 'class="er_overview_cell"'; ?> <?php echo $addname; ?> date="<?php echo $dateToday-$interval;?>" onclick="<?php if(isset($nonepage) && !empty($table_click)) echo $table_click; elseif((isset($edit) || isset($add) || isset($nonepage)) && $onClick==0){ ?>;changer();clickTwo(this);clickOne(this);setVals2('<?php echo $roomID; ?>','<?php echo $row_count; ?>');<?php  } elseif($onClick==1){ echo "location.href = 'admin.php?page=reservations&edit=".$reservation_array[$CountNumberOfAdd]['ID']."';"; } ?>" style="background: <?php echo $bg_color_occ;?>; padding:0px; overflow:hidden; text-shadow:none; border-style:none; text-decoration:none; font: normal 11px Arial, sans-serif; vertical-align:middle;<?php if($onClick==1) echo 'cursor:pointer'; ?>" abbr="<?php echo $bg_color_occ;?>" axis="<?php echo $cell_count+1; ?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this);"<?php } ?>>
						<?php echo $value; ?>
          </td><?php
					}
					$bg_color_last=$bg_color_back;
					$wasFull=1;
				} else {
					if($wasFull == 1) $CountNumberOfAdd++;
					$CoutResNights2=0;
					$class = ''; $value = ''; $title = '';
					if(isset($datesHalfOccupied[$cell_count])){
						$value = $datesHalfOccupied[$cell_count]['i'];
						$title = $datesHalfOccupied[$cell_count]['v'];
						$class = 'class="er_overview_cell"';
						$table_click = 'document.getElementById(\'easy-table-search-field\').value = \''.implode('|', $datesHalfOccupied[$cell_count]['id']).'\';easyreservation_send_table(\'all\', 1);';
						if(isset($id) && in_array($id, $datesHalfOccupied[$cell_count]['id'])) $bg_color_free = '#FCEA74';
						if(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", time())) $bg_color_free = '#118D18';
						elseif($dateToday-$interval > time()) $bg_color_free = '#CC3333';
						else $bg_color_free = '#2A78D8';
					}?>
        <td id="<?php echo $roomID.'-'.$row_count.'-'.$preparedCellcount; ?>" title="<?php echo $title; ?>" <?php echo $class; if(isset($edit) || isset($add) || isset($nonepage)){ ?>onclick="<?php if(isset($nonepage) && !empty($table_click)) echo $table_click; else { ?>changer();clickTwo(this);clickOne(this);setVals2('<?php echo $roomID; ?>','<?php echo $row_count; ?>');<?php } ?>"<?php } ?> style="background:<?php echo $bg_pattern.' '.$bg_color_free;?>" abbr="<?php echo $bg_pattern.' '.$bg_color_free;?>" date="<?php echo $dateToday-$interval;?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this);"<?php } ?> axis="<?php echo $cell_count+1; ?>"><?php
					echo $value.'</td>';
					$wasFull=0;
				}
			} else {
				$class = ''; $value = ''; $title = '';
				if(isset($datesHalfOccupied[$cell_count])){
					$value = $datesHalfOccupied[$cell_count]['i'];
					$title = $datesHalfOccupied[$cell_count]['v'];
					$class = 'class="er_overview_cell"';
					$table_click = 'document.getElementById(\'easy-table-search-field\').value = \''.implode('|', $datesHalfOccupied[$cell_count]['id']).'\';easyreservation_send_table(\'all\', 1);';
					if(isset($id) && in_array($id, $datesHalfOccupied[$cell_count]['id'])) $bg_color_free = '#FCEA74';
					if(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", time())) $bg_color_free = '#118D18';
					elseif($dateToday-$interval > time()) $bg_color_free = '#CC3333';
					else $bg_color_free = '#2A78D8';
				}
				?><td id="<?php echo $roomID.'-'.$row_count.'-'.$preparedCellcount; ?>" title="<?php echo $title; ?>" <?php echo $class; if(isset($edit) || isset($add) || isset($nonepage)){ ?>onclick="<?php if(isset($nonepage) && !empty($table_click)) echo $table_click; else { ?>changer();clickTwo(this);clickOne(this);setVals2('<?php echo $roomID; ?>','<?php echo $row_count; ?>');<?php } ?>"<?php } ?> style="background:<?php echo $bg_pattern.' '.$bg_color_free;?>" abbr="<?php echo $bg_pattern.' '.$bg_color_free;?>" date="<?php echo $dateToday-$interval;?>" <?php if($overview_options['overview_onmouseover'] == 1){ ?>onmouseover="hoverEffect(this);"<?php } ?> axis="<?php echo $cell_count+1; ?>"><?php echo $value; ?></td><?php
			}
		}
		unset($daysOccupied,$datesHalfOccupied,$numberOccupied,$reservation_array);
		echo '</tr>';
	}
	$res->destroy();
} ?></tbody>
</table>