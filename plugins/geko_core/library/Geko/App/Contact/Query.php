<?php
/*
 * "geko_core/library/Geko/App/Contact/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Contact_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function init() {

		$this->addPlugin( 'Geko_App_Meta_Plugin_Query', array(
			'entity_key' => 'contact_id'
		) );
		
		parent::init();
		
		return $this;
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		//// params
		
		if (
			( $iAccountId = intval( $aParams[ 'account_id' ] ) ) || 
			( $aParams[ 'add_account_fields' ] )
		) {
			
			$oQuery
				
				->field( 'ac.login' )
				->field( 'ac.id', 'account_id' )
				
				->joinLeft( '##pfx##account', 'ac' )
					->on( 'ac.rel_id = ct.id' )
					->on( 'ac.rel_type_id = ?', Geko_App_Entity_Type::_getId( 'contact' ) )
			;
			
			if ( $iAccountId ) {
				$oQuery->where( 'ac.id = ?', $iAccountId );
			}
			
		}
		
		//// filters
		
		//
		if ( $mId = $aParams[ 'id' ] ) {
			$oQuery->where( 'ct.id * ($)', $mId );
		}
		
		if ( $sAnyEmail = trim( $aParams[ 'any_email' ] ) ) {
			$oQuery->where( '( ct.email = ? ) OR ( ct.alt_email = ? ) OR ( ct.business_email = ? )', $sAnyEmail );
		}
		
		
		return $oQuery;
	}

}


