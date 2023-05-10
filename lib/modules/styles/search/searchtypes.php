<?php
/*
Plugin Name: searchForm Module searchtypes addon
Plugin URI: http://easyreservations.org/module/search/
Version: 1.0.8
Description: 3.1.5
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!.
*/
if(is_admin()){
	function easyreservations_post_type_setting($rows){
		$autosave = get_option('reservations_search_posttype');
		$rows['<img src="'.RESERVATIONS_URL.'images/email.png"> <b>'.__( 'Post Types', 'easyReservations' ).'</b>'] =  '<input type="text" name="easy_search_post_type" value="'.(($autosave && !empty($autosave)) ? $autosave : 'easy-rooms').'" style="width:200px">';
		return $rows;
	}
	add_filter('er_add_set_main_table_row', 'easyreservations_post_type_setting');

	function easyreservations_save_post_type_setting(){
		update_option('reservations_search_posttype', $_POST['easy_search_post_type']);
	}
	add_action('er_set_main_save', 'easyreservations_save_post_type_setting');

	function easyreservations_add_the_custom_post_select($array, $resourceID){
		$autosave = get_option('reservations_search_posttype');
		if($autosave && $autosave != 'easy-rooms'){
			$posts = get_posts(array('post_type' => $autosave,'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => -1));
			$get_related = get_post_meta($resourceID,'reservations_releated_post', true);
			$options = '';
			foreach($posts as $post){
				if($get_related && $post->ID == $get_related) $sel = 'selected="selected"'; else $sel = '';
				$options .= '<option value="'.$post->ID.'" '.$sel.'>'.__($post->post_title).' (#'.$post->ID.')</option>';
			}
			$array['<b>Related  post</b>'] = '<select name="easy-related-post">'.$options.'</select>';
		}
		return $array;
	}
	add_filter('er_add_res_main_table_row', 'easyreservations_add_the_custom_post_select', 10, 2);

	function easyreservations_save_releated_post($resourceID){
		if(isset($_POST['easy-related-post'])){
			update_post_meta($resourceID, 'reservations_releated_post', $_POST['easy-related-post']);
		}
	}
	add_action('er_res_main_save', 'easyreservations_save_releated_post', 10, 1);
}

function easyreservations_search_change_values($room){
	$post_type = get_option('reservations_search_posttype');
	if($post_type && !empty($post_type) && $post_type !== 'easy-rooms'){
		$the_releated_post = get_post_meta($room->ID, 'reservations_releated_post', true);
		if($the_releated_post){
			if(defined('ICL_SITEPRESS_VERSION')){
				$the_releated_post = icl_object_id($the_releated_post, 'any', true);
			}
			$post = get_post($the_releated_post);
			$room->resid = $the_releated_post;
			$room->post_content = $post->post_content;
			$room->post_title = $post->post_title;
		}
	}
	return $room;
}

add_filter('easy_search_resources', 'easyreservations_search_change_values', 10 ,1);

?>