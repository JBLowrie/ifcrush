<?php
/**
 * ifcrush_bid_show_bid_form - displays the form with
 * the netid selection and confirmation password for pnm
 */
function ifcrush_bid_show_bid_form( $frat_letters ) {
?>
	<hr>
 	<form method="post">
 		<?php  create_available_pnm_netIDs_menu("     "); ?> 
 		<br> <br>			
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
		if ( $pnm_netID == "none" ) {
			echo "<p>Please select a PNM</p>";
			return;
		}
		$pnm_pw = $_POST['pnm_pw'];
		$pnm_name = get_pnm_name_by_netID( $pnm_netID );
		
		$failure = false;
		if ( -1 == ifcrush_bid_verify_password( $pnm_netID, $pnm_pw) ) {
			$failure = true;
			echo "<p>PNM Password Failed - please try again.</p>";
		}
		
		if ( ! $failure ) {
			echo "<h2>$frat_letters offering a bid to $pnm_name</h2>";
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
			echo "Bid entry failed for " . $thisBid['netID'] . 
				 " offered by " . $thisBid['fratID'];
		} else {
			echo " Success! " . $thisBid['netID'] . " - " . $thisBid['fratID'];

		}
}
/**
 * ifcrush_bid_show_table -
 **/
function ifcrush_bid_show_table(){
		global $wpdb;

		$table_name = $wpdb->prefix . "ifc_bid";
		$query = "select * from $table_name";
		$bids = $wpdb->get_results($table_name, $thisBid);
		
		echo "	<table>
					<tr>
						<th>NetID</th>
						<th>FratID</th>
						<th>Delete</th>
					</tr>
				";
		foreach ($bids as $b) {
			echo "	<tr>
						<td>" . $b->NetID  . "</td>
						<td>" . $b->FratID . "</td>
						<td>
							<form method=\"post\">
								<input type=\"hidden\" name=\"NetID\"  value=\"" . $b->NetID  . "\">
								<input type=\"hidden\" name=\"FratID\" value=\"" . $b->FratID . "\">
								<input type=\"submit\" name=\"action\" value=\" deletebid\" \>
							</form>
						</td>
					</tr>";
		}
		echo "</table>";		
}
/**
 * ifcrush_bid_show_table -
 **/
function ifcrush_bid_show_table_admin(){
		global $wpdb;

		$table_name = $wpdb->prefix . "ifc_bid";
		$query = "select * from $table_name";
		$bids = $wpdb->get_results($query);
		
		echo "	<table>
					<tr>
						<th>NetID</th>
						<th>FratID</th>
						<th>Delete</th>
					</tr>
				";
		foreach ($bids as $b) {
			echo "	<tr>
						<td>" . $b->netID  . "</td>
						<td>" . $b->fratID . "</td>
						<td>
							<form method=\"post\">
								<input type=\"hidden\" name=\"NetID\"  value=\"" . $b->netID  . "\">
								<input type=\"hidden\" name=\"FratID\" value=\"" . $b->fratID . "\">
								<input type=\"submit\" name=\"action\" value=\"Delete " . $b->netID ." ". $b->fratID  . " \" \>
							</form>
						</td>
					</tr>";
		}		
}

function ifcrush_delete_bid ($thisbid){

	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_bid";
	$rows_affected = $wpdb->delete( $table_name, $thisbid );	


	if (0 == $rows_affected ) {
		echo "Delete bid failed";
	} else {
		echo "Delete bid succeeded";
	}

}
