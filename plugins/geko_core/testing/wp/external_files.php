<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );

require_once realpath( '../../../../../wp-load.php' );
require_once realpath( '../../../../../wp-admin/includes/admin.php' );

// ---------------------------------------------------------------------------------------------- //

/* /
// do checks
if ( !is_user_logged_in() || !current_user_can( 'administrator' ) ) {
	die();
}

ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );
/* */

define( 'GOOGLEMAPS_API_KEY', 'ABQIAAAAwBIp2sV11qquJPyY6gHZohSC-eu3n-KIEZJw3s9gcs77pIbZSRQf5lbkzAphpjuwINBWgWfTuS22vA' );

// define( 'JQUERY_UI_VERSION', '1.3.2' );
define( 'JQUERY_UI_VERSION', '1.4.2' );
// define( 'JQUERY_UI_VERSION', '1.9.2' );

// define( 'JQUERY_VERSION', '1.3.2' );
// define( 'JQUERY_VERSION', '1.4.2' );
define( 'JQUERY_VERSION', '1.8.3' );
// define( 'JQUERY_VERSION', '1.9.1' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
</head>

<body>

<pre><?php

$oLoader = Geko_Loader_ExternalFiles::getInstance();

$oLoader
	->registerFromXmlConfigFile( '../conf/register_extra.xml' )
	->registerFromXmlConfigFile( '../conf/register.xml' )
	->registerStyle( 'some-style', 'styles/foo.css' )
;


$oLoader
	->enqueueScript( 'geko_wp_form_manage' )
	->enqueueScript( 'geko-jquery-googlemaps' )
;

$oLoader
	->enqueueStyle( 'geko-jquery-ui-wp' )
	->enqueueStyle( 'some-style' )
;

// $oLoader->debug();

?></pre>

<?php

$oLoader->renderScriptTags();
$oLoader->renderStyleTags();

?>

</body>

</html>
