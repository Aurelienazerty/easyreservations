var easyTimeOffset = (easy_both['time'] * 1000) - new Date().getTime();
function easyFormatDate(timestamp){
  if(timestamp < 1262300400000) timestamp = timestamp*1000;
  var format = easy_both['date_format'];
  var jsDate = easyTimestampToDate(timestamp);
  var year = jsDate.getYear();
  if (year < 999) year += 1900;
  var month = easyAddZero(jsDate.getMonth()+1);
  var day = easyAddZero(jsDate.getDate());
  var hour = easyAddZero(jsDate.getHours());
  var minute = easyAddZero(jsDate.getMinutes());

  format = format.replace("Y", year);
  format = format.replace("m", month);
  format = format.replace("d", day);
  format = format.replace("H", hour);
  format = format.replace("i", minute);

  return format
}

function easyDateToStamp(datestring){
  var offset = new Date().getTimezoneOffset();
  return new Date(new Date(datestring).getTime()+ parseFloat(easy_both['offset']*1000) + (offset * 60 * 1000).getTime());
}

function easyTimestampToDate(timestamp){
  var offset = new Date().getTimezoneOffset();
  return new Date(timestamp + parseFloat(easy_both['offset']*1000) + (offset * 60 * 1000));
}

function easyAddZero(nr){
  if(nr < 10) nr = '0'+nr;
  return nr;
}
function easyInArray(array, needle){
	if(array){
		for(var i = 0; i < array.length; i++){
			if(array[i] == needle) return true;
		}
	}
	return false;
}
function changePayPalAmount(place){
	var price = easyStartPrice;
	if(place == 'perc'){
		document.getElementById('easy_radio_perc').checked = true;
		var perc = document.getElementById('easy_deposit_perc').value;
		if(perc.substr(perc.length - 1) == '%'){
			price = easyStartPrice / 100 * parseFloat(perc.substr(0,perc.length - 1));
		} else price = perc;
	} else if(place == 'own'){
		document.getElementById('easy_radio_own').checked = true;
		var price = document.getElementById('easy_deposit_own').value;
	} else if(place == 'full'){
		document.getElementById('easy_radio_full').checked = true;
		var price = easyStartPrice;
	}
	if(price > 0){
		price = Math.round(price*Math.pow(10,2))/Math.pow(10,2);
		easy_set_deposit_amount(price);
		jQuery('script[data-amount]').attr('data-amount', price*100);
	}
}