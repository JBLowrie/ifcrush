<?php
/**
 * @package IFCRushKL
 * @version 0.1
 */
/*
Plugin Name: IFC Rush Kate's
Description: This plugin stores data for Frats and Rushees for rush events.  This is KLs version
Author: Lowrie
Version: 0.1
Author URI: nope
*/
global $ifcrush_db_version;
$ifcrush_db_version = "1.0";

/**
 * ifcrush_install() - creates the tables that store Frats/Rushees/Events
 **/
function ifcrush_install(){

	global $wpdb;
	global $ifcrush_db_version;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   
	$rushee_table_name = $wpdb->prefix . "ifc_rushee";    
	$sql = 	"CREATE TABLE $rushee_table_name (
				netID		varchar(6) not null,
				firstName	varchar(15) not null,
				lastName	varchar(15) not null,
				rushstat	varchar(3) not null,
				gradYear    int,
				school		varchar(8) not null,
				residence	varchar(10) not null,
				email		varchar(30) not null,
				PRIMARY KEY(netID)
			) engine = InnoDB;";
	dbDelta( $sql );
   
	$frat_table_name = $wpdb->prefix . "ifc_fraternity";    
	$sql = 	"CREATE TABLE $frat_table_name (
		fullname varchar(15) not null,
		letters varchar(3) not null,
		rushchair varchar(15) not null,
		email varchar(30) not null,
		PRIMARY KEY(letters)
	) engine = InnoDB;";

   	dbDelta( $sql );

	$event_table_name = $wpdb->prefix . "ifc_event";    
	$sql = 	"CREATE TABLE $event_table_name (
		eventDate date not null,
		title varchar(30) not null,
		eventID int not null auto_increment,
		fratID varchar(3) not null,
		PRIMARY KEY(eventID),
		FOREIGN KEY(fratID) references $frat_table_name(letters)
	) engine = InnoDB;";
   dbDelta( $sql );

	$table_name = $wpdb->prefix . "ifc_bid";    
	$sql = 	"CREATE TABLE $table_name(
		bidstat int not null,
		rusheeID		varchar(6) not null,
		fratID varchar(3) not null,
		FOREIGN KEY (rusheeID) references $rushee_table_name(netID),
		FOREIGN KEY (fratID) references $frat_table_name(letters)
	) engine = InnoDB;";
   dbDelta( $sql );

	$table_name = $wpdb->prefix . "ifc_eventreg";    
	$sql = 	"CREATE TABLE $table_name (
		rusheeID		varchar(6) not null,
		eventID int not null auto_increment,
		FOREIGN KEY (rusheeID) references $rushee_table_name(netID),
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
	ifcrush_install_frats();
	ifcrush_install_rushees();
	ifcrush_install_events();
}
register_activation_hook( __FILE__, 'ifcrush_install_data' );


/** ifcrush_deactivate() - cleans up when the plugin is deactived, 
 ** delete database tables.  Careful of the order of deletion!
 **/
function ifcrush_deactivate()
{
    global $wpdb; 
    
	/** drop this first before deleting rushee and event **/    
	$table_name = $wpdb->prefix . "ifc_eventreg";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
 	/** drop this first before deleting rushee and frat **/       
    $table_name = $wpdb->prefix . "ifc_bid";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
    /** drop this first before deleting frat **/       
    $table_name = $wpdb->prefix . "ifc_event";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    
	$table_name = $wpdb->prefix . "ifc_rushee";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

	$table_name = $wpdb->prefix . "ifc_fraternity";    
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_deactivation_hook( __FILE__, 'ifcrush_deactivate');

/**
 * These are the functions to wire in the shortcodes
 **/ 
include 'ifcrush_frat.php';  /** This has all the Frat table support **/
add_shortcode( 'ifcrush_display_frat_table',   'ifcrush_display_frat_table' );

include 'ifcrush_rushee.php';  /** This has all the Rushee table support **/
add_shortcode( 'ifcrush_display_rushee_table',   'ifcrush_display_rushee_table' );

include 'ifcrush_event.php';  /** This has all the Rushee table support **/
add_shortcode( 'ifcrush_display_event_table',   'ifcrush_display_event_table' );

include 'ifcrush_eventreg.php';
add_shortcode('ifcrush_display_eventreg_table', 'ifcrush_display_eventreg_table')


?>