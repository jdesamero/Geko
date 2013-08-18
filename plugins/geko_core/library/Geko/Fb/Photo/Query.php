<?php

//
class Geko_Fb_Photo_Query extends Geko_Fb_Entity_Query
{
	
	//
	public function getEntities( $mParam )
	{
		// var_dump( $mParam );
		
		// public function &photos_getAlbums( $uid, $aids )
		if ( is_array( $mParam ) ) {
			return $this->filterEntityArray(
				self::$oFb->api_client->photos_get(
					$mParam['subj_id'],
					$mParam['aid'],
					$this->implodeQueryParams( $mParam['pids'] )
				),
				$mParam
			);
		}
		
		// TO DO:
		// $mParam might be an FQL
	}
	
	
}

