<?php
/*
Plugin Name: Geek Oracle Core
Plugin URI: http://geekoracle.com
Description: Core plugin which contains shared libraries/code for other Geek Oracle themes and plugins.
Version: TRUNK
Author: Joel Desamero
Author URI: http://geekoracle.com
*/

//// bootstrap

// path constants

define( 'GEKO_CORE_ROOT', realpath( dirname( __FILE__ ) ) );
define( 'GEKO_CORE_URI', plugins_url( '', __FILE__ ) );
define( 'GEKO_CORE_EXTERNAL_LIB_ROOT', realpath( GEKO_CORE_ROOT . '/external/libs' ) );
define( 'GEKO_LOG', realpath( ABSPATH . '/wp-content/logs/logs.txt' ) );
define( 'GEKO_REGISTER_XML', realpath( GEKO_CORE_ROOT . '/conf/register.xml' ) );
define( 'GEKO_VIEW_HELPER_PATH', realpath( GEKO_CORE_ROOT . '/library' ) );



// include path
set_include_path( implode( PATH_SEPARATOR, array_filter( array(
	realpath( GEKO_CORE_EXTERNAL_LIB_ROOT . '/ZendFramework-1.10.6-geko/library' ),
	realpath( GEKO_CORE_ROOT . '/library' ),
	get_include_path()
) ) ) );


//// run class autoloaders

// include autoloader class
require_once 'Geko/Loader.php';

// register additional library paths
Geko_Loader::setLibRoot( GEKO_CORE_EXTERNAL_LIB_ROOT );
Geko_Loader::addLibRootPaths( 
	'/phpQuery-0.9.5.386/phpQuery',
	'/pearpkgs/PEAR-1.9.0/library',
	'/pearpkgs/PHPUnit-3.4.14/library',
	'/pearpkgs/Console_Getopt-1.3.1/library',
	'/pearpkgs/OLE-1.0.0RC1/library',
	'/pearpkgs/Spreadsheet_Excel_Writer-0.9.1/library',
	'/pearpkgs/WideImage-11.02.19/library',
	'/mime_types-0.1',
	'/recaptcha',
	'/moneris'
);



// manually require files
require_once 'PHPUnit/Framework.php';
require_once 'recaptchalib.php';
require_once 'mpgClasses.php';

// register class namespaces
Geko_Loader::registerNamespaces(
	'Geko_', 'GekoTest_', 'GekoX_', 'Gloc_', 'phpQuery_', 'Mime_Types_',
	'PEAR_', 'Console_', 'OLE_', 'Spreadsheet_', 'WideImage_'
);

// register JavaScript/CSS files
Geko_Wp::setStandardPlaceholders( array(
	'geko_core_root' => GEKO_CORE_ROOT,
	'geko_core_uri' => GEKO_CORE_URI
) );
Geko_Wp::registerExternalFiles( GEKO_REGISTER_XML );


// register global urls to services
Geko_Uri::setUrl( array(
	'wp_admin' => get_bloginfo( 'url' ) . '/wp-admin/admin.php',
	'wp_user_edit' => get_bloginfo( 'url' ) . '/wp-admin/user-edit.php',
	'geko_export' => GEKO_CORE_URI . '/srv/export.php',
	'geko_pdf' => GEKO_CORE_URI . '/srv/pdf.php',
	'geko_process' => GEKO_CORE_URI . '/srv/process.php',
	'geko_thumb' => GEKO_CORE_URI . '/srv/thumb.php',
	'geko_upload' => GEKO_CORE_URI . '/srv/upload.php',
	'geko_styles' => GEKO_CORE_URI . '/styles'
) );



//// logger

if ( is_file( GEKO_LOG ) ) {
	$oWriter = new Zend_Log_Writer_Stream( GEKO_LOG );
	$oLogger = new Zend_Log( $oWriter );
	Zend_Registry::set( 'logger', $oLogger );
}


/* /
function geko_core_debug() {
	$a = array(
		'ABSPATH' => ABSPATH,
		'GEKO_CORE_ROOT' => GEKO_CORE_ROOT,
		'GEKO_CORE_EXTERNAL_LIB_ROOT' => GEKO_CORE_EXTERNAL_LIB_ROOT,
		'GEKO_LOG' => GEKO_LOG,
		'GEKO_REGISTER_XML' => GEKO_REGISTER_XML,
		'include_path' => get_include_path()
	);
	echo '<pre>';
	print_r( $a );
	echo '</pre><br /><br /><br /><br /><br />';
}

add_action( 'admin_footer', 'geko_core_debug' );
/* */



