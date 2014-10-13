<?php
/**
 **  This file contains all the support functions for the table ifcrush_events
 **/
 

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
		create_event_add_row($frat_letters);

		foreach ($allevents as $event) { // populate the rows with db info
			//echo "<pre>"; print_r($event); echo "</pre>";

			create_event_table_row($event);
		}
		create_event_table_footer(); // end the table
	} 
	else { 
		?><h3>No events.  Add one!</h3><?php
		create_event_table_header(); // make a table header
		create_event_add_row($frat_letters);
		create_event_table_footer(); // end the table
	}
}

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
				'title'  	=>  $_POST['eventTitle'],
				'fratID'	=>	$frat_letters
			); // put the form input into an array
			updateEvent($thisevent);
			break;
		case "Delete Event":
			$thisevent = array( 
				'eventID'	=>  $_POST['eventID'],
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['eventTitle'],
				'fratID'	=>	$frat_letters
			); // put the form input into an array
			deleteEvent($thisevent);
			break;
		case "Add Event":
			$thisevent = array( 
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['eventTitle'],
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
		<div id="eventError"></div>
		<div class="ifcrushtable">
			<div class="ifcrushtablerow">
				<div class="ifcrushtablecellnarrow">Date</div>
				<div class="ifcrushtablecellnarrow">Title</div>
				<div class="ifcrushtablecellauto"></div>
			</div>
	<?php
}
function create_event_table_footer() {
	?></div><?php
}

function create_event_add_row($frat_letters) {
	?>
		<div class="ifcrushtableaddrow">
			<form method="post" class="eventForm">
				<div class="ifcrushtablecellnarrow">
					<input type="text" name="eventDate" id="addDate" class="datepicker" value="select date">
				</div>
				<div class="ifcrushtablecellnarrow">
					<input type="text" name="eventTitle" id="addEventTitle" size=20 value="enter event title"/>
				</div>
				<div class="ifcrushtablecellauto">
					<input type="submit" name="action" id="addEventButton" value="Add Event"/>
				</div>
			</form>
		</div><!-- end ifcrushtableaddrow -->
	<?php
}

function create_event_table_row($event) {
	?>
		<div class="ifcrushtablerow">
			<form method="post" class="eventForm">
				<div class="ifcrushtablecellnarrow">
					<input type="text" name="eventDate" class="datepicker" value="<?php echo $event->eventDate;?>"/>
				</div>
				<div class="ifcrushtablecellnarrow">
					<input type="text" name="eventTitle" size=20 value="<?php echo $event->title; ?>"/>
					<input type="hidden" name="eventID" value="<?php echo $event->eventID; ?>" />
				</div>				
				<div class="ifcrushtablecellauto">
					<input type="submit" name="action" value="Update Event"/>
					<input type="submit" name="action" value="Delete Event"/>
					<input type="submit" name="action" value="Show PNMS"/>
				</div>
			</form>
		</div>
	<?php
}
?>