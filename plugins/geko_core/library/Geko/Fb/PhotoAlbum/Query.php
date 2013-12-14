<?php

//
class Geko_Fb_PhotoAlbum_Query extends Geko_Fb_Entity_Query
{
	
	//
	public function getEntities( $mParam )
	{
		// var_dump( $mParam );
		
		// public function &photos_getAlbums( $uid, $aids )
		if ( is_array( $mParam ) ) {
			return $this->filterEntityArray(
				self::$oFb->api_client->photos_getAlbums(
					$mParam['uid'],
					$this->implodeQueryParams( $mParam['aids'] )
				),
				$mParam
			);
		}
		
		// TO DO:
		// $mParam might be an FQL
	}

	
}

