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
		
		if ( $oCart = $_SESSION[ 'cart66' ][ 'Cart66Cart' ] ) {
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
		
		$sCart = do_shortcode( sprintf( '[cart%s]', $sModeAtt ) );
		
		/*
		$oDoc = Geko_PhpQuery_FormTransform::createDoc( $sCart );
		
		$aTr = $oDoc->find( 'tbody > tr' );
		foreach ( $aTr as $oTr ) {
			
			$oTrPq = pq( $oTr );
			
			if ( !trim( $oTrPq->attr( 'class' ) ) ) {
				
				$aTd = $oTrPq->find( 'td' );
				
				$oProdTdPq = $aTd->eq( 0 );
				$oQtyTdPq = $aTd->eq( 1 );
				$oDescTdPq = $aTd->eq( 2 );
				$oTotalTdPq = $aTd->eq( 3 );
				
				if ( 'read' == $sMode ) {
					$iQty = intval( $oQtyTdPq->text() );				
				} else {
					$iQty = intval( $oQtyTdPq->find( 'input' )->val() );
				}
				
				$fTotal = floatval( trim( str_replace( '$', '', $oTotalTdPq->text() ) ) );
				$sProdDesc = trim( $oDescTdPq->text() );
				
				$oDescTdPq->html( '$' . ( $fTotal / $iQty ) );
				$oProdTdPq->append( sprintf( '<span>- %s</span>', $sProdDesc ) );
			}
		}
		
		echo strval( $oDoc );
		*/
		
		echo $sCart;
		
		return $this;
	}
	
	//
	public function outputCheckout( $sGateway = 'mijireh' ) {
		
		$oDoc = NULL;
		$sCheckout = do_shortcode( sprintf( '[checkout_%s]', $sGateway ) );
		
		/* if ( is_array( $aValues ) ) {
			
			if ( in_array( $sGateway, array( 'payleap', 'beanstream' ) ) ) {
				
				if ( isset( $aValues[ 'billing' ] ) ) {
					
					$aBilling = $aValues[ 'billing' ];
					
					$oDoc = $this->getDoc( $sCheckout, $oDoc );
					
					$oDoc = $this->mapValues( $oDoc, $aBilling, array(
						'first_name' => '#billing-firstName',
						'last_name' => '#billing-lastName',
						'address' => '#billing-address',
						'address2' => '#billing-address2',
						'city' => '#billing-city',
						'state' => '#billing-state',
						'zip' => '#billing-zip'
					) );
					
				}
				
			}
			
		}
		
		if ( $oDoc ) $sCheckout = strval( $oDoc ); */
		
		echo $sCheckout;
		
		return $this;
	}
	
	
	//
	public function outputReceipt() {
		
		echo do_shortcode( '[receipt]' );
	}
	
	
	
	//// helpers
	
	//
	public function getDoc( $sContent, $oDoc ) {
		
		if ( $oDoc ) return $oDoc;
		
		return Geko_PhpQuery_FormTransform::createDoc( $sContent );
	}
	
	//
	public function mapValues( $oDoc, $aValues, $aMap ) {
		
		foreach ( $aMap as $sKey => $sSelector ) {
			if ( isset( $aValues[ $sKey ] ) ) {
				$oDoc->find( $sSelector )->val(
					trim( $aValues[ $sKey ] )
				);
			}
		}
		
		return $oDoc;
	}
	
	
}



