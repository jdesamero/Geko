<?php

//
class Geko_Wp_Cart66_Gateway_Beanstream extends Cart66GatewayAbstract
{

	protected $_apiData;
	protected $_apiEndPoint;
	
	
	protected $_sTitle = 'Beanstream';
	protected $_sSlug = 'beanstream';
	protected $_sUrl = 'https://www.beanstream.com/scripts/process_transaction.asp';
	
	
	
	//
	public function getInstance() {
		
		static $oInstance;
		
		if ( !$oInstance ) {
			$sClass = __CLASS__;
			$oInstance = new $sClass();
		}
		
		return $oInstance;
	}
	
	
	//
	public function __construct() {
		
		parent::__construct();
		
		// initialize error arrays
		$this->_errors = array();
		$this->_jqErrors = array();
		
		$mode = 'LIVE';
		if ( Cart66Setting::getValue( 'payleap_test_mode' ) ) {
			$mode = 'TEST';
		}
		
		$this->clearErrors();
		
		// Set end point and api credentials
		$apiUsername = Cart66Setting::getValue( 'payleap_api_username' );
		$apiTransactionKey = Cart66Setting::getValue( 'payleap_transaction_key' );
		$apiEndPoint = Cart66Setting::getValue( 'auth_url' );
		
		if ( 'TEST' == $mode ) {
			$apiEndPoint = 'https://uat.payleap.com/TransactServices.svc/ProcessCreditCard';
			$apiUsername = Cart66Setting::getValue( 'payleap_test_api_username' );
			$apiTransactionKey = Cart66Setting::getValue( 'payleap_test_transaction_key' );
		}
		
		$this->_apiEndPoint = $apiEndPoint;
		
		// Set api data
		$this->_apiData[ 'APIUSERNAME' ] = $apiUsername;
		$this->_apiData[ 'TRANSACTIONKEY' ] = $apiTransactionKey;
		
		if ( !( $this->_apiData[ 'APIUSERNAME' ] && $this->_apiData[ 'TRANSACTIONKEY' ] ) ) {
			throw new Cart66Exception( 'Invalid Beanstream Configuration', 66520 ); 
		}
		
	}
	
	
	
	/**
	 * Return an array of accepted credit card types where the keys are the diplay values and the values are the gateway values
	 * 
	 * @return array
	 */
	public function getCreditCardTypes() {
	
		$cardTypes = array();
		$setting = new Cart66Setting();
		$cards = Cart66Setting::getValue( 'auth_card_types' );
		
		if ( $cards ) {
			
			$cards = explode( '~', $cards );
		
			if ( in_array( 'mastercard', $cards ) ) {
				$cardTypes[ 'MasterCard' ] = 'mastercard';
			}
		
			if ( in_array( 'visa', $cards ) ) {
				$cardTypes[ 'Visa' ] = 'visa';
			}
		
			if ( in_array( 'amex', $cards ) ) {
				$cardTypes[ 'American Express' ] = 'amex';
			}
		
			if ( in_array( 'discover', $cards ) ) {
				$cardTypes[ 'Discover' ] = 'discover';
			}
		}
		
		return $cardTypes;
	}
	
	
	
