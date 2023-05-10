var easyCalendars = [];
jQuery("body").on({
	click: function(){
		var split = this.id.split("-");
		if(easyCalendars[split[2]]){
			var cal = easyCalendars[split[2]];
			cal.click(this,jQuery(this).attr('date'),split[4]);
		}
	}
}, "td[date]");

jQuery('#form_room').bind("change", function(){
		var x = 0;
		for(i in easyCalendars){
			if(x > 1) return;
			easyCalendars[i].change('resource', jQuery(this).val());
			x++;
		}
});

function easyCalendar(nonce, atts, type){
	this.id = atts['id'];
	this.nonce = nonce;
	this.resource = atts['resource'];
	this.date = 0;
	this.type = type;
	this.atts = atts;

	this.clicknr = 0;
	this.cellnr = 0;
	this.calm = 0;

	this.change = change;
	this.send = send;
	this.click = click;

	easyCalendars[this.id] = this;

	function change(key, value){
		this[key] = value;
		this.send();
	}
	function send(){
		var data = {
			action: 'easyreservations_send_calendar',
			security: this.nonce,
			room: this.resource,
			date: this.date,
			where: this.type,
			atts: this.atts
		};
		if(this.persons) data.persons = this.persons
		if(this.childs) data.childs = this.childs
		if(this.reservated) data.reservated = this.reservated
		var id = this.id;
		jQuery.post(easyAjax.ajaxurl , data, function(response) {
			jQuery("#CalendarFormular-"+id+" #showCalender").html(response);
		});
	}
	function click(cell, date, m){
		jQuery("#CalendarFormular-"+this.id+' .reqdisabled').removeClass('reqdisabled');
		if(this.clicknr == 2 || (atts['select'] == 1 && this.clicknr == 1)){
			jQuery("#CalendarFormular-"+this.id+" .calendar-cell-selected").removeClass("calendar-cell-selected");
			this.clicknr = 0;
		}

		if(this.clicknr == 1){
			jQuery("#CalendarFormular-"+this.id+' .reqstartdisabled').addClass('reqdisabled');
			if(jQuery(cell).hasClass('reqenddisabled')) return false;
			this.cellnr = parseFloat(this.cellnr);
			this.calm = parseFloat(this.calm);
			var axis = parseFloat(cell.axis);
			if(this.calm != m) axis = 31;
			if(!document.getElementById('easy-cal-' + this.id + '-'+ this.cellnr + '-' + this.calm)) this.cellnr = 1;
			if(this.cellnr <= axis && parseFloat(m) >= this.calm){
				for(var i = this.cellnr; i<=axis; i++){
					var element = '#easy-cal-' + this.id + '-'+ i + '-' + this.calm;
					if(i != axis && jQuery(element).hasClass('calendar-cell-full') == true){
						jQuery("#CalendarFormular-"+this.id+" .calendar-cell-selected").removeClass("calendar-cell-selected");
						jQuery('#easy-form-to, #easy-search-to').val('');
						jQuery(cell.parentNode.parentNode.parentNode.parentNode).addClass("calendar-full");
						break;
					}
					jQuery(element).addClass("calendar-cell-selected");
					if(i == 31 && this.calm != m){
						i = 0;
						this.calm = this.calm + 1;
						if(this.calm == m) axis = parseFloat(cell.axis);
					}
				}
				jQuery('#easy-form-to,#easy-widget-datepicker-to,#easy-search-to').val(date);
				if(document.getElementById('easy-form-units') && document.getElementById('easy-form-from')){
					var instance = jQuery( '#easy-form-from' ).data( "datepicker" );
					if(instance){
						var dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, document.getElementById('easy-form-from').value, instance.settings );
						var dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, date, instance.settings );
						var diff = Math.abs(dateanf - dateend)/1000;
						var interval = 86400;
						var interval_array = eval("(" + easyAjax.interval + ")");
						if(interval_array[this.resource]) interval = interval_array[this.resource];
						jQuery('#easy-form-units').val(Math.ceil(diff/interval));
					}
				}
				if(window.easyreservations_send_price) easyreservations_send_price('easyFrontendFormular');
				if(window.easyreservations_send_validate) easyreservations_send_validate(false, 'easyFrontendFormular');
				if(window.easyreservations_send_search) easyreservations_send_search();
				this.clicknr = 2;
			} else {
				this.clicknr = 2;
				this.calm = 0;
				jQuery("#CalendarFormular-"+this.id+" .calendar-cell-selected").removeClass("calendar-cell-selected");
			}
		}
		if(this.clicknr == 0){
			if(jQuery(cell).hasClass('reqenddisabled')) return false;
			jQuery("#CalendarFormular-"+this.id+' .reqenddisabled').addClass('reqdisabled');
			jQuery(cell.parentNode.parentNode.parentNode.parentNode).removeClass("calendar-full");
			jQuery(cell).addClass("calendar-cell-selected");
			jQuery('#easy-form-from,#easy-widget-datepicker-from, #easy-search-from').val(date);
			this.calm = m;
			this.cellnr = cell.axis;
			this.clicknr = 1;
			if(atts['select'] == 1 && window.easyreservations_send_price) easyreservations_send_price('easyFrontendFormular');
		}
	}
	this.send();
}