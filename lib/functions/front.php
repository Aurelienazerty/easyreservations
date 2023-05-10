<?php 

	function easyreservations_clean_shortcodes($content){
		$pattern_full = '(name="easyFrontendFormular">.*?</form|<form name="HourlyCalendarFormular".*?</form|<form name="easy_search_formular".*?</form|<form name="CalendarFormular.*?</form|<div id="searchbar".*?</div|<div class="easy_form_success".*?</div|id="edittable".*?</table>|<div class="easy-edit-status">.*?</div>|<span class="row">.*?</span>)s';
		preg_match_all($pattern_full, $content, $matches);
		if(!empty($matches[0])){
			foreach($matches[0] as $match){
				if(strpos($match, 'easy-edit-status') !== false || strpos($match, 'searchbar') !== false || strpos($match, 'span class="row"') !== false || strpos($match, 'easy_form_success') !== false) $thematch =  str_replace( array( '<br>', '<br />' ), '', $match );
				else $thematch = $match;
				$content = str_replace($match, str_replace( array( '<p>', '</p>' ), '', $thematch ), $content );
			}
		}
		return $content;
	}

	add_filter( 'the_content', 'easyreservations_clean_shortcodes', 99999 );
?>