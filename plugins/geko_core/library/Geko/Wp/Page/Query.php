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
	
	
	//
	public function getSingleEntity( $mParam ) {
		
		$aParams = array();
		
		if ( is_string( $mParam ) ) {
			parse_str( $mParam, $aParams );
		} elseif ( is_array( $mParam ) ) {
			$aParams = $mParam;
		}

		$aParams[ 'post_type' ] = 'page';
		
		return parent::getSingleEntity( $aParams );
	}

}


