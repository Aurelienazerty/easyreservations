<?php
require('../../../../../wp-load.php');
global $wpdb;
if ( !is_user_logged_in() || !current_user_can('edit_posts') )
	wp_die(__("You are not allowed to access this file.", "easyReservations"));

	$form = "SELECT option_name FROM ".$wpdb->prefix ."options WHERE option_name like 'reservations_form_%' "; // Get User made Forms
	$formresult = $wpdb->get_results($form);
	$formoptions = '<option value="">'.__("Standard", "easyReservations").'</option>';
	foreach( $formresult as $result )	{
		$formcutedname=str_replace('reservations_form_', '', $result->option_name);
		if($formcutedname!=""){
			$formoptions.= '<option value=" '.$formcutedname.'">'.$formcutedname.'</option>';
		}
	}

	$roomsoptions = easyreservations_resource_options('',0,'',true);
?><html xmlns="http://www.w3.org/1999/xhtml" style="background:#fff">
	<head>
	<title><?php _e("easyReservations Shortcode Creator", "easyReservations"); ?></title>
	<script language="javascript" type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.js'></script>
	<script language="javascript" type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-admin/js/common.js'></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/hoverIntent.js'></script>
	<script type='text/javascript'>
		/* <![CDATA[ */
		var commonL10n = {"warnDelete":"You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete."};
		/* ]]> */
	</script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/jquery.color.min.js'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/ui/jquery.ui.widget.min.js'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/jquery/ui/jquery.ui.position.min.js'></script>
	<script type='text/javascript' src='<?php echo get_option('siteurl'); ?>/wp-includes/js/wp-pointer.js'></script>
	<script type='text/javascript'>
		/* <![CDATA[ */
		var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"<?php echo addslashes(get_option('siteurl')); ?>\/wp-includes\/js\/thickbox\/loadingAnimation.gif","closeImage":"http:\/\/127.0.0.1\/er\/wp-includes\/js\/thickbox\/tb-close.png"};
		/* ]]> */
	</script>
	<style>
		input[tcype="text"], select {
			padding:3px;
			width: 120px;
			background-color: #FFFFFF;
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			border-color: #DFDFDF;
			border-radius: 3px 3px 3px 3px;
			border-style: solid;
			border-width: 1px;
		}

		.easyreservations_tiny_popUp {
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			line-height: 30px;
		}

		.easyreservations_tiny_popUp td {
			font-family: Arial,"Bitstream Vera Sans",Helvetica,Verdana,sans-serif !important;
			font-size: 13px !important;
			color: #666666 !important;
		}

		label {
			font-weight:bold;
			color: #333333;
		}
	</style>
	<base target="_self" />
	</head>
	<body id="link" onload=";document.body.style.display='';" style="display: none;background:#fff">
		<form name="easyreservations_tiny_popUp" action="#">
			<table border="0" cellpadding="0" cellspacing="0"  class="easyreservations_tiny_popUp" style="width:99%;">
				<tbody>
					<tr>
						<td nowrap="nowrap" style="border-bottom:1px solid #ececec;padding-bottom:4px;width:30%"><label for="easyreservation_type_select"><?php _e("Select", "easyReservations"); ?></label></td>
						<td  style="border-bottom:1px solid #ececec;padding-bottom:4px;width:70%">
							<select id="easyreservation_type_select" name="easyreservation_type_select" style="width: 100px" onChange="jumpto(this.value)">
								<option value="choose"><?php _e("choose", "easyReservations"); ?></option>
								<option value="form"><?php _e("Form", "easyReservations"); ?></option>
								<option value="calendar"><?php _e("Calendar", "easyReservations"); ?></option>
								<?php do_action('easy-tinymce-add-name'); ?>
							 </select> <?php _e("Choose type of shortcode", "easyReservations"); ?>
						</td>
					</tr>
				</tbody>
				<tbody id="tiny_Field">
					<div style="float: left">
						<tr><td colspan="2"><?php _e("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations"); ?>. <?php _e("This can happen with posts in category-views or on homepage", "easyReservations"); ?>.<br><?php _e("To prevent this add the shortcodes after the [more] tag", "easyReservations"); ?>.<br></td></tr>
					</div>
				</tbody>
			</table>
			<div class="mceActionPanel" style="vertical-align:bottom;">
				<div style="float: left">
					<input type="submit" id="insert" name="insert" value="<?php _e("Insert", "easyReservations"); ?>" onclick="insertEasyShortcode();" />
				</div>
				<div style="float: right">
					<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", "easyReservations"); ?>" onclick="tinyMCEPopup.close();" />
				</div>
			</div>
		</form>
	</body>
