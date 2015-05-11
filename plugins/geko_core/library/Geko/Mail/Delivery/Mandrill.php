<?php


if ( !class_exists( 'Mandrill' ) ) {
	
	require_once( str_replace(
		'library/Geko/Mail/Delivery',
		'external/libs/mailchimp-mandrill-api-php/Mandrill.php',
		dirname( __FILE__ )
	) );
	
}



//
class Geko_Mail_Delivery_Mandrill extends Geko_Mail_Delivery
{
	
	protected $_sApiKey;
	protected $_oMandrill;					// mandrill service object
	
	//
	public function __construct( $sApiKey ) {
		
		$this->_sApiKey = $sApiKey;
		
		$this->_oMandrill = new Mandrill( $this->_sApiKey );
		
	}
	
	
	
	
	//
	public function send( $sEmail, $sFirstName = '', $sLastName = '', $aParams = array(), $aFields = array() ) {
		
		// first, do checks
		
		if ( !Geko_Validate::email( $sEmail ) ) {
			return Geko_Mail_Error_Validation::create( 'invalid-email', $sEmail );
		}
		
		
		try {
			
			$sTemplate = $aParams[ 'template_name' ];
			
			$aTo = array(
				array(
					'email' => $sEmail,
					'name' => sprintf( '%s %s', $sFirstName, $sLastName ),
					'type' => 'to'
				)
			);
			
			
			
			$aMessage = array();
			
			
			$aData = array();
			
			foreach ( $aParams as $sKey => $sValue ) {
				
				if ( in_array( $sKey, array( 'html', 'text', 'subject', 'from_email', 'from_name' ) ) ) {
					
					$aMessage[ $sKey ] = $sValue;
					unset( $aParams[ $sKey ] );
				}
				
			}
			
			foreach ( $aFields as $sKey => $sValue ) {
				
				$aData[] = array(
					'name' => $sKey,
					'content' => $sValue
				);				
			}
			
			
			$aMessage = array_merge( $aMessage, array(
				'to' => $aTo,
				'global_merge_vars' => $aData,
				'merge' => TRUE
			) );
			
			
			if ( $sTemplate ) {
				
				$aRes = $this
					->_oMandrill
					->messages
					->sendTemplate( $sTemplate, $aData, $aMessage )
				;
			
			} else {

				$aRes = $this
					->_oMandrill
					->messages
					->send( $aMessage )
				;
				
			}
			

			if (
				( is_array( $aRes ) ) &&
				( strtolower( $aRes[ 0 ][ 'email' ] ) == strtolower( $sEmail ) )
			) {

				if ( 'sent' == $aRes[ 0 ][ 'status' ] ) {
					
					// success!!!
					return TRUE;
					
				} elseif ( 'rejected' == $aRes[ 0 ][ 'status' ] ) {
					
					// Rejected
					return new Geko_Mail_Error_Result( sprintf( 'Rejected: %s', $aRes[ 0 ][ 'reject_reason' ] ), $aRes );
				
				}
			
			}
			
			
			// unexpected result
			return new Geko_Mail_Error_Result( 'Unexpected result', $aRes );
			
			
		} catch ( Exception $e ) {
			
			return new Geko_Mail_Error_Exception( $e );
			
		}
		
	}


}



