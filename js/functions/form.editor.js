var tag_before = '',
		tag_edit = false,
		savedSelection = false,
		insert_began = [];

jQuery('#accordion').accordion({heightStyle: "content", autoHeight: false, icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }});
jQuery('formtag').bind('click', function(){
	tag_before = this;
	var text = jQuery(this).html().replace('[', '').replace(']', '');
	var tag_final = {},
		n = 0,
		pattern, match;
	pattern = /(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/g;
	text = text.replace( /[\u00a0\u200b]/g, ' ');
	while((match = pattern.exec(text))){
		if(match[1]) tag_final[match[1]] = match[2];
		else if(match[3]) tag_final[match[3]] = match[4];
		else if(match[5]) tag_final[match[5]] = match[6];
		else if(match[7]){
			tag_final[n] = match[7];
			n++;
		} else if(match[8]){
			tag_final[n] = match[8];
			n++;
		}
	}
	tag_edit = true;
	generateTagEdit(tag_final[0], tag_final);
});

jQuery('#formcontainer').bind('click', function(){
	savedSelection = saveSelection();
});

jQuery('table.formtable tbody tr').bind('click', function(){
	if(jQuery(this).attr('attr')) generateTagEdit(jQuery(this).attr('attr'));
	else if(jQuery(this).attr('bttr')){
		var list = {
				b: { 0:"<strong>", 1:"</strong>" },
				i: { 0:"<i>", 1:"</i>" },
				label: { 0:"<label>", 1:"</label>" },
				span: { 0:'<span class="small">', 1:"</span>" },
				row: { 0:'<span class="row">', 1:"</span>" },
				h1: { 0:'<h1>', 1:"</h1>" },
				h2: { 0:'<h2>', 1:"</h2>" }
			},
			type = jQuery(this).attr('bttr');
		if(list[type]){
			var end = false;
			if(list[type][1]) end = list[type][1];
			insertTag(type, list[type][0], end)
		}
	}
});

jQuery('table.formtable tbody tr').bind('mouseenter mouseleave', function(){
  var type = jQuery(this).attr('attr');
	jQuery('formtag[attr="'+type+'"]').toggleClass('taghover');
});

jQuery('#formcontainer').bind("keypress",function(e){
	if(e.which===13){
		if (window.getSelection) {
			var selection = window.getSelection(),
				range = selection.getRangeAt(0),
				br = document.createElement("br"),
				n = document.createTextNode("\n");
			range.deleteContents();
			range.insertNode(br);
			range.insertNode(n);
			range.setStartAfter(br);
			range.setEndAfter(br);
			range.collapse(false);
			selection.removeAllRanges();
			selection.addRange(range);
		}
		return false;
	}
});
/*
jQuery('#formcontainer').bind("paste",function(e){
	var data = e.originalEvent.clipboardData.getData('html');
	if(!data) data = window.clipboardData.getData("Text");
	console.log(data);
	setTimeout(function(e) {
		var value = jQuery('#formcontainer').html().replace("&nbsp;", "<br>\r\n");
		jQuery('#formcontainer').html(value);
		jQuery('#formcontainer *').removeAttr("style");
	}, 0);
});*/
jQuery('#formcontainer').bind("paste",function(e){
	savedSelection = saveSelection();
	e.preventDefault();
	var text;
	if( e.originalEvent.clipboardData ){
		text = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
		window.document.execCommand('insertText', false, text);
	} else {
		text = window.clipboardData.getData("Text");
		if (window.getSelection)
			window.getSelection().getRangeAt(0).insertNode( document.createTextNode(text) );
	}


	//insertAtCaret(text);


	//handlepaste(document.getElementById("formcontainer"), e);
});

function handlepaste(elem, e) {
	var savedcontent = elem.innerHTML;
	var test;
	if (e && e.clipboardData && e.clipboardData.getData) {// Webkit - get data from clipboard, put into editdiv, cleanup, then cancel event
		if (/text\/html/.test(e.clipboardData.types))
			elem.innerHTML = e.clipboardData.getData('text/html');
		else if (/text\/plain/.test(e.clipboardData.types))
			elem.innerHTML = e.clipboardData.getData('text/plain');
		else
			elem.innerHTML = "";
		waitforpastedata(elem, savedcontent);
		if (e.preventDefault) {
			e.stopPropagation();
			e.preventDefault();
		}
		return false;
	}
	else {// Everything else - empty editdiv and allow browser to paste content into it, then cleanup
		elem.innerHTML = "";
		waitforpastedata(elem, savedcontent);
		return true;
	}
}

function waitforpastedata(elem, savedcontent) {
	if (elem.childNodes && elem.childNodes.length > 0)
		processpaste(elem, savedcontent);
	else {
		that = { e: elem, s: savedcontent }
		that.callself = function () {
			waitforpastedata(that.e, that.s)
		}
		setTimeout(that.callself,20);
	}
}

function processpaste (elem, savedcontent) {
	pasteddata = elem.innerHTML;
	//elem.innerHTML = savedcontent;
	insertAtCaret(pasteddata);
}


function generateTagEdit(type, tag){
	if(fields[type]){
		var title = 'Add';
		if(tag) title = 'Edit';
		jQuery('*[name=deltag]').remove();
		var value = '<h3 name="deltag">'+title+' '+fields[type]['name']+' field</h3><div name="deltag"><input type="hidden" name="0" value="'+type+'">';
		value += '<p class="desc">'+fields[type]['desc']+'</p>';
		var options = fields[type]['options'];
		if(typeof options == 'function') value += options(tag);
		else {
			jQuery.each(options, function(k,v){
				if(v['title'] && v['input'] != 'check') value += '<h4>'+v['title']+'</h4>';
				value += '<p>';
				var sel = false, hasclass = '';
				if(v['class']) hasclass = ' class="'+v['class']+'"';
				if(tag && tag[k]) sel = tag[k];
				else if(v['default']) sel = v['default'];
				else sel = '';
				if(typeof v['input'] == 'function') value += v['input'](tag);
				else {
					if(v['input'] == 'text'){
						value += '<input type="text" name="'+k+'" value="'+sel+'"'+hasclass+'>';
					} else if(v['input'] == 'textarea'){
						value += '<textarea name="'+k+'"'+hasclass+'>'+sel+'</textarea>';
					} else if(v['input'] == 'check'){
						if(tag && tag[k] || (!tag && v['checked'])) sel = 'checked="checked" '; else sel = '';
						value += '<input type="checkbox" name="'+k+'" value="'+v['default']+'" '+sel+hasclass+'> '+v['title'];
					} else if(v['input'] == 'select'){
						value += '<select name="'+k+'"'+hasclass+'>';
						value += generateOptions(v['options'],sel);
						value += '</select>';
					}
				}
				value += '</p>';
			});
		}
		value += '<a href="javascript:" class="easybutton button-primary" onclick="submitTag();">'+title+'</a>&nbsp;';
		value += '<a href="javascript:" class="button" onclick="deactivateTag();">Cancel</a>';
		value += '</div>';
		jQuery('#accordion').prepend(value);
		jQuery('#accordion').accordion( "destroy").accordion({heightStyle: "content", autoHeight: false, icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }});
	}
}

