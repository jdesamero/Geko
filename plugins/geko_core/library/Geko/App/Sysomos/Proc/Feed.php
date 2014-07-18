<?php

//
class Geko_App_Sysomos_Proc_Feed extends Geko_Proc
{
	
	protected $_sStartMsg = 'Get feeds!!!';
	
	
	
	//
	public function run( $aHbIds ) {
		
		// start
		$this->start();
		
		
		
		$oDb = Geko_App::get( 'db' );
		
		
		
		$oSysPoll = new Geko_App_Sysomos_Poll( NULL, $oDb );
		
		
		//
		foreach ( $aHbIds as $iHbId ) {
			
			$oHb = new Geko_Sysomos_Heartbeat( $iHbId );
			
			// $aRes = $oHb->getInfo();
			$aRes = $oHb->getRssContent();
			
			if ( $sErrorMsg = $aRes[ 'error_msg' ] ) {
				
				$this->output( 'get_rss_content_error', $sErrorMsg, $aRes );
				
			} else {
				
				$aFeeds = $aRes[ 'feed' ];
				
				foreach ( $aFeeds as $aFeed ) {

					$oSysPoll->trackMapFeed( $aFeed );
					
					$this->output(
						'track_map_feed',
						sprintf( '%s - %s', $aFeed[ 'country' ], $aFeed[ 'location' ] ),
						$aFeed
					);
					
				}
			
			}
			
		}
		
		
		// $oSysPoll->resolveMissingCoords();
		$oSysPoll->truncateMapFeed();
		
		
		$this->finish();
		
	}
	
	
}



