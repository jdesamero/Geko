<?php

//
class Geko_Wp_Comment_Query extends Geko_Wp_Entity_Query
{
	
	// implement by sub-class to process $aParams
	public function init( $aParams ) {
		
		$this->setRawEntities( get_comments( $aParams ) );
		
		return $aParams;
	}
	
	
	
}


