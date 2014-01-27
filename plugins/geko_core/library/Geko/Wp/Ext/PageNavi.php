<?php

// wrapper for wp_pagenavi()
class Geko_Wp_Ext_PageNavi
{

	//
	public function get( $aParams = array() ) {
		
		if ( function_exists( 'wp_pagenavi' ) ) {
			
			$aPgnvParams = array( 'echo' => FALSE );
			
			$aParams = is_array( $aParams ) ? $aParams : NULL ;
			
			if ( $aParams ) {
				
				// accepted keys
				$aParamKeys = array( 'before', 'after', 'options', 'query', 'type' );
				foreach ( $aParamKeys as $sKey ) {
					if ( $mValue = $aParams[ $sKey ] ) {
						$aPgnvParams[ $sKey ] = $mValue;
					}
				}
			}
			
			$sRes = wp_pagenavi( $aPgnvParams );
			
			if ( $aParams ) {
				
				if ( $sVarName = $aParams[ 'varname' ] ) {
					
					$oDoc = phpQuery::newDocument( $sRes );
					
					foreach ( $oDoc[ 'a' ] as $oLink ) {
						
						$oLinkPq = pq( $oLink );
						$sHref = $oLinkPq->attr( 'href' );
						$aRegs = array();
						
						$iPageNum = NULL;
						if ( preg_match( '/page\/([0-9]+)/', $sHref, $aRegs ) ) {
							$iPageNum = $aRegs[ 1 ];
							$sHref = str_replace( sprintf( '/page/%d', $iPageNum ), '', $sHref );
						}
						
						$oUrl = new Geko_Uri( $sHref );
						
						if ( $iPageNum ) {
							$oUrl->setVar( $sVarName, $iPageNum );
						} else {
							$oUrl->unsetVar( $sVarName );
						}
						
						$oLinkPq->attr( 'href', strval( $oUrl ) );
					}
					
					$sRes = strval( $oDoc );
				}
			}
			
			return $sRes;
		}
		
		return NULL;
	}
	
	//
	public function show( $aParams = array() ) {
		echo self::get( $aParams );
	}

}
