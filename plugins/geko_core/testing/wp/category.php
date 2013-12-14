<?php

require_once realpath( '../../../../../wp-load.php' );
require_once realpath( '../../../../../wp-admin/includes/admin.php' );

// ---------------------------------------------------------------------------------------------- //

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );



$oTmpl = Geko_Wp_Template::getInstance();
$aRes = $oTmpl->getTemplateValues( array(
	'prefix' => 'inscape-category-template',
	'attribute_name' => 'Category Template'
) );


print_r( $aRes );

