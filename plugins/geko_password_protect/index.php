<?php
/*
Plugin Name: Geek Oracle Password Protect
Plugin URI: http://geekoracle.com
Description: Password protects site frontend from prying eyes.
Version: TRUNK
Author: Joel Desamero
Author URI: http://geekoracle.com
*/

require_once( 'includes/loader.inc.php' );

$sPluginClass = 'Geko_Wp_PasswordProtect';
$sFile = __FILE__;

add_action( 'plugins_loaded', function() use( $sPluginClass, $sFile ) {
	geko_load_plugin( $sPluginClass, $sFile );
} );


