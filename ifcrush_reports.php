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
			ifcrush_create_frat_report( $letters );
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
		echo "<h3>No report data for $frat_letters</h3><div></div>";
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
		echo "<h3>PNM Event Attendance List for $frat_letters</h3>
			  <div>";
		echo "<table><tr><th>Net ID</th><th>Name</th><th>Event Title</th></tr>";
		foreach ( $allresults as $r ){
			echo "<tr><td>$r->pnm_netID</td><td>" . get_pnm_name_by_netID($r->pnm_netID) .
							"</td><td>$r->title</td></tr>"; 
		}
		echo "</table>
		      </div>
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
		echo "<h3>PNM Event Attendance Count for $frat_letters</h3>
		      <div>";
		echo "<table><tr><th>Net ID</th><th>Name</th><th>Number of events attended</th></tr>";
		foreach ( $allresults as $r ){
			echo "<tr><td>$r->pnm_netID</td><td>" . get_pnm_name_by_netID($r->pnm_netID) .
							"</td><td>$r->num_events</td></tr>"; 
		}
		echo "</table>
		      </div>
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
		      <div>";
		echo "<table><tr><th>Net ID</th><th>Name</th><th></th></tr>";
		foreach ( $allresults as $r ){
			echo "<tr><td>$r->netID</td><td>" . get_pnm_name_by_netID($r->netID) .
							"</td><td></td></tr>"; 
		}
		echo "</table>
		      </div>
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

/**** The monster query   *****
$query_PNMS_and_ALL_data = "
select um1.user_id, 
	   um1.meta_value as ifcrush_netID,
	   um2.meta_value as last_name, 
	   um3.meta_value as first_name,
	   um4.meta_value as ifcrush_residence,
	   um5.meta_value as ifcrush_yog
	   um6.meta_value as ifcrush_school  from 
	   				      $wpdb->usermeta as um1 
				left join $wpdb->usermeta as um2 on um1.user_id = um2.user_id 
				left join $wpdb->usermeta as um3 on um1.user_id = um3.user_id 
				left join $wpdb->usermeta as um4 on um1.user_id = um4.user_id 
				left join $wpdb->usermeta as um5 on um1.user_id = um5.user_id 
				left join $wpdb->usermeta as um36on um1.user_id = um6.user_id 
				
					WHERE ( um1.meta_key LIKE 'ifcrush_netID' 
							AND um2.meta_key LIKE 'last_name' 
							AND um3.meta_key LIKE 'first_name' 
							AND um4.meta_key LIKE 'ifcrush_residence'  
							AND um5.meta_key LIKE 'ifcrush_yog'  
							AND um6.meta_key LIKE 'ifcrush_school'  
						)
						AND um1.user_id IN 
						(SELECT user_id FROM $wpdb->usermeta 
							WHERE meta_key LIKE 'ifcrush_role' 
								AND meta_value LIKE 'pnm')) as p;

$query_PNMS_with_BIDS_and_ALL_data = "
select um1.user_id, 
	   um1.meta_value as ifcrush_netID,
	   um2.meta_value as last_name, 
	   um3.meta_value as first_name,
	   um4.meta_value as ifcrush_residence,
	   um5.meta_value as ifcrush_yog,
	   um6.meta_value as ifcrush_school  from 
	   				      $wpdb->usermeta as um1 
				left join $wpdb->usermeta as um2 on um1.user_id = um2.user_id 
				left join $wpdb->usermeta as um3 on um1.user_id = um3.user_id 
				left join $wpdb->usermeta as um4 on um1.user_id = um4.user_id 
				left join $wpdb->usermeta as um5 on um1.user_id = um5.user_id 
				left join $wpdb->usermeta as um6 on um1.user_id = um6.user_id 
				
					WHERE ( um1.meta_key LIKE 'ifcrush_netID' 
							AND um2.meta_key LIKE 'last_name' 
							AND um3.meta_key LIKE 'first_name' 
							AND um4.meta_key LIKE 'ifcrush_residence'  
							AND um5.meta_key LIKE 'ifcrush_yog'  
							AND um6.meta_key LIKE 'ifcrush_school'  
						)
						AND um1.user_id IN 
						(SELECT user_id FROM $wpdb->usermeta 
							WHERE meta_key LIKE 'ifcrush_role' 
								AND meta_value LIKE 'pnm')) as p
			WHERE ifcrush_netID IN (SELECT netID from $ifc_bid_table);
****/

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
		<th>residence</th><th>yog</th><th>school</th><th>bid</th></tr>";
	if ( $allresults ) {
		foreach ( $allresults as $r ){
			ifcrush_pnm_create_row($r);
		}		
		echo "</table>";
	} else {
		echo "no rushees";
	}
	echo "<br><br>";
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
	<td>$bidstatus</td>"; 
		
}

function query_PNMS_and_ALL_data(){
	global $wpdb;
		$bid_table_name 	= $wpdb->prefix . "ifc_bid";

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
			left join wp_usermeta as um2 on um1.user_id = um2.user_id 
			left join wp_usermeta as um3 on um1.user_id = um3.user_id 
			left join wp_usermeta as um4 on um1.user_id = um4.user_id 
			left join wp_usermeta as um5 on um1.user_id = um5.user_id 
			left join wp_usermeta as um6 on um1.user_id = um6.user_id 
			left join wp_ifc_bid on netID = um1.meta_value
		WHERE ( um1.meta_key LIKE 'ifcrush_netID' 
			AND um2.meta_key LIKE 'last_name' 
			AND um3.meta_key LIKE 'first_name' 
			AND um4.meta_key LIKE 'ifcrush_residence' 
			AND um5.meta_key LIKE 'ifcrush_yog' 
			AND um6.meta_key LIKE 'ifcrush_school' )";
	
	return($query_PNMS_and_ALL_data);
}



/* Total PNMS 
 * PNMS - total, with bids, without
 * PNMS by residence -  total, with bids, without
 * PMNS by YOG -  total, with bids, without
 * PNMS by School -  total, with bids, without
 */ 
 ?>