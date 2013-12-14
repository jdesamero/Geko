<?php

abstract class Geko_Fb_Entity extends Geko_Entity
{
	protected static $oFb;
	
	//
	public static function setApiObj( $oFb )
	{
		self::$oFb = $oFb;
		Geko_Fb_Entity_Query::setApiObj( $oFb );
	}
	
	// ------------------------------------------------------------------------------------------ //
	
	//
	public function assertEntityId( $mEntity )
	{
		if (
			preg_match( '/^[0-9_-]+$/', $mEntity ) &&
			( $mEntityId = strval( $mEntity ) )
		) {
			return $mEntityId;
		}
		
		return NULL;
	}
	
	
}



