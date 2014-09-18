<?php
/**
 **  This file contains all the support functions for report functions
 **/

function ifcrush_report_handle_form() { 
// 
	global $debug;
	if ($debug){
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
	rusheesbyfrat("LXA");

// 	switch($_POST['']) {
// 		case 'eventsbyfrat':
// 			eventsbyfrat($_POST['frat']);
// 			break;
// 		case 'rusheesbyfrat':
// 			rusheesbyfrat($_POST['frat']);
// 			break;
// 		default:
// 			echo "no report specified";
// 	}
	
} // handles changes to db from the front end

function ifcrush_report_rusheesbyfrat() {

	global $wpdb;

	if (!isset($_POST['letters'])) $frat = "LXA";

	$rushee_table_name 	= $wpdb->prefix . "ifc_rushee";
	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT * FROM $rushee_table_name join $eventreg_table_name on netID=rusheeID
					JOIN $event_table_name on 
					$eventreg_table_name.eventID = $event_table_name.eventID
					where fratID='$frat'";
					
	$allresults = $wpdb->get_results($query);
	
	if ($allresults) {
		foreach ($allresults as $result) {
		?>
			<div class="reportrow"><?php echo "$result->fratID-$result->title $result->firstName $result->lastName"; ?></div>
		<?php
		}
	} 
	else { 
		?><h2>No results!</h2><?php
	}
} // updates a rushee with a matching netID if updateRushee is tagged


?>