<?php

//
class Geko_Mail_Error_Validation extends Geko_Mail_Error
{
	
	
	//
	public static function create() {
		
		$aArgs = func_get_args();
		
		$sType = array_shift( $aArgs );
		$sMessage = '';
		$sDetails = '';
		
		if ( 'already-subscribed' == $sType ) {
			
			list( $sListId, $sEmail, $sChecker ) = $aArgs;
			
			$sMessage = 'Email already subscribed to list.';
			
			$sDetails = sprintf(
				'List id: %s; Email: %s; Checker: %s',
				$sListId,
				$sEmail,
				$sChecker
			);
		
		} elseif ( 'invalid-email' == $sType ) {
			
			list( $sEmail ) = $aArgs;
			
			$sMessage = 'Email provided is invalid.';
			$sDetails = sprintf( 'Email: %s', $sEmail );
			
		} else {
			
			$sMessage = sprintf( 'Unknown type: %s', $sType );
			$sType = 'unknown-validation-type';
			$sDetails = print_r( $aArgs, TRUE );
			
		}
		
		return new Geko_Mail_Error_Validation( $sType, $sMessage, $sDetails );
		
	}
	
	
	//
	public function __construct( $sType, $sMessage, $sDetails ) {
		
		$this
			->setType( $sType )
			->setMessage( $sMessage )
			->setDetails( $sDetails )
		;
		
	}
	

}


