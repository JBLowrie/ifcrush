<?php
/**
 **  This file contains all the support functions for the table ifcrush_frat
 **/
 /**
  * ifcrush_frat() shortcode entry point
  * Fraternities can view their events, create events, register PNMs
  *
  * KBL todo - on failure to access due to login, maybe we should add a link to the
  * login page
  
  **/
function ifcrush_frat(){
	global $debug;
	if ( $debug ) echo "[ifcrush_frat] ";
	
	if ( false == is_user_logged_in() ) {
		echo "sorry you must logged in as a recruitment chair to use this page.";
		return;
	}
	
	$current_user = wp_get_current_user();
	if ( is_user_an_rc( $current_user ) ){
		$frat_letters =  get_frat_letters( $current_user );
	} else {
		echo "sorry you must be a recruitment chair to use this page";
		return;
	}
	
	/* Now I know who I am.
	 * Lets see if there are any forms to handle 
	 */
	if ( isset( $_POST['action'] ) ){
		ifcrush_frat_handle_forms( $_POST['action'], $frat_letters );

	} else {	
		/* Create the options for a fraternity.*/
		echo "Hello $frat_letters.";
		ifcrush_frat_show_options();
		
	}
}
function ifcrush_frat_handle_forms( $action,$frat_letters ){
	ifcrush_frat_show_options();

	switch ( $action ) {
		case "View Reports":
			//ifcrush_display_done_form( "Return to Event List" );
			echo "<h2>Reports for $frat_letters</h2>";
			echo '<div id="accordion">';
			ifcrush_create_frat_report( $frat_letters );
			echo '</div>';
			break;
		
		case "Update Event":
		case "Delete Event":
		case "Add Event":
			ifcrush_event_handle_form( $frat_letters );
			ifcrush_display_events( $frat_letters );
			break;
			
		case "Show PNMS":
			ifcrush_display_done_form( "Return to Event List" );
			ifcrush_display_register_pnm_at_event( $_POST['eventID'], $frat_letters );
			break;
			
		case "Delete Event Reg":
			ifcrush_display_done_form( "Finished showing PNMS" );
			/* add function call to delete this event registration */
			ifcrush_eventreg_handle_form( "delete registration" );
			ifcrush_display_register_pnm_at_event( $_POST['eventID'], $frat_letters );
			break;
					
		case "Register this PMN":
			ifcrush_display_done_form( "Finished showing PNMS" );
			/* add this specific PMN to the event, and then display everything */
			ifcrush_eventreg_handle_form( "add registration" );
			ifcrush_display_register_pnm_at_event( $_POST['eventID'] );		
			break;
			
		case "Register PMNs":
			ifcrush_display_done_form( "Finished showing PNMS" );
			/* display the pmns registered for this event and a form for one more */
			ifcrush_display_register_pnm_at_event( $_POST['eventID'] );
			break;
			
		case "View Event Table":
			echo "<h2>Event Table for $frat_letters</h2>";
			ifcrush_display_events( $frat_letters );
			break;
			
		case "View Bid Offer Form":
			ifcrush_bid_show_bid_form( $frat_letters );
			break;
			
		case "Create Bid":
			ifcrush_bid_handle_bid_form( $frat_letters );
			break;
			
		default:
			echo "unknown action : $action<br>";
			//die();
	}
}

/**
 *  I want an array of arrays (or objects) where each element is a pmn with their name
 *  and netID.  I'd like to use a select so there is only one query.
 *  So, I get all the meta data for users (I don't need the user table)
 *  and create one array element for each user with meta data.
 *  Then I copy just the users I want into another array. 
  **/
function ifcrush_get_all_frats(){

	global $wpdb;		
	$table_name = $wpdb->prefix . "usermeta";
	$query = "select 
um1.meta_value as ifcrush_frat_letters, 
um2.meta_value as ifcrush_frat_fullname
from $table_name as um1 
left join $table_name as um2 on um1.user_id = um2.user_id 
where   
um1.meta_key='ifcrush_frat_letters' AND 
um2.meta_key='ifcrush_frat_fullname' AND 
um1.user_id IN (SELECT user_id FROM $table_name WHERE meta_key='ifcrush_role' and meta_value='rc')
order by ifcrush_frat_fullname";

	$all_frats = $wpdb->get_results( $query );

	return($all_frats);
}

