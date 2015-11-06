<?php
/*
 * "geko_core/library/Geko/Wp/MimeTypeIcon.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_MimeTypeIcon extends Geko_Wp_Initialize
{
	
	//
	public function init() {
		add_filter( 'wp_mime_type_icon', array( $this, 'getIconUrl' ), 10, 3 );
	}
	
	//
	public function getIconUrl( $sIconSrc, $sMime, $iPostId ) {
		
		$sCustomIconSrc = sprintf( '/images/mime-%s.', str_replace( '/', '-', $sMime ) );
		
		if ( $sCustomIconSrc = Geko_File::isFileCoalesce(
			TEMPLATEPATH,
			sprintf( '%sgif', $sCustomIconSrc ),
			sprintf( '%spng', $sCustomIconSrc ),
			sprintf( '%sjpg', $sCustomIconSrc ),
			sprintf( '%sjpeg', $sCustomIconSrc )
		) ) {
			return sprintf( '%s%s', get_bloginfo( 'template_directory' ), $sCustomIconSrc );
		}
		
		return $sIconSrc;
	}
	
}


