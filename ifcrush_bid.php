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
 			create_pnm_netIDs_menu("     "); 
 		?> 
 		<input type="submit" name="action"  value="Create Bid" />
	</form>
	<hr>
<?php
}

function ifcrush_bid_handle_bid_form( $frat_letters ){
?>
			<h2>Not implemented yet!  :( </h2>
<?php
	/* 
	 * verify PNM bid status - 
	 *		is unavailable if they have accepted another bid
	 *		(i.e. isnull(status)
	 * if the PNM has accepted another bid, they are not available
	 */
	
	/* ask pnm to confirm their password */
	/* update bid status to the frat letters of the current frat */
}