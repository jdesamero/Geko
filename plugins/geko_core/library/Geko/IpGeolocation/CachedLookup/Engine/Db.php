<?php

//
class Geko_IpGeolocation_CachedLookup_Engine_Db extends Geko_CachedLookup_Engine_Db
{
	
	protected $_oDb;
	
	
	//
	public function init() {
		
		if ( $oDb = $this->_oDb ) {

			//// address lookup table
			
			$oSqlTable = new Geko_Sql_Table();
			$oSqlTable
				->create( '##pfx##geko_ip_geolocation', 'i' )
				->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
				->fieldInt( 'ip_address', array( 'unsgnd' ) )
				->fieldInt( 'country_id', array( 'unsgnd' ) )
				->fieldVarChar( 'region', array( 'size' => 256 ) )
				->fieldVarChar( 'city', array( 'size' => 256 ) )
				->fieldVarChar( 'zip', array( 'size' => 16 ) )
				->fieldFloat( 'lat', array( 'size' => '10,7', 'sgnd' ) )
				->fieldFloat( 'lng', array( 'size' => '10,7', 'sgnd' ) )
				->fieldVarChar( 'timezone', array( 'size' => 16 ) )
			;
			
			$oDb->tableCreateIfNotExists( $oSqlTable );
			
		}
		
	}
	
	
	//
	public function getCached( $iHash, $aArgs ) {
		
		if ( $oDb = $this->_oDb ) {
			
			// check if there is something cached
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'i.id' )
				->field( 'i.ip_address' )
				->field( 'i.country_id' )
				->field( 'i.region' )
				->field( 'i.city' )
				->field( 'i.zip' )
				->field( 'i.lat' )
				->field( 'i.lng' )
				->field( 'i.timezone' )
				
				->from( '##pfx##geko_ip_geolocation', 'i' )
				
				->where( 'i.ip_address = ?', $iHash )
				
			;
			
			
			$aRes = $oDb->fetchRow( strval( $oQuery ) );
			
			
			if ( is_array( $aRes ) && ( 0 == count( $aRes ) ) ) {
				$aRes = NULL;
			}
			
			return $aRes;
		}
		
		return NULL;
	}
	
	
	
	//
	public function getCountryId( $sCountryAbbr ) {
		
		$oDb = $this->_oDb;
		
		$sCountryAbbr = strtoupper( trim( $sCountryAbbr ) );

		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'c.country_id' )
			->from( '##pfx##geko_location_country', 'c' )
			->where( 'country_abbr = ?', $sCountryAbbr )
		;
		
		$iCountryId = $oDb->fetchOne( strval( $oQuery ) );
		
		return $iCountryId;
	}
	
	
	
	//
	public function saveToCache( $iHash, $aArgs, $aActRes ) {
		
		if ( $oDb = $this->_oDb ) {
			
			if ( 'ok' == strtolower( $aActRes[ 'statusCode' ] ) ) {
				
				//// insert
				
				$oDb->insert( '##pfx##geko_ip_geolocation', array(
					'ip_address' => $iHash,
					'country_id' => $this->getCountryId( $aActRes[ 'countryCode' ] ),
					'region' => $aActRes[ 'regionName' ],
					'city' => $aActRes[ 'cityName' ],
					'zip' => $aActRes[ 'zipCode' ],
					'lat' => $aActRes[ 'latitude' ],
					'lng' => $aActRes[ 'longitude' ],
					'timezone' => $aActRes[ 'timeZone' ]
				) );
				
			}
		}
		
		
		return $this;
	}

}

