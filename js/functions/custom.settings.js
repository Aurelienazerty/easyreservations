var optTempID = 1, ifTempID = 1, first = true, added = {};

function custom_edit(id){
	if(all_custom_fields && all_custom_fields[id]){
		var field = all_custom_fields[id];
		jQuery('#custom_name').val(field['title']);
		if(field['price']) jQuery('#custom_price_field').prop('checked', 'checked');
		custom_type_select(field['type']);
		custom_field_extras(field);
		custom_field_value(field);
		jQuery('*[id^=clauses_sortable]').sortable();
		jQuery('#options_sortable').sortable();
		jQuery('#custom_creator').append('<input type="hidden" name="custom_id" id="custom_id" value="'+id+'">')
	}
}

function custom_type_select(sel){
	var options = {};
	options['x'] = '--';
	if(!sel) sel = jQuery('#custom_field_type').val();
	if(!jQuery('#custom_price_field').prop('checked') ){
		options['text'] = 'Text field';
		options['area'] = 'Text area';
	}
	options['check'] = 'Checkbox';
	options['select'] = 'Select field';
	options['radio'] = 'Radio buttons';
	jQuery('#custom_field_type').html(generateOptions(options, sel));
}

function custom_field_extras(sel){
	var value = '';
	if(sel) value = sel["unused"];
	var html = '<span title="Content in emails if custom field wasn\'t chosen." style="margin-bottom:4px;display: inline-block;">Unused <input type="text" name="custom_field_unused" value="'+value+'"></span>';
	var selected = '';
	if(sel && sel['required']) selected = ' checked="checked"';
	html += '<br><input type="checkbox" id="custom_field_required" name="custom_field_required"'+selected+'> Required';
	//if(jQuery('#custom_price_field').prop('checked') )
	jQuery('#custom_field_extras').html(html);
}

function custom_field_value(sel){
	var type = jQuery('#custom_field_type').val();
	jQuery('#custom_value_tr').remove();
	if(type !== 'x'){
		var options = '', value = '';
		if(type == 'text' || type == 'area'){
			if(!sel){
				sel = new Array();
				sel["value"] = "";
			}
			options = '<ul><li class="sortable">Value <input type="text" name="custom_field_value" value="'+sel["value"]+'"></li></ul>';
		} else {
			if(type !== 'check') options += '<strong>Options</strong> <a id="add_new_custom" href="javascript:add_new_option()">+</a>'
			options += '<ul id="options_sortable">'
			options += custom_generate_option(sel);
			options += '</ul>';
		}
		jQuery('#custom_type_tr').after('<tr id="custom_value_tr"><td colspan="2">'+options+'</td></tr>');
		jQueryTooltip();
	}
}

function custom_generate_option(sel){
	if(!sel){
		var sel = new Object ();
		sel["type"] = jQuery('#custom_field_type').val();
		sel['options'] = new Object ();
		sel['options'][optTempID] = new Object ();
		sel['options'][optTempID]["value"] = "";
		if(jQuery('#custom_price_field').prop('checked')) sel['options'][optTempID]["price"] = 100;
		if(sel["type"] == 'check' || sel["type"] == 'check') sel['options'][optTempID]["checked"] = false;
	}
	var options = '';
	for(var k in sel['options']) {
		var v = sel['options'][k];
		options += '<li class="sortable" id="option_'+k+'">';
		if(sel['type'] !== 'check') options += '<img src="'+plugin_url+'/easyreservations/images/delete.png" onclick="delete_option(this);" style="float:right">';
		options += '<input type="hidden" name="id[]" value="'+k+'">';
		options += 'Value <input type="text" name="value[]" value="'+v["value"]+'">';
		if(sel['type'] == 'check' || sel['type'] == 'radio'){
			var checked = '';
			if(v['checked']) checked = ' checked="checked"';
			options += '<input type="checkbox" name="checked[]"'+checked+' onchange="check_checkboxes(this)" value="1"> Checked';
		}
		else options += '<input type="hidden" name="checked[]" value="0">';
		if(v["price"]){
			options += ' Price <input type="text" name="price[]" value="'+v["price"]+'" style="width:50px;text-align:right">&'+currency+';';
			options += '<br><strong>Conditions' +	'</strong> <a id="add_if_clause" href="javascript:add_if_clause(\''+k+'\');">+</a><ul id="clauses_sortable'+k+'">';
			if(v['clauses']){
				for(var clause in v['clauses']){
					options += add_if_clause(k, v['clauses'][clause]);
				}
			}
			options += '</ul>';
		}
		options += '</li>';
	}
	return options;
}

