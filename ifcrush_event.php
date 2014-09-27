<?php
/**
 **  This file contains all the support functions for the table ifcrush_events
 **/
 
//  $event_table_name = $wpdb->prefix . "ifc_event";    
// 	$sql = 	"CREATE TABLE $event_table_name (
// 		eventDate date not null,
// 		title varchar(30) not null,
// 		eventID int not null auto_increment,
// 		fratID varchar(3) not null,
// 		PRIMARY KEY(ID),
// 		FOREIGN KEY(fratID) references $frat_table_name(letters)
// 	) engine = InnoDB;";

/** 
 * Display events for a fraternity
 **/
function ifcrush_display_events($frat_letters) {
	
	global $wpdb;
	
	$event_table_name = $wpdb->prefix . "ifc_event";
	$query = "SELECT * FROM $event_table_name where fratID='$frat_letters'";
	$allevents = $wpdb->get_results($query);
	
	if ($allevents) {
		create_event_table_header(); // make a table header
		foreach ($allevents as $event) { // populate the rows with db info
			//echo "<pre>"; print_r($event); echo "</pre>";

			create_event_table_row($event);
		}
		create_event_add_row($frat_letters);
		create_event_table_footer(); // end the table
	} 
	else { 
		?><h2>No events.  Add one!</h2><?php
		create_event_table_header(); // make a table header
		create_event_add_row($frat_letters);
		create_event_table_footer(); // end the table
	}
}


/**
 *  dummied up data - this could be obsolete
 **/
function ifcrush_install_events() {
	$events = array( array('eventDate' => '2014-9-15', 	'title'=> 'Sunday Dinner 1',	'fratID' => "LXA"),
					 array('eventDate' => '2014-9-22', 	'title'=> 'Sunday Dinner 2',	'fratID' => "LXA"),
					 array('eventDate' => '2014-9-29', 	'title'=> 'Sunday Dinner 3',	'fratID' => "LXA"),
					 array('eventDate' => '2014-9-15', 	'title'=> 'Sunday Dinner 1',	'fratID' => "SAE"),
					 array('eventDate' => '2014-9-22', 	'title'=> 'Sunday Dinner 2',	'fratID' => "SAE"),
					 array('eventDate' => '2014-9-29', 	'title'=> 'Sunday Dinner 3',	'fratID' => "SAE"),
					);
	
	foreach ($events as $event)
   		addEvent($event);
} // initial array of events (cooked data)

/* This function is going to add an event for the passed $frat_letters
 * This function is called from ifcrush_frat with $frat_letters 
 */
function ifcrush_event_handle_form($frat_letters) { 

	global $debug;
	if ($debug){
			echo "[ifcrush_event_handle_form] $frat_letters";
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
	
	$action = $_POST['action'];
	switch ($action) {
		case "Update Event":
			$thisevent = array( 
				'eventID'	=>  $_POST['eventID'],
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['title'],
				'fratID'	=>	$frat_letters
			); // put the form input into an array
			updateEvent($thisevent);
			break;
		case "Delete Event":
			$thisevent = array( 
				'eventID'	=>  $_POST['eventID'],
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['title'],
				'fratID'	=>	$frat_letters
			); // put the form input into an array
			deleteEvent($thisevent);
			break;
		case "Add Event":
			$thisevent = array( 
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['title'],
				'fratID'	=>	$frat_letters
			); // put the form input into an array
			addEvent($thisevent);
			break;
		default:
			echo "[ifcrush_event_handle_form]: bad action";
	}
} 

/* Event_handler_form helpers */
function addEvent($thisevent) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_event";
	$rows_affected = $wpdb->insert($table_name, $thisevent);
	
	if ($rows_affected == 0) {
		echo "INSERT ERROR for " . $thisevent['eventDate']. $thisevent['title'] . $thisevent['frat'];
	}
	return $rows_affected;
} // adds a event to the table if addEvent is tagged

function updateEvent($thisevent) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_event";
	$where = array('eventID' => $thisevent['eventID']);
	$wpdb->update( $table_name, $thisevent, $where );
} // updates a event with a matching netID if updateEvent is tagged

function deleteEvent($thisevent) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_event";
	$wpdb->delete( $table_name, $thisevent);
} // deletes a event if deleteEvent is tagged

function create_event_table_header() {
	?>
		<table>
		<tr>
			<th>Date</th>
			<th>Title</th>
			<th>Actions</th>

		</tr>
	<?php
}
function create_event_table_footer() {
	?></table><?php
}

function create_event_add_row($frat_letters) {
	?>
		<form method="post">
		<tr>
				<td>
					<input type="text" name="eventDate" size=12 value="YYYY-DD-MM"/>
				</td>
				<td>
					<input type="text" name="title" size=20 value="enter event title"/>
				</td>
				<td>
					<input type="submit" name="action" value="Add Event"/>
				</td>
		</tr>
		</form>

	<?php
}

function create_event_table_row($event) {
	?>
		<form method="post">
		<tr>
				<td>
					<input type="text" name="eventDate" size=20 value="<?php echo $event->eventDate; ?>"/>
				</td>
				<td>
					<input type="text" name="title" size=20 value="<?php echo $event->title; ?>"/>
				</td>
				<td>
					<input type="hidden" name="eventID" value="<?php echo $event->eventID; ?>" />				
					<input type="submit" name="action" value="Update Event"/>
					<input type="submit" name="action" value="Delete Event"/>
					<input type="submit" name="action" value="Show PNMS"/>
				</td>
		</tr>
		</form>

	<?php
}


/*** may be obsolete **/
function create_rc_frat_menu($current){
	$allpnms = get_all_frats();
?>
	<select name="fratID">
<?php
	echo "<option value=\"none\">select fraternity</option>\n";

	foreach ($allpnms as $pnm) {
		$frat_fullname = $pnm['ifcrush_frat_fullname']; 
		$frat_letters = $pnm['ifcrush_frat_letters']; 

		if ($frat_letters == $current) {
			echo "<option value=\"$frat_letters\" selected=\"selected\">$frat_fullname</option>\n";
		} else {
			echo "<option value=\"$frat_letters\">$frat_fullname</option>\n";
		}
	}
?>
	</select>
<?php
}

?>