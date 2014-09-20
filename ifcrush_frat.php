<?php
/**
 **  This file contains all the support functions for the table ifcrush_frat
 **/
 
/** initial array of fraternities **/
/** ifcrush_install_frats() - HACK to put in some sample data.  
 ** Should be deleted or actual frat data added once actual data is available. jbltodo
 **/
function ifcrush_install_frats() {
	$frats = array( 
	array('fullname' => 'Alpha Epsilon Pi', 	'letters'=> 'AEP',	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Delta Chi', 			'letters'=> 'DX', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Delta Tau Delta', 		'letters'=> 'DTD', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Evans Scholars', 		'letters'=> 'ES', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Lambda Chi Alpha', 	'letters'=> 'LXA', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Phi Delta Theta', 		'letters'=> 'FDT', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Phi Gamma Delta', 		'letters'=> 'FJ', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Phi Kappa Psi', 		'letters'=> 'PKP', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Phi Mu Alpha', 		'letters'=> 'PMA', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Pi Kappa Alpha', 		'letters'=> 'PKA', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Sigma Alpha Epsilon', 	'letters'=> 'SAE', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Sigma Chi', 			'letters'=> 'SX', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Sigma Phi Epsilon', 	'letters'=> 'SPE', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Theta Chi', 			'letters'=> 'TX', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"),
	array('fullname' => 'Zeta Beta Tau', 		'letters'=> 'ZBT', 	'rushchair' => 'mr. tbd', 'email' =>"tbd@u.northwestern.edu"));
	
	foreach ($frats as $frat)
   		addFrat($frat);

}
function ifcrush_frat_handle_form(){
	global $debug;
	if ($debug){
			echo "<pre>"; print_r($_POST); echo "</pre>";
	}
	
	if (!isset($_POST['letters']))
		return;
		
	$thisfrat = array(
			'letters'	=> $_POST['letters'],
			'fullname'	=> $_POST['fullname'], 
			'rushchair' => $_POST['rushchair'], 
			'email'		=> $_POST['email']
		);
	
	/*** handle form submission **/
	if ( isset($_POST['deleteFrat']) ){
		deleteFrat($thisfrat);
	} else if ( isset($_POST['updateFrat']) ){
		updateFrat($thisfrat);
	} else if ( isset($_POST['addFrat']) ) {
		addFrat($thisfrat);
	}
}

/** support function for inserting.  Inserts the passed object data in the
 ** Database
 **/
function addFrat($thisfrat){
	global $wpdb;

   	if ( userInFrat($thisfrat) ) {
   	   	
		$table_name = $wpdb->prefix . "ifc_fraternity";
	    $rows_affected = $wpdb->insert( $table_name, $thisfrat);
	
	   	if ($rows_affected == 0) {
   			echo "INSERT ERROR for " . $thisfrat['letters'];	
   		}
   		return $rows_affected;
   	} else {
   		echo "sorry no one logged in or you aren't authorized for this frat, add cancelled";
   	}
}

/** Updates the DB with data from the POST vars if someone is logged in.
 **/
function updateFrat($thisfrat){
		global $wpdb;
		

		if ( userInFrat($thisfrat) ) {
			/** jbl todo - check to see if current user is admin or is rush Chair
			 ** for that frat **/
			$table_name = $wpdb->prefix . "ifc_fraternity";
			$where = array('letters' => $thisfrat['letters']);
        	$wpdb->update( $table_name, $thisfrat, $where );
		} else {
			echo "sorry no one logged in or you aren't authorized for this frat, update cancelled";
		} 
}
function deleteFrat($thisfrat){
	global $wpdb;
	
	if (userInFrat($thisfrat)){
		$table_name = $wpdb->prefix . "ifc_fraternity";
		$wpdb->delete( $table_name, array( 'letters' => $thisfrat['letters'] ) );
	} else {
		echo "sorry no one logged in or you aren't authorized for this frat, delete cancelled";
	}
}

function userInFrat($thisfrat){
	if ( is_user_logged_in() ) {
		/** jbl todo - check to see if current user is admin or is rush Chair
		 ** for $thisfrat **/
		$current_user = wp_get_current_user();
		return true;
	} else {
		return false;
	}
}

function insertFrat(){
 		
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
		} else {
			echo "no one logged in, insert cancelled";
			return;
		}
     
		$thisfrat = array(
			'letters' 	=> $_POST['letters'], 
	   		'fullname' 	=> $_POST['fullname'], 
			'rushchair'	=> $_POST['rushchair'],
 			'email'	    => $_POST['email'],
		);
   		add_frat($thisfrat);
}

function ifcrush_display_frat_table(){

	/** handle the form if submitted, and display for next time **/
	ifcrush_frat_handle_form();
	//display_add_frat_form();
	
	global $wpdb;	   
	
	$frat_table_name = $wpdb->prefix . "ifc_fraternity";    
	$query= " SELECT * FROM $frat_table_name";
	$allfrats = $wpdb->get_results( $query );

	if ( $allfrats ) {
		create_frat_table_header();
		foreach ( $allfrats as $frat ){
			create_frat_table_row($frat);
		}	
		create_frat_add_row();
		create_frat_table_footer();
	} else {
		?><h2>No Frats!</h2><?php
	}
}
function create_frat_table_header(){
	?>
	<div class="frattable">
		<div class="fratrow">
				<div class="fratid">
					Fraternity (name and letters)
				</div>
				<div class="rushchair">
					Rush chair (name and email)
				</div>
				<div class="frataction">
					Action
				</div>
 		</div><!--end fratrow-->
	<?php
}
function create_frat_table_footer(){
	?>
	</div><!-- end frat table-->  
<?php
}

/** jbltodo - add javascript form checking **/
function create_frat_add_row(){
?><br>
	<div class="fratrow">
		<form method="post">
			<div  class="fratid">
				<input type="text" name="fullname" size="20" value="enter frat name"/>&nbsp;
				<input type="text" name="letters" size="3" value=""/>
			</div><!-- end fratid-->
			<div class="rushchair">	
				<input type="text" name="rushchair" size="15" value="enter rush chair"/>&nbsp;
				<input type="text" name="email" size="25" value=" rush chair email"/>
			</div><!-- end rushchair-->
			<div class="frataction">		
				<input type="submit" name="addFrat" value="Add Frat"/>
			</div><!-- end frataction-->
		</form>
	</div><br><!-- end fratrow-->
<?php
}

function create_frat_table_row($frat){
	//print_r($frat);
	?>
	<br>
	<div class="fratrow">
		<form method="post">
			<div class="fratid">
				<input type="text" name="fullname"  size="20" value="<?php echo $frat->fullname; ?>"/>&nbsp;
				<input type="text" name="letters"   size="3" value="<?php echo $frat->letters; ?>"/>
			</div><!-- end fratid-->
			<div class="rushchair">
				<input type="text" name="rushchair" size="15" value="<?php echo $frat->rushchair; ?>"/>&nbsp;
				<input type="text" name="email" size="25" value="<?php echo $frat->email; ?>"/>
			</div><!-- end rushchair-->
			<div class="frataction">
				<input type="submit" name="updateFrat" value="Update"/>&nbsp;
				<input type="submit" name="deleteFrat" value="Delete"/>
			</div><!-- end frataction-->
		</form>
	</div><br><!-- end fratrow-->
<?php
}
?>