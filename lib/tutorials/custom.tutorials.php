<?php
	function easyreservations_custom_tutorial() {
		$handler = array('#wpbody a.current','#custom_price_field', '#custom_field_type', 'input[name="value[]"]', 'input[name="price[]"]', '#add_new_custom', '#add_if_clause', 'select[name="if_cond_type[]"]', 'select[name="if_cond_happens[]"]', 'select[name="if_cond_mult[]"]', 'input[name=custom_field_unused]');
		$content = array(
				'<h3>Custom fields</h3><p>With custom fields you can add your own fields to the form easily. The information\\\'s gathered can be used through the whole system like in the emails, invoices and when the guest edits his reservation.</p>',
				'<h3>Price fields</h3><p>They can have an influence on the price as well.<p>',
				'<h3>Form elements</h3><p>Normal custom fields can be any form element while price fields can only be checkboxes, radio buttons or select fields.</p>',
				'<h3>Value</h3><p>The value is the default value for text fields and areas, the information that gets saved when the checkbox is saved or an option to select for select fields and radio buttons.</p>',
				'<h3>Price</h3><p>The price if the checkbox is checked or the option is selected. When conditions are set up but not met this amount also gets applied instead of the conditions one.</p>',
				'<h3>Add new option</h3><p>For select fields and radio buttons you can add new options. They can be dragged and dropped to order them.</p>',
				'<h3>Conditions</h3><p>Price fields options can change the applied price based on conditions. The first met conditions amount gets applied instead of the price fields one. They can be dragged and dropped as well.</p>',
				'<h3>Type of condition</h3><p>Select which information should affect the price and the operator and number to test it against. An example would be "If persons is greater as 4".</p>',
				'<h3>Chained conditions</h3><p>With AND and OR you can chain multiple conditions together to only change the price when multiple or one of multiple conditions is met. With THEN you define the price and end the conditions chain, so another condition after a THEN is always independent. Also nothing happens if a conditions chain ends without defining a price.</p>',
				'<h3>Multiplier</h3><p>You can also multiply the conditions amount as in the resources filters.</p>',
				'<h3>Unused</h3><p>If you add this custom field to your email but the guest hasn\\\'t chosen or filled it out this value get inserted in the email instead.</p>',
		);
		$at = array('top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top');
		$execute = array('', 'jQuery("#custom_price_field").prop("checked", true);', 'jQuery("#custom_field_type").val("select");custom_field_value(false)', '', '', 'add_if_clause("1");', '', 'clause_happens_select("price","1", 1, true)', '', '', '');
		echo easyreservations_execute_pointer(11, $handler, $content, $at, $execute);
	}
?>