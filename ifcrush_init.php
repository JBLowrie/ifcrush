<?php
/**
 * @package IFCRush
 * @version 0.1
 */
/*
Plugin Name: IFC Rush
Description: This plugin stores data for Frats and Rushees for rush events.
Author: Lowrie
Version: 0.1
Author URI: nope
*/
global $ifcrush_db_version;
$ifcrush_db_version = "1.0";
global $debug;
$debug = 1;


/**
 * ifcrush_install() - creates the tables that store Frats/Rushees/Events
 **/
function ifcrush_install(){

	global $wpdb;
	global $ifcrush_db_version;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$event_table_name = $wpdb->prefix . "ifc_event";    
	$sql = 	"CREATE TABLE $event_table_name (
		eventDate date not null,
		title varchar(30) not null,
		eventID int not null auto_increment,
		fratID varchar(3) not null,
		PRIMARY KEY(eventID)
	) engine = InnoDB;";
   dbDelta( $sql );

	$table_name = $wpdb->prefix . "ifc_bid";    
	$sql = 	"CREATE TABLE $table_name(
		bidstat int not null,
		netID		varchar(6) not null,
		fratID		varchar(3) not null
	) engine = InnoDB;";
   dbDelta( $sql );

	$table_name = $wpdb->prefix . "ifc_eventreg";    
	$sql = 	"CREATE TABLE $table_name (
		pnm_netID		varchar(6) not null,
		eventID int not null auto_increment,
		PRIMARY KEY(pnm_netID, eventID),
		FOREIGN KEY (eventID) references $event_table_name(eventID)
	) engine = InnoDB;";
   	dbDelta( $sql );
   
   add_option( "ifcrush_db_version", $ifcrush_db_version );
}
register_activation_hook( __FILE__, 'ifcrush_install' );

/** ifcrush_install_data() - Puts in some sample data.  Should be deleted once actual data is
 ** available.
 **/
function ifcrush_install_data() {
global $debug;
 	if ($debug){
 		ifcrush_install_frats();
		ifcrush_install_events();
// 		ifcrush_install_eventreg();
 	}
}
register_activation_hook( __FILE__, 'ifcrush_install_data' );


/** ifcrush_deactivate() - cleans up when the plugin is deactived, 
 ** delete database tables.  Careful of the order of deletion!
 **/
function ifcrush_deactivate()
{
    global $wpdb; 
    
	/** drop this first before deleting event **/    
	$table_name = $wpdb->prefix . "ifc_eventreg";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
 	/** drop this first before deleting frat **/       
    $table_name = $wpdb->prefix . "ifc_bid";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
    /** drop this first before deleting frat **/       
    $table_name = $wpdb->prefix . "ifc_event";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

}
register_deactivation_hook( __FILE__, 'ifcrush_deactivate');

/**
 * Add stylesheet to the page
 **/
function safely_add_stylesheet() {
	wp_enqueue_style( 'prefix-style', plugins_url('css/ifcrushstyle.css', __FILE__) );
}
add_action( 'wp_enqueue_scripts', 'safely_add_stylesheet' );



/**
 * These are the functions to wire in the shortcodes
 **/ 
include 'ifcrush_frat.php';  /** This has all the Frat table support **/
add_shortcode( 'ifcrush_display_frat_table',   'ifcrush_display_frat_table' );

include 'ifcrush_pnm.php';  /** This has all the Rushee table support **/
add_shortcode( 'ifcrush_display_pnms',   'ifcrush_display_pnms' );

include 'ifcrush_event.php';  /** This has all the Rushee table support **/
add_shortcode( 'ifcrush_display_event_table',   'ifcrush_display_event_table' );

include 'ifcrush_eventreg.php';
add_shortcode('ifcrush_display_eventreg_table', 'ifcrush_display_eventreg_table');

include 'ifcrush_reports.php';
add_shortcode('ifcrush_display_reports', 'ifcrush_display_reports');

include 'ifcrush_user_support.php';
add_action( 'register_form', 'ifcrush_register_form' );?>