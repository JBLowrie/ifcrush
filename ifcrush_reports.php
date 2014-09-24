<?php
/**
 **  This file contains all the support functions for report functions
 **/
 
/**  This is the short code ifcrush_report_rusheesbyfrat entry point **/
function ifcrush_display_reports(){

	if (!is_user_logged_in()) {
		echo "sorry you must be logged use reporting";
		return;
	}
	
	ifcrush_display_report_form((isset($_POST['letters'])) ? $_POST['letters']: "");
	
	if (isset($_POST['letters'])) 
		ifcrush_report_handle_form($_POST['letters']);

}

function ifcrush_display_report_form($frat_letters){
?>
	<form method="post">
		<?php ifcrush_create_frat_letter_menu($frat_letters); ?>
		<input type="submit" value="Create Report"/>
	</form>
<?php
}
function ifcrush_report_handle_form($frat) { 
// 
	global $debug;
	if ($debug){
			echo "ifcrush_report_handle_form: $frat";
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
	
	ifcrush_report_display_pnmsbyfrat($frat);
	
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
	
} 


/** This is a report function.  A function should be added for 
 ** each desired report, and the appropriate case should be added to handle form.
 **/ 
function ifcrush_report_display_pnmsbyfrat($frat) {

	global $wpdb;

	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT * FROM $eventreg_table_name 
					JOIN $event_table_name on 
					$eventreg_table_name.eventID = $event_table_name.eventID
					where fratID='$frat'";
					
	$allresults = $wpdb->get_results($query);
	//echo "<pre>"; print_r($allresults); echo "</pre>";
	
	if ($allresults) {
		foreach ($allresults as $result) {
		?>
			<div class="reportrow"><?php echo "$result->fratID-$result->title $result->pnm_netID"; ?></div>
		<?php
		}
	} 
	else { 
		?><h2>No results!</h2><?php
	}
} 

function ifcrush_create_frat_letter_menu($current){
	global $wpdb;
	$frat_table_name = $wpdb->prefix . "ifc_fraternity";    

	$query = "SELECT letters, fullname FROM $frat_table_name group by letters";
					
	$frats = $wpdb->get_results($query);
?>
	<select name="letters">
<?php
	echo "<option value=\"none\">select fraternity</option>\n";
	foreach ($frats as $frat) {
		//echo "comparing $guesttype $current";
		if ($frat->letters == $current) {
			echo "<option value=\"$frat->letters\" selected=\"selected\">$frat->fullname</option>\n";
		} else {
			echo "<option value=\"$frat->letters\">$frat->fullname</option>\n";
		}
	}
?>
	</select>
<?php
}
?>