	//
	public function initCheckout( $total ) {
	
		$p = $this->getPayment();
		$b = $this->getBilling();
	
		Cart66Common::log( 'Payment info for checkout: ' . print_r( $p, true ) );
		
		$extData = $this->generateExtendedData();
	
		$expMonth = $p[ 'cardExpirationMonth' ];
		$expYear = substr( $p[ 'cardExpirationYear' ], -2 );
		// $this->addField( 'Username', $this->_apiData[ 'APIUSERNAME' ] );
		// $this->addField( 'Password', $this->_apiData[ 'TRANSACTIONKEY' ] );
		$this->addField( 'requestType', 'BACKEND' );
		$this->addField( 'merchant_id', '117586520' );
		$this->addField( 'trnCardOwner', $b[ 'firstName' ] . '+' . $b[ 'lastName' ] );
		$this->addField( 'trnCardNumber', $p[ 'cardNumber' ] );
		$this->addField( 'trnExpMonth', $expMonth );
		$this->addField( 'trnExpYear', $expYear );
		$this->addField( 'trnOrderNumber', '' );
		
		$this->addField( 'trnAmount', $total );
		$this->addField( 'Amount', $total );
		
		$this->addField( 'ordEmailAddress', $p[ 'email' ] );
		$this->addField( 'ordName', $b[ 'firstName' ] . ' ' . $b[ 'lastName' ] );
		$this->addField( 'ordPhoneNumber', preg_replace( '/\D/', '', $p[ 'phone' ] ) );
		$this->addField( 'ordAddress1', $b[ 'address' ] );
		$this->addField( 'ordCity', $b[ 'city' ] );
		$this->addField( 'ordProvince', $b[ 'state' ] );
		$this->addField( 'ordPostalCode', $b[ 'zip' ] );
		$this->addField( 'ordCountry', $b[ 'country' ] );
		
		// set b for redeemPoints
		// $this->addField( 'ref1', $b[ 'redeemPoints' ] );
		
		$this->addField( 'ExpDate', $expMonth . $expYear );
		$this->addField( 'trnCardCvd', $p[ 'securityId' ] );
		
		//// TO DO!!!!!
		
		/* /
		// Update User Info if not exist.
		
		$oMainLayout = Gloc_Layout_Main::getInstance();
		
		if ( $oMainLayout->isLoggedIn() ) {
			
			$oUser = $oMainLayout->getUser();
			
			global $wpdb;
			
			$strSQL = "select * from wp_geko_location_address where object_id = " . $oUser->getId();
			
			$result = $wpdb->get_results( $strSQL );
			
			if ( !$result ) {
				$strSQL = "INSERT INTO wp_geko_location_address (object_id, objtype_id, address_line_1, address_line_2, city, province_id, postal_code) VALUES (" . $oUser->getId() . ",3,'" . $b['address'] . "','" . $b['address2'] . "','" . $b['city'] . "','0','" . $b['zip'] . "')";
				// echo $strSQL;
				// break;
				$result = $wpdb->query( $strSQL );
			}
		}
		/* */
		
	}


	//
	private function generateExtendedData() {
		
		$b = $this->getBilling();
		$p = $this->getPayment();
		
		$billTo = array(
			'Name' => $b[ 'firstName' ] . ' ' . $b[ 'lastName' ],
			'Address' => array(
				'Street' => $b[ 'address' ],
				'City' => $b[ 'city' ],
				'State' => $b[ 'state' ],
				'Zip' => $b[ 'zip' ],
				'Country' => $b[ 'country' ]
			),
			'Email' => $p[ 'email' ],
			'Phone' => preg_replace( '/\D/', '', $p[ 'phone' ] )
		);
		
		$invoice = array(
			'InvNum' => '',
			'BillTo' => $billTo
		);
		
		$data = array(
			'TrainingMode' => 'F',
			'Invoice' => $invoice
		);
		
		$xml = trim( $this->arrayToXml( $data ) );
		$xml = preg_replace( '/>\s+</', '><', $xml );
		
		return $xml;
	}
	
	
	//
	public static function arrayToXml( $array, $name = false, $space = '', $standalone = false, $beginning = true, $nested = 0 ) {
	
		$output = '';
	
		if ( $beginning ) {
			
			if ( $standalone ) header( 'content-type:text/xml;charset=utf-8' );
			if ( !isset( $output ) ) { $output = ''; }
			if ( $standalone ) $output .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			
			if ( !empty( $space ) ) {
				$output .= '<' . $name . ' xmlns="' . $space . '">' . "\n";
			} elseif( $name ) {
				$output .= '<' . $name . '>' . "\n";
			}
			
			$nested = 0;
		}
		
		// This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
		$ArrayNumberPrefix = 'ARRAY_NUMBER_';
		
		foreach ( $array as $root => $child ) {
			
			if ( is_array( $child ) ) {
				
				$output .= str_repeat( ' ', ( 2 * $nested ) ) . '  <' . ( is_string( $root ) ? $root : $ArrayNumberPrefix . $root ) . '>' . "\n";
				$nested++;
				$output .= self::arrayToXml( $child, NULL, NULL, NULL, FALSE, $nested );
				$nested--;
				$tag = is_string( $root ) ? $root : $ArrayNumberPrefix . $root;
				$ex = explode( ' ', $tag );
				$tag = array_shift( $ex );
				$output .= str_repeat( ' ', ( 2 * $nested ) ) . '  </' . $tag . '>' . "\n";
			
			} else {
				
				if ( !isset( $output ) ) { $output = ''; }
				$tag = is_string( $root ) ? $root : $ArrayNumberPrefix . $root;
				$ex = explode( ' ', $tag );
				$tag = array_shift( $ex );
				$output .= str_repeat( ' ', ( 2 * $nested ) ) . '  <' . ( is_string( $root ) ? $root : $ArrayNumberPrefix . $root ) . '>' . $child . '</' . $tag . '>' . "\n";
				
			}
		}
		
		$ex = explode( ' ', $name );
		$name = array_shift( $ex );
		if ( $beginning && $name ) $output .= '</' . $name . '>';
		
		return $output;
	}
	
