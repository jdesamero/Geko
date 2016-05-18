<?php
/*
 * "geko_core/library/Geko/App/Finance/Import/Cibc.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Import_Cibc extends Geko_App_Finance_Import
{
	
	protected $_bSkipFirstLine = FALSE;
	
	
	//
	protected function formatCsvRow( $aRow ) {
		
		$sTransDate = $aRow[ 0 ];
		$sDetails = trim( $aRow[ 1 ] );
		$sFundsOut = $aRow[ 2 ];
		$sFundsIn = $aRow[ 3 ];
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$sDescription = $sDetails;
		$sLongDetails = $sDetails;
		
		
		if ( $sMappedDesc = $this->getMappedDescription( $sDescription ) ) {
			
			$sDescription = $sMappedDesc;
			
		}
		
		
		
		
		$sExternalRef = '';
		
		$sFundsIn = trim( $sFundsIn );
		$sFundsOut = trim( $sFundsOut );
		
		$iDebitCredit = ( $sFundsIn ) ? 0 : 1 ;
		$fAmount = floatval( $sFundsIn ? $sFundsIn : $sFundsOut );
				
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		// output formatted item
		return parent::formatCsvRow( array(
			'details' => $sDescription,
			'date_entered' => $sTransDate,
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

