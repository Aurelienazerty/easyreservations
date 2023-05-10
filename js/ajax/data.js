function easy_get_data(form){
    var tnights = 0, nights = '', to = '', toplus = 0, fromplus = 0, childs = 0, persons = 1, captcha = 'x!', captcha_prefix = '', tom = 0, toh = '', fromm = 0, fromh = '', theid = '';

    if(jQuery('#'+form+' input[name="from"]').length > 0){
        var from = jQuery('#'+form+' input[name="from"]').val();
        if(jQuery('#'+form+' #date-from-hour').length > 0) fromh = parseInt(jQuery('#'+form+' #date-from-hour').val()) * 60;
        if(jQuery('#'+form+' #date-from-min').length > 0) fromm = parseInt(jQuery('#'+form+' #date-from-min').val());
        if(fromh != '') fromplus = (fromh + fromm)*60;
    } else alert('no arrival field - correct that');

    if(jQuery('#'+form+' input[name="to"]').length > 0){
        to = jQuery('#'+form+' input[name="to"]').val()
    } else if(jQuery('#'+form+' *[name="nights"]').length > 0){
        nights = jQuery('#'+form+' *[name="nights"]').val();
        tnights = nights;
    }

    if(jQuery('#'+form+' #date-to-hour').length > 0) toh = parseInt(jQuery('#'+form+' #date-to-hour').val()) * 60;
    if(jQuery('#'+form+' #date-to-min').length > 0) tom = parseInt(jQuery('#'+form+' #date-to-min').val());
    if(toh != '') toplus = (toh + tom)*60;

    if(jQuery('#'+form+' *[name="easyroom"]').length > 0) var room = jQuery('#'+form+' *[name="easyroom"]').val();
    else alert('no room field - correct that');

    var instance = jQuery('#'+form+' input[name="from"]').data( "datepicker" );
    if(instance && to != ''){
        var dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, from, instance.settings );
        var dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, to, instance.settings );
        var difference_ms = Math.abs(dateanf - dateend);
        var diff = difference_ms/1000;
        diff += toplus;
        diff -= fromplus;
        var interval_array = eval("(" + easyAjax.interval + ")");
        var interval = interval_array[room];
        tnights = Math.ceil(diff/interval);
    }
    if(jQuery('#'+form+' *[name="childs"]').length > 0) childs = parseFloat(jQuery('#'+form+' *[name="childs"]').val());
    if(jQuery('#'+form+' *[name="persons"]').length > 0) persons = parseFloat(jQuery('#'+form+' *[name="persons"]').val());
    if(jQuery('#'+form+' input[name="email"]').length > 0) var email = jQuery('#'+form+' input[name="email"]').val();
    else alert('no email field - correct that');
    if(jQuery('#'+form+' *[name="captcha_value"]').length > 0) captcha = jQuery('#'+form+' *[name="captcha_value"]').val();
    if(jQuery('#'+form+' *[name="captcha_prefix"]').length > 0) captcha_prefix = jQuery('#'+form+' *[name="captcha_prefix"]').val();
    if(jQuery('#'+form+' input[name="thename"]').length > 0) var thename = jQuery('#'+form+' input[name="thename"]').val();
    else alert('no name field - correct that');

    return {
        security:jQuery('#'+form+' input[name="pricenonce"]').val(),
        captcha:captcha,
        captcha_prefix:captcha_prefix,
        from:from,
        fromplus:fromplus,
        to:to,
        toplus:toplus,
        thename:thename,
        nights:nights,
        tnights:tnights,
        childs:childs,
        persons:persons,
        room: room,
        email:email
    };
}