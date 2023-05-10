<?php
/*
Plugin Name: Language Module
Plugin URI: http://easyreservations.org/lang/
Version: 1.2.4
Description: 3.3
Author: Feryaz Beer
License:GPL2

YOU ARE ALLOWED TO USE AND MODIFY THE FILES, BUT NOT TO SHARE OR RESELL THEM IN ANY WAY!.
*/

function easyreservations_translate_content($content, $local = false){
	if(!$local) $local = strtolower(get_locale());
	else $local = strtolower($local);
	if(preg_match("#\[:[0-9a-z_]*\]#ism",$content)){
		$translation = false;
		$blocks = preg_split("#(\[:[0-9a-z_]*\])#ism", $content, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		$count = count($blocks);
		$lang_code = array();
		$new_content = '';
		foreach($blocks as $key => $block){
			if(preg_match("#\[:([0-9a-z_]*)\]#ism",$block, $lang_code)){
				if(isset($lang_code[1])){
					if(strtolower($lang_code[1]) == $local){
						$new_content .= $blocks[$key+1];
						$translation = true;
					} elseif($lang_code[1] == 'else'){
						if(!$translation){
							$new_content .= $blocks[$key+1];
							if($key+2 >= $count) $content .= $blocks[$key+1];
							else $translation = $blocks[$key+1];
						}
					} elseif($lang_code[1] == 'end'){
						if($translation && $translation !== true) $content .= $translation;
						$translation = false;
					}
				}
				if(!isset($lang_code[1]) || $lang_code[1] !== 'end'){
					unset($blocks[$key+1]);
				}
			} elseif(isset($blocks[$key])){
				$new_content .= $blocks[$key];
			}
		}
		if(!empty($new_content)) {
			$content = $new_content;
		}
	}
	$tag = easyreservations_shortcode_parser($content, true, 'lang ');
	foreach($tag as $m){
		$field=shortcode_parse_atts($m);
		$text = '';
		if(isset($field['value'])) $text = $field['value'];
		if(isset($field[$local])) $text = $field[$local];
		$content = str_replace('[lang '.$m.']', $text, $content);
	}
	return $content;
}

if(is_admin()){
	function easyreservations_lang_email(){
		?><p><code class="codecolor">[lang]</code> <i><?php echo __( 'translateable content' , 'easyReservations');?></i></p><?php
	}

	function easyreservations_lang_js_add_func(){?>
		function generateLangSelection(tag){
			var value = '<a href="javascript:" onclick="generateLangOptions()">Add new translation</a>';
			if(tag){
				jQuery.each(tag, function(k,v){
				  if(isNaN(parseFloat(k)) && !isFinite(k) && k != 'value') value += generateLangOptions(k,v,true);
				});
			} else value += '<span class="langinform"></span>';
			return value;
		}

		function generateLangOptions(sel,val,doreturn){
			var value = '<p style="padding:0px;margin:0px;" class="langinform"><select class="not" name="langcodes[]">';
			jQuery.each(<?php echo json_encode(easyreservations_get_lang_array()); ?>, function(v,k){
				var selected = '';
				if(sel && sel == k) selected = 'selected="selected"';
        value += '<option value="'+k+'" '+selected+'>'+v+'</option>';
			});

			if(!val) val = '';
			value += '</select> <input type="text" class="not" name="translation[]" value="'+val+'">';
			value += '<a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" class="button" style="margin:0px 2px 0px 3px;padding:5px 8px" >&#8722;</a></p>';

			if(doreturn) return value;
			else jQuery('.langinform:last').after(value);
		}

		function addLangToTag(){
			var codefields = document.getElementsByName('langcodes[]');
			var transfields = document.getElementsByName('translation[]');
			var tag = '';

			if(codefields.length >= 1){
				for(var i = 0; i < codefields.length; i++){
					tag += codefields[i].value+'="'+transfields[i].value+'" ';
				}
			}
			return tag;
		}

		fields['lang'] = {
			name: '<?php addslashes(_e( 'Translation' , 'easyReservations' ));?>',
			desc: '<?php addslashes(_e( 'For multilingual content. It can be used inside other tags and used in emails, the search bar or invoice templates.' , 'easyReservations' ));?>',
			generate: addLangToTag,
			options: {
				value: {
					title: '<?php addslashes(_e( 'Value' , 'easyReservations' ));?>',
					input: 'text',
					default: 'Standard text'
				},
				lang: {
					title: '<?php addslashes(_e( 'Translations' , 'easyReservations' ));?>',
					input: generateLangSelection
				}
			}
		}
	<?php
	}

	function easyreservations_add_lang_select(){
		echo '<tr class="alternate"><td nowrap><b>'.__( 'Language' , 'easyReservations' ).':</b> <select name="easy-set-local">'.easyreservations_lang_options().'</select></td></tr>';
	}

	function easyreservations_add_lang_to_form_list($accordeon){
		$accordeon .= '<tr attr="lang">';
			$accordeon .= '<td style="background-image:url('.RESERVATIONS_URL.'images/country.png);"></td>';
			$accordeon .= '<td><strong>'.__('Translation','easyReservations').'</strong><br><i>'.__('For multilingual content','easyReservations').'</i></td>';
		$accordeon .= '</tr>';
		return $accordeon;
	}

	add_action('easy-mail-add-input', 'easyreservations_add_lang_select');
	add_action('easy-form-js-before', 'easyreservations_lang_js_add_func');
	add_action('easy-email-list', 'easyreservations_lang_email');
	add_filter('easy-form-list', 'easyreservations_add_lang_to_form_list', 10, 1);
}

	add_filter('easy-form-content', 'easyreservations_translate_content', 10, 2);
	add_filter('easy-email-content', 'easyreservations_translate_content', 10, 2);
	add_filter('easy-email-subj', 'easyreservations_translate_content', 10, 2);
	add_filter('easy-widget-content', 'easyreservations_translate_content', 10, 2);
	add_filter('easy-invoice-content', 'easyreservations_translate_content', 10, 2);

	function easyreservations_get_lang_array(){
		$array = array('English' => 'en_EN', 'Azeri' => 'azb', 'Afrikaans' => 'af', 'Albanian' => 'sq_AL', 'Arabic' => 'ar', 'Azeri' => 'azr_AZR', 'Bangla' => 'bn_BD', 'Basque' => 'eu', 'Belarusian' => 'be_BY', 'German' => 'de_DE', 'Bulgarian' => 'bg_BG', 'Catalan' => 'ca', 'Chilean' => 'es_CL', 'Chinese' => 'zh_CN', 'Hong Kong' => 'zh_HK', 'Taiwan' => 'zh_TW', 'Croatian' => 'hr', 'Czech' => 'cs_CZ', 'Danish' => 'da_DK', 'Dutch' => 'nl_NL', 'Esperanto' => 'eo', 'Estonian' => 'et', 'Faroese' => 'fo', 'Finnish' => 'fi', 'French' => 'fr_FR', 'Galician' => 'gl_ES', 'Georgian' => 'ge_GE', 'German' => 'de_DE', 'Greek' => 'el', 'Hebrew' => 'he_IL', 'Hindi' => 'hi_IN', 'Hungarian' => 'hu_HU', 'Icelandic' => 'is_IS', 'Indonesian' => 'id_ID', 'Italian' => 'it_IT', 'Japanese' => 'ja', 'Kazakh' => 'kk', 'Khmer' => 'km_KH', 'Korean' => 'ko_KR', 'Kyrgyz' => 'ky', 'Latvian' => 'lv', 'Lithuanian' => 'lt_LT', 'Macedonian' => 'mk_MK', 'Malagasy' => 'mg_MG', 'Myanmar' => 'my_MM', 'Norwegian' => 'norsk', 'Bokmål' => 'nb_NO', 'Nynorsk' => 'nn_NO', 'Persian' => 'fa_IR', 'European Portuguese' => 'pt_PT', 'Brazilian Portuguese' => 'pt_BR', 'Romanian' => 'ro_RO', 'Russian' => 'ru_RU', 'Sakha' => 'sah', 'Serbian' => 'sr_RS', 'Sinhala' => 'si_LK', 'Slovak' => 'sk_SK', 'Slovenian' => 'sl_SI', 'Spanish - España' => 'es_ES', 'Spanish - Perú' => 'es_PE', 'Sundanese' => 'su_ID', 'Swedish' => 'sv_SE', 'Tajik' => 'tg', 'Tamil' => 'ta_LK', 'Thai' => 'th', 'Turkish' => 'tr', 'Ukrainian' => 'uk', 'Uighur' => 'ug_CN', 'Uzbek' => 'uz_UZ', 'Vietnamse' => 'vi', 'Welsh' => 'cy');
		ksort($array);
		return $array;
	}

	function easyreservations_lang_options($sel = "en_EN"){
		$array = easyreservations_get_lang_array();
		$options = '';
		foreach($array as $key => $lang){
			$selected = $lang == $sel ? 'selected="selected"' : '';
			$options .= '<option value="'.$lang.'" '.$selected.'>'.$key.' ('.$lang.')</option>';
		}
		return $options;
	}
?>