<?php
	function easyreservations_resource_tutorial() {
		$handler = array('#idgroundprice', '#idchilddiscount', '#idbilling','#idtaxes','#idcountres','#availabilityby', '#idpersons', '#idpermission', '#show_add_price_link', 'select[name="price_filter_imp"]','*[name="filter_form_usetime_checkbox"]', '#price_filter_cond_range', '#price_filter_cond_unit', '#show_add_discount_link', '#filter_form_discount_type', '#filter_form_discount_cond', '#filter_form_discount_mode', '#filter-price-mode','#filter-price-field', '#show_add_avail_link', '#show_add_req_link');
		$content = array(
			'<h3>Base price</h3><p>The amount of money that one time (hour/day/week) of reservation costs if no filter gets applied. Must be positive.<p>',
			'<h3>Child discount</h3><p>The price that gets subtracted by this amount and multiplied by the amount of children if <b>Price per person</b> is enabled. Afterwards the custom prices, conditional filters, coupons and taxes gets calculated.</p>',
			'<h3>Billing</h3><p>Select the interval of bookings that defines how many times the base price gets applied. Started times counts as full ones. Price filters lower than the selected interval wont work.</p>',
			'<h3>Taxes</h3><p>This tag is to gather custom information. That can happen by the form types text fields, text areas, radio buttons, selects or checkboxes. They can be required.</p>',
			'<h3>Count of resource</h3><p>Select how often this resource can be reserved at the same time. In the box below you can define names for them.</p>',
			'<h3>Availability</h3><p><b>Per object</b><br>The resource count determines how often the resource can get reserved at the same time independent from the amount of persons that reserves. Each unit can have an name or number.</p><p><b>Per person</b><br>The resource count determines how many persons can reserve the resource at the same time. In this mode the resource will just have one row in the overview that shows the amount of reservations.</p>',
			'<h3>Requirements</h3><p>Set the required times and persons. Persons means adults plus children\\\'s.</p>',
			'<h3>Permission</h3><p>Set the required permission to admin this resource. Will effect reservations dashboard, resource settings, statistics and dashboard widget.</p>',
			'<h3>Filters</h3><p>With the filters you can define the price and the availability of the resource by flexible conditions.</p>',
			'<h3>Priority</h3><p>The priority defines the order in that the filters gets checked. Filters that change the base price gets checked and applied once per billing unit while discounts and extra price only once per type of condition. In all cases only the first filter matched gets applied so the order is very important.</p>',
			'<h3>Time</h3><p>With this condition you can check for date ranges or flexible date units.</p>',
			'<h3>Date range</h3><p>The selected hour is only important in hourly billing. In weekly billing the date condition will only work on arrival day and each seventh thereafter.</p>',
			'<h3>Units</h3><p>The units works by OR in the same kind of unit and by AND in relation with other kinds. That means that you can change the price of monday and tuesday in june in 2012, but cant change it for each monday and the complete june in 2012. Together with the priorities and unlimited filters every price system is adjustable.</p>',
			'<h3>Conditions</h3><p>If you add more filter with the same type of condition only the first condition match from high to low will get applied, once for extra prices and once for discounts.</p>',
			'<h3>Type of condition</h3><p>Choose the type of information you want to charge. Recurring guest counts approved reservations by the same email.</p>',
			'<h3>Condition</h3><p>The amount that must match or being greater then the condition type for the filter to get applied.</p>',
			'<h3>Mode</h3><p>Select how the amount should be calculated. Persons means adults plus children\\\'s.</p>',
			'<h3>Type of filter</h3><p>Select if the filter should change the base price of the resource and get applied once per billing unit or a discount or an extra charge.</p>',
			'<h3>Amount</h3><p>Can be positive or negative. No percentage sign or comma. <b>213.43/-23</b></p>',
			'<h3>Unavailability Filter</h3><p>Define unavailable time. Selection of time works same as for price filters.</p>',
			'<h3>Requirements Filter</h3><p>With this filter you can change the requirements of the resource by date range or date units.</p>',
		);
		$execute = array('','','','','easy_add_tax(1, jQuery(\'#idtaxes\').nextSibling);', 'easy_add_tax(2, jQuery(\'#idtaxesvalue span:last\'));', '', 'show_add_price();document.filter_form.reset();document.getElementById(\'filter-price-field\').value = 100;', 'show_use_time(true);', '', '', '', 'show_use_condition(true);', '', '', '', '','', 'javascript:show_add_avail();document.filter_form.reset();', 'reset_filter_form()');
		$at = array('top','top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top');
		echo easyreservations_execute_pointer(18, $handler, $content, $at, $execute);
	}
?>