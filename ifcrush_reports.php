<?php
/**
 **  This file contains all the support functions for report functions
 **/
 
/**  This is the short code ifcrush_report_rusheesbyfrat entry point **/
function ifcrush_display_reports(){

	if ( ! is_user_logged_in() ) {
		echo "sorry you must be logged use reporting";
		return;
	}
	
	$current_user = wp_get_current_user();
	
	if ( is_user_an_rc( $current_user ) ){
		/* pass user info to reporting function so only
		 * that frat's info is displayed
		 */
		$fratLetters =  get_frat_letters( $current_user );
		ifcrush_display_frat_events( $fratLetters );
	} else {
		/* assume its an admin */
		ifcrush_display_events_by_frat_form( isset( $_POST['letters']) ? $_POST['letters']:"" );
		if ( isset( $_POST['reportype'] ) )
			ifcrush_report_handle_form();
	}
}

function ifcrush_display_eventsbypnms_form( $frat_letters ){
?>
	<legend>PNMS by Fraternity Event </legend>
	<fieldset>
	<form method="post">
		<?php ifcrush_create_frat_menu($frat_letters); ?>
		<input type="hidden" name="reportype" value="pnmsbyfratevent" />
		<input type="submit" value="Create Report"/>
	</form>
	</fieldset>
<?php
}

function ifcrush_display_events_by_frat_form( $frat_letters ){
?>
	<legend>Select a Fraternity to display their events</legend>
	<fieldset>
	<form method="post">
		<?php ifcrush_create_frat_menu( $frat_letters ); ?>
		<input type="hidden" name="reportype" value="displayfratevents" />
		<input type="submit" value="Create Report"/>
	</form>
	</fieldset>
<?php
}
function ifcrush_report_handle_form() { 

	global $debug;
	if ( $debug ){
			echo "<pre>"; print_r( $_POST ); echo "</pre>";
	}
		
	switch( $_POST['reportype'] ) {
		case 'pnmsbyfratevent':
			ifcrush_report_pnmsbyfratevent( $_POST['letters'] );
			break;
		case 'displayfratevents':
			ifcrush_display_frat_events( $_POST['letters'] );
			break;
		default:
			echo "no report specified";
	} 
	
} 

function ifcrush_display_frat_events( $frat ) {

	global $wpdb;

	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT * FROM $event_table_name where fratID='$frat'";
					
	$allresults = $wpdb->get_results( $query );
	//echo "<pre>"; print_r( $allresults ); echo "</pre>";
	
	if ( $allresults ) {
		foreach ( $allresults as $result ) {
		?>
			<div class="reportrow"><?php echo "$result->fratID $result->title "; ?></div>
		<?php
		}
	} 
	else { 
		?><h2>No results!</h2><?php
	}
} 
function ifcrush_create_frat_reports(){
	$allfrats = ifcrush_get_all_frats();

	if ( $allfrats ) {
		foreach ( $allfrats as $thisfrat ){
			$letters = $thisfrat->ifcrush_frat_letters;
			echo "<h3>Reports  for $letters</h3>
			  <div>";
			ifcrush_create_frat_report( $letters );
			echo "
			</div>";

		}
	}
}

/** This is a report function.  A function should be added for 
 ** each desired report, and the appropriate case should be added to handle form.
 **/ 
function ifcrush_create_frat_report( $frat_letters ) {	

	
	$no_event_data = ! ifcrush_create_frat_report_pnms_event_title( $frat_letters );
	$no_count_data = ! ifcrush_create_frat_report_pnms_event_count( $frat_letters );
	$no_bid_data   = ! ifcrush_create_frat_report_pnms_bid_count  ( $frat_letters );
	
	if ( $no_event_data && $no_count_data && $no_bid_data) {
		echo "No report data for $frat_letters";
	}
} 

function ifcrush_create_frat_report_pnms_event_title( $frat_letters ){
	global $wpdb;

	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT * FROM $eventreg_table_name 
					JOIN $event_table_name on 
					$eventreg_table_name.eventID = $event_table_name.eventID
					where fratID='$frat_letters' order by pnm_netID";
		
	$allresults = $wpdb->get_results($query);
	
	if ( $allresults ) {
		echo "<h3>Event Attendance for $frat_letters</h3>
				<table><tr><th>Net ID</th><th>Name</th><th>Event Title</th></tr>
		";
		foreach ( $allresults as $r ){
			echo "<tr><td>$r->pnm_netID</td><td>" . get_pnm_name_by_netID($r->pnm_netID) .
							"</td><td>$r->title</td></tr>
							"; 
		}
		echo "</table>
		";
		return true;
	} 
	return false;
}

