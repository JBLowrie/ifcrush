<?php
function ifcrush_bid_show_bid_form( $frat_letters ) {
?>
	<hr>
 	<form method="post">
 		<?php
 			/* maybe a new create_available_pnms( $frat_letters )
 			 * with counts of the number of events attended.
 			 * then you can still select someone who hasn't been
 			 * to any of your events, but those who have are 
 			 * easier to find
 			 */  
 			create_available_pnm_netIDs_menu("     "); 
 		?> 
 		<input type="submit" name="action"  value="Create Bid" />
	</form>
	<hr>
<?php
}

function ifcrush_bid_handle_bid_form( $frat_letters ){
?>
<?php
		global $wpdb;
	
		$pnm_netID = $_POST['available_pnm_netID'];
		$pnm_name = get_pnm_name_by_netID( $pnm_netID );
		echo "<h3>$frat_letters offering a bid to $pnm_name</h3>";
		
		$table_name = $wpdb->prefix . "ifcrush_bid";
		$thisBid = array(
						'netID' => $pnm_netID,
						'fratID' => $frat_letters
					);
		$rows_affected = $wpdb->insert($table_name, $thisBid);
		
		if ( 0 == $rows_affected ) {
				echo "bid entry failed for " . $thisBid['pnm_netID'];
			}
				
		return $rows_affected;
	/* 
	 * verify PNM bid status - 
	 *		is unavailable if they have accepted another bid
	 *		(i.e. isnull(status)
	 * if the PNM has accepted another bid, they are not available
	 */
	
	/* ask pnm to confirm their password */
	/* update bid status to the frat letters of the current frat */
}
