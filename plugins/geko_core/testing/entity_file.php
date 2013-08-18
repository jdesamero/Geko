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
	<title>Entity File</title>

	<style type="text/css">
		
		h1, h2, th, td {
			font-family: 'Helvetica', 'Arial', 'sans-serif';
		}
		
		h2 {
			font-size: 18px;
		}
		
		th, td {
			font-size: 12px;
		}
		
		table {
			margin: 0;
			padding: 0;
			border: 0;
			border-collapse: collapse;
		}
		
		table tr th,
		table tr td {
			border-top: solid 1px #000;
			border-left: solid 1px #000;
			vertical-align: top;
			padding: 3px 6px;
			white-space: nowrap;
		}

		table tr th:last-child,
		table tr td:last-child {
			border-right: solid 1px #000;
		}

		table tr:last-child td {
			border-bottom: solid 1px #000;
		}
		
		table tr:nth-child(odd) td {
			background-color: #ddd;
		}
		
		em.missing {
			color: red;
		}
		
	</style>
	
</head>

<body>

<!--

// ---------------------------------------------------------------------------------------------- //

get[SomeValue]						SomeValue = file name only
	1) if method exists...
	2) if method does not exist
		$oItem->getSomeValue();				// maps to entity property "some_value"

// ---------------------------------------------------------------------------------------------- //

get[SomeValue]Url		
	1) if method exists...
	2) if method does not exist, look for "some_value_url" property
	3) if above does not exist
		$oItem->getSomeValue();				// maps to entity property "some_value"
		pass filename to _getFileUrl( $oItem->getSomeValue() )	helper method

- OR -

fileurlSomeValue

// ---------------------------------------------------------------------------------------------- //

get[SomeValue]Size

- OR -

filesizeSomeValue

// ---------------------------------------------------------------------------------------------- //

getThe[SomeValue]Url

- OR -

theimageurlSomeValue

-->

<?php

$aMapping = array(
	
	'Gloc_Inscape_Typical' => array(
		
		// camelize issue
		'drawings_2d_pdf' => 'Drawings2dPdf',
		'drawings_3d_pdf' => 'Drawings3dPdf',
		'2d_dwg_file' => '2dDwgFile',
		'3d_dwg_file' => '3dDwgFile',
		
		// these are okay
		'bom_pdf' => 'BomPdf',
		'revit_file' => 'RevitFile',
		'worksheets_sp4' => 'WorksheetsSp4',
		'google_sketchup_jpg' => 'GoogleSketchupJpg',
		'google_sketchup_file_skp' => 'GoogleSketchupFileSkp',
		'rendering_jpg' => 'RenderingJpg'


	)
);

?>

<table>
	<tr>
		<th>Class</th>
		<th>&nbsp;</th>
		<th>Camelized</th>
		<th>Param Func</th>
		<th>&nbsp;</th>
		<th>Param</th>
		<th>Camelized Func</th>
	</tr>
	<?php foreach ( $aMapping as $sClass => $aMethods ): ?>
		<?php foreach ( $aMethods as $sParam => $sCamelized ): ?>
			<tr>
				<td><?php echo $sClass; ?></td>
				<td>&nbsp;</td>
				<td><?php echo $sCamelized; ?></td>
				<td><?php echo Geko_Inflector::underscore( $sCamelized ); ?></td>
				<td>&nbsp;</td>
				<td><?php echo $sParam; ?></td>
				<td><?php echo Geko_Inflector::camelize( $sParam ); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>
</table>

<?php

$oTypical = new Gloc_Inscape_Typical( 1 );

echo '<br /><br />';

$oTypical->echoGoogleSketchupJpg(); echo '<br />';
$oTypical->echoGoogleSketchupJpgUrl(); echo '<br />';
$oTypical->echoGoogleSketchupJpgSize(); echo '<br />';
$oTypical->echoTheGoogleSketchupJpgUrl( array( 'w' => 10, 'h' => 10 ) ); echo '<br />';
echo '(get) ' . $oTypical->getTheGoogleSketchupJpgUrl( array( 'w' => 10, 'h' => 10 ) ); echo '<br />';

echo '<br /><br />';

$oTypical->echoDrawings2dPdf(); echo '<br />';
$oTypical->echoDrawings2dPdfUrl(); echo '<br />';
$oTypical->echoDrawings2dPdfSize(); echo '<br />';

echo '<br /><br /><br /><br /><br />';

echo $oTypical->fileurlGoogleSketchupJpg(); echo '<br />';
echo $oTypical->filepathGoogleSketchupJpg(); echo '<br />';
echo $oTypical->filesizeGoogleSketchupJpg(); echo '<br />';
echo $oTypical->theimageurlGoogleSketchupJpg( array( 'w' => 10, 'h' => 10 ) ); echo '<br />';

echo '<br /><br />';

echo $oTypical->fileurlDrawings2dPdf(); echo '<br />';
echo $oTypical->filepathDrawings2dPdf(); echo '<br />';
echo $oTypical->filesizeDrawings2dPdf(); echo '<br />';

?>

</body>

</html>
