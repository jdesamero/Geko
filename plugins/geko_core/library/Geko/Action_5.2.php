<?php

//
class Geko_Action
{
	
	//
	public static function get( $sMethod, $aArgs, $oReq ) {
		
		// $sMethod and $aArgs currently un-used
		
    	$sAction = sprintf( 'Action_%s_%s', $oReq->getControllerName(), $oReq->getActionName() );
    	
    	$sAction = preg_replace_callback( '/_[a-z]/', create_function(
			'$matches',
			'return strtoupper($matches[0]);'
    	), $sAction );
    	
    	if ( @class_exists( $sAction ) ) {
    		return $sAction;
    	} else {
    		return FALSE;		
		}
		
	}
	

}

