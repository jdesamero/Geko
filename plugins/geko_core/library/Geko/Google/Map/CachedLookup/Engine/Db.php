<?php

//
class Geko_Google_Map_CachedLookup_Engine_Db extends Geko_CachedLookup_Engine_Db
{
	
	protected $_oDb;
	
	protected $_sTableSignature = 'geko_google_map';
	
	protected $_aAddrCompTypes = NULL;
	
	
	//
	public function createTable() {
		
		
		$oDb = $this->_oDb;
		
		
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
			->fieldLongText( 'place_id' )
			->fieldLongText( 'address' )
			->fieldLongText( 'type' )
			->fieldFloat( 'lat', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'lng', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'ne_lat', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'ne_lng', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'sw_lat', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'sw_lng', array( 'size' => '10,7', 'sgnd' ) )
			->fieldInt( 'country_id', array( 'unsgnd' ) )
			->fieldInt( 'addcomp_id', array( 'unsgnd' ) )
		;
		
		$oDb->tableCreateIfNotExists( $oSqlTable3 );
		
		
		//// address component type

		$oSqlTable4 = new Geko_Sql_Table();
		$oSqlTable4
			->create( '##pfx##geko_gmap_type', 't' )
			->fieldInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'label', array( 'size' => 256, 'unq' ) )
		;
		
		$oDb->tableCreateIfNotExists( $oSqlTable4 );
		
		
		//// address components

		$oSqlTable5 = new Geko_Sql_Table();
		$oSqlTable5
			->create( '##pfx##geko_gmap_address_component', 'a' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'long_name' )
			->fieldVarChar( 'short_name', array( 'size' => 256 ) )
			->fieldBigInt( 'parent_id', array( 'unsgnd' ) )
			->fieldBigInt( 'postal_code_id', array( 'unsgnd' ) )
		;
		
		$oDb->tableCreateIfNotExists( $oSqlTable5 );
		
		
		//// address component type relation
		
		$oSqlTable6 = new Geko_Sql_Table();
		$oSqlTable6
			->create( '##pfx##geko_gmap_addcomp_type_rel', 'ar' )
			->fieldBigInt( 'addcomp_id', array( 'unsgnd' ) )
			->fieldInt( 'type_id', array( 'unsgnd' ) )
			->indexUnq( 'addcomp_type_index', array( 'addcomp_id', 'type_id' ) )
		;
		
		$oDb->tableCreateIfNotExists( $oSqlTable6 );
		
		
		//// postal code
		
		$oSqlTable7 = new Geko_Sql_Table();
		$oSqlTable7
			->create( '##pfx##geko_gmap_postal_code', 'p' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'long_name' )
			->fieldVarChar( 'short_name', array( 'size' => 256 ) )
			->fieldBigInt( 'country_id', array( 'unsgnd' ) )
		;
		
		$oDb->tableCreateIfNotExists( $oSqlTable7 );
		
		
		
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
			
			// hacky, appy formatting
			if ( is_array( $aRes ) ) {
				
				foreach ( $aRes as $i => $aRow ) {
					
					$aRow[ 'id' ] = intval( $aRow[ 'id' ] );
					
					$aRow[ 'lat' ] = floatval( $aRow[ 'lat' ] );
					$aRow[ 'lng' ] = floatval( $aRow[ 'lng' ] );
					
					$aRow[ 'ne_lat' ] = floatval( $aRow[ 'ne_lat' ] );
					$aRow[ 'ne_lng' ] = floatval( $aRow[ 'ne_lng' ] );
					$aRow[ 'sw_lat' ] = floatval( $aRow[ 'sw_lat' ] );
					$aRow[ 'sw_lng' ] = floatval( $aRow[ 'sw_lng' ] );
					
					$aRow[ 'country_id' ] = intval( $aRow[ 'country_id' ] );
					
					$aRes[ $i ] = $aRow;
				}
				
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
				$oGeoCoun = Geko_Geography_Country::getInstance();
				$iCountryId = $oGeoCoun->getCountryId( $aCoord[ 'country' ] );
			}
			
			
			// handle address components
			$iLastAddrCompId = $this->saveAddressComponents( $aCoord[ 'address_components' ] );
			
			
			$aValues = array(
				'address' => $sAddress,
				'place_id' => $aCoord[ 'place_id' ],
				'slug' => $sHash,
				'type' => $aCoord[ 'type' ],
				'lat' => $aCoord[ 'lat' ],
				'lng' => $aCoord[ 'lng' ],
				'ne_lat' => $aCoord[ 'ne_lat' ],
				'ne_lng' => $aCoord[ 'ne_lng' ],
				'sw_lat' => $aCoord[ 'sw_lat' ],
				'sw_lng' => $aCoord[ 'sw_lng' ],
				'country_id' => $iCountryId,				// !!! this country id is from XML, reconcile this later
				'addcomp_id' => $iLastAddrCompId
			);
			
			
			// insert
			$oDb->insert( '##pfx##geko_gmap_coords', $aValues );
			
