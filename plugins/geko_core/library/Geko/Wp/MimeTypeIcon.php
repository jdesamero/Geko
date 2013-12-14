<?php

//
class Geko_Wp_MimeTypeIcon extends Geko_Wp_Initialize
{
	
	//
	public function init()
	{
		add_filter( 'wp_mime_type_icon', array( $this, 'getIconUrl' ), 10, 3 );
	}
	
	//
	public function getIconUrl( $sIconSrc, $sMime, $iPostId )
	{
		$sCustomIconSrc = '/images/mime-' . str_replace( '/', '-', $sMime ) . '.';
		
		if ( $sCustomIconSrc = Geko_File::isFileCoalesce(
			TEMPLATEPATH,
			$sCustomIconSrc . 'gif',
			$sCustomIconSrc . 'png',
			$sCustomIconSrc . 'jpg',
			$sCustomIconSrc . 'jpeg'
		) ) {
			return get_bloginfo('template_directory') . $sCustomIconSrc;
		}
		
		return $sIconSrc;
	}
	
}

