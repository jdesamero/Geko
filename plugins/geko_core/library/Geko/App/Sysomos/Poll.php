<?php

// put heartbeat values into a database
class Geko_App_Sysomos_Poll
{
	
	protected $_iHbId = NULL;
	protected $_bMarkPolled = FALSE;
	
	protected $_oDb = NULL;
	
	protected $_sHeartbeatTable = 'heartbeat';
	protected $_sHbTagTable = 'hb_tag';
	protected $_sHbCountryTable = 'hb_country';
	protected $_sHbPollDeltaTable = 'hb_poll_delta';
	protected $_sHbMapFeedTable = 'hb_map_feed';
	
	protected $_sGeoContinentTable = 'geo_continent';
	protected $_sGeoCountryTable = 'geo_country';
	protected $_sGeoLocationTable = 'geo_location';
	protected $_sGeoCoordsTable = 'geo_coords';
	protected $_sGeoLocCoordsRelTable = 'geo_loc_coords_rel';
	
	
	protected $_iHbTagTableId = 1;
	protected $_iHbCountryTableId = 2;
	
	
	protected $_sTruncateDeltaInterval = '-1 day';
	
	
	
	
	
	//
	public function __construct( $mHbId = NULL, $oDb = NULL ) {
		
		// set this first, $oDb needed by getNextToPoll()
		
		if ( !$oDb ) {
			$oDb = Geko_App::get( 'db' );
		}
		
		$this->_oDb = $oDb;
		
		
		if ( is_array( $mHbId ) ) {
			$iHbId = $this->getNextToPoll( $mHbId );
		} else {
			$iHbId = $mHbId;
		}
		
		$this->_iHbId = $iHbId;
		
	}
	
	
	//
	public function getDateTime( $iTs = NULL ) {
		return Geko_Db_Sqlite::getTimestamp( $iTs );
	}
	
	//
	public function resolveHbId( $iHbId = NULL ) {
		if ( $iHbId ) return $iHbId;
		return $this->_iHbId;
	}
	
	
	
	//
	public function getHbId() {
		return $this->_iHbId;
	}
	
	
	
	//
	public function track( $sName, $iHbId = NULL ) {
		
		if ( $sName ) {
			
			$oDb = $this->_oDb;
			
			$iHbId = $this->resolveHbId( $iHbId );
			
			$sName = trim( $sName );
			
			$bCheck = $oDb->fetchOne( sprintf(
				"SELECT hid FROM %s WHERE hid = %d",
				$this->_sHeartbeatTable,
				$iHbId
			) );
			
			
			if ( FALSE !== $bCheck ) {
				
				// update
				$oDb->update( $this->_sHeartbeatTable, array(
					'name' => $sName,
					'date_modified' => $this->getDateTime()
				), sprintf( 'hid = %d', $iHbId ) );
				
			} else {
				
				// insert
				$oDb->insert( $this->_sHeartbeatTable, array(
					'hid' => $iHbId,
					'name' => $sName,
					'polled' => 0,
					'date_created' => $this->getDateTime(),
					'date_modified' => $this->getDateTime()
				) );
				
			}
			
		}
		
		return $this;
	}
	
