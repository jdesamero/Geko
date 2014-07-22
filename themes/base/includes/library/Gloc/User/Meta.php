<?php

class Gloc_User_Meta extends Geko_Wp_User_Meta
{

	//
	public function getTitle() {
		return 'Member Properties';
	}
	
	
	/* /
	//
	public function add() {
		
		parent::add();
		
		Geko_Wp_Enumeration_Manage::getInstance()->init();
		
		return $this;
	}
	/* */

	// $aCategories = Geko_Wp_Enumeration_Query::getSet( 'user-categories' );
	
	//
	public function formFields() {
		
		$sRole = '';
		
		if ( $iUserId = $this->resolveUserId() ) {
			$oUser = new Gloc_User( $iUserId );
			$sRole = $oUser->getRoleSlug();
		}
		
		if ( 'subscriber' == $sRole ) {
			
			$this->fieldRow( 'Phone', 'phone' );
			
		}
		
	}
	
}


