var custom_temp_id = 1000, price_temp_id = 1000;

function easy_add_custom_content(){
	var id = jQuery('#custom_add_select').val();
	var content = '';
	if(id == 'custom' || id == 'price'){
		content += '<select name="custommodus" style="margin-bottom:4px" id="custommodus"><option value="edit">Editable</option><option value="visible">Visible</option><option value="hidden">Hidden</option></select> for Guests<br>';
		content += '<input type="text" name="customtitle" id="customtitle" style="width:260px" value="Title" onfocus="if (this.value == \'Title\') this.value = \'\';" onblur="if (this.value == \'\') this.value = \'Title\';"><br>';
		if(id == 'price'){
			content += '<input type="text" name="customvalue" id="customvalue" value="Value" style="width:120px;margin-top:2px;" onfocus="if (this.value == \'Value\') this.value = \'\';" onblur="if (this.value == \'\') this.value = \'Value\';" value="Value">';
			content += '<input type="text" name="customamount" id="customamount" style="width:60px;margin-top:2px;text-align:right;" value="">&'+easy_currency+';';
		} else content += '<textarea name="customvalue" id="customvalue" value="Value" style="width:260px;margin-top:2px;" onfocus="if (this.value == \'Value\') this.value = \'\';" onblur="if (this.value == \'\') this.value = \'Value\';">Value</textarea>';
	}
	content += '<div style="margin-top:5px"><input type="button" id="custom_add_field" class="button" value="Add custom field"></div>';
	jQuery('#custom_add_content').html(content);
	jQuery('#custom_add_field').bind('click', easy_generate_custom);
}

jQuery('#custom_add_select').bind('change', easy_add_custom_content);
easy_add_custom_content();

function easy_generate_custom(){
	var id = jQuery('#custom_add_select').val();
	var field = '';
	field += '<tr><td nowrap="nowrap"><img style="vertical-align:text-bottom;" src="'+easy_url+'images/';
	if(id == 'custom' || id == 'price'){
		var s = '';
		var temp_id = 0;
		if(id == 'price'){
			s = 'P';
			price_temp_id = price_temp_id + 1;
			temp_id = price_temp_id;
		} else {
			custom_temp_id = custom_temp_id + 1;
			temp_id = custom_temp_id;
		}
		if(id == 'price') field += 'money.png"> ';
		else field += 'message.png"> ';
		field += jQuery('#customtitle').val();
		field += ' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode)" src="'+easy_url+'images/delete.png"></td>';
		field += '<td>'+jQuery('#customvalue').val()+'<input type="hidden" name="custom'+s+'title'+temp_id+'" value="'+jQuery('#customtitle').val()+'">';
		field += '<input type="hidden" name="custom'+s+'value'+temp_id+'" value="'+jQuery('#customvalue').val()+'"><input type="hidden" name="custom'+s+'modus'+temp_id+'" value="'+jQuery('#custommodus').val()+'">';
		if(id == 'price'){
			field += ': '+jQuery('#customamount').val()+' &'+easy_currency+';<input name="custom_price'+temp_id+'" value="'+jQuery('#customamount').val()+'" type="hidden">';
			document.getElementById("customPrices").innerHTML += field+'</td></tr>';
		} else document.getElementById("testit").innerHTML += field+'</td></tr>';
	} else {
		var data = {
			action: 'easyreservations_get_custom',
			security:custom_nonce,
			id: id
		};
		jQuery.post(ajaxurl, data, function(response){
			response = jQuery.parseJSON(response);
			if(response && response[0]){
				if(response[1]['price']) field += 'money.png"> ';
				else field += 'message.png"> ';
				field += response[1]['title'];
				field += ' <img style="vertical-align:middle;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode)" src="'+easy_url+'images/delete.png"></td>';
				field += '<td>'+response[0]+'</td></tr>';
				if(response[1]['price']) document.getElementById("customPrices").innerHTML += field;
				else document.getElementById("testit").innerHTML += field;
			}
		});
	}
	jQuery('#custom_add_content').html('');
}

