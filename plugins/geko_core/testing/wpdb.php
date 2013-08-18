<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );

require_once realpath( '../wp-load.php' );
require_once realpath( '../wp-admin/includes/admin.php' );

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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title>Icons</title>
	
</head>

<body>

<?php

global $wpdb;

// returns true or false

/* /
var_dump( $wpdb->insert(
	'wp_commentmeta',
	array(
		'comment_id' => 13,
		'meta_key' => 'roo',
		'meta_value' => 'rar nma'
	)
) );
/* */


echo $wpdb->prepare( 'id = %d', 101 );
echo '<br />';
echo $wpdb->prepare( 'id = %d', 'furdi-mouse' );
echo '<br />';
echo $wpdb->prepare( 'id = %d', 55.6 );
echo '<br />';
echo $wpdb->prepare( 'id = %d', '55.6' );
echo '<br />';
echo $wpdb->prepare( 'id = %d', '10778' );
echo '<br /><br />';

echo $wpdb->prepare( 'slug = %s', 'furdi-mouse' );
echo '<br /><br />';

echo $wpdb->prepare( 'percentage = %f', 0.71 );
echo '<br />';
echo $wpdb->prepare( 'percentage = %f', 'furdi-mouse' );
echo '<br />';
echo $wpdb->prepare( 'percentage = %f', 3723.23929232323 );
echo '<br /><br />';

?>

</body>

</html>
