<?php

//
class Geko_Wp_Ext_WooCommerce_Gateway_MiraPayHosted extends WC_Payment_Gateway
{
	
	protected $_sStagingGatwayUrl = 'https://staging.eigendev.com/mirapay/secure_credit.php';
	protected $_sLiveGatwayUrl = 'https://www3.eigendev.com/mirapay/secure_credit.php';
	
	protected $_sPreTransHashKey = 'mirapay_pre_transaction_hash';
	protected $_sPreCheckKey = 'mkeycheck';
	protected $_sPostTransHashKey = 'mirapay_post_transaction_hash';
	protected $_sMiraResponseKey = 'mirapay_response';
	protected $_sMiraIdKey = 'mirapay_transaction_id';
	protected $_sMiraStatusKey = 'mirapay_status';
	
	protected $_aNotices = array(
		'failed' => 'Your payment failed!',
		'cancelled' => 'You cancelled your payment!',
		'key_mismatch' => 'Transaction key mismatch! Please send an email to %s for assistance!',
		'unknown' => 'An unknown Mirapay status was sent ("%s"). Please send an email to %s and report the error.'
	);
	
	protected $_sMode = '';
	
	
	
	
	//
	public function __construct() {
		
		$this->id = 'geko_mirapay_hosted';
		$this->icon = '';
		$this->has_fields = FALSE;
		$this->method_title = 'MiraPay Hosted';
		$this->method_description = 'MiraPay Hosted empowers merchants to quickly and easily accept payments online through their web site.';
		
		$this->init_form_fields();
		$this->init_settings();
		
		$this->title = $this->get_option( 'title' );
		$this->_sMode = $this->get_option( 'mode' );
		
		add_action( sprintf( 'woocommerce_update_options_payment_gateways_%s', $this->id ), array( $this, 'process_admin_options' ) );
		
		$this->handleMiraPayResponse();
		$this->sendNotices();
		
	}
	
	
	//// accessors
	
	//
	public function getModePrefix() {
		return sprintf( '%s_', $this->_sMode );
	}
	
	// apply mode prefix
	public function getModeOption( $sKey ) {
		
		$sPrefix = $this->getModePrefix();
		
		return $this->get_option( sprintf( '%s%s', $sPrefix, $sKey ) );
	}
	
	//
	public function getOrderAmount( $oOrder ) {
		
		return number_format( $oOrder->get_total() - round( $oOrder->get_total_shipping() + $oOrder->get_shipping_tax(), 2 ) + $oOrder->get_order_discount(), 2, '.', '' );
	}
	
	
	
	//// main methods
	
	//
	public function init_form_fields() {
		
		$this->form_fields = array(
			
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'woocommerce' ),
				'type' => 'checkbox',
				'label' => __( 'Enable MiraPay Hosted Payment', 'woocommerce' ),
				'default' => 'yes'
			),
			
