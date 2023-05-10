<?php
/*
Plugin Name: Invoice Module
Plugin URI: http://easyreservations.org/module/invoice/
Version: 1.0.17
Description: 3.4
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!
*/

function easyreservations_load_invoice_template($nr = 1){
	$file = WP_PLUGIN_DIR.'/easyreservations/lib/modules/invoice/templates/invoice_'.$nr.'.html';
	$handle = fopen($file, "r");
	$output = fread($handle, filesize($file));
	return $output;
}

function easyreservations_generate_invoice($res, $template = false, $local = false){
	$output = easyreservations_load_invoice_template();
	$invoices = get_option('reservations_invoice_options');
	$settings = $invoices['settings'];
	if(!$template) $template = $settings['maintemplate'];
	if(isset($invoices['invoices'][$template])) $options = $invoices['invoices'][$template];
	else return array('content' => 'Set up an invoice template in Invoice settings first.');

	$invoice = <<<EOF
	$output
EOF;
	$invoice = $invoice.'<--FILENAME-->'.$options['filename'];
	$res->Calculate(true);

	$tags = easyreservations_shortcode_parser($invoice, true);
	foreach($tags as $placeholder){
		$tags = shortcode_parse_atts( $placeholder);
		if($tags[0] == 'logo'){
			if(isset($options['logo']) && !empty($options['logo'])){
				$explode = explode('uploads', $options['logo']);
				if(isset($explode[1])){
					$dir = WP_CONTENT_DIR.'/uploads'.str_replace('\\' ,'/', $explode[1]);
					$filesize = getimagesize($dir);
					$value = '<img src="'.str_replace('\\', '/', $options['logo']).'" style="margin-right:5px;width:'.($filesize[0]*.5).'px;height:'.($filesize[1]*.5).'px;">';
				} else $value = '<img src="'.$options['logo'].'" style="margin-right:5px">';
			} else $value = '';
			$invoice = str_replace('[logo]',$value,$invoice);
		} elseif($tags[0] == 'header-title' || $tags[0] == 'header-h1' || $tags[0] == 'header-content' || $tags[0] == 'address-header' || $tags[0] == 'address' || $tags[0] == 'header-h1-sub' || $tags[0] == 'header-date' || $tags[0] == 'header-h1' || $tags[0] == 'header-h1' || $tags[0] == 'infoblock' || $tags[0] == 'footer-1' || $tags[0] == 'footer-2' || $tags[0] == 'footer-3' || $tags[0] == 'content-h1' || $tags[0] == 'content-header' || $tags[0] == 'content-footer'){
			if(isset($options[$tags[0]]) && !empty($options[$tags[0]])) $value = $options[$tags[0]];
			else $value = '';
			$invoice = str_replace('['.$placeholder.']',$value, $invoice);
		}
	}

	$roomnumber = __(easyreservations_get_roomname($res->resourcenumber, $res->resource));
	$price_per_person = get_post_meta($res->resource, 'easy-resource-price', true);
	if(is_array($price_per_person)) $price_per_person = $price_per_person[0];

	$times = $res->times;
  $table_1_value = '';
	$total_1 = 0;
	$table_2_value = '';
	$total_2 = 0;
	$total_custom = 0;
	$table_tax_value = '';
	$table_tax_value_1 = '';
	$table_tax_value_2 = '';
	$tax_amount = 0;
	$table_tax = 0;
	$separated = 0;
	$table_price_value = '';
	$custom_fields = get_option('reservations_custom_fields');
	$custom_fields = $custom_fields['fields'];

	foreach($res->history as $exactly){
		if($exactly['type'] == 'groundprice'){
			$legend = str_replace(array('[dailydate]','[dailydatetime]', '[theprice]'), array(date(RESERVATIONS_DATE_FORMAT, $exactly['date']),date(RESERVATIONS_DATE_FORMAT.' H:i', $exactly['date']), easyreservations_format_money($exactly['priceday'],1)),$options['seperatedlegend']);
			$class = empty($table_1_value) ? ' class="obe"' : '';
			if(isset($options['calc']) && $options['calc'] == 1 && $price_per_person == 1) $exactly['priceday'] = $exactly['priceday']*$res->adults;
			if($options['price'] == 0) $table_1_value .= '<tr'.$class.'><td class="message">'.$legend.'</td><td class="price" axis="oben">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			$total_1 += $exactly['priceday'];
		} elseif($exactly['type'] == 'pricefilter'){
			$legend = str_replace(array('[dailydate]', '[dailydatetime]', '[theprice]', '[filtername]'), array(date(RESERVATIONS_DATE_FORMAT, $exactly['date']), date(RESERVATIONS_DATE_FORMAT.' H:i', $exactly['date']), easyreservations_format_money($exactly['priceday'],1), $exactly['name']),$options['groundpricelegend']);
			$class = empty($table_1_value) ? ' class="obe"' : '';
			if(isset($options['calc']) && $options['calc'] == 1 && $price_per_person == 1) $exactly['priceday'] = $exactly['priceday']*$res->adults;
			if($options['price'] == 0) $table_1_value .= '<tr'.$class.'><td class="message">'.$legend.'</td><td class="price" axis="oben">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			$total_1 += $exactly['priceday'];
		} elseif($exactly['type'] == 'persons' && (!isset($options['calc']) || $options['calc'] == 0)){
			if($options['persons'] == 1) $table_2_value .= '<tr class="bot"><td class="message">'.$options['personslegend'].'</td><td class="price" axis="persons" title="'.$exactly['name'].'">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			else $separated += $exactly['priceday'];
			$total_2 += $exactly['priceday'];
		} elseif($exactly['type'] == 'childs'){
			if($options['childs'] == 1) $table_2_value .= '<tr class="bot"><td class="message">'.$options['childslegend'].'</td><td class="persons" axis="unten" title="'.$exactly['name'].'">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			else $separated += $exactly['priceday'];
			$total_2 += $exactly['priceday'];
		} elseif($exactly['type'] == 'stay' || $exactly['type'] == 'loyal' || $exactly['type'] == 'pers' || $exactly['type'] == 'early' || $exactly['type'] == 'adul' || $exactly['type'] == 'child' || $exactly['type'] == 'charge' || $exactly['type'] == 'discount'){
			$legend = str_replace(array('[cond]', '[theprice]', '[filtername]'), array((isset($exactly['cond'])) ? $exactly['cond'] : '', easyreservations_format_money($exactly['priceday'],1), $exactly['name']),$options['discountlegend']);
			if($options['discount'] == 1) $table_2_value .= '<tr class="bot"><td class="message">'.$legend.'</td><td class="price" axis="unten">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			else $separated += $exactly['priceday'];
			$total_2 += $exactly['priceday'];
		} elseif($exactly['type'] == 'customp_p' || $exactly['type'] == 'customp_n' || $exactly['type'] == 'customp'){
			if($exactly['type'] == 'customp') $legend = str_replace(array('[customamount]', '[customvalue]', '[theprice]', '[customtitle]'), array($exactly['priceday'], $custom_fields[$exactly['id']]['options'][$exactly['value']]['value'], easyreservations_format_money($exactly['priceday'],1), $custom_fields[$exactly['id']]['title']),$options['customlegend']);
			else $legend = str_replace(array('[customamount]', '[customvalue]', '[theprice]', '[customtitle]'), array($exactly['amount'], $exactly['value'], easyreservations_format_money($exactly['priceday'],1), $exactly['name']),$options['customlegend']);
			if($options['custom'] == 1) $table_price_value .= '<tr class="bot"><td class="message">'.$legend.'</td><td class="price" axis="unten">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			else $separated += $exactly['priceday'];
			$total_2 += $exactly['priceday'];
			$total_custom += $exactly['priceday'];
		} elseif($exactly['type'] == 'coupon'){
			$legend = str_replace(array('[couponcode]', '[theprice]'), array($exactly['name'], easyreservations_format_money($exactly['priceday'],1), $exactly['name']),$options['couponlegend']);
			if($options['coupon'] == 1) $table_2_value .= '<tr class="bot"><td class="message">'.$legend.'</td><td class="price" axis="unten">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			else $separated += $exactly['priceday'];
			$total_2 += $exactly['priceday'];
		} elseif($exactly['type'] == 'tax'){
			$legend = str_replace(array('[taxname]','[tax]', '[theprice]'), array($exactly['name'], $exactly['amount'], easyreservations_format_money($exactly['priceday'],1), $exactly['name']),$options['taxlegend']);
			$thetax = '<tr class="tax" id="tax"><td class="message" style="text-align:right">'.$legend.'</td><td class="price" axis="tax" title="'.$exactly['amount'].'">'.easyreservations_format_money($exactly['priceday'],1).'</td></tr>';
			if($options['tax'] == 1){
				if(isset($exactly['class']) && $exactly['class'] == 2){
					if(!empty($total_2) && empty($table_tax_value_2)) $table_tax_value_2 .= '<tr class="seperator" id="seperator"><td class="message" style="text-align:right;">'.$options['subtotal2legend'].'</td><td class="price" axis="subtotal1">'.easyreservations_format_money($total_1+$total_2,1).'</td></tr>';
					$table_tax_value_2 .= $thetax;
				} elseif(isset($exactly['class']) && $exactly['class'] == 1){
					if(!empty($total_2) && empty($table_tax_value_1)) $table_tax_value_1 .= '<tr class="seperator" id="seperator"><td class="message" style="text-align:right;">'.$options['subtotal1legend'].'</td><td class="price" axis="subtotal1">'.easyreservations_format_money($total_1+$total_2-$total_custom,1).'</td></tr>';
					$table_tax_value_1 .= $thetax;
				} else{
					$table_tax_value .= $thetax;
					$tax_amount += $exactly['amount'];
				}
				$total_2 += $exactly['priceday'];
				$table_tax += $exactly['priceday'];
			} else {
				$separated += $exactly['priceday'];
				$total_2 += $exactly['priceday'];
			}
		}
	}

	if($options['price'] == 1){
		$legend = str_replace(array('[dailyprice]', '[theprice]'), array(easyreservations_format_money(($total_1/$times),1), easyreservations_format_money($exactly['priceday'],1)),$options['togetherlegend']);
		if(isset($options['calc']) && $options['calc'] == 1 && $price_per_person == 1) $exactly['priceday'] = $total_1*$res->adults;
		$table_1_value = '<tr class="obe"><td class="message">'.$legend.'</td><td class="price" axis="oben" style="border-top: 0.001mm solid black">'.easyreservations_format_money($total_1,1).'</td></tr>';
	}

	if($separated > 0 && $options['summarized'] == 1){
		$legend = str_replace(array('[theprice]'), array(easyreservations_format_money($separated,1)),$options['summarizedlegend']);
		$table_2_value .= '<tr class="bot"><td class="message" >'.$legend.'</td><td class="price" axis="oben">'.easyreservations_format_money($separated,1).'</td></tr>';
	}

	$table_head = '<thead><tr><th class="messagehead" style="border-bottom: 0.001mm solid black;">'.$options['messagehead'].'</th><th class="pricehead">'.$options['pricehead'].'</th></tr></thead><tbody>';
	if($options['subtotal1'] == 1 && $table_2_value != '') $table_seperator = '<tr class="seperator" id="seperator"><td class="message" style="text-align:right;">'.$options['subtotal1legend'].'</td><td class="price" axis="subtotal1">'.easyreservations_format_money($total_1,1).'</td></tr>';
	else $table_seperator = '';
	if($options['subtotal2'] == 1 && !isset($res->fixed)) $table_total = '<tr class="total" id="total"><td class="message" style="text-align:right">'.$options['subtotal2legend'].'</td><td class="price" axis="subtotal2">'.easyreservations_format_money($total_1+$total_2,1).'</td></tr>';
	else $table_total = '';
	if($options['paid'] == 1) $table_total .= '<tr class="paid" id="paid"><td class="message" style="text-align:right">'.$options['paidlegend'].'</td><td class="price" axis="paid">'.easyreservations_format_money('-'.$res->paid,1).'</td></tr>';
	else $res->paid = 0;
	$table_total .= '<tr class="sum" id="sum"><td class="message" style="text-align:right">'.$options['sumlegend'].'</td><td class="price"  axis="total"><strong>'.easyreservations_format_money(($res->price-$res->paid),1).'</strong></td></tr>';
	if($options['due']  == 1) $table_total .= '<tr class="due" id="due"><td class="message" style="text-align:right"> '.__('Due date', 'easyReservations').'</td><td class="price"><strong>'.$options['duedate'].'</strong></td></tr>';
	$table_top = '<table class="invoice" editablecontent="true" id="editable" cellpadding="0" cellspacing="0" style="width:100%">';
	if(isset($res->fixed) && $res->fixed){
		$table_tax_value = '';
		if($tax_amount > 0){
			$table_tax_value = '<tr class="tax" id="tax"><td class="message" style="text-align:right">Tax '.$tax_amount.'%</td><td class="price" axis="tax" title="'.$exactly['amount'].'">'.easyreservations_format_money($table_tax,1).'</td></tr>';
		}
		$table_1_value = '<tr class="bot"><td class="message">'.$res->resourcename.' '.$res->times.'x</td><td class="price" axis="unten">'.easyreservations_format_money($res->price-$table_tax,1).'</td></tr>';
		$table_2_value = ''; $table_seperator = ''; $table_tax_value_1 = ''; $table_tax_value_2 = ''; $table_price_value = '';
	}
	$table = $table_top.$table_head.$table_1_value.$table_seperator.$table_2_value.$table_tax_value_1.$table_price_value.$table_tax_value_2.$table_tax_value.$table_total.'</tbody></table>';
	$invoice = str_replace('[table]', $table, $invoice);
	$invoices_nr = get_option('reservations_invoice_number');
	if(!$invoices_nr || $invoices_nr['nr'] < 1) $invoices_nr['nr'] = 1;

	$customs = $res->getCustoms($res->custom, 'cstm', false, true);
	$custom_prices = $res->getCustoms($res->prices, 'cstm', false, true);

	$invoice = stripslashes($invoice);
	$invoice = apply_filters( 'easy-invoice-content', $invoice, $local);
	$tags = easyreservations_shortcode_parser($invoice, true);
	foreach($tags as $placeholder){
		$tags=shortcode_parse_atts( $placeholder);
		if($tags[0]=="custom"){
			$content = '';
			if(isset($tags['id'])){
				if(isset($custom_fields[$tags['id']])){
					$custom_field = $custom_fields[$tags['id']];
					if(isset($custom_field['price'])) $array = $custom_prices;
					else $array = $customs;
					if(isset($array['c'.$tags['id']])){
						$cstm = $array['c'.$tags['id']];
						if(!isset($tags['show'])){
							$content = $res->getCustomsValue($cstm);
							if(isset($custom_field['price'])) $content .= ' ('.easyreservations_format_money($res->calculateCustom($tags['id'], $cstm['value'], $res->prices),1).')';
						} elseif($tags['show'] == 'title') $content = $custom_field['title'];
						elseif($tags['show'] == 'value'){
							$content = $res->getCustomsValue($cstm);
						} elseif($tags['show'] == 'amount') $content = easyreservations_format_money($res->calculateCustom($tags['id'], $cstm['value'], $res->prices),1);
					} else $content = $custom_field['else'];
				}
			} elseif(!empty($res->custom)){
				foreach($customs as $custom){
					if(isset($tags[1]) && $tags[1] == $custom['title']){
						$content = $custom['value'];
						break;
					}
				}
			}

			$invoice = str_replace('['.$placeholder.']',$content, $invoice);
		} elseif($tags[0]=="arrival"){
			if(isset($tags[1]) && $tags[1] == "time") $datestring = RESERVATIONS_DATE_FORMAT. ' H:i';
			else $datestring = RESERVATIONS_DATE_FORMAT;
			$invoice = str_replace('['.$placeholder.']', date($datestring, $res->arrival), $invoice);
		} elseif($tags[0]=="departure"){
			if(isset($tags[1]) && $tags[1] == "time") $datestring = RESERVATIONS_DATE_FORMAT. ' H:i';
			else $datestring = RESERVATIONS_DATE_FORMAT;
			$invoice = str_replace('['.$placeholder.']', date($datestring, $res->departure), $invoice);
		} elseif($tags[0]=="invoice_number"){
			$amount = '';
			if(isset($invoices_nr['stay']) && is_array($invoices_nr['id']) && in_array($res->id,$invoices_nr['id'])){
				$tag = array_search($res->id,$invoices_nr['id']);
				$count = count($invoices_nr['id']);
				$invoices_nr['nr'] = (int) $invoices_nr['nr'] - $count + $tag;
			}
			if(isset($tags[1])) for($i = 0; $i < $tags[1]-strlen($invoices_nr['nr']); $i++) $amount .= '0';
			$invoice = str_replace('['.$placeholder.']', $amount.$invoices_nr['nr'], $invoice);
		} elseif($tags[0]=="persons"){
			$invoice = str_replace('['.$tags[0].']', ($res->adults+$res->childs), $invoice);
		} elseif($tags[0]=="adults"){
			$invoice = str_replace('['.$tags[0].']', $res->adults, $invoice);
		} elseif($tags[0]=="childs"){
			$invoice = str_replace('['.$tags[0].']', $res->childs, $invoice);
		} elseif($tags[0]=="price"){
			$res->Calculate();
			$invoice = str_replace('['.$tags[0].']', easyreservations_format_money($res->price,1), $invoice);
		} elseif($tags[0]=="subtotal1"){
			$invoice = str_replace('['.$tags[0].']', easyreservations_format_money($total_1,1), $invoice);
		} elseif($tags[0]=="total"){
			$res->Calculate();
			$invoice = str_replace('['.$tags[0].']', easyreservations_format_money($res->price-$res->paid,1), $invoice);
		} elseif($tags[0]=="taxes"){
			$invoice = str_replace('['.$tags[0].']', easyreservations_format_money($table_tax,1), $invoice);
		} elseif($tags[0]=="subtotal2"){
			$invoice = str_replace('['.$tags[0].']', easyreservations_format_money($total_2,1), $invoice);
		} elseif($tags[0]=="times" || $tags[0]=="units"){
			$invoice = str_replace('['.$tags[0].']', $times, $invoice);
		} elseif($tags[0]=="paid"){
			$invoice = str_replace('['.$tags[0].']', easyreservations_format_money($res->paid,1), $invoice);
		} elseif($tags[0]=="email"){
			$invoice = str_replace('['.$tags[0].']', $res->email, $invoice);
		} elseif($tags[0]=="country"){
			$invoice = str_replace('['.$tags[0].']', easyreservations_country_name($res->country), $invoice);
		} elseif($tags[0]=="name" || $tags[0]=="thename"){
			$invoice = str_replace('['.$tags[0].']', $res->name, $invoice);
		} elseif($tags[0]=="res_id" || $tags[0]=="ID"){
			$amount = '';
			if(isset($tags[1])) for($i = 0; $i < $tags[1]-strlen($res->id); $i++) $amount .= '0';
			$invoice = str_replace('['.$placeholder.']', $amount.$res->id, $invoice);
		} elseif($tags[0]=="resource"){
			$invoice = str_replace('['.$tags[0].']', $res->resourcename, $invoice);
		} elseif($tags[0]=="resource-number" || $tags[0] == "resourcenumber"){
			$invoice = str_replace('['.$placeholder.']', $roomnumber, $invoice);
		} elseif($tags[0]=="date"){
			if(isset($tags[1])) $form= $tags[1];
			else $form = RESERVATIONS_DATE_FORMAT;
			if(isset($tags[2])) $date = date($form, strtotime(str_replace('"', '',$tags[2])));
			else $date = date($form, time());
			$invoice = str_replace('['.$placeholder.']', $date, $invoice);
		} elseif($tags[0]=="datetime"){
			if(isset($tags[1])) $form= $tags[1].' H:i';
			else $form = RESERVATIONS_DATE_FORMAT.' H:i';
			if(isset($tags[2])) $date = date($form, strtotime(str_replace('"', '',$tags[2])));
			else $date = date($form, time());
			$invoice = str_replace('['.$placeholder.']', $date, $invoice);
		} elseif($tags[0]=="due"){
			if(isset($tags[1])) $time = time()+($tags[1]*86400);
			else $time = time()+(30*86400);
			if(isset($tags[2])) $date = date($tags[1], $time);
			else $date = date(RESERVATIONS_DATE_FORMAT, $time);
			$invoice = str_replace('['.$placeholder.']', $date, $invoice);
		}
	}

	$divide = explode('<--FILENAME-->',$invoice);
	$invoice = $divide[0];

	return array('content' => $invoice, 'name' => '', 'filename' => $divide[1]);
}

