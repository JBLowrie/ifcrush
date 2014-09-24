<?php
/**
 **  This file contains all the support functions for viewing and managing
 **  Potential New Members (aka PNMs).  These 
 
 
 
 **/
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
	$query = "select * from wp_usermeta where meta_key IN 
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
?>