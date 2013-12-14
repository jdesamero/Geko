<?php

//
class Geko_Wp_Resolver_Path_Mobile extends Geko_Wp_Resolver_Path
{
	
	protected $_aPrefixes = array( 'Gloc_Layout_Mobile_', 'Geko_Layout_Mobile_', 'Gloc_Layout_', 'Geko_Layout_' );
	
	//
	public function isMatch() {
		
		// perform device detection
		$oBrowser = new Geko_Browser();
		
		return (
			$oBrowser->isDevice( Geko_Browser::DEVICE_TABLET ) || 
			$oBrowser->isDevice( Geko_Browser::DEVICE_MOBILE ) || 
			$_GET[ 'geko_wp_resolver_mobile_test' ]
		) ? TRUE : FALSE ;
	}
	
	//
	public function resolvePath( $sClassFile ) {
		return sprintf( '%s/mobile/%s', dirname( $sClassFile ), basename( $sClassFile ) );
	}
	
}


