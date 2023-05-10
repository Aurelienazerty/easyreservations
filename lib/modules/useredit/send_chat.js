function easyreservations_send_chat(key, t){

	var error = 0;
	var tsecurity = document.getElementById('easy-chat-nonce').value;

	var message_field = document.getElementById('easy-chat-message');
	if(message_field) var message = message_field.value;
	else message = '';

	var userID = document.getElementById('userID');
	if(userID) var tuserID = userID.value;
	else tuserID = '';

	var name_field = document.getElementById('easy-form-thename');
	if(name_field) var name = name_field.value;
	else name = '';

	if(key && t && key >= 0){
		var field1 = t.parentNode;
		var field2 = field1.previousSibling;
		field1.parentNode.removeChild(field2);
		field1.parentNode.removeChild(field1);
	} else {
		key = '';
	}

	var userfield = document.getElementById('editID');
	if(userfield) var user = userfield.value;

	mode =  'visible';

	var data = {
		action: 'easyreservations_send_chat',
		security:tsecurity,
		message:message,
		mode:mode,
		key:key,
		name:name,
		user: 'g'+tuserID,
		id: user
	};

	if(error == 0){
		jQuery.post(easyAjax.ajaxurl , data, function(response){
			if(key == ''){
				var element = document.getElementById('easy-chat-add');
				element.innerHTML += response;
			}
			return false;
		});
	}
}