			$iCoordId = $oDb->lastInsertId();
			
			
		}
		
		
		return $iCoordId;
	}
	
	
	// save address components
	public function saveAddressComponents( $aAddrComps ) {
		
		$oDb = $this->_oDb;
		
		$iLastAddrCompId = NULL;
		$iParentId = NULL;
		$iCountryId = NULL;
		
		$aPostalCode = NULL;
		$iPostalCodeId = NULL;
		
		
		// go through components, starting from last
		while ( count( $aAddrComps ) > 0 ) {
			
			$iParentId = $iLastAddrCompId;		// remember for next insertion
			
			
			// check if this already exists
			$aLast = array_pop( $aAddrComps );
			
			// build query
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'a.id' )
				->from( '##pfx##geko_gmap_address_component', 'a' )
				->where( 'a.long_name = ?', $aLast[ 'long_name' ] )
				->where( 'a.short_name = ?', $aLast[ 'short_name' ] )
			;
			
			// join each type
			
			$aTypes = $aLast[ 'types' ];
			$aTypeIds = array();			// use potentially for insertion
			
			$bIsCountry = FALSE;
			$bIsPostalCode = FALSE;
			
			foreach ( $aTypes as $i => $sType ) {
				
				if ( 'country' == $sType ) $bIsCountry = TRUE;
				if ( 'postal_code' == $sType ) $bIsPostalCode = TRUE;
				
				$iTypeId = $this->getAddrCompTypeId( $sType );
				$aTypeIds[] = $iTypeId;
				
				$sK1 = sprintf( 'r%d', $i );		// relation key
				$sK2 = sprintf( 't%d', $i );		// type key
				
				$oQuery
					
					->joinLeft( '##pfx##geko_gmap_addcomp_type_rel', $sK1 )
						->on( sprintf( '%s.addcomp_id = a.id', $sK1 ) )
					
					->joinLeft( '##pfx##geko_gmap_type', $sK2 )
						->on( sprintf( '%s.id = %s.type_id', $sK2, $sK1 ) )
						
					->where( sprintf( '%s.label = ?', $sK2 ), $sType )
				;
				
			}
			
			// finally check for parent
			
			if ( !$bIsPostalCode ) {
				
				if ( $bIsCountry ) {
					$oQuery->where( '( a.parent_id IS NULL ) OR ( a.parent_id = 0 )' );
				} else {
					$oQuery->where( '( a.parent_id = ? )', $iLastAddrCompId );
				}
				
				
				// perform the query
				$iLastAddrCompId = $oDb->fetchOne( strval( $oQuery ) );
				
				if ( !$iLastAddrCompId ) {
					
					// then perform insert on main table
					
					$aAddrCompValues = array(
						'long_name' => $aLast[ 'long_name' ],
						'short_name' => $aLast[ 'short_name' ]
					);
					
					if ( $iParentId ) {
						$aAddrCompValues[ 'parent_id' ] = $iParentId;
					}
					
					$oDb->insert( '##pfx##geko_gmap_address_component', $aAddrCompValues );
					
					
					$iLastAddrCompId = $oDb->lastInsertId();
					
					
					// then perform insert(s) on rel table
					foreach ( $aTypeIds as $iTypeId ) {
						
						$oDb->insert( '##pfx##geko_gmap_addcomp_type_rel', array(
							'addcomp_id' => $iLastAddrCompId,
							'type_id' => $iTypeId
						) );
						
					}
					
				}
				
				
				// remember the country id
				if ( $bIsCountry ) $iCountryId = $iLastAddrCompId;
				
				
			} else {
				
				// remember postal code and process later
				$aPostalCode = $aLast;
				
			}
			
		}
		
		// save postal code info, if there was any
		if ( is_array( $aPostalCode ) ) {
			
			// build query
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'p.id' )
				->from( '##pfx##geko_gmap_postal_code', 'p' )
				->where( 'p.long_name = ?', $aPostalCode[ 'long_name' ] )
				->where( 'p.short_name = ?', $aPostalCode[ 'short_name' ] )
				->where( 'p.country_id = ?', $iCountryId )
			;
			
			$iPostalCodeId = $oDb->fetchOne( strval( $oQuery ) );
			
			if ( !$iPostalCodeId ) {
				
				// then insert postal code
				
				$oDb->insert( '##pfx##geko_gmap_postal_code', array(
					'long_name' => $aPostalCode[ 'long_name' ],
					'short_name' => $aPostalCode[ 'short_name' ],
					'country_id' => $iCountryId
				) );
				
				$iPostalCodeId = $oDb->lastInsertId();
			}
			
		}
		
		if ( $iPostalCodeId && $iLastAddrCompId ) {
			
			// update the last component
			$oDb->update(
				'##pfx##geko_gmap_address_component',
				array( 'postal_code_id' => $iPostalCodeId ),
				array( 'id = ?' => $iLastAddrCompId )
			);
			
		}
		
		// return the last component id to it can be assigned to coordinates table
		return $iLastAddrCompId;
	}
	
	
	//
	public function getAddrCompTypeId( $sType ) {
		
		$oDb = $this->_oDb;
		
		// init once, create a hash
		if ( NULL === $this->_aAddrCompTypes ) {
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 't.label' )
				->field( 't.id' )
				->from( '##pfx##geko_gmap_type', 't' )
			;
			
			$this->_aAddrCompTypes = $oDb->fetchPairs( strval( $oQuery ) );
			
		}
		
		
		
		// create new type
		if ( !( $iTypeId = $this->_aAddrCompTypes[ $sType ] ) ) {
			
			$oDb->insert( '##pfx##geko_gmap_type', array(
				'label' => $sType
			) );
			
			$iTypeId = $oDb->lastInsertId();
			
			$this->_aAddrCompTypes[ $sType ] = $iTypeId;
		}
		
		return $iTypeId;
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



