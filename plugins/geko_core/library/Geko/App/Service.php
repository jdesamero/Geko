<?php

//
class Geko_App_Service extends Geko_Service
{
	
	
	//
	public function getAction() {

		if ( $sAction = trim( $_GET[ 'section' ] ) ) {
			return $sAction;
		}
		
		if ( $sAction = trim( $this->aLeftovers[ 1 ] ) ) {
			return $sAction;
		}
		
		return parent::getAction();
	}
	
	
}

