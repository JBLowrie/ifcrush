<?php
/**
 **  This file contains all the support functions for viewing and managing
 **  Potential New Members (aka PNMs).  These 
 **/
 
/**
 * This is the PNM shortcode entry point
 **/
function ifcrush_pnm(){
// 	global $debug;
// 	if ($debug) echo "[ifcrush_pnm] ";
	
	if (!is_user_logged_in()) {
		echo "sorry you must logged in as a pnm to access this page.";
		return;
	}
	
	$current_user = wp_get_current_user();
	if (is_user_a_pnm($current_user)){
		/* get the frat of the rc */
		$pnm_netID =  get_pnm_netID($current_user);
	} else {
		echo "sorry you must logged in as a pnm to access this page.";
		return;
	}
	
	/* 
	 * Now I know who I am.
	 */	
	$username = get_current_user_name($current_user);
	echo "Hello $username.  Here are your events.";
	
	ifcrush_event_reg_for_pnm($pnm_netID);
	
	/** all done **/
}
 
/* kbl note is_admin() is a wp function */
function ifcrush_isuserrc(){
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		return true;
	} else {
		return false;
	}
}

//Display PNMs
function ifcrush_display_pnms() {	
	
	$allpnms = get_all_pmns();
	
	if (!is_user_logged_in()) {
		echo "sorry you must be logged in to see pnms";
		return;
	}
	
		if ($allpnms) {
			create_pnm_table_header(); // make a table header
			foreach ($allpnms as $pnm) { 
					create_pnm_table_row($pnm);
			}
			create_pnm_table_footer(); // end the table
		} 
		else { 
			?><h2>No Potential New Members!</h2><?php
		}
}

function create_pnm_table_header(){
	?>
	<div class="pnmtable">
		<div class="pnmrow">
				<div class="pnmid">
					ID
				</div>
				<div class="pnmdata">
					PNM Data
				</div>
				<div class="pnmstatus">
					Affiliation
				</div>
 		</div><!--end pnmrow-->
	<?php
}
function create_pnm_table_footer(){
	?>
	</div><!-- end pnm table-->  
<?php
}
/**
 *  I want an array of arrays (or objects) where each element is a pmn with their name
 *  and netID.  I'd like to use a select so there is only one query.
 *  So, I get all the meta data for users (I don't need the user table)
 *  and create one array element for each user with meta data.
 *  Then I copy just the users I want into another array. 
  **/
function get_all_pmns(){

	global $wpdb;		
	$table_name = $wpdb->prefix . "usermeta";
	$query = "select * from $table_name where meta_key IN 
 				('ifcrush_netID', 'ifcrush_role', 'first_name', 'last_name',
 				'ifcrush_residence','ifcrush_school','ifcrush_yog',
 				'ifcrush_affiliation')";

	$all_meta_raw = $wpdb->get_results($query);
	
	/* get all the users */
	$allusers=array();
	foreach ($all_meta_raw as $meta){
		$allusers[$meta->user_id][$meta->meta_key] = $meta->meta_value;
	}

	/* now just the ones we want */
	$allpnms=array();
	foreach ($allusers as $user){
		if (is_pnm($user)) {
			array_push($allpnms, $user);
		}
	}

	return($allpnms);
}
function is_pnm($user){
	return isset($user['ifcrush_role']) && ($user['ifcrush_role'] == 'pnm');
}

function create_pnm_table_row($pnm) {
?>
		<div>
			<form method="post">
				<div class="pnmid">
					<?php 
						echo $pnm['ifcrush_netID'] . " - ";
						echo $pnm['first_name'] . " ";
						echo $pnm['last_name'] . "<br>";
					?>
				</div>
				<div class="pnmdata">
					<?php
						echo $pnm['ifcrush_residence'] . " ";
						echo $pnm['ifcrush_school'] . " ";
						echo $pnm['ifcrush_yog'] . " ";
					?>					
				</div>
				<div class="pnmstatus">
					<?php
						echo $pnm['ifcrush_affiliation'] . " ";
					?>	
				</div>
			</form>
		</div>
	<?php
}
/**
 * I don't like doing it this way because there are extra queries.  We should
 * fix the places where this function is needed
 **/
function get_pnm_name_by_netID($netID){
	global $wpdb;	   

	$table_name = $wpdb->prefix . "usermeta";
	$query = "select meta_value from $table_name where 
				meta_key='first_name' and user_id in
				(SELECT user_id FROM $table_name WHERE meta_value='$netID') or 
				meta_key='last_name' and user_id in 
				(SELECT user_id FROM $table_name WHERE meta_value='$netID')";
				
	/** should return only first_name and last_name **/		

	$meta_values = $wpdb->get_results($query);
	foreach($meta_values as $name) {
		if (!isset($full_name))
			$full_name = $name->meta_value . " ";
		else
			$full_name .= $name->meta_value . " ";
	}	
	return $full_name;
}
?>