(function() {
	tinymce.PluginManager.add('easyReservations', function( editor, url ) {
		editor.addButton( 'easyReservations', {
			text: '',
			icon: true,
			image: url + "/logo.png",
			onclick: function() {
				tinymce.activeEditor.windowManager.open({
					file : url + '/tinyMCE_shortcode_add.php',
					width : 670,
					height : 420,
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			}
		});
	});
})();