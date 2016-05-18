<?php
/*
 * "geko_core/library/Geko/App/Finance/Import/Amex.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Import_Amex extends Geko_App_Finance_Import
{
	
	protected $_bSkipFirstLine = FALSE;
	
	
	
	//
	protected function formatCsvRow( $aRow ) {
		
		$sTransDate = $aRow[ 0 ];
		$sReference = $aRow[ 1 ];
		$fAmount = floatval( $aRow[ 2 ] );
		$sDesc1 = trim( $aRow[ 3 ] );
		$sDesc2 = trim( $aRow[ 4 ] );
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		
		$sDescription = trim( sprintf( '%s %s', $sDesc1, $sDesc2 ) );
		$sLongDetails = sprintf( '%s; %s', $sDesc1, $sDesc2 );
		
		
		if ( $sMappedDesc = $this->getMappedDescription( $sDescription ) ) {
			
			$sDescription = $sMappedDesc;
			
		}
		
		
		list( $iMon, $iDay, $iYear ) = explode( '/', $sTransDate );
		
		$sDate = sprintf( '%d-%02d-%02d', $iYear, $iMon, $iDay );
		
		$iDebitCredit = ( $fAmount > 0 ) ? 0 : 1 ;
		$fAmount = abs( $fAmount );
		
		
		$sExternalRef = trim( str_replace( 'Reference:', '', $sReference ) );
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		// output formatted item
		return parent::formatCsvRow( array(
			'details' => $sDescription,
			'date_entered' => $sDate,
			'items' => array(
				array(
					'details' => $sDescription,
					'long_details' => $sLongDetails,
					'external_reference' => $sExternalRef,
					'debit_credit' => $iDebitCredit,
					'amount' => $fAmount,
					'sort_order' => 1
				)
			)
		) );
		
	}
	
	
	
}

