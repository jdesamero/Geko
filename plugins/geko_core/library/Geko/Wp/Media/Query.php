<?php

//
class Geko_Wp_Media_Query extends Geko_Wp_Post_Query
{
	private static $bInitCalled = FALSE;
	
	// implement by sub-class to process $aParams
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		if ( !self::$bInitCalled ) {
			Geko_Wp_Media_QueryHooks::register();
			self::$bInitCalled = TRUE;
		}
		
		$aParams = array_merge(
			$aParams,
			array( 'post_files' => 1 )
		);
		
		return $aParams;
	}
	
}


