<?php

require_once realpath( '../wp-load.php' );
require_once realpath( '../wp-admin/includes/admin.php' );

// ---------------------------------------------------------------------------------------------- //

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );

function exception_handler( $e ) {
	echo 'Uncaught exception: ' . $e->getMessage() . '<br /><br />';
	echo strval( $e );
}

set_exception_handler( 'exception_handler' );

// ---------------------------------------------------------------------------------------------- //

$sSendToEmail = 'asgas@ahsakhskajs.com';		// fake, will bounce
$sSendToName = 'Some Fake User';				// fake, will bounce

// $sSendToEmail = 'jdesamero@gmail.com';			// real email
// $sSendToName = 'Joel Desamero';				// fake, will bounce

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //

$sEmail = 'bounce@geekoracle.com';
$sEmailName = 'Geko Bounce Handler';
$sPassword = '!changeme';
$sImapServer = 'imap.gmail.com';
$sSmtpServer = 'smtp.gmail.com';

$aImapConfig = array(
	'host' => $sImapServer,
	'ssl' => TRUE,
	'port' => 993,
	'folder' => 'inbox',
	'user' => $sEmail,
	'password' => $sPassword
);

$aSmtpConfig = array(
	'ssl' => 'tls',
	'port' => 587,
	'auth' => 'login',
	'username' => $sEmail,
	'password' => $sPassword
);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //

$sUser2 = 'surveys';
$sEmail2 = 'surveys@eccles-ins.com';
$sEmailName2 = 'Eccl Bounce Handler';
$sPassword2 = '#developer2013';
$sImapServer2 = 'mail.eccles-ins.com';
$sSmtpServer2 = 'mail.eccles-ins.com';

$aImapConfig2 = array(
	'host' => $sImapServer2,
	'ssl' => TRUE,
	'port' => 993,
	'folder' => 'inbox',
	'user' => $sUser2,
	'password' => $sPassword2
);

$aSmtpConfig2 = array(
	'ssl' => 'ssl',
	'port' => 143,
	// 'auth' => 'login',
	'username' => $sEmail2,
	'password' => $sPassword2
);

// ---------------------------------------------------------------------------------------------- //

//// Transport Test

/* /

$oTr = new Zend_Mail_Transport_Smtp( $sSmtpServer, $aSmtpConfig );	

print_r( $oTr );

echo '<br /><br />';

// $oTr2 = Geko_Wp_EmailMessage_Transport::factory( 1 );
$oTr2 = Geko_Wp_EmailMessage_Transport::factory( 'geko-bounce-handler' );

print_r( $oTr2 );

echo '<br /><br />';

$oEmsg = new Geko_Wp_EmailMessage( 'alt-survey-notification' );

print_r( $oEmsg->getTransport() );

/* */

// ---------------------------------------------------------------------------------------------- //

/* /
// Storage Test
$oSt = new Zend_Mail_Storage_Imap( $aImapConfig );
print_r( $oSt );
echo '<br /><br />';
/* */

/* /
// $oSt2 = Geko_Wp_EmailMessage_Storage::factory( 1 );
$oSt2 = Geko_Wp_EmailMessage_Storage::factory( 'geko-bounce-handler' );
print_r( $oSt2 );
/* */

/* /
$oSt = new Zend_Mail_Storage_Imap( $aImapConfig2 );
print_r( $oSt );
/* */

/* /
$oSt2 = Geko_Wp_EmailMessage_Storage::factory( 'eccl-bounce-handler' );
print_r( $oSt2 );
/* */

// ---------------------------------------------------------------------------------------------- //

/* /

function foo( $aCustom, $oMsg, $oStrg ) {
	
	$sContent = $oMsg->getContent();
	
	// campaign id
	$aRegs = array();
	if ( preg_match( '/X-EcclCmpId: ([0-9]+)/', $sContent, $aRegs ) ) {
		$aCustom[ 'eccl_cmp_id' ] = $aRegs[ 1 ];
	}
	
	// contact id
	$aRegs = array();
	if ( preg_match( '/X-EcclContId: ([0-9]+)/', $sContent, $aRegs ) ) {
		$aCustom[ 'eccl_cont_id' ] = $aRegs[ 1 ];
	}
	
	return $aCustom;
}

add_filter( 'Geko_Wp_EmailMessage_Storage::saveMessagesToDb::custom', 'foo', 10, 3 );

$oSt = new Geko_Wp_EmailMessage_Storage( 'geko-bounce-handler' );
$oSt->saveMessagesToDb();

/* */

// ---------------------------------------------------------------------------------------------- //

/* /


$oMail = new Zend_Mail_Storage_Imap( $aImapConfig );


echo '<br /><hr /><br />';

foreach ( $oMail as $i => $oMsg ) {
	
	$oMsg = new Geko_Wp_EmailMessage_Storage_Message( $oMsg );
	
	echo sprintf( '<b>index</b>: %s<br />', $i );
	echo sprintf( '<b>is multipart</b>: %s<br />', $oMsg->isMultipart() ? 'yes' : 'no' );
	echo sprintf( '<b>is a bounce</b>: %s<br />', $oMsg->isBounce() ? 'yes' : 'no' );
	echo sprintf( '<b>status code</b>: %s<br />', $oMsg->getDeliveryStatusCode() );
	echo sprintf( '<b>status details</b>: %s<br />', $oMsg->getDeliveryStatusDetails() );
	
	echo '<br /><br />';
	
	$aHeaders = $oMsg->getHeaders();
	
	foreach ( $aHeaders as $sName => $mValue ) {
		echo sprintf( '<b>%s</b>: %s<br />', $sName, $oMsg->getHeaderSafe( $sName ) );
	}
	
	echo '<br /><br />';
	echo $oMsg->getContent();
	echo '<br /><hr /><br />';
}

/* */


echo 'aaa<br />';

if ( $_GET[ 'mail' ] ) {
	
	/* /
	$oTr = new Zend_Mail_Transport_Smtp( $sSmtpServer, $aSmtpConfig );	
	// Zend_Mail::setDefaultTransport( $oTr );
	
	$oMail = new Zend_Mail();
	$oMail
		->setFrom( $sEmail, $sEmailName )
		->addTo( $sSendToEmail, $sSendToName )
		->setBodyText( 'This is the text of the mail. Geko' )
		->setSubject( 'Bounce Test Subject. Geko' )
		->send( $oTr )
	;
	
	echo 'bbb<br />';
	/* */
	
	/* /
	$oTr2 = new Zend_Mail_Transport_Smtp( $sSmtpServer2, $aSmtpConfig2 );	
	// Zend_Mail::setDefaultTransport( $oTr2 );
	
	$oMail2 = new Zend_Mail();
	$oMail2
		->setFrom( $sEmail2, $sEmailName2 )
		->addTo( $sSendToEmail, $sSendToName )
		->setBodyText( 'This is the text of the mail. Eccl' )
		->setSubject( 'Bounce Test Subject. Eccl' )
		->send( $oTr2 )
	;
	
	echo 'ccc<br />';
	/* */
	
}

/* /
$oDeliver = new Geko_Wp_EmailMessage_Delivery( 'survey-notification' );
$oDeliver
	->addRecipient( 'jdesamero@gmail.com', 'Joel Desamero' )
	->setMergeParams( array(
		'survey_title' => 'Some Survey',
		'survey_url' => 'http://apple.com'
	) )
	->send()
;
/* */

echo 'ddd<br />';