	//
	public function trackTag( $sName, $sTitle, $iMentions, $iHbId = NULL ) {
		
		if ( $sName ) {
		
			$oDb = $this->_oDb;
			
			$iHbId = $this->resolveHbId( $iHbId );
			
			
			$sName = trim( $sName );
			$sTitle = trim( $sTitle );
			$iMentions = intval( $iMentions );
			
			
			$aPrev = $oDb->fetchRow( sprintf(
				"SELECT id, mentions, date_modified FROM %s WHERE ( hid = %d ) AND ( name = '%s' )",
				$this->_sHbTagTable,
				$iHbId,
				$sName
			) );
			
			
			if ( is_array( $aPrev ) ) {
				
				$iHbTagId = intval( $aPrev[ 'id' ] );
				$iPrevMentions = intval( $aPrev[ 'mentions' ] );
				
				// update
				$oDb->update( $this->_sHbTagTable, array(
					'title' => $sTitle,
					'mentions' => $iMentions,
					'date_modified' => $this->getDateTime()
				), array(
					sprintf( 'hid = %d', $iHbId ),
					sprintf( "name = '%s'", $sName )
				) );
				
				//
				$this->trackDelta( $this->_iHbTagTableId, $iHbTagId, $iMentions, $iPrevMentions, $aPrev[ 'date_modified' ] );
				
			} else {
				
				// insert
				$oDb->insert( $this->_sHbTagTable, array(
					'hid' => $iHbId,
					'name' => $sName,
					'title' => $sTitle,
					'mentions' => $iMentions,
					'date_created' => $this->getDateTime(),
					'date_modified' => $this->getDateTime()
				) );
				
			}
			
		}
		
		return $this;
	}
	
	
	//
	public function trackMapFeed( $aFeed ) {
		
		$oDb = $this->_oDb;
		
		$sFeedId = $oDb->fetchOne( sprintf(
			"SELECT sid FROM %s WHERE sid = '%s'",
			$this->_sHbMapFeedTable,
			$aFeed[ 'id' ]
		) );
		
		if ( !$sFeedId ) {
			
			$iLocId = $this->getLocationId( $aFeed[ 'location' ], $aFeed[ 'country' ] );
			
			// insert
			$oDb->insert( $this->_sHbMapFeedTable, array(
				'sid' => $aFeed[ 'id' ],
				'seq' => $aFeed[ 'seq' ],
				'time' => $aFeed[ 'time' ],
				'ts' => $aFeed[ 'ts' ],
				'type' => $aFeed[ 'type' ],
				'sentiment' => $aFeed[ 'sentiment' ],
				'loc_id' => $iLocId
			) );
			
			// Update geo_location match count
			$iLocCount = intval( $oDb->fetchOne( sprintf(
				"SELECT match_count FROM %s WHERE id = %d",
				$this->_sGeoLocationTable,
				$iLocId
			) ) );
			
			$oDb->update( $this->_sGeoLocationTable, array(
				'match_count' => ( $iLocCount + 1 )
			), sprintf( 'id = %d', $iLocId ) );
			
		}
		
	}
	
	
	//// geography
	
	//
	public function getCountryId( $sCountryAbbr ) {
		
		$oDb = $this->_oDb;
		
		$sCountryAbbr = strtoupper( trim( $sCountryAbbr ) );
		
		$iCountryId = $oDb->fetchOne( sprintf(
			"SELECT id FROM %s WHERE abbr = '%s'",
			$this->_sGeoCountryTable,
			$sCountryAbbr
		) );
		
		return $iCountryId;
	}
	
	//
	public function trackCountry( $sCountryAbbr, $iMentions, $iHbId = NULL ) {
		
		$oDb = $this->_oDb;
		
		$iHbId = $this->resolveHbId( $iHbId );
		$iMentions = intval( $iMentions );
		
		
		if ( $iCountryId = $this->getCountryId( $sCountryAbbr ) ) {
			
			
			$aPrev = $oDb->fetchRow( sprintf(
				"SELECT id, mentions, date_modified FROM %s WHERE ( hid = %d ) AND ( country_id = %d )",
				$this->_sHbCountryTable,
				$iHbId,
				$iCountryId
			) );
			
			
			if ( is_array( $aPrev ) ) {
				
				$iHbCountryId = intval( $aPrev[ 'id' ] );
				$iPrevMentions = intval( $aPrev[ 'mentions' ] );
				
				// update
				$oDb->update( $this->_sHbCountryTable, array(
					'mentions' => $iMentions,
					'date_modified' => $this->getDateTime()
				), array(
					sprintf( 'hid = %d', $iHbId ),
					sprintf( 'country_id = %d', $iCountryId )
				) );
				
				//
				$this->trackDelta( $this->_iHbCountryTableId, $iHbCountryId, $iMentions, $iPrevMentions, $aPrev[ 'date_modified' ] );
				
			} else {
				
				// insert
				$oDb->insert( $this->_sHbCountryTable, array(
					'hid' => $iHbId,
					'country_id' => $iCountryId,
					'mentions' => $iMentions,
					'date_created' => $this->getDateTime(),
					'date_modified' => $this->getDateTime()
				) );
				
			}
			
		}
		
		return $this;
	}
	
	
	
