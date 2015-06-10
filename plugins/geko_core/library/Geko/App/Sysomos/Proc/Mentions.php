<?php

//
class Geko_App_Sysomos_Proc_Mentions extends Geko_Proc
{
	
	protected $_sStartMsg = 'Get mentions!!!';
	
	protected $_bTrackTags = FALSE;
	protected $_bTrackCountry = TRUE;

	
	
	
	//
	public function getCountryGroups( $iGroupCount = NULL, $iTopOnly = NULL ) {
		
		if ( NULL !== $iTopOnly ) {
			
			// query only the countries with the most mentions
			
			$aMeasure = new Geko_App_Sysomos_Heartbeat_Country_Query( array(
				'limit' => $iTopOnly,
				'orderby' => 'mention_total',
				'order' => 'DESC'
			), FALSE );
			
			$aCountryFilters = $aMeasure->gatherAbbr();
			
		} else {
			
			$oGeoCoun = Geko_Geography_Country::getInstance();
			
			// query all countries
			$aCountryFilters = array_keys( $oGeoCoun->getCountries() );
		}
		
		
		if ( NULL === $iGroupCount ) {
			// no grouping required, wrap in an array
			return array( $aCountryFilters );
		}
		
		// otherwise, perform grouping
		
		$aGrouped = array();
		
		$aGroup = array();
		foreach ( $aCountryFilters as $sCountryCode ) {
			$iCount = count( $aGroup );
			$aGroup[] = $sCountryCode;
			if ( $iCount == ( $iGroupCount - 1 ) ) {
				$aGrouped[] = $aGroup;
				$aGroup = array();		// reset
			}
		}
		
		return $aGrouped;
	}
	
	
	
	
	//
	public function runTrackTags( $oSysPoll, $aTagTypes ) {
		
		//
		if ( $this->_bTrackTags ) {
			
			$this->output( 'start_track_by_tag', 'By tag...' );
			
			$aTags = $aTagTypes[ 'tag' ];
			
			foreach ( $aTags as $sKey => $aRow ) {
				
				$sTitle = $aRow[ 'title' ];
				$iMentions = $aRow[ 'mentions' ];
				
				$oSysPoll->trackTag( $sKey, $sTitle, $iMentions );
				
				$this->output(
					'track_tag',
					sprintf( '%s - %s (%d)', $sKey, $sTitle, $iMentions ),
					array(
						'tag' => $aRow,
						'key' => $sKey
					)
				);
				
			}
		
		}

	}
	
	//
	public function runTrackCountry( $oSysPoll, $aTagTypes ) {

		//
		if ( $this->_bTrackCountry ) {

			$this->output( 'start_track_by_country', 'By country...' );
			
			$aCountries = $aTagTypes[ 'country' ];
			
			if ( is_array( $aCountries ) ) {
				
				foreach ( $aCountries as $sKey => $aRow ) {
					
					$sTitle = $aRow[ 'title' ];
					$iMentions = $aRow[ 'mentions' ];
					
					
					$oSysPoll->trackCountry( $sKey, $iMentions );

					$this->output(
						'track_country',
						sprintf( '%s - %s (%d)', $sKey, $sTitle, $iMentions ),
						array(
							'tag' => $aRow,
							'key' => $sKey
						)
					);

				}
			}
			
		}
		
	}
	
	
	//
	public function runMeasure( $oHb, $oSysPoll, $aRes, $aGrouped ) {
		
		
		//// set up filters
		
		$aTagFilters = array_keys( $aRes[ 'tags' ] );
		
		
		
		//
		foreach ( $aGrouped as $aGroup ) {
			
			$aMes = $oHb->getMeasure( array(
				'startday' => $sStartDay,
				'numdays' => $iNumDays,
				'subfilter' => array(
					'tag' => $aTagFilters,
					'country' => $aGroup
				)
			) );
			
			
			if ( $sErrorMsg = $aMes[ 'error_msg' ] ) {
				
				$this->output( 'get_measure_error', $sErrorMsg, $aMes );	
				
			} else {
				
				$aTagTypes = $aMes[ 'tags' ];
				
				$this->runTrackTags( $oSysPoll, $aTagTypes );
				$this->runTrackCountry( $oSysPoll, $aTagTypes );
				
			}
			
		}
		
		
	}
	

	//
	public function run( $aHbIds, $sStartDay, $iCountryGroupCount = NULL, $iTopOnly = NULL ) {
		
		// start
		$this->start();
		
		
		$oDb = Geko_App::get( 'db' );
		
		
		// country group
		$aGrouped = $this->getCountryGroups( $iCountryGroupCount, $iTopOnly );
		
		
		// $iCycleCount = 1;
		$iCycleCount = count( $aHbIds );
		
		
		
		$iNumDays = 0;
		
		for ( $i = 0; $i < $iCycleCount; $i++ ) {
			
			$oSysPoll = new Geko_App_Sysomos_Poll( $aHbIds, $oDb );
			
			if ( $iHbId = $oSysPoll->getHbId() ) {
				
				$oHb = new Geko_Sysomos_Heartbeat( $iHbId );
				
				$aRes = $oHb->getInfo();
				
				
				if ( $sErrorMsg = $aRes[ 'error_msg' ] ) {
				
					$this->output( 'get_info_error', $sErrorMsg, $aRes );
					
				} else {
					
					$oSysPoll->track( $aRes[ 'name' ] );
					
					$this->output(
						'track_heartbeat',
						sprintf( 'Heartbeat: %s', $aRes[ 'name' ] ),
						$aRes
					);
					
					
					$this->runMeasure( $oHb, $oSysPoll, $aRes, $aGrouped );
					
				}
				
				
			} else {
				
				$this->output( 'nothing_to_poll', 'Nothing to poll!!!' );
				
				break;
			}
			
			unset( $oSysPoll );	
		}
		
		
		$oSysPoll = new Geko_App_Sysomos_Poll( NULL, $oDb );
		$oSysPoll->truncateDelta();
		
		
		$this->finish();
		
		
	}


}


