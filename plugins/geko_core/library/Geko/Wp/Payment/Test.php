<?php

//
class Geko_Wp_Payment_Test extends Geko_Wp_Payment
{

	protected $bValidLibrary = TRUE;		// no external libraries used
	
	//
	public function getGatewayId() {
		return Geko_Wp_Payment::GATEWAY_ID_TEST;
	}
	
	
}




