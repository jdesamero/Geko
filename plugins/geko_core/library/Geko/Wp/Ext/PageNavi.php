<?php

// wrapper for wp_pagenavi()
class Geko_Wp_Ext_PageNavi
{

	//
	public static function get( $aParams = array() ) {
		
		if ( function_exists( 'wp_pagenavi' ) ) {
			
			$aPgnvParams = array( 'echo' => FALSE );
			
			$aParams = is_array( $aParams ) ? $aParams : NULL ;
			
			if ( $aParams ) {
				
				// accepted keys
				$aParamKeys = array( 'before', 'after', 'options', 'query', 'type' );
				foreach ( $aParamKeys as $sKey ) {
					if ( $mValue = $aParams[ $sKey ] ) {
						
						if ( ( 'query' == $sKey ) && ( $mValue instanceof Geko_Wp_Post_Query ) ) {
							$mValue = $mValue->getWpQuery();
						}
						
						$aPgnvParams[ $sKey ] = $mValue;
					}
				}
			}
			
			$sRes = wp_pagenavi( $aPgnvParams );
			
			if ( $aParams ) {
				
				if (
					( $sVarName = $aParams[ 'varname' ] ) || 
					( $aLocalize = $aParams[ 'localize' ] )
				) {
					
					$oDoc = phpQuery::newDocument( $sRes );
					
					//// multi-pagination support
					
					if ( $sVarName ) {
						
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
					}
					
					
					//// localization support
					
					if ( $aLocalize && is_array( $aLocalize ) ) {
						
						// '&laquo; First'
						if ( $sFirstLabel = $aLocalize[ 'first' ] ) {
							$oDoc[ 'a.first' ]->html( $sFirstLabel );
						}
						
						// 'Last &raquo;'
						if ( $sLastLabel = $aLocalize[ 'last' ] ) {
							$oDoc[ 'a.last' ]->html( $sLastLabel );
						}
						
						// 'Pages %s of %s'
						if ( $sPagesLabel = $aLocalize[ 'pages' ] ) {
							
							$sPagesLabelCur = $oDoc[ 'span.pages' ]->html();
							
							preg_match( '/([0-9]+)[^0-9]+([0-9]+)/', $sPagesLabelCur, $aRegs );
							$sPagesLabel = sprintf( $sPagesLabel, $aRegs[ 1 ], $aRegs[ 2 ] );
							
							$oDoc[ 'span.pages' ]->html( $sPagesLabel );
						
						}
					}
					
					
					$sRes = strval( $oDoc );
				}
			}
			
			return $sRes;
		}
		
		return NULL;
	}
	
	
	//
	public static function show( $aParams = array() ) {
		echo self::get( $aParams );
	}

}