function submitTag(){
	var tag_new = '';
	var type = false;
	jQuery('*[name=deltag] :input:not(.not)').each(function(ui, child){
		if(child.value != '' && (child.type != 'checkbox' || child.checked == true )){
			if(!type) type = child.value;
			if(child.name == "*" || (!isNaN(parseFloat(child.name)) && isFinite(child.name))){
				if(jQuery(this).hasClass('quote'))tag_new += '"'+child.value+'" ';
				else tag_new += child.value+' ';
			} else tag_new += child.name+'="'+child.value+'" ';
		}
	});
	if(fields[type] && fields[type]['generate']) tag_new += fields[type]['generate']();
	tag_new = tag_new.substr(0,tag_new.length-1)
	tag_new = '['+tag_new+']';
	if(tag_edit) jQuery(tag_before).html(tag_new);
	else insertAtCaret('<formtag attr="'+type+'">'+tag_new+'</formtag>');
	deactivateTag();
}

function deactivateTag(){
	tag_edit = false;
	jQuery('*[name=deltag]').fadeOut("fast");
	jQuery('#accordion').accordion( "destroy").accordion({heightStyle: "content", autoHeight: false, icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" }});
}

function insertAtCaret(text){
	if(savedSelection){
		restoreSelection(text);
	} else {
		jQuery('#formcontainer').prepend(text);
	}
}

function resetToDefault(){
	var Default = '[error]\n';
	Default += '<h1>Reserve now![show_price style="float:right;"]</h1>\n';
	Default += '<h2>General information</h2>\n\n';
	Default += '<label>Arrival Date\n<span class="small">When will you come?</span>\n</label><span class="row">[date-from style="width:75px"] [date-from-hour style="width:auto" value="12"]:[date-from-min style="width:auto"]</span>\n\n';
	Default += '<label>Departure Date\n<span class="small">When will you go?</span>\n</label><span class="row">[date-to style="width:75px"] [date-to-hour style="width:auto" value="12"]:[date-to-min style="width:auto"]</span>\n\n';
	Default += '<label>Resource\n<span class="small">Where you want to sleep?</span>\n</label>[resources]\n\n';
	Default += '<label>Adults\n<span class="small">How many guests?</span>\n</label>[adults 1 10]\n\n';
	Default += '<label>Children\'s\n<span class="small">With children\'s?</span>\n</label>[childs 0 10]\n\n';
	Default += '<h2>Personal information</h2>\n\n';
	Default += '<label>Name\n<span class="small">What is your name?</span>\n</label>[thename]\n\n';
	Default += '<label>Email\n<span class="small">What is your email?</span>\n</label>[email]\n\n';
	Default += '<label>Phone\n<span class="small">Your phone number?</span>\n</label>[custom text Phone *]\n\n';
	Default += '<label>Street\n<span class="small">Your street?</span>\n</label>[custom text Street *]\n\n';
	Default += '<label>Postal code\n<span class="small">Your postal code?</span>\n</label>[custom text PostCode *]\n\n';
	Default += '<label>City\n<span class="small">Your city?</span>\n</label>[custom text City *]\n\n';
	Default += '<label>Country\n<span class="small">Your country?</span>\n</label>[country]\n\n';
	Default += '<label>Message\n<span class="small">Any comments?</span>\n</label>[custom textarea Message]\n\n';
	Default += '<label>Captcha\n<span class="small">Type in code</span>\n</label>[captcha]\n\n';
	Default += '<div style="text-align:center">[submit Send]</div>';

	jQuery('#formcontainer').html(htmlForTextWithEmbeddedNewlines(Default));
}

if (window.getSelection) {
	saveSelection = function() {
		var sel = window.getSelection(), ranges = [];
		if (sel.rangeCount) {
			for (var i = 0, len = sel.rangeCount; i < len; ++i) {
				ranges.push(sel.getRangeAt(i));
			}
		}
		return ranges;
	};
	restoreSelection = function(text) {
		var sel = window.getSelection();
		sel.removeAllRanges();
		for (var i = 0, len = savedSelection.length; i < len; ++i) {
			sel.addRange(savedSelection[i]);
		}
		var range = sel.getRangeAt(0);
		range.collapse (false);
		var el = document.createElement("div");
		el.innerHTML = text;
		var frag = document.createDocumentFragment(), node, lastNode;
		while ( (node = el.firstChild) ) {
			lastNode = frag.appendChild(node);
		}
		//document.execCommand("insertHTML", true, text);
		//range.deleteContents();
		range.insertNode( frag );
		sel.removeAllRanges();

	};
	insertTag = function(type, start, end){
		if(savedSelection){
			var sel = window.getSelection();
			sel.removeAllRanges();
			for (var i = 0, len = savedSelection.length; i < len; ++i) {
				sel.addRange(savedSelection[i]);
			}
			if(!sel.type || sel.type == 'None' || sel.type == 'Caret'){
				if(end && insert_began[start]){
					insert_began[start] = null;
					document.execCommand("insertText", true, end);
					jQuery('tr[bttr="'+type+'"] tag').text(start);
				} else {
					if(end){
						insert_began[start] = 1;
						jQuery('tr[bttr="'+type+'"] tag').text(end);
					}
					document.execCommand("insertText", true, start);
				}
			} else {
				var text = window.getSelection();
				text = start + text;
				if(end) text += end;
				document.execCommand("insertText", true, text);
			}
			savedSelection = saveSelection();
		} else {
			jQuery('#formcontainer').prepend(start);
		}
	};
} else if (document.selection && document.selection.createRange) {
	saveSelection = function() {
		var sel = document.selection;
		return (sel.type != "None") ? sel.createRange() : null;
	};
	restoreSelection = function(text) {
		if (savedSelection) {
			savedSelection.select();
			document.selection.createRange().text = text;
		}
	};
	insertTag = function(){
		alert('Function not available in your browser');
	};
}

function htmlForTextWithEmbeddedNewlines(text) {
	var htmls = [];
	var lines = text.split(/\n/);
	var tmpDiv = jQuery(document.createElement('div'));
	for (var i = 0 ; i < lines.length ; i++) {
		htmls.push(tmpDiv.text(lines[i]).html());
	}
	return htmls.join("<br>\n");
}