</html>
<script type='text/javascript'>
function jumpto(x){ // Chained inputs;

	var click = 0;
	var end = 0;
	<?php
	// check if a custom form style is available and add the option to the fieldset on demand
	$customFormStyleOption = '';
	if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/form.css')) $customFormStyleOption = '<option value="custom">' . addslashes(__("Custom Style", "easyReservations") ). '</option>';
	$custom_calendar_style = '';
	if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/calendar.css')) $custom_calendar_style = '<option value="custom">' .addslashes( __("Custom Style", "easyReservations") ). '</option>';?>

	if(x == "form"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_form_chooser"><?php echo addslashes(__("Form", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="easyreservation_form_chooser" name="easyreservation_form_chooser" style="width: 100px"><?php echo $formoptions; ?></select> <?php echo addslashes(__("Select form template", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservations_resource"><?php echo addslashes(__("Resource", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="easyreservations_resource" name="easyreservations_resource" style="width: 100px"><?php echo $roomsoptions; ?></select> <?php echo addslashes(__("Attached to reservations if no resource [tag] in form", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_formstyle_chooser"><?php addslashes(__("Style", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="easyreservation_formstyle_chooser" name="easyreservation_formstyle_chooser" style="width: 100px"><option value="none"><?php echo addslashes(__("White", "easyReservations")); ?></option><option value="blue"><?php echo addslashes(__("Blue", "easyReservations")); ?></option><?php echo $customFormStyleOption; do_action('easy-tinymce-add-style-form'); ?></select> <?php echo addslashes(__("Select style", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_multiple_check"><?php echo addslashes(__("Multiple reservations", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><input type="checkbox" name="easyreservation_multiple_check" id="easyreservation_multiple_check" checked> <?php echo addslashes(__("Allow multiple reservations in a", "easyReservations")); ?> <select id="easyreservation_multiple_style" name="easyreservation_multiple_style" style="width: 100px"><option value="full"><?php echo addslashes(__("Full", "easyReservations")); ?></option><option value="popup"><?php echo addslashes(__("Popup", "easyReservations")); ?></option></select> <?php echo addslashes(__("Overlay", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td colspan="2"><u><b><?php echo addslashes(__("Overlay", "easyReservations")); ?></b></u><br><?php echo addslashes(__("If multiple reservations are enabled the overlay will show the list of the reservations.", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_val_message"><?php echo addslashes(__("Overlay message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="easyreservation_form_val_message" name="easyreservation_form_val_message" style="width: 250px;padding:3px;font-size:13px" value="Reservation successfully verified"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_val_submessage"><?php echo addslashes(__("Overlay sub-message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><textarea id="easyreservation_form_val_submessage" name="easyreservation_form_val_submessage" style="width: 250px;padding:3px;font-size:13px">Either make additional reservations or submit</textarea></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_res_name"><?php echo addslashes(__("Name for resources", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="easyreservation_form_res_name" name="easyreservation_form_res_name" style="width:150px;padding:3px;font-size:13px" value="Room"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_pers"><?php echo addslashes(__("Persons in list", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_show_pers" name="easyreservation_show_pers" checked></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td colspan="2"><u><b><?php echo addslashes(__("Submit", "easyReservations")); ?></b></u><br><?php echo addslashes(__("After submit one or multiple reservations the overlay or page will show this messages, the price and, if available, the paypal button and the credit cards form.", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_submit_message"><?php echo addslashes(__("Submit message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="easyreservation_form_submit_message" name="easyreservation_form_submit_message" style="width: 250px;padding:3px;font-size:13px" value="Reservation successfully sent"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_submit_submessage"><?php echo addslashes(__("Submit sub-message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><textarea id="easyreservation_form_submit_submessage" name="easyreservation_form_submit_submessage" style="width: 250px;padding:3px;font-size:13px">Please continue by paying through PayPal or enter your credit card details.</textarea></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php _e("Show price", "easyReservations"); ?>: </label></td>';
			FieldAdd += '<td><label><input type="checkbox"  id="easyreservation_show_price" name="easyreservation_edit_table" checked></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td colspan="2"><u><b><?php echo addslashes(__("Credit Card", "easyReservations")); ?> (Payment Module is required)</b></u><br><?php echo addslashes(__("If the credit card form is enabled this messages will be shown after a successfully submit of it.", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_credit_message"><?php echo addslashes(__("Credit Card message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="easyreservation_form_credit_message" name="easyreservation_form_credit_message" style="width: 250px;padding:3px;font-size:13px" value="Reservation complete"></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_form_credit_submessage"><?php echo addslashes(__("Credit Card sub-message", "easyReservations")); ?>: </label></td>';
			FieldAdd += '<td><textarea id="easyreservation_form_credit_submessage" name="easyreservation_form_credit_submessage" style="width: 250px;padding:3px;font-size:13px">You\'ll receive an email with the reservations details</textarea></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><b><?php echo addslashes(__("Only add one form per page or post", "easyReservations")); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "calendar"){
		var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_room"><?php echo addslashes(__("Resource", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="easyreservation_calendar_room" name="easyreservation_calendar_room" style="width: 100px"><?php echo $roomsoptions; ?></select> <?php echo addslashes(__("Select default resource", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_style"><?php echo addslashes(__("Style", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="easyreservation_calendar_style" name="easyreservation_calendar_style" style="width: 100px"><?php echo $custom_calendar_style; ?><option value="1"><?php _e("simple", "easyReservations"); ?></option><option value="2"><?php echo addslashes(__("modern", "easyReservations")); ?></option><?php do_action('easy-tinymce-add-style-cal'); ?></select> <?php echo addslashes(__("Select calendar style", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_show_price"><?php echo addslashes(__("Price", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select id="easyreservation_show_price" name="easyreservation_show_price" style="width: 100px"><option value="0"><?php echo addslashes(__("no", "easyReservations")); ?></option><option value="1">150&<?php echo RESERVATIONS_CURRENCY; ?>;</option><option value="2">150</option><option value="3"><?php echo easyreservations_format_money(150,1); ?></option><option value="4"><?php echo easyreservations_format_money(150); ?></option></select> <?php echo addslashes(__("Show price in calendar", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_width"><?php echo addslashes(__("Width", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select name="easyreservation_calendar_width" id="easyreservation_calendar_width"><?php echo easyreservations_num_options(1,100,100); ?></select> %</td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_calendar_checkreq"><?php echo addslashes(__("Check", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><input type="checkbox" id="easyreservation_calendar_checkreq"> <?php echo addslashes(__("Check resources global possible arrival and departure days so they cant be clicked", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<?php do_action('easy-tinymce-cal',1); ?>';
			FieldAdd += '<tr><td colspan="2"><?php echo addslashes(__("This shortcode adds an availability calendar to the post or page", "easyReservations")); ?>. <?php echo addslashes(__("You can combine it with a form or the edit-form by add it to the same page", "easyReservations")); ?>.<br><b><?php echo addslashes(__("Only add the calendar once per page or post", "easyReservations")); ?>.</b></td></tr>';
		document.getElementById("tiny_Field").innerHTML = FieldAdd;
	} else if(x == "choose"){
		document.getElementById("tiny_Field").innerHTML = '<tr><td colspan="2"><?php echo addslashes(__("The shortcodes wont work if more then one of the same type are on the same site", "easyReservations")); ?>. <?php echo addslashes(__("This can happen with posts in category-views or on homepage", "easyReservations")); ?>.<br><?php echo addslashes(__("To prevent this add the shortcodes after the [more] tag", "easyReservations")); ?>.<br></td></tr>';
	}  <?php do_action('easy-tinymce-add', $roomsoptions); ?>
}

function insertEasyShortcode() {
	//var html = tinyMCE.activeEditor.selection.getContent(); // GET CURRENT SELECT IN TEXT ! MAYBE IMPORTANT LATER
	//html = html.replace(/<p>/g,"").replace(/<\/p>/g,"<br \/>");
	var tagtext = '[easy_';
	var y = document.easyreservations_tiny_popUp.easyreservation_type_select.options[document.easyreservations_tiny_popUp.easyreservation_type_select.options.selectedIndex].value;

	classAttribs = document.getElementById('easyreservation_type_select').value;

	if(y == "form"){
		classAttribs += document.getElementById('easyreservation_form_chooser').value + ' style="' + document.getElementById('easyreservation_formstyle_chooser').value + '" submit="' + document.getElementById('easyreservation_form_submit_message').value + '" subsubmit="' + document.getElementById('easyreservation_form_submit_submessage').value + '" credit="' + document.getElementById('easyreservation_form_credit_message').value + '" subcredit="' + document.getElementById('easyreservation_form_credit_submessage').value + '"';
		if(document.getElementById('easyreservation_form_res_name').value != '') classAttribs += ' resourcename="'+document.getElementById('easyreservation_form_res_name').value+'"';
		if(document.getElementById('easyreservation_show_price').checked == true) classAttribs += ' price="1"';
		if(document.getElementById('easyreservation_show_pers').checked == true) classAttribs += ' pers="1"';
		if(document.getElementById('easyreservations_resource')) classAttribs += ' resource="'+document.getElementById('easyreservations_resource').value+'"';
		if(document.getElementById('easyreservation_multiple_check').checked == true) classAttribs += ' multiple="'+document.getElementById('easyreservation_multiple_style').value+'" validate="'+document.getElementById('easyreservation_form_val_message').value+'" subvalidate="'+document.getElementById('easyreservation_form_val_submessage').value+'"';
	} else if(y == "calendar"){
		classAttribs += ' resource="' + document.getElementById('easyreservation_calendar_room').value + '"';
		if(document.getElementById('easyreservation_calendar_width').value != "") classAttribs += ' width="' + document.getElementById('easyreservation_calendar_width').value + '"';
		if(document.getElementById('easyreservation_calendar_style').value != "") classAttribs += ' style="' + document.getElementById('easyreservation_calendar_style').value + '"';
		if(document.getElementById('easyreservation_calendar_checkreq').checked == true) classAttribs += ' req="1"';
		if(document.getElementById('easyreservation_show_price').value != "") classAttribs += ' price="' + document.getElementById('easyreservation_show_price').value + '"';
		var monthesfield = document.getElementById('easyreservation_calendar_monthesx');
		if(monthesfield){
			classAttribs += ' monthes="' + monthesfield.value + 'x' + document.getElementById('easyreservation_calendar_monthesy').value + '"';
		}
		var intervalfield = document.getElementById('easyreservation_calendar_interval');
		if(intervalfield) classAttribs += ' interval="' + intervalfield.value + '"';
		var headerfield = document.getElementById('easyreservation_calendar_header');
		if(headerfield && headerfield.checked == true) classAttribs += ' header="1"';
	} <?php do_action('easy-tinymce-save'); ?>

	if(y != "choose") tinyMCEPopup.editor.execCommand('mceInsertContent', false, tagtext+classAttribs+']');

	tinyMCEPopup.close();
	return;
}

var userSettings = {
		'url': '<?php echo SITECOOKIEPATH; ?>',
		'uid': '<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>',
		'time':'<?php echo time() ?>'
	},
	ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
	thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
	decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
	isRtl = <?php echo (int) is_rtl(); ?>;
</script>

<?php 
global $reservations_settings;

if(!isset($reservations_settings['tutorial']) || $reservations_settings['tutorial'] == 1){
	require_once(WP_PLUGIN_DIR."/easyreservations/lib/tutorials/handle.tutorials.php");
	easyreservations_load_pointer('tinymce');
}
do_action('admin_print_footer_scripts'); ?>