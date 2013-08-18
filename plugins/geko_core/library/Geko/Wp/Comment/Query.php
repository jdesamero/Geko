<?php

//
class Geko_Wp_Comment_Query extends Geko_Wp_Entity_Query
{
	
	// implement by sub-class to process $aParams
	public function init( $aParams ) {
		
		$this->_aEntities = get_comments( $aParams );
		$this->_iTotalRows = count( $this->_aEntities );
		
		return $aParams;
	}
	
	//
	public function getSingleEntity( $mParam ) {
		
		$aRes = get_comments( $mParam );
		
		if ( count( $aRes ) > 0 ) {
			return $aRes[ 0 ];
		}
		
		return NULL;
	}
	
}