	//
	public function addField( $field, $value ) {
		$this->fields[ $field ] = $value;
	}

	//
	public function doSale() {
    	
    	$sale = false;
    	
    	if ( $this->fields[ 'Amount' ] > 0 ) {
    		
    		foreach( $this->fields as $key => $value ) {
    			$this->field_string .= $key . '=' . urlencode( $value ) . '&';
    		}
    		
    		$header = array( 'MIME-Version: 1.0', 'Content-type: application/x-www-form-urlencoded', 'Contenttransfer-encoding: text' ); 
			$ch = curl_init();
			
			// set URL and other appropriate options 
			curl_setopt( $ch, CURLOPT_URL, 'https://www.beanstream.com/scripts/process_transaction.asp' ); 
			curl_setopt( $ch, CURLOPT_VERBOSE, 1 ); 
			curl_setopt( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP ); 
			// uncomment for host with proxy server
			// curl_setopt( $ch, CURLOPT_PROXY, 'http://proxyaddress:port' ); 
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $header ); 
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE ); 
			curl_setopt( $ch, CURLOPT_POST, TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, rtrim( $this->field_string, '& ' ) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			
			
			// send packet and receive response
			// close the curl resource, and free system resources
			$this->response_string = urldecode( curl_exec( $ch ) );
			// $result = curl_exec( $ch );
			// echo $result;
			// echo "test:". $this->response_string;
			// break;
			
			if ( curl_errno( $ch ) ) {
				$this->response[ 'Response Reason Text' ] = curl_error( $ch );
			} else {
				curl_close( $ch );
			}
			
			parse_str( $this->response_string, $responseVars );
			// echo $responseVars[ 'avsMessage' ];
			// print_r( $responseVars );
			// break;
			
			// $xml = new SimpleXMLElement( $this->response_string );
			$this->response[ 'Response Reason Text' ] = $responseVars[ 'avsMessage' ];
			$this->response[ 'Transaction ID' ] = $responseVars[ 'trnId' ];
			$this->response[ 'Response Code' ] = $responseVars[ 'avsResult' ];
			$this->response[ 'Approved' ] = $responseVars[ 'trnApproved' ];
			// $this->dump_response();
			
			// Prepare to return the transaction id for this sale.
			
			if ( $this->response[ 'Approved' ] == 1 ) {
				
				//// TO DO!!!!!!!!!!!!!!!!!
				/* /
				$sale = $this->response[ 'Transaction ID' ];
				
				$oPtMng = Geko_Wp_Point_Manage::getInstance();

				$oMainLayout = Gloc_Layout_Main::getInstance();

				if ( $oMainLayout->isLoggedIn() ) {
					
					$oUser = $oMainLayout->getUser();
					
					// Remove points if discount was selected
					if ( _e( 'Discount', 'cart66' ) ) {
					
						$mRes = $oPtMng->getPoints( $oUser->getId() );
						
						$aPoints = array(
							'user_id' => $oUser->getId(),
							'point_event_slug' => 'shop-redeem', 
							'point_value' => $mRes,
							'meta' => array(
								'order_id' => $this->response[ 'Transaction ID' ]					
							)					
						);
						
						$mRes = $oPtMng->awardPoints( $aPoints );
					}
					
					// specify parameters for awarding the points using test parameters.
					$aPoints = array(
						'user_id' => $oUser->getId(),
						'point_event_slug' => 'shop-earn',
						'meta' => array(
							'order_id' => $this->response[ 'Transaction ID' ],
							'point_value' => intval( $total * BODYPLUS_POINTS_PER_DOLLAR )
						)	
					);
					
					// returns TRUE when successful, and a numerical error code if it fails
					$mRes = $oPtMng->awardPoints( $aPoints );
					
					if ( TRUE === $mRes ) {
						// do stuff
					} else {
						// point could not be awarded due to error
					} 
          
				}
				/* */
				
			}
			
		} else {
			// Process free orders without sending to the Auth.net gateway
			$this->response[ 'Transaction ID' ] = 'MT-' . Cart66Common::getRandString();
			$sale = $this->response[ 'Transaction ID' ];
		}
	
