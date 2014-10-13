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
		echo "sorry you must logged in as a fraternity to access this page.";
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
		/* List events and actions for this fraternity */
		echo "Hello $frat_letters. Here are your events. <br>";
		ifcrush_display_request_report_form();
		ifcrush_display_events( $frat_letters );
	}
}
function ifcrush_frat_handle_forms( $action,$frat_letters ){
	switch ( $action ) {
		case "Create Report":
		 	
			ifcrush_display_done_form( "Return to Event List" );
			echo "<h2>Report for $frat_letters</h2>";
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
			
		default:
			echo "unknown action : $action<br>";
	}
}
/**
 *  I want an array of arrays (or objects) where each element is a pmn with their name
 *  and netID.  I'd like to use a select so there is only one query.
 *  So, I get all the meta data for users (I don't need the user table)
 *  and create one array element for each user with meta data.
 *  Then I copy just the users I want into another array. 
  **/
function get_all_frats(){

	global $wpdb;		
	$table_name = $wpdb->prefix . "usermeta";
	$query = "select 
um1.meta_value as ifcrush_frat_letters, 
um2.meta_value as ifcrush_frat_fullname
from wp_usermeta as um1 
left join wp_usermeta as um2 on um1.user_id = um2.user_id 
where   
um1.meta_key='ifcrush_frat_letters' AND 
um2.meta_key='ifcrush_frat_fullname' AND 
um1.user_id IN (SELECT user_id FROM wp_usermeta WHERE meta_key='ifcrush_role' and meta_value='rc')
order by ifcrush_frat_fullname";

	$all_frats = $wpdb->get_results( $query );

	return($all_frats);
}
function is_rc( $user ){
	return isset( $user['ifcrush_role'] ) && ( $user['ifcrush_role'] == 'rc' );
}
function ifcrush_display_done_form( $label ){
?>
<br><form method="post">
		<input type="submit" value="<?php echo $label; ?>"/>
	</form>
<hr>
<?php
}
 
function userInFrat( $thisfrat ){
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		return true;
	} else {
		return false;
	}
}

// this is just a button to request the report
function ifcrush_display_request_report_form(){
?>
<form method="post">
	<input type="submit" name="action" value="Create Report"/>	
</form>
<?php
}
/* display frats in a list */
function ifcrush_list_frats(){
global $wpdb;	   
	$allfrats = get_all_frats();

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
		?><div>No Frats!</div><?php
	}
}

function ifcrush_list_frats_and_events(){
	global $wpdb;	   
	$allfrats = get_all_frats();

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
		?><h2>No Frats!</h2><?php
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
	$allfrats = get_all_frats();

	if ( $allfrats ) {
		create_frat_table_header();
		foreach ( $allfrats as $frat ){
			create_frat_table_row($frat);
		}	
		create_frat_table_footer();
	} else {
		?><h2>No Frats!</h2><?php
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
	$allfrats = get_all_frats();
?>
	<select name="fratID">
<?php
	echo "<option value=\"none\">select fraternity</option>\n";

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
