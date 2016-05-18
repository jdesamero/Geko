<?php
/*
 * "geko_core/library/Geko/App/Finance/Import/RogersBank.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Import_RogersBank extends Geko_App_Finance_Import
{
	
	
	//
	protected function formatCsvRow( $aRow ) {
		
		$sTransDate = $aRow[ 0 ];
		$sPostingDate = $aRow[ 1 ];
		
		$sAmount = str_replace( '$', '', $aRow[ 2 ] );
		if ( FALSE !== strpos( $sAmount, '(' ) ) {
			$sAmount = sprintf( '-%s', str_replace( array( '(', ')' ), '', $sAmount ) );
		}
		
		$fAmount = floatval( $sAmount );
		
		$sDescription = trim( $aRow[ 3 ] );
		$sCity = trim( $aRow[ 4 ] );
		$sState = trim( $aRow[ 5 ] );
		$sPostalCode = trim( $aRow[ 6 ] );
		
		$sExternalRef = trim( str_replace( '"', '', $aRow[ 7 ] ) );
		
		$sDebitCredit = trim( $aRow[ 8 ] );
		$sSicMccCode = trim( $aRow[ 9 ] );
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$sLocation = '';
		
		if ( $sCity || $sState || $sPostalCode ) {
			$sLocation = sprintf( '%s, %s, %s', $sCity, $sState, $sPostalCode );
		}
		
		$sLongDetails = trim( sprintf(
			'%s; %s; %s; %s; %s; %s',
			$sPostingDate,
			$sDescription,
			$sLocation,
			$sExternalRef,
			$sDebitCredit,
			$sSicMccCode
		) );
		
		
		if ( $sMappedDesc = $this->getMappedDescription( $sDescription ) ) {
			
			$sDescription = $sMappedDesc;
			
		}
		
		list( $sMon, $sDay, $sYear ) = explode( '/', $sTransDate );
		
		$sDate = sprintf( '%s-%s-%s', $sYear, $sMon, $sDay );
		
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
					'external_reference' => $sExternalRef,
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

