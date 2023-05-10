<?php
/*
Plugin Name: Datepicker Styles Module
Plugin URI: http://easyreservations.org/module/datepicker/
Version: 1.2.9
Description: 3.3
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!
*/

	function easyreservations_register_datepicker_style(){
		wp_deregister_style('datestyle');
		wp_register_style('easy-form-premium', WP_PLUGIN_URL .'/easyreservations/lib/modules/styles/form/form_premium.css');
		wp_register_style('datestyle', WP_PLUGIN_URL .'/easyreservations/lib/modules/styles/css/datepicker.css');
		wp_register_style('easy-cal-premium', WP_PLUGIN_URL . '/easyreservations/lib/modules/styles/calendar/calendar_premium.css');
	}

	function easyreservations_header_datepicker_style(){
		$opt = get_option('reservations_datepicker');
		$style = $opt[0];
		if(!$opt || $style == 1){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#505050;background-image: -ms-linear-gradient(top, #505050 0%, #3F3F3F 100%);background-image: -moz-linear-gradient(top, #505050 0%, #3F3F3F 100%);background-image: -o-linear-gradient(top, #505050 0%, #3F3F3F 100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #505050), color-stop(1, #3F3F3F));background-image: -webkit-linear-gradient(top, #505050 0%, #3F3F3F 100%);background-image: linear-gradient(top, #505050 0%, #3F3F3F 100%); height:30px;; border: 1px solid black;}</style><?php
		} elseif($style == 2){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#666666;background-image: -ms-linear-gradient(top, #666666  0%, #606060 100%);background-image: -moz-linear-gradient(top, #666666  0%, #606060 100%);background-image: -o-linear-gradient(top, #666666  0%, #606060 100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #666666 ), color-stop(1, #606060));background-image: -webkit-linear-gradient(top, #666666  0%, #606060 100%);background-image: linear-gradient(top, #666666  0%, #606060 100%); height:30px;border: 1px solid #3D3D3D;}</style><?php
		} elseif($style == 3){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background: rgb(183,80,75);background: -moz-linear-gradient(top,  rgba(183,80,75,1) 0%, rgba(162,70,65,1) 50%, rgba(155,67,62,1) 51%, rgba(136,57,53,1) 100%);	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(183,80,75,1)), color-stop(50%,rgba(162,70,65,1)), color-stop(51%,rgba(155,67,62,1)), color-stop(100%,rgba(136,57,53,1)));background: -webkit-linear-gradient(top,  rgba(183,80,75,1) 0%,rgba(162,70,65,1) 50%,rgba(155,67,62,1) 51%,rgba(136,57,53,1) 100%);background: -o-linear-gradient(top,  rgba(183,80,75,1) 0%,rgba(162,70,65,1) 50%,rgba(155,67,62,1) 51%,rgba(136,57,53,1) 100%);background: -ms-linear-gradient(top,  rgba(183,80,75,1) 0%,rgba(162,70,65,1) 50%,rgba(155,67,62,1) 51%,rgba(136,57,53,1) 100%);	background: linear-gradient(top,  rgba(183,80,75,1) 0%,rgba(162,70,65,1) 50%,rgba(155,67,62,1) 51%,rgba(136,57,53,1) 100%);filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b7504b', endColorstr='#883935',GradientType=0 );border: 1px solid #74312E;border-bottom: 1px solid black;}</style><?php
		} elseif($style == 4){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#CC2E2E;background-image: -ms-linear-gradient(top, #CC2E2E  0%, #B52B2B 100%);background-image: -moz-linear-gradient(top, #DB3434  0%, #B52B2B 100%);background-image: -o-linear-gradient(top, #CC2E2E  0%, #B52B2B 100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #B52B2B ), color-stop(1, #B52B2B));background-image: -webkit-linear-gradient(top, #CC2E2E  0%, #B52B2B 100%);background-image: linear-gradient(top, #CC2E2E  0%, #B52B2B 100%); height:30px;border: 1px solid #A02323; font-weight: bold;}</style><?
		} elseif($style == 5){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#8549D8;background-image: -ms-linear-gradient(top, #8549D8   0%, #7B46C4  100%);background-image: -moz-linear-gradient(top, #8549D8   0%, #7B46C4  100%);background-image: -o-linear-gradient(top, #8549D8   0%, #7B46C4  100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #8549D8), color-stop(1, #7B46C4 ));background-image: -webkit-linear-gradient(top, #8549D8   0%, #7B46C4  100%);background-image: linear-gradient(top, #8549D8   0%, #7B46C4 100%); height:30px;border: 1px solid #61379B; font-weight: bold;}</style><?
		} elseif($style == 6){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#499BEA;background-image: -ms-linear-gradient(top, #499BEA   0%, #207CE5  100%);background-image: -moz-linear-gradient(top, #499BEA   0%, #207CE5  100%);background-image: -o-linear-gradient(top, #499BEA   0%, #207CE5  100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #499BEA  ), color-stop(1, #207CE5 ));background-image: -webkit-linear-gradient(top, #499BEA   0%, #207CE5  100%);background-image: linear-gradient(top, #499BEA   0%, #207CE5  100%); height:30px;border: 1px solid #2853A8; font-weight: bold;}</style><?
		} elseif($style == 7){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#2E8235;background-image: -ms-linear-gradient(top, #2E8235   0%, #28722E  100%);background-image: -moz-linear-gradient(top, #2E8235   0%, #28722E  100%);background-image: -o-linear-gradient(top, #2E8235   0%, #28722E  100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #2E8235  ), color-stop(1, #28722E ));background-image: -webkit-linear-gradient(top, #2E8235   0%, #28722E  100%);background-image: linear-gradient(top, #2E8235   0%, #28722E  100%); height:30px;border: 1px solid #1F5623; font-weight: bold;}</style><?
		} elseif($style == 8){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#72D369;background-image: -ms-linear-gradient(top, #72D369   0%, #64BA5D  100%);background-image: -moz-linear-gradient(top, #72D369   0%, #69BF61  100%);background-image: -o-linear-gradient(top, #72D369   0%, #64BA5D  100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #72D369  ), color-stop(1, #64BA5D ));background-image: -webkit-linear-gradient(top, #72D369   0%, #64BA5D  100%);background-image: linear-gradient(top, #72D369   0%, #64BA5D  100%); height:30px;border: 1px solid #509349; font-weight: bold;}</style><?
		} elseif($style == 9){?><style type="text/css">.ui-widget-header { text-shadow: 0px 1px 0px black;color: #fff; font-size:13px;background:#F05D27;background-image: -ms-linear-gradient(top, #F05D27   0%, #E55622  100%);background-image: -moz-linear-gradient(top, #F05D27   0%, #E55622  100%);background-image: -o-linear-gradient(top, #F05D27   0%, #E55622  100%);background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #F05D27  ), color-stop(1, #E55622 ));background-image: -webkit-linear-gradient(top, #F05D27   0%, #E55622  100%);background-image: linear-gradient(top, #F05D27   0%, #E55622  100%); height:30px;border: 1px solid #AD451F; font-weight: bold;}</style><?php
		}
	}

	function easyreservations_add_premium_cal_style(){
		echo '<option value="premium">'.__("premium", "easyReservations").'</option>';
	}

	add_action('easy-tinymce-add-style-cal', 'easyreservations_add_premium_cal_style');
	add_action('easy-tinymce-add-style-form', 'easyreservations_add_premium_cal_style');
	$did_datepicker_function = false;

	function easyreservations_header_datepicker_script(){
		global $did_datepicker_function;
		$opt = get_option('reservations_datepicker');
		if($opt && !$did_datepicker_function){
			$did_datepicker_function = true;
			$times = $opt[1];
			if($times > 0){
				?><script>easybookedDays = new Array();</script><?php
				easyreservations_load_resources();
				global $the_rooms_array;
				$res = new Reservation(false, array('dontclean', 'interval' => 86400));
				foreach($the_rooms_array as $room){
					$dates = '';
					$res->resource = $room->ID;
					$res->interval = 86400;
					for($i = 0; $i < $times; $i++){
						$res->arrival = strtotime(date("d.m.Y",time()))+43200+($i*86400);
						if(floor($res->checkAvailability(4)) > 0) $dates .= '"'.date("Y-n-j",$res->arrival).'",';
					}
					?><script>easybookedDays[<?php echo $room->ID; ?>] = [<?php if(!empty($dates)) echo substr($dates,0,-1); ?>];</script><?php
				} ?>
        <script>
          function easydisabledays(date, room){
              if(room && room > 0){
                  var dateAsString = date.getFullYear().toString() + "-" + (date.getMonth()+1).toString() + "-" + date.getDate();
                  var result = jQuery.inArray( dateAsString, easybookedDays[room] ) ==-1 ? [true] : [false];
                  return result;
              } else return [true];
          }
        </script><?php
			}
		}
	}

	add_action('wp_enqueue_scripts', 'easyreservations_register_datepicker_style');
	add_action('admin_enqueue_scripts', 'easyreservations_register_datepicker_style');
	add_action('wp_print_styles', 'easyreservations_header_datepicker_style');
	add_action('admin_print_styles', 'easyreservations_header_datepicker_style');

	if(is_admin()){
		function easyreservation_add_datepicker_option($rows){
			$style = get_option('reservations_datepicker');
			if(!$style && $style != 0) $style = 1;
			$rows['<img src="'.RESERVATIONS_URL.'images/to.png"> <b>'.__( 'Datepicker style', 'easyReservations' ).'</b>'] = easyreservations_generate_input_select('reservations_datepicker', array(1=> 'Black',2 => 'Grey', 3=>'Red dark',4=>'Red',5=>'Purple',6=>'Blue',7=>'Green dark',8=>'Green',9=>'Orange'),$style[0]);
			$rows['<img src="'.RESERVATIONS_URL.'images/auto.png"> <b>'.__( 'Datepicker availability', 'easyReservations' ).'</b>'] = sprintf(__('Check availability for %s days', 'easyReservations'), '<select name="reservations_datepicker_avail">'.easyreservations_num_options(0,360,$style[1]).'</select>').' - <i>'.__('Cost at least one ms loading time per day and resource', 'easyReservations').'</i>';
			return $rows;
		}

		add_filter('er_add_set_main_table_row', 'easyreservation_add_datepicker_option');

		function easyreservations_save_datepicker_option(){
			if(isset($_POST['reservations_datepicker'])) update_option('reservations_datepicker', array($_POST['reservations_datepicker'], $_POST['reservations_datepicker_avail']));
		}

		add_action('er_set_main_save', 'easyreservations_save_datepicker_option');
	}
?>