	//
	public function getLocationId( $sLocation, $sCountryAbbr ) {
		
		$oDb = $this->_oDb;
		
		$oLocFmt = new Geko_Sysomos_LocationFormat( $sLocation );
		
		$aInfo = $oLocFmt->getInfo();
		
		$sNormLocation = $aInfo[ 'norm_location' ];
		$fLat = floatval( $aInfo[ 'lat' ] );
		$fLon = floatval( $aInfo[ 'lon' ] );
		$bLocChanged = $aInfo[ 'loc_changed' ];
		$sHash = $aInfo[ 'key' ];

		
		$iLocId = $oDb->fetchOne( sprintf(
			"SELECT id FROM %s WHERE hash = '%s'",
			$this->_sGeoLocationTable,
			$sHash
		) );
		
		if ( FALSE === $iLocId ) {
			
			$aValues = array(
				'hash' => $sHash,
				'location' => $sNormLocation,
				'country_id' => $this->getCountryId( $sCountryAbbr )
			);
			
			if ( $fLat && $fLon ) {
				$aValues[ 'status' ] = Geko_App_Sysomos_Geo_Location::STAT_ALREADY_COORDS;
			}
			
			
			// insert
			$oDb->insert( $this->_sGeoLocationTable, $aValues );
			
			$iLocId = $oDb->lastInsertId();
			
			
			if ( $fLat && $fLon ) {
				
				$aCoord = array(
					'address' => $sNormLocation,
					'hash' => $sHash,
					'lat' => $fLat,
					'lng' => $fLng,
					'type' => '__coords',
					'country_id' => $iCountryId
				);
				
				$this->assignCoords( $iLocId, array( $aCoord ) );
			}
			
		}
		
		return $iLocId;
	}
	
	
	//
	public function getCoordId( $aCoord ) {
		
		$oDb = $this->_oDb;
		
		$sAddress = $aCoord[ 'address' ];
		
		if ( !$sHash = $aCoord[ 'hash' ] ) {
			$sHash = md5( $sAddress );
		}
		
		$iCoordId = $oDb->fetchOne( sprintf(
			"SELECT id FROM %s WHERE hash = '%s'",
			$this->_sGeoCoordsTable,
			$sHash
		) );
		
		
		if ( FALSE === $iCoordId ) {
			
			if ( !$iCountryId = $aCoord[ 'country_id' ] ) {
				$iCountryId = $this->getCountryId( $aCoord[ 'country' ] );
			}
			
			$aValues = array(
				'address' => $sAddress,
				'hash' => $sHash,
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
			$oDb->insert( $this->_sGeoCoordsTable, $aValues );
			
			$iCoordId = $oDb->lastInsertId();
			
		}
		
		
		return $iCoordId;
	}
	
	
	//
	public function assignCoords( $iLocId, $aCoords ) {
		
		$oDb = $this->_oDb;
		
		$i = 0;
		
		foreach ( $aCoords as $aCoord ) {
			
			$i++;
			
			$iCoordId = $this->getCoordId( $aCoord );
			
			$oDb->insert( $this->_sGeoLocCoordsRelTable, array(
				'loc_id' => $iLocId,
				'coord_id' => $iCoordId,
				'idx' => $i
			) );
			
		}
		
		return $this;
	}
	
	
	
	
	//// deltas
	
	//
	public function trackDelta( $iTableId, $iMeasureId, $iCurMentions, $iPrevMentions, $sPrevDate ) {
		
		$oDb = $this->_oDb;
		
		$iCurMentions = intval( $iCurMentions );
		$iPrevMentions = intval( $iPrevMentions );
		
		$iMentionDelta = $iCurMentions - $iPrevMentions;
		
		if ( 0 != $iMentionDelta ) {
		
			$fPercentDelta = abs( $iMentionDelta ) / $iPrevMentions;
			
			// insert
			$oDb->insert( $this->_sHbPollDeltaTable, array(
				'table_id' => $iTableId,
				'measure_id' => $iMeasureId,
				'mention_delta' => $iMentionDelta,
				'percent_delta' => $fPercentDelta,
				'date_delta' => $sPrevDate,
				'date_created' => $this->getDateTime()
			) );
		}
		
	}
	
		
	
	// so that our poll delta table does not get too large
	public function truncateDelta() {
		
		$oDb = $this->_oDb;
		
		$oDb->delete(
			$this->_sHbPollDeltaTable,
			sprintf(
				"date_created < datetime( '%s', '%s' )",
				$this->getDateTime(),
				$this->_sTruncateDeltaInterval
			)
		);
		
	}
	
	
	//
	public function truncateMapFeed() {
		
		$iTsInterval = 60 * 60 * 2;		// 2 hrs
		
		$oDb = $this->_oDb;
		
		$oDb->delete(
			$this->_sHbMapFeedTable,
			sprintf(
				"ts < ( ( SELECT MAX( ts ) FROM %s ) - %d )",
				$this->_sHbMapFeedTable,
				$iTsInterval
			)
		);
		
		/* /
		
		//// TO DO: THIS IS BROKEN NOW!!!
		
		$oDb->delete(
			$this->_sGeoLocationTable,
			array(
				'match_count IS NULL',
				'( country_id = "" ) OR ( country_id IS NULL ) OR ( country_id = 0 )'
			)
		);
		/* */
		
		/* /
		$oDb->delete(
			$this->_sGeoLocationTable,
			sprintf(
				"id NOT IN ( SELECT loc_id FROM %s )",
				$this->_sHbMapFeedTable
			)
		);
		/* */
		
	}
	
	
	
	// find the heartbeat ids not stored and poll those first
	public function getNextToPoll( $aHbIds ) {
		
		$iHbId = NULL;
		
		$oDb = $this->_oDb;
		
		
		$aCurHbIds = $oDb->fetchCol( sprintf( 'SELECT hid FROM %s', $this->_sHeartbeatTable ) );
		
		if ( is_array( $aCurHbIds ) ) {
			$aDiff = array_diff( $aHbIds, $aCurHbIds );
		}
		
		if ( count( $aDiff ) > 0 ) {
			
			$iHbId = array_shift( $aDiff );
		
		} else {
			
			// check first if everyone has been polled
			$bTest = $oDb->fetchOne( sprintf(
				'SELECT COUNT(*) = SUM( CASE WHEN polled IS NULL THEN 0 ELSE polled END ) AS test FROM %s',
				$this->_sHeartbeatTable
			) );
			
			
			if ( $bTest ) {
				// reset
				$oDb->update( $this->_sHeartbeatTable, array( 'polled' => 0 ) );
			}
			
			
			// look at the polled flag
			$iHbId = $oDb->fetchOne( sprintf(
				'SELECT hid FROM %s WHERE ( polled = 0 ) OR ( polled IS NULL ) LIMIT 1',
				$this->_sHeartbeatTable
			) );
			
			
			if ( $iHbId ) {
				$this->_bMarkPolled = TRUE;
			}
			
		}
		
		return $iHbId;
	}
	
	
	
	//
	public function __destruct() {
		
		if ( $this->_bMarkPolled && $this->_iHbId ) {
			
			$oDb = $this->_oDb;
			
			$oDb->update(
				$this->_sHeartbeatTable,
				array( 'polled' => 1 ),
				sprintf( 'hid = %d', $this->_iHbId )
			);
			
		}
		
	}
	
	
}


