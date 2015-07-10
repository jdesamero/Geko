<?php

//
class Geko_Currency_Rate_CachedLookup_Engine_Db extends Geko_CachedLookup_Engine_Db
{
	
	protected $_oDb;
	
	protected $_sTableSignature = 'geko_currency_rate';
	
	
	
	//
	public function createTable() {
		
		
		$oDb = $this->_oDb;
		
		
		//// address lookup table
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_currency_rate', 'r' )
			->fieldInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'rate_key', array( 'size' => 256 ) )
			->fieldLongText( 'rate_value' )
		;
		
		$oDb->tableCreateIfNotExists( $oSqlTable );
		
		
	}

	
	
	// ignore $iHash and $aArgs, return the full rate table
	public function getCached( $iHash, $aArgs ) {
		
		if ( $oDb = $this->_oDb ) {
			
			// check if there is something cached
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'r.rate_key' )
				->field( 'r.rate_value' )
				
				->from( '##pfx##geko_currency_rate', 'r' )
				
			;
			
			
			$aRes = $oDb->fetchPairs( strval( $oQuery ) );
			
			
			if ( is_array( $aRes ) && ( 0 == count( $aRes ) ) ) {
				$aRes = NULL;
			}
			
			if ( is_array( $aRes ) ) {
				
				$sBase = '';
				$iTimestamp = 0;			// retreival timestamp
				
				foreach ( $aRes as $sKey => $sValue ) {
					
					if ( $sKey == '__TIMESTAMP__' ) {
						$iTimestamp = intval( $sValue );
					} elseif ( $sKey == '__BASE__' ) {
						$sBase = $sValue;					
					} else {
						$aRes[ $sKey ] = floatval( $sValue );
					}
				}
				
				$aRes = array(
					'base' => $sBase,
					'timestamp' => $iTimestamp,
					'rates' => $aRes
				);
				
			}
			
			return $aRes;
		}
		
		return NULL;
	}
	
	
	
	//
	public function saveToCache( $iHash, $aArgs, $aActRes ) {
		
		if ( $oDb = $this->_oDb ) {

			// check if there is something cached
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'r.rate_key' )
				->field( 'r.id' )
				
				->from( '##pfx##geko_currency_rate', 'r' )
				
			;
			
			
			$aRes = $oDb->fetchPairs( strval( $oQuery ) );
			if ( !is_array( $aRes ) ) $aRes = array();
			
			
			$aRates = $aActRes[ 'rates' ];
			
			// hacky, save meta values as well
			$aRates[ '__TIMESTAMP__' ] = time();				// don't use $aActRes[ 'timestamp' ]
			$aRates[ '__BASE__' ] = $aActRes[ 'base' ];
			
			
			// insert or update rate
			foreach ( $aRates as $sKey => $mValue ) {
				
				if ( $iRateId = $aRes[ $sKey ] ) {
					
					// update
					$oDb->update(
						'##pfx##geko_currency_rate',
						array( 'rate_value' => $mValue ),
						array( 'id = ?' => $iRateId )
					);
					
				} else {
					
					// insert
					$oDb->insert( '##pfx##geko_currency_rate', array(
						'rate_key' => $sKey,
						'rate_value' => $mValue
					) );
				
				}
				
			}
			
			
		}
		
		
		return $this;
	}

	
	
	
}

