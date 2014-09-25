<?php
/**
 **  This file contains all the support functions for the table ifcrush_frat
 **/
 
/** 
 * ifcrush_display_frat_table  shortcode entry point
 **/
function ifcrush_display_frat_table(){

	if (!is_user_logged_in()) {
		echo "sorry you must be logged in to see and edit fraternities";
		return;
	}
	/** handle the form if submitted, and display for next time **/
	//ifcrush_frat_handle_form();
	//display_add_frat_form();
	
	global $wpdb;	   
	
	$allfrats = get_all_frats();

	if ( $allfrats ) {
		create_frat_table_header();
		foreach ( $allfrats as $frat ){
			create_frat_table_row($frat);
		}	
		create_frat_table_footer();
	} else {
		?><h2>No Frats!</h2><?php
	}
}
/**
 *  I want an array of arrays (or objects) where each element is a pmn with their name
 *  and netID.  I'd like to use a select so there is only one query.
 *  So, I get all the meta data for users (I don't need the user table)
 *  and create one array element for each user with meta data.
 *  Then I copy just the users I want into another array. 
  **/
function get_all_frats(){

	global $wpdb;		
	$query = "select * from wp_usermeta where meta_key IN 
 				('ifcrush_role', 'first_name', 'last_name', 'ifcrush_frat_letters', 'ifcrush_frat_fullname')";

	$all_meta_raw = $wpdb->get_results($query);
	
	/* get all the users */
	$allusers=array();
	foreach ($all_meta_raw as $meta){
		$allusers[$meta->user_id][$meta->meta_key] = $meta->meta_value;
	}

	/* now just the ones we want */
	$allfrats=array();
	foreach ($allusers as $user){
		if (is_rc($user)) {
			array_push($allfrats, $user);
		}
	}

	return($allfrats);
}
function is_rc($user){
	return isset($user['ifcrush_role']) && ($user['ifcrush_role'] == 'rc');
}

 
/** initial array of fraternities **/
/** ifcrush_install_frats() - HACK to put in some sample data.  
 ** Should be deleted or actual frat data added once actual data is available. jbltodo
 **/
function ifcrush_install_frats() {
	$frats = array( 
	array('fullname' => 'Alpha Epsilon Pi', 	'letters'=> 'AEP',	'email' =>"AEP@u.northwestern.edu"),
	array('fullname' => 'Delta Chi', 			'letters'=> 'DX',	'email' =>"DX@u.northwestern.edu"),
	array('fullname' => 'Delta Tau Delta', 		'letters'=> 'DTD',	'email' =>"DTD@u.northwestern.edu"),
	array('fullname' => 'Evans Scholars', 		'letters'=> 'ES',	'email' =>"ES@u.northwestern.edu"),
	array('fullname' => 'Lambda Chi Alpha', 	'letters'=> 'LXA',	'email' =>"LXA@u.northwestern.edu"),
	array('fullname' => 'Phi Delta Theta', 		'letters'=> 'FDT',	'email' =>"FDT@u.northwestern.edu"),
	array('fullname' => 'Phi Gamma Delta', 		'letters'=> 'FJ', 	'email' =>"FJ@u.northwestern.edu"),
	array('fullname' => 'Phi Kappa Psi', 		'letters'=> 'PKP', 	'email' =>"PKP@u.northwestern.edu"),
	array('fullname' => 'Phi Mu Alpha', 		'letters'=> 'PMA', 	'email' =>"PMA@u.northwestern.edu"),
	array('fullname' => 'Pi Kappa Alpha', 		'letters'=> 'PKA', 	'email' =>"PKA@u.northwestern.edu"),
	array('fullname' => 'Sigma Alpha Epsilon', 	'letters'=> 'SAE', 	'email' =>"SAE@u.northwestern.edu"),
	array('fullname' => 'Sigma Chi', 			'letters'=> 'SX', 	'email' =>"SX@u.northwestern.edu"),
	array('fullname' => 'Sigma Phi Epsilon', 	'letters'=> 'SPE', 	'email' =>"SPE@u.northwestern.edu"),
	array('fullname' => 'Theta Chi', 			'letters'=> 'TX', 	'email' =>"TX@u.northwestern.edu"),
	array('fullname' => 'Zeta Beta Tau', 		'letters'=> 'ZBT', 	'email' =>"ZBT@u.northwestern.edu"));
	
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

/** This function adds a user that is a fraternity.
 ** support function for inserting.  Inserts the passed object data in the user
 ** data and metadata
 ** Database
 **/
function addFrat($thisfrat){
	global $wpdb;

	$user_id = username_exists( $thisfrat['letters'] );
	if ( !$user_id and email_exists($thisfrat['email']) == false ) {
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_id = wp_create_user( $thisfrat['letters'], $random_password, $thisfrat['email'] );
    	update_user_meta( $user_id, 'first_name', 'Mr.'  );
    	update_user_meta( $user_id, 'last_name', $thisfrat['fullname']  );
    	update_user_meta( $user_id, 'ifcrush_frat_fullname', $thisfrat['fullname']  );   
    	update_user_meta( $user_id, 'ifcrush_frat_letters', $thisfrat['letters']  );    	
    	update_user_meta( $user_id, 'ifcrush_role', 'rc'  );
	} 
// 	else /* KBL todo - return value? maybe an error log */
// 	{
// 		echo "Fraternity already exists as a user - not added";
// 	}
}

/** Updates the DB with data from the POST vars if someone is logged in.
 **/
function updateFrat($thisfrat){
		global $wpdb;
	
		if ( userInFrat($thisfrat) ) {
			$table_name = $wpdb->prefix . "ifc_fraternity";
			$where = array('letters' => $thisfrat['letters']);
        	$wpdb->update( $table_name, $thisfrat, $where );
		} else {
			echo "sorry no one logged in or you aren't authorized for this frat, update cancelled";
		} 
}

function userInFrat($thisfrat){
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		return true;
	} else {
		return false;
	}
}

function create_frat_table_header(){
	?>
	<div class="frattable">
		<div class="fratrow">
				<div class="fratid">
					Fraternity
				</div>
				<div class="rushchair">
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
					Recruitment chair (name and email)
=======
					Rush chair
>>>>>>> FETCH_HEAD
=======
					Rush chair
>>>>>>> FETCH_HEAD
=======
					Rush chair
>>>>>>> FETCH_HEAD
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


function create_frat_table_row($thisfrat){
	//print_r($thisfrat);
	//'ifcrush_role', 'first_name', 'last_name', 'letters', 'ifcrush_frat_fullname'
	?>
	<br>
	<div class="fratrow">
			<div class="fratid">
				<?php
					echo $thisfrat['ifcrush_frat_fullname'] . " " . $thisfrat['ifcrush_frat_letters'];
				?>
			</div><!-- end fratid-->
			<div class="rushchair">
				<?php
					echo $thisfrat['first_name'] . " " . $thisfrat['last_name'];
				?>
			</div><!-- end rushchair-->
			<div class="frataction">
				nothing for now
			</div><!-- end frataction-->
	</div><br><!-- end fratrow-->
<?php
}
?>