add_action('easy-view-title-right', 'easyreservations_generate_invoice_form', 10, 1);
add_action('easy-edit-title-right', 'easyreservations_generate_invoice_form', 10, 1);

function easyreservations_insert_attachment($res, $where, $template = false, $content = false){
	$invoices = get_option('reservations_invoice_options');
	$settings = $invoices['settings'];
	if(!$template) $template = $settings['guesttemplate'];
	if((isset($settings['email_'.$where]) && $settings['email_'.$where] !== 0) || !$where){
		if($where && isset($settings['email_'.$where])){
			if($settings['email_'.$where] == 1) $template = $settings['guesttemplate'];
			else $template = $settings['email_'.$where];
		}
		if(!$content) $content = easyreservations_generate_invoice($res, str_replace('*', '', $template));
		if(is_array($content)){
			if(isset($content['filename'])){
				$filename = $content['filename'];
				$content = ($content['content']);
			} else return false;
		} else $content = ($content);
		if(empty($filename) || strlen($filename) < 2) $filename = 'Invoice-'.$res->id.'-'.date("Y-m-d-H-i");
		$filename = str_replace('/', '-', $filename);
		require_once(dirname(__FILE__).'/html2pdf/html2pdf.class.php');
		try {
			$html2pdf = new HTML2PDF('P', 'A4', 'en');
			$html2pdf->pdf->SetDisplayMode('real');
			$html2pdf->pdf->setImageScale(0.57);
			$html2pdf->setDefaultFont('helvetica');
			$html2pdf->pdf->SetProtection(array('print'), '', 'admin_password');
			$html2pdf->writeHTML($content, false);
			$invoices = get_option('reservations_invoice_number');
			if(!is_array($invoices)) $invoices = array('id' => array(), 'nr' => 2);
			if(!isset($invoices['id']) || !is_array($invoices['id'])) $invoices['id'] = array();
			if(!in_array($res->id, $invoices['id'])){
				$invoices['id'][] = $res->id;
				$invoices['nr'] = $invoices['nr'] +1;
				update_option('reservations_invoice_number', $invoices);
			}
			$temp = sys_get_temp_dir();
			if($temp && is_writable($temp)){
				if(substr($temp,-1,1) !== '/') $temp = $temp.'/';
				$filepath = $temp.$filename.'.pdf';
			} else $filepath = addslashes(RESERVATIONS_DIR.'lib/modules/invoice/'.$filename.'.pdf');
			$html2pdf->Output($filepath, 'F');
			return $filepath;
		} catch(HTML2PDF_exception $e) {
			var_dump($e->getMessage());
			return false;
		}
	}
	return false;
}

