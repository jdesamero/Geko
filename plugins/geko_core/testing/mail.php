<?php

require_once realpath( '../wp-load.php' );
require_once realpath( '../wp-admin/includes/admin.php' );

// ---------------------------------------------------------------------------------------------- //

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );


echo mail( 'joel@geekoracle.com', 'This is a test email mail()', 'This is a test email mail()' );
// echo mail( 'jdesamero@gmail.com', 'This is a test email mail()', 'This is a test email mail()' );

/* */
$oMail = new Zend_Mail();
$oMail
	->addTo( 'joel@geekoracle.com', 'Joel Desamero' )
	// ->addTo( 'jdesamero@gmail.com', 'Furdi Mouse' )
	->setBodyText( 'This is a test email Zend' )
	->setSubject( 'This is a test email Zend' )
	->send()
;
/* */

