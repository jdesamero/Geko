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
</head>

<body>

<?php

$aFmMetaDataFmt = array();
$aFmMetaData = new Geko_Wp_Form_MetaData_Query( array(
	'form_id' => 1,
	'showposts' => -1,
	'posts_per_page' => -1
), FALSE );

foreach ( $aFmMetaData as $oFmMetaData ) {
	$aFmMetaDataFmt[] = array(
		'id' => $oFmMetaData->getId(),
		'fmitmtyp_id' => $oFmMetaData->getFmitmtypId(),
		'name' => $oFmMetaData->getName(),
		'slug' => $oFmMetaData->getSlug(),
		'rank' => $oFmMetaData->getRank(),
		'lang_id' => $oFmMetaData->getLangId(),
		'context_id' => $oFmMetaData->getContextId()
	);		
}

print_r( $aFmMetaDataFmt );


/*

wp_geko_form_item
	parent_itmvalidx_id
	parent_itm_id
	hide_subs
	
wp_geko_form_item_type
	has_choice_subs

wp_geko_form_item_value
	hide_items
	show_widgets

*/

?>

</body>

</html>
