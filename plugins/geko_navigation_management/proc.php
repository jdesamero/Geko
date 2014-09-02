<?php

// ini_set( 'display_errors', 1 );
// error_reporting( E_ALLÊ^ÊE_NOTICE );
// error_reporting( E_ALL );

if ( !file_exists( '../../../wp-load.php' ) ) die ( 'wp-load.php not found' );

require_once( realpath( '../../../wp-load.php' ) );
require_once( realpath( '../../../wp-admin/includes/admin.php' ) );

$oPluginAdmin = Geko_Wp_NavigationManagement_PluginAdmin::getInstance();
$oPluginAdmin->procSave();

header( sprintf( 'Location: %s', $oPluginAdmin->getRedirect() ) );


