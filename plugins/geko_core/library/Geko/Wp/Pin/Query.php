<?php

// listing
class Geko_Wp_Pin_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		if ( $aParams[ 'kwsearch' ] ) {
			$aParams[ 'kwsearch_fields' ] = array( 'p.pin' );
		}
		
		return $aParams;
	}

	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		
		// pin id
		if ( isset( $aParams[ 'pin_id' ] ) ) {
			$oQuery->where( 'p.pin_id = ?', $aParams[ 'pin_id' ] );
		}
		
		// pin itself (slug)
		if ( isset( $aParams[ 'pin' ] ) ) {
			$oQuery->where( 'p.pin = ?', $aParams[ 'pin' ] );
		}
		
		
		// flag filters
		
		if ( $sRedeemed = $aParams[ 'redeemed' ] ) {
			
			if ( 'yes' == $sRedeemed ) {
				$oQuery->where( 'p.redeemed = 1' );
			} elseif ( 'no' == $sRedeemed ) {
				$oQuery->where( '( p.redeemed = 0 ) || ( p.redeemed IS NULL )' );			
			}
			
		}
		
		if ( $sTesting = $aParams[ 'testing' ] ) {
			
			if ( 'yes' == $sTesting ) {
				$oQuery->where( 'p.testing = 1' );
			} elseif ( 'no' == $sTesting ) {
				$oQuery->where( '( p.testing = 0 ) || ( p.testing IS NULL )' );			
			}
			
		}
		
		if ( $sNpn = $aParams[ 'npn' ] ) {
			
			if ( 'yes' == $sNpn ) {
				$oQuery->where( 'p.npn = 1' );
			} elseif ( 'no' == $sNpn ) {
				$oQuery->where( '( p.npn = 0 ) || ( p.npn IS NULL )' );			
			}
			
		}
		
		
		return $oQuery;
	}
	
	
}