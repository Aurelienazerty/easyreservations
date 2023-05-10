<?php

function easyreservations_googlecal_initialize(){
	$options = get_option('reservations_googlecal');
	include_once(WP_PLUGIN_DIR."/easyreservations/lib/modules/sync/googleapi/Google_Client.php");
	include_once(WP_PLUGIN_DIR."/easyreservations/lib/modules/sync/googleapi/contrib/Google_CalendarService.php");

	$client = new Google_Client();
	$client->setApplicationName("easyReservations Google Calendar Sync");
	$client->setClientId($options['clientid']);
	$client->setAccessType('offline');
	$client->setClientSecret($options['clientsecure']);
	$client->setRedirectUri(admin_url( 'admin.php?page=reservation-settings&site=sync&gcb=true', 'http' ));
	$client->setDeveloperKey($options['developerkey']);
	$cal = new Google_CalendarService($client);

	if(isset($_GET['gcb']) && isset($_GET['code'])){
		$client->authenticate();
		$options['token'] = $client->getAccessToken();
		update_option('reservations_googlecal', $options);
		return true;
	}

	if(isset($options['token'])){
		$client->setAccessToken($options['token']);
	}

	if($client->getAccessToken()){
		$options['token'] = $client->getAccessToken();
		update_option('reservations_googlecal', $options);
	} else {
		$authUrl = $client->createAuthUrl();
		return '<a href="'.$authUrl.'" class="button">'.__('Connect to google', 'easyReservations').'</a>';
	}

	return array($cal, $client);
}

if(is_admin()){
	function easyreservations_google_cal_settings($rows){
		easyreservations_load_resources();
		global $the_rooms_array;
		$options = get_option('reservations_googlecal');
		if(isset($_POST['clientid'])){
			$options['clientid'] = $_POST['clientid'];
			$options['clientsecure'] = $_POST['clientsecure'];
			$options['developerkey'] = $_POST['developerkey'];
			update_option('reservations_googlecal', $options);
		}

		$rows[__('client id', 'easyReservations')] = '<input type="text" name="clientid" value="'.$options['clientid'].'">';
		$rows[__('client secret', 'easyReservations')] = '<input type="text" name="clientsecure" value="'.$options['clientsecure'].'">';
		$rows[__('developer key', 'easyReservations')] = '<input type="text" name="developerkey" value="'.$options['developerkey'].'">';

		if($options && $options['clientid']){
			try {
				$client = easyreservations_googlecal_initialize();
				if($client && is_string($client)){
					$rows['col-1'] = $client;
				} elseif(is_array($client)) {
					$list = $client[0]->calendarList->listCalendarList();
					$rows['col-2'] = '<b>Resources Calendar</b>';
					foreach($the_rooms_array as $resource){
						if(isset($_POST['cal-'.$resource->ID])){
							if($_POST['cal-'.$resource->ID] !== 'false'){
								if($_POST['cal-'.$resource->ID] == 'new'){
									$calendar = new Google_Calendar();
									$calendar->setSummary(__($resource->post_title));
									$timezone = get_option('timezone_string');
									if($timezone && !empty($timezone)) $calendar->setTimeZone($timezone);
									$createdCalendar = $client[0]->calendars->insert($calendar);
									$options['calendars'][$resource->ID] = $createdCalendar['id'];
								} else $options['calendars'][$resource->ID] = $_POST['cal-'.$resource->ID];
							}
						}

						if(isset($options['calendars']) && isset($options['calendars'][$resource->ID])) $sel = $options['calendars'][$resource->ID];
						else $sel = false;
						$rows[$resource->post_title] = '<select name="cal-'.$resource->ID.'"><option value="false">'.__('Inactive', 'easyReservations').'</option><option value="new">'.__('New', 'easyReservations').'</option>'.easyreservations_googlecal_calendar_options($list, $sel).'</select>';
					}
				} else {

				}
			} catch(Exception $e){
				var_dump($e->getMessage());
			}
		}
		update_option('reservations_googlecal', $options);
		return $rows;
	}

	add_filter('easyreservations_google_setting_rows', 'easyreservations_google_cal_settings', 10, 1);

	function easyreservations_googlecal_calendar_options($list, $sel){
		$options = '';
		foreach($list['items'] as $calendar){
			$options.='<option value="'.$calendar['id'].'" '.selected($sel, $calendar['id'], false).'>'.$calendar['summary'].'</option>';
		}
		return $options;
	}

	function easyreservations_googlecal_callback(){
	}
	add_action('er_sync_settings_top', 'easyreservations_googlecal_callback');
}
?>