function is_rc( $user ){
	return isset( $user['ifcrush_role'] ) && ( $user['ifcrush_role'] == 'rc' );
}

function ifcrush_frat_show_options(){
?>
<form method="post">
	<input type="submit" name="action" value="View Reports"/>	
	<input type="submit" name="action" value="View Event Table"/>	
	<input type="submit" name="action" value="View Bid Offer Form"/>	
</form>
<?php
}

function ifcrush_display_done_form( $label ){
?>
<br><form method="post">
		<input type="submit" value="<?php echo $label; ?>"/>
	</form>
<hr>
<?php
}
 
/* display frats in a list */
function ifcrush_list_frats(){
global $wpdb;	   
	$allfrats = ifcrush_get_all_frats();

	echo "<h3>List of Fraternities</h3>";
	if ( $allfrats ) {
		echo "<div>";
		echo "<table>";
		echo "<tr><th>Letters</th><th>Fullname</th><th></th></tr>";

		foreach ( $allfrats as $thisfrat ){
			$letters = $thisfrat->ifcrush_frat_letters;
			$fullname = $thisfrat->ifcrush_frat_fullname;
			echo "<tr><td>$letters</td><td>$fullname</td><td></td></tr>";
		}	
		echo "</table></div>";
	} else {
		?><div>No Frats to list!</div><?php
	}
}

function ifcrush_list_frats_and_events(){
	global $wpdb;	   
	$allfrats = ifcrush_get_all_frats();

	if ( $allfrats ) {
		echo "<h3>Fraternities and Events</h3>";

		echo "<table>";
		echo "<tr><th>Letters</th><th>Fullname</th><th></th></tr>";

		foreach ( $allfrats as $thisfrat ){
			$letters = $thisfrat->ifcrush_frat_letters;
			$fullname = $thisfrat->ifcrush_frat_fullname;
			echo "<tr><td>$letters</td><td>$fullname</td><td>Add Report</td></tr>";
		}	
		echo "</table>";
	} else {
		?><h2>No Frats to list events for!</h2><?php
	}
}

/** 
 * ifcrush_display_frats - this is an admin function
 **/
function ifcrush_display_frats(){
	// if ( false == is_user_admin() ) {
// 		echo "sorry you must be an admin to view fraternities";
// 		return;
// 	}
	
	global $wpdb;	   
	$allfrats = ifcrush_get_all_frats();

	if ( $allfrats ) {
		create_frat_table_header();
		foreach ( $allfrats as $frat ){
			create_frat_table_row($frat);
		}	
		create_frat_table_footer();
	} else {
		?><h2>No Frats to display!</h2><?php
	}
}

function create_frat_table_header(){
	?>
	<div class="ifcrushtable">
		<div class="ifcrushtablerow">
				<div class="ifcrushtablecellnarrow">
					Fraternity
				</div>
				<div class="ifcrushtablecellnarrow">
					Recruitment chair
				</div>
				<div class="ifcrushtablecellauto">
					Action
				</div>
 		</div><!--end ifcrushtablerow-->
	<?php
}
function create_frat_table_footer(){
	?>
	</div><!-- end frat table-->  
<?php
}

function create_frat_table_row($thisfrat){
	$letters = $thisfrat->ifcrush_frat_letters;
	$fullname = $thisfrat->ifcrush_frat_fullname;
	?>
	<br>
	<div class="ifcrushtablerow">
			<div class="ifcrushtablecellnarrow">
				<?php
					echo $fullname;
				?>
			</div><!-- end fratid-->
			<div class="ifcrushtablecellnarrow">
				<?php
					echo $letters;
				?>
			</div><!-- end rushchair-->
			<div class="ifcrushtableauto">
				
			</div><!-- end frataction-->
	</div><!-- end ifcrushtablerow-->
<?php
}

/** just keeping this around **/
function create_rc_frat_menu( $current ){
	$allfrats = ifcrush_get_all_frats();
?>
	<select name="fratID">
	<option value="none">select fraternity</option>
<?php
	foreach ( $allfrat as $thisfrat ) {
		$letters = $thisfrat->ifcrush_frat_letters;
		$fullname = $thisfrat->ifcrush_frat_fullname;
		if ( $frat_letters == $current ) {
			echo "<option value=\"$letters\" selected=\"selected\">$fullname</option>\n";
		} else {
			echo "<option value=\"$letters\">$fullname</option>\n";
		}
	}
?>
	</select>
<?php
}
