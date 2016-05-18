<?php
/*
 * "geko_core/library/Geko/App/Finance/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 'f.id = ?', $iId );			
		}
		
		//
		if ( $iOwnerId = intval( $aParams[ 'owner_id' ] ) ) {
			$oQuery->where( 'f.owner_id = ?', $iOwnerId );			
		}
		
		
		//
		if ( $aParams[ 'include_summary' ] ) {
			
			$oQuery
				
				->field( "SUM(
					i.amount * 
					(
						IF( ( t.debit_credit = 0 ) OR ( t.debit_credit IS NULL ) OR ( t.debit_credit = '' ), 1, -1 ) * 
						IF( ( i.debit_credit = 0 ) OR ( i.debit_credit IS NULL ) OR ( i.debit_credit = '' ), -1, 1 )
					)
				)", 'amount' )
				
				->joinLeft( '##pfx##finance_type', 't' )
					->on( 't.id = f.type_id' )
					
				->joinLeft( '##pfx##finance_item', 'i' )
					->on( 'i.account_id = f.id' )
				
				->group( 'f.id' )
				
				->where( 'i.id IS NOT NULL' )
				
			;
			
		}
		
		return $oQuery;
	}
	
	
}


