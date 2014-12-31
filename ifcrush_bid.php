<?php
function ifcrush_bid_show_bid_form( $frat_letters ) {
?>
	<hr>
 	<form method="post">
 		<?php
 			/* --kbltodo  maybe a new create_available_pnms( $frat_letters )
 			 * with counts of the number of events attended.
 			 * then you can still select someone who hasn't been
 			 * to any of your events, but those who have are 
 			 * easier to find. 
 			 */  
 			create_available_pnm_netIDs_menu("     ");
 			?>  			
 			PNM Password: <input type="password" name="pnm_pw"/> 		
 		<input type="submit" name="action"  value="Create Bid" />
	</form>
	<hr>
<?php
}

/**
 * This function simultaneously verifies usernames/passwords.  This is because both
 * the pnm and the rc have to type in their usernames/passwords to create the bid
 */
function ifcrush_bid_verify_password( $netID, $userpassword ) {
	global $wpdb;		
	$table_name = $wpdb->prefix . "usermeta";
	$query = "select user_id from $table_name where meta_value = '$netID'";
	$userid = $wpdb->get_results( $query );
	$user = get_user_by( 'id', $userid[0]->user_id );

	if ( $user && wp_check_password( $userpassword, $user->data->user_pass, $user->ID) ) {
   		return 0;
	} else {
   		return -1 ;
   	}
}

function ifcrush_bid_handle_bid_form( $frat_letters ){
?>
<?php
		global $wpdb;
	
		$pnm_netID = $_POST['available_pnm_netID'];
		if ( $pnm_netID == "none" )
			echo "<p>Please select a PNM</p>";
		$pnm_pw = $_POST['pnm_pw'];
		$pnm_name = get_pnm_name_by_netID( $pnm_netID );
		echo "<h3>$frat_letters offering a bid to $pnm_name</h3>";
		
		$failure = false;
		if ( -1 == ifcrush_bid_verify_password( $pnm_netID, $pnm_pw) ) {
			$failure = true;
			echo "<p>PNM Password Failed</p>";
		}
		
		if ( ! $failure ) {
			$thisBid = array(  'netID' => $pnm_netID, 'fratID' => $frat_letters );
			ifcrush_bid_insert_bid($thisBid);		       
		}
}
/**
 * ifcrush_bid_insert_bid - insert the bid into the bid tabls
 **/
function ifcrush_bid_insert_bid($thisBid){
		global $wpdb;

		$table_name = $wpdb->prefix . "ifc_bid";
		$rows_affected = $wpdb->insert($table_name, $thisBid);
		
		if ( 0 == $rows_affected ) {
			echo "bid entry failed for " . $thisBid['netID'] . " - " . $thisBid['fratID'];
		} else {
			echo "Success! " . $thisBid['netID'] . " - " . $thisBid['fratID'];

		}
				
		return $rows_affected;
}
