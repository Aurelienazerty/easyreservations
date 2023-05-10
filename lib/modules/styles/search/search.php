<?php
/*
Plugin Name: searchForm Module
Plugin URI: http://easyreservations.org/module/search/
Version: 1.2.8
Description: 3.4
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!.
*/
global $reservations_active_modules;
if(file_exists(dirname(__FILE__)."/searchtypes.php") && is_array($reservations_active_modules) && in_array('relatedpost', $reservations_active_modules)) 	include_once(dirname(__FILE__)."/searchtypes.php");
if(file_exists(dirname(__FILE__)."/attributes.php") && is_array($reservations_active_modules) && in_array('attributes', $reservations_active_modules)) 	include_once(dirname(__FILE__)."/attributes.php");

	function easyreservations_search_add_tinymce(){
		$custom_style = '';
		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/search.css')) $custom_style = '<option value="custom">' . addslashes(__("Custom Style", "easyReservations")) . '</option>'; ?>
		else if(x == "search") {
			var FieldAdd = '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_style"><?php echo addslashes(__("Style", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_search_style" name="easyreservation_search_style" style="width: 100px"><?php echo $custom_style; ?><option value="1" selected><?php echo addslashes(__("modern", "easyReservations")); ?></option></select></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_theme"><?php echo addslashes(__("Theme", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><select id="easyreservation_search_theme" name="easyreservation_search_theme" style="width: 100px"><option value="list" selected><?php echo addslashes(__("List", "easyReservations")); ?></option><option value="table"><?php echo addslashes(__("Table", "easyReservations")); ?></option></select></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_width"><?php echo addslashes(__("Width", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><select name="easyreservation_search_width" id="easyreservation_search_width"><?php echo easyreservations_num_options(1,100,100); ?></select> %</td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_form"><?php echo addslashes(__('Form', 'easyReservations')); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_search_form" name="easyreservation_search_form" style="width: 180px" value="<?php echo get_option('reservations_edit_url'); ?>"> <?php echo addslashes(__("URL to page or post with form", "easyReservations")); ?></label> </label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_start"><?php echo addslashes(__("Search", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox" id="easyreservation_search_start" name="easyreservation_search_start" checked> <?php echo addslashes(__("Search after page loaded", "easyReservations")); ?></label><label><input type="checkbox" id="easyreservation_search_direct" name="easyreservation_search_direct" checked> <?php echo addslashes(__("Search after every change", "easyReservations")); ?></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_exclude"><?php echo addslashes(__("Exclude", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_search_exclude" name="easyreservation_search_exclude"> <?php echo addslashes(__("Exclude resource by comma saperated IDs", "easyReservations")); ?></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_unavail"><?php echo addslashes(__("Unavailable", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox" id="easyreservation_search_unavail" name="easyreservation_search_unavail" checked> <?php echo addslashes(__("Show unavailable resources too", "easyReservations")); ?></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_image"><?php echo addslashes(__("Thumbnail", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox" id="easyreservation_search_image" name="easyreservation_search_image" checked> <?php echo addslashes(__("Width", "easyReservations")); ?>:</label> <input type="text" id="easyreservation_search_img_x" name="easyreservation_search_img_x" style="width: 40px" value="100"> px <label><?php echo addslashes(__("Height", "easyReservations")); ?>: <input type="text" id="easyreservation_search_img_y" name="easyreservation_search_img_y" style="width: 40px" value="100"></label> px</td>';
			FieldAdd += '</tr>';
			FieldAdd += '</tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label><?php echo addslashes(__('Show', 'easyReservations')); ?></label></td>';
			FieldAdd += '<td><label><input type="checkbox" id="easyreservation_search_price" name="easyreservation_search_price" checked> <?php echo addslashes(__("Price", "easyReservations")); ?></label><br><label><input type="checkbox" id="easyreservation_search_availability" name="easyreservation_search_availability" checked> <?php echo addslashes(__("Availability", "easyReservations")); ?></label><br><label><input type="checkbox" id="easyreservation_search_content" name="easyreservation_search_content" checked> <input type="text" id="easyreservation_search_content_max" name="easyreservation_search_content_max" style="width: 40px" value="500"> <?php echo addslashes(__("first characters of content", "easyReservations")); ?></label><br><label><input type="checkbox" id="easyreservation_search_more" name="easyreservation_search_more" checked> <?php echo addslashes(__("More link to resources post", "easyReservations")); ?></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label><?php echo addslashes(__('Calendar', 'easyReservations')); ?></label></td>';
			FieldAdd += '<td><input type="checkbox" id="easyreservation_search_cal_show" name="easyreservation_search_cal_show" checked> <label><?php echo addslashes(sprintf(__("Show %s days in calendar with %s", "easyReservations"), '<input type="text" id="easyreservation_search_cal_days"  value="15" style="width: 40px">', '</label><select id="easyreservation_search_cal_display"><option value="none">'. addslashes(__('Nothing', 'easyReservations')).'</option><option value="price">'. addslashes(__('Price', 'easyReservations')).'</option><option value="left">'. addslashes(__('Free space count', 'easyReservations')).'</option></select>')); ?><br></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_submit"><?php echo addslashes(__("Submit", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_search_submit" name="easyreservation_search_submit" style="width: 100px" value="Search"><?php _e("Text for seach button", "easyReservations"); ?></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" valign="top"><label for="easyreservation_search_submit"><?php echo addslashes(__("Reserve", "easyReservations")); ?></label></td>';
			FieldAdd += '<td><label><input type="text" id="easyreservation_search_reserve" name="easyreservation_search_reserve" style="width: 100px" value="Reserve now!"><?php echo addslashes(__("Text for reserve link/button", "easyReservations")); ?></label></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr>';
			FieldAdd += '<td nowrap="nowrap" style="vertical-align:top"><label for="easyreservation_search_res_name"><?php _e("Resource", "easyReservations"); ?>: </label></td>';
			FieldAdd += '<td><input type="text" id="easyreservation_search_res_name" name="easyreservation_search_res_name" style="width:150px;padding:3px;font-size:13px" value="Room"> <?php echo addslashes(__("Name for resources", "easyReservations")); ?></td>';
			FieldAdd += '</tr>';
			FieldAdd += '<tr><td colspan="2"><?php echo addslashes(__("This shortcode adds an availability search form to the post or page", "easyReservations")); ?>. <?php echo addslashes(__("You can combine it with a calendar by add it to the same page", "easyReservations")); ?>.<br><b><?php echo addslashes(__("Only add it once per page or post", "easyReservations")); ?>.</b></td></tr>';
			document.getElementById("tiny_Field").innerHTML = FieldAdd;
		}<?php
	}

	add_action('easy-tinymce-add', 'easyreservations_search_add_tinymce');

	function easyreservations_search_save_tinymce(){?>
		else if(y == "search"){
			classAttribs += ' style="' + document.getElementById('easyreservation_search_style').value + '"';
			classAttribs += ' theme="' + document.getElementById('easyreservation_search_theme').value + '"';
			classAttribs += ' submit_button="' + document.getElementById('easyreservation_search_submit').value + '"';
			classAttribs += ' reserve_text="' + document.getElementById('easyreservation_search_reserve').value + '"';
			classAttribs += ' width="' + document.getElementById('easyreservation_search_width').value + '"';
			classAttribs += ' form_url="' + document.getElementById('easyreservation_search_form').value + '"';
			classAttribs += ' resourcename="' + document.getElementById('easyreservation_search_res_name').value + '"';
			if(document.getElementById('easyreservation_search_exclude').value != '') classAttribs += ' exclude="' + document.getElementById('easyreservation_search_exclude').value + '"';
			if(document.getElementById('easyreservation_search_start').checked == true) classAttribs += ' start="1"';
			if(document.getElementById('easyreservation_search_direct').checked == true) classAttribs += ' searchdirectly="1"';
			if(document.getElementById('easyreservation_search_unavail').checked == true) classAttribs += ' unavail="1"';
			if(document.getElementById('easyreservation_search_image').checked == true){
				classAttribs += ' image="1"';
				classAttribs += ' img_x="' + document.getElementById('easyreservation_search_img_x').value + '"';
				classAttribs += ' img_y="' + document.getElementById('easyreservation_search_img_y').value + '"';
			}
			if(document.getElementById('easyreservation_search_price').checked == true) classAttribs += ' price="1"';
			if(document.getElementById('easyreservation_search_availability').checked == true) classAttribs += ' availability="1"';
			if(document.getElementById('easyreservation_search_more').checked == true) classAttribs += ' more="1"';
			if(document.getElementById('easyreservation_search_content').checked == true) classAttribs += ' content="' + document.getElementById('easyreservation_search_content_max').value + '"';
			if(document.getElementById('easyreservation_search_cal_show').checked == true){
				classAttribs += ' calendar="' + document.getElementById('easyreservation_search_cal_days').value + '"';
				classAttribs += ' calendar_mode="' + document.getElementById('easyreservation_search_cal_display').value + '"';
			}
		} <?php
	}

	add_action('easy-tinymce-save', 'easyreservations_search_save_tinymce');

	function easyreservations_search_add_tinymce_name(){
		echo '<option value="search">'.__("Search", "easyReservations").'</option>';
	}

	add_action('easy-tinymce-add-name', 'easyreservations_search_add_tinymce_name');

