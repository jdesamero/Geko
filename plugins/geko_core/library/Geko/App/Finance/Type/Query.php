<?php
/*
 * "geko_core/library/Geko/App/Finance/Type/Query.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Type_Query extends Geko_App_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		//
		if ( $iId = intval( $aParams[ 'id' ] ) ) {
			$oQuery->where( 't.id = ?', $iId );			
		}
		
		
		$bIncludeSummary = $aParams[ 'include_summary' ];
		$iOwnerId = intval( $aParams[ 'owner_id' ] );
		
		//
		if ( $bIncludeSummary || $iOwnerId ) {
			
			$oQuery
				
				->joinLeft( '##pfx##finance', 'f' )
					->on( 'f.type_id = t.id' )
			
			;
			
			if ( $bIncludeSummary ) {
				
				$oQuery
					
					->field( "SUM(
						i.amount * 
						(
							IF( ( t.debit_credit = 0 ) OR ( t.debit_credit IS NULL ) OR ( t.debit_credit = '' ), 1, -1 ) * 
							IF( ( i.debit_credit = 0 ) OR ( i.debit_credit IS NULL ) OR ( i.debit_credit = '' ), -1, 1 )
						)
					)", 'amount' )
					
					
					->joinLeft( '##pfx##finance_item', 'i' )
						->on( 'i.account_id = f.id' )
					
					->group( 't.id' )
					
					->where( 'f.id IS NOT NULL' )
					
				;
				
			}
			
			//
			if ( $iOwnerId ) {
				$oQuery->where( 'f.owner_id = ?', $iOwnerId );			
			}
			
		}
		
		
		return $oQuery;
	}
	
	
}