function add_if_clause(id, sel){
	if(sel) var c = sel;
	else var c = {price: '', cond: 1, operator: 'equal', type: 'units', mult: 'x'};
	var clause = '<li class="sortable clause" id="if_clause_'+id+'_'+ifTempID+'">';
	clause += '<img src="'+plugin_url+'/easyreservations/images/delete.png" onclick="delete_option(this);" style="float:right">';
	clause += 'If <select name="if_cond_type[]" onchange="change_operator(this);" style="width:80px">';
	clause += generateOptions({units:"Billing unit",resource:"Resource",adult:"Adults",child:"Children",arrival:"Arrival",departure:"Departure",field:"Price field"}, c['type']);
	clause += '</select>';
	var selection = c['price'];
	clause += generate_if_clause_condition(c);
	if(!isNaN(parseFloat(c['price'])) && isFinite(c['price'])) selection = 'price';
	clause += '<select name="if_cond_happens[]" onchange="clause_happens_select(jQuery(this).val(),\''+id+'\', '+ifTempID+', true)" style="width:68px">'+generateOptions({x:"--",and:"AND",or:"OR",price:"THEN"}, selection)+'</select>';
	clause += clause_happens_select(c['price'], id, ifTempID, false, c['mult']);
	clause += '<input type="hidden" name="if_option[]" value="'+id+'"></li>';
	ifTempID++;
	if(!sel){
		jQuery('#clauses_sortable'+id).append(clause).sortable();
	} else {
		return clause;
	}
}

function generate_if_clause_condition(sel){
	var clause = '';
	if(sel['type'] == 'field'){
		var select = generate_customs_select(sel['operator']);
		if(!select) clause += '<b name="if_cond[]">Add price fields first</b>';
		else {
			clause += ' <select name="if_cond_operator[]" onchange="change_condition_options(this)" title="Other price field that has to be selected">'+ select[0] + '</select><span class="delete"> and option </span>';
			clause += ' <select name="if_cond[]" title="Price fields option that has to be selected">' + generate_customs_options_select(select[1], sel['cond']) + '</select><span class="delete"> are selected </span>';
		}
	} else if(sel['type'] == 'resource'){
		clause += '<select name="if_cond_operator[]">'+generateOptions({equal:"=", notequal:"!="}, sel['operator'])+'</select> ';
		clause += '<select name="if_cond[]" title="Resource that has to be selected">'+generateOptions(resources, sel['cond'])+'</select>';
	} else {
		clause += '<select name="if_cond_operator[]">'+generateOptions({equal:"=", notequal:"!=", greater:">", greaterequal:">=",smaller:"<", smallerequal:"<="}, sel['operator'])+'</select> ';
		if(sel['type'] == 'arrival' || sel['type'] == 'departure'){
			clause += '<input type="text" name="if_cond[]" onclick="generate_datepicker(this);" value="'+sel['cond']+'" style="width:84px;text-align:center"> ';
		} else {
			clause += '<input type="text" name="if_cond[]" title="Number that has to be matched" value="'+sel['cond']+'" style="width:40px;text-align:center"> ';
		}
	}
	return clause;
}

function generate_customs_select(sel){
	var options = {};
	for(var key in all_custom_fields){
		if(all_custom_fields[key]['price']){
			if(!sel || (isNaN(parseFloat(sel)) && !isFinite(sel))) sel = key;
			options[key] = all_custom_fields[key]['title'];
		}
	}
	if(options == {}) return false;
	else return [generateOptions(options, sel), sel];
}

