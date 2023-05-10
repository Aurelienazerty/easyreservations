<?php
	require('../../../../../../wp-load.php');
	$wp->send_headers();
	if (!wp_verify_nonce($_POST['reservation_import_nonce'], 'easy-import' )) die('');

	$file_name = $_FILES['reservation_import_upload_file']['name'];
	$file_tmp_name = $_FILES['reservation_import_upload_file']['tmp_name'];
	$file_type = $_FILES['reservation_import_upload_file']['type'];
	$file_size = $_FILES['reservation_import_upload_file']['size'];
	$plugin_dir = WP_PLUGIN_DIR.'/easyreservations/';
	$uploads = wp_upload_dir();
	$saved_file_location = $uploads['basedir'].'/'. $file_name;
	if($file_type == 'text/xml'){
		if(current_user_can('manage_options')){
			if(move_uploaded_file($file_tmp_name, $saved_file_location)) {
				global $wpdb;
				$row = 0;
				$where = ''; $what = '';
				$xml = simplexml_load_file($saved_file_location);
				$db = $xml['db'][0];
				$res = 0;
				$arrival = 0;
				foreach($xml->children() as $child){
					if($child->getName() != 'id' && $child->getName() != 'Database' && $child->getName() != 'row' && $child->getName() != 'dat' && $child->getName() != 'special' && $child->getName() != 'notes'){
						if(strpos($where, $child->getName()) !== false) {
							continue;
						}

						if($child->getName() == 'arrivalDate' ){
							$where .= 'arrival, ';
							$what .= "'".date("Y-m-d H:i:s", strtotime($child)+43200)."', ";
							$arrival = strtotime($child)+43200;
						} elseif($child->getName() == 'nights' ){
							$where .= 'departure, ';
							$what .= "'".date("Y-m-d H:i:s", $arrival+($child * 86400))."', ";
						} else {
							$where .= $child->getName().', ';
							$what .= "'".$child."', ";
						}
					} elseif($child->getName() == 'row' && $row != 0){
						$where = substr($where,0,-2);
						$what = mb_convert_encoding(html_entity_decode(substr($what, 0, -2)), 'ISO-8859-1');



						$sql = "INSERT INTO ".$wpdb->prefix ."reservations ( $where ) 
						VALUES ($what )";

						$wpdb->query( $sql ); 
						$where = ''; $what = ''; $arrival = 0;
						$res++;
					}
					$row++;
				}
				unlink($saved_file_location);
				wp_redirect( admin_url().'admin.php?page=reservation-settings&import=true&count='.$res );

			}  else wp_redirect( admin_url().'admin.php?page=reservation-settings&import=http' );
		} else wp_redirect( admin_url().'admin.php?page=reservation-settings&import=access' );
	} else wp_redirect( admin_url().'admin.php?page=reservation-settings&import=file' );
	exit;
?>