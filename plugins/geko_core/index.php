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

// needed for domain mirror
$sPluginUrl = preg_replace(
	'/(http|https):\/\/(.+)\/wp-content(.+)/',
	sprintf( '%s/wp-content$3', get_bloginfo( 'wpurl' ) ),
	plugins_url( '', __FILE__ )
);

define( 'GEKO_CORE_ROOT', realpath( dirname( __FILE__ ) ) );
define( 'GEKO_CORE_URI', $sPluginUrl );
define( 'GEKO_CORE_EXTERNAL_LIB_ROOT', realpath( sprintf( '%s/external/libs', GEKO_CORE_ROOT ) ) );
define( 'GEKO_LOG', realpath( sprintf( '%s/wp-content/logs/logs.txt', ABSPATH ) ) );
define( 'GEKO_REGISTER_XML', realpath( sprintf( '%s/conf/register.xml', GEKO_CORE_ROOT ) ) );
define( 'GEKO_VIEW_HELPER_PATH', realpath( sprintf( '%s/library', GEKO_CORE_ROOT ) ) );

define( 'GEKO_IMAGE_THUMB_CACHE_DIR', realpath( sprintf( '%s/wp-content/cache/', ABSPATH ) ) );
define( 'GEKO_IMAGE_THUMB_CACHE_URI', sprintf( '%s/wp-content/cache/', get_bloginfo( 'wpurl' ) ) );



// include path
set_include_path( implode( PATH_SEPARATOR, array_filter( array(
	realpath( sprintf( '%s/ZendFramework-1.10.6-geko/library', GEKO_CORE_EXTERNAL_LIB_ROOT ) ),
	realpath( sprintf( '%s/library', GEKO_CORE_ROOT ) ),
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
	'wp_admin' => sprintf( '%s/wp-admin/admin.php', Geko_Wp::getUrl() ),
	'wp_login' => sprintf( '%s/wp-login.php', Geko_Wp::getUrl() ),
	'wp_user_edit' => sprintf( '%s/wp-admin/user-edit.php', Geko_Wp::getUrl() ),
	'geko_export' => sprintf( '%s/srv/export.php', GEKO_CORE_URI ),
	'geko_pdf' => sprintf( '%s/srv/pdf.php', GEKO_CORE_URI ),
	'geko_process' => sprintf( '%s/srv/process.php', GEKO_CORE_URI ),
	'geko_thumb' => sprintf( '%s/srv/thumb.php', GEKO_CORE_URI ),
	'geko_upload' => sprintf( '%s/srv/upload.php', GEKO_CORE_URI ),
	'geko_styles' => sprintf( '%s/styles', GEKO_CORE_URI ),
	'geko_ext' => sprintf( '%s/external', GEKO_CORE_URI ),
	'geko_ext_images' => sprintf( '%s/external/images', GEKO_CORE_URI ),
	'geko_ext_styles' => sprintf( '%s/external/styles', GEKO_CORE_URI ),
	'geko_ext_swf' => sprintf( '%s/external/swf', GEKO_CORE_URI )
) );



// image thumbnailer
Geko_Image_Thumb::setCacheDir( GEKO_IMAGE_THUMB_CACHE_DIR );



//// logger

$aLoggerParams = array();

if ( defined( 'GEKO_LOG_DISABLED' ) && GEKO_LOG_DISABLED ) {
	$iLoggerType = Geko_Log::WRITER_DISABLED;
} elseif ( defined( 'GEKO_LOG_FIREBUG' ) && GEKO_LOG_FIREBUG ) {
	$iLoggerType = Geko_Log::WRITER_FIREBUG;
} elseif ( defined( 'GEKO_LOG_STREAM' ) && GEKO_LOG_STREAM ) {
	$iLoggerType = Geko_Log::WRITER_STREAM;
} else {
	if ( is_file( GEKO_LOG ) ) {
		$iLoggerType = Geko_Log::WRITER_FILE;
		$aLoggerParams[ 'file' ] = GEKO_LOG;
	} else {
		$iLoggerType = Geko_Log::WRITER_STREAM;
	}
}

$oLogger = new Geko_Log( $iLoggerType, $aLoggerParams );
Zend_Registry::set( 'logger', $oLogger );


