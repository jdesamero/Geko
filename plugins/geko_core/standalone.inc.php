<?php

//// bootstrap

// path constants

define( 'GEKO_CORE_EXTERNAL_LIB_ROOT', realpath( sprintf( '%s/external/libs', GEKO_CORE_ROOT ) ) );
define( 'GEKO_LOG', realpath( sprintf( '%s/logs/logs.txt', GEKO_STANDALONE_PATH ) ) );
define( 'GEKO_REGISTER_EXTRA_XML', realpath(  sprintf( '%s/conf/register_extra.xml', GEKO_CORE_ROOT ) ) );
define( 'GEKO_REGISTER_XML', realpath( sprintf( '%s/conf/register.xml', GEKO_CORE_ROOT ) ) );
define( 'GEKO_GEOGRAPHY_XML', realpath( sprintf( '%s/conf/geography.xml', GEKO_CORE_ROOT ) ) );
define( 'GEKO_VIEW_HELPER_PATH', realpath( sprintf( '%s/library', GEKO_CORE_ROOT ) ) );



// include path
set_include_path( implode( PATH_SEPARATOR, array_filter( array(
	realpath( sprintf( '%s/ZendFramework-1.12.9-minimal/library', GEKO_CORE_EXTERNAL_LIB_ROOT ) ),
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
	'/recaptcha'
);



// manually require files
require_once 'recaptchalib.php';

// register class namespaces
Geko_Loader::registerNamespaces(
	'Geko_', 'GekoTest_', 'GekoX_', 'Gloc_', 'Tmpl_', 'Srv_', 'phpQuery_',
	'PEAR_', 'Console_', 'OLE_', 'Spreadsheet_', 'WideImage_'
);





