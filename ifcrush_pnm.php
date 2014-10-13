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
	$allpnms = get_all_pnm_ids_names();

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
/**
 *  I want an array of arrays (or objects) where each element is a pmn with their name
 *  and netID.  I'd like to use a select so there is only one query.
 *  This funciton returns an array of objects.
 **/
function get_all_pnm_ids_names(){

	global $wpdb;		
	$table_name = $wpdb->prefix . "usermeta";
	$query = "select um1.user_id, 
um1.meta_value as ifcrush_netID, 
um2.meta_value as last_name,
um3.meta_value as first_name
from wp_usermeta as um1 
left join wp_usermeta as um2 on um1.user_id = um2.user_id 
left join wp_usermeta as um3 on um1.user_id = um3.user_id 
where   
um3.meta_key='first_name' AND 
um2.meta_key='last_name' AND 
um1.meta_key='ifcrush_netID' AND
um1.user_id IN (SELECT user_id FROM wp_usermeta WHERE meta_key='ifcrush_role' and meta_value='pnm')
order by ifcrush_netID";

	$allpnms= $wpdb->get_results( $query );
	
	return( $allpnms );
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
?>