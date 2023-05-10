window.easyLastPrice = 0;
function easyreservations_send_price(form){
	if(!document.easyFrontendFormular) return false;
	var error = 0, customPrices = 0, new_custom = {};
	jQuery("#showPrice").html('<img style="vertical-align:text-bottom" src="' + easyAjax.plugin_url + '/easyreservations/images/loading.gif">');

	var reserved = '', priceper = '';
	if(jQuery('#'+form+' input[name="reserved"]').length > 0) reserved = jQuery('#'+form+' input[name="reserved"]').val();
	if(jQuery('#'+form+' input[name="easypriceper"]').length > 0) priceper = jQuery('#'+form+' input[name="easypriceper"]').val();

	var data = easy_get_data(form);
	if(data['from'] == "") return false;
  if(jQuery('#'+form+' input[name^="coupon"]').length > 0) data['coupon'] = jQuery('#'+form+' input[name^="coupon"]').val();
  data['action'] = 'easyreservations_send_price';
  data['priceper'] = priceper;
  data['reserved'] = reserved;
  jQuery('[id^="custom_price"]:checked,select[id^="custom_price"]').each(function(){
    var explodenormalprice = '';
    var Type = this.type;
    if(Type == "select-one"){
      explodenormalprice = this.value.split(':');
    } else if(Type == "radio" &&  this.checked != undefined && this.checked){
      explodenormalprice = this.value.split(':');
    } else if(Type == "checkbox" &&  this.checked){
      explodenormalprice = this.value.split(':');
    } else if(Type == "hidden"){
      explodenormalprice = this.value.split(':');
    }
    if(explodenormalprice !== ''){
      var price = 0;
      var fieldprice = explodenormalprice[1];
      fieldprice = fakeIfStatements(fieldprice, data['persons'], data['childs'], data['tnights'], data['room']);
      if(fieldprice === false) fieldprice = explodenormalprice[1];
      if(this.className == 'pp'){
        price = fieldprice * (data['persons'] + data['childs']);
      } else if(this.className == 'pa'){
	      price = fieldprice * data['persons'];
      } else if(this.className == 'pan'){
	      price = fieldprice * data['persons'] * data['tnights'];
      } else if(this.className == 'pc'){
        price = fieldprice * data['childs'];
      } else if(this.className == 'pcn'){
        price = fieldprice * data['childs'] * data['tnights'];
      } else if(this.className == 'pn'){
        price = fieldprice * data['tnights'];
      } else if(this.className == 'pb'){
        price = fieldprice * (data['persons'] + data['childs']) * data['tnights'];
      } else {
        price = fieldprice;
      }
      if(!isNaN(parseFloat(price)) && isFinite(price)) customPrices += parseFloat(price);
    }
  });

	jQuery("input[id^='easy-new-custom-']:radio:checked, select[id^='easy-new-custom-'],input[id^='easy-new-custom-']:checkbox:checked").each ( function (i){
		new_custom[jQuery(this).attr('id').replace('easy-new-custom-', '')] = jQuery(this).val();
	});

	data['new_custom'] = new_custom;
	data['customp'] = customPrices;

	if(error == 0){
		jQuery.post(easyAjax.ajaxurl , data, function(response) {
			response = jQuery.parseJSON(response);
			jQuery("#showPrice").html(response[0]);
			window.easyLastPrice = parseFloat(response[1]);
			return false;
		});
	}
}