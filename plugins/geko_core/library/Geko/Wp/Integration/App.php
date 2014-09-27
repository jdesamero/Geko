<?php

//
class Geko_Wp_Integration_App extends Geko_Integration_App_Abstract
{
	const CODE = 'wp';
	protected $sCode = 'wp';
	
	
	//
	public function detect() {
		return ( defined( 'ABSPATH' ) && defined( 'WPINC' ) );
	}
	
	//
	public function _getKey()	 {
		return ( defined( 'WP_ADMIN' ) && ( TRUE == WP_ADMIN ) ) ? 'wp-admin' : 'wp-theme' ;
	}
	
	//
	public function getDbConn() {
		global $wpdb;
		return $wpdb->dbh;
	}
	
}


