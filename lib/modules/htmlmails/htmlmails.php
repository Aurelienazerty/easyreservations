<?php
/*
Plugin Name: htmlEmails Module
Plugin URI: http://easyreservations.org/module/htmlemails/
Version: 1.1.8
Description: 3.2.1
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!
*/

if(is_admin()){
	function easyreservations_generate_email_settings(){
		wp_enqueue_style('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');

		$emails = easyreservations_get_emails();

		$email_options = '<select id="idemailselect" onchange="changeEmail(this.value);">';
		foreach($emails as $key => $email){
			$email_options .= '<option value="'.$key.'">'.$email['name'].'</option>';
		}
		$email_options .= '</select>';
		$link2 = site_url().'/wp-includes';
		add_action('admin_print_footer_scripts', 'easy_add_my_email_quicktags'); ?>
	<link rel="stylesheet" media="screen" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/modules/htmlmails/colorpicker/css/colorpicker.css" />
	<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/modules/htmlmails/colorpicker/js/colorpicker.js"></script>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%">
			<thead>
				<tr>
					<th><?php echo __( 'Email Settings' , 'easyReservations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr valign="top">
					<td><?php echo __( 'Choose type of email' , 'easyReservations' ); ?>: <?php echo $email_options; ?></td>
				</tr>
				<tr valign="top">
					<td>
						<?php echo __( 'Subj' , 'easyReservations' ); ?>: <input id="subj" type="text" name="themail[]" style="width:300px"> <input type="checkbox" name="active" id="active"> <?php echo __( 'Activate email' , 'easyReservations' ); ?>
					</td>
				</tr>	
				<tr valign="top">
					<td>
						<b style="margin:3px"><u id="idplaintext"><?php echo __( 'Plain text' , 'easyReservations' ); ?></u></b> <small><a href="javascript:setDefault();" id="default">standard</a></small><br>
						<?php wp_editor( '', 'mail1', array( 'textarea_rows' => 4, 'wpautop' => false, 'tinymce' => false, 'media_buttons' => false, 'quicktags' => array('buttons' => 's' ) ) ); ?><br>
						<input id="mail2" type="hidden" name="themail[]" value="<--HTML-->"><input id="mail4" type="hidden" name="themail[]" value="">
						<b style="margin:3px;text-decoration:underline;">HTML</b>
					</td>
				</tr>
				<tr valign="top">
					<td class="bar">
						<?php
							$scan = scandir(dirname(__FILE__).'/templates/');
							$template_option = '';
							foreach($scan as $file){
								if(substr($file,-4) == 'html'){
									$template_option .= '<option value="'.substr($file,0,-5).'">'.ucfirst(substr($file,0,-5)).'</option>';
								}
							}
						?>
						<span>
							<a href="javascript:readTemplate(document.getElementById('template').value)">Template</a>
							<select id="template" style="width:95px;padding-left:1px"><?php echo $template_option; ?></select>
						</span>
						<span>
							<a title="bold" href="javascript:format_op('bold', null)" style="font-weight: bold;">B</a>
							<a title="italic" href="javascript:format_op('italic', null)" style="font-style: italic;">I</a>
							<a title="strikethrough" href="javascript:format_op('strikethrough', null)" style="text-decoration: line-through;">abc</a>
							<a title="underline" href="javascript:format_op('underline', null)" style="text-decoration: underline;">U</a>
							<a title="horizontal rule" href="javascript:format_op('inserthtml', '<div class=breakline></div>')">Hr</a>
						</span>
						<span>
							<a href="javascript:format_op('justifyleft', null)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -99px -17px;background-color:#f1f1f1 !important;" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<a href="javascript:format_op('justifycenter', null)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -119px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<a href="javascript:format_op('justifyright', null);" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -139px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<a href="javascript:format_op('justifyfull', null)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -299px -17px;background-color:#f1f1f1;" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
						</span>
						<span>
							<a title="unordered list" href="javascript:format_op('insertorderedlist', null);" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -39px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<a title="ordered list" href="javascript:format_op('insertunorderedlist', null);" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -60px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
						</span>
						<span>
							<a title="undo" href="javascript:format_op('undo', null)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -498px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<a title="redo" href="javascript:format_op('redo', null)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -480px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
						</span>
						<span>
							<a title="outdent" href="javascript:format_op('outdent', null);" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -440px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
							<a title="indent" href="javascript:format_op('indent', null);" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -460px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
						</span>
						<span>
							<a title="text color" href="javascript:format_op('forecolor', document.getElementById('textcolor').value)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -319px -21px;background-color:#f1f1f1;" class="icona"><div id="mceColorPreview" class="mceColorPreview" style="background-color: rgb(136, 136, 136);">&nbsp;</div></a>
							<input type="hidden" id="textcolor" style="width:61px" name="adas" value="#000000">
							<a title="text color" id="texttrigger" href="javascript:format_op('forecolor', document.getElementById('textcolor').value)" style="background-image: url('<?php echo $link2; ?>/images/down_arrow.gif');background-position:3px 3px;background-repeat:no-repeat;background-color:#f1f1f1"> &nbsp;&nbsp;</a>
						</span>
						<span>
							<a href="javascript:javascript:format_op('fontsize', document.getElementById('fontsize').value +'px')">aA</a>
							<select id="fontsize" style="width:40px;padding-left:1px"><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="18">18</option><option value="20">20</option><option value="24">24</option><option value="30">30</option><option value="36">36</option></select>
						</span>
						<span>
							<a title="text color" href="javascript:format_op('createlink', document.getElementById('url').value)">URL</a>
							<input type="text" id="url" style="width:97px" value="http://">
							<a title="text color" href="javascript:format_op('unlink', document.getElementById('url').value)" style="background-image: url('<?php echo $link2; ?>/images/wpicons.png');background-position: -179px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
						</span>
						<span>
							<a title="text color" href="javascript:;" id="upload_area">IMG</a>
						</span>
						<span>
							<a title="text color" href="javascript:changeHTML();" id="htmllink">HTML</a>
						</span>
						<span>
							<a href="javascript:javascript:format_op('inserthtml', '['+document.getElementById('insertag').value +']')">Tag</a>
							<select id="insertag" style="width:67px;padding-left:1px;line-height:2em;">
								<option value="arrival"><?php printf ( __( 'Arrival date' , 'easyReservations' ));?> [arrival]</option>
								<option value="departure"><?php printf ( __( 'Departure date' , 'easyReservations' ));?> [date-to]</option>
								<option value="units"><?php printf ( __( 'Times of stay' , 'easyReservations' ));?> [times]</option>
								<option value="hours"><?php printf ( __( 'Hours' , 'easyReservations' ));?> [hours]</option>
								<option value="nights"><?php printf ( __( 'Nights' , 'easyReservations' ));?> [nights]</option>
								<option value="weeks"><?php printf ( __( 'Weeks' , 'easyReservations' ));?> [weeks]</option>
								<option value="resource"><?php printf ( __( 'Resource' , 'easyReservations' ));?> [resource]</option>
								<option value="resource-number"><?php printf ( __( 'Resource Number' , 'easyReservations' ));?> [resource-number]</option>
								<option value="persons"><?php printf ( __( 'Persons' , 'easyReservations' ));?> [persons]</option>
								<option value="adults"><?php printf ( __( 'Adults' , 'easyReservations' ));?> [adults]</option>
								<option value="childs"><?php printf ( __( 'Children' , 'easyReservations' ));?> [childs]</option>
								<option value="thename"><?php printf ( __( 'Name' , 'easyReservations' ));?> [thename]</option>
								<option value="email"><?php printf ( __( 'Email' , 'easyReservations' ));?> [email]</option>
								<option value="country"><?php printf ( __( 'Country' , 'easyReservations' ));?> [country]</option>
								<option value="custom id=*"><?php printf ( __( 'Custom' , 'easyReservations' ));?> [custom]</option>
								<option value="date"><?php printf ( __( 'Date today' , 'easyReservations' ));?> [date]</option>
								<option value="ID"><?php printf ( __( 'ID of reservation' , 'easyReservations' ));?> [ID]</option>
								<option value="adminmessage"><?php printf ( __( 'Message from admin' , 'easyReservations' ));?> [adminmessage]</option>
								<option value="changelog"><?php printf ( __( 'Changelog' , 'easyReservations' ));?> [changelog]</option>
								<option value="editlink"><?php printf ( __( 'Link to edit' , 'easyReservations' ));?> [editlink]</option>
								<option value="price"><?php printf ( __( 'Price' , 'easyReservations' ));?> [price]</option>
								<option value="paid"><?php printf ( __( 'Paid' , 'easyReservations' ));?> [paid]</option>
								<?php if(function_exists('easyreservations_generate_paypal_button')){ ?><option value="paypal"><?php printf ( __( 'PayPal URL' , 'easyReservations' ));?> [paypal]</option><?php } ?>
							</select>
						</span>
					</td>
				</tr>	
				<tr valign="top">
					<td id="mailheader" style="vertical-align:top;height: 200px;padding:0px;border-top:1px solid #C6C6C6;">
						<div id="mail3" contenteditable="true"></div>
					</td>
				</tr>	
			</tbody>
		</table>
		<input type="button" onclick="saveMail(); return false;" class="easybutton button-primary" style="margin-top:4px" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>">
		<?php do_action('easy-htmlmails-footer', 'das'); ?>
		<script>
			var vis = 0;
			function changeHTML(){
				if(vis == 0){
					document.getElementById('mailheader').innerHTML = '<textarea id="mail3" class="thetextarea">' + htmlEncode(document.getElementById('mail3').innerHTML) + '</textarea>';
					document.getElementById('htmllink').innerHTML = 'Visual';
					vis = 1;
				} else {
					var str = htmlDecode(jQuery('#mail3').val());
					jQuery('#mailheader').html('<div id="mail3" contenteditable="true">' + str + '</div>');
					document.getElementById('htmllink').innerHTML = 'HTML';
					vis = 0;
				}
			}

			function saveMail(){
				if(document.getElementById('mail3').type == 'textarea') var val = htmlDecode(jQuery('#mail3').val());
				else var val = document.getElementById('mail3').innerHTML;
				document.getElementById('mail4').value = val;
				document.getElementById('reservations_email_settings').submit();
			}

			function htmlEncode(value){
				return jQuery('<div/>').text(value).html();
			}

			function htmlDecode(value){
				return jQuery('<div/>').html(value).html();
			}

			function format_op(kommando, value){
				document.execCommand(kommando, false, value);
				if(kommando == 'fontsize'){
					var fontElements = document.getElementsByTagName("font");
					for (var i = 0, len = fontElements.length; i < len; ++i) {
						fontElements[i].style.fontSize = parseFloat(fontElements[i].size) +'px';
						fontElements[i].removeAttribute("size");
					}
				}
			}				
			var emails = <?php echo json_encode($emails); ?>;
			function changeEmail(val){
				if(val != ''){
					jQuery('#idemailselect').val(val);
					document.getElementById('default').href = "javascript:setDefault('inputemail" + emails[val]['standard'] + "');";
					var url = "admin.php?page=reservation-settings&site=email&type="+val;
					url = url.replace(/_/g, '1337');
					document.getElementById('reservations_email_settings').action = encodeURI(url);
					document.getElementById('mail1').value = '';
					document.getElementById('mail3').value = '';
					document.getElementById('mail1').name= emails[val]['name_msg'] + '[]';
					document.getElementById('mail2').name= emails[val]['name_msg'] + '[]';
					document.getElementById('mail3').name= emails[val]['name_msg'] + '[]';
					document.getElementById('mail4').name= emails[val]['name_msg'] + '[]';
					document.getElementById('subj').name= emails[val]['name_subj'];
					document.getElementById('active').name= emails[val]['name_active'];
					if(emails[val]['option']['msg']){
						var msg = emails[val]['option']['msg'];
						var subj = emails[val]['option']['subj'];
						var active = emails[val]['option']['active'];
						var html = msg.split('<--HTML-->');
						document.getElementById('mail1').value = html[0];
						if(active == 1) document.getElementById('active').checked = 1;
            else document.getElementById('active').checked = 0;
						if(html[1]) document.getElementById('mail3').innerHTML = html[1];
						document.getElementById('subj').value = subj;
					}
				}
			}

			function setDefault(inputname){
				var tafa = document.getElementsByName(inputname);
				document.getElementById('mail1').value = tafa[0].value;
			}

			function readTemplate(name) {
				var HTML_FILE_URL = '<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/modules/htmlmails/templates/' + name + '.html';

				jQuery(document).ready(function() {
					jQuery.get(HTML_FILE_URL, function(data) {
							data = data.replace(/THELINK/g, '<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/modules/htmlmails');
							if(document.getElementById('mail3').type == 'textarea') jQuery('#mail3').val(jQuery(data).html());
							else jQuery('#mail3').html(jQuery(data));
					});
				});
			}

			jQuery('#textcolor,#texttrigger').ColorPicker({
				color: '#666666',
				onChange: function (hsb, hex, rgb) {
					document.getElementById('textcolor').value = '#' + hex;
					document.getElementById('mceColorPreview').style.background = '#' + hex;
				},
				onSubmit: function (hsb, hex, rgb) {
					document.getElementById('textcolor').value = '#' + hex;
				}
			});

			jQuery(document).ready(function() {
				jQuery('#upload_area').click(function() {
					formfield = jQuery('#logourl').attr('name');
					tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
					return false;
				});

				window.send_to_editor = function(html) {
					document.execCommand('inserthtml', false, html);
					tb_remove();
				}
			});			</script>
		<style>
			.thetextarea {
				width:100%;
				height:600px;
				margin:0px;
				padding:0px;
				border:none;
			}
			.bar {
				margin:0px;
				padding:5px 5px 2px 7px;
				margin-bottom:10px;
				background:#E8E8E8;
				border-bottom:1px solid #C6C6C6;
				border-top:1px solid #C6C6C6;
				font-family: Arial,sans-serif;
				color:#777777;
				vertical-align: middle;
				font-size:13px;
			}
			.bar > span {
				padding:0px;
				margin-right:3px;
				left:0;
				right:0;
				overflow:hidden;
			}

			.bar > span > select, .bar > span > input {
				border-radius:0px;
				background-image: -moz-linear-gradient(center top , #F7F4F4, #F1F1F1);
				border: 1px solid #BABABA;
				border-right: 0px;
				font-size: 12px !important;
				text-decoration: none;
				color:#696969;
				width:70px;
				cursor: pointer;
				display:inline-block;
				margin-right:-4px;
			}

			.bar > span > select {
				height:25px;
				padding:3px 4px 4px!important;
			}

			.bar > span > input {
				height: 25px;
				padding: 0px 4px 0 !important;
			}

			.bar > span > a, .list {
				display:inline-block;
				min-height:14px;
				height:15px;
				background-image: -moz-linear-gradient(center top , #F7F4F4, #F1F1F1);
				border: 1px solid #BABABA;
				border-right: 0px;
				font-size: 12px !important;
				text-decoration: none;
				padding:4px 5px 4px 5px;
				color:#696969;
				cursor: pointer;
				font-weight: bold;
				margin:2px -4px 2px 0px;
				background:#F1F1F1;
			}

			.bar > span > a.active {
				background:#F7F4F4;
			}

			.bar > span >a:hover {
				color:#000;
				-webkit-box-shadow: 0px 0px 5px #c6c6c6; /* webkit browser*/ -moz-box-shadow: 0px 0px 5px #c6c6c6; /* firefox */ box-shadow: 0px 0px 5px #c6c6c6;
				background:#F9F9F9;
			}

			.bar > span > a:active {
				background:#F7F4F4;
			}

			.bar > span >:first-child  {
				border-right: 0px;
				border-top-left-radius: 4px;
				border-bottom-left-radius: 4px;
			}

			.bar > span >:last-child {
				border-right: 1px solid #BABABA;
				border-top-right-radius: 4px;
				border-bottom-right-radius: 4px;
			}
			.bar > a.icona {
				background-image: url("$link2/images/wpicons.png");
			}
			.mceColorPreview {
				display:inline-block;
				height: 4px;
				margin: 11px -1px 0;
				overflow: hidden;
				width: 16px;
				vertical-align:text-bottom;
			}
		</style>
		<?php
		if(isset($_GET['type'])) echo "<script>changeEmail('".str_replace("1337", "_", $_GET['type'])."');</script>";
		else echo "<script>changeEmail('reservations_email_sendmail');</script>";
	}

	add_action('easy-email-settings', 'easyreservations_generate_email_settings');
}

function easy_add_my_email_quicktags(){ ?>
	<script type="text/javascript">
		QTags.addButton( 'line break', '<?php echo addslashes(__('Line Break')); ?>', '<br>');
		QTags.addButton( 'arrival', '<?php echo addslashes(__('Arrival')); ?>', '[arrival]');
		QTags.addButton( 'departure', '<?php echo addslashes(__('Departure')); ?>', '[departure]');
		QTags.addButton( 'times', '<?php echo addslashes(__('Times')); ?>', '[times]');
		QTags.addButton( 'adults', '<?php echo addslashes(__('Adults')); ?>', '[adults]');
		QTags.addButton( 'childs', '<?php echo addslashes(__('Childrens')); ?>', '[childs]');
		QTags.addButton( 'country', '<?php echo addslashes(__('Country')); ?>', '[country]');
		QTags.addButton( 'resource', '<?php echo addslashes(__('Resource')); ?>', '[resource]');
		QTags.addButton( 'resourcenumber', '<?php echo addslashes(__('Resource Number')); ?>', '[resourcenumber]');
		QTags.addButton( 'price', '<?php echo addslashes(__('Price')); ?>', '[price]');
		QTags.addButton( 'paid', '<?php echo addslashes(__('Paid')); ?>', '[paid]');
		QTags.addButton( 'changlog', '<?php echo addslashes(__('Changlog')); ?>', '[changlog]');
		QTags.addButton( 'editlink', '<?php echo addslashes(__('Editlink')); ?>', '[editlink]');
		QTags.addButton( 'adminmessage', '<?php echo addslashes(__('Admin Message')); ?>', '[adminmessage]');
		QTags.addButton( 'custom', '<?php echo addslashes(__('Custom fields')); ?>', '[custom id="*"]');
		<?php if(function_exists('easyreservations_generate_paypal_button')){ ?>QTags.addButton( 'paypal', '<?php echo addslashes(__('PayPal URL')); ?>', '[paypal]');<?php } ?>
	</script>
<?php }

function easyreservations_send_multipart_mail($content){
	add_filter('wp_mail_content_type', function () { return "multipart/alternative"; });
	$mail_contents = explode('<--HTML-->', $content);
	$message = ['text/plain' => '', 'text/html' => ''];
	$message['text/plain'] = html_entity_decode(str_replace('<br>', '\n', str_replace(['[', ']'], '', $mail_contents[0])));
	if (isset($mail_contents[1]) && !empty($mail_contents[1])) {
		$message['text/html'] .= $mail_contents[1];
	} else {
		$message['text/html'] .= $mail_contents[0];
	}
	return $message;
}

if(!function_exists( 'wp_mail' )/* && version_compare(get_bloginfo('version'), '3.4', '<')*/) :
	/**
	 * Send mail, similar to PHP's mail
	 *
	 * A true return value does not automatically mean that the user received the
	 * email successfully. It just only means that the method used was able to
	 * process the request without any errors.
	 *
	 * Using the two 'wp_mail_from' and 'wp_mail_from_name' hooks allow from
	 * creating a from address like 'Name <email@address.com>' when both are set. If
	 * just 'wp_mail_from' is set, then just the email address will be used with no
	 * name.
	 *
	 * The default content type is 'text/plain' which does not allow using HTML.
	 * However, you can set the content type of the email by using the
	 * 'wp_mail_content_type' filter.
	 *
	 * If $message is an array, the key of each is used to add as an attachment
	 * with the value used as the body. The 'text/plain' element is used as the
	 * text version of the body, with the 'text/html' element used as the HTML
	 * version of the body. All other types are added as attachments.
	 *
	 * The default charset is based on the charset used on the blog. The charset can
	 * be set using the 'wp_mail_charset' filter.
	 *
	 * @since 1.2.1
	 *
	 * @uses PHPMailer
	 *
	 * @param string|array $to Array or comma-separated list of email addresses to send message.
	 * @param string $subject Email subject
	 * @param string|array $message Message contents
	 * @param string|array $headers Optional. Additional headers.
	 * @param string|array $attachments Optional. Files to attach.
	 * @return bool Whether the email contents were sent successfully.
	 */
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		// Compact the input, apply the filters, and extract them back out

		/**
		 * Filter the wp_mail() arguments.
		 *
		 * @since 2.2.0
		 *
		 * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
		 *                    subject, message, headers, and attachments values.
		 */
		$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

		if ( isset( $atts['to'] ) ) {
			$to = $atts['to'];
		}

		if ( isset( $atts['subject'] ) ) {
			$subject = $atts['subject'];
		}

		if ( isset( $atts['message'] ) ) {
			$message = $atts['message'];
		}

		if ( isset( $atts['headers'] ) ) {
			$headers = $atts['headers'];
		}

		if ( isset( $atts['attachments'] ) ) {
			$attachments = $atts['attachments'];
		}

		if ( ! is_array( $attachments ) ) {
			$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
		}
		global $phpmailer;

		// (Re)create it, if it's gone missing
		if ( ! ( $phpmailer instanceof PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new PHPMailer( true );
		}

		// Headers
		if ( empty( $headers ) ) {
			$headers = array();
		} else {
			if ( !is_array( $headers ) ) {
				// Explode the headers out, so this function can take both
				// string headers and an array of headers.
				$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
			} else {
				$tempheaders = $headers;
			}
			$headers = array();
			$cc = array();
			$bcc = array();

			// If it's actually got contents
			if ( !empty( $tempheaders ) ) {
				// Iterate through the raw headers
				foreach ( (array) $tempheaders as $header ) {
					if ( strpos($header, ':') === false ) {
						if ( false !== stripos( $header, 'boundary=' ) ) {
							$parts = preg_split('/boundary=/i', trim( $header ) );
							$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
						}
						continue;
					}
					// Explode them out
					list( $name, $content ) = explode( ':', trim( $header ), 2 );

					// Cleanup crew
					$name    = trim( $name    );
					$content = trim( $content );

					switch ( strtolower( $name ) ) {
						// Mainly for legacy -- process a From: header if it's there
						case 'from':
							$bracket_pos = strpos( $content, '<' );
							if ( $bracket_pos !== false ) {
								// Text before the bracketed email is the "From" name.
								if ( $bracket_pos > 0 ) {
									$from_name = substr( $content, 0, $bracket_pos - 1 );
									$from_name = str_replace( '"', '', $from_name );
									$from_name = trim( $from_name );
								}

								$from_email = substr( $content, $bracket_pos + 1 );
								$from_email = str_replace( '>', '', $from_email );
								$from_email = trim( $from_email );

								// Avoid setting an empty $from_email.
							} elseif ( '' !== trim( $content ) ) {
								$from_email = trim( $content );
							}
							break;
						case 'content-type':
							if ( is_array($message) ) {
								// Multipart email, ignore the content-type header
								break;
							}
							if ( strpos( $content, ';' ) !== false ) {
								list( $type, $charset_content ) = explode( ';', $content );
								$content_type = trim( $type );
								if ( false !== stripos( $charset_content, 'charset=' ) ) {
									$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
								} elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
									$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
									$charset = '';
								}

								// Avoid setting an empty $content_type.
							} elseif ( '' !== trim( $content ) ) {
								$content_type = trim( $content );
							}
							break;
						case 'cc':
							$cc = array_merge( (array) $cc, explode( ',', $content ) );
							break;
						case 'bcc':
							$bcc = array_merge( (array) $bcc, explode( ',', $content ) );
							break;
						default:
							// Add it to our grand headers array
							$headers[trim( $name )] = trim( $content );
							break;
					}
				}
			}
		}

		// Empty out the values that may be set
		$phpmailer->ClearAllRecipients();
		$phpmailer->ClearAttachments();
		$phpmailer->ClearCustomHeaders();
		$phpmailer->ClearReplyTos();

		$phpmailer->Body= '';
		$phpmailer->AltBody= '';

		// From email and name
		// If we don't have a name from the input headers
		if ( !isset( $from_name ) )
			$from_name = 'WordPress';

		/* If we don't have an email from the input headers default to wordpress@$sitename
		 * Some hosts will block outgoing mail from this address if it doesn't exist but
		 * there's no easy alternative. Defaulting to admin_email might appear to be another
		 * option but some hosts may refuse to relay mail from an unknown domain. See
		 * https://core.trac.wordpress.org/ticket/5007.
		 */

		if ( !isset( $from_email ) ) {
			// Get the site domain and get rid of www.
			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ) {
				$sitename = substr( $sitename, 4 );
			}

			$from_email = 'wordpress@' . $sitename;
		}

		/**
		 * Filter the email address to send from.
		 *
		 * @since 2.2.0
		 *
		 * @param string $from_email Email address to send from.
		 */
		$phpmailer->From = apply_filters( 'wp_mail_from', $from_email );

		/**
		 * Filter the name to associate with the "from" email address.
		 *
		 * @since 2.3.0
		 *
		 * @param string $from_name Name associated with the "from" email address.
		 */
		$phpmailer->FromName = apply_filters( 'wp_mail_from_name', $from_name );

		// Set destination addresses
		if ( !is_array( $to ) )
			$to = explode( ',', $to );

		foreach ( (array) $to as $recipient ) {
			try {
				// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
				$recipient_name = '';
				if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
					if ( count( $matches ) == 3 ) {
						$recipient_name = $matches[1];
						$recipient = $matches[2];
					}
				}
				$phpmailer->AddAddress( $recipient, $recipient_name);
			} catch ( phpmailerException $e ) {
				continue;
			}
		}

		// If we don't have a charset from the input headers
		if ( !isset( $charset ) )
			$charset = get_bloginfo( 'charset' );

		// Set the content-type and charset

		/**
		 * Filter the default wp_mail() charset.
		 *
		 * @since 2.3.0
		 *
		 * @param string $charset Default email charset.
		 */
		$phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

		// Set mail's subject and body
		$phpmailer->Subject = $subject;

		if ( is_string($message) ) {
			$phpmailer->Body = $message;

			// Set Content-Type and charset
			// If we don't have a content-type from the input headers
			if ( !isset( $content_type ) )
				$content_type = 'text/plain';

			/**
			 * Filter the wp_mail() content type.
			 *
			 * @since 2.3.0
			 *
			 * @param string $content_type Default wp_mail() content type.
			 */
			$content_type = apply_filters( 'wp_mail_content_type', $content_type );

			$phpmailer->ContentType = $content_type;

			// Set whether it's plaintext, depending on $content_type
			if ( 'text/html' == $content_type )
				$phpmailer->IsHTML( true );

			// For backwards compatibility, new multipart emails should use
			// the array style $message. This never really worked well anyway
			if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
				$phpmailer->AddCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
		}
		elseif ( is_array($message) ) {
			foreach ($message as $type => $bodies) {
				foreach ((array) $bodies as $body) {
					if ($type === 'text/html') {
						$phpmailer->Body = $body;
					}
					elseif ($type === 'text/plain') {
						$phpmailer->AltBody = $body;
					}
					else {
						$phpmailer->AddAttachment($body, '', 'base64', $type);
					}
				}
			}
		}

		// Add any CC and BCC recipients
		if ( !empty( $cc ) ) {
			foreach ( (array) $cc as $recipient ) {
				try {
					// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
					$recipient_name = '';
					if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
						if ( count( $matches ) == 3 ) {
							$recipient_name = $matches[1];
							$recipient = $matches[2];
						}
					}
					$phpmailer->AddCc( $recipient, $recipient_name );
				} catch ( phpmailerException $e ) {
					continue;
				}
			}
		}

		if ( !empty( $bcc ) ) {
			foreach ( (array) $bcc as $recipient) {
				try {
					// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
					$recipient_name = '';
					if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
						if ( count( $matches ) == 3 ) {
							$recipient_name = $matches[1];
							$recipient = $matches[2];
						}
					}
					$phpmailer->AddBcc( $recipient, $recipient_name );
				} catch ( phpmailerException $e ) {
					continue;
				}
			}
		}

		// Set to use PHP's mail()
		$phpmailer->IsMail();

		// Set custom headers
		if ( !empty( $headers ) ) {
			foreach ( (array) $headers as $name => $content ) {
				$phpmailer->AddCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
			}
		}

		if ( !empty( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				try {
					$phpmailer->AddAttachment($attachment);
				} catch ( phpmailerException $e ) {
					continue;
				}
			}
		}

		/**
		 * Fires after PHPMailer is initialized.
		 *
		 * @since 2.2.0
		 *
		 * @param PHPMailer &$phpmailer The PHPMailer instance, passed by reference.
		 */
		do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

		// Send!
		try {
			return $phpmailer->Send();
		} catch ( phpmailerException $e ) {
			return false;
		}
	}
endif;

?>