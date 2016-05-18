<?php
/*
 * "geko_core/library/Geko/App/Finance/Import/Tangerine.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Import_Tangerine extends Geko_App_Finance_Import
{
	
	
	//
	protected function formatCsvRow( $aRow ) {
		
		$sTransDate = $aRow[ 0 ];
		$sTransType = trim( $aRow[ 1 ] );
		$sDescription = trim( $aRow[ 2 ] );
		$sMemo = trim( $aRow[ 3 ] );
		$fAmount = floatval( $aRow[ 4 ] );
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$sLongDetails = trim( sprintf( '%s; %s; %s', $sTransType, $sDescription, $sMemo ) );
		
		if ( $sMappedDesc = $this->getMappedDescription( $sDescription ) ) {
			
			$sDescription = $sMappedDesc;
			
		}
		
		list( $iMon, $iDay, $iYear ) = explode( '/', $sTransDate );
		
		$sDate = sprintf( '%d-%02d-%02d', $iYear, $iMon, $iDay );
		
		$iDebitCredit = ( $fAmount > 0 ) ? 0 : 1 ;
		$fAmount = abs( $fAmount );
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
				
		// output formatted item
		return parent::formatCsvRow( array(
			'details' => $sDescription,
			'date_entered' => $sDate,
			'items' => array(
				array(
					'details' => $sDescription,
					'long_details' => $sLongDetails,
					'external_reference' => '',
					'debit_credit' => $iDebitCredit,
					'amount' => $fAmount,
					'sort_order' => 1
				)
			)
		) );
		
	}
	
	
	//
	protected function getMappedOnlinePayment( $sOnlinePayment ) {
		return $this->getMapped( $sOnlinePayment, $this->_aOnlinePaymentMap );
	}

	
	
}

