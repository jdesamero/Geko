<?php

//// bootstrap

// path constants

define( 'GEKO_CORE_EXTERNAL_LIB_ROOT', realpath( sprintf( '%s/external/libs', GEKO_CORE_ROOT ) ) );
define( 'GEKO_LOG', realpath( sprintf( '%s/logs/logs.txt', GEKO_STANDALONE_PATH ) ) );
define( 'GEKO_REGISTER_EXTRA_XML', realpath(  sprintf( '%s/conf/register_extra.xml', GEKO_CORE_ROOT ) ) );
define( 'GEKO_REGISTER_XML', realpath( sprintf( '%s/conf/register.xml', GEKO_CORE_ROOT ) ) );
define( 'GEKO_VIEW_HELPER_PATH', realpath( sprintf( '%s/library', GEKO_CORE_ROOT ) ) );



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
	'Geko_', 'GekoTest_', 'GekoX_', 'Gloc_', 'Tmpl_', 'Srv_', 'phpQuery_',
	'Mime_Types_', 'PEAR_', 'Console_', 'OLE_', 'Spreadsheet_', 'WideImage_'
);



$oLoader = Geko_Loader_ExternalFiles::getInstance();
$oLoader
	->setMergeParams( array(
		'geko_core_root' => GEKO_CORE_ROOT,
		'geko_core_uri' => GEKO_CORE_URI
	) )
	->registerFromXmlConfigFile( GEKO_REGISTER_EXTRA_XML )
	->registerFromXmlConfigFile( GEKO_REGISTER_XML )
;


if ( method_exists( 'Geko_Constant_Values', 'getUrls' ) ) {
	$oLoader->setMergeParams( Geko_Constant_Values::getUrls() );
}


if ( defined( 'GEKO_TEMPLATE_URL' ) ) {
	$oLoader->setMergeParams( array(
		'geko_template_url' => GEKO_TEMPLATE_URL
	) );
}

if ( defined( 'GEKO_TEMPLATE_PATH' ) ) {
	
	$oLoader->setMergeParams( array(
		'geko_template_path' => GEKO_TEMPLATE_PATH
	) );
	
	$sRegFile = sprintf( '%s/etc/register.xml', GEKO_TEMPLATE_PATH );
	if ( is_file( $sRegFile ) ) {
		$oLoader->registerFromXmlConfigFile( $sRegFile );
	}
}


// register global urls to services
Geko_Uri::setUrl( array(
	'geko_export' => sprintf( '%s/srv/export.php', GEKO_CORE_URI ),
	'geko_pdf' => sprintf( '%s/srv/pdf.php', GEKO_CORE_URI ),
	'geko_process' => sprintf( '%s/srv/process.php', GEKO_CORE_URI ),
	'geko_thumb' => sprintf( '%s/srv/thumb.php', GEKO_CORE_URI ),
	'geko_upload' => sprintf( '%s/srv/upload.php', GEKO_CORE_URI ),
	'geko_styles' => sprintf( '%s/styles', GEKO_CORE_URI ),
	'geko_ext_styles' => sprintf( '%s/external/styles', GEKO_CORE_URI ),
	'geko_ext_swf' => sprintf( '%s/external/swf', GEKO_CORE_URI ),
	'geko_app_srv' => sprintf( '%s/srv', GEKO_CORE_URI )
) );



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


