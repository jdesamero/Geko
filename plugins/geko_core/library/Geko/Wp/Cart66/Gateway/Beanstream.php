<?php

//
class Geko_Wp_Cart66_Gateway_Beanstream extends Geko_Wp_Cart66_Gateway
{
	
	protected $_sTitle = 'Beanstream';
	protected $_sSlug = 'beanstream';
	protected $_sUrl = 'https://www.beanstream.com/scripts/process_transaction.asp';
	
	
	
	
	//
	public function preInitCheckout() {

		// initialize error arrays
		$this->_errors = array();
		$this->_jqErrors = array();
		
		
		$bTestMode = $this->getSettingValue( 'test_mode' ) ? TRUE : FALSE ;
		
		$this->clearErrors();
		
		// Set end point and api credentials
		
		$apiEndPoint = Cart66Setting::getValue( 'auth_url' );
		
		$apiMerchantId = $this->getSettingValue( 'merchant_id' );		
		
		if ( $bTestMode ) {
			$apiMerchantId = $this->getSettingValue( 'test_merchant_id' );		
		}
		
		$this->_apiEndPoint = $apiEndPoint;
		
		// Set api data
		$this->_apiData[ 'MERCHANTID' ] = $apiMerchantId;
		
		if ( !$this->_apiData[ 'MERCHANTID' ] ) {
			throw new Cart66Exception( sprintf( 'Invalid %s Configuration', $this->_sTitle ), 66520 ); 
		}
		
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
		$this->addField( 'merchant_id', $this->_apiData[ 'MERCHANTID' ] );
		$this->addField( 'trnCardOwner', $b[ 'firstName' ] . ' ' . $b[ 'lastName' ] );
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
	public function doSale() {
    	
    	$sale = false;
    	
    	if ( $this->fields[ 'Amount' ] > 0 ) {
    		
    		foreach( $this->fields as $key => $value ) {
    			$this->field_string .= sprintf( '%s=%s&', $key, urlencode( $value ) );
    		}
    		
    		$header = array( 'MIME-Version: 1.0', 'Content-type: application/x-www-form-urlencoded', 'Contenttransfer-encoding: text' ); 
			$ch = curl_init();
			
			// set URL and other appropriate options 
			
			// $this->_apiEndPoint is the same as $this->_sUrl
			curl_setopt( $ch, CURLOPT_URL, $this->_apiEndPoint ); 
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
				
				$sale = $this->response[ 'Transaction ID' ];
				
				//// TO DO!!!!!!!!!!!!!!!!!
				/* /
				
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
	  
	
	
	//// settings form manipulation methods
	
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
		$oSettingsDiv = $oDoc->find( '#gateway-other_gateways' );
		$oTable = $oSettingsDiv->find( 'table.form-table' );
		
		if ( $oAfter->length() > 0 ) {
			$oAfter->after( $sOption );
		} else {
			$oSel->append( $sOption );
		}
		
		// logo
		$oSettingsDiv->prepend( sprintf( '
			<a class="%srow" target="_blank" href="#" style="display: inline;">
				<img align="left" alt="%s" src="%s/beanstream_logo.png" />
			</a>		
		', $this->_sPrefix, $this->_sTitle, Geko_Uri::getUrl( 'geko_ext_images' ) ) );
		
		
		// fields
		$oTable->find( 'tbody' )->append(
			Geko_String::fromOb( array( $this, 'outputFields' ) )
		);
		
		// populate form values
		$oTable = Geko_Html::populateForm( $oTable, $this->getFormValues( array(
			'merchant_id', 'test_mode', 'test_merchant_id'
		) ), TRUE );
		
		return $oDoc;
	}
	
	
	//
	public function outputFields() {
		
		$sTitle = $this->_sTitle;
		$sPrefix = $this->_sPrefix;
		
		?>
		<tr class="<?php echo $sPrefix; ?>row" valign="top" style="display: table-row;">
			<th scope="row">Merchant ID</th>
			<td>
				<input id="<?php echo $sPrefix; ?>merchant_id" class="regular-text" type="text" value="" name="<?php echo $sPrefix; ?>merchant_id">
			</td>
		</tr>
		<tr class="<?php echo $sPrefix; ?>row" valign="top" style="display: table-row;">
			<th scope="row"><?php echo $sTitle; ?> Test Mode</th>
			<td>
				<input id="<?php echo $sPrefix; ?>test_mode_yes" type="radio" value="1" name="<?php echo $sPrefix; ?>test_mode">
				<label for="<?php echo $sPrefix; ?>test_mode_yes">Yes</label>
				<input id="<?php echo $sPrefix; ?>test_mode_no" type="radio" value="0" name="<?php echo $sPrefix; ?>test_mode">
				<label for="<?php echo $sPrefix; ?>test_mode_no">No</label>
			</td>
		</tr>
		<tr class="<?php echo $sPrefix; ?>row" valign="top" style="display: table-row;">
			<th scope="row">Test Merchant ID</th>
			<td>
				<input id="<?php echo $sPrefix; ?>test_merchant_id" class="regular-text" type="text" value="" name="<?php echo $sPrefix; ?>test_merchant_id">
			</td>
		</tr>
		<?php
	}
	
	
	//
	public function settingsScript( $oDoc ) {
		
		$oFirst = $oDoc->find( ':first' );
		
		$sJs = $oFirst->text();
		
		$sFind = 'function setGatewayDisplay() {';
		
		$sReplace = $sFind . sprintf( "
			
			\$jq( '.%s_row' ).hide();
			if ( \$jq( '#auth_url :selected' ).attr( 'id' ) == '%s_url' ) {
				\$jq( '.%s_row' ).show();
			}
			
		", $this->_sSlug, $this->_sSlug, $this->_sSlug );
		
		$oFirst->text( str_replace( $sFind, $sReplace, $sJs ) );
		
		return $oDoc;
	}
	
	
	
}


