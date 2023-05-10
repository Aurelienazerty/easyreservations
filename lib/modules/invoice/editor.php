<?php
require('../../../../../../wp-load.php');
$wp->send_headers();
// get the HTML
wp_print_scripts('thickbox') ;
if(isset($_GET['id'])) $id = $_GET['id'];
if(isset($_GET['email'])) $mail = $_GET['email'];
if(isset($_GET['inv'])) $inv = $_GET['inv'];
else $inv = false;
if(isset($_GET['lang'])) $lang = $_GET['lang'];
else $lang = false;

$res = new Reservation($id);

try{

	$invselect = easyreservations_get_invoice_select($inv);
	$generated_content = easyreservations_generate_invoice($res, $inv, $lang);
	$content = $generated_content['content'];
	$filename = $generated_content['filename'];
	$nonce = wp_nonce_field('easy-invoice', 'easy-invoice');
	$button_generate = __( 'Generate' , 'easyReservations' );
	$button_preview = __( 'Preview' , 'easyReservations' );
	$button_mail = __( 'Mail' , 'easyReservations' );
	$link = site_url().'/wp-includes/js';
	$link2 = site_url().'/wp-includes';
	$plugin_url = WP_PLUGIN_URL;
	$moneypattern = easyreservations_format_money(66.55,1);
	if(function_exists('easyreservations_lang_options')) $langselect = easyreservations_lang_options($lang);
	else $langselect = '<option value="">Install multilingual</option>';

	if(!$inv) $inv = 0;
	$thecolor = '';

	if(isset($_POST['sendmail'])){
		$attachment = easyreservations_insert_attachment($res, false, false, array('filename' => 'invoice', 'content' => stripslashes($_POST['emailcontent'])));
		$array = array('active' => 1, 'msg' => $_POST['emailmessage'], 'subj' => $_POST['emailsubject']);
		$mailgot = $res->sendMail($array, $_POST['sendmail'], $attachment);
		if($mailgot) $thecolor = 'background:#BFFFC4';
		else $thecolor = 'background:#FFBFBF';
	}

	$send = <<<EOF
	<script type='text/javascript' src='$link/jquery/jquery.js'></script>
	<script type='text/javascript' src='$link/thickbox/thickbox.js'></script>
	<link rel="stylesheet" media="screen" type="text/css" href="$link/thickbox/thickbox.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="colorpicker/css/colorpicker.css" />
	<script type="text/javascript" src="colorpicker/js/colorpicker.js"></script>
	<style>
		.bar {
			width:1000px;
			height:100%;
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

		.bar > span select, .bar > span > input {
			background-image: -moz-linear-gradient(center top , #F7F4F4, #F1F1F1);
			border: 1px solid #BABABA;
			border-right: 0px;
			font-size: 12px !important;
			text-decoration: none;
			padding:3px 4px 4px;
			color:#696969;
			width:70px;
			cursor: pointer;
			margin-bottom:4px;
			display:inline-block;
			margin-right:-4px;
		}

		.bar > span > input {
			padding: 4px 4px 5px;
		}

		.bar > span > a, .list {
			display:inline-block;
			min-height:14px;
			background-image: -moz-linear-gradient(center top , #F7F4F4, #F1F1F1);
			border: 1px solid #BABABA;
			border-right: 0px;
			font-size: 12px !important;
			text-decoration: none;
			padding:5px 5px 4px 5px;
			color:#696969;
			cursor: pointer;
			font-weight: bold;
			margin:2px -4px 2px 0px;
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

		.bar b {
			-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
			-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
			box-shadow:inset 0px 1px 0px 0px #ffffff;
			background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #dedede), color-stop(1, #cccccc) );
			background:-moz-linear-gradient( center top, #dedede 5%, #cccccc 100% );
			filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dedede', endColorstr='#cccccc');
			background-color:#dedede;
			-moz-border-radius:3px;
			-webkit-border-radius:3px;
			border-radius:3px;
			border:1px solid #bfbfbf;
			display:inline-block;
			color:#575757;
			font-family:arial;
			font-size:14px;
			font-weight:bold;
			padding:6px 6px;
			text-decoration:none;
			text-shadow:1px 1px 0px #ffffff;
		}
		.bar b:hover {
			background-image: -moz-linear-gradient(center top , #66B9E2, #4192D1);
		}
		.bar a.blue {
			background-color: #57AADF;
			background: -moz-linear-gradient(center top , #50BAF7 0pt, #2875EF 100%);
			border: 1px solid #417FA7;
			border-radius: 4px 4px 4px 4px;
			box-shadow: 0 1px 0 #64CBF9 inset;
			color: #FFFFFF;
			text-decoration:none;
			padding: 5px;
			text-shadow: 0 -1px 0 #0F49EA;
			font-size:13px;
		}

		.bar > a.icona {
			background-image: url("$link2/images/wpicons.png");
		}

		.bar a.blue:hover {
			background-image: -moz-linear-gradient(center top , #66B9E2, #4192D1);
		}

		.mceColorPreview {
			display:inline-block;
			height: 4px;
			margin: 11px -1px 0;
			overflow: hidden;
			width: 16px;
			vertical-align:text-bottom;
		}
		#invoice th, #invoice .seperator td {
			border-bottom:1px solid black !important;
		}
		#invoice .seperator td, #invoice .total td, #invoice .tax td {
			border-top:1px solid black !important;
		}
		#invoice div.headerdiv {
			height:120px;
			margin-bottom: 0mm;
		}
		#invoice div.breakline {
			height:3px;
			display: block;
			width:100%;
		}
		table.addresstable {
			padding-top:0mm;
		}

		.thetextarea {
			width:100%;
			height:700px;
			margin:0px;
			padding:0px;
			boder:none;
		}
	
		select {  }
	</style>
	<div style="position: fixed;">
	<div class="bar" style="">
		<form id="generatepdf" action="$plugin_url/easyreservations/lib/modules/invoice/generate.php" method="post" style="display:inline-block;margin-bottom:0px;margin-top:5px">
			$nonce
			<input type="hidden" id="invoicesave" name="invoice" value="">
			<input type="hidden" id="invoice_preview" name="invoice_preview" value="">
			<input type="hidden" id="filename" name="filename" value="$filename">
			<input type="hidden" id="id" name="id" value="$id">
			<a href="javascript:generatePDF(1);" class="blue">$button_generate</a>
		</form>
		<span>
			<a href="javascript:generatePDF(2);">$button_preview</a>
			<a href="#TB_inline?height=200&width=300&inlineId=dialog" class="thickbox"" style="$thecolor" id="opener" title="Email with Invoice as attachment">$button_mail</a>
		</span>
		<span>
			<a href="javascript:changeTemplate('template')">Template</a>
			<select id="template" style="width:95px;padding-left:1px">$invselect</select>
		</span>
		<span>
			<a href="javascript:changeTemplate('lang')">Lang</a>
			<select id="invoicelang" style="width:45px;padding-left:1px">$langselect</select>
		</span>
		<span>
			<a title="add row" href="javascript:addRow(document.getElementById('row').value)">Row</a>
			<select id="row" style="padding:3px 3px 4px 0px;width:65px;"><option value="1">Head</option><option value="2">Middle</option><option value="3">Bottom</option></select>
		</span>
		<span>
			<a title="add column" href="javascript:addColumn(document.getElementById('col').value)">Column</a>
			<select id="col" style="padding:3px 3px 4px 0px;width:55px;"><option value="1">Left</option><option value="2">Middle</option><option value="3">Right</option></select>
		</span>
		<span>
			<a title="calculate" href="javascript:recalculate()">Calc</a>
			<a title="live calculation" class="list" style="padding:0px;padding-top:3px;padding-bottom:7px;" onclick="var check = document.getElementById('live'); if(check.checked == true) check.checked = false; else check.checked = true;">
				<input title="live calculation" type="checkbox" id="live" style="margin:1px 4px 0px 4px;padding-top:10px">
			</a>
		</span>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="checker"></span><a href="javascript:check_for_format('bold',1);">a</a>
		<br>
		<span>
			<a title="bold" href="javascript:format_op('bold', null)" style="font-weight: bold;">B</a>
			<a title="italic" href="javascript:format_op('italic', null)" style="font-style: italic;">I</a>
			<a title="strikethrough" href="javascript:format_op('strikethrough', null)" style="text-decoration: line-through;">abc</a>
			<a title="underline" href="javascript:format_op('underline', null)" style="text-decoration: underline;">U</a>
			<a title="horizontal rule" href="javascript:format_op('inserthtml', '<div class=breakline></div>')">Hr</a>
		</span>
		<span>
			<a href="javascript:format_op('justifyleft', null)" style="background-image: url('$link2/images/wpicons.png');background-position: -99px -17px;background-color:#f1f1f1 !important;" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
			<a href="javascript:format_op('justifycenter', null)" style="background-image: url('$link2/images/wpicons.png');background-position: -119px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
			<a href="javascript:format_op('justifyright', null);" style="background-image: url('$link2/images/wpicons.png');background-position: -139px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
			<a href="javascript:format_op('justifyfull', null)" style="background-image: url('$link2/images/wpicons.png');background-position: -299px -17px;background-color:#f1f1f1;" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		</span>
		<span>
			<a title="unordered list" href="javascript:format_op('insertorderedlist', null);" style="background-image: url('$link2/images/wpicons.png');background-position: -39px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
			<a title="ordered list" href="javascript:format_op('insertunorderedlist', null);" style="background-image: url('$link2/images/wpicons.png');background-position: -60px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		</span>
		<span>
			<a title="undo" href="javascript:format_op('undo', null)" style="background-image: url('$link2/images/wpicons.png');background-position: -498px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
			<a title="redo" href="javascript:format_op('redo', null)" style="background-image: url('$link2/images/wpicons.png');background-position: -480px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		</span>
		<span>
			<a title="outdent" href="javascript:format_op('outdent', null);" style="background-image: url('$link2/images/wpicons.png');background-position: -440px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
			<a title="indent" href="javascript:format_op('indent', null);" style="background-image: url('$link2/images/wpicons.png');background-position: -460px -17px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		</span>
		<span>
			<a title="text color" href="javascript:format_op('forecolor', document.getElementById('textcolor').value)" style="background-image: url('$link2/images/wpicons.png');background-position: -319px -21px;background-color:#f1f1f1;" class="icona"><div id="mceColorPreview" class="mceColorPreview" style="background-color: rgb(136, 136, 136);">&nbsp;</div></a>
			<input type="hidden" id="textcolor" style="width:61px" name="adas" value="#000000">
			<a title="text color" id="texttrigger" href="javascript:format_op('forecolor', document.getElementById('textcolor').value)" style="background-image: url('$link2/images/down_arrow.gif');background-position:3px 3px;background-repeat:no-repeat;background-color:#f1f1f1"> &nbsp;&nbsp;</a>
		</span>
		<span>
			<a href="javascript:javascript:format_op('fontsize', document.getElementById('fontsize').value +'px')">aA</a>
			<select id="fontsize" style="width:40px;padding-left:1px"><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="18">18</option><option value="20">20</option><option value="24">24</option><option value="30">30</option><option value="36">36</option></select>
		</span>
		<span>
			<a title="text color" href="javascript:format_op('createlink', document.getElementById('url').value)">URL</a>
			<input type="text" id="url" style="width:47px" name="dsa" value="http://">
			<a title="text color" href="javascript:format_op('unlink', document.getElementById('url').value)" style="background-image: url('$link2/images/wpicons.png');background-position: -179px -18px;background-color:#f1f1f1" class="icona">&nbsp;&nbsp;&nbsp;&nbsp;</a>
		</span>
		<span>
			<a title="text color" href="javascript:changeHTML();" id="htmllink">HTML</a>
		</span>
	</div>
	</div>
	<div id="invoicehead" style="padding-top:72px;">
		<div id="invoice" contenteditable="true" style="padding-right:10px;margin:0px">
			$content
		</div>
	</div>
	<div id="dialog" title="Basic dialog" style="display:none;">
		<form id="emailpdf" method="post" style="display:inline-block;margin-bottom:0px;margin-top:5px">
			<input type="hidden" name="emailcontent" id="emailcontent">
			<p><b>To</b> <span style="width:270px;text-align:right;display:inline-block"><input type="text" name="sendmail" value="$mail" style="width:200px"></span></p>
			<p><b>Subject</b> <span style="width:237px;text-align:right;display:inline-block"><input type="text" name="emailsubject" value="" style="width:200px"></span></p>
			<p style="vertical-align:top"><b style="vertical-align:top">Message</b> <span style="width:231px;text-align:right;display:inline-block"><textarea name="emailmessage" value="$mail" style="width:200px;height:240px"></textarea></span></p>
			<p style="text-align:right"><a href="#" onclick="document.getElementById('emailcontent').value = document.getElementById('invoice').innerHTML;document.getElementById('emailpdf').submit();" class="blue" style="font-weight:bold">Send email</a></p>
		</form>
	</div>
<script>
	var vis = 0;
	function changeHTML(){
		if(vis == 0){
			document.getElementById('invoicehead').innerHTML = '<textarea id="invoice" class="thetextarea">' + htmlEncode(document.getElementById('invoice').innerHTML) + '</textarea>';
			document.getElementById('htmllink').innerHTML = 'Visual';
			vis = 1;
		} else {
			var str = htmlDecode(jQuery('#invoice').val());
			jQuery('#invoicehead').html('<div id="invoice" contenteditable="true" style="padding-right:10px;margin:0px">' + str + '</div>');
			document.getElementById('htmllink').innerHTML = 'HTML';
			vis = 0;
		}
	}

	function htmlEncode(value){
		return jQuery('<div/>').text(value).html();
	}

	function htmlDecode(value){
		return jQuery('<div/>').html(value).html();
	}

	document.getElementsByTagName("body")[0].style.margin = 0;
	function addRow(w){
		var row = '<tr><td class="message">Test</td><td class="price" axis="oben">$moneypattern</td></tr>';
		if(w == 1) jQuery('.seperator').before(row);
		else if(w == 2) jQuery('.total').before(row);
		else if(w == 3){
			if(jQuery('.paid').length > 0) var ele = '.paid';
			else var ele = '.sum';
			jQuery(ele).before('<tr><td class="message" style="text-align:right">Test</td><td class="price" axis="oben">$moneypattern</td></tr>');
		}
		if(document.getElementById('live').checked == true) recalculate();
	}
	
	function generatePDF(e){
		if(document.getElementById('invoice').type == 'textarea') var val = htmlDecode(jQuery('#invoice').val());
		else var val = document.getElementById('invoice').innerHTML; 
		document.getElementById('invoicesave').value = val;
		if(e == 1){
			document.getElementById('generatepdf').target='_self';
			document.getElementById('invoice_preview').value = 0;
		} else {
			document.getElementById('generatepdf').target='_blank';
			document.getElementById('invoice_preview').value = 1;
		}
		document.getElementById('generatepdf').submit();

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

	function addColumn(w){
		if(w == 1){
			jQuery('.message').before('<td style="width:100px">Test</td>');
			jQuery('.messagehead').before('<th>Test</th>');
		} else if(w == 2) {
			jQuery('.message').after('<td style="width:100px">Test</td>');
			jQuery('.messagehead').after('<th>Test</th>');
		} else {
			jQuery('.price').after('<td style="text-align:right;width:100px">Test</td>');
			jQuery('.pricehead').after('<th style="text-align:right">Test</th>');
		}
		jQuery('.message').width(jQuery('.message').width()-0);
		jQuery('.price').width(jQuery('.price').width()-15);
	}

	function stringtoamount(s){
		var pattern = /[0-9-,.]+/g;
		var matches = s.match(pattern);
		return parseFloat(matches.toString().replace(',', '.'));
	}

	function amountostring(a){
		a = Math.round(a*Math.pow(10,2))/Math.pow(10,2);
		var pattern = '$moneypattern';
		var split = a.toString().split('.');
		if(!split[1]) split[1] = '00';
		if(split[1].length == 1) split[1] = split[1] + '0';
		pattern = pattern.replace(66, split[0]);
		pattern = pattern.replace(55, split[1]);
		return pattern;
	}

	function recalculate(){
		var subtotal1 = 0;
		var subtotal2 = 0;

		var all = jQuery('td[axis="oben"]');
		for(var i = 0; i < all.length; i++){
			var inner = stringtoamount(all[i].innerHTML);
			subtotal1 += inner;
		}
		var subtotal = jQuery('td[axis="subtotal1"]');
		subtotal[0].innerHTML = amountostring(subtotal1);

		var all = jQuery('td[axis="unten"]');
		for(var i = 0; i < all.length; i++){
			var inner = stringtoamount(all[i].innerHTML);
			subtotal2 += inner;
		}
	
		var persons = jQuery('td[axis="persons"]');
		if(persons[0]){
			var personsamount = parseFloat(persons[0].title)-1;
			subtotal2 += (personsamount*subtotal1);
			persons[0].innerHTML = amountostring(personsamount*subtotal1);
		}

		var childs = jQuery('td[axis="childs"]');
		if(childs[0]){
			var childsamount = parseFloat(childs[0].title)-1;
			subtotal2 += (childsamount*subtotal1);
			childs[0].innerHTML = amountostring(childsamount*subtotal1);
		}

		var subtotal = jQuery('td[axis="subtotal2"]');
		subtotal[0].innerHTML = amountostring(subtotal1+subtotal2);
		
		var total = subtotal1+subtotal2;

		var all = jQuery('td[axis="tax"]');
		for(var i = 0; i < all.length; i++){
			var amount = (subtotal1+subtotal2)/100*parseFloat(all[i].title);
			total += parseFloat(amount);
			all[i].innerHTML = amountostring(amount);
		}

		var paid = jQuery('td[axis="paid"]');
		if(paid[0]){
			var paidamount = stringtoamount(paid[0].innerHTML);
			total += paidamount;
		}
		
		var sum = jQuery('td[axis="total"]');
		sum[0].innerHTML = amountostring(total);
	}

	var editable = document.getElementById("editable");

	function listener(evt) {
		if(document.getElementById('live').checked == true) recalculate();
	}

	if (editable.addEventListener) {
		editable.addEventListener("DOMCharacterDataModified", listener, false);
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

	function check_for_format(kommando, x){
		var result = document.queryCommandState(kommando);
		if(x == 1) document.getElementById('checker').innerHTML = result;
	}

	function changeTemplate(x){
		var template = document.getElementById('template').value;
		var lang = document.getElementById('invoicelang').value;
		if(x == 'template') var link = '$plugin_url/easyreservations/lib/modules/invoice/editor.php?id=$id&lang=$lang&inv='+template;
		else var link = '$plugin_url/easyreservations/lib/modules/invoice/editor.php?id=$id&lang='+lang+'&inv=$inv';
		window.location  = link;
	}

</script><style>div.footerdiv { height:auto !important; }</style>
EOF;

echo $send;
} catch(Exception $e){
	echo 'Error: '.$e->getMessage();
}