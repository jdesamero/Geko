<?php

class Geko_Wp_Cart66 extends Geko_Singleton_Abstract
{
	
	const MSG_PLUGIN_NOT_ACTIVATED = '<strong>Warning!</strong> Please activate the Cart66 Pro Plugin!';
	
	
	protected $bCalledInit = FALSE;
	
	protected $_aBilling = array();
	protected $_aPayment = array();
	
	protected $_oCalculation = NULL;
	
	protected $_bCart66PluginActivated = FALSE;
	protected $_bIframeMode = FALSE;
	
	
	
	//
	public function init() {
		
		if ( !$this->bCalledInit ) {
			
			if ( class_exists( 'Cart66' ) ) {
				
				Geko_Wp_Db::addPrefix( 'cart66_products' );
				Geko_Wp_Db::addPrefix( 'cart66_orders' );
				Geko_Wp_Db::addPrefix( 'cart66_order_items' );
				
				add_action( 'init', array( $this, 'wpInit' ) );
				
				$this->_bCart66PluginActivated = TRUE;
			}
			
			$this->bCalledInit = TRUE;
		}
	}
	
	
	// TO DO: Make gateways configurable, add moneris
	public function wpInit() {
		
		if ( $this->_bCart66PluginActivated ) {
		
			$oScm = new Geko_Wp_Cart66_ShortcodeManager();
			add_shortcode( 'checkout_beanstream', array( $oScm, 'beanstreamCheckout' ) );
			
			if ( is_admin() ) {
				
				$oBsGw = Geko_Wp_Cart66_Gateway_Beanstream::getInstance( FALSE );
	
				add_action( 'admin_cart66_settings_checkout_form_pq', array( $this, 'doCheckoutTab' ) );
				
				add_action( 'admin_cart66_settings_gateways_form_pq', array( $oBsGw, 'settingsForm' ) );
				add_action( 'admin_cart66_settings_gateways_script_pq', array( $oBsGw, 'settingsForm' ) );
				
			}
			
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
	
	
	
	//
	public function setIframeMode( $bIframeMode ) {
		$this->_bIframeMode = $bIframeMode;
		return $this;
	}
	
	//
	public function getIframeMode() {
		return $this->_bIframeMode;
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
		
		if ( $this->_bCart66PluginActivated ) {
			
			if ( $oCart = Cart66Session::get( 'Cart66Cart' ) ) {
				$aCartItems = $oCart->getItems();
				foreach ( $aCartItems as $oItem ) {
					$iTotal += intval( $oItem->getQuantity() );
				}
			}
			
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $iTotal;
	}
	
	
	//
	public function outputCart( $sMode = '' ) {
		
		if ( $this->_bCart66PluginActivated ) {
		
			$sModeAtt = '';
			
			if ( $sMode ) {
				$sModeAtt = sprintf( ' mode="%s"', $sMode );
			}
			
			echo do_shortcode( sprintf( '[cart%s]', $sModeAtt ) );
			
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $this;
	}
	
	//
	public function outputCheckout( $sGateway = 'mijireh' ) {
		
		if ( $this->_bCart66PluginActivated ) {
			echo do_shortcode( sprintf( '[checkout_%s]', $sGateway ) );
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $this;
	}
	
	
	//
	public function outputReceipt() {
		
		if ( $this->_bCart66PluginActivated ) {
			echo do_shortcode( '[receipt]' );
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $this;
	}
	
	
	//// settings
	
	public function doCheckoutTab( $oDoc ) {
		
		$oTable1 = $oDoc->find( '#cc-cart_checkout table.form-table' );
		
		$oTable1->find( 'tbody' )->append(
			Geko_String::fromOb( array( $this, 'outputCheckoutFields' ), array( 'checkout' ) )
		);
		
		// populate form values
		$oTable1 = Geko_Html::populateForm( $oTable1, $this->getFormValues( array(
			'cart_wp_user_integration',
			'cart_wp_user_terms_checkbox',
			'cart_wp_user_terms_verbiage'
		) ), TRUE );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$oTable2 = $oDoc->find( '#cc-terms_of_service table.form-table' );
		
		$oTable2->find( 'tbody' )->append(
			Geko_String::fromOb( array( $this, 'outputCheckoutFields' ), array( 'terms' ) )
		);
		
		// populate form values
		$oTable2 = Geko_Html::populateForm( $oTable2, $this->getFormValues( array(
			'cart_terms_agree_checkbox',
			'cart_terms_verbiage'
		) ), TRUE );
		
		return $oDoc;
	}
	
	//
	public function outputCheckoutFields( $sSection ) {
		
		if ( 'checkout' == $sSection ): ?>
			<tr valign="top">
				<th scope="row">Enable Wordpress User Account Integration</th>
				<td>
					<input type="radio" value="1" id="cart_wp_user_integration_yes" name="cart_wp_user_integration" />
					<label for="cart_wp_user_integration_yes">Yes</label>
					<input type="radio" value="" id="cart_wp_user_integration_no" name="cart_wp_user_integration" />
					<label for="cart_wp_user_integration_no">No</label>
					<p class="description">Use this option to tie in user accounts to Worpress users.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Enable WP User Account "Agree to Terms" Checkbox</th>
				<td>
					<input type="radio" value="1" id="cart_wp_user_terms_checkbox_yes" name="cart_wp_user_terms_checkbox" />
					<label for="cart_wp_user_terms_checkbox">Yes</label>
					<input type="radio" value="" id="cart_wp_user_terms_checkbox_no" name="cart_wp_user_terms_checkbox" />
					<label for="cart_wp_user_terms_checkbox_no">No</label>
					<p class="description">Enable an agree to terms checkbox for account creation.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">WP User Account "Agree to Terms" Verbiage</th>
				<td>
					<input type="text" value="" id="cart_wp_user_terms_verbiage" name="cart_wp_user_terms_verbiage" class="regular-text" />
					<p class="description">Text that will go beside the "Agree to Terms" checkbox.</p>
				</td>
			</tr>
		<?php elseif ( 'terms' == $sSection ): ?>
			<tr valign="top">
				<th scope="row">Enable "Agree To Terms" Checkbox</th>
				<td>
					<input type="radio" value="1" id="cart_terms_agree_checkbox_yes" name="cart_terms_agree_checkbox" />
					<label for="cart_terms_agree_checkbox_yes">Yes</label>
					<input type="radio" value="" id="cart_terms_agree_checkbox_no" name="cart_terms_agree_checkbox" />
					<label for="cart_terms_agree_checkbox_no">No</label>
					<p class="description">Enable an "Agree to Terms" checkbox when completing a purchase.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">"Agree to Terms" Verbiage</th>
				<td>
					<input type="text" value="" id="cart_terms_verbiage" name="cart_terms_verbiage" class="regular-text" />
					<p class="description">Text that will go beside the "Agree to Terms" checkbox.</p>
				</td>
			</tr>
		<?php endif;
	}
	
	
	//
	public function getFormValues( $aKeys ) {
		
		$aValues = array();
		
		foreach ( $aKeys as $sKey ) {
			$aValues[ $sKey ] = Cart66Setting::getValue( $sKey );
		}
		
		return $aValues;
	}
	
	
	
}



