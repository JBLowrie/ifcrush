<?php
/**
 **  This file contains all the support functions for the table ifcrush_frat
 **/
 /**
  * ifcrush_frat() shortcode entry point
  * Fraternities can view their events, create events, register PNMs
  *
  **/
function ifcrush_frat(){
	global $debug;
	//if ($debug) echo "[ifcrush_frat] ";
	
	if (!is_user_logged_in()) {
		echo "sorry you must logged in as a fraternity to access this page.";
		return;
	}
	
	$current_user = wp_get_current_user();
	if (is_user_an_rc($current_user)){
		$frat_letters =  get_frat_letters($current_user);
	} else {
		echo "sorry you must be a recruitment chair to use this page";
		return;
	}
	
	/* Now I know who I am.
	 * Lets see if there are any forms to handle 
	 */
	if ( isset($_POST['action']) ){
	
		ifcrush_frat_handle_forms($_POST['action'], $frat_letters);

	} else {	
		/* List events and actions for this fraternity */
		echo "Hello $frat_letters.";
		ifcrush_display_request_report_form();
		echo "Here are your events.";
		ifcrush_display_events($frat_letters);
	}
	
	/** all done **/
}
function ifcrush_frat_handle_forms($action,$frat_letters){
	switch ($action) {
		case "Create Report":
			echo "<h3>Report for $frat_letters</h3>";
			ifcrush_create_frat_report($frat_letters);
			break;
		
		case "Update Event":
		case "Delete Event":
		case "Add Event":
			ifcrush_event_handle_form($frat_letters);
			ifcrush_display_events($frat_letters);
			break;
			
		case "Show PNMS":
			ifcrush_display_done_form("Finished showing PNMS");
			ifcrush_display_register_pnm_at_event($_POST['eventID'], $frat_letters);
			break;
			
		case "Delete Event Reg":
			ifcrush_display_done_form("Finished showing PNMS");
			/* add function call to delete this event registration */
			ifcrush_eventreg_handle_form("delete registration");
			ifcrush_display_register_pnm_at_event($_POST['eventID'], $frat_letters);
			break;
					
		case "Register this PMN":
			ifcrush_display_done_form("Finished showing PNMS");
			/* add this specific PMN to the event, and then display everything */
			//echo "register ". $_POST['pnm_netID'] . " at " . $_POST['eventID'] . " <br>";
			ifcrush_eventreg_handle_form("add registration");
			ifcrush_display_register_pnm_at_event($_POST['eventID']);		
			break;
			
		case "Register PMNs":
			ifcrush_display_done_form("Finished showing PNMS");
			/* display the pmns registered for this event and a form for one more */
			echo "display register pmns and add form for one more<br>";
			ifcrush_display_register_pnm_at_event($_POST['eventID']);
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
	$query = "select * from $table_name where meta_key IN 
 				('ifcrush_role', 'first_name', 'last_name', 'ifcrush_frat_letters', 'ifcrush_frat_fullname')";

	$all_meta_raw = $wpdb->get_results($query);
	
	/* get all the users */
	$allusers=array();
	foreach ($all_meta_raw as $meta){
		$allusers[$meta->user_id][$meta->meta_key] = $meta->meta_value;
	}

	/* now just the ones we want */
	$allfrats=array();
	foreach ($allusers as $user){
		if (is_rc($user)) {
			array_push($allfrats, $user);
		}
	}

	return($allfrats);
}
function is_rc($user){
	return isset($user['ifcrush_role']) && ($user['ifcrush_role'] == 'rc');
}
function ifcrush_display_done_form($label){
?>
<br><form method="post">
		<input type="submit" value="<?php echo $label; ?>"/>
	</form>
<hr>
<?php
}
 
function userInFrat($thisfrat){
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
/** 
 * ifcrush_display_frats - this is an admin function
 **/
function ifcrush_display_frats(){
	if (!is_user_logged_in()) {
		echo "sorry you must be logged in to view fraternities";
		return;
	}
	
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
				<div class="ifcrushtablecellwide">
					Fraternity
				</div>
				<div class="ifcrushtablecellwide">
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
	//print_r($thisfrat);
	//'ifcrush_role', 'first_name', 'last_name', 'letters', 'ifcrush_frat_fullname'
	?>
	<br>
	<div class="ifcrushtablerow">
			<div class="ifcrushtablecellwide">
				<?php
					echo $thisfrat['ifcrush_frat_fullname'] . " " . $thisfrat['ifcrush_frat_letters'];
				?>
			</div><!-- end fratid-->
			<div class="ifcrushtablecellwide">
				<?php
					echo $thisfrat['first_name'] . " " . $thisfrat['last_name'];
				?>
			</div><!-- end rushchair-->
			<div class="ifcrushtableauto">
				nothing for now
			</div><!-- end frataction-->
	</div><!-- end ifcrushtablerow-->
<?php
}

/** just keeping this around **/
function create_rc_frat_menu($current){
	$allfrats = get_all_frats();
?>
	<select name="fratID">
<?php
	echo "<option value=\"none\">select fraternity</option>\n";

	foreach ($allfrat as $frat) {
		$frat_fullname = $frat['ifcrush_frat_fullname']; 
		$frat_letters = $frat['ifcrush_frat_letters']; 

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