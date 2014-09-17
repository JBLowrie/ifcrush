<?php
/**
 **  This file contains all the support functions for the table ifcrush_eventreg
 **/


function ifcrush_eventreg_handle_form() { 

	echo "<pre>"; print_r($_POST); echo "</pre>";
	
	if ( isset($_POST['addEventreg']) ){
		// This is cuz addEvent doesn't take an eventID
		$thiseventreg = array( 
			'rusheeID' =>  $_POST['rusheeID'],
			'eventID'  	=>  $_POST['eventID'],
		); // put the form input into an array
		addEventreg($thiseventreg);
	} else {
			
		//delete, update
		if ( isset($_POST['updateEventreg']) ){
			$thiseventreg = array( 
				'rusheeID'	=>  $_POST['rusheeID'],
				'eventID' =>  $_POST['eventID'],
			); // put the form input into an array
			updateEventreg($thiseventreg);
			
		} else if ( isset($_POST['deleteEventreg']) ){
			$thiseventreg = array( 
				'rusheeID'	=>  $_POST['rusheeID'],
				'eventID' =>  $_POST['eventID'],
			); // put the form input into an array
			deleteEventreg($thiseventreg);
		}
	}
} // handles changes to db from the front end

/* Event_handler_form helpers */
function addEventreg($thiseventreg) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$rows_affected = $wpdb->insert($table_name, $thiseventreg);
	
	if ($rows_affected == 0) {
		echo "INSERT ERROR for " . $thiseventreg['rusheeID']. $thiseventreg['eventID'] ;
	}
	return $rows_affected;
} // adds a event to the table if addEvent is tagged

function updateEventreg($thiseventreg) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$where = array('eventID' => $thiseventreg['eventID']);
	$wpdb->update( $table_name, $thiseventreg, $where );
} // updates a event with a matching netID if updateEvent is tagged

function deleteEventreg($thiseventreg) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$wpdb->delete( $table_name, $thiseventreg);
} // deletes a event if deleteEvent is tagged


//Display event table
function ifcrush_display_eventreg_table() {

	ifcrush_eventreg_handle_form(); // handle updates, adds, deletes
	
	global $wpdb;
	
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";
	$query = "SELECT * FROM $eventreg_table_name";
	$alleventregs = $wpdb->get_results($query);
	
	if ($alleventregs) {
		create_eventreg_table_header(); // make a table header
		foreach ($alleventregs as $eventreg) { // populate the rows with db info
			//echo "<pre>"; print_r($event); echo "</pre>";

			create_eventreg_table_row($eventreg);
		}
		create_eventreg_add_row();
		create_eventreg_table_footer(); // end the table
	} 
	else { 
		?><h2>No event regs!</h2><?php
		create_eventreg_table_header(); // make a table header
		create_eventreg_add_row();
		create_eventreg_table_footer(); // end the table
	}
}

function create_eventreg_table_header() {
	?>
		<table>
		<tr>
			<th>RusheeID</th>
			<th>eventID</th>
		</tr>
	<?php
}
function create_eventreg_table_footer() {
	?></table><?php
}

function create_rushee_netIDs_menu($current){
	global $wpdb;

	$rushee_table_name = $wpdb->prefix . "ifc_rushee";    

	$query = "SELECT netID FROM $rushee_table_name group by netID";
	$rushees = $wpdb->get_results($query);
?>
	<select name="rusheeID">
<?php
	foreach ($rushees as $rushee) {
		//echo "comparing $guesttype $current";
		if ($rushee->netID == $current) {
			echo "<option value=\"$rushee->netID\" selected=\"selected\">$rushee->netID</option>\n";
		} else {
			echo "<option value=\"$rushee->netID\">$rushee->netID</option>\n";
		}
	}
?>
	</select>
<?php
}
function create_event_eventIDs_menu($current){
	global $wpdb;

	$event_table_name = $wpdb->prefix . "ifc_event";    

	$query = "SELECT eventID FROM $event_table_name group by eventID";
	$events = $wpdb->get_results($query);
?>
	<select name="eventID">
<?php
	foreach ($events as $event) {
		//echo "comparing $guesttype $current";
		if ($event->eventID == $current) {
			echo "<option value=\"$event->eventID\" selected=\"selected\">$event->eventID</option>\n";
		} else {
			echo "<option value=\"$event->eventID\">$event->eventID</option>\n";
		}
	}
?>
	</select>
<?php
}

function create_eventreg_add_row() {
	?>
		<form method="post">
			<tr>
				<td> <!-- create a selection menu with Frats -->
					<?php create_rushee_netIDs_menu("   "); ?>
				</td>
				<td>
					<?php create_event_eventIDs_menu("   "); ?>
				</td>
				<td>
					<input type="submit" name="addEventreg" value="Add Eventreg"/>
				</td>
			</tr>
		</form>

	<?php
}

function create_eventreg_table_row($event) {
	?>
		<form method="post">
			<tr>
				<td> <!-- create a selection menu with Frats -->
					<?php create_rushee_netIDs_menu($eventreg->rusheeID); ?>
				</td>
				<td> <!-- create a selection menu with Frats -->
					<?php create_event_eventIDs_menu($eventreg->eventID); ?>
				</td>
				<td>			
					<input type="submit" name="updateEventreg" value="Update"/><input type="submit" name="deleteEventreg" value="Delete"/>
				</td>
			</tr>
		</form>

	<?php
}
 
 
 
 
 
 ?>