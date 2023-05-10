<?php
/*
Plugin Name: searchForm Module Attributes addon
Plugin URI: http://easyreservations.org/module/search/
Version: 1.0.2
Description: 3.1.5
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!.
*/
if(is_admin()){
	add_action('er_set_save', 'easyreservations_attributes_savemain_setting');

	function easyreservations_attributes_savemain_setting(){
		if(isset($_GET['deleteattr'])){
			$attributes = get_option('reservations_search_attributes');
			if(isset($attributes[$_GET['deleteattr']])){
				unset($attributes[$_GET['deleteattr']]);
				update_option('reservations_search_attributes', $attributes);
				echo  '<div class="updated"><p>'.__( 'Attribute deleted' , 'easyReservations' ).'</p></div>';
			} else echo  '<div class="error"><p>'.__( 'Attribute not found' , 'easyReservations' ).'</p></div>';
		} elseif(isset($_POST['search_attribute_name']) && !empty($_POST['search_attribute_name'])){
			$attributes = get_option('reservations_search_attributes', array());
			$type =  $_POST['search_attribute_type'];
			$id = str_replace(' ', '', strtolower($_POST['search_attribute_name']));
			if($_POST['attribute_edit'] == '0' && isset($attributes[$id])){
				echo  '<div class="error"><p>'.__( 'There is already an attribute with a similar title' , 'easyReservations' ).'</p></div>';
				return false;
			}
			$attribute = array();
			$attribute['title'] = $_POST['search_attribute_name'];
			$attribute['type'] = $type;
			if($type == ''){
				echo  '<div class="error"><p>'.__( 'Select a type for the attribute' , 'easyReservations' ).'</p></div>';
				return false;
			} elseif($type == 'select'){
				if(!isset($_POST['search_attribute_type_select']) || empty($_POST['search_attribute_type_select'])){
					echo  '<div class="error"><p>'.__( 'No options provided' , 'easyReservations' ).'</p></div>';
					return false;
				}
				$attribute['select'] = $_POST['search_attribute_type_select'];
			} elseif($type == 'minmax'){
				if(!isset($_POST['search_attribute_type_min']) || (empty($_POST['search_attribute_type_min']) && $_POST['search_attribute_type_min'] != "0") || !is_numeric($_POST['search_attribute_type_min'])){
					echo  '<div class="error"><p>'.__( 'Minimum value must be entered and be a number' , 'easyReservations' ).'</p></div>';
					return false;
				}
				if(!isset($_POST['search_attribute_type_max']) || (empty($_POST['search_attribute_type_max']) && $_POST['search_attribute_type_max'] != "0") || !is_numeric($_POST['search_attribute_type_max'])){
					echo  '<div class="error"><p>'.__( 'Maximum value must be entered and be a number' , 'easyReservations' ).'</p></div>';
					return false;
				}
				$attribute['min'] = $_POST['search_attribute_type_min'];
				$attribute['max'] = $_POST['search_attribute_type_max'];
			}

			if($_POST['attribute_edit'] != '0' && isset($attributes[$_POST['attribute_edit']])){
				$attributes[$_POST['attribute_edit']] = $attribute;
				echo  '<div class="updated"><p>'.__( 'Attribute edited' , 'easyReservations' ).'</p></div>';
			} elseif($_POST['attribute_edit'] != 0){
				echo  '<div class="error"><p>'.__( 'Maximum value must be entered and be a number' , 'easyReservations' ).'</p></div>';
				return false;
			} else {
				$attributes[$id] = $attribute;
				echo  '<div class="updated"><p>'.__( 'Attribute added' , 'easyReservations' ).'</p></div>';
			}

			update_option('reservations_search_attributes', $attributes);
		}
	}

	add_action('er_set_main_out', 'easyreservations_attributes_main_setting');

	function easyreservations_attributes_main_setting(){
		$attributes = get_option('reservations_search_attributes'); ?>
		<form name="search_attributes_form" id="search_attributes_form" method="post">
		<input type="hidden" name="attribute_edit" id="attribute_edit" value="0">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width:100%;margin-top:7px;margin-bottom:7px">
			<thead>
				<tr>
					<th colspan="4"><?php echo __( 'searchForm attributes' , 'easyReservations' );?></th>
				</tr>
			</thead>
			<tbody><?php
				if($attributes && !empty($attributes)){
					echo '<script>easyAttributes = '.json_encode($attributes).';</script>';
					foreach($attributes as $key => $attribute){
						echo '<tr>';
							echo '<td>'.$attribute['title'].'</td>';
							if($attribute['type'] == 'check'){
								$type = 'Checkbox';
								$value = '';
							} elseif($attribute['type'] == 'select'){
								$type = 'Select';
								$value = $attribute['select'];
							} elseif($attribute['type'] == 'minmax'){
								$type = 'Min - Max';
								$value = $attribute['min'].' - '.$attribute['max'];
							}
							echo '<td style="font-weight:bold">'.$type.'</td>';
							echo '<td>'.$value.'</td>';
							echo '<td style="text-align:right"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'images/edit.png" style="cursor:pointer" onClick="editAttribute(\''.$key.'\');"> <a href="admin.php?page=reservation-settings&deleteattr='.$key.'"><img style="vertical-align:middle;" src="'.RESERVATIONS_URL.'images/delete.png"></a></td>';
						echo '</tr>';
					}
				} else {
					echo '<tr><td>'.__('No attributes' , 'easyReservations').'</td></tr>';
				} ?>
			</tbody>
			<thead>
				<tr>
					<th colspan="4"><?php echo __( 'Add attribute' , 'easyReservations' );?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:30%"><?php echo __( 'Name' , 'easyReservations' );?></td>
					<td colspan="3"><input type="text" name ="search_attribute_name" id="search_attribute_name"></td>
				</tr>
				<tr>
					<td><?php echo __( 'Type' , 'easyReservations' );?></td>
					<td colspan="3">
						<select onchange="editType(this.value);" name="search_attribute_type" id="search_attribute_type"><option value="check"><?php echo __( 'Checkbox' , 'easyReservations' );?></option><option value="select"><?php echo __( 'Select' , 'easyReservations' );?></option><option value="minmax"><?php echo __( 'Min - Max' , 'easyReservations' );?></option></select>
						<span id="search_attribute_type_span"></span>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="button" value="<?php echo __( 'Save Changes' , 'easyReservations' );?>" onclick="document.getElementById('search_attributes_form').submit(); return false;" style="margin-top:7px;" id="search_attribute_submit" class="easybutton button-primary" >
	</form>
	<script>
		function editAttribute(key){
			if(easyAttributes[key]){
				jQuery('#search_attribute_type_span').html('');
				var attribute = easyAttributes[key];
				jQuery('#search_attribute_name').attr('value', attribute['title']);
				jQuery('#attribute_edit').attr('value',key);
				jQuery('#search_attribute_submit').attr('value', '<?php echo addslashes(__( 'Edit' , 'easyReservations' ));?>');
				if(attribute['type'] == 'select'){
					document.getElementById('search_attribute_type').selectedIndex = 1;
					editType(attribute['type'], attribute['select']);
				} else if(attribute['type'] == 'minmax'){
					document.getElementById('search_attribute_type').selectedIndex = 2;
					editType(attribute['type'], attribute['min'], attribute['max']);
				} else {
					document.getElementById('search_attribute_type').selectedIndex = 0;
				}
			}
		}
		function editType(type,first,sec){
			jQuery('#search_attribute_type_span').html('');
			var content = '';
			var content1 = '';
			if(type == 'select'){
				if(first) content = first;
				jQuery('#search_attribute_type_span').html('<input type="text" name="search_attribute_type_select" value="'+content+'" style="width:400px;"> <?php echo addslashes(__( 'Comma separated options' , 'easyReservations' ));?>');
			} else if(type == 'minmax'){
				if(first) content = first;
				if(sec) content1 = sec;
				jQuery('#search_attribute_type_span').html('Min: <input type="text" name="search_attribute_type_min" value="'+content+'" style="width:100px;"> - Max: <input type="text" name="search_attribute_type_max" value="'+content1+'" style="width:100px;"> <?php echo addslashes(__( 'Only numbers' , 'easyReservations' ));?>');
			}
		}
	</script><?php
	}

	add_action('er_res_save', 'easyreservations_save_attributes_resources_settings', 10, 1);

	function easyreservations_save_attributes_resources_settings($resource){
		if(isset($_POST['search_attributes_form_submit'])){
			$attributes = get_option('reservations_search_attributes');
			$new_options = array();
			if($attributes){
				foreach($attributes as $key => $attribute){
					if(isset($_POST[$key])){
						$select = $_POST[$key];
						$new_options[$key] = $select;
						if($attribute['type'] == 'minmax'){
							if(!empty($select) && !is_numeric($select)){
								echo sprintf(__( 'Value of $s must be a number' , 'easyReservations' ), $attribute['title']);
								return false;
							}
						}
					} else $new_options[$key] = 0;
				}
				update_post_meta($resource,'reservations_attributes',$new_options);
			}
		}
	}

	add_action('easy-resource-side-end', 'easyreservations_attributes_resources_settings', 10, 1);

	function easyreservations_attributes_resources_settings($resource){
		$attributes = get_option('reservations_search_attributes');
		$resource_attributes = get_post_meta($resource,'reservations_attributes',true);
		if($attributes && !empty($attributes)){ ?>
			<form name="search_attributes_form" id="search_attributes_form" method="post">
				<input type="hidden" name="search_attributes_form_submit" value="1">
				<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width:100%;margin-top:7px;margin-bottom:7px">
					<thead>
						<tr>
							<th colspan="4"><?php echo __( 'searchForm attributes' , 'easyReservations' );?> <input type="button" style="float:right;" onclick="document.getElementById('search_attributes_form').submit(); return false;" class="button" value="<?php echo __('Save', 'easyReservations'); ?>"></th>
						</tr>
					</thead>
					<tbody><?php
						foreach($attributes as $key => $attribute){
							if(isset($resource_attributes[$key])) $current_options = $resource_attributes[$key];
							else $current_options = false;
							echo '<tr><td style="vertical-align:top">'.$attribute['title'];
							if($attribute['type'] == 'check'){
								$sel = $current_options && $current_options == 1 ? 'checked="checked"' : '';
								echo '</td><td><input type="checkbox" name="'.$key.'" value="1" '.$sel.'></td>';
							} elseif($attribute['type'] == 'select'){
								$sel = $current_options && is_array($current_options) ? $current_options : false;
								$options = explode(',',$attribute['select']);
								$options_string = '';
								foreach($options as $tkey => $option){
									$selected = $sel && in_array($tkey, $sel) ? 'selected="selected"' : '';
									$options_string .= '<option value="'.$tkey.'" '.$selected.'>'.$option.'</option>';
								}
								echo '</td><td><select name="'.$key.'[]" style="height: 80px;min-height: 80px;" multiple>'.$options_string.'</select></td>';
							} elseif($attribute['type'] == 'minmax'){
								$val = $current_options ? $current_options : '';
								echo ' '.$attribute['min'].' - '.$attribute['max'].'</td><td><input type="text" name="'.$key.'" value="'.$val.'" style="width:80px"></td>';
							}
							echo '</tr>';
						} ?>
					</tbody>
				</table>
			</form><?php
		}
	}

	add_action('easy-search-bar', 'easyreservations_search_bar_attribute_selector');
}

function easyreservations_search_bar_attribute_selector(){
	$attributes = get_option('reservations_search_attributes');
	if($attributes && !empty($attributes)){
		$options_string = '<option value="">Select</option>';
		foreach($attributes as $key => $attribute){
			$options_string .= '<option value="'.$key.'">'.$attribute['title'].'</option>';
		}
		echo '<select id="attribute_selector" onchange="firstAttribute(this.value);">'.$options_string.'</select><span id="attribute_selector_content"></span>';
		echo <<<JAVASCRIPT
<script type="text/javascript">
		function firstAttribute(key){
			if(easyAttributes[key]){
				document.getElementById('attribute_selector').disabled = true;
				var attribute = easyAttributes[key];
				var value = '';
				if(attribute['type'] == 'check'){
					value = '<select id="attribute_second" name="type"><option value="checkbox">Checkbox</option><option value="button">Button</option></select> <input type="checkbox" id="attribute_third" value="checked">';
				} else if(attribute['type'] == 'minmax'){
					value = '<select id="attribute_second" name="type"><option value="slider">Slider</option><option value="text">One text field (max)</option><option value="mtext">Two text fields (min-max)</option><option value="select">One select (max)</option><option value="mselect">Two selects (min-max)</option></select>';
				}
				value += '<a href="#" onclick="easyAddAttributeTag()" class="easybutton button-primary" style="margin:0px 2px 0px 2px"><b>Add</b></a><a href="#" onclick="easyResetAttributes()" class="easybutton button-primary" style="margin:0px 2px 0px 2px"><b>Reset</b></a>';
				document.getElementById('attribute_selector_content').innerHTML = value;
			}
		}
		
		function easyAddAttributeTag(){
			var key = document.getElementById('attribute_selector').value;
			var tag = '';
			if(easyAttributes[key]){
				var attribute = easyAttributes[key];
				tag = '[attr '+key;
				if(attribute['type'] == 'check'){
					tag += ' type="'+document.getElementById('attribute_second').value+'"';
					if(document.getElementById('attribute_third').checked == true) tag += ' checked';
				} else if(attribute['type'] == 'minmax'){
					tag += ' type="'+document.getElementById('attribute_second').value+'"';
				}
				tag+=']';
			}
			document.getElementById('easy_search_bar').innerHTML = tag+' '+document.getElementById('easy_search_bar').innerHTML;
		}
		
		function easyResetAttributes(){
			document.getElementById('attribute_selector').selectedIndex = 0;
			document.getElementById('attribute_selector').disabled = false;
			document.getElementById('attribute_selector_content').innerHTML = '';
		}
</script>
JAVASCRIPT;
	}
}

add_filter('easy-search-tag-unknown','easyreservations_attribute_tag_generator', 10, 2);

function easyreservations_attribute_tag_generator($the_search_bar, $fields){
	$tags=shortcode_parse_atts( $fields);
	if($tags[0] == 'attr'){
		if(isset($tags['width'])) $width = $tags['width'];
		else $width = '50';
		$value = '';
		$attributes = get_option('reservations_search_attributes');
		if(isset($attributes[$tags[1]])){
			$attribute = $attributes[$tags[1]];
			if($attribute['type'] == 'select'){
				$options = explode(',', $attribute['select']);
				$options_string = '';
				foreach($options as $key => $option) $options_string.= '<option value="'.$key.'">'.$option.'</option>';
				$width = isset($tags['width']) ? $tags['width'] : '';
				$value = '<select name="attr_'.$tags[1].'" style="width:'.$width.'px"><option value="emptyXX">Select</option>'.$options_string.'</select>';
			} elseif($attribute['type'] == 'check'){
				$checked = isset($tags['checked']) ? 'checked="checked"' : '';
				if(!isset($tags['type']) || $tags['type'] == 'checkbox'){
					$value = '<input type="checkbox" name="attr_'.$tags[1].'" value="1" '.$checked.'>';
				} else {
					if(isset($tags['value'])) $val = $tags['value'];
					else $val = $attribute['title'];
					$check = !empty($checked) ? ' checked' : '';
					$value = '<input type="button" class="attr_button'.$check.'" onclick="easyFakeButtonCheck(this,\'attr_'.$tags[1].'\');" value="'.$val.'"><input type="checkbox" id="attr_'.$tags[1].'" name="attr_'.$tags[1].'" style="width:1px;height:1px;display:hidden;" value="1"  '.$checked.'>';
				}
			} elseif($attribute['type'] == 'minmax'){
				if(!isset($tags['type']) || $tags['type'] == 'text'){
					$val = isset($tags['value']) ? $tags['value'] : '';
					$value = '<input type="text" maxlength="'.strlen($attribute['max']).'" class="easy_attr_numeric" name="attr_'.$tags[1].'" style="width:'.$width.'px" value="'.$val.'">';
				} elseif(!isset($tags['type']) || $tags['type'] == 'mtext'){
					$val = isset($tags['value']) ? $tags['value'] : '';
					$value = '<input type="text" maxlength="'.strlen($attribute['max']).'" class="easy_attr_numeric"  style="width:'.$width.'px" name="attr_'.$tags[1].'[]" value="'.$val.'"> - <input type="text" maxlength="'.strlen($attribute['max']).'" class="easy_attr_numeric" name="attr_'.$tags[1].'[]"  style="width:'.$width.'px" value="'.$val.'">';
				} elseif($tags['type'] == 'select'){
					$val = isset($tags['selected']) ? $tags['selected'] : '';
					$value = '<select name="attr_'.$tags[1].'" style="width:'.$width.'px"><option value="emptyXX">Select</option>'.easyreservations_num_options($attribute['min'],$attribute['max'],$val).'</select>';
				} elseif($tags['type'] == 'mselect'){
					$val = isset($tags['selected']) ? $tags['selected'] : '';
					$options = easyreservations_num_options($attribute['min'],$attribute['max'],$val);
					$value = '<select name="attr_'.$tags[1].'[]" style="width:'.$width.'px"><option value="emptyXX">Select</option>'.$options.'</select> - <select name="attr_'.$tags[1].'[]" style="width:'.$width.'px"><option value="emptyXX">Select</option>'.$options.'</select>';
				} elseif($tags['type'] == 'slider'){
					wp_enqueue_script('jquery-ui-slider');
					$val = isset($tags['value']) ? $tags['value'] : '';
					$rand = rand(1,1000);
					$value = '<span class="easy_attr_slider'.$rand.'" style="width:'.$width.'px"></span><span class="easy_attr_nr'.$rand.'" style="font-weight:bold"></span><input type="hidden" id="easy_attr_slider'.$rand.'" name="attr_'.$tags[1].'" value="'.$val.'"><script>jQuery(document).ready(function(){jQuery(".easy_attr_slider'.$rand.'").slider({ min: '.$attribute['min'].',max: '.$attribute['max'].', value: "'.$val.'", slide: function(event, ui) { jQuery(".easy_attr_slider'.$rand.'").attr("value", ui.value); jQuery(".easy_attr_nr'.$rand.'").attr("value", ui.value);} });});</script>';
				}
			}
		}
		$the_search_bar = str_replace('['.$fields.']',$value, $the_search_bar);
	}
	return $the_search_bar;
}

add_filter('easy_search_resources', 'easyreservations_search_attributes_check', 9 ,1);

function easyreservations_search_attributes_check($resource){
	$attributes = get_option('reservations_search_attributes');
	$resource_attributes = get_post_meta($resource->ID,'reservations_attributes',true);
	if($attributes && $resource_attributes && !empty($attributes) && !empty($resource_attributes)){
		$fields = array();
		parse_str($_POST['serialize'], $fields);
		foreach($attributes as $key => $attribute){
			if(isset($fields['attr_'.$key]) && $fields['attr_'.$key] !== NULL && $fields['attr_'.$key] != 'emptyXX'){
				if($attribute['type'] == 'check'){
					if(isset($resource_attributes[$key]) && $resource_attributes[$key] == 0){
						$resource = false;
						break;
					}
				} elseif($attribute['type'] == 'select'){
					if(isset($resource_attributes[$key]) && is_array($resource_attributes[$key]) && !in_array($fields['attr_'.$key], $resource_attributes[$key])){
						$resource = false;
						break;
					}
				} elseif($attribute['type'] == 'minmax'){
					if(isset($resource_attributes[$key])){
						$value = $fields['attr_'.$key];
						if(is_array($value)){
							$min = $value[0];
							$max = $value[1];
							if(!empty($min) && !empty($max)  && ($resource_attributes[$key] < $min || $resource_attributes[$key] > $max)){
								$resource = false;
								break;
							}
						} else {
							if(!empty($value) && $resource_attributes[$key] < $value){
								$resource = false;
								break;
							}
						}
					}
				}
			}
		}
	}
	return $resource;
}
?>