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

function ifcrush_event_handle_form() { 

	global $debug;
	if ($debug){
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
	
	if ( isset($_POST['addEvent']) ){
		// This is cuz addEvent doesn't take an eventID
		$thisevent = array( 
			'eventDate' =>  $_POST['eventDate'],
			'title'  	=>  $_POST['title'],
			'fratID' 	=>  $_POST['fratID'], 
		); // put the form input into an array
		addEvent($thisevent);
	} else {
			
		//delete, update
		if ( isset($_POST['updateEvent']) ){
			$thisevent = array( 
				'eventID'	=>  $_POST['eventID'],
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['title'],
				'fratID' 	=>  $_POST['fratID'], 
			); // put the form input into an array
			updateEvent($thisevent);
			
		} else if ( isset($_POST['deleteEvent']) ){
			$thisevent = array( 
				'eventID'	=>  $_POST['eventID'],
				'eventDate' =>  $_POST['eventDate'],
				'title'  	=>  $_POST['title'],
				'fratID' 	=>  $_POST['fratID'], 
			); // put the form input into an array
			deleteEvent($thisevent);
		}
	}
} // handles changes to db from the front end

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


//Display event table
function ifcrush_display_event_table() {

	ifcrush_event_handle_form(); // handle updates, adds, deletes
	
	global $wpdb;
	
	$event_table_name = $wpdb->prefix . "ifc_event";
	$query = "SELECT * FROM $event_table_name";
	$allevents = $wpdb->get_results($query);
	
	if ($allevents) {
		create_event_table_header(); // make a table header
		foreach ($allevents as $event) { // populate the rows with db info
			//echo "<pre>"; print_r($event); echo "</pre>";

			create_event_table_row($event);
		}
		create_event_add_row();
		create_event_table_footer(); // end the table
	} 
	else { 
		?><h2>No events!</h2><?php
		create_event_table_header(); // make a table header
		create_event_add_row();
		create_event_table_footer(); // end the table
	}
}

function create_event_table_header() {
	?>
		<table>
		<tr>
			<th>Date</th>
			<th>Title</th>
			<th>Frat</th>
			<th>Actions</th>

		</tr>
	<?php
}
function create_event_table_footer() {
	?></table><?php
}

function create_frat_letters_menu($current){
	global $wpdb;

	$frat_table_name = $wpdb->prefix . "ifc_fraternity";    

	$query = "SELECT letters FROM $frat_table_name group by letters";
	$frats = $wpdb->get_results($query);
?>
	<select name="fratID">
<?php
	foreach ($frats as $frat) {
		//echo "comparing $guesttype $current";
		if ($frat->letters == $current) {
			echo "<option value=\"$frat->letters\" selected=\"selected\">$frat->letters</option>\n";
		} else {
			echo "<option value=\"$frat->letters\">$frat->letters</option>\n";
		}
	}
?>
	</select>
<?php
}
function create_event_add_row() {
	?>
		<form method="post">
		<tr>
				<td>
					<input type="text" name="eventDate" size=6 value="year-date-month"/>
				</td>
				<td>
					<input type="text" name="title" size=15 value="enter title"/>
				</td>
				<td> <!-- create a selection menu with Frats -->
					<?php create_frat_letters_menu("   "); ?>
				</td>
				<td>
					<input type="submit" name="addEvent" value="Add Event"/>
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
				<td> <!-- create a selection menu with Frats -->
					<?php create_frat_letters_menu($event->fratID); ?>
				</td>
				<td>
					<input type="hidden" name="eventID" value="<?php echo $event->eventID; ?>" />				
					<input type="submit" name="updateEvent" value="Update"/><input type="submit" name="deleteEvent" value="Delete"/>
				</td>
		</tr>
		</form>

	<?php
}

?>