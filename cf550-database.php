<?php defined( 'ABSPATH' ) or die( 'Access Denied, get lost!' );
///////////////////////
////    DATABASE    ///
///////////////////////
register_activation_hook( __FILE__, 'cf_db_install' );
add_action( 'plugins_loaded', 'cf_db_install' );
global $cf_db_version;
$cf_db_version = '1.4.1';

function cf_db_install() {
	global $wpdb;
	global $cf_db_version;

	if ( get_site_option( 'cf_db_version' ) != $cf_db_version 
            || get_site_option("cf_db_version") == false ) {
				
		$charset_collate = $wpdb->get_charset_collate();

		$cf_programs = "CREATE TABLE ".$wpdb->prefix . "cf_programs (
			id int(9) NOT NULL AUTO_INCREMENT,
			date date DEFAULT '0000-00-00' NOT NULL,
			title text,
			description text,
			PRIMARY KEY  (id)
		) $charset_collate;";

        
        $cf_attendance = "CREATE TABLE ".$wpdb->prefix . "cf_attendance (
			id int(9) NOT NULL AUTO_INCREMENT,
			program_id int(9),
			member_ssn text,
			score text,
			date date,
			time time,
			day text,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$cf_members = "CREATE TABLE ".$wpdb->prefix . "cf_members (
			id int(9) NOT NULL AUTO_INCREMENT,
			name text NOT NULL,
			ssn text NOT NULL,
			created date DEFAULT '0000-00-00' NOT NULL,
			access_hash text,
			PRIMARY KEY  (id),
			UNIQUE (ssn(15))
		) $charset_collate;";

		$cf_subscription = "CREATE TABLE ".$wpdb->prefix . "cf_subscription (
			id int(9) NOT NULL AUTO_INCREMENT,
			name text,
			type text,
			value text,
			description text,
			crossfit boolean,
			PRIMARY KEY (id)
		) $charset_collate;";

		$cf_purchase = "CREATE TABLE ".$wpdb->prefix . "cf_purchase (
			id int(9) NOT NULL AUTO_INCREMENT,
			member_ssn text,
			subscription_id int(9),
			date date DEFAULT '0000-00-00' NOT NULL,
			time time DEFAULT '00:00:00' NOT NULL,
			frozen boolean DEFAULT 0 NOT NULL,
			frozen_remainder text,
			unfrozen date DEFAULT '0000-00-00' NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $cf_programs );
		dbDelta( $cf_attendance );
		dbDelta( $cf_members );
		dbDelta( $cf_subscription );
		dbDelta( $cf_purchase );

		update_option( 'cf_db_version', $cf_db_version );
	}
}
?>