<?php
	function easyreservations_widgets_tutorial() {
		$handler = array('div[id*="easyreservations_form_widget"] input[name*="title"]', 'div[id*="easyreservations_form_widget"] textarea[name*="form_editor"]', 'div[id*="easyreservations_form_widget"] input[name*="form_url"]');
		$content = array(
			'<h3>Widget</h3><p>The widget is a pre-form that fills the content of a form or searchForm. It can show the calendar and a form generated from the most important form [tags].</p>',
			'<h3>Form</h3><p>Define the form here. You can click on the [tags] below to add the to the text area. HTML is fully supported.</p>',
			'<h3>URL</h3><p>Enter the URL to a page or post with a form or a searchForm in it. The information selected in the widget gets transmitted.</p>',
		);
		$at = array('top', 'right', 'top' );
		$position = array('', '', '' );

		echo easyreservations_execute_pointer(3, $handler, $content, $at, false, $position);
	}

	function easyreservation_widget_open_event(){
		$return = <<<EOF
 <script type="text/javascript">
		var countopeneasywidget = 0;
		jQuery('div[id*="easyreservations_form_widget"] > div.widget-top, div[id*="easyreservations_form_widget"] a.widget-action').live('click', function(){
			if(countopeneasywidget == 0) setTimeout('easypointer0();', 200);
			countopeneasywidget++;
		});
</script>
EOF;
		echo $return;
	}

	add_action( 'admin_print_footer_scripts', 'easyreservation_widget_open_event', 20 );
?>