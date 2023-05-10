<?php

function easyreservations_woo_add_to_cart($res){
	if(class_exists('Woocommerce')){
		$prod_ids = get_option('reservations_woo_product_ids');
		if($prod_ids[$res->resource]){
			$order = array('res' => $res->id, 'resprice' => $res->price);
			WC()->session->set_customer_session_cookie(true);
			if(WC()->cart->add_to_cart( $prod_ids[$res->resource], 1, 0, array('resprice' => $res->price, 'resid' => $res->id), $order)){
				return true;
			}
		}
	}
}
add_action('reservation_successful_guest','easyreservations_woo_add_to_cart', 10, 1);

function easyreservations_woo_calculate_total($price, $card){
	$price = 0;

	foreach($card->cart_contents as $line){

		if(isset($line['resprice']) && isset($line['resid'])){

			$res = new Reservation($line['resid']);
			$res->Calculate();
			$price += $res->price;
		}	elseif(isset($line['line_total'])) $price += $line['line_total'];
	}
	$price += $card->tax_total;
	$price += $card->fee_total;
	$price += $card->shipping_tax_total;
	$price += $card->shipping_total;
	$price += $card->c;

	return $price;
}

function easyreservations_woo_callback($orderID){
	if(function_exists('easyreservations_ipn_callback')){
		$order = new WC_Order((int) $orderID);
		foreach($order->get_items() as $item){
			if(isset($item['resid'])){
				easyreservations_ipn_callback($item['resid'], $item['resprice']);
			}
		}
	}
}

function easyreservations_woo_correct_price($price, $values, $that){
	if(isset($values['resprice']) && isset($values['resid'])){
		$res = new Reservation($values['resid']);
		$res->Calculate();
		return wc_price($res->price);
	}
	return $price;
}

function easyreservations_woo_correct_card_price($prices, $values, $that){
	$price = 0;
	$resids = '';
	foreach($that->cart_contents as $line){
		if(isset($line['variation']) && isset($line['variation']['resid'])){
			$resids .= $line['variation']['resid'].',';
			$res = new Reservation($line['variation']['resid']);
			$res->Calculate();
			$price += ($res->price + $line['line_tax']);
		} elseif(isset($line['line_total'])) {$price += $line['line_total'];}
	}
	return wc_price($price).'<input type="hidden" name="easy_reservations_id" value="'.substr($resids,0,-1).'">';
}

add_filter('woocommerce_cart_subtotal', 'easyreservations_woo_correct_card_price', 50, 3);
add_action('woocommerce_calculated_total','easyreservations_woo_calculate_total', 10, 2);
add_action('woocommerce_payment_complete','easyreservations_woo_callback', 10, 1);
add_filter('woocommerce_order_formatted_line_subtotal', 'easyreservations_woo_correct_price', 50, 3);
add_filter('woocommerce_cart_item_price', 'easyreservations_woo_correct_price', 50, 3);
add_filter('woocommerce_cart_item_subtotal', 'easyreservations_woo_correct_price', 50, 3);

if(is_admin()){
	function easyreservations_woo_add_project($resource_id, $baseprice){
		if(class_exists('Woocommerce')){
			$prod_ids = get_option('reservations_woo_product_ids');
			if(!$prod_ids || !isset($prod_ids[$resource_id]) || !get_post($prod_ids[$resource_id])){
				$add_woo_product = array(
					'post_title' => get_the_title($resource_id),
					'post_content' => 'product of easyreservation resource',
					'post_status' => 'private',
					'post_type' => 'product'
				);

				$product_id = wp_insert_post( $add_woo_product );

				$_POST['_sku'] = $resource_id;
				$_POST['_stock_status'] = 'instock';
				$_POST['_visibility'] = 'private';
				$_POST['product-type'] = 'simple';
				$_POST['_regular_price'] = $baseprice;
				$_POST['woocommerce_quick_edit_nonce'] = wp_create_nonce('woocommerce_quick_edit_nonce');
				if(class_exists('WC_Admin_CPT_Product')){
					$prod = new WC_Admin_CPT_Product();
					$prod->bulk_and_quick_edit_save_post($product_id, get_post($product_id));
				} elseif(class_exists('WC_Admin_Post_Types')){
					$prod = new WC_Admin_Post_Types();
					$prod->bulk_and_quick_edit_save_post($product_id, get_post($product_id));
				} elseif(function_exists('woocommerce_admin_product_quick_edit_save'))
					woocommerce_admin_product_quick_edit_save( $product_id, get_post($product_id) );
				update_post_meta($product_id, "_sku", $resource_id);
				update_post_meta($product_id, "_regular_price", $baseprice);
				if($prod_ids){
					$prod_ids[$resource_id] = $product_id;
				} else {
					$prod_ids = array($resource_id => $product_id);
				}

				update_option('reservations_woo_product_ids', $prod_ids);
			} else {
				update_post_meta( $prod_ids[$resource_id], '_regular_price', $baseprice );
			}
		}
	}
	add_action('edit_resource', 'easyreservations_woo_add_project', 10, 2 );
}
?>