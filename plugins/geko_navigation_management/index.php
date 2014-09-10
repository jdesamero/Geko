<?php
/*
Plugin Name: Geek Oracle Navigation Management
Plugin URI: http://geekoracle.com
Description: Provides a management system for Zend_Navigation based menus.
Version: TRUNK
Author: Joel Desamero
Author URI: http://geekoracle.com
*/

require_once( 'includes/loader.inc.php' );

$sPluginClass = 'Geko_Wp_NavigationManagement';
$sFile = __FILE__;

add_action( 'plugins_loaded', function() use( $sPluginClass, $sFile ) {
	geko_load_plugin( $sPluginClass, $sFile );
} );


