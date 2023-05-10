(function(jQuery){
	var initLayout = function() {
		var hash = window.location.hash.replace('#', '');
		var currentTab = jQuery('ul.navigationTabs a')
							.bind('click', showTab)
							.filter('a[rel=' + hash + ']');
		if (currentTab.size() == 0) {
			currentTab = jQuery('ul.navigationTabs a:first');
		}
		showTab.apply(currentTab.get(0));
		jQuery('#colorpickerHolder').ColorPicker({flat: true});
		jQuery('#colorpickerHolder2').ColorPicker({
			flat: true,
			color: '#00ff00',
			onSubmit: function(hsb, hex, rgb) {
				jQuery('#colorSelector2 div').css('backgroundColor', '#' + hex);
			}
		});
		jQuery('#colorpickerHolder2>div').css('position', 'absolute');
		var widt = false;
		jQuery('#colorSelector2').bind('click', function() {
			jQuery('#colorpickerHolder2').stop().animate({height: widt ? 0 : 173}, 500);
			widt = !widt;
		});
		jQuery('#colorpickerField1, #colorpickerField2, #colorpickerField3').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				jQuery(el).val(hex);
				jQuery(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				jQuery(this).ColorPickerSetColor(this.value);
			}
		})
		.bind('keyup', function(){
			jQuery(this).ColorPickerSetColor(this.value);
		});
		jQuery('#colorSelector').ColorPicker({
			color: '#0000ff',
			onShow: function (colpkr) {
				jQuery(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				jQuery(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				jQuery('#colorSelector div').css('backgroundColor', '#' + hex);
			}
		});
	};
	
	var showTab = function(e) {
		var tabIndex = jQuery('ul.navigationTabs a')
							.removeClass('active')
							.index(this);
		jQuery(this)
			.addClass('active')
			.blur();
		jQuery('div.tab')
			.hide()
				.eq(tabIndex)
				.show();
	};
	
	EYE.register(initLayout, 'init');
})(jQuery)