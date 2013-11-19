<?php

class Geko_Wp_Cart66 extends Geko_Singleton_Abstract
{
	
	protected $bCalledInit = FALSE;
	
	protected $_aBilling = array();
	protected $_aPayment = array();
	
	protected $_oCalculation = NULL;
	
	
	
	//
	public function init() {
		
		if ( !$this->bCalledInit ) {
			
			Geko_Wp_Db::addPrefix( 'cart66_products' );
			Geko_Wp_Db::addPrefix( 'cart66_orders' );
			Geko_Wp_Db::addPrefix( 'cart66_order_items' );
			
			add_action( 'init', array( $this, 'wpInit' ) );
			
			$this->bCalledInit = TRUE;
		}
	}
	
	
	//
	public function wpInit() {
		
		$oScm = new Geko_Wp_Cart66_ShortcodeManager();
		add_shortcode( 'checkout_beanstream', array( $oScm, 'beanstreamCheckout' ) );
		
		if ( is_admin() ) {
			
			$oBsGw = Geko_Wp_Cart66_Gateway_Beanstream::getInstance( FALSE );
			
			add_filter( 'admin_cart66_settings_gateways_form_pq', array( $oBsGw, 'settingsForm' ) );
			add_filter( 'admin_cart66_settings_gateways_script_pq', array( $oBsGw, 'settingsForm' ) );
			
		}
	}
	
	
	
	//// accessors
	
	//
	public function setBilling( $aBilling ) {
		if ( is_array( $aBilling ) ) {
			$this->_aBilling = array_merge( $this->_aBilling, $aBilling );
		}
		return $this;
	}
	
	//
	public function setPayment( $aPayment ) {
		if ( is_array( $aPayment ) ) {
			$this->_aPayment = array_merge( $this->_aPayment, $aPayment );
		}
		return $this;
	}
	
	//
	public function getBilling() {
		return $this->_aBilling;
	}
	
	//
	public function getPayment() {
		return $this->_aPayment;
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
	
	
	
	//// db
	
	//
	public function getProductGroupHash() {
		
		global $wpdb;
		
		$sQuery = sprintf(
			'SELECT DISTINCT p.name, MD5( p.name ) AS name_key FROM %s p ORDER BY p.name ASC',
			$wpdb->cart66_products
		);
		
		$aResFmt = array();
		$aRes = $wpdb->get_results( $sQuery, ARRAY_A );
		
		foreach ( $aRes as $aRow ) {
			$aResFmt[ $aRow[ 'name_key' ] ] = $aRow[ 'name' ];
		}
		
		return $aResFmt;
	}
	
	
	//
	public function getProductVarieties( $sProdGroupKey ) {
		
		global $wpdb;
		
		$sQuery = sprintf(
			"SELECT p.id, p.name, p.item_number, p.price_description, p.price FROM %s p WHERE MD5( p.name ) = '%s'",
			$wpdb->cart66_products,
			addslashes( $sProdGroupKey )
		);
		
		return $wpdb->get_results( $sQuery, ARRAY_A );
	}
	
	//
	public function getProductVarietySelectHtml( $sProdGroupKey, $aAtts, $mValue = NULL, $aParams = array() ) {
		
		if ( !isset( $aParams[ 'empty_choice' ] ) ) {
			$aParams[ 'empty_choice' ] = '- Select -';
		}
		
		$aProdVars = $this->getProductVarieties( $sProdGroupKey );
		
		if ( is_array( $aProdVars ) ) {
			
			foreach ( $aProdVars as $aRow ) {
				$aParams[ 'choices' ][ $aRow[ 'id' ] ] = array(
					'atts' => array( 'data-prodname' => $aRow[ 'name' ] ),
					'label' => sprintf( '%s $%s', $aRow[ 'price_description' ], $aRow[ 'price' ] )
				);
			}
			
			$oWidget = Geko_Html_Widget::create( 'select', $aAtts, $mValue, $aParams );
			return strval( $oWidget->get() );
		}
		
		return NULL;
	}
	
	
	//
	public function getCartNumPieces() {
		
		$iTotal = 0;
		
		if ( $oCart = Cart66Session::get( 'Cart66Cart' ) ) {
			$aCartItems = $oCart->getItems();
			foreach ( $aCartItems as $oItem ) {
				$iTotal += intval( $oItem->getQuantity() );
			}
		}
		
		return $iTotal;
	}
	
	
	//
	public function outputCart( $sMode = '' ) {
		
		$sModeAtt = '';
		
		if ( $sMode ) {
			$sModeAtt = sprintf( ' mode="%s"', $sMode );
		}
		
		echo do_shortcode( sprintf( '[cart%s]', $sModeAtt ) );
		
		return $this;
	}
	
	//
	public function outputCheckout( $sGateway = 'mijireh' ) {
		
		echo do_shortcode( sprintf( '[checkout_%s]', $sGateway ) );
		
		return $this;
	}
	
	
	//
	public function outputReceipt() {
		
		echo do_shortcode( '[receipt]' );
		
		return $this;
	}
	
	
	
	
}



