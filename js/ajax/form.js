function fakeIfStatements(fieldprice, persons, childs, nights, room){
	var myregexp = /;/i;
	var match = myregexp.exec(fieldprice);
	if(match != null){
		var fieldpriceexplode = fieldprice.split(/-(?=[^\}]*(?:\{|$))/);
		var thetype = fieldpriceexplode[1];
		var explstatements = fieldpriceexplode[0].split(/;(?=[^\}]*(?:\{|$))/);
		for(var i in explstatements){
			var explif = explstatements[i].split(/\>(?=[^\}]*(?:\{|$))/);
			if(thetype == 'pers'){
				if((parseFloat(persons)+parseFloat(childs)) >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
        }
			} else if(thetype == 'child'){
				if(childs >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				} 
			} else if(thetype == 'res'){
				if(room == parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				} 
			} else if(thetype == 'both'){
				if(((parseFloat(persons)+parseFloat(childs))*nights) >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			} else if(thetype == 'adul' || thetype == 'adults' || thetype == 'adult'){
				if(persons >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			} else if(thetype == 'night' || thetype == 'times'){
				if(nights >= parseFloat(explif[0])){
					fieldprice = fakeIfStatements(explif[1].substr(1, explif[1].length-2), persons, childs, nights, room);
					if(fieldprice === false) fieldprice = explif[1];
				}
			}
		}
		return fieldprice.replace('+','-');
	}
	return false;
}

var easyReservationIDs = new Array();
var easyReservationDatas = new Array();
var easyReservationsPrice = new Array();
var easyReservationEdit = false;
var easyReservationFSubmit = false;
var easyEffectSavere = true;
var easyDoAdd = true;
var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");

function easyOverlayDimm(close){
	if(close && close == 1){
		jQuery('#easyFormOverlay,#easyFormInnerlay').fadeOut("slow", function(){
			jQuery('#easyFormOverlay,#easyFormInnerlay').remove();
		});
	} else {
		if(!document.getElementById('easyFormOverlay')){
			var form = jQuery('#easyFrontendFormular');
			var height = form.outerHeight();
			var width = form.outerWidth();
			var bgc = easyGetBackgroundColor(document.getElementById('easyFrontendFormular'));
			if(bgc  && bgc != '') var bgcolor = bgc;
			else var bgcolor = easyReservationAtts['bg'];
			form.before('<div id="easyFormOverlay" class="'+easyReservationAtts['multiple']+' easyloading" style="height:'+height+'px;width:'+width+'px;background-color:'+bgcolor+';"></div>');
			jQuery("#easyFormOverlay").fadeIn("fast");
		} else {
			easyOverlayDimm(1);
		}
	}
}

jQuery(document).keyup(function(e){if(e.keyCode == 116) easyCancelSubmit();});
jQuery('#easyFormOverlay').keyup(function(e){if(e.keyCode == 27) easyCancelSubmit();});
jQuery('#easyFrontendFormular input[type="submit"]').bind("click",function(){
	jQuery('#easyFrontendFormular *[disabled="disabled"]').attr('disabled', false).attr('temp', 'disabled');
});
//window.onbeforeunload = function(e){}
jQuery( window ).bind('beforeunload', function() {
	if (!easyReservationFSubmit) easyCancelSubmit();
});

function easyInnerlay(content,add){
	if(!add){
		add = true;
		if(easyReservationAtts['cancel'] != ""){
			jQuery('#easybackbutton').html('<input type="button" class="easy-button back" value="'+easyReservationAtts['cancel']+'" onclick="easyOverlayDimm(0);easyInnerlay(1,342);">');
		}
	} else {
		easyDoAdd = false;
		add  = false;
		window.location.hash = 'easyFormOverlay';
	}
	if(document.getElementById('easyFormOverlay')){
		if(easyReservationEdit) easyEdit(easyReservationDatas.length-1, false);
		var form = jQuery('#easyFrontendFormular');
		var width = form.outerWidth();
		width = parseFloat(width) - 50;
		if(content == 1){
			content = easyInnerlayTemplate.replace(/\\/g, "");
			var reservations = "";
			var allprice = 0;
			var thedatas = easyReservationDatas;
			var theprices = easyReservationsPrice;
			if(add){
				if(window.easyLastPrice && window.easyLastPrice > 0) var easyLastPrice= window.easyLastPrice;
				else var easyLastPrice = 0;
				if(!easyReservationEdit){
          var ndata = new Array();

					jQuery('#easyFrontendFormular *[disabled="disabled"]').attr('disabled', false).attr('temp', 'disabled');
					jQuery.each(jQuery('#easyFrontendFormular').serializeArray(), function(i, field){
            ndata[field.name] = field.value;
          });
					jQuery('#easyFrontendFormular *[temp="disabled"]').attr('disabled', 'disabled').attr('temp', false);

					theprices.push(easyLastPrice);
          thedatas.push(ndata);
        }
      }
			for(i in thedatas){
				var datas = thedatas[i];
        if(datas && datas['easyroom']){
          if(typeof(thedatas[i]) == 'string') datas = easyUnserialize(thedatas[i]);
          if(i == thedatas.length-1) reservations += '<tr class="active">';
          else reservations += '<tr>';
          reservations+='<td>'+datas['from'];
          if(datas['date-from-hour']){
            reservations+= ' ';
            if(datas['date-from-hour'] < 10) reservations += '0';
            reservations+= datas['date-from-hour']+':';
            if(datas['date-from-min']) {
              if(datas['date-from-min'] < 10) reservations += '0';
              reservations+= datas['date-from-min'];
            } else reservations+='00';
          }
          if(datas['to']){
            reservations+= '<br>'+datas['to'];
            if(datas['date-to-hour']){
              reservations+=' ';
              if(datas['date-to-hour'] < 10) reservations += '0';
              reservations+=datas['date-to-hour']+':';
              if(datas['date-to-min']){
                if(datas['date-to-min'] < 10) reservations += '0';
                reservations+=datas['date-to-min'];
              } else reservations+='00';
            }
          }
          var res_info = all_resoures_array[datas['easyroom']]['post_title'];
          reservations+='</td><td>'+res_info+'</td>';
          if(easyReservationAtts['pers'] && easyReservationAtts['pers'] == 1){
            reservations+='<td>'+datas['persons'];
            if(datas['childs']) reservations+='+'+datas['childs'];
            reservations+='</td>';
          }
          allprice += parseFloat(theprices[i]);
          reservations+='<td>'+easy_money_format(parseFloat(theprices[i]))+'</td>';
          if(i == thedatas.length-1){
            var onclickc = 'easyOverlayDimm(1);';
            var onclicke = 'easyOverlayDimm(1);';
          } else {
            var onclickc = 'easyCancelSubmit('+easyReservationIDs[i]+', this);';
            var onclicke = 'easyEdit('+i+',true);';
          }
          reservations+='<td><img src="'+easyAjax.plugin_url+'/easyreservations/images/edit.png" onclick="'+onclicke+'" title="edit"> <img src="'+easyAjax.plugin_url+'/easyreservations/images/delete.png" onclick="'+onclickc+'" title="cancel"></td>';
          reservations+='</tr>';
        }
      }
			reservations+='<tr><td></td><td></td>';
			if(easyReservationAtts['pers'] && easyReservationAtts['pers'] == 1) reservations+= '<td></td>';
			reservations+='<td style="text-align:right">'+easy_money_format(allprice)+'</td><td></td></tr>';
			thedatas = undefined;
			if(add){
				easyReservationDatas.splice(-1,1);
				easyReservationsPrice.splice(-1,1);
			}
		}

		easyEffectSavere = false;
		var thecon = '<div id="easyFormInnerlay" class="'+easyReservationAtts['multiple']+'" style="width:'+width+'px;">'+content+'</div>';
		var test = jQuery('#easyFormOverlay').after(thecon);
		if(reservations) jQuery('#easy_overlay_tbody').html(reservations);
		jQuery('#easyFormInnerlay').focus();
		jQuery("#easyFormInnerlay").fadeIn("fast");
		jQuery("#easyFormInnerlay").css("display", "inline-block");
		jQuery("#easyFormOverlay").removeClass('easyloading');
		window.location.hash = 'easyFormInnerlay';
		easyReservationEdit = false;
	}
}

function easyFormSubmit(submit){
	var data = {
		atts: easyReservationAtts,
		ids: easyReservationIDs,
		action: 'easyreservations_send_form'
	};
	if(submit  && submit == 1){
		jQuery('#easyFormInnerlay').fadeOut("slow", function(){
			jQuery('#easyFormOverlay').addClass('easyloading');
		});
		jQuery('#easyFormInnerlay').remove();
		data['submit'] = 1;
	}

	if(easyDoAdd){
		jQuery('#easyFrontendFormular *[disabled="disabled"]').attr('disabled', false).attr('temp', 'disabled');
		var array = jQuery('#easyFrontendFormular').serializeArray();
		jQuery('#easyFrontendFormular *[temp="disabled"]').attr('disabled', 'disabled').attr('temp', false);

		jQuery.each(array, function(i, field){
			data[field.name] = field.value;
		});
	} else {
		data['easynonce'] = jQuery('#easyFrontendFormular input[name=easynonce]').val();
	}
	if(easyReservationEdit) data['edit'] = easyReservationEdit;
	jQuery.post(easyDate.ajaxurl , data, function(response){
		if(response[0] == '['){
			response = jQuery.parseJSON(response);
			if(easyReservationEdit){
				for(i in easyReservationIDs){
					if(easyReservationIDs[i] == easyReservationEdit) var thei = i;
				}
				easyReservationDatas[thei] = jQuery('#easyFrontendFormular').serialize();
				easyReservationsPrice[thei] = response[1];
				easyInnerlay(1);
			} else {
				if(response[0]){
					easyReservationIDs.push(response[0]);
					easyReservationDatas.push(data);
					easyReservationsPrice.push(response[1]);
				}
				if(submit  && submit == 1){
          if(window.$fragment_refresh) jQuery.ajax( $fragment_refresh );
					easyReservationIDs = new Array();
					easyReservationDatas = new Array();
					easyReservationsPrice = new Array();
					easyReservationEdit = false;
					easyEffectSavere = true;
					jQuery('#easybackbutton').html('');
					easyInnerlay(response[2]);
				} else if(submit) submit();
			}
		} else {
		  document.getElementById('easy-show-error').innerHTML = response;
			jQuery("#easy-show-error-div").removeClass('hide-it');
			easyOverlayDimm(1);
		}
	});
}
function easyEdit(i,dimm){
	if(dimm && dimm !== false){
		if(!easyLastPrice) var easyLastPrice = 0;
		easyReservationDatas.push(jQuery('#easyFrontendFormular').serialize());
		easyReservationsPrice.push(easyLastPrice);
		easyReservationEdit = easyReservationIDs[i];
	}
	var data = easyReservationDatas[i];
	if(data){
		data = easyUnserialize(data);
		jQuery.each(data, function (name,value) {
			jQuery("input[name='"+name+"'],select[name='"+name+"']").each(function() {
				switch (this.nodeName.toLowerCase()){
					case "input":
						switch (this.type){
							case "radio":
							case "checkbox":
								if(this.value && this.value==value) jQuery(this).click();
								break;
							default:
								jQuery(this).val(value);
								break;
						}
						break;
					case "select":
						jQuery("option",this).each(function(){
							if(this.value&&this.value==value) this.selected=true;
						});
						break;
				}
			});
		});
		if(dimm) easyOverlayDimm(1);	
	} else alert('Error #3243 - please report in forum.');
}

function easyCancelSubmit(single,ele){
	if(ele){
		jQuery(ele).closest('tr').fadeOut("slow", function(){jQuery(ele).closest('tr').remove(); });	
	} else {
		jQuery('#easyFormInnerlay').fadeOut("slow", function(){
			jQuery('#easyFormOverlay').addClass('easyloading');
			jQuery('#easyFormInnerlay').remove();
		});
	}
	var ids = '';
	if(easyReservationIDs && easyReservationIDs.length > 0){
		for(i in easyReservationIDs){
			ids += easyReservationIDs[i]+',';
		}
		if(ids != ''){
			var cancel = '';
			if(single){
				cancel = '&cancel='+single;
			} else {
				jQuery('#easybackbutton').html('');
			}

			jQuery.post(easyDate.ajaxurl , 'delete='+ids+'&action=easyreservations_send_form'+cancel, function(response){

			});
		}
	}
	easyReservationIDs = new Array();
	easyReservationDatas = new Array();
	easyReservationsPrice = new Array();
	if(!ele) easyOverlayDimm(1);
}

function easyAddAnother(){
	easyDoAdd = true;
	jQuery('#easyFormInnerlay').fadeOut("slow", function(){
		jQuery('#easyFormOverlay').addClass('easyloading');
		jQuery('#easyFormInnerlay').remove();
	});
	easyFormSubmit(easyAddAnotherCallback);
}

function easyAddAnotherCallback(){
	if(easyReservationAtts['reset'] == 1){
		var name = jQuery('#easy-form-thename').val();
		var email = jQuery('#easy-form-email').val();
		var country = jQuery('#easy-form-country').val();

		var array = {};
		jQuery('input[type="text"][name^="easy-custom-"], textarea[name^="easy-custom-"]').each(function(key, value){
			array[jQuery(this).attr('id')] = jQuery(this).val();
		});

		document.getElementById('easyFrontendFormular').reset();

		for ( var key in array ) {
			jQuery('#'+key).val(array[key]);
		}

		jQuery('#easy-form-thename').val(name);
		jQuery('#easy-form-email').val(email);
		jQuery('#easy-form-country').val(country);
	}
	easyOverlayDimm(1);
}

function easyUnserialize(serializedString){
	var str = decodeURI(serializedString);
	var pairs = str.split('&');
	var obj = {}, p, idx, val;
	for(var i=0, n=pairs.length; i < n; i++){
		p = pairs[i].split('=');
		idx = p[0];
		if(idx.indexOf("[]") == (idx.length - 2)){
			var ind = idx.substring(0, idx.length-2)
			if(obj[ind] === undefined) obj[ind] = [];
			obj[ind].push(easyParseValue(unescape(p[1])));
		} else {
			obj[idx] = easyParseValue(unescape(p[1]));
		}
	}
	return obj;
}

function easyParseValue(strVal) {
	return ( strVal.match(/^[0-9]+$/) ) ? parseInt(strVal) : (strVal == 'true') ? true : (strVal == 'false') ? false : strVal.replace(/[+]/g, " ");
}

function easyGetBackgroundColor(element){
	element = element.parentNode;
	if(element){
		var bgc = easyrgb2hex(jQuery(element).css('backgroundColor'));
		if(bgc && bgc != '') return bgc;
		else {
			if(element === document.body) return false;
			return easyGetBackgroundColor(element);
		}
	} else return false;
}

function easyrgb2hex(rgb){
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	if(rgb) return "#" + easyhex(rgb[1]) + easyhex(rgb[2]) + easyhex(rgb[3]);
	else return false;
}

function easyhex(x){
	return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}

function easy_money_format(amount){
	var currency_settings = easyDate['currency'];
	if(currency_settings instanceof Array || currency_settings instanceof Object) var bla = 1;
	else {
		var sign = currency_settings;
		currency_settings = [];currency_settings['sign'] = sign;currency_settings['divider1'] = '.';currency_settings['divider2'] = ',';currency_settings['decimal'] = 1;currency_settings['place'] = 0;currency_settings['whitespace'] = 1;
	}
	var add = '';
	if(amount < 0){
		amount = amount.substr(1);
		add = '-';
	}
	amount = parseFloat(amount);
	var dig = 0;
	if(currency_settings['decimal'] == 1) dig = 2;
	var money = add+easy_number_format(amount, dig, currency_settings['divider2'],currency_settings['divider1']);
	var white = '';
	if(currency_settings['whitespace'] == 1) white = ' ';
	if(currency_settings['place'] == 0){
		return money+white+'&'+currency_settings['sign']+';';
	} else return '&'+currency_settings['sign']+';'+white+money;
}

function easy_number_format(number, decimals, dec_point, thousands_sep){
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function(n, prec){
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if(s[0].length > 3) s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	if((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}