		return $sale;
	}
	
	//
	public function getResponseReasonText() {
		return $this->response[ 'Response Reason Text' ];
	}
	
	//
	public function getTransactionId() {
		return $this->response[ 'Transaction ID' ];
	}
  

	//
	public function getTransactionResponseDescription() {
		
		$description[ 'errormessage' ] = $this->getResponseReasonText();
		$description[ 'errorcode' ] = $this->response[ 'Response Code' ];
		
		$this->_logFields();
		$this->_logResponse();
		
		return $description;
	}


	//   
	protected function _logResponse() {
		
		$out = "Beanstream Response Log\n";
	
		foreach ( $this->response as $key => $value ) {
			$out .= "\t$key = $value\n";
		}
		
		Cart66Common::log( sprintf( '[%s - line %s] %s', basename( __FILE__ ), __LINE__, $out ) );
	}
	
	
	//
	protected function _logFields() {
		
		$out = "Beanstream Field Log\n";
	
		foreach ( $this->fields as $key => $value ) {
			$out .= "\t$key = $value\n";
		}
		
		Cart66Common::log( sprintf( '[%s - line %s] %s', basename( __FILE__ ), __LINE__, $out ) );
	}
	
	//
	public function dumpFields() {
		
		// Used for debugging, this function will output all the field/value pairs
		// that are currently defined in the instance of the class using the
		// add_field() function.
		
		?>
		<h3>beanstream_class->dump_fields() Output:</h3>
		<table width="95%" border="1" cellpadding="2" cellspacing="0">
			<tr>
				<td bgcolor="black"><b><font color="white">Field Name</font></b></td>
				<td bgcolor="black"><b><font color="white">Value</font></b></td>
			</tr>
			<?php foreach ( $this->fields as $key => $value ): ?>
				<tr>
					<td><?php echo $key; ?></td>
					<td><?php echo urldecode( $value ); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<br />
		<?php
	}
	
	
	//
	public function dumpResponse() {
		
		// Used for debugging, this function will output all the response field
		// names and the values returned for the payment submission.  This should
		// be called AFTER the process() function has been called to view details
		// about payleap's response.
		
		?>
		<h3>payleap_class->dump_response() Output:</h3>
		<table width="95%" border="1" cellpadding="2" cellspacing="0">
			<tr>
				<td bgcolor="black"><b><font color="white">Index&nbsp;</font></b></td>
				<td bgcolor="black"><b><font color="white">Field Name</font></b></td>
				<td bgcolor="black"><b><font color="white">Value</font></b></td>
			</tr>
			<?php $i = 0; foreach ( $this->response as $key => $value ): ?>
				<tr>
					<td valign="top" align="center"><?php echo $i; ?></td>
					<td valign="top"><?php echo $key; ?></td>
					<td valign="top"><?php echo $value; ?>&nbsp;</td>
				</tr>
			<?php $i++; endforeach; ?>
		</table>
		<br />
		<?php
	}
	
	
	//// settings form methods
	
	//
	public function settingsForm( $oDoc ) {
		
		$sOption = sprintf(
			'<option id="%s_url" value="%s">%s</option>',
			$this->_sSlug,
			$this->_sUrl,
			$this->_sTitle
		);
		
		$oAfter = $oDoc->find( 'option#authorize_test_url' );
		$oSel = $oDoc->find( 'select#auth_url' );
		
		if ( $oAfter->length() > 0 ) {
			$oAfter->after( $sOption );
		} else {
			$oSel->append( $sOption );
		}
		
		// $oSel->removeAttr( 'selected' );
		
		return $oDoc;
	}
	
	
}


