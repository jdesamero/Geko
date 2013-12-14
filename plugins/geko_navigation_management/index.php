<?php
/*
Plugin Name: Geek Oracle Navigation Management
Plugin URI: http://geekoracle.com
Description: Provides a management system for Zend_Navigation based menus.
Version: TRUNK
Author: Joel Desamero
Author URI: http://geekoracle.com
*/

$sPluginClass = 'Geko_Wp_NavigationManagement';

require_once( 'includes/loader.inc.php' );

add_action( 'plugins_loaded', create_function( '', $sPluginFunc ) );


