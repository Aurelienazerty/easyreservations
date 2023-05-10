jQuery(document).ready(function(){
	jQuery('form').bind('change',function() {
		var submit = jQuery(this).find('input[type="submit"]');
		if(submit.length > 0){
			submit.addClass('savemark');
			easyReservationsBlinkButton(submit, this);
		} else {
			var button = jQuery(this).find('input[type="button"]');
			if(button.length > 0){
				button.addClass('savemark');
				easyReservationsBlinkButton(button, this);
			}
		}
	});
});

var button = new Array();

function easyReservationsBlinkButton(e, p){
	var count = jQuery.inArray(jQuery(p).attr("id"), button);
	if(count == -1 ){
		button.push(jQuery(p).attr("id"));
		setInterval(function(){easyReservationsChangeClass(e)},1500);
	}
	return true;
}

function easyReservationsChangeClass(e){
	if(e.hasClass('savemark')){
		e.removeClass('savemark');
	} else {
		e.addClass('savemark');
	}
	return true;
}

function generateOptions(options, sel){
	var value = '';
	if(typeof options == "string"){
		var split = options.split('-');
		for(var k = split[0]; k <= split[1]; k++){
			var selected = '';
			if(sel && sel == k) selected = 'selected="selected"';
			value += '<option value="'+k+'" '+selected+'>'+k+'</option>';
		}
	} else {
		jQuery.each(options, function(ok,ov){
			var selected = '';
			if(sel && sel == ok) selected = ' selected="selected"';
			value += '<option value="'+ok+'"'+selected+'>'+ov+'</option>';
		});
	}
	return value;
}

function jQueryTooltip(){
	jQuery('#jqueryTooltip').destroy;
	var jqueryTooltip = jQuery('<div id="jqueryTooltip"></div>');
	jQuery('body').append(jqueryTooltip);
	jQuery('*[title][title!=""]').hover(function(e) {
		var ae = jQuery(this);
		var title = ae.attr('title');
		ae.attr('title', '');
		ae.data('titleText', title);
		jqueryTooltip.html(title);
		var _t = e.pageY + 20;
		var _l = e.pageX + 20;
		jqueryTooltip.css({ 'top':_t, 'left':_l });
		jqueryTooltip.show(0);
	}, function() {
		var ae = jQuery(this);
		jqueryTooltip.hide(0);
		var title = ae.data('titleText');
		ae.attr('title', title);
	}).mousemove(function(e) {
			var _t = e.pageY + 20;
			var _l = e.pageX + 20;
			jqueryTooltip.css({ 'top':_t, 'left':_l });
	});
}