function ifcrush_create_frat_report_pnms_event_count( $frat_letters ){

	global $wpdb;

	$event_table_name 	= $wpdb->prefix . "ifc_event";
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";

	$query = "SELECT pnm_netID, count(pnm_netID) as num_events from (SELECT pnm_netID FROM $eventreg_table_name 
					JOIN $event_table_name on 
					$eventreg_table_name.eventID = $event_table_name.eventID
					where fratID='$frat_letters') as eventsandreg
					group by pnm_netID";
		
	$allresults = $wpdb->get_results($query);
	
	if ( $allresults ) {
		echo "<h3>PNM Event Attendance Count for $frat_letters</h3>";
		echo "<table><tr><th>Net ID</th><th>Name</th><th>Number of events attended</th></tr>
		";
		foreach ( $allresults as $r ){
			echo "<tr><td>$r->pnm_netID</td><td>" . get_pnm_name_by_netID($r->pnm_netID) .
							"</td><td>$r->num_events</td></tr>
							"; 
		}
		echo "</table>
		";
		return true;
	} 
	return false;
}

function ifcrush_create_frat_report_pnms_bid_count( $frat_letters ){

	global $wpdb;

	$bid_table_name 	= $wpdb->prefix . "ifc_bid";

	$query = "SELECT netID from $bid_table_name where fratID='$frat_letters'";
		
	$allresults = $wpdb->get_results($query);
	
	if ( $allresults ) {
		echo "<h3>BID Report for $frat_letters</h3>
		<table><tr><th>Net ID</th><th>Name</th><th></th></tr>
		";
		foreach ( $allresults as $r ){
			echo "<tr><td>$r->netID</td><td>" . get_pnm_name_by_netID($r->netID) .
							"</td><td></td></tr>
							"; 
		}
		echo "</table>
		      ";
		return true;
	} 
	return false;
}


/** creates html for a selection menu for frats
 **/
function ifcrush_create_frat_menu( $current ){
	$frats = ifcrush_get_all_frats();

	if ( !$frats ) {
		echo "<h3>No Frats!?!</h3>";
		return;
	}
?>
	<select name="letters">
		<option value="none">select fraternity</option>
<?php
	foreach ( $frats as $frat ) {
		$ifcrush_frat_letters = $frat->ifcrush_frat_letters;
		$ifcrush_frat_fullname = $frat->ifcrush_frat_fullname;
		if ( $ifcrush_frat_letters == $current ) {
			echo "<option value=\"$ifcrush_frat_letters\" selected=\"selected\">$ifcrush_frat_fullname</option>\n";
		} else {
			echo "<option value=\"$ifcrush_frat_letters\">$ifcrush_frat_fullname</option>\n";
		}
	}
?>
	</select>
<?php
}

/* 
 * echos the total number of pnms 
 */
function ifcrush_report_total_rushees(){
	global $wpdb;
	$pnm_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta where meta_key = 'ifcrush_role' and meta_value='pnm'" );
		
	$bid_table_name 	= $wpdb->prefix . "ifc_bid";
	$bid_count = $wpdb->get_var( "SELECT COUNT(*) FROM $bid_table_name" );

	echo "<h3>Total Rushees</h3>";	
	echo "$pnm_count Total <br>";
	echo "$bid_count with bids <br><br>";
}



/*
*/
function ifcrush_create_rush_class_reports(){
	$frats = ifcrush_get_all_frats();
	foreach ( $frats as $frat ) {
		
		ifcrush_create_rush_table_for_frat($frat->ifcrush_frat_letters);
	}

}
function ifcrush_create_rush_table_for_frat($frat_letters){
	global $wpdb;
	$newquery = "select * from (". query_PNMS_and_ALL_data() .") 
					where where bidstatus = '$frat_letters' 
					order by last_name, first_name";
	$allresults = $wpdb->get_results("select * from (". query_PNMS_and_ALL_data() . ") as p where bidstatus = '$frat_letters' ");

	
	if ( $allresults ) {
		echo "
			  <h3>Class for ". $frat_letters ."</h3>
			  <div>";
		echo "<table><tr><th>netID</th><th>last name</th><th>first name</th>
					<th>residence</th><th>yog</th><th>school</th><th>bid</th></tr>
			";
		foreach ( $allresults as $r ) {
			ifcrush_pnm_create_row($r);
		}		
		echo "</table>
		</div>";
	} else {
		echo "<h3>No  new members for  ". $frat_letters ."</h3><div></div>
		";
	}
}


/* 
 * echos the total number of pnms by their current residence
 */
