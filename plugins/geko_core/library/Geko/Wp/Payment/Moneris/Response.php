<?php

require_once( sprintf(
	'%s/external/libs/moneris/mpgClasses.php',
	dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) )
) );

//
class Geko_Wp_Payment_Moneris_Response extends Geko_Wp_Payment_Response
{

	
	//
	public function setResponseDataUsingNative( $oMpgResponse ) {
		
		$oPayment = $this->getPaymentInstance();
		// $oAdmin = $this->getAdminInstance();
		
		if ( $oPayment->hasValidLibrary() ) {
		
			$this->_aResponseData = array(
				'card_type' => $oMpgResponse->getCardType(),
				'amount' => $oMpgResponse->getTransAmount(),
				'transaction_id' => $oMpgResponse->getTxnNumber(),
				'receipt_id' => $oMpgResponse->getReceiptId(),
				'transaction_type' => $oMpgResponse->getTransType(),
				'reference_number' => $oMpgResponse->getReferenceNum(),
				'response_code' => $oMpgResponse->getResponseCode(),
				'iso_code' => $oMpgResponse->getISO(),
				'message' => $oMpgResponse->getMessage(),
				'authorization_code' => $oMpgResponse->getAuthCode(),
				'complete' => $oMpgResponse->getComplete(),
				'transaction_date' => $oMpgResponse->getTransDate(),
				'transaction_time' => $oMpgResponse->getTransTime(),
				'ticket' => $oMpgResponse->getTicket(),
				'timed_out' => $oMpgResponse->getTimedOut()
			);
		
		}
		
		return $this;
	}
	
	
	//
	public function getStatusId() {
		
		$oPayment = $this->getPaymentInstance();
		
		if ( $this->_aResponseData && $oPayment->hasValidLibrary() ) {
			
			$sReceiptId = trim( strtolower( $this->_aResponseData[ 'receipt_id' ] ) );
			
			if ( $sReceiptId && ( 'null' != $sReceiptId ) ) {
				
				$sMessage = strtolower( $this->_aResponseData[ 'message' ] );
			
				if ( FALSE !== strpos( $sMessage, 'approved' ) ) {
					return Geko_Wp_Payment_Admin::STATUS_APPROVED;
				} elseif ( FALSE !== strpos( $sMessage, 'declined' ) ) {
					return Geko_Wp_Payment_Admin::STATUS_DECLINED;
				} else {
					return Geko_Wp_Payment_Admin::STATUS_FAILED;
				}
				
			} else {
				return Geko_Wp_Payment_Admin::STATUS_ERROR;
			}
			
		}
		
		return parent::getStatusId();
	}

	
}


