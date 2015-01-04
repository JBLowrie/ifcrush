<?php
// Add the ifcrush admin menu.  
function ifcrush_admin_menu() {
	//add_management_page( $page_title, $menu_title, $capability, $menu_slug, $function );
	add_management_page( "IFC Rush Tools", "IFC Rush Reports", 'manage_options', 'ifcrush_tool', 'ifcrush_admin_tools');
	add_management_page( "IFC Rush PNM dump", "IFC Rush PNM Data Dump", 'manage_options', 'ifcrush_pnm_dump', 'ifcrush_all_data');
	add_management_page( "IFC Rush Data Summary", "IFC Rush Data Summary", 'manage_options', 'ifcrush_summary', 'ifcrush_data_summary');
}

function ifcrush_data_summary(){
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo "<div>
	<h3>Bid Data</h3>";
	
	ifcrush_report_total_rushees();
	ifcrush_report_rushees_by_residence();
	ifcrush_report_rushees_by_school();
	ifcrush_report_rushees_by_yog();

	echo "</div>";

}
function ifcrush_all_data(){
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo "<h3>Bid Data</h3>";
	ifcrush_report_dump_pnms();

	echo '</div>';

}

function ifcrush_admin_tools() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<div id="accordion">';

	// display a list of pnms
	ifcrush_list_pnms();
	
	// display a list of frats.  Then allow the admin to show the report for each.
	ifcrush_list_frats();
	
	ifcrush_create_frat_reports();
	echo '</div>';
	echo '</div>';

}


/**

Add a capability IFCRUSH admin?

What do we want to do on the admin page?

Backup events and event registrations.

List all events, event info (title, sponsoring fraternity) and the pnms registered for them.
List all the pnms and the events they have attended (by fraternity)
List all pnms and for each fraternity, list the count of events they have attended.

Add fraternities (users with rc meta)


**/