			'title' => array(
				'title' => __( 'Title', 'woocommerce' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default' => __( 'MiraPay Hosted Payment', 'woocommerce' ),
				'desc_tip' => TRUE
			),
			
			'mode' => array(
				'title' => __( 'Mode', 'woocommerce' ),
				'type' => 'select',
				'options' => array(
					'staging' => __( 'Staging', 'woocommerce' ),
					'live' => __( 'Live', 'woocommerce' )
				),
				'default' => 'staging'
			),
			
			
			
			'staging_login' => array(
				'title' => __( 'Staging Login', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			'staging_merchant_id' => array(
				'title' => __( 'Staging Merchant ID', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			'staging_password' => array(
				'title' => __( 'Staging Password', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			'staging_order_prefix' => array(
				'title' => __( 'Staging Order Prefix', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			
			
			'live_login' => array(
				'title' => __( 'Live Login', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			'live_merchant_id' => array(
				'title' => __( 'Live Merchant ID', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			'live_password' => array(
				'title' => __( 'Live Password', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			'live_order_prefix' => array(
				'title' => __( 'Live Order Prefix', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			/* 'description' => array(
				'title' => __( 'Customer Message', 'woocommerce' ),
				'type' => 'textarea',
				'default' => ''
			) */
			
			'support_email' => array(
				'title' => __( 'Support Email', 'woocommerce' ),
				'type' => 'text',
				'description' => __( 'Email used when displaying a "please contact admin" error.', 'woocommerce' ),
				'default' => get_bloginfo( 'admin_email' ),
				'desc_tip' => TRUE
			),
			
			'log_file' => array(
				'title' => __( 'Log File', 'woocommerce' ),
				'type' => 'text',
				'description' => __( 'Path to log file where transactions are logged. If it does not exist, nothing is logged.', 'woocommerce' ),
				'default' => '',
				'desc_tip' => TRUE
			)
			
			
		);
		
	}
	
	//
	public function process_payment( $iOrderId ) {
		
		global $woocommerce;
		
		
		$oOrder = new WC_Order( $iOrderId );
		$oCustomer = $oOrder->get_user();
		
		$sMerchantId = $this->getModeOption( 'merchant_id' );
		$sPassword = $this->getModeOption( 'password' );
		$sOrderPrefix = $this->getModeOption( 'order_prefix' );
		
		$sCustomerEmail = $oCustomer->user_email;
		$sMtId = sprintf( '%s%d', $sOrderPrefix, $iOrderId );
		
		$sAmount = $this->getOrderAmount( $oOrder );
		
		$sMkey = md5( sprintf( '%s%s%s', $sMtId, $sAmount, $sPassword ) );
		
		// track the mkey above, and assign as order meta data
		
		update_post_meta( $iOrderId, $this->_sPreTransHashKey, $sMkey );
		
		// set-up the return URL
		// SuccessURL and FailURL is the same, use "Response" query string value to handle error situation
		$oReturnUrl = new Geko_Uri( $woocommerce->cart->get_checkout_url() );
		$oReturnUrl->setVar( $this->_sPreCheckKey, $sMkey );
		
		$sReturnUrl = strval( $oReturnUrl );
		
		
		// set-up the gateway url
		
		$sGatewayUrl = ( 'live' == $this->_sMode ) ? $this->_sLiveGatwayUrl : $this->_sStagingGatwayUrl ;
		
		$oGatewayUrl = new Geko_Uri( $sGatewayUrl );
		$oGatewayUrl
			->setVar( 'MTID', $sMtId )
			->setVar( 'Merchant_ID', $sMerchantId )
			->setVar( 'MKEY', $sMkey )
			->setVar( 'SuccessURL', $sReturnUrl )
			->setVar( 'FailURL', $sReturnUrl )
			->setVar( 'EMail', $sCustomerEmail )
			->setVar( 'Amount1', $sAmount )
		;
		
		
		//// log request
		
		$sData = sprintf(
			"MTID: %s\nMerchant_ID: %s\nMKEY: %s\nSuccessURL: %s\nFailURL: %s\nEMail: %s\nAmount1: %s",
			$sMtId, $sMerchantId, $sMkey, $sReturnUrl, $sReturnUrl, $sCustomerEmail, $sAmount
		);
		
		$this->logToFile( 'Request', $sData );
		
		
		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => strval( $oGatewayUrl )
		);
	}
	
	
	//
	public function handleMiraPayResponse() {
		
		global $woocommerce;
		
		
		$sMkeyCheck = trim( $_GET[ $this->_sPreCheckKey ] );
		$sPostMkey = trim( $_GET[ 'MKEY' ] );		// this is the new MKEY
		$sMtId = trim( $_GET[ 'MTID' ] );
		$sMiraId = trim( $_GET[ 'MiraID' ] );
		$sResponse = trim( $_GET[ 'Response' ] );
		
		
		//// set-up logging data
		
		$sScriptStatus = '';
		
		$sData = sprintf(
			"MKEY Check: %s\nPost MKEY: %s\nMTID: %s\nMiraID: %s\nResponse: %s",
			$sMkeyCheck, $sPostMkey, $sMtId, $sMiraId, $sResponse
		);
		
		
		$oRedirectUrl = NULL;
		
		//
		if ( $sMkeyCheck && $sPostMkey && $sMtId && $sMiraId && $sResponse ) {
			
			// checkout page is the default redirect URL
			$oRedirectUrl = new Geko_Uri( $woocommerce->cart->get_checkout_url() );
			
			
			// obtain the order id
			// strip prefix from mtid
			
			$sOrderPrefix = $this->getModeOption( 'order_prefix' );
			$sMerchantId = $this->getModeOption( 'merchant_id' );
			$sPassword = $this->getModeOption( 'password' );
			
			$iOrderId = intval( substr( $sMtId, strlen( $sOrderPrefix ) ) );
			
			// validate the order
			$oOrder = new WC_Order( $iOrderId );
			
			$sAmount = $this->getOrderAmount( $oOrder );
			
			// get the stored mkey (pre)
			$sStoredMKey = get_post_meta( $iOrderId, $this->_sPreTransHashKey, TRUE );
			
			// calculate the new mkey (post)
			// MTID, Amount1, MiraID, Response and the Merchant's Password
			$sPostMkeyCheck = md5( sprintf( '%s%s%s%s%s', $sMtId, $sAmount, $sMiraId, $sResponse, $sPassword ) );
			
			
			if (
				( $sPostMkey == $sPostMkeyCheck ) && 
				( $sStoredMKey == $sMkeyCheck )
			) {
				
				// stored mkey and given mkey is a match, store transaction details
				
				update_post_meta( $iOrderId, $this->_sPostTransHashKey, $sPostMkey );
				update_post_meta( $iOrderId, $this->_sMiraResponseKey, $sResponse );
				update_post_meta( $iOrderId, $this->_sMiraIdKey, $sMiraId );
				
				
				// handle responses accordingly
				
				if ( 'APPROVED' == $sResponse ) {
				
					// Mark as complete
					$oOrder->update_status( 'complete', __( 'MiraPay Hosted Payment was Completed', 'woocommerce' ) );
					
					// Reduce stock levels
					$oOrder->reduce_order_stock();
				
					// Remove cart
					$woocommerce->cart->empty_cart();
					
					// redirect to order complete page
					$oRedirectUrl = new Geko_Uri( $this->get_return_url( $oOrder ) );
									
				} else {
					
					// log the failure
					$sOrderStatus = '';
					$sOrderStatusMsg = '';
					
					if ( 'DECLINED' == $sResponse ) {
						
						$sOrderStatus = 'failed';
						$sOrderStatusMsg = 'MiraPay Hosted Payment was Declined';
						
					} elseif ( 'CANCELED' == $sResponse ) {
						
						$sOrderStatus = 'cancelled';
						$sOrderStatusMsg = 'MiraPay Hosted Payment was Cancelled';
					
					}
					
					// update order status
					if ( $sOrderStatus && $sOrderStatusMsg ) {
						$oOrder->update_status( $sOrderStatus, __( $sOrderStatusMsg, 'woocommerce' ) );
						$oRedirectUrl->setVar( $this->_sMiraStatusKey, $sOrderStatus );
					}
					
				}
				
				$sScriptStatus = 'Has Response';
				
			} else {
				
				// mkey mismatch
				$oRedirectUrl->setVar( $this->_sMiraStatusKey, 'key_mismatch' );
				
				$sScriptStatus = 'MKEY Mismatch';
			}
			
		}
		
		
		// only log stuff if script status was set
		if ( $sScriptStatus ) {
			$sData .= sprintf( "\nScript Status: %s", $sScriptStatus );
			$this->logToFile( 'Response', $sData );
		}
		
		
		// redirect, if needed
		if ( $oRedirectUrl ) {
			
			header( sprintf( 'Location: %s', strval( $oRedirectUrl ) ) );
			die();
		}
		
	}
	
	
	//
	public function sendNotices() {
		
		if ( $sStatusKey = trim( $_GET[ $this->_sMiraStatusKey ] ) ) {
			
			$sNoticeMsg = $this->_aNotices[ $sStatusKey ];
			$sSupportEmail = $this->get_option( 'support_email' );
			
			$aEmailTag = sprintf( '<a href="mailto:%s">%s</a>', $sSupportEmail, $sSupportEmail );
			
			if ( 'key_mismatch' == $sStatusKey ) {
				$sNoticeMsg = sprintf( $sNoticeMsg, $aEmailTag );
			}
			
			if ( !$sNoticeMsg ) {
				$sNoticeMsg = sprintf( $this->_aNotices[ 'unknown' ], $sStatusKey, $aEmailTag );
			}
			
			wc_add_notice( sprintf( '%s %s', __( 'Payment error:', 'woothemes' ), $sNoticeMsg ), 'error' );
		}

	
	}
	
	
	//
	public function logToFile( $sTitle, $sData ) {
		
		$sLogFile = $this->get_option( 'log_file' );
		
		if ( $sLogFile && is_file( $sLogFile ) ) {
			
			$sOut = "==========================\n\n";
			
			$sOut .= sprintf( "Title: %s\n", $sTitle );
			$sOut .= sprintf( "Date: %s\n", date( 'D, j M Y H:i:s' ) );
			$sOut .= sprintf( "Mode: %s\n", $this->_sMode );
			$sOut .= sprintf( "Remote IP: %s\n", $_SERVER[ 'REMOTE_ADDR' ] );
			$sOut .= sprintf( "User Agent: %s\n\n", $_SERVER[ 'HTTP_USER_AGENT' ] );
			
			$sOut .= $sData;
			
			$sOut .= "\n\n==========================\n\n";
			
			file_put_contents( $sLogFile, $sOut, FILE_APPEND );
		}
		
		return $this;
	}
	
	
	
}



