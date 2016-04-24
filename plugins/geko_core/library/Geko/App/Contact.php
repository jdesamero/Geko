<?php
/*
 * "geko_core/library/Geko/App/Contact.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Contact extends Geko_App_Entity
{
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->addPlugin( 'Geko_App_Meta_Plugin_Entity' )
		;
				
		return $this;
	}
	
	
	//
	public function getFullName() {
		return sprintf(
			'%s %s',
			$this->getEntityPropertyValue( 'first_name' ),
			$this->getEntityPropertyValue( 'last_name' )
		);
	}
	
	
	// generate a login from the available email fields
	public function generateLogin() {
		
		$sEmail = Geko_String::coalesce(
			$this->getEntityPropertyValue( 'email' ),
			$this->getEntityPropertyValue( 'alt_email' ),
			$this->getEntityPropertyValue( 'business_email' )
		);
		
		return Geko_Inflector::sanitize( $sEmail );
	}
	
	
}


