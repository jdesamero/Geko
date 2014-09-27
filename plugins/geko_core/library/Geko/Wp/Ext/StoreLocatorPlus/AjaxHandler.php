<?php

//
class Geko_Wp_Ext_StoreLocatorPlus_AjaxHandler extends SLPlus_AjaxHandler
{
	
	protected $_aLocationFilters = array();
	
	
	
	//
	public function execute_LocationQuery( $optName_HowMany = '' ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		//........
		// SLP options that tweak the query
		//........
		
		// Distance Unit (KM or MI) Modifier
		// Since miles is default, if kilometers is selected, divide by 1.609344 in order to convert the kilometer value selection back in miles
		//
		
		$iMultiplier = ( get_option( 'sl_distance_unit' ) == 'km' ) ? 6371 : 3959 ;
		
		// Return How Many?
		//
		if ( empty( $optName_HowMany ) ) {
			$optName_HowMany = sprintf( sprintf( '%s_maxreturned', SLPLUS_PREFIX ) );
		}
		
		$maxReturned = trim( get_option( $optName_HowMany, '25' ) );
		
		if ( !is_numeric( $maxReturned ) ) {
			$maxReturned = '25';
		}
		
		
		
		////// START ORIG CODE //////
		
		/* /
		
		//........
		// Post options that tweak the query
		//........
		
		// Add all the location filters together for SQL statement.
		// FILTER: slp_location_filters_for_AJAX
		//
		$filterClause = '';
		$locationFilters = apply_filters( 'slp_location_filters_for_AJAX', array() );
				
		foreach ( $locationFilters as $filter ) {
			$filterClause .= $filter;
		}
		
		// Set the query
		// FILTER: slp_mysql_search_query
		//
		$this->dbQuery = apply_filters( 'slp_mysql_search_query',
			"SELECT *,".
			"( $iMultiplier * acos( cos( radians('%s') ) * cos( radians( sl_latitude ) ) * cos( radians( sl_longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( sl_latitude ) ) ) ) AS sl_distance ".
			"FROM {$this->plugin->db->prefix}store_locator ".
			"WHERE sl_longitude<>'' and sl_latitude<>'' ".
			$filterClause . ' ' .
			"HAVING (sl_distance < %d) ".
			'ORDER BY sl_distance ASC '.
			'LIMIT %d'
		);
		
		// Run the query
		//
		// First convert our placeholder dbQuery into a string with the vars inserted.
		// Then turn off errors so they don't munge our JSONP.
		//
		$this->dbQuery = $wpdb->prepare(
			$this->dbQuery,
			$_POST[ 'lat' ],
			$_POST[ 'lng' ],
			$_POST[ 'lat' ],
			$_POST[ 'radius' ],
			$maxReturned
		);
		
		/* */
		
		////// END ORIG CODE //////
		
		
		
		////// START NEW CODE //////
				
		// track this for debugging
		$this->_aLocationFilters = apply_filters( 'slp_location_filters_for_AJAX', array() );
		
		$aParams = array(
			'multiplier' => $iMultiplier,
			'latitude' => $_POST[ 'lat' ],
			'longitude' => $_POST[ 'lng' ],
			'radius' => $_POST[ 'radius' ],
			'limit' => $maxReturned
		);
		
		$oQuery = $this->getLocationQuery( $aParams );
		$oQuery = apply_filters( sprintf( '%s::search_query', get_class( $this ) ), $oQuery, $aParams );
		
		$this->dbQuery = strval( $oQuery );
		
		
		////// END NEW CODE //////
		
		
		
		// $wpdb->hide_errors();
		
		$result = $oDb->fetchAllAssoc( $this->dbQuery );
		$result = apply_filters( sprintf( '%s::search_result', get_class( $this ) ), $result, $aParams );
		
		
		
		// Problems?  Oh crap.  Die.
		// $wpdb->last_error ???
		//
		if ( $result === null ) {
			die ( json_encode( array(
				'success' => FALSE, 
				'response' => sprintf( 'Invalid query: %s', '(unknown)' ),
				'dbQuery' => $this->dbQuery
			) ) );
		}
		
		// Return the results
		//
		return $result;
	}
	
	
	//
	public function getLocationQuery( $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		
		$oQuery
			
			->field( 'la.address_line_1', 'sl_address' )
			->field( 'la.address_line_2', 'sl_address2' )
			->field( 'la.city', 'sl_city' )
			->field( 'lp.province_name', 'sl_state' )
			->field( 'la.postal_code', 'sl_zip' )
			->field( 'lc.country_name', 'sl_country' )
			->field( 'la.latitude', 'sl_latitude' )
			->field( 'la.longitude', 'sl_longitude' )
			->field( 'la.address_id', 'sl_id' )
			
			
			->field( sprintf( "
				(
					%d * 
					acos(
						cos( radians('%s') ) * 
						cos( radians( la.latitude ) ) * 
						cos(
							radians( la.longitude ) - 
							radians('%s')
						) + 
						sin( radians('%s') ) * 
						sin( radians( la.latitude ) )
					)
				)
			", $aParams[ 'multiplier' ], $aParams[ 'latitude' ], $aParams[ 'longitude' ], $aParams[ 'latitude' ] ), 'sl_distance' )
			
			
			->from( '##pfx##geko_location_address', 'la' )
			
			->joinLeft( '##pfx##geko_location_province', 'lp' )
				->on( 'lp.province_id = la.province_id' )

			->joinLeft( '##pfx##geko_location_country', 'lc' )
				->on( 'lc.country_id = lp.country_id' )
				
				
			->having( 'sl_distance < ?', $aParams[ 'radius' ] )
			->order( 'sl_distance', 'ASC' )
			->limit( $aParams[ 'limit' ] )
			
		;
		
		return $oQuery;
	}
	
	
	/* */
	//
	public function renderJSON_Response( $mData ) {
		
		if ( is_array( $mData ) ) {
			$mData[ 'geko_override_msg' ] = sprintf( 'JSON was overridden by %s', get_class( $this ) );
			$mData[ 'location_filters' ] = $this->_aLocationFilters;
		}
		
		parent::renderJSON_Response( $mData );
	}
	/* */
	
	
}



