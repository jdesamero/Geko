<?php

//
class Geko_Mail_Error_Result extends Geko_Mail_Error
{
	
	//
	public function __construct( $sMessage, $mResult ) {
		
		$this
			->setMessage( $sMessage )
			->setDetails( print_r( $mResult, TRUE ) )
		;
		
	}
	
}


