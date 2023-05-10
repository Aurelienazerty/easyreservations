<?php
/**
 * Foo_Widget Class
 */
class easyReservations_form_widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::__construct( /* Base ID */'easyReservations_form_widget', /* Name */'easyReservations Widget', array( 'description' => 'easyReservations form and calendar widget' ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		easyreservations_load_resources();
		global $easyreservations_script, $the_rooms_array, $post;

		wp_enqueue_style('datestyle');
		wp_enqueue_style('easy-form-little', false, array(), false, 'all');
		wp_enqueue_script('jquery-ui-datepicker');
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$calendar = esc_attr( $instance[ 'calendar' ] );
		$calendar_style = esc_attr( $instance[ 'calendar_style' ] );
		$calendar_price = esc_attr( $instance[ 'calendar_price' ] );
		$calendar_width = esc_attr( $instance[ 'calendar_width' ] );
		$calendar_room = esc_attr( $instance[ 'calendar_room' ] );
		if(array_key_exists($post->ID,$the_rooms_array)) $calendar_room = $post->ID;
		$form_url = esc_attr( $instance[ 'form_url' ] );
		$form_button = esc_attr( $instance[ 'form_button' ] );
		$form_editor = esc_attr( $instance[ 'form_editor' ] );
		$calendar_width = (float) $calendar_width;
		if($calendar_width > 100) $calendar_width = 100;
		if($calendar_price == "on") $showPrice = 1;
		else $showPrice = 0;

		if(!empty($form_editor)){
			$form_editor = apply_filters( 'easy-widget-content', $form_editor);
			$theForm = stripslashes($form_editor);
			$tags = easyreservations_shortcode_parser($theForm, true);
			$form_date = false;
			foreach($tags as $fields){
				$field=shortcode_parse_atts( $fields);
				if($field[0]=="date-from"){
					$theForm=str_replace('['.$fields.']', '<input id="easy-widget-datepicker-from" type="text" name="from" value="'.date(RESERVATIONS_DATE_FORMAT, time()).'">', $theForm); $form_date = true;
				} elseif($field[0]=="date-to"){
					$theForm=str_replace('['.$fields.']', '<input id="easy-widget-datepicker-to"  type="text" name="to" value="'.date(RESERVATIONS_DATE_FORMAT, time()+172800).'">', $theForm); $form_date = true;
				} elseif($field[0]=="date-from-hour" || $field[0]=="date-to-hour"){
					if(isset($field[1])) $end = $field[1]; else $end = 0;
					$theForm=str_replace('['.$fields.']', '<select id="easy-widget-'.$field[0].'" name="'.$field[0].'" style="width:45px">'.easyreservations_num_options("00", 23, $end).'</select>', $theForm);
				} elseif($field[0]=="date-from-min" || $field[0]=="date-to-min"){
					if(isset($field[1])) $end = $field[1]; else $end = 0;
					$theForm=str_replace('['.$fields.']', '<select id="easy-widget-'.$field[0].'" name="'.$field[0].'" style="width:45px">'.easyreservations_num_options("00", 59, $end).'</select>', $theForm);
				} elseif($field[0]=="units" || $field[0]=="nights" || $field[0]=="times"){
					if(isset($field[1])) $number=$field[1]; else $number=31;
					$theForm=preg_replace('/\['.$fields.'\]/', '<select id="easy-widget-nights" name="nights">'.easyreservations_num_options(1,$number).'</select>', $theForm);
				} elseif($field[0]=="persons" || $field[0]=="adults"){
					$start = 1;
					if(isset($field[1])) $end = $field[1]; else $end = 6;
					if(isset($field[2])){ $start = $field[1]; $end = $field[2]; }
					$theForm=preg_replace('/\['.$fields.'\]/', '<select name="persons">'.easyreservations_num_options($start,$end).'</select>', $theForm);
				} elseif($field[0]=="childs"){
					if(isset($field[1])) $end = $field[1]; else $end = 6;
					if(isset($field[2])){ $start = $field[1]; $end = $field[2]; }
					$theForm=preg_replace('/\['.$fields.'\]/', '<select name="childs">'.easyreservations_num_options($start,$end).'</select>', $theForm);
				}  elseif($field[0]=="thename"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input type="text" id="easy-widget-thename" name="thename">', $theForm);
				}  elseif($field[0]=="email"){
					$theForm=preg_replace('/\['.$fields.'\]/', '<input id="easy-widget-email"  type="text" name="email">', $theForm);
				} elseif($field[0]=="country"){
					$theForm=str_replace('['.$fields.']', '<select id="easy-widget-country" name="country">'.easyreservations_country_options('').'</select>', $theForm);
				} elseif($field[0]=="rooms" || $field[0]=="resources"){
					$exclude = explode(",", isset($field["exclude"]) ? $field["exclude"] : '');
					$theForm=str_replace('['.$fields.']', '<select name="easyroom" id="form_room">'.easyreservations_resource_options($calendar_room, 0, $exclude).'</select>', $theForm);
				}
			}
		}

		if(isset($before_widget)) echo $before_widget;
		if($title && !empty($title) && isset($before_title) && isset($after_title)) echo $before_title.$title.$after_title;
		if($calendar == "on"){
			$array = array('width' => $calendar_width, 'style' =>  $calendar_style, 'price' => $showPrice, 'header' => 0, 'req' => 0, 'interval' => 1, 'monthes' => 1, 'select' => 2, 'resource' => $calendar_room, 'id' => rand(1,99999), 'date' => 0);
			wp_enqueue_script( 'easyreservations_send_calendar' );
			wp_enqueue_style('easy-cal-'.$calendar_style, false, array(), false, 'all'); ?>
			<form name="widget_formular" id="CalendarFormular-<?php echo $array['id']; ?>">
          <div id="showCalender" class="widget"></div>
			</form><?php
			$cal = 'new easyCalendar("'.wp_create_nonce( 'easy-calendar' ).'", '.json_encode($array).', "widget");';
			if(!function_exists('wpseo_load_textdomain')) $easyreservations_script .= 'if(window.easyCalendar) '.$cal.' else ';
			$easyreservations_script .= 'jQuery(window).ready(function(){'.$cal.'});';
		}
		if($form_date) add_action('wp_print_footer_scripts', 'easyreservatons_call_datepickers');
		if(isset($theForm)){
			if(isset($form_url) && !empty($form_url)){
				if($form_url == 'res' || $form_url == 'resource'){
					$array = array();
					foreach($the_rooms_array as $resource){
						$array[$resource->ID] = get_permalink($resource->ID);
						if($resource->ID == $calendar_room) $form_url = get_permalink($calendar_room);
					}
					$easyreservations_script .= 'var easyResourcePermalinkArray = '.json_encode($array).'; var easyWidgetResField = jQuery(\'#easy_widget_form #form_room\'); easyWidgetResField.bind(\'change\', function(){jQuery(\'form[name=easy_widget_form]\').attr(\'action\', easyResourcePermalinkArray[easyWidgetResField.val()]);});';
				} ?>
				<form method="post" action="<?php echo esc_url(__($form_url)); ?>" name="easy_widget_form" id="easy_widget_form">
					<?php echo htmlspecialchars_decode($theForm); ?>
					<p class="easy-submit"><input type="submit" class="easybutton" value="<?php echo $form_button; ?>"></p>
				</form><?php
			} else echo htmlspecialchars_decode($theForm);
		}
		if(isset($after_widget)) echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['calendar'] = strip_tags($new_instance['calendar']);
		$instance['calendar_style'] = strip_tags($new_instance['calendar_style']);
		$instance['calendar_width'] = strip_tags($new_instance['calendar_width']);
		$instance['calendar_price'] = strip_tags($new_instance['calendar_price']);
		$instance['calendar_room'] = strip_tags($new_instance['calendar_room']);
		$instance['form_url'] = strip_tags($new_instance['form_url']);
		$instance['form_editor'] = $new_instance['form_editor'];
		$instance['form_button'] = strip_tags($new_instance['form_button']);
		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance){
		if($instance){
			$title = esc_attr( $instance[ 'title' ] );
			$calendar_style = esc_attr( $instance[ 'calendar_style' ] );
			$calendar_width = esc_attr( $instance[ 'calendar_width' ] );
			$calendar_width = (float) $calendar_width;
			if($calendar_width > 100) $calendar_width = 100;
			$calendar_room = esc_attr( $instance[ 'calendar_room' ] );
			$form_url = esc_attr( $instance[ 'form_url' ] );
			$form_button = esc_attr( $instance[ 'form_button' ] );
			$form_editor = esc_attr( $instance[ 'form_editor' ] );
			$calendar = esc_attr( $instance['calendar'] );
			$calendar_price = esc_attr( $instance['calendar_price'] );
		} else {
			$title = __( 'Reserve now!', 'easyReservations' );
			$calendar_width = 100;
			$calendar_style = 1;	
			$calendar_room = 1;
			$calendar_price = 0;
			$calendar = 1;
			$form_url = __( 'type in URL to a form', 'easyReservations' );
			$form_button = __( 'Reserve now!', 'easyReservations' );
			$form_editor = '[date-from] [date-from-hour] [date-from-min]<br>[date-to] [date-to-hour] [date-to-min]<br>
<label>Res:</label> [resources]<br>
<label>Name:</label> [thename]<br>
<label>Email:</label> [email]<br><label>Country:</label> [country]';
		} ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'easyReservations'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar'); ?>"><?php _e('Show calendar:', 'easyReservations'); ?>
			<input id="<?php echo $this->get_field_id('calendar'); ?>" <?php checked( (bool) $calendar, true ); ?> name="<?php echo $this->get_field_name('calendar'); ?>" type="checkbox" /></label> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_style'); ?>"><?php _e('Calendar style:', 'easyReservations'); ?>
			<select id="<?php echo $this->get_field_id('calendar_style'); ?>" name="<?php echo $this->get_field_name('calendar_style'); ?>" ><option value="1" <?php selected( $calendar_style, 1 ); ?>>simple</option><option value="2" <?php selected( $calendar_style, 2 ); ?>>modern</option><?php if(function_exists('easyreservations_generate_multical')){ ?><option value="3" <?php selected( $calendar_style, 3 ); ?>>boxed</option><?php } if(function_exists('easyreservations_add_premium_cal_style')){ ?><option value="premium" <?php selected( $calendar_style, 'premium' ); ?>>premium</option><?php } ?></select></label> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_room'); ?>"><?php _e('Default resource:', 'easyReservations'); ?>
			<select id="<?php echo $this->get_field_id('calendar_room'); ?>" name="<?php echo $this->get_field_name('calendar_room'); ?>"><?php echo easyreservations_resource_options($calendar_room); ?></select></label> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_width'); ?>"><?php _e('Calendar width:', 'easyReservations'); ?></label> 
			<select name="<?php echo $this->get_field_name('calendar_width'); ?>" id="<?php echo $this->get_field_id('calendar_width'); ?>"><?php echo easyreservations_num_options(1,100,$calendar_width); ?></select> %
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('calendar_price'); ?>"><?php _e('Show price in calendar:', 'easyReservations'); ?>
			<input id="" <?php checked( (bool) $calendar_price, true ); ?> name="<?php echo $this->get_field_name('calendar_price'); ?>" type="checkbox" /></label> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('form_editor'); ?>"><?php _e('Widgets form template:', 'easyReservations'); ?><br>
			<textarea id="<?php echo $this->get_field_id('form_editor'); ?>" name="<?php echo $this->get_field_name('form_editor'); ?>" cols="40" rows="10"><?php echo $form_editor; ?></textarea></label> 
		</p>
		<code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[date-from]';">[date-from]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[date-to]';">[date-to]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[adults 1 5]';">[adults *]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[childs Select]';">[childs *]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[resources]';">[resources]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[country]';">[country]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[email]';">[email]</code>, <code style="cursor:pointer" onclick="document.getElementById('<?php echo $this->get_field_id('form_editor'); ?>').value += '[thename]';">[thename]</code>
		<p>
			<label for="<?php echo $this->get_field_id('form_url'); ?>"><?php _e('Form URL:', 'easyReservations'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('form_url'); ?>" name="<?php echo $this->get_field_name('form_url'); ?>" type="text" value="<?php echo $form_url; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('form_button'); ?>"><?php _e('Submit button:', 'easyReservations'); ?>
			<input class="widefat" style="width:80px" id="<?php echo $this->get_field_id('form_button'); ?>" name="<?php echo $this->get_field_name('form_button'); ?>" type="text" value="<?php echo $form_button; ?>" /></label> 
		</p>
		<?php 
	}
}
add_action( 'widgets_init', function() {
	register_widget("easyReservations_form_widget");
} );

function easyreservatons_call_datepickers(){
	easyreservations_build_datepicker(0, array("easy-widget-datepicker-from", "easy-widget-datepicker-to"));?>

	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#easy-widget-datepicker-from, #easy-widget-datepicker-to").datepicker({
				beforeShowDay: function(date){
					if(window.easydisabledays && document.easy_widget_form.easyroom) return easydisabledays(date,document.easy_widget_form.easyroom.value);
					return [true];
				}
			});
		});
	</script>
<?php
}