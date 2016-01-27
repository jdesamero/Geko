<?php

//
class Geko_Wp_Resolver_Path_Mobile extends Geko_Wp_Resolver_Path
{
	
	protected $_aPrefixes = array( 'Gloc_Layout_Mobile_', 'Geko_Layout_Mobile_', 'Gloc_Layout_', 'Geko_Layout_' );
	protected $_bMatchTabletToo = FALSE;
	
	//
	public function setMatchTabletToo( $bMatchTabletToo ) {
		
		$this->_bMatchTabletToo = $bMatchTabletToo;
		
		return $this;
	}
	
	
	//
	public function isMatch() {
		
		// perform device detection
		$oBrowser = new Geko_Browser();
		
		$bMatchDevice = NULL;
		
		if ( $this->_bMatchTabletToo ) {
			$bMatchDevice = ( $oBrowser->isDevice( Geko_Browser::DEVICE_MOBILE ) || $oBrowser->isDevice( Geko_Browser::DEVICE_TABLET ) );
		} else {
			$bMatchDevice = $oBrowser->isDevice( Geko_Browser::DEVICE_MOBILE );
		}
		
		return ( $bMatchDevice || $_GET[ 'geko_wp_resolver_mobile_test' ] ) ? TRUE : FALSE ;
	}
	
	//
	public function resolvePath( $sClassFile ) {
		return sprintf( '%s/mobile/%s', dirname( $sClassFile ), basename( $sClassFile ) );
	}
	
}


