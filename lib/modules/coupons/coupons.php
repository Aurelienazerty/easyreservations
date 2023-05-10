<?php
/*
Plugin Name: Coupons Module
Plugin URI: http://easyreservations.org/module/coupons/
Version: 1.0.14
Description: 3.4
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!
*/

if(is_admin()){
	add_action('er_set_tab_add', 'easyreservations_add_coupons_settings_tab');

	function easyreservations_add_coupons_settings_tab(){
		$current = isset($_GET['site']) && $_GET['site'] == "coupons" ?'current' : '';
		$tab = '<li><a href="admin.php?page=reservation-settings&site=coupons" class="'.$current.'"><img style="vertical-align:text-bottom ;" src="'.RESERVATIONS_URL.'images/money.png"> '. __( 'Coupons' , 'easyReservations' ).'</a></li>';

		echo $tab;
	}

	add_action('er_set_add', 'easyreservations_coupon_admin');

	function easyreservations_coupon_admin(){
		if(isset($_GET['site']) && $_GET['site'] == "coupons"){
			wp_enqueue_style( 'datestyle' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			add_action('admin_print_footer_scripts','easyreservations_restrict_input_coupon');
			$coupons = get_option('reservations_coupons');
			echo '<table class="'.RESERVATIONS_STYLE.'" style="width:99%">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>'.__( 'Code' , 'easyReservations' ).'</th>';
					echo '<th>'.__( 'Time' , 'easyReservations' ).'</th>';
					echo '<th style="text-align:center">'.__( 'Used' , 'easyReservations' ).'</th>';
					echo '<th>'.__( 'Discount' , 'easyReservations' ).'</th>';
					echo '<th style="width:16px;"></th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			if(!empty($coupons)){
				foreach($coupons as $key => $coupon){
					if(is_numeric($coupon['amount'])) $price = easyreservations_format_money($coupon['amount'], 1);
					else $price = $coupon['amount'];
					if(!isset($coupon['maxuse']) || empty($coupon['maxuse']) || $coupon['maxuse'] == 0) $max = '∞';
					else $max = $coupon['maxuse'];
					if(time() >= $coupon['from'] && time() <= $coupon['to']) $color = '#5EE06B';
					else $color = '#F26868';
					echo '<tr>';
						echo '<td><code>'.$key.'</code></td>';
						echo '<td><span style="background:'.$color.';padding:3px;color:#fff">'.date(RESERVATIONS_DATE_FORMAT,$coupon['from']).' - '.date(RESERVATIONS_DATE_FORMAT,$coupon['to']).'</span></td>';
						echo '<td style="text-align:center">'.$coupon['used'].'/'.$max.'</td>';
						echo '<td>'.$price.'</td>';
						echo '<td><a href="admin.php?page=reservation-settings&site=coupons&delete='.$key.'"><img src="'.RESERVATIONS_URL.'images/delete.png" style="vertical-align:tet-bottom"></a></td>';
					echo '</tr>';
				}
			} else echo '<tr><td colspan="5">'.__( 'No coupons' , 'easyReservations' ).'</td></tr>';
			echo '</tbody>';
			echo '</table>';?>
			<form method="post" action="admin.php?page=reservation-settings&site=coupons"  id="reservation_coupon_settings" name="reservation_coupon_settings">
			<?php wp_nonce_field('easy-add-coupon','easy-add-coupon'); ?>
				<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:500px;margin-top: 5px">
					<thead>
						<tr>
							<th colspan="2"><?php printf ( __( 'Add coupon' , 'easyReservations' ));?> <input class="easybutton button-primary" type="button" style="float:right" onclick="document.getElementById('reservation_coupon_settings').submit(); return false;" value="Add"></th>
						</tr>
					</thead>
					<tbody>
						<tr class="alternate">
							<td><?php echo __( 'Code' , 'easyReservations' ); ?></td>
							<td style="text-align:right"><select name="generator"><?php echo easyreservations_num_options(1,32,6); ?></select><a href="javascript:randomString(document.reservation_coupon_settings.generator.value)" class="button">gen</a> <input type="text" name="name"></td>
						</tr>
						<tr>
							<td><?php echo __( 'From' , 'easyReservations' ); ?></td>
							<td style="text-align:right"><input type="text" name="from" id="from"></td>
						</tr>
						<tr class="alternate">
							<td><?php echo __( 'To' , 'easyReservations' ); ?></td>
							<td style="text-align:right"><input type="text" id="to" name="to"></td>
						</tr>
						<tr>
							<td><?php echo __( 'Maximum usages' , 'easyReservations' ); ?></td>
							<td style="text-align:right"><select name="maxuse"><option value="0">∞</option><?php echo easyreservations_num_options(1,100,50); ?></select></td>
						</tr>
						<tr class="alternate">
							<td><?php echo __( 'Amount' , 'easyReservations' ); ?></td>
							<td style="text-align:right"><input type="text" style="text-align: right" name="amount"></td>
						</tr>
					</tbody>
				</table>
			</form>
			<script type="text/javascript">
				function randomString(string_length) {
					var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ";
					var randomstring = '';
					for (var i=0; i<string_length; i++) {
						var rnum = Math.floor(Math.random() * chars.length);
						randomstring += chars.substring(rnum,rnum+1);
					}
					document.reservation_coupon_settings.name.value = randomstring;
				}
			</script>
		<?php echo easyreservations_build_datepicker(1,array('from', 'to'),RESERVATIONS_DATE_FORMAT);
		}
	}

	function easyreservations_restrict_input_coupon(){
		easyreservations_generate_restrict(array(array('input[name="amount"]', true, true)));
	}
	
	function easyreservations_admin_add_coupon_to_res($res){
		if(isset($_POST['newcoupon'])){
			$new_coupon = array();
			$coupons = array();
			foreach($_POST['newcoupon'] as $newcoupon){
				$new_coupon[] = array('type' => 'coup', 'value' => $newcoupon);
				$coupons[] = $newcoupon;
			}
			if(isset($_POST['allcoupon'])){
				foreach($_POST['allcoupon'] as $newcoupon){
					if(!in_array($newcoupon, $coupons)) $new_coupon[] = array('type' => 'coup', 'value' => $newcoupon);
				}
			}
			$res->Customs($new_coupon, true, false, true, 'coup');
		}
		return $res;
	}
	add_filter('easy-edit-prices', 'easyreservations_admin_add_coupon_to_res', 10, 1);

	function easyreservations_add_coupon(){
		if(isset($_GET['site']) && $_GET['site'] == "coupons"){
			if(isset($_POST['name'])){
				if (!wp_verify_nonce($_POST['easy-add-coupon'], 'easy-add-coupon' )) die('Security check <a href="'.$_SERVER['referer_url'].'">('.__( 'Back' , 'easyReservations' ).')</a>' );
				$coupons = get_option('reservations_coupons');
				$coupons[str_replace(' ', '',$_POST['name'])] = array( 'from' => EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['from'].' 00:00:00')->getTimestamp(), 'to' => EasyDateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST['to'].' 00:00:00')->getTimestamp(), 'used' => 0, 'amount' => $_POST['amount'], 'maxuse' => $_POST['maxuse'] );
				update_option('reservations_coupons', $coupons);
				echo '<br><div class="updated"><p>'.sprintf(__( 'Coupon %s added' , 'easyReservations'),str_replace(' ', '',$_POST['name'])).'</p></div>';
			} elseif(isset($_GET['delete'])){
				$delete =$_GET['delete'];
				$coupons = get_option('reservations_coupons');
				unset($coupons[$delete]);
				update_option('reservations_coupons', $coupons);
				echo '<br><div class="updated"><p>'.sprintf(__( 'Coupon %s deleted' , 'easyReservations'),'<b>'.$delete.'</b>').'</p></div>';
			}
		}
	}
	add_action('er_set_save', 'easyreservations_add_coupon');

	function easyreservations_coupon_list($res){
		$coupons = get_option('reservations_coupons');
		$guests_coupons = $res->getCustoms($res->prices, 'coup');
		$coupon_list = array();
		if(empty($guests_coupons)) $coupon_list = __( 'No coupons' , 'easyReservations' );
		else {
			foreach($guests_coupons as $key => $guests_coupon){
				if($coupons && isset($coupons[$guests_coupon['value']])){
					$actual = $coupons[$guests_coupon['value']];
					if(($res->arrival >= $actual['from'] && $res->arrival <= $actual['to']) && $actual['used'] <= $actual['maxuse']) $class = 'green';
					else $class = 'red';
				} else $class = 'blue';
				$coupon_list .= '<span class="coupon coupon-'.$class.'">'.$guests_coupon['value'].' <a href="admin.php?page=reservations&edit='.$res->id.'&deletepricefield='.$key.'"><img src="'.RESERVATIONS_URL.'images/delete.png" style=""></a></span><input type="hidden" name="allcoupon[]" value="'.$guests_coupon['value'].'">';
			}
		}
		return $coupon_list;
	}

	function easyreservations_add_edit_box($res){
		$coupons = get_option('reservations_coupons');
		if(!$coupons || empty($coupons)) return false;
		$options = '';
		foreach($coupons as $key => $coupon){
			$options .= '<option value="'.$key.'">'.$key.' </option>';
		}
		$coupon_list = easyreservations_coupon_list($res);?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:5px;min-width:320px;width:320px;">
			<thead>
				<tr>
					<th><?php echo __( 'Coupons' , 'easyReservations' );?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td nowrap>
						<?php echo $coupon_list; ?><div id="add-coupons-here"></div>
						<select name="addcoupon" style="margin-bottom:4px" id="addcoupon"><?php echo $options; ?></select>
						<input type="button" onclick="addCouponToForm();" style="margin-top:3px" class="button" value="<?php echo __( 'Add coupon' , 'easyReservations' );?>">
					</td>
				</tr>
			</tbody>
		</table>
		<script type="text/javascript">
			function addCouponToForm(){
				var code = document.getElementById('addcoupon').value;
				document.getElementById('add-coupons-here').innerHTML += '<span class="coupon coupon-yellow">'+code+'</span><input type="hidden" name="newcoupon[]" value="'+code+'"><input type="hidden" name="allcoupon[]" value="'+code+'">';
			}
		</script><?php
	}

	add_action('easy-dash-edit-side-bottom', 'easyreservations_add_edit_box', 10, 1);

	function easyreservations_coupon_add_form_editor(){ ?>
	  fields['coupon'] = {
		  name: '<?php addslashes(_e( 'Coupon' , 'easyReservations' ));?>',
		  desc: '<?php addslashes(_e( 'Text field to enter coupon codes' , 'easyReservations' ));?>',
		  options: {
			  style: style,
			  title: title,
			  disabled:disabled
		  }
	  }<?php
	}

	add_action('easy-form-js-before', 'easyreservations_coupon_add_form_editor');

	function easyreservations_add_coupon_to_form_list($accordeon){
		$accordeon .= '<tr attr="coupon">';
		$accordeon .= '<td style="background-image:url('.RESERVATIONS_URL.'images/special.png);"></td>';
		$accordeon .= '<td><strong>'.__('Coupon','easyReservations').'</strong><br><i>'.__('Text field to enter coupon codes','easyReservations').'</i></td>';
		$accordeon .= '</tr>';
		return $accordeon;
	}
	add_filter('easy-form-list', 'easyreservations_add_coupon_to_form_list', 11, 1);

} else {

	function easyreservations_add_coupon_tag($theForm, $fields, $formid){
		$field=shortcode_parse_atts( $fields);
		if($field[0] == 'coupon'){
			$value = isset($field['value']) ? $field['value'] : '';
			$style = isset($field['style']) ? $field['style'] : '';
			$title = isset($field['title']) ? $field['title'] : '';
			$maxlength = isset($field['maxlength']) ? $field['maxlength'] : '';
			$disabled = isset($field['disabled']) ? 'disabled="disabled"' : '';
			$theForm = preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-form-coupon" name="coupon[]" '.$disabled.' value="'.$value.'" style="'.$style.'" title="'.$title.'" onchange="easyreservations_send_price(\''.$formid.'\');">', $theForm);
		}
		return $theForm;
	}

	add_filter('easy-form-tag', 'easyreservations_add_coupon_tag', 10, 3);
}

	function easyreservations_calculate_coupon($res){
		$coupons = get_option('reservations_coupons');
		if(isset($res->coupon)){
			if(strpos($res->coupon,',') !== false) $prices = array_filter(explode(',',$res->coupon));
			else $prices = array(array('value' => $res->coupon));
		}	else {
			if(!is_array($res->prices)) $prices = $res->getCustoms($res->prices, 'coup');
			else $prices = $res->prices;
		}

		if(!empty($prices)){
			foreach($prices as $coupon){
				if(is_array($coupon) && isset($coupon['value'])) $coupon = $coupon['value'];
				if(isset($coupons[$coupon])){
					$actual = $coupons[$coupon];
					if($res->arrival >= $actual['from'] && $res->departure <= $actual['to']){
						if(substr($coupons[$coupon]['amount'], -1) == '%') $couponprice = ($res->price/100) * substr($coupons[$coupon]['amount'], 0, -1);
						else $couponprice = $coupons[$coupon]['amount'];
						$res->price += $couponprice;
						$res->countpriceadd++;
						$res->history[] = array('date'=>$res->arrival+($res->countpriceadd*$res->interval), 'priceday'=>$couponprice, 'type'=> 'coupon', 'name' => $coupon);
					}
				}
			}
		}
		return $res;
	}
	add_filter('easy-calc-pricefields', 'easyreservations_calculate_coupon', 10, 1);

	function easyreservations_add_coupon_to_res($res){
		if(isset($_POST['coupon']) && !$res->admin && (!isset($res->coupon) || $res->coupon !== false)){
			if(!is_array($_POST['coupon'])) $_POST['coupon'] = array($_POST['coupon']);
			$coupons = get_option('reservations_coupons');
			foreach($_POST['coupon'] as $coupon){
				if(isset($coupons[$coupon])){
					$actual = $coupons[$coupon];
					if(($res->arrival >= $actual['from'] && $res->departure <= $actual['to']) && ($actual['used'] < $actual['maxuse'] || $actual['maxuse'] == 0)){
						$new_coupon = array('type' => 'coup', 'value' => $coupon);
						$res->Customs( array($new_coupon), false, false, true, 'coup' );
						$coupons[$coupon]['used']++;
						update_option('reservations_coupons', $coupons);
					}
				}
			}
		}
		return $res;
	}

	add_filter('easy-add-res-ajax', 'easyreservations_add_coupon_to_res', 8, 1);
	add_filter('easy-edit-res-ajax', 'easyreservations_add_coupon_to_res', 8, 1);

	?>