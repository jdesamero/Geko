<?php

//
class Geko_Wp_Ext_WooCommerce_Gateway_MiraPayHosted extends WC_Payment_Gateway
{
	
	protected $_sStagingGatwayUrl = 'https://staging.eigendev.com/mirapay/secure_credit.php';
	protected $_sLiveGatwayUrl = 'https://www3.eigendev.com/mirapay/secure_credit.php';
	
	
	
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
		
		add_action( sprintf( 'woocommerce_update_options_payment_gateways_%s', $this->id ), array( $this, 'process_admin_options' ) );
		
		$this->handleMiraPayResponse();
		
	}
	
	
	
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
				'title' => __( 'Staging Password Hash', 'woocommerce' ),
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
				'title' => __( 'Live Password Hash', 'woocommerce' ),
				'type' => 'text',
				'default' => ''
			),
			
			
			
			'description' => array(
				'title' => __( 'Customer Message', 'woocommerce' ),
				'type' => 'textarea',
				'default' => ''
			)
			
		);
		
	}
	
	//
	public function process_payment( $iOrderId ) {
		
		$oOrder = new WC_Order( $iOrderId );
		
		$sMode = $this->get_option( 'mode' );
		$sPrefix = sprintf( '%s_', $sMode );
		
		$sLogin = $this->get_option( sprintf( '%slogin', $sPrefix ) );
		$sMerchantId = $this->get_option( sprintf( '%smerchant_id', $sPrefix ) );
		$sPassword = $this->get_option( sprintf( '%spassword', $sPrefix ) );
		
		
		
		$sAmount = number_format( $oOrder->get_total() - round( $oOrder->get_total_shipping() + $oOrder->get_shipping_tax(), 2 ) + $oOrder->get_order_discount(), 2, '.', '' );
		
		$sMkey = md5( sprintf( '%s%s%s', $sLogin, $sAmount, $sPassword ) );
		
		
		// set-up the success and failure url
		
		$oReturnUrl = new Geko_Uri( sprintf( '%s/store/checkout/', Geko_Wp::getUrl() ) );
		$oReturnUrl
			->setVar( 'status', 'success' )
			->setVar( 'oid', $iOrderId )
		;
		
		$sSuccessUrl = strval( $oReturnUrl );
		
		$oReturnUrl->setVar( 'status', 'fail' );
		
		$sFailUrl = strval( $oReturnUrl );
		
		
		// set-up the gateway url
		
		$sGatewayUrl = ( 'live' == $sMode ) ? $this->_sLiveGatwayUrl : $this->_sStagingGatwayUrl ;
		
		$oGatewayUrl = new Geko_Uri( $sGatewayUrl );
		$oGatewayUrl
			->setVar( 'MTID', $sLogin )
			->setVar( 'Merchant_ID', $sMerchantId )
			->setVar( 'MKEY', $sMkey )
			->setVar( 'SuccessURL', $sSuccessUrl )
			->setVar( 'FailURL', $sFailUrl )
			->setVar( 'EMail', get_bloginfo( 'admin_email' ) )
			->setVar( 'Amount1', $sAmount )
		;
		
		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => strval( $oGatewayUrl )
		);
	}
	
	
	//
	public function handleMiraPayResponse() {
		
		$iOrderId = $_GET[ 'oid' ];
		$sMkey = $_GET[ 'MKEY' ];
		
		if ( $iOrderId && $sMkey ) {
			
			global $woocommerce;
			
			$oOrder = new WC_Order( $iOrderId );
			
			// Mark as on-hold (we're awaiting the cheque)
			$oOrder->update_status( 'complete', __( 'MiraPay Hosted Payment was Completed', 'woocommerce' ) );
			
			// Reduce stock levels
			$oOrder->reduce_order_stock();
		
			// Remove cart
			$woocommerce->cart->empty_cart();
			
			header( sprintf( 'Location: %s', $this->get_return_url( $oOrder ) ) );
			die();
		}
		
	}
	
	
}



