<?php

//
class Geko_Wp_Cart66_View extends Geko_Singleton_Abstract
{
	
	protected $_sThisFile = '';
	
	
	//
	public function logMsg( $sLine, $sTitle, $sDetails ) {
		
		$sMsg = sprintf( '[%s - line %s] %s', basename( $this->_sThisFile ), $sLine, $sTitle );
		
		if ( $sDetails ) {
			$sMsg = sprintf( '%s: %s', $sMsg, $sDetails );
		}
		
		Cart66Common::log( $sMsg );
		
		return $this;
	}
	
	
	//
	public function render( $data = NULL, $notices = TRUE, $minify = FALSE ) {
	
	}
	

}


