<?php

//
class Geko_Fb_Application_Query extends Geko_Fb_Entity_Query
{
	
	//
	public function getEntities( $mParam )
	{
		// var_dump( $mParam );
		
		// application_getPublicInfo() will retrieve only one value
		
		// public function &photos_getAlbums( $uid, $aids )
		if ( is_array( $mParam ) ) {
			return array(
				self::$oFb->api_client->application_getPublicInfo(
					$mParam['app_id'],
					$mParam['app_api_key'],
					$mParam['app_canvas_name']
				)
			);
		}
		
		// TO DO:
		// $mParam might be an FQL
	}

	
}

