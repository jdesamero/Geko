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
	<title>Html Elements</title>
	
</head>

<body>

<?php

$oSpan = new Geko_Html_Element_Span();
$oSpan->append( 'Apple Computers' );
$oSpan->setId( 'some_span' );

$oSpan2 = new Geko_Html_Element_Span();
$oSpan2->append( 'Microsoft' );
$oSpan2->setId( 'some_span_2' );

$oA = new Geko_Html_Element_A();
// $oA->_setHtmlVersion( 5 );

$oA
	->setHref( 'http://apple.com' )
	->setFoo( 'mouse' )
	->setClass( 'mouse rat pork' )
	// ->append( 'Apple Website' )
	->append( $oSpan )
	// ->append( $oSpan2 )
	->prepend( $oSpan2 )
;

$oA
	->addClass( 'chicken shrimp' )
	->removeClass( 'rat shrimp' )
;

$oInput = new Geko_Html_Element_Input();
$oInput
	->setName( 'foo' )
	->setId( 'foo' )
;

var_dump( $oA->hasClass( 'rat mouse' ) );
var_dump( $oA->hasClass( 'rat moose' ) );

echo strval( $oA ) . '<br />';
echo strval( $oInput ) . '<br />';

$sValues = ' mouse  rat   pork ';
$aFoo = Geko_Array::explodeTrimEmpty( ' ', $sValues );
print_r( $aFoo ) . '<br />';

$sValues = '  ';
$aFoo = Geko_Array::explodeTrimEmpty( ' ', $sValues );
var_dump( $aFoo );
echo '<br />';

$oA2 = Geko_Html_Element::create( 'a', array( 'class' => 'xxx', 'href' => 'http://ms.com' ), 'Do It!' );
echo strval( $oA2 );
echo '<br />';

$oA3 = Geko_Html_Element::create( 'a' );
$oA3
	->setClass( 'foo bar baz' )
	->setHref( 'http://ms.com' )
	->append( 'Moo Cow' )
;

echo strval( $oA3 );
echo '<br />';


?>

<!--
<form>
	<input type="text" /><br />
	<input type="datetime" /><br />
	<input type="color" /><br />
	<input type="range" /><br />
</form>
-->

</body>

</html>
