<?php

abstract class Geko_Fb_Entity_Query extends Geko_Entity_Query
{
	protected static $oFb;
	
	//
	public static function setApiObj( $oFb )
	{
		self::$oFb = $oFb;
	}
	
	// ------------------------------------------------------------------------------------------ //
	
	
	//// initial helpers
	
	//
	public function getDefaultParams()
	{
		$aParams = array();
		
		if ( $iUid = self::$oFb->user ) {
			$aParams['uid'] = $iUid;
		}
		
		return $aParams;
	}
	
	
	
	//// helper methods
	
	//
	public function filterEntityArray( $aEntities, $aParams = array() )
	{
		if ( is_array( $aEntities ) ) {
			
			if ( $aExclude = $aParams['exclude'] ) {
				$aEntities = $this->filterExclude( $aEntities, $aExclude );
			}

			if ( $aInclude = $aParams['include'] ) {
				$aEntities = $this->filterInclude( $aEntities, $aInclude );
			}
						
		}
		
		return $aEntities;
	}
	
	//// exclude
	
	//
	public function filterExclude( $aEntities, $aExclude )
	{		
		if ( Geko_Array::isAssoc( $aExclude ) ) $aExclude = array( $aExclude );
		
		$aFmt = array();
					
		foreach ( $aEntities as $aEntity ) {
			if ( $this->notExcluded( $aEntity, $aExclude ) ) {
				$aFmt[] = $aEntity;
			}
		}
		
		return $aFmt;
	}
	
	//
	public function notExcluded( $aEntity, $aParams )
	{
		foreach ( $aParams as $aRule ) {
			
			$bNotExcluded = FALSE;
			foreach ( $aRule as $sKey => $mRuleValue ) {
				$mValue = $aEntity[ $sKey ];
				if ( is_array( $mRuleValue ) ) {
					if ( !in_array( $mValue, $mRuleValue ) ) $bNotExcluded = TRUE;				
				} else {
					if ( $mValue != $mRuleValue ) $bNotExcluded = TRUE;
				}
				if ( $bNotExcluded ) break;
			}
			if ( !$bNotExcluded ) return FALSE;
			
		}
		
		return TRUE;
	}
	
	//// include
	
	//
	public function filterInclude( $aEntities, $aInclude )
	{		
		if ( Geko_Array::isAssoc( $aInclude ) ) $aInclude = array( $aInclude );
		
		$aFmt = array();
					
		foreach ( $aEntities as $aEntity ) {
			if ( $this->included( $aEntity, $aInclude ) ) {
				$aFmt[] = $aEntity;
			}
		}
		
		return $aFmt;
	}

	//
	public function included( $aEntity, $aParams )
	{
		foreach ( $aParams as $aRule ) {
			
			$bIncluded = TRUE;
			foreach ( $aRule as $sKey => $mRuleValue ) {
				$mValue = $aEntity[ $sKey ];
				if ( is_array( $mRuleValue ) ) {
					if ( !in_array( $mValue, $mRuleValue ) ) $bIncluded = FALSE;				
				} else {
					if ( $mValue != $mRuleValue ) $bIncluded = FALSE;				
				}
				if ( $bIncluded ) return TRUE;
			}
			
		}
		
		return FALSE;
	}
	
	
	
	//
	public function implodeQueryParams( $mParams )
	{
		if ( is_array( $mParams ) ) {
			return implode( ',', $mParams );
		} else {
			return $mParams;
		}
	}
	
}


