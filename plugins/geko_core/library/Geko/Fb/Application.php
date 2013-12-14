<?php

//
class Geko_Fb_Application extends Geko_Fb_Entity
{
	protected $_sEntityIdVarName = 'app_id';
	
	//
	public function init()
	{
		$this
			->setEntityMapping( 'id', 'app_id' )
			->setEntityMapping( 'title', 'display_name' )
			->setEntityMapping( 'slug', 'canvas_name' )
			->setEntityMapping( 'content', 'description' )
			// ->setEntityMapping( 'date_created', 'created' )
			// ->setEntityMapping( 'date_modified', 'modified' )
		;
		
		return parent::init();
	}
	
	//
	public function getExcludedUserIds( $iCurrUid )
	{
		echo $iUid;
		
		// Retrieve array of friends who've already authorized the app.
		$sFql = sprintf(
			"	SELECT			uid
				FROM			user
				WHERE			uid IN (
					SELECT			uid2
					FROM			friend
					WHERE			uid1 = '%s'
								) AND 
								( is_app_user = 1 )
			",
			$iCurrUid
		);
		
		$aRes = self::$oFb->api_client->fql_query( $sFql );
		
		// Extract the user ID's returned in the FQL request into a new array.
		$aFriendUids = array();
		
		if ( is_array( $aRes ) && count( $aRes ) ) {
			foreach ( $aRes as $aFriend ) {
				$aFriendUids[] = $aFriend['uid'];
			}
		}
		
		return $aFriendUids;
	}
	
}

