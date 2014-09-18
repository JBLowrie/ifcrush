<?php
/**
 **  This file contains all the support functions for the table ifcrush_rushees
 **/
 
 
function ifcrush_install_rushees() {
	$rushees = array( array('netID' => 'abc123', 	'firstName'=> 'Joe',	'lastName' => 'Thibideau', 'rushstat' =>"gdi",	'gradYear' =>"2018", 'school' => "Weinberg", 'residence' => "Elder", 'email' => "tbd@u.northwestern.edu"),
					  array('netID' => 'www456', 	'firstName'=> 'Willie',	'lastName' => 'Wildcat', 'rushstat' =>"gdi",	'gradYear' =>"2018", 'school' => "Weinberg", 'residence' => "Elder", 'email' => "www@u.northwestern.edu"),
					  array('netID' => 'usa789', 	'firstName'=> 'Uncle',	'lastName' => 'Sam', 'rushstat' =>"gdi",	'gradYear' =>"2018", 'school' => "Weinberg", 'residence' => "Elder", 'email' => "usa@u.northwestern.edu"),
					  array('netID' => 'bho012', 	'firstName'=> 'Barack',	'lastName' => 'Obama', 'rushstat' =>"gdi",	'gradYear' =>"2018", 'school' => "Weinberg", 'residence' => "Elder", 'email' => "bho@u.northwestern.edu"),
					  array('netID' => 'gwb345', 	'firstName'=> 'George',	'lastName' => 'Bush', 'rushstat' =>"gdi",	'gradYear' =>"2018", 'school' => "Weinberg", 'residence' => "Elder", 'email' => "gwb@u.northwestern.edu")
					);
	
	foreach ($rushees as $rushee)
   		addRushee($rushee);
} // initial array of rushees (cooked data)

function ifcrush_rushee_handle_form() { 
// 
global $debug;
	if ($debug){
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
	
	if (!isset($_POST['netID'])) // if netID is empty, don't do anything
		return;
	$thisrushee = array( 'netID' 	 =>  $_POST['netID'],
						 'firstName' =>  $_POST['firstName'],
						 'lastName'  =>  $_POST['lastName'], 
						 'rushstat'  =>  $_POST['rushstat'],
						 'gradYear'  =>  $_POST['gradYear'], 
						 'school'    =>  $_POST['school'], 
						 'residence' =>  $_POST['residence'], 
						 'email'	 =>  $_POST['email']
						); // put the form input into an array

	//delete, update, or add rushee
	if ( isset($_POST['addRushee']) ){
		addRushee($thisrushee);
		}	
	else if ( isset($_POST['updateRushee']) ){
		updateRushee($thisrushee);
		}
	else if ( isset($_POST['deleteRushee']) ){
		deleteRushee($thisrushee);
	}
} // handles changes to db from the front end

/* rushee_handler_form helpers */
function addRushee($thisrushee) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_rushee";
	$rows_affected = $wpdb->insert($table_name, $thisrushee);
	
	if ($rows_affected == 0) {
		echo "INSERT ERROR for " . $thisrushee['netID'] ;
	}
	return $rows_affected;
} // adds a rushee to the table if addRushee is tagged

function updateRushee($thisrushee) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_rushee";
	$where = array('netID' => $thisrushee['netID']);
	$wpdb->update( $table_name, $thisrushee, $where );
} // updates a rushee with a matching netID if updateRushee is tagged

function deleteRushee($thisrushee) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_rushee";
	$wpdb->delete( $table_name, $thisrushee);
} // deletes a rushee if deleteRushee is tagged


//Display rushee table
function ifcrush_display_rushee_table() {

	ifcrush_rushee_handle_form(); // handle updates, adds, deletes
	
	global $wpdb;
	
	$rushee_table_name = $wpdb->prefix . "ifc_rushee";
	$query = "SELECT * FROM $rushee_table_name";
	$allrushees = $wpdb->get_results($query);
	
	if ($allrushees) {
		create_rush_table_header(); // make a table header
		foreach ($allrushees as $rushee) { // populate the rows with db info
			create_rush_table_row($rushee);
		}
		create_rushee_add_row();
		create_rush_table_footer(); // end the table
	} 
	else { 
		?><h2>No rushees!</h2><?php
	}
}

function create_rush_table_header() {
	?>
		<table>
		<tr>
			<th>netID</th>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Rush Status</th>
			<th>YoG</th>
			<th>School</th>
			<th>Residence</th>
			<th>Email</th>
		</tr>
	<?php
}
function create_rush_table_footer() {
	?></table><?php
}
function create_rushee_add_row() {
	?>
		<tr>
			<form method="post">
				<td>
					<input type="text" name="netID" size=6 value="enter netID">
				</td>
				<td>
					<input type="text" name="firstName" size=15 value="enter first name">
				</td>
				<td>
					<input type="text" name="lastName" size=15 value="enter last name">
				</td>
				<td>
					<input type="text" name="rushstat" size=3 value="enter rush status">
				</td>
				<td>
					<input type="text" name="gradYear" size=4 value="enter YoG">
				</td>
				<td>
					<input type="text" name="school" size=8 value="enter School">
				</td>
				<td>
					<input type="text" name="residence" size=10 value="enter Residence">
				</td>
				<td>
					<input type="text" name="email" size=30 value="enter email">
				</td>
				<td>				
					<input type="submit" name="addRushee" value="Add Rushee"/>
				</td>
			</form>
		</tr>
	<?php
}

function create_rush_table_row($rushee) {
	?>
		<tr>
			<form method="post">
				<td>
					<input type="text" name="netID" size=6 value="<?php echo $rushee->netID; ?>"/>
				</td>
				<td>
					<input type="text" name="firstName" size=15 value="<?php echo $rushee->firstName; ?>"/>
				</td>
				<td>
					<input type="text" name="lastName" size=15 value="<?php echo $rushee->lastName; ?>"/>
				</td>
				<td>
					<input type="text" name="rushstat" size=3 value="<?php echo $rushee->rushstat; ?>"/>
				</td>
				<td>
					<input type="text" name="gradYear" size=4 value="<?php echo $rushee->gradYear; ?>"/>
				</td>
				<td>
					<input type="text" name="school" size=8 value="<?php echo $rushee->school; ?>"/>
				</td>
				<td>
					<input type="text" name="residence" size=10 value="<?php echo $rushee->residence; ?>"/>
				</td>
				<td>
					<input type="text" name="email" size=30 value="<?php echo $rushee->email; ?>"/>
				</td>
				<td>
					<input type="submit" name="updateRushee" value="Update"/><input type="submit" name="deleteRushee" value="Delete"/>
				</td>
			</form>
		</tr>
	<?php
}

?>