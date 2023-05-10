<?php
/*
Plugin Name: Import Module
Plugin URI: http://easyreservations.org/module/import/
Version: 1.2.10
Description: 3.3
Author: Feryaz Beer
License:GPL2
*/
if(is_admin()){
	function easyreservations_generate_import(){ ?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width:100%;margin-top:7px">
			<thead>
				<tr>
					<th><?php echo __( 'Import' , 'easyReservations' );?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<i>Import reservations from .XML Backup files.<br><u>Caution</u>:<br>This will add the reservations regardless of current reservations. Double entrys cause problems at the overview, editation and in availability check.</i>
						<form enctype="multipart/form-data" action="<?php echo WP_PLUGIN_URL?>/easyreservations/lib/modules/import/send_import.php" method="post">
							<input type="hidden" value="<?php echo wp_create_nonce('easy-import'); ?>" name="reservation_import_nonce">
							<input type="file" accept="text/*" maxlength="100000" size="35" name="reservation_import_upload_file">
							<input class="easybutton button-primary" type="button" style="margin-top:7px;" onclick="this.parentNode.submit(); return false;" value="<?php echo __( 'Import' , 'easyReservations' );?>">
						</form>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}

	add_action('er_set_main_side_out', 'easyreservations_generate_import' );

	function easyreservations_generate_import_message(){
		if(isset($_GET['import'])){
			$import = $_GET['import'];
			if($import == 'true'){
				echo '<div class="updated"><p>'.sprintf(__( '%s reservations imported' , 'easyReservations' ), '<b>'.$_GET["count"].'</b>' ).'</p></div>';
			} elseif($import == 'http'){  
				echo '<div class="error"><p>'.__( 'Error at access server' , 'easyReservations' ).'</p></div>';
			} elseif($import == 'access'){
				echo '<div class="error"><p>'.__( 'Only admins can import reservations' , 'easyReservations' ).'</p></div>';
			} elseif($import == 'file'){
				echo '<div class="error"><p>'.__( 'Wrong file' , 'easyReservations' ).'</p></div>';
			}
		}
	}

	add_action('er_set_save', 'easyreservations_generate_import_message' );

	function easyreservations_export_widget(){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:320px;float:left;margin:0px 10px 10px 0px;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php echo __( 'Export' , 'easyReservations' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="background-color:#fff">
						<?php /* - + - + - + - + EXPORT + - + - + - + - */ ?>
						<form  name="export" action="<?php echo WP_PLUGIN_URL; ?>/easyreservations/lib/modules/import/export.php" method="post" nowrap><?php wp_nonce_field('easy-main-export','easy-main-export'); ?>
						<input id="easy-export-id-field" name="easy-export-id-field" type="hidden">
							<select style="margin-top:2px;" name="export_type" onchange="exportSelect(this.value);"><option value="tab"><?php printf ( __( 'Reservations in table' , 'easyReservations' ));?></option><option value="all"><?php printf ( __( 'All reservations' , 'easyReservations' ));?></option><option value="sel"><?php printf ( __( 'Select reservations' , 'easyReservations' ));?></option></select> <select name="export_tech"><option value="xls"><?php printf ( __( 'Excel File' , 'easyReservations' ));?></option><option value="xml"><?php printf ( __( 'Backup (XML)' , 'easyReservations' ));?></option><option value="csv"><?php printf ( __( 'CSV File' , 'easyReservations' ));?></option></select>
							<div id="exportDiv">
								</div><div class="fakehr"></div>
								<b><?php echo __( 'Information' , 'easyReservations' );?></b><br>
								<span style="float:left;width:80px;"><input type="checkbox" name="info_ID" checked> <?php echo __( 'ID' , 'easyReservations' );?><br><input type="checkbox" name="info_name" checked> <?php echo __( 'Name' , 'easyReservations' );?><br><input type="checkbox" name="info_email" checked> <?php echo __( 'Email' , 'easyReservations' );?><br><input type="checkbox" name="info_persons" checked> <?php echo __( 'Persons' , 'easyReservations' );?><br><input type="checkbox" name="info_status" checked> <?php echo __( 'Status' , 'easyReservations' );?></span>
								<span style="float:left;width:100px;;"><input type="checkbox" name="info_date" checked> <?php echo __( 'Date' , 'easyReservations' );?><br><input type="checkbox" name="info_nights" checked> <?php echo ucfirst(easyreservations_interval_infos(0, 0, 2));?><br><input type="checkbox" name="info_reservated" checked> <?php echo __( 'Reserved' , 'easyReservations' );?><br><input type="checkbox" name="info_price" checked> <?php echo __( 'Price/Paid' , 'easyReservations' );?><br><input type="checkbox" name="info_tax" checked> <?php echo __( 'Taxes' , 'easyReservations' );?></span>
								<span nowrap><input type="checkbox" name="info_custom"> <?php echo __( 'Customs' , 'easyReservations' );?><br><input type="checkbox" name="info_country" checked> <?php echo __( 'Country', 'easyReservations' );?><br><input type="checkbox" name="info_room" checked> <?php echo __( 'Resource' , 'easyReservations' );?><br><input type="checkbox" name="info_roomnumber" checked> <?php echo __( 'Resource' , 'easyReservations' );?> #</span>
								<br><br>
								<div class="fakehr"></div>
								<span id="charset"></span>
								<input class="button" style="margin-top:5px;" type="submit" value="<?php printf ( __( 'Export reservations' , 'easyReservations' ));?>">
							</div>
						</form>
					</td>
				</tr>
			</tbody>
		</table><script type="text/javascript">jQuery(window).load(function(){exportExcelCharset();});</script> <?php
	}
	add_action('easy-add-export-widget', 'easyreservations_export_widget' );
}
?>