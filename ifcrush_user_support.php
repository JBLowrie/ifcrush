<?php
/**
 * This file contains support for user functions.  
 * This includes all access to user and user meta data.
 *
 * Potenital New Member registration.
 * The user is registered with an ifcrush_netID and ifcrush_role of pnm
 * aka potential new member.  This is the only "open" registration.  All
 * other users (recruitment aka rush chairs or additional admin) must be
 * added by an admin.
 * KBL - todo add ADMIN menu for adding RCs
 **/
 //1. Add a new form element...
add_action( 'register_form', 'ifcrush_register_form' );

function ifcrush_register_form() {
	$netID = ( isset( $_POST['netID'] ) ) ? trim( $_POST['netID'] ) : '';    
?>
	<h3>Registration Information for IFC Potential New Members</h3>
	<p>
    <label for="netID">NetID
	<input type="text" name="netID" id="netID" class="input" size="25" /></label>
	    <label for="firstname">First name
	<input type="text" name="firstname" id="firstname" class="input" size="25" /></label>
	    <label for="lastname">Last name
	<input type="text" name="lastname" id="lastname" class="input" size="25" /></label>
	<label for="yog">Graduation Year
<?php 	createmenu("yog", array(2018, 2017, 2016, 2015)); ?>
	</label>
	</p>
	<p>
	<label for="school">School
<?php
	createmenu( "school", array('Weinberg','McCormick','Communications','Education and Social Policy','Medill','Bienin') );
?>
	</label>
	</p>
	<p>
	<label for="residence">Residence
<?php
	createmenu( "residence", array( '1835 Hinman','1856 Orrington','630 Emerson (PMA)','720 Emerson (SAI)','Bobb','Foster-Walker (PLEX)','Goodrich','Kemper','Lindgren','McCulloch','North Mid-Quads','Rogers House','Sargent','Seabury','South Mid-Quads','Ayers','Chapin','CCS','East Fairchild','Hobart House','Jones','PARC','Shepard','Slivka','West Fairchild','Willard','Allison','Elder','GREEN House','Interfaith','Off Campus') );
?>
	</label>
    </p>
<?php
}
function createmenu( $name, $option ){
	echo "<select name=\"$name\">";
	foreach ( $option as $opt ){
		echo "<option value=\"$opt\">$opt</option>";
	}
	echo "</select>";
}
//2. Add validation. In this case, we make sure first_name is required.
add_filter( 'registration_errors', 'ifcrush_registration_errors', 10, 3 );
function ifcrush_registration_errors( $errors, $sanitized_user_login, $user_email ) {

	if ( ! isset( $_POST['netID'] ) || trim( $_POST['netID'] == false ) ) {
		$errors->add( 'netID_error', __( '<strong>ERROR</strong>: You must include a netID.', 'ifcrush' ) );
    }
    if ( ! isset( $_POST['firstname'] ) || trim( $_POST['firstname'] == false ) ) {
		$errors->add( 'firstname_error', __( '<strong>ERROR</strong>: You must include a firstname.', 'ifcrush' ) );
    }
    if ( ! isset( $_POST['lastname'] ) || trim( $_POST['lastname'] == false ) ) {
		$errors->add( 'lastname_error', __( '<strong>ERROR</strong>: You must include a firstname.', 'ifcrush' ) );
    }
    return $errors;
}

//3. Finally, save our extra registration user meta.
add_action( 'user_register', 'ifcrush_user_register' );
function ifcrush_user_register( $user_id ) {
	if ( isset( $_POST['netID'] ) ) {
    	update_user_meta(  $user_id, 'ifcrush_netID', trim( $_POST['netID'] ) );
    	update_user_meta(  $user_id, 'ifcrush_residence', $_POST['residence'] );
    	update_user_meta(  $user_id, 'first_name', $_POST['firstname']  );
    	update_user_meta(  $user_id, 'last_name', $_POST['lastname']  );    	
    	update_user_meta(  $user_id, 'ifcrush_role', 'pnm'  );
    	update_user_meta(  $user_id, 'ifcrush_school', $_POST['school']  );
    	update_user_meta(  $user_id, 'ifcrush_yog', $_POST['yog']  );
    	update_user_meta(  $user_id, 'ifcrush_residence', $_POST['residence']  );
    	/* this assumes you would not be registering if you were already affiliated */
    	update_user_meta( $user_id, 'ifcrush_affiliation', 'none'  );
    }
}

/* support functions to see type of current user and to get id of current users
 * IDs in this case are the letters and netIDs since that information 
 * identifies the user in our event and eventreg tables 
 */
function is_user_an_rc( $current_user ){
	$key = 'ifcrush_role';
	$single = true;
	$user_role = get_user_meta($current_user->ID, $key, $single ); 
	return( $user_role == 'rc' );
}

function get_frat_letters( $current_user ){
	$key = 'ifcrush_frat_letters';
	$single = true;
	$frat_letters = get_user_meta($current_user->ID, $key, $single ); 
	return( $frat_letters );
}

function is_user_a_pnm( $current_user ){
	$key = 'ifcrush_role';
	$single = true;
	$user_role = get_user_meta($current_user->ID, $key, $single ); 
	return( $user_role == 'pnm' );
}

function get_pnm_netID( $current_user ){
	$key = 'ifcrush_netID';
	$single = true;
	$netID = get_user_meta($current_user->ID, $key, $single ); 
	return( $netID );
}

function get_current_user_name( $current_user ){
	$key = 'first_name';
	$single = true;
	$firstname = get_user_meta($current_user->ID, $key, $single ); 
	$key = 'last_name';
	$single = true;
	$lastname = get_user_meta($current_user->ID, $key, $single ); 
	return( $firstname . " " . $lastname );
}

/** probably don't need this **/
function is_user_an_ifc_admin( $current_user ){
	$key = 'ifcrush_role';
	$single = true;
	$user_role = get_user_meta($current_user->ID, $key, $single ); 
	return( $user_role == 'ifc_admin' );
}