if(is_admin()){

	function easyreservations_search_add_set(){ ?>
		<form method="post" id="easy-search-form">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width:100%;margin-top:7px;margin-bottom:7px">
				<thead>
					<tr>
						<th><?php echo __( 'searchForm bar' , 'easyReservations' );?><input class="easybutton button-primary" type="submit" style="float:right" onclick="document.getElementById('easy-search-form').submit(); return false;" value="<?php echo __( 'Save' , 'easyReservations' );?>"></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<p>This is the pattern for the search bar in all your <code>[easy_search]</code> forms</p>
							<?php do_action('easy-search-bar'); ?>	
							<textarea name="easy_search_bar" id="easy_search_bar" style="width:100%;height:200px;"><?php
									$the_search_bar = get_option('reservations_search_bar');
									if(!$the_search_bar || empty($the_search_bar)) $the_search_bar = '[date-from value="+1"] [date-to value="+8"] Adults: [adults value="3" max="5"] Childs: [childs value="1" max="5"][submit value="Search"]';
									echo $the_search_bar;
							?></textarea>
							<p><b>Avaialble tags</b> - <i>they have the same standards and requirement relations like in forms</i></p>
							<p>
								<code style="cursor:pointer" onclick="searchOnclick(1)">[date-from]</code>
								<code style="cursor:pointer" onclick="searchOnclick(2)">[date-from-hour]</code>
								<code style="cursor:pointer" onclick="searchOnclick(3)">[date-from-min]</code>
								<code style="cursor:pointer" onclick="searchOnclick(4)">[date-to]</code>
								<code style="cursor:pointer" onclick="searchOnclick(5)">[date-to-hour]</code>
								<code style="cursor:pointer" onclick="searchOnclick(6)">[date-to-min]</code>
								<code style="cursor:pointer" onclick="searchOnclick(7)">[units]</code>
								<code style="cursor:pointer" onclick="searchOnclick(8)">[adults]</code>
								<code style="cursor:pointer" onclick="searchOnclick(9)">[childs]</code>
								<code style="cursor:pointer" onclick="searchOnclick(10)">[submit]</code>
								<code style="cursor:pointer" onclick="searchOnclick(11)">[theme]</code>
							</p>
							<script>
								function searchOnclick(nr){
									if(nr == 1) document.getElementById('easy_search_bar').value += '[date-from value="+1"]';
									else if(nr == 2) document.getElementById('easy_search_bar').value += '[date-from-hour value="12"]';
									else if(nr == 3) document.getElementById('easy_search_bar').value += '[date-from-min value="0"]';
									else if(nr == 4) document.getElementById('easy_search_bar').value += '[date-to value="+7"]';
									else if(nr == 5) document.getElementById('easy_search_bar').value += '[date-to-hour value="12"]';
									else if(nr == 6) document.getElementById('easy_search_bar').value += '[date-to-min value="0"]';
									else if(nr == 7) document.getElementById('easy_search_bar').value += '[units value="7" max="30"]';
									else if(nr == 8) document.getElementById('easy_search_bar').value += '[adults value="2" max="10"]';
									else if(nr == 9) document.getElementById('easy_search_bar').value += '[childs value="0" max="10"]';
									else if(nr == 10) document.getElementById('easy_search_bar').value += '[submit value="Search"]';
									else if(nr == 11) document.getElementById('easy_search_bar').value += '[theme]';
								}
							</script>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	<?php
	}

	add_action('er_set_main_side_top', 'easyreservations_search_add_set');

	function easyreservations_search_save_set(){
		if(isset($_POST['easy_search_bar'])){
			update_option('reservations_search_bar', stripslashes($_POST['easy_search_bar']));
			echo '<div class="updated"><p>'.__( 'Search form settings saved' , 'easyReservations' ).'</p></div>';
		}
	}

	add_action('er_set_save', 'easyreservations_search_save_set' );

} else {

	function easyreservations_search_shortcode($atts){
		global $easyreservations_script;
		if(isset($atts['content_max']) && (!isset($atts['content']) ||$atts['content_max'] > $atts['content'])) $atts['content'] = $atts['content_max'];
		$atts = shortcode_atts(array(
			'width' => 100,
			'img_x' => 100,
			'img_y' => 100,
			'image' => 0,
			'more' => 0,
			'style' => 0,
			'price' => 0,
			'half' => 1,
			'calendar' => 0,
			'calendar_mode' => 'left',
			'theme' => 'list',
			'maxpers' => 0,
			'content' => 0,
			'unavail' => 0,
			'availability' => 0,
			'start' => 0,
			'exclude' => '',
			'content_max' => 500,
			'form_url' => '',
			'resourcename' => __( 'Room' , 'easyReservations' ),
			'searchdirect' => 0,
			'submit_button' => __('Search'),
			'reserve_text' => __('Reserve now!'),
		), $atts);
		$atts['width'] = (float) $atts['width'];
		if($atts['width'] > 100) $atts['width'] = 100;
		$return = '';

		if(wp_style_is( 'easy-search-'.$atts['style'], 'registered')) wp_enqueue_style('easy-search-'.$atts['style'], false, array(), false, 'all');
		else wp_enqueue_style('easy-search-1' , false, array(), false, 'all');	

		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('datestyle');
		wp_enqueue_script('easyreservations_send_search');
		$the_search_bar = get_option('reservations_search_bar');
		$the_search_bar = apply_filters( 'easy-form-content', __($the_search_bar), false);
		$tags = easyreservations_shortcode_parser($the_search_bar, true);
		foreach($tags as $fields){
			$tags=shortcode_parse_atts( $fields);
			$value = isset($tags['value']) ? $tags['value'] : '';
			$style = isset($tags['style']) ? $tags['style'] : '';
			if($tags[0]=="date-from"){
				if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, time());
				elseif(preg_match('/\+{1}[1-9]+/i', $value)){
					$cutplus = str_replace('+', '',$value);
					$value = date(RESERVATIONS_DATE_FORMAT, time()+($cutplus*86400));
				}
				if(isset($_POST['to'])) $value = $_POST['from'];
				$the_search_bar=str_replace('['.$fields.']','<input type="text" id="easy-search-from" name="from" style="position:relative;z-index:100000;width:75px;'.$style.'" value="'.$value.'">', $the_search_bar);
			} elseif($tags[0]=="date-to"){
				if(empty($value)) $value = date(RESERVATIONS_DATE_FORMAT, time());
				elseif(preg_match('/\+{1}[1-9]+/i', $value)){
					$cutplus = str_replace('+', '',$value);
					$value = date(RESERVATIONS_DATE_FORMAT, time()+($cutplus*86400));
				}
				if(isset($_POST['to'])) $value = $_POST['to'];
				$the_search_bar=str_replace('['.$fields.']','<input type="text" id="easy-search-to" name="to" style="position:relative;z-index:100000;width:75px;'.$style.'" value="'.$value.'">', $the_search_bar);
			} elseif($tags[0]=="date-from-hour" || $tags[0]=="date-to-hour"){
				if($tags[0] == "date-from-hour"){
					if(isset($_POST['nights']) && is_numeric($_POST['date-from-hour'])) $value = $_POST['date-from-hour'];
				} else {
					if(isset($_POST['nights']) && is_numeric($_POST['date-to-hour'])) $value = $_POST['date-to-hour'];
				}
				$the_search_bar=str_replace('['.$fields.']', '<select id="'.$tags[0].'" name="'.$tags[0].'" style="'.$style.'">'.easyreservations_num_options("00", 23, $value).'</select>', $the_search_bar);
			} elseif($tags[0]=="date-from-min" || $tags[0]=="date-to-min"){
				if($tags[0] == "date-from-min") if(isset($_POST['nights']) && is_numeric($_POST['date-from-min'])) $value = $_POST['date-from-min'];
				else if(isset($_POST['nights']) && is_numeric($_POST['date-to-min'])) $value = $_POST['date-to-min'];
				$the_search_bar=str_replace('['.$fields.']', '<select id="'.$tags[0].'" name="'.$tags[0].'" style="'.$style.'">'.easyreservations_num_options("00", 59, $value).'</select>', $the_search_bar);
			} elseif($tags[0]=="nights" || $tags[0]=="units" || $tags[0]=="times"){
				$max = isset($tags['max']) ? $tags['max'] : 10;
				if(isset($_POST['nights']) && is_numeric($_POST['nights'])) $value = intval($_POST['nights']);
				$the_search_bar=str_replace('['.$fields.']','<select id="easy-form-nights" name="nights" style="'.$style.'">'.easyreservations_num_options(1, $max, $value).'</select>', $the_search_bar);
			} elseif($tags[0]=="adults"){
				$max = isset($tags['max']) ? $tags['max'] : 10;
				if(isset($_POST['persons']) && is_numeric($_POST['persons'])) $value = intval($_POST['persons']);
				$the_search_bar=str_replace('['.$fields.']','<select id="easy-form-persons" name="persons" style="'.$style.'">'.easyreservations_num_options(1, $max, $value).'</select>', $the_search_bar);
			} elseif($tags[0]=="childs"){
				$max = isset($tags['max']) ? $tags['max'] : 10;
				if(isset($_POST['childs']) && is_numeric($_POST['childs'])) $value = intval($_POST['childs']);
				$the_search_bar=str_replace('['.$fields.']','<select id="easy-form-childs" name="childs" style="'.$style.'">'.easyreservations_num_options(0, $max, $value).'</select>', $the_search_bar);
			} elseif($tags[0]=="submit"){
				if(empty($value)) $value = $atts['submit_button'];
				$the_search_bar=str_replace('['.$fields.']','<input type="button" id="easy-form-submit" name="submit" onclick="easyreservations_send_search();" class="easy-search-submit" value="'.$value.'" style="'.$style.'"><div id="easy-form-loading" style="float:right;vertical-align:middle"></div>', $the_search_bar);
			} elseif($tags[0]=="row" || $tags[0]=="box"){
				$style = isset($tags['style']) ? $tags['style'] : '';
				$class = $tags[0]=="row" ? 'row' : 'box';
				$the_search_bar=str_replace('['.$fields.']','<span class="search_'.$class.'" style="'.$style.'">', $the_search_bar);
			} elseif($tags[0]=="row-end" || $tags[0]=="box-end"){
				$the_search_bar=str_replace('['.$fields.']','</span>', $the_search_bar);
			} elseif($tags[0]=="theme"){
				if($atts['theme'] == 'table') $button = '<img style="cursor:pointer;vertical-align:middle;float:right;margin:6px;" onclick="easyChangeTheme(this);" title="Table" src="'.WP_PLUGIN_URL.'/easyreservations/images/table.png"><input type="hidden" id="easysearchtheme" name="theme" value="table">';
				else $button = '<img style="cursor:pointer;vertical-align:middle;float:right;margin:6px;" onclick="easyChangeTheme(this);" title="List" src="'.WP_PLUGIN_URL.'/easyreservations/images/list.png"><input type="hidden" id="easysearchtheme" name="theme" value="list">';
				$the_search_bar=str_replace('['.$fields.']',$button, $the_search_bar);
			} else {
				$the_search_bar = apply_filters('easy-search-tag-unknown', $the_search_bar,  $fields);
			}
		}

		if($atts['searchdirect'] == 1) $easyreservations_script .= "jQuery(document).ready(function(){jQuery('#easy_search_formular input, #easy_search_formular select').change(function(){easyreservations_send_search();});});";
		$easyreservations_script .= 'var searchAtts = \''.json_encode($atts).'\';';
		$return .= '<form name="easy_search_formular" id="easy_search_formular" style="margin:0px !important;padding:0px !important;display:inline-block;width:'.$atts['width'].'%">';
			$return .= '<input type="hidden" id="easy-search-nonce" value="'.wp_create_nonce( 'easy-search-nonce' ).'">';
			$return .= '<div id="searchbar"  style="margin-right:auto;margin-left:auto;vertical-align:middle;padding:0;width:'.$atts['width'].'%"><div class="easy-searchbar">';
				$return .= $the_search_bar;
			$return .= '</div></div>';
		$return .= '</form><div id="easy_search_div" style="width:'.$atts['width'].'%"></div>';

		if($atts['start'] > 0) $easyreservations_script .= 'easyreservations_send_search();';
		add_action('wp_print_footer_scripts', 'easyreservations_search_make_datepicker', 10 , 2);
		return $return;
	}

	add_shortcode('easy_search', 'easyreservations_search_shortcode');

	function easyreservations_register_search_scripts(){
		$lang = '';
		if(function_exists('icl_object_id')) $lang = '?lang=' . ICL_LANGUAGE_CODE;
		elseif(function_exists('qtrans_getLanguage')) $lang = '?lang=' . qtrans_getLanguage();

		wp_register_script('easyreservations_send_search', WP_PLUGIN_URL.'/easyreservations/lib/modules/search/send_search.js' , array( "jquery" ));
		wp_localize_script('easyreservations_send_search', 'easyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php'.$lang ), 'plugin_url' => WP_PLUGIN_URL, 'easydateformat' => RESERVATIONS_DATE_FORMAT ) );

		if(file_exists(WP_PLUGIN_DIR . '/easyreservations/css/custom/search.css')) wp_register_style('easy-search-custom', WP_PLUGIN_URL.'/easyreservations/css/custom/search.css');
		wp_register_style('easy-search-1', WP_PLUGIN_URL.'/easyreservations/lib/modules/search/css/style_1.css');
	}

	add_action('wp_enqueue_scripts', 'easyreservations_register_search_scripts');
}
	$easy_max_persons = '';

	function easyreservations_send_search_callback(){
		check_ajax_referer('easy-search-nonce', 'security');
		$arrival = strtotime($_POST['from'])+$_POST['fromplus'];

		if(!empty($_POST['to'])){
			$departure = strtotime($_POST['to'])+$_POST['toplus'];
			$nights = easyreservations_get_nights(86400, $arrival, $departure);
		} else $nights = $_POST['nights'];

		if(!empty($_POST['persons'])) $adults = $_POST['persons'];
		else $adults = 1;
		if(!empty($_POST['childs'])) $childs = $_POST['childs'];
		else $childs = 0;

		$atts = (array) json_decode( str_replace( '\\',  '', $_POST['atts']));
		if($nights > 0){
			$rooms = easyreservations_get_rooms(1);
			easyreservations_load_resources(true);
			global $the_rooms_intervals_array;
			
			if(!empty($_POST['theme'])) $atts['theme'] = $_POST['theme'];
			if($atts['theme'] == 'table'){
				$cellcount = 3;
				echo '<table class="search-table"><thead><tr>';
				if($atts['image'] > 0 && function_exists('get_the_post_thumbnail')){
					$cellcount++;
					echo '<th>&nbsp;</th>';
				}
				echo '<th>'.__($atts['resourcename']).'</th>';
				if($atts['maxpers'] > 0){
					$cellcount++;
					echo '<th>'.__( 'Max' , 'easyReservations' ).'</th>';
				}
				if($atts['price'] > 0){
					$cellcount++;
					echo '<th>'.__( 'Price' , 'easyReservations' ).'</th>';
				}
				echo '<th>&nbsp;</th></tr></thead>';
				$table = true;
			} else $table = false;
	
			foreach($rooms as $room){
				if(!empty($_POST['to'])) $nights = easyreservations_get_nights($the_rooms_intervals_array[$room->ID], $arrival, $departure);
				else $departure = $arrival + ($nights * $the_rooms_intervals_array[$room->ID]) + $_POST['toplus'];
				$res = new Reservation(false, array('email' => 'mail@test.com', 'arrival' => $arrival, 'departure' =>$departure,'resource' => (int) $room->ID, 'adults' => (int) $adults, 'childs' => (int) $childs,'reservated' => time()), false);
				try {
					$real_interval = easyreservations_get_interval($the_rooms_intervals_array[$room->ID], 0, 1);
					$roomcount = get_post_meta($room->ID, 'roomcount', true);
					if(is_array($roomcount)){
						$roomcount = $roomcount[0];
						$bypersons = true;
					}
					$avail = $res->checkAvailability(1);
					if($atts['unavail'] == 0 && $avail) continue;
					else {
						if(!empty($atts['exclude']) && in_array($room->ID, explode(',', $atts['exclude']))) continue;
						$errors = $res->Validate(false, 1, true);
						$error = '';
						if($errors){
							if(!is_array($errors)) $errors = array($errors);
							foreach($errors as $aerror){
								if($aerror[0] == 'pers-min') $error .= '| '.sprintf(__( 'min %s pers' , 'easyReservations' ), $aerror[1]).' ';
								elseif($aerror[0] == 'pers-max') $error .= '| '.sprintf(__( 'max %s pers' , 'easyReservations' ), $aerror[1]).' ';
								elseif($aerror[0] == 'nights-min') $error .= '| '.sprintf(__( 'min %1$s %2$s' , 'easyReservations' ), $aerror[1], easyreservations_interval_infos($the_rooms_intervals_array[$room->ID], 0, $aerror[1])).' ';
								elseif($aerror[0] == 'nights-max') $error .= '| '.sprintf(__( 'max %1$s %2$s' , 'easyReservations' ), $aerror[1], easyreservations_interval_infos($the_rooms_intervals_array[$room->ID], 0, $aerror[1])).' ';
								elseif($aerror[0] == 'start-on') $error .= '| '.sprintf(__( 'arrival only %s' , 'easyReservations' ), $aerror[1]).' ';
								elseif($aerror[0] == 'end-on') $error .= '| '.sprintf(__( 'departure only %s' , 'easyReservations' ), $aerror[1]).' ';
							}
						}
						if(!empty($error) && $atts['unavail'] == 0) continue;
						elseif(!empty($error)) $room->error = $error;
						$room->roomcount = $roomcount;
						$room->occupied = $avail;
						$room->resid = $room -> ID;
					}

					$room = apply_filters('easy_search_resources', $room);

					if(!$room) continue;
					$class = $room->occupied ? 'disabled' : '';

					if($table) echo '<tbody><tr class="entry">';
					else echo '<div class="easy-search-room '.$class.'" style="min-height:'.$atts['img_y'].'px"><div class="easy-search-room-inner">';
						if($atts['image'] > 0 && function_exists('get_the_post_thumbnail')){
							if($table) echo '<td onclick="easyShowDescription('.$room->ID.');" style="width:'.$atts['img_y'].'px" class="easy-search-logo">';
							echo get_the_post_thumbnail( $room->resid, array($atts['img_y'], $atts['img_x']));
							if($table) echo '</td>';
						}

						if($table) echo '<td onclick="easyShowDescription('.$room->ID.');" class="easy-search-h1">'.__($room->post_title).'</td>';
						else echo '<span class="easy-search-h1">'.__($room->post_title).'</span>';
						if($table && $atts['maxpers'] > 0){
							global $easy_max_persons;
							$max = isset($easy_max_persons) && !empty($easy_max_persons) && $easy_max_persons > 0 ? $easy_max_persons : '&infin;';
							echo '<td>'.$max.'</td>';
						}
						if($atts['price'] > 0){
							$res->Calculate();
							if($table){
								if(!$room->occupied && !isset($room->error)) echo '<td class="easy-search-price">'.easyreservations_format_money($res->price, 1).'</td>';
							} else echo '<span class="easy-search-price">'.easyreservations_format_money($res->price, 1).'</span>';
						}

						$avail_str = '';
						if(isset($room->error)){
							$avail_str = substr($room->error,2);
						} elseif($room->occupied){
							$avail_str = $errors[] = __( 'Not available at' , 'easyReservations' ).' '.$avail;
						}

						if($table && !empty($avail_str)) echo '<td colspan="2" class="error">'.$avail_str.'</td>';
						elseif(!$table && !empty($avail_str)) echo '<span id="easy-search-avail" class="error">'.$avail_str .'</span>';
						elseif(!$table && $atts['availability'] > 0) echo '<span id="easy-search-avail">'.__('Available', 'easyReservations').' '.(isset($room->error) ? $room->error : '') .'</span>';

						if(isset($atts['more']) && $atts['more'] > 0){
							if(!is_numeric($atts['more']) && strlen($atts['more']) > 3) $more_text = __($atts['more']);
							else $more_text = __('more', 'easyReservations');
							if(function_exists('icl_object_id')) $more = ' <a href="'.get_permalink(icl_object_id((int)$room->resid, 'any', true)).'">'.$more_text.'</a>';
							else $more = ' <a href="'.get_permalink($room->resid).'">'.$more_text.'</a>';
						} else $more = '';

						$room->post_content = substr(__(strip_shortcodes($room->post_content)), 0, $atts['content']).$more;

						global $shortcode_tags;
						$shortcode_tags['easy_hourlycalendar'] = 'easyreservations_hourlycal_shortcode';
						$shortcode_tags['easy_search'] = 'easyreservations_search_shortcode';
						$shortcode_tags['easy_edit'] = 'easyreservations_edit_shortcode';
						$shortcode_tags['easy_form'] = 'reservations_form_shortcode';
						$shortcode_tags['easy_calendar'] = 'reservations_calendar_shortcode';

						$content_str = '';
						if($atts['content'] > 0){
							$content_str = '<div class="easy-search-content">'.__($room->post_content).'</div>';
							if(!$table) echo $content_str;
						}

						if(!empty($atts['form_url']) && empty($avail_str)){
							if($table) echo '<td class="submit">';
							if($atts['form_url'] == "res") $url = get_permalink($room->resid);
							else $url = $atts['form_url'];
							echo '<form name="easy-search-reserve-form" method="post" action="'.$url.'">';
								echo '<input type="hidden" name="easyroom" value="'.$room->ID.'">';
								echo '<input type="hidden" name="persons" value="'.$adults.'">';
								echo '<input type="hidden" name="childs" value="'.$childs.'">';
								echo '<input type="hidden" name="nights" value="'.$nights.'">';
								echo '<input type="hidden" name="from" value="'.date(RESERVATIONS_DATE_FORMAT, $arrival).'">';
								echo '<input type="hidden" name="date-from-hour" value="'.date("G", $arrival).'">';
								echo '<input type="hidden" name="date-to-hour" value="'.date("G", $departure).'">';
								echo '<input type="hidden" name="date-from-min" value="'.date("i", $arrival).'">';
								echo '<input type="hidden" name="date-to-min" value="'.date("i", $departure).'">';
								echo '<input type="hidden" name="to" value="'.date(RESERVATIONS_DATE_FORMAT, $departure).'">';
								if($table) echo '<input type="submit" value="'.__($atts['reserve_text']).'" class="easy-entry-submit">';
								else echo '<a class="easy-search-form-submit" onclick="this.parentNode.submit()">'.__($atts['reserve_text']).'</a>';
							echo '</form>';
							if($table) echo '</td>';
						}
						if($table){
							echo '</tr>';
							if(!empty($content_str)) echo '<tr id="resource_content_'.$room->ID.'" style="display:none" class="content"><td colspan="'.$cellcount.'">'.$content_str.'</td></tr>';
						}
						else echo '</div></div>';
						if($atts['calendar'] > 0){
							$header = ''; $content = ''; $last = null;
							$day_names = easyreservations_get_date_name(0, 3);
							if($nights > $atts['calendar']) $start = $arrival;
							else $start = $arrival - (($atts['calendar'] - $nights)/2*$real_interval);
							if($table) echo '<tr class="calendar"><td colspan="'.$cellcount.'">';
							echo '<table cellpadding="0" cellspacing="0" class="easy-search-calendar-table">';
							for($i = 0; $i < $atts['calendar']; $i++){
								$date_of_day = strtotime(date("d.m.Y", $start)) + ($i * $real_interval);
								if($real_interval > 3600){
									$date_of_day += ($real_interval/2);
								}
								$res->arrival = $date_of_day;
								$res->departure = $date_of_day+$real_interval;
								$res->times = 1;
								$avail = $res->checkAvailability(5);
								$left = $room->roomcount - round($avail[0]);
								$class_header = $date_of_day >= strtotime("midnight", $arrival) && $date_of_day <= $departure ? 'easy-select' : '';

								if($avail[0] >= $room->roomcount) $class_content = " easy-full";
								elseif($avail[0] > 0) $class_content = " easy-part";
								else $class_content = " easy-empty";

								$new = $class_content;
								if($last == null){
									$res->arrival -= 86400;
									$lastavail = $res->checkAvailability(5);
									$res->arrival += 86400;

									if($lastavail[0] >= $room->roomcount) $last = " easy-full";
									elseif($lastavail[0] > 0) $last = " easy-part";
									else $last = " easy-empty";
								}
								if($atts['half'] == 1 && $last !== $new && ($avail[1] > 0)){
									$class_content.= $last.'2';
									if($last == ' easy-empty'){
										$class_content.=" calendar-cell-halfstart";
									} else {
										$class_content.=" calendar-cell-halfend";
									}
								}
								$last = $new;

								if($atts['calendar_mode'] == 'left') $content_value = $left;
								elseif($atts['calendar_mode'] == 'price'){
									$res->Calculate();
									$content_value = easyreservations_format_money($res->price, 0, 0);
								} elseif($atts['calendar_mode'] == 'price2'){
									$res->Calculate();
									$content_value = easyreservations_format_money($res->price,1 , 0);
								} else $content_value = '';
								if($real_interval == 3600) $head_value = date("H", $res->arrival).'h';
								else $head_value = $day_names[date("N", $date_of_day)-1];
								$header.= '<th class="'.$class_header.'"><span class="easy-search-day">'.$head_value.'</span><span class="easy-search-day">'.date("d.m", $date_of_day).'</span></th>';
								$content.='<td class="'.$class_content.'"><span>'.$content_value.'</span></td>';
							}
							echo '<thead>';
								echo '<tr>';
									echo $header;
								echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
								echo '<tr>';
									echo $content;
								echo '</tr>';
							echo '</tbody>';
						echo '</table>';
						if($table) echo '</td></tr>';
					}
				} catch(Exception $e){
					echo $e->getMessage();
				}
			}
			if($table) echo '</tbody></table>';
		}
		if(!isset($avail_str)) echo '<div class="easy-search-error">'.__('None available in this period', 'easyReservations').'</div>';
		exit;
	}

	function easyreservations_search_make_datepicker(){
		easyreservations_build_datepicker(0, array('easy-search-from', 'easy-search-to'), false, true);
	}
	add_action('wp_ajax_easyreservations_send_search', 'easyreservations_send_search_callback');
	add_action('wp_ajax_nopriv_easyreservations_send_search', 'easyreservations_send_search_callback');
?>