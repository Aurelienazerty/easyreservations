<?php
	function easyreservations_dashboard_tutorial() {
		$handler = array('h2:first','.add-new-h2', '#show-settings-link', 'b.overviewDate', '.overview img.ui-datepicker-trigger', 'input[name="daybutton"]', 'td.ov-days:nth-child(8)', '.overview > tbody > tr:first > td:first',  '.overview tr[id^="room"]:nth-child(3) > td:nth-child(10)',  '.overview tr[id^="room"]:nth-child(3) > td:nth-child(13)', '.overview tr[id^="room"]:nth-child(3) > td:first', '#easy-table-navi', '#idstatusbar', '#easy-table-search-date', '#easy-table-search-field', '.reservationTable > thead > tr > th > a:first', '.easy-favourite:first', '#bulk', 'td > div.tablenav', '#idworkload');
		$content = array(
				'<h3>Dashboard</h3><p>Welcome to easyReservations!<br>This is the reservations dashboard and you can add, edit and handle your reservations here.</p>',
				'<h3>Add reservation</h3><p>Click here to add a new reservations manually.<p>',
				'<h3>Dashboard options</h3><p>Configure the dashboard as of your preferences.</p>',
				'<h3>Overview</h3><p>The overview is the visual and interactive schedule of your business.</p>',
				'<h3>Date picker</h3><p>Click on this icon to select the starting day of the overview.</p>',
				'<h3>Mode</h3><p>Select between hourly and daily mode. Reservations with a duration under the selected interval will get shown in one field with the count in it.</p>',
				'<h3>Date</h3><p>Click on a day to show all reservations at this day in the table.</p>',
				'<h3>Resource</h3><p>Click here to edit the resource without wasting a click.</p>',
				'<h3>Overviews cell</h3><p>You can select the date and resource of a reservation by clicking in the cells. If no reservation selected it will lead to add a new reservation. Many information get displayed visually in the cells. The background can be red for unavailable by filter, yellow for weekend and blue for today. Past days have a pattern in it. Reservations get displayed in four different colors for the time and if its active.</p>',
				'<h3>Second click</h3><p>To set the information you have to click on a second cell in the same row.</p>', //10
				'<h3>Resources count</h3><p>Click on the resources count in approve or edit to change the resource without changing the days.</p>', //11
				'<h3>Reservations groups</h3><p>The reservations table is divided in groups of time and status. Trash and favourites only shows up if it has reservations.</p>',
				'<h3>Filter and count</h3><p>Filter reservations in table by status and resources. The count of reservations defines how many reservations gets shown at once.</p>',
				'<h3>Date</h3><p>Show reservations of a selected date.</p>',
				'<h3>Search</h3><p>Search for name, email, id or custom information in your reservations.</p>',
				'<h3>Order by</h3><p>You can order the by the reservations by name, date, reserved and resource.</p>',
				'<h3>Favourite</h3><p>You can favourite reservations to find the better later on and remember them. They get saved per user, so nobody can see your favourites.</p>',
				'<h3>Bulk</h3><p>To trash, delete or restore multiple checked reservations at the same time.</p>',
				'<h3>Pagination</h3><p>If more reservations are in the same group then defined above they get separated and you can navigate through them here.</p>',
				'<h3>Today</h3><p>Here you can see who arrivals and departures today. The percentage value quantifies how much of your resources are reserved today.</p>',
		);
		$options = array('', '', 'pointerClass: \'easy-pointer-right\',', '', '', 'pointerClass: \'easy-pointer-right2\',', '', '', '', '', '', '', '', '',  'pointerClass: \'easy-pointer-right2\',', '', '', '', '', '',);
		$execute = array('', '', '', '', 'easyRes_sendReq_Overview(0,\'no\',\'30\',\'3600\');resetSet();', 'easyRes_sendReq_Overview(0,\'no\',50,86400);resetSet();document.getElementById(\'easy-table-search-date\').value = \''.date(RESERVATIONS_DATE_FORMAT, time()+3*86400).'\';easyreservation_send_table(\'all\', 1);', 'resetTableValues();', 'clickOne(jQuery(\'.overview tr[id^="room"]:nth-child(3) > td:nth-child(10)\').get(0), '.(time()+5*86400).', \'black\');',  'resetSet();clickOne(jQuery(\'.overview tr[id^="room"]:nth-child(3) > td:nth-child(10)\').get(0), '.(time()+5*86400).', \'black\');clickTwo(jQuery(\'.overview tr[id^="room"]:nth-child(3) > td:nth-child(13)\').get(0), '.(time()+8*86400).', \'black\', 1);', 'resetSet()', '', '', '', '', '', 'easyreservations_send_fav(jQuery(\'.easy-favourite:first\').get(0));', 'easyreservations_send_fav(jQuery(\'.easy-favourite:first\').get(0));jQuery(\'input[name="themainbulk"]\').attr(\'checked\', true);checkAllController(document.frmAdd,jQuery(\'input[name="themainbulk"]\').get(0),\'bulkArr\');', 'jQuery(\'input[name="themainbulk"]\').attr(\'checked\', false);checkAllController(document.frmAdd,jQuery(\'input[name="themainbulk"]\').get(0),\'bulkArr\');', '', '');
		$at = array('', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top', 'top');

		echo easyreservations_execute_pointer(20, $handler, $content, $at, $execute, $options);
	}
?>