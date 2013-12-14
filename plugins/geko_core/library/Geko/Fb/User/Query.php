<?php

//
class Geko_Fb_User_Query extends Geko_Fb_Entity_Query
{
	
	//
	public function getEntities( $mParam )
	{
		// var_dump( $mParam );
		
		// public function &photos_getAlbums( $uid, $aids )
		
		if ( is_array( $mParam ) ) {
			return $this->filterEntityArray(
				self::$oFb->api_client->users_getInfo(
					$this->implodeQueryParams( $mParam['uids'] ),
					$this->implodeQueryParams( $mParam['fields'] )
				),
				$mParam
			);
		}
		
		// TO DO:
		// $mParam might be an FQL
	}
	
	
	//
	public function getDefaultParams()
	{
		$aParams = parent::getDefaultParams();
		
		$aParams['fields'] = array(
			'uid',
			'first_name',
			'last_name',
			'name',
			'locale',
			'current_location',
			'affiliations',
			'pic_square',
			'profile_url',
			'sex'
		);
		
		return $aParams;
	}
	
	
}

