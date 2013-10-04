<?php

//
class Gloc_Service_Form extends Geko_Wp_Service
{

	const STAT_SUCCESS = 1;
	
	const STAT_SEND_NOTIFICATION_FAILED = 500;
	
	const STAT_ERROR = 999;
	
	
	
	//
	public function process() {
		
		global $wpdb;
		
		$iFormId = intval( $_REQUEST[ 'form_id' ] );
		$oForm = new Geko_Wp_Form( $iFormId );
		
		if ( $this->isAction( 'submit' ) && $oForm->isValid() ) {
			
			
			// initialize form response table
			$oFmrspMng = Geko_Wp_Form_Response_Manage::getInstance();
			
			$aValues = array(
				'form_id' => $iFormId,
				'completed' => 1
			);
			
			$aRet = $oFmrspMng->doAddAction( array(), $aValues );
			$iFmrspId = $aRet[ 'entity_id' ];
			
			
			// insert new
			$aKeys = $oForm->getItemKeys();
			
			$sDetails = '';			// for email notification
			
			foreach ( $aKeys as $sKey ) {
				
				$aValues = $_REQUEST[ $sKey ];
				if ( !is_array( $aValues ) ) $aValues = array( $aValues );
				
				foreach ( $aValues as $sValue ) {
					$wpdb->insert(
						$wpdb->geko_form_response_value,
						array(
							'fmrsp_id' => $iFmrspId,
							'slug' => $sKey,
							'value' => $sValue
						)
					);
				}
				
				// serialize
				$sDetails .= sprintf( "%s: %s\n", $sKey, implode( ', ', $aValues ) );
			}
			
			
			// Send admin notification
			try {
				
				$this->deliverMail( 'form-notification', array(
					'merge_params' => array(
						'form_name' => $oForm->getTitle(),
						'form_id' => $iFormId,
						'form_details' => $sDetails
					)
				) )->send();
				
			} catch ( Zend_Mail_Transport_Exception $e ) {
				$this->setStatus( self::STAT_SEND_NOTIFICATION_FAILED );
			}
			
			
			// do DB stuff
			$this->setStatus( self::STAT_SUCCESS );
			
		}
		
		$this->setIfNoStatus( self::STAT_ERROR );
		
		return $this;
	}
	
	
}



