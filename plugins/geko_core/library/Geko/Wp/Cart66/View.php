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
	
	
	
	//
	public function getLink( $item, $path, $var ) {
		
		$page = get_page_by_path( $path );
		$link = get_permalink( $page );
		
		$delim = ( strstr( $link, '?' ) ) ? '&' : '?' ;
		
		return sprintf( '%s%s%s=%s', $link, $delim, $var, $item->$var );
	}
	
	//
	public function echoLink( $item, $path, $var ) {
		echo $this->getLink( $item, $path, $var );
	}
	
	
	
	//
	public function _t( $sMsg ) {
		return __( $sMsg, 'cart66' );
	}
	
	//
	public function _e( $sMsg ) {
		return _e( $sMsg, 'cart66' );
	}
	
	
	
	//
	public function getCurr( $val, $html ) {
		return Cart66Common::currency( $val, $html );
	}
	
	//
	public function echoCurr( $val, $html ) {
		echo Cart66Common::currency( $val, $html );
	}
	
	
	
	//
	public function getVal( $key ) {
		return Cart66Setting::getValue( $key );
	}
	
	//
	public function echoVal( $key ) {
		echo Cart66Setting::getValue( $key );
	}
	
	
	
}


