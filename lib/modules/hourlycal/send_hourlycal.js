var easyHourlyCalendars = [];

jQuery('#form_room').change(function() {
	for(key in easyHourlyCalendars){
		easyHourlyCalendars[key].resource = jQuery('#form_room').val();
		easyHourlyCalendars[key].send()
	}
});

jQuery("body").on({
	click: function(){
		var split = this.id.split("-");
		if(easyHourlyCalendars[split[2]]){
			var cal = easyHourlyCalendars[split[2]];
			var date = jQuery(this).attr('date').split('!');
			cal.click(this,date[0],split[3],date[1]);
		}
	}
}, "td[date]");

function easyHourlyCalendar(nonce, atts){
	this.id = atts['id'];
	this.nonce = nonce;
	this.resource = atts['standard'];
	this.date = 0;
	this.atts = atts;

	this.hclicknr = 0;
	this.hclickfirst = 0;
	this.firstdate = 0;

	this.change = change;
	this.send = send;
	this.click = click;

	easyHourlyCalendars[this.id] = this;

	function change(key, value){
		this[key] = value;
		this.send();
	}
	function send(){
		var data = {
			action: 'easyreservations_send_hourlycalendar',
			security: this.nonce,
			room: this.resource,
			date: this.date,
			atts: this.atts
		};
		var id = this.id;
		jQuery.post(easyAjax.ajaxurl , data, function(response) {
			jQuery("#HourlyCalendarFormular-" + id +" #showHourlyCalendar").html(response);
		});
	}
	function click(cell, date, w, h){
		if(this.hclicknr == 2 || (this.atts['select'] == 1 && this.hclicknr == 1)){
			jQuery("#HourlyCalendarFormular-"+this.id+" .hcalendar-cell-selected").removeClass("hcalendar-cell-selected");
			this.hclicknr = 0;
		}
		if(this.hclicknr == 1){
			this.hclickfirst = parseFloat(this.hclickfirst);
			if(this.hclickfirst <= w){
				for(var i=this.hclickfirst; i<=w; i++){
					var element = '#easy-hcalcell-'+this.id+'-'+ i;
					if( i != w && jQuery(element).hasClass('hcalendar-cell-full') == true){
						jQuery("#HourlyCalendarFormular-"+this.id+" .hcalendar-cell-selected").removeClass("hcalendar-cell-selected");
						if(document.getElementById('easy-form-to')) document.getElementById('easy-form-to').value = '';
						jQuery(cell.parentNode.parentNode.parentNode.parentNode).addClass("hcalendar-full");
						break;
					}
					jQuery(element).addClass("hcalendar-cell-selected");
				}
				if(this.atts['select'] == 2){
					h++;
				}
				if(this.firstdate && this.firstdate[0] == date && this.firstdate[1] == h){
					if(h == "23") h = 0;
					else h++;
				}
				jQuery('#easy-form-to,#easy-widget-datepicker-to').val(date);
				jQuery('#date-to-hour,#easy-widget-date-to-hour').val(h);
				if(document.getElementById('easy-form-units') && document.getElementById('easy-form-from')){
					var instance = jQuery( '#easy-form-from' ).data( "datepicker" );
					if(instance){
						var dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, document.getElementById('easy-form-from').value, instance.settings );
						var dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, date, instance.settings );
						var difference_ms = Math.abs(dateanf - dateend);
						var interval = 86400;
						var interval_array = eval("(" + easyAjax.interval + ")");
						if(interval_array[this.resource]) interval = interval_array[this.resource];
						jQuery('#easy-form-units').val(Math.ceil((difference_ms/1000)/interval));
					}
				}
				if(window.easyreservations_send_price) easyreservations_send_price('easyFrontendFormular');
				if(window.easyreservations_send_validate) easyreservations_send_validate(false, 'easyFrontendFormular');
				if(window.easyreservations_send_search) easyreservations_send_search();

				this.hclicknr = 2;
			} else {
				this.hclicknr = 0;
				jQuery("#HourlyCalendarFormular-"+this.id+" .hcalendar-cell-selected").removeClass("hcalendar-cell-selected");
			}
		}
		if(this.hclicknr == 0){
			this.hclickfirst = w;
			jQuery(cell.parentNode.parentNode.parentNode.parentNode).removeClass("hcalendar-full");
			jQuery(cell).addClass("hcalendar-cell-selected");
			jQuery('#easy-form-from,#easy-widget-datepicker-from').val(date);
			jQuery('#date-from-hour,#easy-widget-date-from-hour').val(h);
			this.firstdate = [date,h]
			this.hclicknr = 1;
		}
	}
	this.send();
}