function generate_customs_options_select(id, sel){
	var options = {};
	options['any'] = 'Any';
	if(all_custom_fields[id]){
		for(var key in all_custom_fields[id]['options']){
			options[key] = all_custom_fields[id]['options'][key]['value'];
		}
	}
	return generateOptions(options, sel);
}

function clause_happens_select(sel, opt_id, clause_id, append, mult){
	jQuery('#if_clause_amount_'+opt_id+'_'+clause_id+',#if_clause_mult_'+opt_id+'_'+clause_id+',#delete_'+opt_id+'_'+clause_id).remove();
	var content = '';
	if(sel == 'price' || jQuery.isNumeric(sel)){
		if(sel == 'price') sel = 0;
		content += '<span class="delete" id="delete_'+opt_id+'_'+clause_id+'">Price:';
		content += '<input type="text" name="if_cond_amount[]" id="if_clause_amount_'+opt_id+'_'+clause_id+'" value="'+sel+'" style="width:55px;text-align:right">&'+currency+'; per ';
		content += '<select name="if_cond_mult[]" id="if_clause_mult_'+opt_id+'_'+clause_id+'" style="width:120px">'+generateOptions({
			x:"--",
			price_pers:"Person",
			price_adul:"Adult",
			price_child:"Children",
			price_day:"Billing unit",
			price_both:"Unit and person",
			price_day_adult:"Unit and adult",
			price_day_child:"Unit and children"
		}, mult)+'</select></span>';
	} else if(sel !== '' && sel !== 'x'){
		content = '<input type="hidden" name="if_cond_amount[]" id="if_clause_amount_'+opt_id+'_'+clause_id+'" value="0">';
		content += '<input type="hidden" name="if_cond_mult[]" id="if_clause_mult_'+opt_id+'_'+clause_id+'" value="x">';
		if(append){
			if(!added[opt_id+clause_id]) add_if_clause(opt_id, false);
			added[opt_id+clause_id] = 1;
		}
	}
	if(append) jQuery('#if_clause_'+opt_id+'_'+clause_id).append(content);
	else return content;
}

function add_new_option(){
	optTempID++;
	var html = custom_generate_option(false);
	jQuery('#options_sortable').append(html).sortable();
}

function delete_option(e){
	jQuery(e).parent().remove();
}

custom_type_select(false);
custom_field_extras();

function generate_datepicker(e){
	jQuery(e).datepicker().datepicker("show");
}

function change_operator(e){
	e = jQuery(e).parent();
	var operator = generate_if_clause_condition({price: '', cond: '', operator: 'equal', type: e.find('*[name="if_cond_type[]"]').val()});
	e.find('*[name="if_cond_operator[]"],*[name="if_cond[]"],*[id^=if_clause_amount_],*[id^=if_clause_mult_],span.delete').remove();
	e.find('*[name="if_cond_happens[]"]').prop("selectedIndex",0);
	e.find('*[name="if_cond_type[]"]').after(operator);
	jQueryTooltip();
}

function change_condition_options(e){
	e = jQuery(e).parent();
	e.find('*[name="if_cond[]"]').html(generate_customs_options_select(e.find('*[name="if_cond_operator[]"]').val()));
}

function check_checkboxes(e){
	jQuery('input[type=checkbox][name="checked[]"]:checked').each(function(box){
		if(e !== this) jQuery(this).prop("checked", false);
	});
}

jQuery('#custom_field_type').bind('change', function(){
	custom_field_value(false)
});

jQuery('#custom_price_field').bind('click', function(){
	custom_type_select(false);
	custom_field_extras(false);
	custom_generate_option(false);
	custom_field_value(false);
});

jQuery('#custom_cancel').bind('click', function(){
	jQuery('#custom_id').remove();
});

jQuery('#custom_creator').bind('submit', function(){
	jQuery('input[type=checkbox][name="checked[]"]:not(:checked)').prop("value", "0").prop("type", "hidden");
});