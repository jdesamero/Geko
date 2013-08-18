<?php
/*
Plugin Name: Geek Oracle Password Protect
Plugin URI: http://geekoracle.com
Description: Password protects site frontend from prying eyes.
Version: TRUNK
Author: Joel Desamero
Author URI: http://geekoracle.com
*/

$sPluginClass = 'Geko_Wp_PasswordProtect';

require_once( 'includes/loader.inc.php' );

add_action( 'plugins_loaded', create_function( '', $sPluginFunc ) );


