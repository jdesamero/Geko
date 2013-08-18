<?php

//
class Geko_Wp_Payment_Cash_Response extends Geko_Wp_Payment_Response
{

	
	//
	public function setResponseDataUsingNative( $oTxn ) {
		
		$aTxnParams = $oTxn->getTransactionParams();
		$aBillingParams = $oTxn->getBillingParams();
		
		$this->_aResponseData = array(
			'transaction_type_id' => $oTxn->getTransactionType(),
			'receipt_id' => $aTxnParams[ 'order_id' ],
			'orig_receipt_id' => $aTxnParams[ 'orig_order_id' ],
			// 'customer_id' => $aTxnParams[ '' ],
			'first_name' => $aBillingParams[ 'first_name' ],
			'last_name' => $aBillingParams[ 'last_name' ],
			'phone_number' => $aBillingParams[ 'phone_number' ],
			'email' => $aTxnParams[ 'customer_email' ],
			'details' => $aTxnParams[ 'details' ],
			'amount' => $aTxnParams[ 'amount' ]
		);
		
		return $this;
	}
	
	//
	public function setTransactionId( $iTxnId ) {
		$this->_aResponseData[ 'transaction_id' ] = $iTxnId;
	}
	
	//
	public function getStatusId() {
		
		/*
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
		*/
		
		// return parent::getStatusId();
		
		return Geko_Wp_Payment_Admin::STATUS_APPROVED;
		
	}

	
}


