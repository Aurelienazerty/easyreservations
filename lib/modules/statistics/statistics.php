<?php
/*
Plugin Name: Statistics Module
Plugin URI: http://easyreservations.org
Version: 1.2.3
Description: 3.3
Author: Feryaz Beer
Author URI: http://www.feryaz.de
License:GPL2
*/

if(is_admin()){
	add_action('easy-add-submenu-page', 'easyreservations_add_statistics_submenu');

	function easyreservations_add_statistics_submenu(){
		$reservation_main_permission=get_option("reservations_main_permission");
		if($reservation_main_permission && is_array($reservation_main_permission)){
			if(isset($reservation_main_permission['statistics']) && !empty($reservation_main_permission['statistics'])) $statistics = $reservation_main_permission['statistics'];
			else $statistics = 'edit_posts';
		} else {
			$statistics = 'edit_posts';
		}

		add_submenu_page('reservations', __('Statistics','easyReservations'), __('Statistics','easyReservations'), $statistics, 'reservation-statistics', 'easyreservations_statistics_page');
	}

	add_action('easy-dashboard-between', 'easyreservations_statistics_mini');

	function easyreservations_statistics_mini($stats = false, $only = false){
		easyreservations_load_resources();
		global $wpdb, $the_rooms_array;

		$j = date('Y');
		$m = date('n');
		$startstamp = mktime(0,0,0,$m-1,1,$j);
		$start = date('Y-m-d H:i:s', $startstamp);
		$end = date('Y-m-d H:i:s', mktime(23,59,59,$m+1,0,$j));
		$res_sql ="SELECT ";
			$res_sql .= "sum(IF(MONTH('$start') = MONTH(reservated) AND YEAR('$start') = YEAR(reservated), 1, 0)) as amt, ";
			$res_sql .= "sum(IF(MONTH('$end') = MONTH(reservated) AND YEAR('$end') = YEAR(reservated), 1, 0)) as amt2 ";
		$res_sql.="FROM ".$wpdb->prefix ."reservations";
		$reserved = $wpdb->get_results($res_sql);

		$sql = "SELECT id, price, number, childs, customp, arrival, departure, room FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '$start' < arrival AND '$end' > departure";
		$results = $wpdb->get_results($sql);
		$thisarray = array('all' => 0, 'adults' => 0, 'childs' => 0, 'paidcnt' => 0, 'price' => 0, 'paid' => 0 ); $lastarray = array('all' => 0, 'adults' => 0, 'childs' => 0, 'paidcnt' => 0, 'price' => 0, 'paid' => 0 );
		foreach($results as $result){
			$res = new Reservation($result->id, (array) $result);
			$midday = ($res->arrival + $res->departure)/2;
			$themode = 0;
			if(date('m.y') == date('m.y', $midday)){
				$mode = $thisarray;
				$themode = 1;
			} elseif(date('m.y', $startstamp) == date('m.y', $midday)){
				$mode = $lastarray;
				$themode = 2;
			}
			if($themode > 0){
				$res->Calculate();
				$mode['all']++;
				$mode['adults'] += $res->adults;
				$mode['childs'] += $res->childs;
				$mode['price'] += $res->price;
				$mode['paid'] += $res->paid;
				if(isset($mode['resource'][$res->resource])) $mode['resource'][$res->resource]++;
				else $mode['resource'][$res->resource] = 1;
				if($res->price == $res->paid) $mode['paidcnt']++;

				if($themode == 1) $thisarray = $mode;
				else $lastarray = $mode;
			}
		}
//			if($percent == 100) $color = '#1FB512';
//			elseif($percent > 100) $color = '#ab2ad6';kk  
//			else $color = '#F7B500';
//			$color = '#BC0B0B';

		$last_month_name = easyreservations_get_date_name(2, 0, (int) date('m', $startstamp)-1);
		if(!$stats){
			$stats = 'table-layout:fixed;border-top:0px;';
			$stats2 = 'white-space:nowrap !important;overflow:hidden !important';
		} else {
			$stats = 'margin-top:10px';
			$stats2 = 'text-align:center';
		}
		
		$table = '<table class="'.RESERVATIONS_STYLE.'" style="width:99%;max-width:99%;padding:0px;'.$stats.'">';
			$table .= '<tbody>';
				$table .= '<tr>';
					$table .= '<td class="statisticbox" style="padding:0px;'.$stats2.'">';
						if($only) $table = '';
						$table .= '<span>';
							$table .= '<b style="color:#1FB512"><span>'.$thisarray['all'].'</span> '.__( 'reservations' , 'easyReservations' ).'</b>';
							if($lastarray['all'] == 0) $round = $thisarray['all'];
							else {
								$round = (round(100/$lastarray['all']*$thisarray['all']));
								if($round <= 100) $round = ($round - 100);
								$round = $round.'%';
							}
							if($round > 0) $round = '+'.$round;
							$color = easyreservations_get_color((float) $round);
							$table .= $last_month_name.': <span style="font-size:14px;">'.$lastarray['all'].'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
						$table .= '</span>';
						$table .= '<span>';
							$table .= '<b><span>'.easyreservations_format_money($thisarray['price'], true, 0).'</span> '.__( 'price' , 'easyReservations' ).'</b>';
							if($lastarray['price'] == 0) $round = round($thisarray['price'],2);
							else {
								$round = (round(100/$lastarray['price']*$thisarray['price'], 2));
								if($round <= 100) $round = ($round - 100);
								$round = $round.'%';
							}
							if($round > 0) $round = '+'.$round;
							$color = easyreservations_get_color((float) $round);
							$table .= $last_month_name.': <span style="font-size:14px;">'.easyreservations_format_money($lastarray['price'], true, 0).'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
						$table .= '</span>';
						$table .= '<span>';
							$table .= '<b><span>'.easyreservations_format_money($thisarray['paid'], true, 0).'</span> '.__( 'paid' , 'easyReservations' ).'</b>';
							if($lastarray['paid'] == 0) $round = $thisarray['paid'];
							else {
								$round = (round(100/$lastarray['paid']*$thisarray['paid'])).'%';
								if($round <= 100) $round = ($round - 100).'%';
							}
							if($round > 0) $round = '+'.$round;
							$color = easyreservations_get_color((float) $round);
							$table .= $last_month_name.': <span style="font-size:14px;">'.easyreservations_format_money($lastarray['paid'], true, 0).'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
						$table .= '</span>';
						if(!$only || $only < 2){
							$table .= '<span>';
								$table .= '<b><span>'.$thisarray['paidcnt'].'/'.$thisarray['all'].'</span> '.__( 'paid' , 'easyReservations' ).'</b>';
								if($lastarray['all'] == 0) $round = $thisarray['paidcnt'];
								elseif($thisarray['all'] == 0) $round = $lastarray['paidcnt'];
								else {
									$amount1 = round(100/$thisarray['all']*$thisarray['paidcnt']);
									$amount2 = round(100/$lastarray['all']*$lastarray['paidcnt']);
									if($amount2 == 0) $round = $thisarray['paidcnt'];
									else{
										$round = (round(100/$amount2*$amount1));
										if($round <= 100) $round = ($round - 100);
										$round = $round.'%';
									}
								}
								if($round > 0) $round = '+'.$round;
								$color = easyreservations_get_color((float) $round);
								$table .= $last_month_name.': <span style="font-size:14px;">'.$lastarray['paidcnt'].'/'.$lastarray['all'].'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
							$table .= '</span>';
							$table .= '<span>';
								$table .= '<b><span>'.$reserved[0]->amt2.'</span> '.__( 'reserved' , 'easyReservations' ).'</b>';
								if($reserved[0]->amt == 0) $round = $reserved[0]->amt2;
								else {
									$round = (round(100/$reserved[0]->amt*$reserved[0]->amt2));
									if($round <= 100) $round = ($round - 100);
									$round = $round.'%';
								}
								if($round > 0) $round = '+'.$round;
								$color = easyreservations_get_color((float) $round);
								$table .= $last_month_name.': <span style="font-size:14px;">'.$reserved[0]->amt.'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
							$table .= '</span>';
							$table .= '<span id="easy_statistics_persons">';
								$pers1 = $thisarray['adults'] + $thisarray['childs'];
								$pers2 = $lastarray['adults'] + $lastarray['childs'];
								$table .= '<b><span>'.$thisarray['adults'].' +'.$thisarray['childs'].'</span> '.__( 'persons' , 'easyReservations' ).'</b>';
								if($pers2 == 0) $round = $pers1;
								else {
									$round = (round(100/$pers2*$pers1));
									if($round <= 100) $round = ($round - 100);
									$round = $round.'%';
								}
								if($round > 0) $round = '+'.$round;
								$color = easyreservations_get_color((float) $round);
								$table .= $last_month_name.': <span style="font-size:14px;">'.$lastarray['adults'].' +'.$lastarray['childs'].'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
							$table .= '</span>';
							foreach($the_rooms_array as $resource){
								$table .= '<span>';
									if(!isset($thisarray['resource'][$resource->ID])) $thisarray['resource'][$resource->ID] = 0;
									if(!isset($lastarray['resource'][$resource->ID])) $lastarray['resource'][$resource->ID] = 0;

									$table .= '<b><span>'.$thisarray['resource'][$resource->ID].'</span> '.__($resource->post_title).'</b>';
									if($lastarray['resource'][$resource->ID] == 0) $round = $thisarray['resource'][$resource->ID];
									else {
										$round = (round(100/$lastarray['resource'][$resource->ID]*$thisarray['resource'][$resource->ID]));
										if($round <= 100) $round = ($round - 100);
										$round = $round.'%';
									}
									if($round > 0) $round = '+'.$round;
									$color = easyreservations_get_color((float) $round);
									$table .= $last_month_name.': <span style="font-size:14px;">'.$lastarray['resource'][$resource->ID].'</span> <small style="font-weight:bold;font-size:12px;color:'.$color.'">('.$round.')</small></span>';
								$table .= '</span>';
							}
						} else echo $table;
					$table .= '</td>';
				$table .= '</tr>';
			$table .= '</tbody>';
		$table .= '</table>';
		if($only || $only < 2) echo $table;
	
		add_action('admin_print_footer_scripts', 'easyreservations_add_statistics_mini_script');
	}

	function easyreservations_add_statistics_mini_script(){
		echo <<<EOF
<style>
.statisticbox {
	width:99%;
	text-align:center;
}
.statisticbox > span {
	display: inline-block;
	padding: 10px 15px 10px 15px;
	font-family:Helvetica, Arial, sans-serif;
	font-weight: bold !important;
	text-align:left;
	display: inline-block;
	vertical-align: top;
	color:#777;
	font-size: 13px;
	text-transform: capitalize !important;
}
.statisticbox > span > b {
	padding-bottom: 6px;
	font-size: 17px;	
	font-weight: bold !important;
	color:#888;
	display: block;
	text-transform: capitalize !important;
}
.statisticbox > span > b > span {
	color:#555;
	font-size: 20px;	
}
.statisticbox > span > a {
	display:block;
	font-family: Arial,sans-serif;
	font-size:12px;
	color:#666;
	margin-bottom: 0px;
}
.statisticbox > span > a:hover {
	color:#222;
}
</style>
EOF;
	}

	function easyreservations_statistics_page(){
		easyreservations_load_resources();
		global $wpdb, $the_rooms_array;

		wp_enqueue_script('jquery-flot');
		wp_enqueue_script('jquery-flot-stack');
		wp_enqueue_script('jquery-flot-pie');
		wp_enqueue_script('jquery-flot-crosshair');
		?>
  <form name="resource" id="resource-select" method="GET" action="admin.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">
		<?php
		$array = array('mored' => 0, 'morey'=> 0);
		foreach ($_GET as $key => $value) {
			if(isset($array[$key])) unset($array[$key]);
			if($key !== "resource") echo("<input type='hidden' name='$key' value='$value'/>");
		}
		if(!empty($array)){
			foreach($array as $key => $value) echo("<input type='hidden' name='$key' value='$value'/>");
		}?>
      <h2>
				<?php echo __( 'Reservations Statistics' , 'easyReservations' );?>
          <select name="resource" style="font-size: 12px" onchange="document.getElementById('resource-select').submit();"><option value="0"><?php echo __( 'All resources' , 'easyReservations' );?> </option><?php echo easyreservations_resource_options((isset($_GET['resource'])) ? $_GET['resource'] : false, 1); ?></select>
      </h2>
	<?php

		$res_q = isset($_GET['resource']) && $_GET['resource'] > 0 ? "AND room = '".$_GET['resource']."'" : '';

		$count_all_reservations = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' ".$res_q); // number of total rows in the database
		if($count_all_reservations > 0){
			$nr = 0;
			$guest_count_yearly = ''; $reservated_yearly = ''; $daysaOptions = '';
			$morey = 0;
			if(isset($_GET['morey'])) $morey = $_GET['morey'];
			$maxy = 0;
			$start = time()-365*86400;
			$date=$start;
			while( $nr < 365){
				$date=$start+(86400*$nr)+($morey*86400);
				$lol = date("Y-m-d", $date);
	
				$daysaOptions .= "[\"".$nr."\",'".date(RESERVATIONS_DATE_FORMAT, $date)."','".date("M", $date)."<br>".date("y", $date)."'], ";
				$sql_A = $wpdb->get_results("SELECT sum(IF(DATE('$lol') BETWEEN DATE(arrival) AND DATE(departure) AND approve='yes', 1, 0)) as count, sum(IF(DATE('$lol') = DATE(reservated), 1, 0)) as reserv FROM ".$wpdb->prefix ."reservations WHERE 1=1 ".$res_q);
				$guest_count_yearly.=' [ "'.$nr.'" , '.$sql_A[0]->count.' ], ';
				$reservated_yearly.=' [ "'.$nr.'" , '.$sql_A[0]->reserv.' ], ';
				if($sql_A[0]->count > $maxy) $maxy = $sql_A[0]->count;
				if($sql_A[0]->reserv > $maxy) $maxy = $sql_A[0]->reserv;
				$nr++;
			}
			$maxy++;

			$countallreservationsall = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE 1=1 ".$res_q); // number of total rows in the database
			$countallreservationsfuture = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure > NOW() ".$res_q); // number of total rows in the database
			$countallreservationspast = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND departure < NOW() ".$res_q); // number of total rows in the database
			$countallreservationsreject = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' ".$res_q); // number of total rows in the database
			$countallreservationstrash = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='del' ".$res_q); // number of total rows in the database
			$countallreservationspending = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' ".$res_q); // number of total rows in the database

			$presults = $wpdb->get_results( "SELECT id, price, number, childs, customp, arrival, departure, room, approve FROM ".$wpdb->prefix ."reservations WHERE 1=1 ".$res_q );
			$pricesall=0; $personsall=0; $adults=0; $childs=0; $nightsall=0; $pricesfuture=0; 	$pricespast=0; $paidall= 0; $paidfully = 0; $paidpart = 0; $taxrate = 0;
			$rooms_array = null; $status_array = null; $approveJSarray = ''; $resourceJSarray  = '';

			foreach($presults as $presult){
				$res = new Reservation($presult->id, (array) $presult);
				if(isset($status_array[$res->status])) $status_array[$res->status] = $status_array[$res->status] + 1;
				else $status_array[$res->status] = 1;
				if($res->status == 'yes'){
					$adults+=$res->adults;
					$childs+=$res->childs;
					if(isset($rooms_array[$res->resource])) $rooms_array[$res->resource] = $rooms_array[$res->resource]+1;
					else $rooms_array[$res->resource] = 1;
					$personsall+=$res->adults+$res->childs;
					$nightsall+=$res->times;
					$res->Calculate();
					$pricesall+=$res->price;
					if(isset($res->taxamount)) $taxrate+=$res->taxamount;
					$paidall+=$res->paid;
					if($res->paid == $res->price) $paidfully++;
					elseif($res->paid > 0) $paidpart++;
					if($res->departure > time()) $pricesfuture+=$res->price;
					else $pricespast+=$res->price;
				}
			}

			$count_of_rooms = count($the_rooms_array);
			foreach($the_rooms_array as $room){
				$count = 0; $amount = 0;
				if(isset($rooms_array[$room->ID])){
					$count = $rooms_array[$room->ID];
					$amount=round(100/$count_of_rooms*($rooms_array[$room->ID]), 2);
				}
				$resourceJSarray.='{ label: "'.__($room->post_title).' - '.$count.'",  data: [[1,'.$amount.']]},';
			}

			if(isset($status_array[''])){
				$percent = round(100/$countallreservationsall*$status_array[''], 2);
				$approveJSarray.='{ label: "Pending - '.$status_array[''].'",  data: [[1,'.$percent.']], color: "rgb(116,166,252)"},';
			}
			if(isset($status_array['yes'])){
				$percent = round(100/$countallreservationsall*$status_array['yes'], 2);
				$approveJSarray.='{ label: "Approved - '.$status_array['yes'].'",  data: [[1,'.$percent.']], color: "rgb(94,201,105)"},';
			}
			if(isset($status_array['no'])){
				$percent = round(100/$countallreservationsall*$status_array['no'], 2);
				$approveJSarray.='{ label: "Rejected - '.$status_array['no'].'",  data: [[1,'.$percent.']], color: "#cd4b4b"},';
			}
			if(isset($status_array['del'])){
				$percent = round(100/$countallreservationsall*$status_array['del'], 2);
				$approveJSarray.='{ label: "Trashed - '.$status_array['del'].'",  data: [[1,'.$percent.']], color: "#888"},';
			}

			$percent = round(100/$countallreservationsall*$paidfully, 2);
			$percent2 = round(100/$countallreservationsall*$paidpart, 2);
			$paidJSarray ='{ label: "Paid - '.$paidfully.'",  data: [[1,'.$percent.']], color: "rgb(94,201,105)"},{ label: "Partially paid - '.$paidpart.'",  data: [[1,'.$percent2.']], color: "rgb(116,166,252)"},{ label: "Unpaid - '.($countallreservationsall-$paidfully-$paidpart).'",  data: [[1,'.(100-$percent-$percent2).']], color: "#cd4b4b"}';
			
			$percent = round(100/$personsall*$adults, 2);
			$percent2 = round(100/$personsall*$childs, 2);
			$persJSarray ='{ label: "Adults - '.$adults.'",  data: [[1,'.$percent.']], color: "rgb(94,201,105)"},{ label: "Children - '.$childs.'",  data: [[1,'.$percent2.']], color: "rgb(116,166,252)"}';

			$personsperreservation=$personsall/$count_all_reservations;
			$nightsperreservation=$nightsall/$count_all_reservations;
			$priceperreservation = easyreservations_format_money($pricesall/$count_all_reservations, 1);
			$paid_per_reservation = easyreservations_format_money($paidall/$count_all_reservations, 1);
			$countApproved = ''; $countRejected = ''; $countPending = ''; $daysOptions = '';
			$maxall = 0;
			$mored = 0;
			if(isset($_GET['mored'])) $mored = $_GET['mored'];
			for($ii = 0; $ii < 30; $ii++){
				$daysOptions .= "['".date("d", time()+($ii*86400)+($mored*86400))."<br>".date("M", time()+($ii*86400)+($mored*86400))."'], ";
				$day=date("Y-m-d", time()+($ii*86400)+($mored*86400));
				$count_appr = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '%s' BETWEEN arrival AND departure ".$res_q, $day));
				$countApproved .=  '['.$ii.', '.$count_appr.'], ';
				$count_rej = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' AND '%s' BETWEEN arrival AND departure ".$res_q, $day));
				$countRejected .=  '['.$ii.', '.$count_rej.'], ';
				$count_pend = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '%s' BETWEEN arrival AND departure ".$res_q, $day));
				$countPending .=  '['.$ii.', '.$count_pend.'], ';
				if(($count_pend+$count_rej+$count_appr) > $maxall) $maxall = ($count_pend+$count_rej+$count_appr);
			}
			$maxall++;
		?>
		<script type="text/javascript">
			var rdata = [<?php echo $resourceJSarray; ?>];
			var sdata = [<?php echo $approveJSarray; ?>];
			var pdata = [<?php echo $paidJSarray; ?>];
			var adata = [<?php echo $persJSarray; ?>];
			function plotWithOptions(data) {
				jQuery.plot(jQuery("#percentrooms"), data, {
					series: {
						pie: { 
							innerRadius: 0.3,
							show: true,
							radius: 1,
							label: {
								show: true,
								radius: 3/4,
								formatter: function(label, series){
									return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
								},
								background: { opacity: 0.5 }
							}
						}
					},
					legend: {
						show: false
					},
					grid: {
						hoverable: true
					}
				});
			}

			function changePie(thedat, e){
				if(thedat == 0) plotWithOptions(rdata);
				else if(thedat == 1) plotWithOptions(sdata);
				else if(thedat == 2) plotWithOptions(pdata);
				else if(thedat == 3) plotWithOptions(adata);
				jQuery('.statisticnavi a').removeClass('isactive');
				jQuery(e).addClass('isactive');
			}

			jQuery(document).ready(function(){
				var bars = true, lines = false, steps = false;
				var d1 = [<?php echo $countApproved; ?>];
				var d2 = [<?php echo $countRejected; ?>];
				var d3 = [<?php echo $countPending; ?>];
				var days = [<?php echo $daysOptions; ?>];
				jQuery.plot(jQuery("#nextdays"), [ { data: d1, label: "<?php echo addslashes(ucfirst(__('approved', 'easyReservations'))); ?>", color: "rgb(94,201,105)"}, { data: d2, label: "<?php echo addslashes(ucfirst(__('rejected', 'easyReservations'))); ?>", color: "rgb(229,39,67)"}, { data: d3, label: "<?php echo addslashes(ucfirst(__('pending', 'easyReservations'))); ?>", color: "rgb(116,166,252)"} ], {
					series: {
						stack: true,
						lines: { show: lines, fill: true, steps: steps },
						bars: { show: bars, barWidth: 0.6, align: "center", lineWidth:0 }
					},
					grid: {},
					yaxis: { min: 0, max: <?php echo $maxall; ?> },
					xaxis: { tickFormatter: function (v) { return days[v]; }, tickDecimals: 0, ticks:30 }
				});

				plotWithOptions(rdata);

				var guesy = [ <?php echo $guest_count_yearly; ?> ];
				var resery = [<?php echo $reservated_yearly; ?>];
				var daysy = [<?php echo $daysaOptions; ?>];
					var plot = jQuery.plot(jQuery("#container"), [ { data: guesy, label: "Approved  = 000", color: "#44bc3c" }, { data:  resery, label: "Reserved = 000", color: "#1f68b7" }, { data:  daysy, label: "Date = ", color: "#1f68b7" } ], {
						series: {
							lines: { show: true },
							points: { show: false }
						},
						grid: { hoverable: true, clickable: true },
						yaxis: { min: -1, max: <?php echo $maxy; ?>  },
						crosshair: { mode: "x" },
						xaxis: { tickFormatter: function (v) { if(daysy[v]) return daysy[v][2]; else return ''; }, ticks: 24 }
					});

					var legends = jQuery("#container .legendLabel");
					legends.each(function () { jQuery(this).css('width','100px');});

					var updateLegendTimeout = null;
					var latestPosition = null;
					function updateLegend() {
						updateLegendTimeout = null;
						var pos = latestPosition;
						var axes = plot.getAxes();
						if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
								pos.y < axes.yaxis.min || pos.y > axes.yaxis.max)
								return;

						var i, j, dataset = plot.getData();
						for (i = 0; i < dataset.length; ++i) {
							var series = dataset[i];
							// find the nearest points, x-wise
							for (j = 0; j < series.data.length; ++j)
								if (series.data[j][0] > pos.x) break;

							var y, p1 = series.data[j - 1], p2 = series.data[j];
							if (p1 == null) y = p2[1];
							else if (p2 == null) y = p1[1];
							else {
								if(typeof p1[1] === 'number') y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
								else y = p1[1];
							} 
							if(typeof y === 'number') y = Math.round(y);
							legends.eq(i).text(series.label.replace(/=.*/, "= " + y));
						}
					}

					jQuery("#container").bind("plothover",  function (event, pos, item) {
							latestPosition = pos;
							if (!updateLegendTimeout)
									updateLegendTimeout = setTimeout(updateLegend, 50);
					});
			});
		</script>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 99%">
			<thead>
				<tr>
					<th>
						<?php echo __( 'Upcoming reservations' , 'easyReservations' ); ?> <i><?php echo date('Y', time()+($mored*86400)); ?></i>
						<span class="statsnavi" style="float:right"><a onclick="document.forms.resource.mored.value = <?php echo $mored-30; ?>;document.forms.resource.submit()" href="javascript:"><</a> <a onclick="document.forms.resource.mored.value = 0;document.forms.resource.submit()" href="javascript:">0</a> <a onclick="document.forms.resource.mored.value = <?php echo $mored+30; ?>;document.forms.resource.submit()" href="javascript:">></a></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:0px;background-color:#fff;padding-top:13px">
						<div id="nextdays" style="margin: 0px;width: 99%;height:350px"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php echo easyreservations_statistics_mini(true);?>
		<table style="width: 99%; margin: 10px 0px	" cellspacing="0" cellpadding="0">
			<tr>
				<td style="width: 68%;height:100%;vertical-align: top">
					<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 99%; height:100%" >
						<tbody>
							<tr style="font-size:13px; height:100%" class="alternate">
								<td>
									<div class="statisticnavi">
										<a href="javascript:" onclick="changePie(0, this);"><?php echo __( 'Resource' , 'easyReservations' ); ?></a> &#8226;
										<a href="javascript:" onclick="changePie(1, this);"><?php echo __( 'Status' , 'easyReservations' ); ?></a> &#8226;
										<a href="javascript:" onclick="changePie(2, this);"><?php echo __( 'Paid' , 'easyReservations' ); ?></a> &#8226;
										<a href="javascript:" onclick="changePie(3, this);"><?php echo __( 'Persons' , 'easyReservations' ); ?></a>
									</div>
									<div id="percentrooms" style="height:527px;"></div>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 1%;"></td>
				<td style="width: 32%;vertical-align: top;height:100%">
					<table class="<?php echo RESERVATIONS_STYLE; ?> statisticstable" style="width:100%; height:100%;" >
						<thead>
							<tr>
								<th colspan="2"><?php printf ( __( 'Detailed Statistics' , 'easyReservations' ));?></th>
							</tr>
						</thead>
						<tbody>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Total reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsall; ?> </b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td style="font-size:13px"> - <?php printf ( __( 'Future approved reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsfuture; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td style="font-size:13px;"> - <?php printf ( __( 'Past approved reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationspast; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> - <?php printf ( __( 'Pending reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationspending; ?></b></td>
							</tr>
							<tr>
								<td style="font-size:13px;"> - <?php printf ( __( 'Rejected reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationsreject; ?></b></td>
							</tr>
							<tr  class="alternate"  style="font-size:13px;">
								<td> - <?php printf ( __( 'Trashed reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $countallreservationstrash; ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td>&#216; <?php printf ( __( 'Guests per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo round($personsperreservation, 2); ?></b></td>
							</tr>
							<tr  class="alternate" style="font-size:13px;">
								<td>&#216; <?php echo __( 'Times per reservations' , 'easyReservations' );?>:</td>
								<td style="text-align:right;"><b><?php echo round($nightsperreservation, 2); ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td><?php printf ( __( 'Paid amount of all reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo easyreservations_format_money($paidall, 1); ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td> &#216; <?php printf ( __( 'Paid per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo $paid_per_reservation; ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td><?php printf ( __( 'Revenue of all reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo easyreservations_format_money($pricesall,1); ?></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of future reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo easyreservations_format_money($pricesfuture, 1); ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> - <?php printf ( __( 'Revenue of past reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo easyreservations_format_money($pricespast, 1); ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> - <?php printf ( __( 'Taxes' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><b><?php echo easyreservations_format_money($taxrate, 1); ?></b></td>
							</tr>
							<tr class="alternate" style="font-size:13px;">
								<td> - <?php echo __( 'Gross' , 'easyReservations' );?>:</td>
								<td style="text-align:right;"><b><u><?php echo easyreservations_format_money($pricesall-$taxrate, 1); ?></u></b></td>
							</tr>
							<tr style="font-size:13px;">
								<td> &#216; <?php printf ( __( 'Revenue per reservations' , 'easyReservations' ));?>:</td>
								<td style="text-align:right;"><i><?php echo __( 'Net' , 'easyReservations' );?></i> &nbsp;<b><?php echo $priceperreservation; ?></b><br><i><?php echo __( 'Gross' , 'easyReservations' );?></i> &nbsp;<b><?php echo easyreservations_format_money(($pricesall-$taxrate)/$count_all_reservations,1); ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 35%;"></td>
			</tr>
		</table>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 99%">
			<thead>
				<tr>
					<th>
						<?php echo __( 'Approved and reservated graph' , 'easyReservations' ); ?> <?php echo date('M Y', $start+($morey*86400)).' - '.date('M Y', $date); ?>
						<span class="statsnavi" style="float:right"><a onclick="document.forms.resource.morey.value = <?php echo $morey-60; ?>;document.forms.resource.submit()" href="javascript:"><</a> <a onclick="document.forms.resource.morey.value = 0;document.forms.resource.submit()" href="javascript:">0</a> <a onclick="document.forms.resource.morey.value = <?php echo $morey+60; ?>;document.forms.resource.submit()" href="javascript:">></a></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0px; padding:13px 0px 0px 0px;background-color:#fff;">
						<div id="container" style="margin:0px; padding:0px;height:350px;"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		} else echo '<br><div class="error"><p>'.__( 'Add reservations first' , 'easyReservations' ).'</p></div>';
		echo '</form>';
	}
	
	add_action('wp_dashboard_setup', 'easyreservations_add_dashboard_widgets' );

	function easyreservations_dashboard_widget_function() {
		echo '<style>#easyreservations_dashboard_widget .inside { margin:0px; padding:0px; } #er-dash-table thead th { background:#EAEAEA;border-top:1px solid #ccc;border-bottom:1px solid #ccc; padding:3px !important; } #er-dash-table tbody tr:nth-child(odd) { background:#fff } #er-dash-table tbody td { font-weight:normal !important; padding:3px !important; }</style>';?>
		<script>
			function navibackground(a){
				var e = document.getElementsByName('sendajax'); 
				for(var i=0;i<e.length;i++){ e[i].style.color = '#21759B';e[i].style.fontWeight='normal';} 
				a.style.color='#000';
				a.style.fontWeight='bold';
			}
		</script>
		<div class="statisticbox" style="text-align:right;border-bottom:1px solid #dfdfdf;vertical-align: middle;white-space: nowrap !important;overflow: hidden !important;">
			<span style="color:#3BB0E2; font-weight: bold; font-size: 25px;padding:22px 5px 22px 22px;"><?php $pen = easyreservations_get_pending(); echo $pen;?></span>
			<?php echo easyreservations_statistics_mini(false,1); ?>
		</div>
		<div id="er-dash-navi" style="width:100%;padding:4px;">
			<a id="current" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Current</a> | 
			<a id="leaving" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Leaving today</a> | 
			<a id="arrival" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Arrival today</a> | 
			<a id="pending" name="sendajax" style="cursor:pointer;font-size:12px;" onclick="navibackground(this)">Pending <b><?php echo $pen; ?></b></a> | 
			<a id="future" name="sendajax" style="cursor:pointer" onclick="navibackground(this)">Future</a>
			<span id="er-loading" style="float:right;"></span>
		</div>
		<div id="easy-dashboard-div"></div><?php
	}

	function easyreservations_add_dashboard_widgets() {
		wp_add_dashboard_widget('easyreservations_dashboard_widget', 'easyReservations Dashboard Widget', 'easyreservations_dashboard_widget_function');	
	}

	/* *
	*	Dashboards ajax request
	*/

	function easyreservations_send_dashboard(){
		$nonce = wp_create_nonce( 'easy-dashboard' );
		?><script type="text/javascript" >	
		jQuery(document).ready(function(jQuery) {
			jQuery('a[name|="sendajax"]').click(function() {
				var loading = '<img style="margin-right:7px" src="<?php echo RESERVATIONS_URL; ?>images/loading.gif">';
				jQuery("#er-loading").html(loading);
				var data = {
					action: 'easyreservations_send_dashboard',
					security: '<?php echo $nonce; ?>',
					mode: jQuery(this).attr('id')
				};
				jQuery.post(ajaxurl, data, function(response) {
					jQuery("#easy-dashboard-div").html(response);
					jQuery("#er-loading").html('');
					return false;
				});
			});
		});</script><?php
	}

	add_action('admin_head-index.php', 'easyreservations_send_dashboard');

	/* *
	*	Dashboards ajax callback
	*/
	function easyreservations_send_dashboard_callback() {
		easyreservations_load_resources();
		global $wpdb, $the_rooms_array;
		check_ajax_referer( 'easy-dashboard', 'security' );
		$mode =  $_POST['mode'];
		$dateToday = date("Y-m-d", time());

		if($mode == "current"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE '$dateToday' BETWEEN arrival AND departure AND approve='yes'");
		} elseif($mode == "leaving"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE DATE(arrival) = '$dateToday'  AND approve='yes'");
		} elseif($mode == "pending"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE arrival > NOW() AND approve=''");
		} elseif($mode == "arrival"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE DATE(departure) = '$dateToday' AND approve='yes'");
		} elseif($mode == "future"){
			$query = $wpdb->get_results("SELECT id, name, arrival, departure, room, number, childs FROM ".$wpdb->prefix ."reservations WHERE  arrival > NOW() AND approve='yes'");
		}
		
		if(!empty($query)){

		$table = '<table id="er-dash-table" style="width:100%;text-align:left;font-weight:normal;border-spacing:0px">';
			$table .= '<thead>';
				$table .= '<tr>';
					$table .= '<th>'.__( 'Name' , 'easyReservations').'</th>';
					$table .= '<th>'.__( 'Date' , 'easyReservations').'</th>';
					$table .= '<th>'.__( 'Resource' , 'easyReservations').'</th>';
					$table .= '<th style="text-align:center">'.__( 'Persons' , 'easyReservations').'</th>';
					$table .= '<th style="text-align:right">'.__( 'Price' , 'easyReservations').'</th>';
				$table .= '</tr>';
			$table .= '</thead>';
			$table .= '<tbody>';

		foreach($query as $num => $reservation){
			$res = new Reservation($reservation->id, (array) $reservation);
			$res->Calculate();
			if($num % 2 == 0) $class="odd";
			else $class="even";
				$table .= '<tr class="'.$class.'">';
					$table .= '<td><a href="admin.php?page=reservations&view='.$res->id.'">'.$res->name.'</a></td>';
					$table .= '<td>'.date(RESERVATIONS_DATE_FORMAT, $res->arrival).' - '.date(RESERVATIONS_DATE_FORMAT, $res->departure).' ('.$res->times.')</td>';
					$table .= '<td>'.__($the_rooms_array[$res->resource]->post_title).'</td>';
					$table .= '<td style="text-align:center;">'.$res->adults.' ('.$res->childs.')</td>';
					$table .= '<td style="text-align:right">'.$res->formatPrice(true).'</td>';
				$table .= '</tr>';
		}

			$table .= '</tbody>';
		$table .= '</table>';
		
		echo $table;
		}

		// IMPORTANT: don't forget to "exit"
		exit;
	}

	add_action('wp_ajax_easyreservations_send_dashboard', 'easyreservations_send_dashboard_callback');

}
?>