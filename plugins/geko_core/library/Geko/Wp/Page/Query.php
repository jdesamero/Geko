<?php

//
class Geko_Wp_Page_Query extends Geko_Wp_Post_Query
{
	
	// implement by sub-class to process $aParams
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		$aParams[ 'post_type' ] = 'page';
		
		return $aParams;
	}
	
	
	
}


