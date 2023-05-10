function easyreservations_send_search(){
	var loading = '<img style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading1.gif">';
	jQuery("#easy-form-loading").html(loading);

	var tsecurity = document.getElementById('easy-search-nonce').value;
	var error = 0;
	var fromh = 12*60; var fromm = 0; var toh = 12*60; var tom = 0; 

	var fromfield = document.easy_search_formular.from;
	if(fromfield){
		var from = fromfield.value;

		if(document.getElementById('date-from-hour')) fromh = parseInt(document.getElementById('date-from-hour').value) * 60;
		if(document.getElementById('date-from-min')) fromm = parseInt(document.getElementById('date-from-min').value);
		
		var fromplus = (fromh + fromm)*60;

	} else alert('no arrival field - correct that');
	
	var tofield = document.easy_search_formular.to;
	if(tofield){
		var to = tofield.value;

		if(document.getElementById('date-to-hour')) toh = parseInt(document.getElementById('date-to-hour').value) * 60;
		if(document.getElementById('date-to-min')) tom = parseInt(document.getElementById('date-to-min').value);

		var toplus = (toh + tom)*60;
	} 

	var nightsfield = document.easy_search_formular.nights;
	if(nightsfield) var nights = nightsfield.value;
	else nights = 1

	var persons = 1;
	var persfield = document.easy_search_formular.persons;
	if(persfield)  persons = persfield.value;
	
	var childs = 0;
	var childsfield = document.easy_search_formular.childs;
	if(childsfield) childs = childsfield.value;

	var theme = '';
	var themefield = document.easy_search_formular.theme;
	if(themefield) theme = themefield.value;

	var data = {
		action: 'easyreservations_send_search',
		security:tsecurity,
		from:from,
		fromplus:fromplus,
		to:to,
		toplus:toplus,
		nights:nights,
		childs:childs,
		persons:persons,
		theme:theme,
		atts:searchAtts,
		serialize:jQuery('#easy_search_formular').serialize()
	};

	if(error == 0){
		jQuery.post(easyAjax.ajaxurl , data, function(response){
			var height = jQuery("#easy_search_div").css("height");
			jQuery("#easy_search_div").after('<div id="easy-filler" style="min-height:'+height+';height:'+height+';"&nbsp;</div>')
			jQuery("#easy_search_div").css("display","none");
			jQuery("#easy_search_div").html(response);
			jQuery("#easy_search_div").fadeIn("slow");
			jQuery("#easy-filler").remove();
			jQuery("#easy-form-loading").html('');
		});
	}
}

function easyFakeButtonCheck(e,checkbox){
	if(document.getElementById(checkbox).checked == true){
		jQuery(e).removeClass('checked');
		document.getElementById(checkbox).checked = false;
	} else {
		jQuery(e).addClass('checked');
		document.getElementById(checkbox).checked = true;
	}
}

jQuery(document).ready(function(){
   if(jQuery(".easy_attr_numeric").length>0){
		jQuery(".easy_attr_numeric").keydown(function(e) {
			if(e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 27 || e.keyCode == 13 || (e.keyCode == 65 && e.ctrlKey === true) || (e.keyCode >= 35 && e.keyCode <= 39)) return;
			else if(e.shiftKey || (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105 )) {
				e.preventDefault(); 
			}
		});
	 }
});

function easyShowDescription(nr){
	jQuery('tr[id ^="resource_content_"]').fadeOut("slow");
	jQuery('#resource_content_'+nr).fadeIn("slow");
}

function easyChangeTheme(e){
	if(e.title == 'List'){
		jQuery('#easysearchtheme').val('table');
		e.title = 'Table';
		e.src = easyAjax.plugin_url+'/easyreservations/images/table.png';
	} else {
		jQuery('#easysearchtheme').val('list');
		e.title = 'List';
		e.src = easyAjax.plugin_url+'/easyreservations/images/list.png';
	}
	if(searchAtts && searchAtts['searchdirect'] == 1) easyreservations_send_search();
}