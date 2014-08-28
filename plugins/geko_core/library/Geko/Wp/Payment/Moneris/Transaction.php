<?php

require_once( sprintf(
	'%s/external/libs/moneris/mpgClasses.php',
	dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) )
) );

//
class Geko_Wp_Payment_Moneris_Transaction extends Geko_Wp_Payment_Transaction
{
	
	
	//
	public function perform() {
		
		$oResponse = parent::perform();
		
		$oPayment = $this->getPaymentInstance();
		$oAdmin = $this->getAdminInstance();
		
		if ( $oPayment->hasValidLibrary() ) {
		
			//// setup transaction array
			$sOrderIdPrefix = ( $oAdmin->isTestMode() ) ? $this->_aTransParams[ 'test_prefix' ] : '';
			
			$sCardNumber = $this->_aTransParams[ 'card_number' ];
			$fAmount = number_format( $this->_aTransParams[ 'amount' ], 2 );
			$sTestDetails = '';
			
			//// check for error simulation
			if ( $oAdmin->isTestMode() ) {
				
				$sSimulateError = $_POST[ 'simulate_payment_error' ];
				
				if ( in_array( $sSimulateError, array( 'no_error', 'declined', 'hold_card', 'system_timeout' ) ) ) {
					
					$sTestDetails .= 'Force Card Number to: 4242424242424242 (Visa); Original Card Number: ' . $sCardNumber . ';';
					$sCardNumber = '4242424242424242';
					
					// set penny amounts to simulate error
					if ( 'no_error' == $sSimulateError ) {
						$sTestDetails .= ' Simulate: "00/027" Approved; Original amount: ' . $fAmount . ';';
						$fAmount = 4.70;
					} elseif ( 'declined' == $sSimulateError ) {
						$sTestDetails .= ' Simulate: "05/481" Declined; Original amount: ' . $fAmount . ';';
						$fAmount = 4.05;
					} elseif ( 'hold_card' == $sSimulateError ) {
						$sTestDetails .= ' Simulate: "07/480" Hold Card/Call/Capture; Original amount: ' . $fAmount . ';';
						$fAmount = 4.07;
					} elseif ( 'system_timeout' == $sSimulateError ) {
						$sTestDetails .= ' Simulate: "68/113" System Timeout; Original amount: ' . $fAmount . ';';
						$fAmount = 4.68;
					}
					
				} elseif ( 'timeout_error_real' == $sSimulateError ) {
					
					// set a super-short timeout value
					$sTestDetails .= 'Set a super-short timeout value of 1;';
					$oPayment->setGlobal( 'CLIENT_TIMEOUT', 1 );
					
				} elseif ( 'malformed_request' == $sSimulateError ) {
					
					// set a bad card number
					$sTestDetails .= 'Force Card Number to: "Bad Card Number"; Original Card Number: ' . $sCardNumber . ';';
					$sCardNumber = 'Bad Card Number';
					
				}
				
			}
			
			if ( $this->_iTransType == Geko_Wp_Payment::TRANSTYPE_REFUND ) {
				
				$aTransaction = array(
					'type' => 'refund', 
					'order_id' => $sOrderIdPrefix . $this->_aTransParams[ 'orig_order_id' ],
					'txn_number' => $this->_aTransParams[ 'transaction_id' ],
					'amount' => $fAmount
				);
				
			} else {
				
				$aTransaction = array(
					'type' => 'purchase', 
					'order_id' => $sOrderIdPrefix . $this->_aTransParams[ 'order_id' ],
					'cust_id' => $this->_aTransParams[ 'customer_id' ],
					'amount' => $fAmount,
					'pan' => $sCardNumber,
					'expdate' => $this->_aTransParams[ 'expiration_date' ]
				);
				
			}
			
			$aTransaction[ 'crypt_type' ] = $oAdmin->getCryptType();
			
			
			//// setup customer info, if applicable
			
			$oMpgCustInfo = NULL;
			
			if (
				( $sEmail = $this->_aTransParams[ 'customer_email' ] ) || 
				( $sDetails = $this->_aTransParams[ 'details' ] ) || 
				$this->_aBillingParams || $this->_aShippingParams || $this->_aItems
			) {
				
				$oMpgCustInfo = new mpgCustInfo();
				
				$sDetails = trim( $sDetails . ' ' . $sTestDetails );					// append test details if any
				
				if ( $sEmail ) $oMpgCustInfo->setEmail( $sEmail );						// email
				if ( $sDetails ) $oMpgCustInfo->setInstructions( $sDetails );			// details
				
				// billing info
				if ( $this->_aBillingParams ) {
					
					$aAddress = array();
					
					if ( $sAddress = $this->_aBillingParams[ 'address_line_1' ] ) $aAddress[] = $sAddress;
					if ( $sAddress = $this->_aBillingParams[ 'address_line_2' ] ) $aAddress[] = $sAddress;
					if ( $sAddress = $this->_aBillingParams[ 'address_line_3' ] ) $aAddress[] = $sAddress;
					
					$sAddress = implode( ', ', $aAddress );
					
					$oMpgCustInfo->setBilling( array(
						'first_name' => $this->_aBillingParams[ 'first_name' ],
						'last_name' => $this->_aBillingParams[ 'last_name' ],
						'company_name' => $this->_aBillingParams[ 'company_name' ],
						'address' => $sAddress,
						'city' => $this->_aBillingParams[ 'city' ],
						'province' => $this->_aBillingParams[ 'province' ],
						'postal_code' => $this->_aBillingParams[ 'postal_code' ],
						'country' => $this->_aBillingParams[ 'country' ],
						'phone_number' => $this->_aBillingParams[ 'phone_number' ],
						'fax' => $this->_aBillingParams[ 'fax_number' ],
						'tax1' => $this->_aBillingParams[ 'tax_amount_1' ],
						'tax2' => $this->_aBillingParams[ 'tax_amount_2' ],
						'tax3' => $this->_aBillingParams[ 'tax_amount_3' ],
						'shipping_cost' => $this->_aBillingParams[ 'shipping_amount' ]
					) );
					
				}
				
				// shipping info
				if ( $this->_aShippingParams ) {
					
					$aAddress = array();
					
					if ( $sAddress = $this->_aShippingParams[ 'address_line_1' ] ) $aAddress[] = $sAddress;
					if ( $sAddress = $this->_aShippingParams[ 'address_line_2' ] ) $aAddress[] = $sAddress;
					if ( $sAddress = $this->_aShippingParams[ 'address_line_3' ] ) $aAddress[] = $sAddress;
					
					$sAddress = implode( ', ', $aAddress );
					
					$oMpgCustInfo->setShipping( array(
						'first_name' => $this->_aShippingParams[ 'first_name' ],
						'last_name' => $this->_aShippingParams[ 'last_name' ],
						'company_name' => $this->_aShippingParams[ 'company_name' ],
						'address' => $sAddress,
						'city' => $this->_aShippingParams[ 'city' ],
						'province' => $this->_aShippingParams[ 'province' ],
						'postal_code' => $this->_aShippingParams[ 'postal_code' ],
						'country' => $this->_aShippingParams[ 'country' ],
						'phone_number' => $this->_aShippingParams[ 'phone_number' ],
						'fax' => $this->_aShippingParams[ 'fax_number' ],
						'tax1' => $this->_aShippingParams[ 'tax_amount_1' ],
						'tax2' => $this->_aShippingParams[ 'tax_amount_2' ],
						'tax3' => $this->_aShippingParams[ 'tax_amount_3' ],
						'shipping_cost' => $this->_aShippingParams[ 'shipping_amount' ]
					) );
								
				}
				
				// items
				if ( $this->_aItems ) {
					foreach ( $this->_aItems as $aItem ) {
						$oMpgCustInfo->setItems( array(
							'name' => $aItem[ 'product_name' ],
							'quantity' => $aItem[ 'quantity' ],
							'product_code' => $aItem[ 'product_code' ],
							'extended_amount' => $aItem[ 'price_per_item' ]
						) );
					}
				}
				
			}
			
			
			//// perform transaction
			
			$oMpgTxn = new mpgTransaction( $aTransaction ); 
			
			if ( $oMpgCustInfo ) $oMpgTxn->setCustInfo( $oMpgCustInfo );
			
			$oMpgRequest = new mpgRequest( $oMpgTxn ); 
			
			$oMpgHttpPost  = new mpgHttpsPost(
				$oAdmin->getStoreId(), $oAdmin->getApiToken(), $oMpgRequest
			);
			
			
			
			//// get the native response object and format
			
			$oMpgResponse = $oMpgHttpPost->getMpgResponse(); 
			$oResponse->setResponseDataUsingNative( $oMpgResponse );
		
		}
		
		return $oResponse;
		
	}
	
	
}


