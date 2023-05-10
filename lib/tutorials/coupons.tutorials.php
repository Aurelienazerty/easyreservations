<?php
	function easyreservations_coupons_tutorial() {
		$handler = array('#wpbody a.current','input[name="name"]', 'input[name="to"]', 'input[name="amount"]', 'input[name="amount"]');
		$content = array(
			'<h3>Coupons</h3><p>After adding <b>[coupons]</b> to the forms your guests can enter here defined codes to get discounts.</p>',
			'<h3>Code</h3><p>Enter the code here. The generator adds randomized codes by numbers and big letters.</p>',
			'<h3>Available</h3><p>Define the date range that this code is usable.</p>',
			'<h3>Amount</h3><p>The amount can be a price or percentage and positive or negative. <b>154.8/10%/-584/-15%</b></p>',
		);
		$at = array('top', 'top', 'top', 'top');
		$execute = array('randomString(document.reservation_coupon_settings.generator.value);', 'document.reservation_coupon_settings.from.value="21.01.2020";document.reservation_coupon_settings.to.value="11.08.2020";', 'document.reservation_coupon_settings.amount.value="-15%"');
		echo easyreservations_execute_pointer(4, $handler, $content, $at, $execute);
	}
?>