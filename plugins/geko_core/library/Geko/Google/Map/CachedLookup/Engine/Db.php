<?php

//
class Geko_Google_Map_CachedLookup_Engine_Db extends Geko_CachedLookup_Engine_Db
{
	
	protected $_oDb;
	
	
	//
	public function init() {
		
		if ( $oDb = $this->_oDb ) {
			
			//// address lookup table
			
			$oSqlTable = new Geko_Sql_Table();
			$oSqlTable
				->create( '##pfx##geko_gmap_lookup', 'l' )
				->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
				->fieldLongText( 'lookup' )
				->fieldChar( 'slug', array( 'size' => 32, 'unq' ) )
			;
			
			$oDb->tableCreateIfNotExists( $oSqlTable );
			
			
			//// rel table
			
			$oSqlTable2 = new Geko_Sql_Table();
			$oSqlTable2
				->create( '##pfx##geko_gmap_lookup_coords_rel', 'r' )
				->fieldBigInt( 'coord_id' )
				->fieldBigInt( 'lookup_id' )
				->fieldSmallInt( 'idx' )
			;
			
			$oDb->tableCreateIfNotExists( $oSqlTable2 );
			
			
			//// location table
			
			$oSqlTable3 = new Geko_Sql_Table();
			$oSqlTable3
				->create( '##pfx##geko_gmap_coords', 'c' )
				->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
				->fieldChar( 'slug', array( 'size' => 32, 'unq' ) )
				->fieldLongText( 'address' )
				->fieldLongText( 'type' )
				->fieldFloat( 'lat', array( 'size' => '10,7', 'sgnd' ) )
				->fieldFloat( 'lng', array( 'size' => '10,7', 'sgnd' ) )
				->fieldFloat( 'ne_lat', array( 'size' => '10,7', 'sgnd' ) )
				->fieldFloat( 'ne_lng', array( 'size' => '10,7', 'sgnd' ) )
				->fieldFloat( 'sw_lat', array( 'size' => '10,7', 'sgnd' ) )
				->fieldFloat( 'sw_lng', array( 'size' => '10,7', 'sgnd' ) )
				->fieldInt( 'country_id', array( 'unsgnd' ) )
			;
			
			$oDb->tableCreateIfNotExists( $oSqlTable3 );
		
		}
		
	}
	
	
	//
	public function getCached( $sHash, $aArgs ) {
		
		if ( $oDb = $this->_oDb ) {
			
			// check if there is something cached
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'c.id' )
				->field( 'c.slug' )
				->field( 'c.address' )
				->field( 'c.type' )
				->field( 'c.lat' )
				->field( 'c.lng' )
				->field( 'c.ne_lat' )
				->field( 'c.ne_lng' )
				->field( 'c.sw_lat' )
				->field( 'c.sw_lng' )
				->field( 'c.country_id' )
				
				->from( '##pfx##geko_gmap_coords', 'c' )
				
				->joinLeft( '##pfx##geko_gmap_lookup_coords_rel', 'r' )
					->on( 'r.coord_id = c.id' )

				->joinLeft( '##pfx##geko_gmap_lookup', 'l' )
					->on( 'l.id = r.lookup_id' )
				
				->where( 'l.slug = ?', $sHash )
				
			;
			
			
			$aRes = $oDb->fetchAll( strval( $oQuery ) );
			
			
			if ( is_array( $aRes ) && ( 0 == count( $aRes ) ) ) {
				$aRes = NULL;
			}
			
			return $aRes;
		}
		
		return NULL;
	}
	
	
	
	//
	public function getCoordId( $aCoord ) {
		
		$oDb = $this->_oDb;
		
		$sAddress = $aCoord[ 'address' ];
		
		if ( !$sHash = $aCoord[ 'hash' ] ) {
			$sHash = md5( $sAddress );
		}
		
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'c.id' )
			->from( '##pfx##geko_gmap_coords', 'c' )
			->where( 'slug = ?', $sHash )
		;
		
		$iCoordId = $oDb->fetchOne( strval( $oQuery ) );
		
		
		if ( FALSE === $iCoordId ) {
			
			if ( !$iCountryId = $aCoord[ 'country_id' ] ) {
				$iCountryId = $this->getCountryId( $aCoord[ 'country' ] );
			}
			
			$aValues = array(
				'address' => $sAddress,
				'slug' => $sHash,
				'type' => $aCoord[ 'type' ],
				'lat' => $aCoord[ 'lat' ],
				'lng' => $aCoord[ 'lng' ],
				'ne_lat' => $aCoord[ 'ne_lat' ],
				'ne_lng' => $aCoord[ 'ne_lng' ],
				'sw_lat' => $aCoord[ 'sw_lat' ],
				'sw_lng' => $aCoord[ 'sw_lng' ],
				'country_id' => $iCountryId
			);
			
			
			// insert
			$oDb->insert( '##pfx##geko_gmap_coords', $aValues );
			
			$iCoordId = $oDb->lastInsertId();
			
		}
		
		
		return $iCoordId;
	}

	
	
	//
	public function assignCoords( $iLookupId, $aCoords ) {
		
		$oDb = $this->_oDb;
		
		$i = 0;
		
		foreach ( $aCoords as $aCoord ) {
			
			$i++;
			
			$iCoordId = $this->getCoordId( $aCoord );
			
			$oDb->insert( '##pfx##geko_gmap_lookup_coords_rel', array(
				'lookup_id' => $iLookupId,
				'coord_id' => $iCoordId,
				'idx' => $i
			) );
			
		}
		
		return $this;
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
	public function saveToCache( $sHash, $aArgs, $aActRes ) {
		
		if ( $oDb = $this->_oDb ) {
			
			$sLocNorm = $aArgs[ 0 ];
			$aCoords = $aActRes[ 'details' ];
			
			if ( ( 'ok' == $aActRes[ 'status' ] ) && $sLocNorm && $aCoords ) {
				
				//// insert
				
				$oDb->insert( '##pfx##geko_gmap_lookup', array(
					'lookup' => $sLocNorm,
					'slug' => $sHash
				) );
				
				$iLookupId = $oDb->lastInsertId();
				
				
				//// assign coords
				
				$this->assignCoords( $iLookupId, $aCoords );
				
				
			}
			
		}
		
		return $this;
	}
	
	
}



