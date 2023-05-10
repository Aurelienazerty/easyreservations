<?php
	function easyreservations_paypal_tutorial() {
		$handler = array('#wpbody a.current','select[name="er_pay_modus"]', 'select[name="language"]', 'input[name="deposit_payown"]');
		$content = array(
				'<h3>PayPal</h3><p>After turning on and setting an owner the <b>Buy now!</b> button will appear after successful reservations and at the guest editing panels status bar.</p>',
				'<h3>Mode</h3><p>The Sandbox is the perfect way to try out your payment gateway without using real money. You just need a free <a target="_blank" href="http://developer.paypal.com">Sandbox Account</a> and set up a seller and a buyer fake account.<p>',
				'<h3>Language</h3><p>Select the language in that the PayPal site should be before your guests loggin in to purchase.</p>',
				'<h3>Deposit</h3><p>With the deposit function you can let your guests only pay a part of the calculated price. You can let them choose to pay the full price, to pay a percentage amount or select from multiple or even let them choose the amount by themselves.</p>',
		);
		$at = array('top', 'top', 'top', 'top');
		echo easyreservations_execute_pointer(4, $handler, $content, $at);
	}
?>