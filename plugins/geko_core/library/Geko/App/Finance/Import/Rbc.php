<?php
/*
 * "geko_core/library/Geko/App/Finance/Import/Rbc.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Import_Rbc extends Geko_App_Finance_Import
{
	
	protected $_aOnlinePaymentMap = array();
	
	
	//
	public function __construct( $aParams ) {
		
		if ( $aOnlinePaymentMap = $aParams[ 'online_payment_map' ] ) {
			$this->_aOnlinePaymentMap = $aOnlinePaymentMap;
		}
		
		parent::__construct( $aParams );
		
	}
	
	
	
	//
	protected function formatCsvRow( $aRow ) {
		
		$sAccountType = $aRow[ 0 ];
		$sAccountNum = $aRow[ 1 ];
		$sTransDate = $aRow[ 2 ];
		$sChequeNum = $aRow[ 3 ];
		$sDesc1 = trim( $aRow[ 4 ] );
		$sDesc2 = trim( $aRow[ 5 ] );
		$fAmount = floatval( $aRow[ 6 ] );
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		
		$sDescription = sprintf( '%s %s', $sDesc1, $sDesc2 );
		$sLongDetails = sprintf( '%s; %s; %s; %s', $sAccountType, $sAccountNum, $sDesc1, $sDesc2 );
		$sExternalRef = '';
		
		$aRegs = array();
		
		if ( $sMappedDesc = $this->getMappedDescription( $sDesc1 ) ) {
			
			$sDescription = $sMappedDesc;
			
		} elseif ( preg_match( '/PTB DEP \-\- ([A-Z0-9]+)/', $sDesc2, $aRegs ) ) {
			
			$sDescription = 'Deposit';
			$sExternalRef = trim( $aRegs[ 1 ] );

		} elseif ( preg_match( '/PTB WD \-\-\- ([A-Z0-9]+)/', $sDesc2, $aRegs ) ) {
			
			$sDescription = 'Cash Withdrawal';
			$sExternalRef = trim( $aRegs[ 1 ] );

		} elseif ( preg_match( '/CHEQUE \- ([0-9]+)/', $sDesc1, $aRegs ) ) {
			
			$sDescription = 'Cheque';
			$sExternalRef = trim( $aRegs[ 1 ] );

		} elseif ( preg_match( '/BR TO BR \- ([0-9]+)/', $sDesc1, $aRegs ) ) {
			
			$sDescription = 'Branch-to-Branch Transfer';
			$sExternalRef = trim( $aRegs[ 1 ] );

		} elseif ( preg_match( '/INTERAC E\-TRF\- ([0-9]+)/', $sDesc2, $aRegs ) ) {
			
			$sDescription = 'Interac Email Money Transfer';
			$sExternalRef = trim( $aRegs[ 1 ] );
			
		} elseif ( preg_match( '/WWW PAYMENT \- ([0-9]+) ([A-Z \-]+)/', $sDesc2, $aRegs ) ) {
			
			$sVendor = trim( $aRegs[ 2 ] );
			if ( $sVendorFmt = $this->getMappedOnlinePayment( $sVendor ) ) {
				$sVendor = $sVendorFmt;
			}
			
			$sDescription = sprintf( 'Online Payment: %s', $sVendor );
			
			$sExternalRef = trim( $aRegs[ 1 ] );
		
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

