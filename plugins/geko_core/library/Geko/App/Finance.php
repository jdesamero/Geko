<?php
/*
 * "geko_core/library/Geko/App/Finance.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance extends Geko_App_Entity
{
	
	protected static $_aAccounts = NULL;
	
	protected $_sEntityIdVarName = 'id';
	
	
	
	
	//
	public static function _getAccounts( $iOwnerId = NULL ) {
		
		if ( NULL === self::$_aAccounts ) {
			self::$_aAccounts = new Geko_App_Finance_Query( array(), FALSE );
		}
		
		if ( $iOwnerId ) {
			
			return self::$_aAccounts->subsetOwnerId( $iOwnerId );
		}
		
		return self::$_aAccounts;
	}
	
	
	
	//// instance methods
	
	//
	public function init() {
		
		parent::init();
		
		
		$this
			->setEntityMapping( 'title', 'name' )
		;
		
		return $this;
	}

	
	
}


