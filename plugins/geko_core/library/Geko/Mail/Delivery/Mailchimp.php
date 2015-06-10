<?php

if ( !class_exists( 'Mailchimp' ) ) {
	
	require_once( str_replace(
		'library/Geko/Mail/Delivery',
		'external/libs/mailchimp-api-php/Mailchimp.php',
		dirname( __FILE__ )
	) );
	
}


//
class Geko_Mail_Delivery_Mailchimp extends Geko_Mail_Delivery
{
	
	protected $_sApiKey;
	protected $_oMailchimp;					// mailchimp service object
	
	
	/* List of known exceptions:
	 *		Mailchimp_Invalid_ApiKey
	 *			Invalid MailChimp API Key: <bad key>
	 *		Mailchimp_List_DoesNotExist
	 *			Invalid MailChimp List ID: <bad list id>
	 *		Mailchimp_List_AlreadySubscribed
	 *			<email address> is already subscribed to the list.
	 */
	
	//
	public function __construct( $sApiKey ) {
		
		$this->_sApiKey = $sApiKey;
		
		$this->_oMailchimp = new Mailchimp( $this->_sApiKey );
		
	}
	
	
	//
	public function send( $sEmail, $sFirstName = '', $sLastName = '', $aParams = array(), $aFields = array() ) {
		
		$sListId = $aParams[ 'list_id' ];
		
		
		// first, do checks
		
		if ( !Geko_Validate::email( $sEmail ) ) {
			return Geko_Mail_Error_Validation::create( 'invalid-email', $sEmail );
		}
		
		
		$mRes = $this->subscriberExists( $sListId, $sEmail );
		if ( TRUE === $mRes ) {
			
			return Geko_Mail_Error_Validation::create( 'already-subscribed', $sListId, $sEmail, 'Mailchimp API' );
			
		} elseif ( $mRes instanceof Geko_Mail_Error ) {
			
			return $mRes;
		}
		
		
		
		//
		
		try {
			
			if ( $sFirstName = trim( $sFirstName ) ) {
				$aFields[ 'FNAME' ] = $sFirstName;
			}

			if ( $sLastName = trim( $sLastName ) ) {
				$aFields[ 'LNAME' ] = $sLastName;
			}
			
			
			$aRes = $this
				->_oMailchimp
				->lists
				->subscribe(
					$sListId,
					array( 'email' => $sEmail ),
					$aFields,
					$this->getParam( $aParams, 'email_type', 'html' ),
					$this->getParam( $aParams, 'double_optin', TRUE ),
					$this->getParam( $aParams, 'update_existing ', FALSE ),
					$this->getParam( $aParams, 'replace_interests ', TRUE ),
					$this->getParam( $aParams, 'send_welcome ', FALSE )
				)
			;
			
			
			if (
				( is_array( $aRes ) ) &&
				( strtolower( $aRes[ 'email' ] ) == strtolower( $sEmail ) ) && 
				( $aRes[ 'euid' ] ) && 
				( $aRes[ 'leid' ] )
			) {
				
				// success!!!
				return TRUE;
				
			} else {
				
				// unexpected result
				return new Geko_Mail_Error_Result( 'Unexpected result', $aRes );
				
			}
			
		} catch ( Exception $e ) {
			
			return new Geko_Mail_Error_Exception( $e );
			
		}
		
	}
	
	
	//
	public function subscriberExists( $sListId, $sEmail ) {
		
		// first, do checks
		
		if ( !Geko_Validate::email( $sEmail ) ) {
			return Geko_Mail_Error_Validation::create( 'invalid-email', $sEmail );
		}
		
		
		try {
			
			$aRes = $this
				->_oMailchimp
				->lists
				->memberInfo(
					$sListId,
					array( array( 'email' => $sEmail ) )
				)
			;
			
			
			if ( is_array( $aRes ) ) {
				
				if (
					( 1 == $aRes[ 'success_count' ] ) && 
					( 0 == $aRes[ 'error_count' ] ) &&
					( strtolower( $aRes[ 'data' ][ 0 ][ 'email' ] ) == strtolower( $sEmail ) )
				) {
					
					// success!!!
					return TRUE;
	
				} elseif (
					( 0 == $aRes[ 'success_count' ] ) && 
					( 1 == $aRes[ 'error_count' ] ) &&
					( 232 == $aRes[ 'errors' ][ 0 ][ 'code' ] )
				) {
					
					// success, as well!!!
					return FALSE;
					
				}
				
			}
			
			
			// unexpected result
			return new Geko_Mail_Error_Result( 'Unexpected result', $aRes );
			
			
		} catch ( Exception $e ) {
			
			return new Geko_Mail_Error_Exception( $e );
			
		}
		
	}
	
	
	
	
	/* /
	//
	public function getLists() {
		try {
			print_r( $this->_oMailchimp->lists->getList() );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
	}
	/* */
	

}



