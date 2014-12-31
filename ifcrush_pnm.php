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
	if ( is_user_a_pnm( $current_user ) ){
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
	echo "Hello $username.  You attended these events:<br><br>";
	
	ifcrush_eventreg_for_pnm($pnm_netID);
	
	/** all done **/
}
 
/* display pnms in a list */
function ifcrush_list_pnms(){   
	$allpnms = get_all_pnm_ids_names_bids();

	echo "<h3>List of Potential New Members</h3>";
	if ( $allpnms ) {
		echo "<div>";
		echo "<table>";
		echo "<tr><th>Letters</th><th>Fullname</th><th></th></tr>";

		foreach ( $allpnms as $thispnm ){
			$netID = $thispnm->ifcrush_netID;
			$fullname = $thispnm->last_name . ", " . $thispnm->first_name;
			echo "<tr><td>$netID</td><td>$fullname</td><td></td></tr>";
		}	
		echo "</table></div>";
	} else {
		?><div>No PNMs!</div><?php
	}
}

//Display PNMs
function ifcrush_display_pnms() {	
	
	$allpnms = get_all_pnm_ids_names();
	
	if (!is_user_logged_in()) {
		echo "sorry you must be logged in to see pnms";
		return;
	}
	
	if ( $allpnms ) {
		create_pnm_table_header(); // make a table header
		foreach ( $allpnms as $pnm ) { 
				create_pnm_table_row($pnm);
		}
		create_pnm_table_footer(); // end the table
	} else { 
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
function is_pnm( $user ){
	return isset( $user['ifcrush_role'] ) && ( $user['ifcrush_role'] == 'pnm' );
}

function create_pnm_table_row( $pnm ) {
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
function get_pnm_name_by_netID( $netID ){
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


function create_available_pnm_netIDs_menu( $current ){

	$availablepnms = get_available_pnm_ids_names();
	
	if (!$availablepnms) {
		echo "<h3>No PNMS!?!</h3>";
		return;
	}
	global $debug;
	if ($debug) {
		echo "<pre>";
		print_r($allpnms);
		echo "</pre>";
	}
	
?>
	<select name="available_pnm_netID">
<?php
	echo "<option value=\"none\">enter NetID</option>\n";

	foreach ( $availablepnms as $pnm ) {
		$pnm_netID = $pnm->ifcrush_netID; 
		$last_name = $pnm->last_name; 
		$first_name = $pnm->first_name; 
		$name = $first_name . " " . $last_name;
		$displayoption = $pnm_netID ." - ".$name;
		
		if ( $pnm_netID == $current ) {
			echo "<option value=\"$pnm_netID\" selected=\"selected\">$displayoption</option>\n";
		} else {
			echo "<option value=\"$pnm_netID\">$displayoption</option>\n";
		}
	}
?>
	</select>
<?php
}


/**
 *  function get_available_pnm_ids_names() - returns an array of objects where 
 *  each element is a pmn with their name and netID.  I'd like to use a select 
 *  so there is only one query.
 **/
function get_available_pnm_ids_names(){

	global $wpdb;		
	$usermeta_table = $wpdb->prefix . "usermeta";
	$ifc_bid_table = $wpdb->prefix . "ifc_bid";
	
	$query = "
		select * from 
		(select um1.user_id, 
			um1.meta_value as ifcrush_netID, 
			um2.meta_value as last_name, 
			um3.meta_value as first_name from $usermeta_table as um1 
				left join $usermeta_table as um2 on um1.user_id = um2.user_id 
				left join $usermeta_table as um3 on um1.user_id = um3.user_id 
					WHERE ( um3.meta_key LIKE 'first_name' 
						AND um2.meta_key LIKE 'last_name' 
						AND um1.meta_key LIKE 'ifcrush_netID' )
						AND um1.user_id IN 
						(SELECT user_id FROM $usermeta_table 
							WHERE meta_key LIKE 'ifcrush_role' 
								AND meta_value LIKE 'pnm')) as p
			WHERE ifcrush_netID NOT IN (SELECT netID from $ifc_bid_table)";

	$availablepnms= $wpdb->get_results( $query );
	
	return( $availablepnms );
}

/**
 *  I want an array of arrays (or objects) where each element is a pmn with their name
 *  and netID.  I'd like to use a select so there is only one query.
 *  This function returns an array of objects.
 **/
function get_all_pnm_ids_names(){

	global $wpdb;		

	$query = "select um1.user_id, 
		um1.meta_value as ifcrush_netID, 
		um2.meta_value as last_name, 
		um3.meta_value as first_name from $usermeta as um1 
				left join $usermeta as um2 on um1.user_id = um2.user_id 
				left join $usermeta as um3 on um1.user_id = um3.user_id 
					WHERE ( um3.meta_key LIKE 'first_name' 
						AND um2.meta_key LIKE 'last_name' 
						AND um1.meta_key LIKE 'ifcrush_netID' )
						AND um1.user_id IN 
						(SELECT user_id FROM $usermeta 
							WHERE meta_key LIKE 'ifcrush_role' 
								AND meta_value LIKE 'pnm')";

	$allpnms= $wpdb->get_results( $query );
	
	return( $allpnms );
}


?>