if(is_admin()){
	add_action('er_set_tab_add', 'easyreservations_invoice_add_settings_tab');

	function easyreservations_invoice_add_settings_tab(){
		if(isset($_GET['site']) && $_GET['site'] == "invoice"){
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			$current = 'current'; 
		} else $current = '';
		echo '<li ><a href="admin.php?page=reservation-settings&site=invoice" class="'.$current.'"><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'images/invoice.png"> '. __( 'Invoice' , 'easyReservations' ).'</a></li>';
	}

	add_action('er_set_save', 'easyreservations_invoice_save_settings');

	function easyreservations_invoice_save_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "invoice"){
			if(isset($_POST['action']) && $_POST['action'] == "reservation_invoice_settings" && !empty($_POST['name'])){
				$invoices = get_option('reservations_invoice_options');
				$active = $_POST['active'];
				if (!wp_verify_nonce($_POST['easy-set-invoice'], 'easy-set-invoice' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
				$_POST = array_map('stripslashes', $_POST);
				if(isset($_POST['due'])) $due = 1; else $due = 0;
				if(isset($_POST['paid'])) $paid = 1; else $paid = 0;
				if(isset($_POST['custom'])) $custom = 1; else $custom = 0;
				if(isset($_POST['childs'])) $childs = 1; else $childs = 0;
				if(isset($_POST['persons'])) $persons = 1; else $persons = 0;
				if(isset($_POST['tax'])) $tax = 1; else $tax = 0;
				if(isset($_POST['subtotal2'])) $subtotal2 = 1; else $subtotal2 = 0;
				if(isset($_POST['subtotal'])) $subtotal = 1; else $subtotal = 0;
				if(isset($_POST['discount'])) $discount = 1; else $discount = 0;
				if(isset($_POST['coupon'])) $coupon = 1; else $coupon = 0;
				if(isset($_POST['summarized'])) $summarized = 1; else $summarized = 0;
				if(isset($_POST['calc'])) $calc = 1; else $calc = 0;

				$options = array(
          'name' => $_POST['name'],
          'filename' => $_POST['filename'],
          'logo' => $_POST['logo'],
          'price' => isset($_POST['price']) ? $_POST['price'] : 0,
          'header-h1' => $_POST['header-h1'],
          'header-h1-sub' => $_POST['header-h1-sub'],
          'header-date' => $_POST['header-date'],
          'header-content' => $_POST['header-content'],
          'header-title' => $_POST['header-title'],
          'infoblock' => $_POST['infoblock'],
          'address-header' => $_POST['address-header'],
          'address' => $_POST['address'],
          'content-header' => $_POST['content-header'],
          'content-h1' => $_POST['content-h1'],
          'content-footer' => $_POST['content-footer'],
          'footer-1' => $_POST['footer-1'],
          'footer-2' => $_POST['footer-2'],
          'footer-3' => $_POST['footer-3'],
          'calc' => $calc,
          'due' => $due,
          'duelegend' => $_POST['duelegend'],
          'duedate' => $_POST['duedate'],
          'paid' => $paid,
          'paidlegend' => $_POST['paidlegend'],
          'discount' => $discount,
          'discountlegend' => $_POST['discountlegend'],
          'summarized' => $summarized,
          "summarizedlegend" => $_POST['summarizedlegend'],
          'custom' => $custom,
          'customlegend' => $_POST['customlegend'],
          'persons' => $persons,
          'personslegend' => $_POST['personslegend'],
          'seperatedlegend' => $_POST['seperatedlegend'],
          'messagehead' => $_POST['messagehead'],
          'pricehead' => $_POST['pricehead'],
          'groundpricelegend' => $_POST['groundpricelegend'],
          'togetherlegend' => $_POST['togetherlegend'],
          'childs' => $childs,
          'childslegend' => $_POST['childslegend'],
          'coupon' => $coupon,
          'couponlegend' => $_POST['couponlegend'],
          'subtotal2' => $subtotal2,
          'subtotal2legend' => $_POST['subtotal2legend'],
          'subtotal1' => $subtotal,
          'subtotal1legend' => $_POST['subtotal1legend'],
          'tax' => $tax,
          'taxlegend' => $_POST['taxlegend'],
          'sumlegend' => $_POST['sumlegend']
         );

				if(isset($_POST['liveprice'])) $liveprice = 1; else $liveprice = 0;
				if(isset($_POST['email_sendmail'])) $email_sendmail = $_POST['email_sendmail_template']; else $email_sendmail = 0;
				if(isset($_POST['email_to_admin'])) $email_to_admin = $_POST['email_to_admin_template']; else $email_to_admin = 0;
				if(isset($_POST['email_to_user'])) $email_to_user = $_POST['email_to_user_template']; else $email_to_user = 0;
				if(isset($_POST['email_to_userapp'])) $email_to_userapp = $_POST['email_to_userapp_template']; else $email_to_userapp = 0;
				if(isset($_POST['email_to_userdel'])) $email_to_userdel = $_POST['email_to_userdel_template']; else $email_to_userdel = 0;
				if(isset($_POST['email_to_user_admin_edited'])) $email_to_user_admin_edited = $_POST['email_to_user_admin_edited_template']; else $email_to_user_admin_edited = 0;
				if(isset($_POST['email_to_admin_edited'])) $email_to_admin_edited = $_POST['email_to_admin_edited_template']; else $email_to_admin_edited = 0;
				if(isset($_POST['email_to_user_edited'])) $email_to_user_edited = $_POST['email_to_user_edited_template']; else $email_to_user_edited = 0;
				if(isset($_POST['email_to_admin_paypal'])) $email_to_admin_paypal = $_POST['email_to_admin_paypal_template']; else $email_to_admin_paypal = 0;
				if(isset($_POST['email_to_admin_canceled'])) $email_to_admin_canceled = $_POST['email_to_admin_canceled_template']; else $email_to_admin_canceled = 0;
				if(isset($_POST['email_to_user_paypal'])) $email_to_user_paypal = $_POST['email_to_user_paypal_template']; else $email_to_user_paypal = 0;

				$settings = array(
          'maintemplate' => isset($_POST['maintemplate']) ? $_POST['maintemplate'] : 0,
          'guesttemplate' => isset($_POST['guesttemplate']) ? $_POST['guesttemplate'] : 0,
          'liveprice' => $liveprice,
          'email_sendmail' => $email_sendmail,
          'email_to_admin' => $email_to_admin,
          'email_to_user' => $email_to_user,
          'email_to_userapp' => $email_to_userapp,
          'email_to_userdel' => $email_to_userdel,
          'email_to_user_admin_edited' => $email_to_user_admin_edited,
          'email_to_admin_edited' => $email_to_admin_edited,
          'email_to_user_edited' => $email_to_user_edited,
          'email_to_admin_paypal' => $email_to_admin_paypal,
          'email_to_admin_canceled' => $email_to_admin_canceled,
          'email_to_user_paypal' => $email_to_user_paypal
        );
				$invoices['settings'] = $settings;
				if(isset($_GET['add'])) $invoices['invoices'][] = $options;
				else $invoices['invoices'][$active] = $options;

				update_option('reservations_invoice_options', $invoices);

				$invoices_nr = get_option('reservations_invoice_number');
				$invoices_nr['nr'] = $_POST['invoicenr'];
				if(isset($_POST['stay'])) $invoices_nr['stay'] = 1;
				elseif(isset($invoices_nr['stay'])) unset($invoices_nr['stay']);
				update_option('reservations_invoice_number', $invoices_nr);

				echo '<br><div class="updated"><p>'.__( 'Invoice settings changed' , 'easyReservations').'</p></div>';
			} elseif(isset($_GET['delete'])){
				$invoices = get_option('reservations_invoice_options');
				unset($invoices['invoices'][$_GET['delete']]);
				update_option('reservations_invoice_options', $invoices);
			}
		} 
	}

	add_action('er_set_add', 'easyreservations_invoice_add_settings');

	function easyreservations_invoice_add_settings(){
		if(isset($_GET['site']) && $_GET['site'] == "invoice"){
		$standard = array(
			'header-content' => 'Yourstreet Nr<br>
Zip City<br>
<br>
email: your@emailcom<br>
phone: your/phone',
			'infoblock' => '<b>Referenece</b>: #[res_id 5]
<br><b>Invoice</b>: #[invoice_number 5]
<br><b>Persons</b>: [persons]
<br><b>Arrival</b>: [arrival]
<br><b>Departure</b>: [departure]
<b>Resource</b>: [resource]',
			'address' => '[thename]<br>
[custom Street]<br>
[custom City] [custom PostCode]<br>
[country]',
			'content-header' => 'Dear [thename],<br>
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
			'content-h1' => 'Invoice',
			'content-footer' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam.',
			'duelegend' => 'Due date',
			'duedate' => '[due 30]',
			'paidlegend' => 'Already paid',
			'discountlegend' => '[filtername]',
			'summarizedlegend' => 'Discounts',
			'customlegend' => '[customtitle] per [persons] person',
			'personslegend' => 'Price per [adults] adults',
			'seperatedlegend' => '[dailydate] [resource] #[resource-number]',
			'messagehead' => 'Summary',
			'pricehead' => 'Price',
			'groundpricelegend' => '[dailydate] [resource] #[resource-number] ([filtername])',
			'togetherlegend' => '[arrival] - [departure] [resource] [units] times a [dailyprice]',
			'childslegend' => 'Price per [childs] childrens',
			'couponlegend' => '[couponcode]',
			'subtotal2legend' => 'Subtotal',
			'subtotal1legend' => 'Subtotal',
			'price' => 0,
			'calc' => 0,
			'taxlegend' => 'Tax [tax]%',
			'name' => 'Test Admin',
			'filename' => 'invoice-[resource]-[date]',
			'header-h1' => 'Invoice',
			'header-h1-sub' => 'Date',
			'header-date' => '[date]',
			'address-header' => 'YourCompany | Yourstreet Nr | Zip City',
			'header-title' => 'YourCompany',
			'sumlegend' => 'Total',
			'footer-1' => 'HR-Nummer: HRB 130005<br>
Geschäftsführer: Max Mustermann<br>
USt.IDNummer: DE324325243<br>
Steuernummer: 59/704/00831',
			'footer-2' => 'HR-Nummer: HRB 130005<br>
Geschäftsführer: Max Mustermann<br>
USt.IDNummer: DE324325243<br>
Steuernummer: 59/704/00831',
			'footer-3' => 'HR-Nummer: HRB 130005<br>
Geschäftsführer: Max Mustermann<br>
USt.IDNummer: DE324325243<br>
Steuernummer: 59/704/00831'
		);

			$invoices = get_option('reservations_invoice_options');
			if(isset($_GET['invoice'])) $active = $_GET['invoice'];
			else {
				$active = 0;
				$key = -1;
			}
			if(isset($invoices['invoices'][$active])){
				$is_active = true;
				$options = $invoices['invoices'][$active];
				$settings = $invoices['settings'];
			} else {
				$is_active = false;
				$options = array();
				$settings = array( 'maintemplate' => 0, 'guesttemplate' => 0,'liveprice' => 0);
			}
			if(!empty($invoices)){
				$navigation = '';
				foreach($invoices['invoices'] as $key => $invoice){
					$class = isset($active) && $active == $key ? ' class="curr"' : '';
					$navigation .= '<li'.$class.'><a href="admin.php?page=reservation-settings&site=invoice&invoice='.$key.'">'.$invoice['name'].'</a> <a href="admin.php?page=reservation-settings&site=invoice&delete='.$key.'"><img style="vertical-align:text-bottom;" src="'.RESERVATIONS_URL.'images/delete.png"></a></li>';
				}
			} else $navigation = '<strong>None</strong>';
			$invoices_nr = get_option('reservations_invoice_number');
			if(!$invoices_nr || $invoices_nr['nr'] < 1) $invoices_nr['nr'] = 1;
			//if(empty($options)) $options = array('message' => 'Add special instructions to merchant', 'title' => '[resource] for [times] days | [arrival] - [departure]', 'owner' => '', 'modus' => 'off', 'currency' => 'USD', 'button' => 'https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif')?>
			<form method="post" action="admin.php?page=reservation-settings&site=invoice&invoice=<?php echo $active; ?>"  id="reservation_invoice_settings" name="reservation_invoice_settings">
				<?php wp_nonce_field('easy-set-invoice','easy-set-invoice'); ?>
				<input type="hidden" name="action" value="reservation_invoice_settings">
				<input type="hidden" id="add" name="add" value="0">
				<input type="hidden" id="active" name="active" value="<?php echo $active; ?>">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%">
					<thead>
						<tr>
							<th colspan="3"><?php echo __( 'Invoice Settings' , 'easyReservations' );?><input class="easybutton button-primary" type="submit" style="float:right" onclick="document.getElementById('reservation_invoice_settings').submit(); return false;" value="<?php echo __( 'Save' , 'easyReservations' );?>"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style="width: 25%;">Main Invoice Template</td>
							<td style="width: 50%;"><select name="maintemplate" id="idmaintemplate"><?php echo easyreservations_get_invoice_select($settings['maintemplate']); ?></select></td>
							<td rowspan ="5" style="vertical-align: top;border-bottom: 0px">
								<div class="pointers">
									<u><b id="idtags">Overall available tags</b></u><br>
									<p><code style="cursor:default">[arrival]</code> <?php printf ( __( 'arrival date' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[departure]</code> <?php printf ( __( 'departure date' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[units]</code> <?php echo __( 'amount of billing units' , 'easyReservations' );?></p>
									<p><code style="cursor:default">[thename]</code> <?php echo __( 'name' , 'easyReservations' );?></p>
									<p><code style="cursor:default">[email]</code> <?php echo __( 'email' , 'easyReservations' );?></p>
									<p><code style="cursor:default">[resource]</code> <?php echo __( 'Resource' , 'easyReservations' );?></p>
									<p><code style="cursor:default">[resource-number]</code> Resource number</p>
									<p><code style="cursor:default">[persons]</code> <?php printf ( __( 'amount of adults and children' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[adults]</code> <?php printf ( __( 'amount of adults' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[childs]</code> <?php printf ( __( 'amount of children' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[country]</code> <?php printf ( __( 'country of guest' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[custom title]</code> Custom field value</p>
									<p><code style="cursor:default">[price]</code> <?php printf ( __( 'price of reservation' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[paid]</code> <?php printf ( __( 'paid amount' , 'easyReservations' ));?></p>
									<p><code style="cursor:default">[subtotal1]</code> Subtotal 1</p>
									<p><code style="cursor:default">[subtotal2]</code> Subtotal 2</p>
									<p><code style="cursor:default">[taxes]</code> Taxes amount</p>
									<p><code style="cursor:default">[invoice_number]</code> Invoice Number</p>
									<p><code style="cursor:default">[res-id]</code> Reservations ID</p>
									<p><code style="cursor:default">[date]</code> Current date</p>
									<p><code style="cursor:default">[datetime]</code> Current datetime</p>
									<p><code style="cursor:default">[due 30]</code> Date to pay current+nr</p>
								</div>
							</td>
						</tr>
						<tr>
							<td style="width: 25%;">Guest Invoice Template</td>
							<td style="width: 50%;;"><select name="guesttemplate" id="idguesttemplate"><?php echo easyreservations_get_invoice_select($settings['guesttemplate']); ?></select></td>
						</tr>
						<tr>
							<td style="width: 25%;padding:9px">Editor</td>
							<td style="width: 50%;"><input type="checkbox" name="liveprice" <?php checked($settings['liveprice'],1); ?>> Live price calculation as default</td>
						</tr>
						<tr>
							<td style="width: 25%;padding:9px">Invoice Number</td>
							<td style="width: 50%;"><input type="checkbox" name="stay" <?php checked((isset($invoices_nr['stay'])) ? $invoices_nr['stay'] : 0,1); ?>> Keep Invoice number per reservation<br><input type="text" name="invoicenr" id="idinvoicenr" value="<?php echo $invoices_nr['nr']; ?>" style="width:100px;margin:3px 0px 0px 0px"> Number of next Invoice</td>
						</tr>
						<tr>
							<td style="width: 25%;padding:9px;vertical-align: top">Email Attachment</td>
							<td style="width: 50%;">
								<?php
									$emails = easyreservations_get_emails();
									foreach($emails as $key => $email){
										$key = str_replace('reservations_', '', $key);
										echo '<input type="checkbox" name="'.$key.'" '.checked((is_string($settings[$key]) || $settings[$key] == 1) ? true : false,true,false).'> '.$email['name'].' '.__('with template', 'easyReservations').' ';
										echo '<select name="'.$key.'_template"><option value="1" '.selected(($settings[$key] == 0 || $settings[$key] == 1) ? true : false,true,false).'>Guest template</option>'.easyreservations_get_invoice_select(str_replace('*', '', $settings[$key]), '*').'</select><br>';
									}
								?>
							</td>
						</tr>
					</tbody>
				</table>
				<div id="usual1" class="usual" style="margin-top:10px">
				<ul class="navtabs" id="idthetabs"><?php echo $navigation; ?></ul>
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%;">
					<colgroup>
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
						<col width="5%"><col width="5%">
					</colgroup>
					<tbody>
						<tr>
							<td colspan="20" style="border-bottom:1px solid #BFBFBF"><input type="text" title="Name of the Invoice Template" name="name" id="name" value="<?php if(isset($options['name'])) echo htmlspecialchars($options['name']); ?>" style="width:300px;height:35px"> <input type="button" style="margin-top:3px" onclick="document.getElementById('add').value = 1;document.getElementById('reservation_invoice_settings').action = 'admin.php?page=reservation-settings&site=invoice&invoice=<?php echo $key+1; ?>&add';document.getElementById('reservation_invoice_settings').submit(); return false;" class="button-secondary" value="<?php if($is_active) echo __( 'Copy & Add' , 'easyReservations' ); else echo __( 'Add' , 'easyReservations' ); ?>"><input type="button" onclick="easy_set_stand()" class="button-secondary" value="Standard"><span style="float:right"><input type="text" title="Name of the generated .PDF file" name="filename" id="filename" value="<?php if(isset($options['filename'])) echo htmlspecialchars($options['filename']); else echo 'filename';?>" style="width:300px;height:35px"></span></td>
						</tr>
						<tr style="">
							<td colspan="5" style="vertical-align: middle;text-align: center;border-bottom: 0px;padding-top:18px"><div id="upload_area" style=""><?php if(isset($options['logo']) && !empty($options['logo'])){ $explode = explode('uploads', $options['logo']); if(isset($explode[1])) $url = content_url().'/uploads'.$explode[1]; else $url = $options['logo']; echo '<img src="'.$url.'" style="width:120px;height:120px">'; } ?></div><input type="hidden" id="logourl" name="logo" value="<?php if(isset($options['logo']) && !empty($options['logo'])) echo $options['logo']; ?>"></td>
							<td colspan="9" style="border-bottom: 0px;padding-top:18px"><input type="text" name="header-title" id="header-title" tyle="width:100%;margin-bottom: 6px" value="<?php echo htmlspecialchars($options['header-title']); ?>"><br><textarea name="header-content" id="header-content" style="width:100%;height:120px"><?php echo htmlspecialchars($options['header-content']); ?></textarea></td>
							<td colspan="6" style="text-align: right; vertical-align: bottom;border-bottom: 0px;padding-right:11px;padding-top:18px"><input type="text" name="header-h1" id="header-h1" style="width:200px;margin-bottom: 6px;height:34px;text-align: right;font-size: 20px" value="<?php echo htmlspecialchars($options['header-h1']); ?>"><br><input type="text" id="header-h1-sub" name="header-h1-sub" style="width:130px;text-align: right" value="<?php echo htmlspecialchars($options['header-h1-sub']); ?>"> <input type="text" name="header-date" id="header-date" style="width:100px;text-align: right" value="<?php echo htmlspecialchars($options['header-date']); ?>"></td>
						</tr>
						<tr>
							<td  colspan="10"  style="width:49%;border-bottom: 0px">
								<input type="text" name="address-header" id="address-header" style="width:400px;margin:8px 0px 6px 50px;" value="<?php echo htmlspecialchars($options['address-header']);?>"><br>
								<textarea name="address" id="address" style="width:400px;height:100px;margin-left: 50px"><?php echo htmlspecialchars($options['address']); ?></textarea>
							</td>
							<td colspan="10" style="width:49%;text-align: right; vertical-align: top;padding-right:13px;border-bottom: 0px"><textarea name="infoblock" id="infoblock" style="width:100%;height:170px;text-align:right;"><?php echo htmlspecialchars($options['infoblock']); ?></textarea></td>
						</tr>
						<tr>
							<td  colspan="20"  style="width:49%;padding-right:11px;border-bottom: 0px">
								<input type="text" name="content-h1" id="content-h1" style="width:500px;margin:10px 0px 6px 10px;height:34px" value="<?php echo htmlspecialchars($options['content-h1']); ?>"><br>
								<textarea name="content-header" id="content-header" style="width:99%;height:100px;margin-left: 10px"><?php echo htmlspecialchars($options['content-header']); ?></textarea><br>
							</td>
						</tr>
						<tr>
							<td  colspan="20"  style="width:49%;padding-right:11px;border-bottom: 0px">
							<style>
								.pointers > p {margin:0px;padding:0px;}
								#jqueryTooltip {background:#F9F8CC;padding:5px;line-height: 16px;border-color:#565656;max-width:200px;font-size: 11px;}
								table.iheader {width:99%;border: 1px solid #BFBFBF;border-collapse: collapse;	}
								table.iheader > tbody > tr > td {border-bottom: 1px solid #DBDBDB;padding:4px 9px;	background:#F4F4F4;color:#4F4F4F;}
								table.iheader > tbody > tr:last-of-type > td {border-bottom: 1px solid #BFBFBF;}
                .usual {width:100%;border:0;margin:0px;}
                .invoicenavi > span {padding: 3px;margin: 10px;}
								#upload_area:hover {background:#F4F4F4;}
								#upload_area {border: 4px dashed #DDDDDD;display:inline-block;cursor:pointer;width:120px;height: 120px;background:#fff;}
								table.invoice {margin:5px;}
								table.invoice th {text-align: left;border-bottom: 0.1px solid #CCC;padding:6px;font-size:16px;}
								table.invoice td {padding:6px;border:none;border-right:0px !important;font-size:13px;}
								.invoice > tbody > tr > td:last-of-type {border-right:0px !important;padding-right: 11px;}
								th.pricehead {text-align:right !important;text-align: right;}
								table.invoice > tbody > tr > td.price {text-align: right;border-right:0px !important;width:90px;}
								td.message {width:528px;}
								tr.seperator > td {border-top: 0.1px solid #CCC;border-bottom: 0.1px solid #CCC;}
								tr.seperator td.price {font-weight: bold;}
								tr.total > td {border-top: 0.1px solid #CCC;}
							</style>
							<table id="editable" class=" invoice <?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width:100%" padding="0" margin="0" editablecontent="true">
								<thead>
									<tr>
										<th class="messagehead"><input type="text" name="messagehead" id="messagehead" value="<?php echo htmlspecialchars($options['messagehead']); ?>" style="width:150px"></th>
										<th class="pricehead"><input type="text" name="pricehead" id="pricehead" value="<?php echo htmlspecialchars($options['pricehead']); ?>" style="width:150px;text-align:right"></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="message"><input type="radio" id="radio1" name="price" <?php checked($options['price'],0); ?> value="0">
                      <input onclick="document.getElementById('radio1').checked = true;" title="For each baseprice <br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[filtername]</code> <i>Name of the filter</i><br><code>[dailydate]</code> <i>Date of day</i><br><code>[dailydatetime]</code> <i>Datetime of day</i>" type="text" name="seperatedlegend" id="seperatedlegend" style="width:300px" value="<?php echo htmlspecialchars($options['seperatedlegend']); ?>"> &nbsp;<i>For each day</i>
                    </td>
										<td class="price" axis="oben">Base Price Amount</td>
									</tr>
									<tr>
										<td class="message"><input onclick="document.getElementById('radio1').checked = true;" title="For each time filter <br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[filtername]</code> <i>Name of the filter</i><br><code>[dailydate]</code> <i>Date of day</i><br><code>[dailydatetime]</code> <i>Datetime of day</i>" type="text" name="groundpricelegend" id="groundpricelegend" style="width:300px;margin-left:18px" value="<?php echo htmlspecialchars($options['groundpricelegend']); ?>"></td>
										<td class="price" axis="oben">Price filter Amount</td>
									</tr>
									<tr>
										<td class="message"><input type="radio" id="radio2" name="price" <?php checked($options['price'],1); ?> value="1"> <input onclick="document.getElementById('radio2').checked = true;" title="Summarize of time filters and base price<br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[dailyprice]</code> <i>Price/Billing Units</i>" type="text" name="togetherlegend" id="togetherlegend" style="width:300px" value="<?php echo htmlspecialchars($options['togetherlegend']); ?>"> &nbsp;<i>Summarized</i></td>
										<td class="price" axis="oben">Summarized Amount</td>
									</tr>
									<tr id="seperator" class="seperator">
										<td class="message" style=""><input type="checkbox" class="thecheckbox"  name="calc" id="dacalc" <?php checked($options['calc'],1); ?> onclick="document.getElementById('dapersons').checked=false;"> Calculate adults directly in first stage<span style="float:right;;display:inline-block"><input type="checkbox" class="thecheckbox"  name="subtotal" <?php checked($options['subtotal1'],1); ?>> <input type="text" name="subtotal1legend" id="subtotal1legend" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['subtotal1legend']); ?>"></span></td>
										<td class="price" axis="subtotal1"><strong>Subtotal 1 Amount</strong></td>
									</tr>
									<tr>
										<td class="message" style=""><input type="checkbox" class="thecheckbox"  name="persons" id="dapersons" <?php checked($options['persons'],1); ?> onclick="document.getElementById('dacalc').checked=false;"> <input title="Price per adult <br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i>" type="text" name="personslegend" id="personslegend" style="width:300px" value="<?php echo htmlspecialchars($options['personslegend']); ?>"></td>
										<td class="price" axis="persons">Price per Person Amount</td>
									</tr>
									<tr>
										<td class="message" style=""><input type="checkbox" class="thecheckbox"  name="childs" <?php checked($options['childs'],1); ?>> <input title="Price per children <br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i>" type="text" name="childslegend" id="childslegend" style="width:300px" value="<?php echo htmlspecialchars($options['childslegend']); ?>"></td>
										<td class="price" axis="childs">Price per Children Amount</td>
									</tr>
									<tr>
										<td class="message" style=""><input type="checkbox" class="thecheckbox"  name="discount" <?php checked($options['discount'],1); ?>> <input title="For each conditional filter <br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[filtername]</code> <i>Name of the filter</i><br><code>[cond]</code> <i>Condition of the filter</i>" type="text" name="discountlegend" id="discountlegend" style="width:300px" value="<?php echo htmlspecialchars($options['discountlegend']); ?>"></td>
										<td class="price" axis="unten">Discount Filter Amount</td>
									</tr>
									<tr>
										<td class="message" style=""><input type="checkbox" class="thecheckbox"  name="custom" <?php checked($options['custom'],1); ?>> <input title="For each custom price <br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[customtitle]</code> <i>Title of custom field</i><br><code>[customvalue]</code> <i>Value of custom field</i>" type="text" name="customlegend" id="customlegend" style="width:300px" value="<?php echo htmlspecialchars($options['customlegend']); ?>"></td>
										<td class="price" axis="unten">Custom Price Amount</td>
									</tr>
									<tr>
										<td class="message" style=""><input type="checkbox" class="thecheckbox" name="coupon" <?php checked($options['coupon'],1); ?>> <input title="For each coupon code<br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[couponcode]</code> <i>Code of the coupon</i>" type="text" name="couponlegend" id="couponlegend" style="width:300px" value="<?php echo htmlspecialchars($options['couponlegend']); ?>"></td>
										<td class="price" axis="unten">Coupon Amount</td>
									</tr>
									<tr>
										<td class="message" style=""><input type="checkbox" class="thecheckbox"  name="summarized" <?php checked($options['summarized'],1); ?>> <input title="Summarize of deactivated options in this stage so the calculation is correct<br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i>" type="text" name="summarizedlegend" id="summarizedlegend" style="width:300px" value="<?php echo htmlspecialchars($options['summarizedlegend']); ?>"></td>
										<td class="price" axis="unten">Summarized Amount</td>
									</tr>
									<tr id="total" class="total">
										<td class="message" style="text-align:right"><input type="checkbox" class="thecheckbox"  name="subtotal2" <?php checked($options['subtotal2'],1); ?>> <input id="subtotal2legend" type="text" name="subtotal2legend" id="subtotal2legend" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['subtotal2legend']); ?>"></td>
										<td class="price" axis="subtotal2"><strong>Subtotal 2 Amount</strong></td>
									</tr>
									<tr id="tax" class="tax">
										<td class="message" style="text-align:right"><input type="checkbox" class="thecheckbox"  name="tax" <?php checked($options['tax'],1); ?>> <input id="taxlegend" title="For each tax<br><b><u>Available tags</u></b><br><code>[theprice]</code> <i>Price of this row</i><br><code>[tax]</code> <i>Percantage of tax</i><br><code>[taxname]</code> <i>Name of tax</i>" type="text" id="taxlegend" name="taxlegend" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['taxlegend']); ?>"></td>
										<td class="price" axis="tax"><strong>Tax Amount</strong></td>
									</tr>
									<tr id="paid" class="paid">
										<td class="message" style="text-align:right"><input type="checkbox" class="thecheckbox"  name="paid"  <?php checked($options['paid'],1); ?>> <input id="paidlegend" type="text" name="paidlegend" id="paidlegend" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['paidlegend']); ?>"></td>
										<td class="price" axis="paid"><strong>Paid Amount</strong></td>
									</tr>
									<tr id="sum" class="sum">
										<td class="message" style="text-align:right"><input type="text" name="sumlegend" id="sumlegend" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['sumlegend']); ?>"></td>
										<td class="price" axis="total"><strong>Total</strong></td>
									</tr>
									<tr id="due" class="due">
										<td class="message" style="text-align:right"> <input type="checkbox" class="thecheckbox"  name="due" <?php checked($options['due'],1); ?>> <input type="text" id="duelegend" name="duelegend" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['duelegend']); ?>"></td>
										<td class="price"><strong><input type="text" id="duedate" name="duedate" style="width:100px;text-align:right" value="<?php echo htmlspecialchars($options['duedate']); ?>"></strong></td>
									</tr>
								</tbody>
							</table>
							</td>
						</tr>
						<tr>
							<td  colspan="20"  style="width:49%;padding-right:11px;border-bottom: 0px">
								<textarea name="content-footer" id="content-footer" style="width:99%;height:100px;margin-left: 10px"><?php echo htmlspecialchars($options['content-footer']); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="6" style="padding-bottom:5px"><textarea name="footer-1" id="footer-1" style="width:99%;height:100px;margin-left: 10px"><?php echo htmlspecialchars($options['footer-1']); ?></textarea></td>
							<td colspan="8" style="padding-bottom:5px"><textarea name="footer-2" id="footer-2" style="width:99%;height:100px;margin-left: 10px"><?php echo htmlspecialchars($options['footer-2']); ?></textarea></td>
							<td colspan="6" style="padding-bottom:5px"><textarea name="footer-3" id="footer-3" style="width:97%;height:100px;margin-left: 10px;"><?php echo htmlspecialchars($options['footer-3']); ?></textarea></td>
						</tr>
					</tbody>
				</table>
				</div>
				<?php if($is_active){ ?><input type="button" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>" onclick="document.getElementById('add').value = 0;document.getElementById('reservation_invoice_settings').action = 'admin.php?page=reservation-settings&site=invoice&invoice=<?php echo $active; ?>';document.getElementById('reservation_invoice_settings').submit(); return false;" style="margin-top:7px;" class="easybutton button-primary" style="margin-top:4px" ><?php } ?>
			</form>
			<script>
				jQuery(document).ready(function() {
					jQuery('#upload_area').click(function() {
						formfield = jQuery('#logourl').attr('name');
						tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true&amp;inline=true');
						return false;
					});

					window.send_to_editor = function(html) {
						var imgurl = jQuery(html).attr('src');
						var imgval = imgurl;
						if(imgurl.search("uploads") > 0 && "<?php echo ini_get('allow_url_fopen'); ?>" != "on"){
							var splited = imgurl.split('uploads');
							imgval = "<?php $dir = str_replace('\\','/',WP_CONTENT_DIR); echo $dir; ?>/uploads"+splited[1];
							imgval = imgval.replace(/\//g, '\\');
						}
						jQuery('#logourl').val(imgval);
						document.getElementById('upload_area').innerHTML = '<img src="'+imgurl+'" style="width:120px;height:120px">';
						tb_remove();
					}
				});
				jQueryTooltip();
				function easy_set_stand(){
					jQuery('.thecheckbox').each(function (index, domEle) {
						domEle.checked = true;
					});
					document.getElementById('radio2').checked = true;
					var standards = <?php echo json_encode($standard); ?>;
					for (var i in standards) {
						if(document.getElementById(i)) document.getElementById(i).value = standards[i];
					}
				}
			</script><?php
		}
	}

	function easyreservations_generate_invoice_form($res,$class='', $mode=true){
		$invoice = <<<EOF
	<script>
		function wopen(url, name, w, h){
			if(document.getElementById('invoicelang')) url = url + '&lang=lang' + document.getElementById('invoicelang').value;
			var win = window.open(url,name,'width=' + w + ', height=' + h + ', ' + 'location=no, menubar=no, ' + 'status=no, toolbar=no, scrollbars=yes, resizable=no');
			win.focus();win.focus();
		}</script>
EOF;
		if($mode){
			echo $invoice.'<a class="invoicebutton'.$class.' button" onclick="wopen(\''.WP_PLUGIN_URL.'/easyreservations/lib/modules/invoice/editor.php?id='.$res->id.'&email='.$res->email.'\', \'Invoice editor\', 673,900 );">Invoice</a>';
		} else return '| <a href="javascript:" onclick="var win = window.open(\''.WP_PLUGIN_URL.'/easyreservations/lib/modules/invoice/editor.php?id='.$res->id.'&email='.$res->email.'\', \'Invoice editor\',\'width=673, height=900, location=no, menubar=no, status=no, toolbar=no, scrollbars=yes, resizable=no\');win.resizeTo(673, 900);win.focus();win.focus();">Invoice</a>';
	}

}
	function easyreservations_get_invoice_select($sel = false, $sign = ''){
		$options = get_option('reservations_invoice_options');
		$result = '';
		if(!$sel) $sel = $options['settings']['maintemplate'];
		if(!empty($options['invoices'])){
			foreach($options['invoices'] as $key => $invoice){
				$select = $sel == $key ? 'selected="selected"' : '';
				$result .= '<option value="'.$sign.$key.'" '.$select.'>'.$invoice['name'].'</option>';
			}
		}
		return $result;
	}
?>