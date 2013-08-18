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
	<link rel="stylesheet" href="styles/form.css" type="text/css" media="screen" />
</head>

<body>

<div class="bg">
	<span class="geko-form-icon geko-form-icon-text"></span>
	<span class="geko-form-icon geko-form-icon-textarea"></span>
	<span class="geko-form-icon geko-form-icon-radio"></span>
	<span class="geko-form-icon geko-form-icon-checkbox"></span>
	<span class="geko-form-icon geko-form-icon-checkbox_multi"></span>
	<span class="geko-form-icon geko-form-icon-select"></span>
	<span class="geko-form-icon geko-form-icon-select_multi"></span>
	<br clear="all" />
	<span class="geko-form-icon geko-form-icon-file"></span>
	<span class="geko-form-icon geko-form-icon-password"></span>
	<span class="geko-form-icon geko-form-icon-plain_text"></span>
	<span class="geko-form-icon geko-form-icon-date"></span>
	<span class="geko-form-icon geko-form-icon-email"></span>
	<span class="geko-form-icon geko-form-icon-url"></span>
	<br clear="all" />
	<span class="geko-form-icon geko-form-icon-image"></span>
	<span class="geko-form-icon geko-form-icon-telephone"></span>
	<span class="geko-form-icon geko-form-icon-logo"></span>
	<span class="geko-form-icon geko-form-icon-submit"></span>
	<span class="geko-form-icon geko-form-icon-captcha"></span>
	<span class="geko-form-icon geko-form-icon-footer"></span>
	<br clear="all" />
	<span class="geko-form-icon geko-form-icon-header"></span>
	<span class="geko-form-icon geko-form-icon-spacer"></span>
	<span class="geko-form-icon geko-form-icon-break"></span>
	<span class="geko-form-icon geko-form-icon-number"></span>
	<span class="geko-form-icon geko-form-icon-header"></span>
	<span class="geko-form-icon geko-form-icon-regex"></span>
	<br clear="all" />
</div>

<br />
<br />

<img src="images/form_item_icons.png" />

</body>

</html>
