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
/* */

/* /
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
	<title>Points</title>
</head>

<body>

<h1>Points</h1>

<?php

$oPtMng = Geko_Wp_Point_Manage::getInstance();

$aCheck = array(
	// 'testing_only' => TRUE,
	// 'user_id' => 1,
	'email' => 'jdesamero@gmail.com',
	// 'point_event_slug' => 'sign-up',
	// 'point_event_slug' => 'question-submission',
	// 'point_event_slug' => 'address-information',
	'point_event_slug' => 'time-limit-test',
	// 'point_event_slug' => 'redeem',
	// 'point_value' => 500,
	/* 'meta' => array(
		// 'cforms_submission_id' => 0,
		// 'cforms_submission_id' => 1,
		'cforms_submission_id' => 999,
		'foo' => 'bar'
	) */
);

$iErrorCode = 0;

echo 'Has Points:<br />';

var_dump( $oPtMng->hasPoints( $aCheck, $iErrorCode ) );
echo '<br />';

var_dump( $iErrorCode );

echo '<br /><br />';

echo 'Award Points:<br />';

/* */

$aAward = array(
	// 'testing_only' => TRUE,
	// 'user_id' => 1,
	'email' => 'jdesamero@gmail.com',
	'point_event_slug' => 'time-limit-test',
	// 'point_event_slug' => 'sign-up',
	// 'point_event_slug' => 'question-submission',
	// 'point_event_slug' => 'redeem',
	// 'point_value' => 500,
	/* 'meta' => array(
		'cforms_submission_id' => 1
	) */
);

/* /

/* /

var_dump( $oPtMng->awardPoints( $aAward ) );

echo '<br /><br />';
echo 'Has Points:<br />';
var_dump( $oPtMng->hasPoints( $aCheck ) );

/* /
var_dump( $oPtMng->hasPoints( array(
	'email' => 'jdesamero@gmail.com',
	'point_event_slug' => 'widget-click',
	'meta' => array(
		'widget-code' => 'featured-product'
	)
) ) );
/* */

?>

</body>

</html>