function ifcrush_report_rushees_by_residence(){
	global $wpdb;
	$query = "select ifcrush_residence, count(ifcrush_residence) as num from (";
	$query .= query_PNMS_and_ALL_data();
	$query .= ") as p group by ifcrush_residence";
		
	$allresults = $wpdb->get_results($query);

	echo "<h3>Rushees by Residence</h3>";
	if ( $allresults ) {
		foreach ( $allresults as $r ){
				echo $r->num . "  " . $r->ifcrush_residence . "<br>"; 
		}
	} else {
		echo "no results for rushees by residence";
	}
	echo "<br><br>";
	
}
/* 
 * echos the total number of pnms by their current residence
 */
function ifcrush_report_rushees_by_school(){
	global $wpdb;
	$query = "select ifcrush_school, count(ifcrush_school) as num from (";
	$query .= query_PNMS_and_ALL_data();
	$query .= ") as p group by ifcrush_school";
		
	$allresults = $wpdb->get_results($query);

	echo "<h3>Rushees by School</h3>";
	if ( $allresults ) {
		foreach ( $allresults as $r ){
				echo $r->num . "  " . $r->ifcrush_school . "<br>"; 
		}
	} else {
		echo "no results for rushees by school";
	}
	echo "<br><br>";
	
}
/* 
 * echos the total number of pnms by their current residence
 */
function ifcrush_report_rushees_by_yog(){
	global $wpdb;
	$query = "select ifcrush_yog, count(ifcrush_yog) as num from (";
	$query .= query_PNMS_and_ALL_data();
	$query .= ") as p group by ifcrush_yog";
		
	$allresults = $wpdb->get_results($query);

	echo "<h3>Rushees by Year</h3>";
	if ( $allresults ) {
		foreach ( $allresults as $r ){
				echo $r->num . "  " . $r->ifcrush_yog . "<br>"; 
		}
	} else {
		echo "no results for rushees by year of graduation";
	}
	echo "<br><br>";
	
}

function ifcrush_report_dump_pnms(){
	global $wpdb;
	$allresults = $wpdb->get_results(query_PNMS_and_ALL_data() . " order by last_name, first_name");

	echo "<h3>All Data</h3>";
	echo "<table><tr><th>netID</th><th>last name</th><th>first name</th>
		<th>residence</th><th>yog</th><th>school</th><th>bid</th></tr>
		";
	if ( $allresults ) {
		foreach ( $allresults as $r ){
			ifcrush_pnm_create_row($r);
		}		
		echo "</table>
		";
	} else {
		echo "no rushees";
	}
}
function ifcrush_pnm_create_row($r){
	$bidstatus = isset($r->bidstatus) ?  $r->bidstatus : "none";
	echo "<tr>
	<td>".$r->ifcrush_netID."</td> 
	<td>".$r->last_name."</td> 
	<td>".$r->first_name."</td> 
	<td>".$r->ifcrush_residence."</td> 
	<td>".$r->ifcrush_yog."</td> 
	<td>".$r->ifcrush_school."</td> 
	<td>$bidstatus</td>
	</tr>
	"; 
		
}

function query_PNMS_and_ALL_data(){
	global $wpdb;
		
	$bid_table_name 	= $wpdb->prefix . "ifc_bid";
	$usermeta = $wpdb->usermeta;

	$query_PNMS_and_ALL_data = "
	select um1.user_id, 
		um1.meta_value as ifcrush_netID, 
		um2.meta_value as last_name, 
		um3.meta_value as first_name, 
		um4.meta_value as ifcrush_residence, 
		um5.meta_value as ifcrush_yog,
		um6.meta_value as ifcrush_school,
		fratID as bidstatus
		from 
			wp_usermeta as um1 
			left join $usermeta as um2 on um1.user_id = um2.user_id 
			left join $usermeta as um3 on um1.user_id = um3.user_id 
			left join $usermeta as um4 on um1.user_id = um4.user_id 
			left join $usermeta as um5 on um1.user_id = um5.user_id 
			left join $usermeta as um6 on um1.user_id = um6.user_id 
			left join $bid_table_name on netID = um1.meta_value
		WHERE ( um1.meta_key LIKE 'ifcrush_netID' 
			AND um2.meta_key LIKE 'last_name' 
			AND um3.meta_key LIKE 'first_name' 
			AND um4.meta_key LIKE 'ifcrush_residence' 
			AND um5.meta_key LIKE 'ifcrush_yog' 
			AND um6.meta_key LIKE 'ifcrush_school' )";
			
	global $debug;
	if ( $debug ) {
		echo "The big query is $query";
		echo "<pre>";
			print_r( $query_PNMS_and_ALL_data );
		echo "</pre>";
	}
		
	return( $query_PNMS_and_ALL_data );
}



/* Total PNMS 
 * PNMS - total, with bids, without
 * PNMS by residence -  total, with bids, without
 * PMNS by YOG -  total, with bids, without
 * PNMS by School -  total, with bids, without
 */ 
 ?>