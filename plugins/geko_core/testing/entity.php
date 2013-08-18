<?php

require_once realpath( '../wp-load.php' );
require_once realpath( '../wp-admin/includes/admin.php' );

/* /
ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );
/* */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Entity</title>
	<style type="text/css">
		
	</style>
</head>

<body>

<h1>Entity</h1>

<h2>Control Test</h2>

<?php

function dumpItem( $oItem ) {
	printf(
		'%s | %s | %s | %s | %s<br />',
		$oItem->getId(),
		$oItem->getValue( 'id' ),
		$oItem->getEntityPropertyValue( 'id' ),
		$oItem->getTitle(),
		$oItem->getSlug()
	);
}

print_r( explode( ':', 'foo' ) );
echo '<br />';
print_r( explode( ':', 'foo:bar' ) );

$oSqlDelete = new Geko_Sql_Delete();


echo '<br /><br /><br />';

$oItemMng = Geko_Wp_Form_Item_Manage::getInstance()->init();

// print_r( $oItemMng->updateRelatedWhere( 345 ) );
// print_r( $oItemMng->updateRelatedDelete( $oSqlDelete, array( 8, 6, 251 ) ) );

$aKeyFields = $oItemMng->getPrimaryTableKeyFields();
foreach ( $aKeyFields as $oField ) {
	printf(
		'%s | %s<br />',
		$oField->getFieldName(),
		$oField->getFormat()
	);
}

echo '<br />';

$aItems = new Geko_Wp_Form_Item_Query( array(
	'showposts' => -1,
	'posts_per_page' => -1
), FALSE );

foreach ( $aItems as $oItem ) {
	dumpItem( $oItem );
}

echo '<br /><br />';

$oItem = new Geko_Wp_Form_Item( 22 );

dumpItem( $oItem );

/* /
ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );
/* */

// var_dump( $oItem->getFurdi() );

?>

<hr />

<h2>Multi Keys</h2>

<?php

function dumpItemVal( $oItemVal ) {
	printf(
		'%s | %s | %s | %s | %s<br />',
		$oItemVal->getId(),
		$oItemVal->getValue( 'id' ),
		$oItemVal->getEntityPropertyValue( 'id' ),
		$oItemVal->getLabel(),
		$oItemVal->getSlug()
	);
}

// : -> %3A

$oItemValsMng = Geko_Wp_Form_ItemValue_Manage::getInstance()->init();

// print_r( $oItemValsMng->updateRelatedWhere( '345:56' ) );
// print_r( $oItemValsMng->updateRelatedDelete( $oSqlDelete, array( '345:56', '1:56', '9:9' ) ) );


$aKeyFields = $oItemValsMng->getPrimaryTableKeyFields();
foreach ( $aKeyFields as $oField ) {
	printf(
		'%s | %s<br />',
		$oField->getFieldName(),
		$oField->getFormat()
	);
}

echo '<br />';

$aItemVals = new Geko_Wp_Form_ItemValue_Query( array(
	'showposts' => -1,
	'posts_per_page' => -1
), FALSE );

foreach ( $aItemVals as $oItemVal ) {
	dumpItemVal( $oItemVal );
}

echo '<br /><br />';

$oItemVal = new Geko_Wp_Form_ItemValue( '32:2' );
dumpItemVal( $oItemVal );

$oItemVal = new Geko_Wp_Form_ItemValue( '12:1' );
dumpItemVal( $oItemVal );

$oItemVal = new Geko_Wp_Form_ItemValue( '39:3' );
dumpItemVal( $oItemVal );

/* /
$aRegs = array();
var_dump( preg_match( '/^([0-9]+):([0-9]+)$/', '32:2', $aRegs ) );
array_shift( $aRegs );
var_dump( $aRegs );
/* */

?>

<hr />

<?php

/* /
ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );
/* */

class SomeParent
{

	public function __call( $sMethod, $aArgs ) {
		
		if ( 'goo' == $sMethod ) {
			return 'GOO GOO GOO';
		}
		
	}

}


class SomeChild extends SomeParent
{

	public function goo() {
		return '{{{' . parent::goo() . '}}}';
	}

}

$oSomeChild = new SomeChild();

echo $oSomeChild->goo();

echo '<br /><br />';

var_dump( class_exists( 'Geko_Wp_Location_Plugin' ) );
var_dump( is_subclass_of( 'Geko_Wp_Location_Plugin', 'Geko_Wp_Options_Plugin' ) );

?>

</body>

</html>