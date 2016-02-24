<?php
/*
 * "geko_core/library/Geko/App.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App extends Geko
{
	
	
	//
	public function getScriptUrls( $aOther = NULL ) {
		
		$aOther = parent::getScriptUrls( $aOther );
		
		
		$oUrl = new Geko_Uri();
		
		$sCurPage = strval( $oUrl );
		
		$oUrl->unsetVars();
		$sCurPath = strval( $oUrl ); 

		$oUrl->setVar( 'ajax_content', 1 );
		$sAjaxContent = strval( $oUrl ); 
		
		$aRet = array(
			'url' => GEKO_STANDALONE_URL,
			'curpage' => $sCurPage,
			'curpath' => $sCurPath,
			'ajax_content' => $sAjaxContent,
			'srv' => Geko_Uri::getUrl( 'geko_app_srv' )
		);
		
		if ( is_array( $aOther ) ) {
			$aRet = array_merge( $aRet, $aOther );
		}
		
		return $aRet;
	}
	
	
	
}



