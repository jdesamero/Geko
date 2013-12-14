<?php

//
class Geko_Wp_Twitter
{
	
	//
	public static function get( $sTwitterUser ) {
	
		//// twitter feed
		$aTwitterData = NULL;
		
		// TO DO: Allow for custom prefixes to allow for multiple feeds
		$iLastUpdate = get_option( 'geko_twitter_last_update' );
		
		if ( !$iLastUpdate || ( $iLastUpdate < time() ) ) {
			
			$aTwitterCache = Zend_Json::decode( get_option( 'geko_twitter_cache' ) );
			if ( !is_array( $aTwitterCache ) ) $aTwitterCache = array(); 
			$bSetCache = FALSE;
			
			// Get User timeline: Currently broken!!!
			// TO DO: Make this more flexible
			/* /
			$sTwitterRss = 'http://twitter.com/statuses/user_timeline/' . $sTwitterUser;
			$oHttp = new Zend_Http_Client( $sTwitterRss );
			$oXml = simplexml_load_string( $oHttp->request()->getBody() );
			$aTwitterItems = $oXml->channel->item;
			$aTwitterItemsFmt = array();
			foreach ( $aTwitterItems as $oItem ) {
				$oItemCache = new StdClass;
				$oItemCache->title = strval( $oItem->title );
				$oItemCache->pubDate = strval( $oItem->pubDate );
				$aTwitterItemsFmt[] = $oItemCache;
			}
			/* */
			
			$aTwitterItemsFmt = array();
			if ( count( $aTwitterItemsFmt ) > 0 ) {
				$aTwitterCache[ 'items' ] = $aTwitterItemsFmt;
				$bSetCache = TRUE;
			}
			
			// Get tweet count
			$sTwitterUser = 'http://api.twitter.com/1/users/show.xml?screen_name=' . $sTwitterUser;
			$oHttp = new Zend_Http_Client( $sTwitterUser );
			$oXml = simplexml_load_string( $oHttp->request()->getBody() );
			
			$iTweetCount = intval( $oXml->statuses_count );
			if ( $iTweetCount ) {
				$aTwitterCache[ 'tweet_count' ] = $iTweetCount;
				$bSetCache = TRUE;
			}
			
			// only set cache if successful
			if ( $bSetCache ) {
				update_option( 'geko_twitter_cache', Zend_Json::encode( $aTwitterCache ) );	
			}
			
			$aTwitterData = $aTwitterCache;
			
			// update date regardless, so we don't hammer the server
			update_option( 'geko_twitter_last_update', time() + ( 60 * 60 ) );
			
		}
		
		if ( !is_array( $aTwitterData ) ) $aTwitterData = Zend_Json::decode( get_option( 'geko_twitter_cache' ) );
		if ( !is_array( $aTwitterData ) ) $aTwitterData = array();
		
		return $aTwitterData;
		
	}
	
}

