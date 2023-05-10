<?php
	require('../../../../../../wp-load.php');
	//if (!wp_verify_nonce($_POST['easy-main-export'], 'easy-main-export' )) exit;

	if($_POST['export_tech'] == 'xls' || $_POST['export_tech'] == 'csv'){
		easyreservations_load_resources();
		global $the_rooms_array, $wpdb;
		if($_POST['export_tech'] == 'xls'){
			$export_mode = true;
			require('./export-xls.class.php');
			$filename = "easyReservation_".date("Y-m-d_H-i",time()).".xls"; // The file name you want any resulting file to be called.
			#create an instance of the class
			$xls = new ExportXLS($filename);
			$header = array();
		} else {
			$export_mode = false;
			$filename = $file."_".date("Y-m-d_H-i",time());
			$out = '';
		}

		$selects = 'id, arrival, departure, name, email, number, childs, room, roomnumber, country, approve, price, custom, customp, reservated';
		if(isset($_POST['info_ID'])){
			if($export_mode) $header[] = 'ID';
			else $out .= 'uID, ';
		}
		if(isset($_POST['info_name'])){
			if($export_mode) $header[] = 'Name';
			else $out .= 'Name, ';
		}
		if(isset($_POST['info_email'])){
			if($export_mode) $header[] = 'Email';
			else $out .= 'Email, ';
		}
		if(isset($_POST['info_persons'])){
			if($export_mode){
				$header[] = 'Adults';
				$header[] = 'Children';
			} else $out .= 'Adults, Children, ';
		}
		if(isset($_POST['info_date'])){
			if($export_mode){
				$header[] = 'From';
				$header[] = 'To';
			} else $out .= 'From, To, ';
		}
		if(isset($_POST['info_nights'])){
			if($export_mode) $header[] =ucfirst(easyreservations_interval_infos());
			else $out .= ucfirst(easyreservations_interval_infos()).', ';
		}
		if(isset($_POST['info_reservated'])){
			if($export_mode) $header[] = 'Reserved';
			else $out .= 'Reserved, ';
		}
		if(isset($_POST['info_country'])){
			if($export_mode) $header[] = 'Country';
			else $out .= 'Country, ';
		}
		if(isset($_POST['info_status'])){
			if($export_mode) $header[] = 'Status';
			else $out .= 'Status, ';
		}
		if(isset($_POST['info_room'])){
			if($export_mode) $header[] = 'Resource';
			else $out .= 'Resource, ';
		}
		if(isset($_POST['info_roomnumber'])){
			if($export_mode) $header[] = 'Resource Number';
			else $out .= 'Resource Number, ';
		}
		if(isset($_POST['info_price'])){
			if($export_mode) {
				$header[] = 'Price';
				$header[] = 'Paid';
			} else $out .= 'Price, Paid, ';
		}
		if(isset($_POST['info_tax'])){
			if($export_mode) {
				$header[] = 'Tax';
			} else $out .= 'Tax, ';
		}

		if(isset($_POST['export_type']) && $_POST['export_type'] == 'tab'){
			$IDs = substr($_POST['easy-export-id-field'],0,-2);
			$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations WHERE id IN ($IDs)";
		} elseif(isset($_POST['export_type']) && $_POST['export_type'] == 'all'){
			$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations";
		} elseif(isset($_POST['export_type']) && $_POST['export_type'] == 'sel'){
			
			if(isset($_POST['approved'])) $status .= "OR approve = 'yes' ";
			if(isset($_POST['rejected'])) $status .= "OR approve = 'no' ";
			if(isset($_POST['trashed'])) $status .= "OR approve = 'del' ";
			if(isset($_POST['pending'])) $status .= "OR approve = '' ";
			$status = substr($status,2);

			if(isset($_POST['past'])) $time .= "OR (arrival < NOW() AND NOW() NOT BETWEEN arrival AND departure)";
			if(isset($_POST['present'])) $time .= "OR NOW() BETWEEN arrival AND departure ";
			if(isset($_POST['future'])) $time .= "OR (arrival > NOW() AND NOW() NOT BETWEEN arrival AND departure)";
			$time = substr($time,2);

			$sql_reservations = "SELECT $selects FROM ".$wpdb->prefix ."reservations WHERE ($status) AND ($time)";
		}
		$reservationsExportArray = $wpdb->get_results($sql_reservations);

		if(isset($_POST['info_custom'])){
			$custom_fields = get_option('reservations_custom_fields');
			$the_customs_titles = array(); $the_customs = array();
			foreach($reservationsExportArray as $key => $exportReservations){
				$res = new Reservation($exportReservations->id, (array) $exportReservations);
				$customs = $res->getCustoms($res->custom, 'cstm');
				if(is_array($customs) && !empty($customs)){
					foreach($customs as $custom){
						if(isset($custom['id'])){
							$title = $custom_fields['fields'][$custom['id']]['title'];
							$the_customs[$title][$key] = $res->getCustomsValue($custom);
							$the_customs_titles[] = $title;
						} else {
							$the_customs[$custom['title']][$key] = $custom['value'];
							$the_customs_titles[] = $custom['title'];
						}
					}
				}
			}
			if(!empty($the_customs_titles)){
				$the_customs_titles = array_unique($the_customs_titles);
				foreach($the_customs_titles as $key => $custom_title){
					if(!empty($custom_title)){
						if($export_mode) $header[] = $custom_title;
						else $out .= $custom_title.', ';
					}
					else unset($the_customs_titles[$key]);
				}
			}
		}
		if($export_mode){
			$xls->addHeader($header);
			foreach($reservationsExportArray as $count => $exportReservations){
				$res = new Reservation($exportReservations->id, (array) $exportReservations);
				if($count > 0) $row = array();
				if(isset($_POST['info_ID'])) $row[] = $res->id;
				if(isset($_POST['info_name'])) $row[] = $res->name;
				if(isset($_POST['info_email'])) $row[] = $res->email;
				if(isset($_POST['info_persons'])){
					$row[] = (string) $res->adults;
					$row[] = (string) $res->childs;
				}
				if(isset($_POST['info_date'])){ $row[] = date(RESERVATIONS_DATE_FORMAT_SHOW, strtotime($exportReservations->arrival)); $row[] = date(RESERVATIONS_DATE_FORMAT_SHOW, strtotime($exportReservations->departure)); }
				if(isset($_POST['info_nights'])) $row[] = $res->times;
				if(isset($_POST['info_reservated'])) $row[] = date(RESERVATIONS_DATE_FORMAT_SHOW, $res->reservated);
				if(isset($_POST['info_country'])) $row[] = easyreservations_country_name($res->country);
				if(isset($_POST['info_status'])) $row[] = $res->getStatus();
				if(isset($_POST['info_room'])) $row[] = __($the_rooms_array[$res->resource]->post_title);
				if(isset($_POST['info_roomnumber'])) $row[] = (string) easyreservations_get_roomname($res->resourcenumber, $res->resource);
				if(isset($_POST['info_price'])){
					$row[] = (string) str_replace(',', '.', $res->Calculate());
					$row[] = (string) str_replace(',', '.', $res->paid);
				}
				if(isset($_POST['info_tax'])){
					$res->calculate(true);
					$tax = 0;
					foreach($res->history as $price){
						if($price['type'] == 'tax'){
							$tax += $price['priceday'];
						}
					}
					$row[] = (string) str_replace(',', '.', round($tax,2));
				}
				if(isset($_POST['info_custom'])){
					if(!empty($the_customs_titles)){
						foreach($the_customs_titles as $key => $custom_title){
							if(isset($the_customs[$custom_title][$count])) $row[] = $the_customs[$custom_title][$count];
							else $row[] = '';
						}
					}
				}
				$xls->addRow($row);
			}
			$xls->sendFile();
		} else {
			$out = substr($out,0,-2)."\n";
			foreach($reservationsExportArray as $count => $exportReservations){
				$res = new Reservation($exportReservations->id, (array) $exportReservations);
				if(isset($_POST['info_ID'])) $out .= $res->id.', ';
				if(isset($_POST['info_name'])) $out .= $res->name .', ';
				if(isset($_POST['info_email'])) $out .= $res->email .', ';
				if(isset($_POST['info_persons'])){
					$out .= $res->adults .', ';
					$out .= $res->childs .', ';
				}
				if(isset($_POST['info_date'])) $out .= date(RESERVATIONS_DATE_FORMAT_SHOW, $res->arrival).', '.date(RESERVATIONS_DATE_FORMAT_SHOW, $res->departure).', ';
				if(isset($_POST['info_nights'])) $out .= $res->times .', ';
				if(isset($_POST['info_reservated'])) $out .= date(RESERVATIONS_DATE_FORMAT_SHOW, $res->reservated) .', ';
				if(isset($_POST['info_country'])) $out .= easyreservations_country_name($res->country) .', ';
				if(isset($_POST['info_status'])) $out .= $res->getStatus().', ';
				if(isset($_POST['info_room'])) $out .= __($the_rooms_array[$res->resource]->post_title).', ';
				if(isset($_POST['info_roomnumber'])) $out .= easyreservations_get_roomname($res->resourcenumber, $res->resource).', ';
				if(isset($_POST['info_price'])){
					$out .= str_replace(',', '.', $res->Calculate()).', '.str_replace(',', '.', $res->paid).', ';
				}
				if(isset($_POST['info_tax'])){
					$res->calculate(true);
					$tax = 0;
					foreach($res->history as $price){
						if($price['type'] == 'tax'){
							$tax = $tax+$price['priceday'];
						}
					}
					$out .= str_replace(',', '.', $tax).', ';
				}
				if(isset($_POST['info_custom'])){
					if(!empty($the_customs_titles)){
						foreach($the_customs_titles as $key => $custom_title){
							if(isset($the_customs[$custom_title][$count])) $out .= $the_customs[$custom_title][$count].', ';
							else $out .= ', ';
						}
					}
				}
				$out = substr($out,0,-2)."\n";
			}

		//Now we're ready to create a file. This method generates a filename based on the current date & time.
		$filename = "easyReservation_".date("Y-m-d_H-i",time()).'.csv';

		//Generate the CSV file header
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Length: " . strlen($out));
		// Output to browser with appropriate mime type, you choose <img src="http://thetechnofreak.com/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
		header("Content-type: text/x-csv");
		//header("Content-type: text/csv");
		//header("Content-type: application/csv");		header("Content-disposition: attachment; filename=".$filename.".csv;");
		header("Content-Disposition: attachment; filename=$filename");

		//Print the contents of out to the generated file.
		print $out;

		}

	} elseif($_POST['export_tech'] == 'xml'){
		global $wpdb;

		$xml = new SimpleXMLElement("<?xml version='1.0' standalone='yes'?><Database/>");

		if(isset($_POST['export_type']) && $_POST['export_type'] == 'tab'){
			global $wpdb;
			$IDs = substr($_POST['easy-export-id-field'],0,-2);
			$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE id in($IDs)";
		} elseif(isset($_POST['export_type']) && $_POST['export_type'] == 'all'){
			global $wpdb;
			$sql = "SELECT * FROM ".$wpdb->prefix ."reservations";
		} elseif(isset($_POST['export_type']) && $_POST['export_type'] == 'sel'){

			$status = '';
			if(isset($_POST['approved'])) $status .= "OR approve = 'yes' ";
			if(isset($_POST['rejected'])) $status .= "OR approve = 'no' ";
			if(isset($_POST['trashed'])) $status .= "OR approve = 'del' ";
			if(isset($_POST['pending'])) $status .= "OR approve = '' ";
			if(!empty($status)){
				$status = '('.substr($status,2).')';
			}

			$time = '';
			if(isset($_POST['past'])) $time .= "OR (arrival < NOW() AND NOW() NOT BETWEEN arrival AND departure)";
			if(isset($_POST['present'])) $time .= "OR NOW() BETWEEN arrival AND departure ";
			if(isset($_POST['future'])) $time .= "OR (arrival > NOW() AND NOW() NOT BETWEEN arrival AND departure)";
			if(!empty($time)){
				$time = '('.substr($time,2).')';
			}

			global $wpdb;
			$sql = "SELECT * FROM ".$wpdb->prefix ."reservations WHERE $status AND $time";
		}

		$res =  $wpdb->get_results($sql);
		$i = 0;
		$dbversion = "1.6";
		$xml->addAttribute('xmlns:db','1.6');

		//$xml->addChild("database", $dbversion); 
		foreach($res as $data){
			$i++;
			$row = $xml->addChild('row');
			foreach ($data as $key => $val){
				$xml->addChild($key, mb_convert_encoding($val, 'UTF-8'));
			}
		}

		$row = $xml->addChild("row");
		$xml = $xml->asXML();
		$filename = "easyReservations_Backup_DB-".$dbversion."_".date("Y-m-d_H-i",time());


		//Generate the CSV file header
		header("Content-type: text/force-download");
		header("Content-disposition: xml" . date("Y-m-d") . ".xml");
		header("Content-disposition: attachment; filename=".$filename.".xml");

		print $xml;
	}
	//Exit the script
	exit;
?>