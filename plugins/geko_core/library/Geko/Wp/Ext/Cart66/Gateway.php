<?php

//
class Geko_Wp_Ext_Cart66_Gateway extends Cart66GatewayAbstract
{
	
	protected $_apiData;
	protected $_apiEndPoint;
	
	
	protected $_sTitle = NULL;
	protected $_sSlug = NULL;
	protected $_sPrefix = NULL;
	protected $_sUrl = NULL;
	
	protected $_sInstanceClass = NULL;
	
	protected $_oCalculation = NULL;
	
	
	
	
	//
	public static function getInstance( $bInitCheckout = TRUE ) {
		
		static $oInstance;
		
		if ( !$oInstance ) {
			$sClass = get_called_class();
			$oInstance = new $sClass( $bInitCheckout );
		}
		
		return $oInstance;
	}
	
	
	//
	public function __construct( $bInitCheckout = TRUE ) {
		
		// set default prefix
		if ( NULL === $this->_sPrefix ) {
			$this->_sPrefix = sprintf( '%s_', $this->_sSlug );
		}
		
		$this->_sInstanceClass = get_class( $this );
		
		if ( $bInitCheckout ) {
			parent::__construct();
			$this->preInitCheckout();
		}
		
	}
	
	
	
	// implement by sub-class
	
	public function preInitCheckout() { }
	public function initCheckout( $total ) { }
	public function doSale() { }
	public function getResponseReasonText() { }
	public function getTransactionId() { }
	
	
	
	// accessors
	
	//
	public function populateBilling( $aBilling ) {
		if ( is_array( $aBilling ) ) {
			$this->_billing = array_merge( $this->_billing, $aBilling );
		}
		return $this;
	}
	
	//
	public function populatePayment( $aPayment ) {
		if ( is_array( $aPayment ) ) {
			$this->_payment = array_merge( $this->_payment, $aPayment );
		}
		return $this;
	}
	
	
	//
	public function setCalculation( $oCalculation ) {
		$this->_oCalculation = $oCalculation;
		return $this;
	}
	
	//
	public function getCalculation() {
		return $this->_oCalculation;
	}
	
	
	//
	public function saveOrderExtra( $fDiscount, $total, $tax, $transactionId, $status, $accountId ) {
		
		global $wpdb;
		
		$iOrderId = $this->saveOrder( $total, $tax, $transactionId, $status, $accountId );
		
		$aData = array( 'discount_amount' => $fDiscount );
		
		// track user id
		if ( Cart66Setting::getValue( 'cart_wp_user_integration' ) ) {
			
			global $user_ID;
			
			if ( $user_ID ) {
				$aData[ 'wp_user_id' ] = $user_ID;
			}
		}
		
		$wpdb->update( $wpdb->cart66_orders, $aData, array(
			'id' => $iOrderId
		) );
		
		return $iOrderId;
	}
	
	
	
	// return an array of accepted credit card types where the keys are the diplay values
	// and the values are the gateway values
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
	protected function generateExtendedData() {
		
		$b = $this->getBilling();
		$p = $this->getPayment();
		
		$billTo = array(
			'Name' => sprintf( '%s %s', $b[ 'firstName' ], $b[ 'lastName' ] ),
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
			if ( $standalone ) $output .= sprintf( '<?xml version="1.0" encoding="UTF-8"?>%s', "\n" );
			
			if ( !empty( $space ) ) {
				$output .= sprintf( '<%s xmlns="%s">%s', $name, $space, "\n" );
			} elseif( $name ) {
				$output .= sprintf( '<%s>%s', $name, "\n" );
			}
			
			$nested = 0;
		}
		
		// This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
		$ArrayNumberPrefix = 'ARRAY_NUMBER_';
		
		foreach ( $array as $root => $child ) {
			
			if ( is_array( $child ) ) {
				
				$sRootElem = ( is_string( $root ) ? $root : sprintf( '%s%s', $ArrayNumberPrefix, $root ) );
				
				$output .= sprintf( '%s  <%s>%s', str_repeat( ' ', ( 2 * $nested ) ), $sRootElem, "\n" );
				$nested++;
				$output .= self::arrayToXml( $child, NULL, NULL, NULL, FALSE, $nested );
				$nested--;
				$tag = is_string( $root ) ? $root : sprintf( '%s%s', $ArrayNumberPrefix, $root );
				$ex = explode( ' ', $tag );
				$tag = array_shift( $ex );
				$output .= sprintf( '%s  </%s>%s', str_repeat( ' ', ( 2 * $nested ) ), $tag, "\n" );
			
			} else {
				
				if ( !isset( $output ) ) { $output = ''; }
				$tag = is_string( $root ) ? $root : sprintf( '%s%s', $ArrayNumberPrefix, $root );
				$ex = explode( ' ', $tag );
				$tag = array_shift( $ex );
				
				$sRootElem = ( is_string( $root ) ? $root : sprintf( '%s%s', $ArrayNumberPrefix, $root ) );
				
				$output .= sprintf( '%s  <%s>%s</%s>%s', str_repeat( ' ', ( 2 * $nested ) ), $sRootElem, $child, $tag, "\n" );
				
			}
		}
		
		$ex = explode( ' ', $name );
		$name = array_shift( $ex );
		if ( $beginning && $name ) $output .= sprintf( '</%s>', $name );
		
		return $output;
	}
	
	//
	public function addField( $field, $value ) {
		$this->fields[ $field ] = $value;
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
		
		$out = sprintf( "%s Response Log\n", $this->_sTitle );
		
		foreach ( $this->response as $key => $value ) {
			$out .= "\t$key = $value\n";
		}
		
		Cart66Common::log( sprintf( '[%s - line %s] %s', basename( __FILE__ ), __LINE__, $out ) );
	}
	
	
	//
	protected function _logFields() {
		
		$out = sprintf( "%s Field Log\n", $this->_sTitle );
	
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
		<h3><?php echo $this->_sInstanceClass; ?>->dump_fields() Output:</h3>
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
		// about GATEWAY_CLASS's response.
		
		?>
		<h3><?php echo $this->_sInstanceClass; ?>->dump_response() Output:</h3>
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
	
	
	//// settings form manipulation methods
	
	
	// implement by sub-class
	public function settingsForm( $oDoc ) { }
	public function outputFields() { }
	public function settingsScript( $oDoc ) { }
	
	
	
	//
	public function getFormValues( $aKeys ) {
		
		$aValues = array();
		
		foreach ( $aKeys as $sKey ) {
			$sPfKey = sprintf( '%s%s', $this->_sPrefix, $sKey );
			$aValues[ $sPfKey ] = $this->getSettingValue( $sKey );
		}
		
		return $aValues;
	}
	
	//
	public function getSettingValue( $sKey ) {
		return Cart66Setting::getValue( sprintf( '%s%s', $this->_sPrefix, $sKey ) );
	}
	
	
	
}


