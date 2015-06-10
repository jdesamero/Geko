<?php

//
class Geko_Mail_Error_Exception extends Geko_Mail_Error
{

	
	//
	public function __construct( $e ) {
		
		$this
			->setType( get_class( $e ) )
			->setMessage( $e->getMessage() )
			->setDetails( strval( $e ) )
		;
		
	}
	

}


