<?php
	wp_enqueue_script('jquery-flot');
	wp_enqueue_script('jquery-flot-stack');
	global $wpdb;

	$count_reserved = ''; $count_approved = ''; $count_rejected = ''; $count_pending = ''; $daysOptions = ''; $daysOptionsPast = '';
	$maxres = 0; $maxall = 0;
	$dayNames = easyreservations_get_date_name(0, 3);
	for($ii = 0; $ii < 8; $ii++){
		$daysOptions .= "['".$dayNames[date("N", time()+($ii*86400))-1]."<br>".date("d.m", time()+($ii*86400))."'], ";
		$daysOptionsPast .= "['".$dayNames[date("N", time()-604800+($ii*86400))-1]."<br>".date("d.m", time()-604800+($ii*86400))."'], ";
		$day=date("Y-m-d H:i:s", time()+($ii*86400));
		$dayPastAnf=date("Y-m-d H:i:s", strtotime(date("d.m.Y", time()))-604800+($ii*86400));
		$dayPastEnd=date("Y-m-d H:i:s", (strtotime(date("d.m.Y", time()))+86399)-604800+($ii*86400));
		$count_res =  $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE reservated BETWEEN '%s' AND '%s'", $dayPastAnf, $dayPastEnd));
		if($count_res > $maxres) $maxres = $count_res;
		$count_reserved .= '['.$ii.', '.$count_res.'], ';
		$count_appr = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND '%s' BETWEEN arrival AND departure", $day));
		$count_approved .= '['.$ii.', '.$count_appr.'], ';
		$count_rej = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='no' AND '%s' BETWEEN arrival AND departure", $day));
		$count_rejected .= '['.$ii.', '.$count_rej.'], ';
		$count_pend = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix ."reservations WHERE approve='' AND '%s' BETWEEN arrival AND departure",$day));
		$count_pending .= '['.$ii.', '.$count_pend.'], ';
		if(($count_pend+$count_rej+$count_appr) > $maxall) $maxall = ($count_pend+$count_rej+$count_appr);
	}
	$maxres++; $maxall++;
?><script type="text/javascript">
<?php if( $show['show_upcoming'] == 1 ){ ?>
  var bars = true, lines = false, steps = false;
	var d1 = [<?php echo $count_approved; ?>];
	var d2 = [<?php echo $count_rejected; ?>];
	var d3 = [<?php echo $count_pending; ?>];
	var days = [<?php echo $daysOptions; ?>];
	jQuery(document).ready(function(){
		jQuery.plot(jQuery("#container"), [ { data: d1, label: "<?php echo addslashes(ucfirst(__('approved', 'easyReservations'))); ?>", color: "rgb(94,201,105)"}, { data: d2, label: "<?php echo addslashes(ucfirst(__('rejected', 'easyReservations'))); ?>", color: "rgb(229,39,67)"}, { data: d3, label: "<?php echo addslashes(ucfirst(__('pending', 'easyReservations'))); ?>", color: "rgb(116,166,252)"} ], {
			series: {
				stack: true,
				lines: { show: lines, fill: true, steps: steps },
				bars: { show: bars, barWidth: 0.6, align: "center", lineWidth:0 }
			},
			grid: {hoverable: true, clickable: true,axisMargin: 50},
			yaxis: { min: 0, max: <?php echo $maxall; ?>, tickDecimals: 0 },
			xaxis: { tickFormatter: function (v) { return days[v]; }, tickDecimals: 0 }
		});
	});
<?php } if($show['show_new'] == 1 ){ ?>
	var s1 = [<?php echo $daysOptionsPast; ?>];
	var s2 = [<?php echo $count_reserved; ?>];
	jQuery(document).ready(function(){
		jQuery.plot(jQuery("#container2"), [ { data: s1, label: "sin(x)"}, { data:  s2, label: "cos(x)" } ], {
			series: {
				lines: { show: true },
				points: { show: true }
			},
			legend: {show:false},
			grid: { hoverable: true, clickable: true },
			yaxis: { min: 0, max: <?php echo $maxres; ?> },
			xaxis: { tickFormatter: function (v) { return s1[v]; } }
		});
	});
<?php } ?>
</script>