<?php

//
class Geko_Wp_Payment_Cash_Transaction extends Geko_Wp_Payment_Transaction
{
	
	
	//
	public function perform() {
		
		$oResponse = parent::perform();
		
		if ( $this->_iTransType == Geko_Wp_Payment::TRANSTYPE_REFUND ) {
			
		} else {
			
			$this->_aTransParams[ 'orig_order_id' ] = $this->_aTransParams[ 'order_id' ];
			
		}
		
		$oResponse->setResponseDataUsingNative( $this );
		
		return $oResponse;
		
	}
	
	
}


