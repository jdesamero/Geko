<?php

// ini_set( 'display_errors', 1 );
// error_reporting( E_ALLÊ^ÊE_NOTICE );
// error_reporting( E_ALL );

if ( !file_exists( '../../../wp-config.php' ) ) die ( 'wp-config.php not found' );
require_once( '../../../wp-config.php' );

$oPluginAdmin = Geko_Wp_NavigationManagement_PluginAdmin::getInstance();
$oPluginAdmin->procSave();

header( 'Location: ' . $oPluginAdmin->getRedirect() );

