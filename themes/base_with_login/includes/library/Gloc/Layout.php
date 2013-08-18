<?php

//
class Gloc_Layout extends Geko_Wp_Layout
{
	
	protected $_sRenderer = 'Gloc_Layout_Renderer';
	
	
	//
	public function getScriptUrls( $aOther = NULL ) {
		
		$oUrl = Geko_Uri::getGlobal();
		
		$aRet = array(
			'curpage' => strval( $oUrl ),
			'process' => Geko_Uri::getUrl( 'geko_process' ),
			'url' => get_bloginfo( 'url' )
		);
		
		if ( is_array( $aOther ) ) {
			$aRet = array_merge( $aRet, $aOther );
		}
		
		return $aRet;
	}
	
	
}


