<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );

require_once realpath( '../../../../../wp-load.php' );
require_once realpath( '../../../../../wp-admin/includes/admin.php' );

$iModeId = Geko_PhpUnit::getModeId( $_GET[ 'mode' ] );
$sMode = Geko_PhpUnit::getModeKey( $iModeId );

$aJsonParams = array(
	'mode_id' =>  $iModeId,
	'mode' => $sMode
);

wp_enqueue_style( 'geko_phpunit' );
wp_enqueue_script( 'geko_phpunit' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title>PHPUnit</title>
	
	<?php wp_print_styles(); ?>
	<?php wp_print_scripts(); ?>
	
	<script type="text/javascript">
				
		jQuery( document ).ready( function( $ ) {
			
			var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
			
			$.gekoPhpUnit( oParams );
			
		} );
		
	</script>
	
</head>

<body class="<?php echo $sMode; ?>">

<h1>PHPUnit</h1>

<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
// error_reporting( E_ALL );

// $sPath = '../libs/GeekOracleExtensions-TRUNK/library';
$sPath = '../library';
Geko_PhpUnit::run( $sPath, 'GekoTest', $iModeId );

?>

</body>

</html>
