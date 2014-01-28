<?php

//
class Geko_Wp_Ext_Cart66 extends Geko_Singleton_Abstract
{
	
	const MSG_PLUGIN_NOT_ACTIVATED = '<strong>Warning!</strong> Please activate the Cart66 Pro Plugin!';
	
	
	protected $_iNumPieces = NULL;
	protected $_iNumOrders = NULL;
	protected $_aBilling = array();
	protected $_aPayment = array();
	
	protected $_oCalculation = NULL;
	
	protected $_bCart66PluginActivated = FALSE;
	protected $_bIframeMode = FALSE;
	
	protected $_iUserId = NULL;
	protected $_bHistoryPaginate = FALSE;
	protected $_iHistoryItemsPerPage = NULL;
	
	protected $_aLabelOverrides = array();
	
	
	
	
	//
	public function start() {
		
		parent::start();
		
		if ( class_exists( 'Cart66' ) ) {
			
			Geko_Wp_Db::addPrefix( 'cart66_products' );
			Geko_Wp_Db::addPrefix( 'cart66_orders' );
			Geko_Wp_Db::addPrefix( 'cart66_order_items' );
			
			add_action( 'init', array( $this, 'wpInit' ) );
			
			add_action( 'template_redirect', array( $this, 'ajaxTriggerCheck' ), 9 );
			
			$this->adminRedirect();
			
			$this->_bCart66PluginActivated = TRUE;
		}
	}
	
	
	//
	public function adminRedirect() {
		if (
			( is_admin() ) && 
			( 'cart66_admin' == $_GET[ 'page' ] ) && 
			( 'view' == $_GET[ 'task' ] ) && 
			( $sOuid = trim( $_GET[ 'ouid' ] ) )
		) {
			
			global $wpdb;
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'o.id' )
				->from( $wpdb->cart66_orders, 'o' )
				->where( 'o.ouid = ?', $sOuid )
			;
			
			$iId = intval( $wpdb->get_var( strval( $oQuery ) ) );
			
			$sHeader = sprintf(
				'Location: %s/wp-admin/admin.php?page=cart66_admin&task=view&id=%d',
				get_bloginfo( 'url' ),
				$iId
			);
			
			header( $sHeader );
			die();
		}
	}
	
	
	// TO DO: Make gateways configurable, add moneris
	public function wpInit() {
		
		if ( $this->_bCart66PluginActivated ) {
		
			$oScm = new Geko_Wp_Ext_Cart66_ShortcodeManager();
			add_shortcode( 'checkout_beanstream', array( $oScm, 'beanstreamCheckout' ) );
			
			if ( is_admin() ) {
				
				$oBsGw = Geko_Wp_Ext_Cart66_Gateway_Beanstream::getInstance( FALSE );
				
				add_action( 'admin_cart66_settings_checkout_form_pq', array( $this, 'doCheckoutTab' ) );
				
				add_action( 'admin_cart66_settings_notifications_form_pq', array( $this, 'doNotificationsTab' ) );
				
				add_action( 'admin_cart66_settings_gateways_form_pq', array( $oBsGw, 'settingsForm' ) );
				add_action( 'admin_cart66_settings_gateways_script_pq', array( $oBsGw, 'settingsForm' ) );
				
			}
			
		}
	}
	
	
	
	//// accessors
	
	//
	public function setUserId( $iUserId ) {
		$this->_iUserId = $iUserId;
		return $this;
	}
	
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
	
	
	//
	public function setLabelOverride() {
		
		$aArgs = func_get_args();
		
		if ( is_array( $aValues = $aArgs[ 0 ] ) ) {
			$this->_aLabelOverrides = array_merge( $this->_aLabelOverrides, $aValues );
		} else {
			list( $sKey, $sValue ) = $aArgs;
			$this->_aLabelOverrides[ $sKey ] = $sValue;
		}
		
		return $this;
	}
	
	//
	public function getLabelOverride( $sKey, $sDefaultVal ) {
		
		if ( $sValue = $this->_aLabelOverrides[ $sKey ] ) {
			return __( $sValue, 'cart66' );
		}
		
		return $sDefaultVal;
	}
	
	
	//
	public function setOrderHistoryPagination( $bPaginate = TRUE, $iItemsPerPage = 15 ) {
		
		$this->_bHistoryPaginate = $bPaginate;
		$this->_iHistoryItemsPerPage = $iItemsPerPage;
		
		if ( $bPaginate && ( $iItemsPerPage > 0 ) ) {
			
			global $wp_query;
			
			$iNumOrders = $this->getNumOrders();
			
			$wp_query->found_posts = $iNumOrders;
			$wp_query->max_num_pages = ceil( $iNumOrders / $iItemsPerPage );		
		}
		
		return $this;
	}
	
	
	
	
	//// db
	
	//
	public function getProductGroupHash() {
		
		global $wpdb;
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->distinct( TRUE )
			->field( 'p.name' )
			->field( 'MD5( p.name )', 'name_key' )
			->from( $wpdb->cart66_products, 'p' )
			->order( 'p.name', 'ASC' )
		;
		
		$aResFmt = array();
		$aRes = $wpdb->get_results( strval( $oQuery ), ARRAY_A );
		
		foreach ( $aRes as $aRow ) {
			$aResFmt[ $aRow[ 'name_key' ] ] = $aRow[ 'name' ];
		}
		
		return $aResFmt;
	}
	
	
	//
	public function getProductVarieties( $sProdGroupKey ) {
		
		global $wpdb;
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'p.id' )
			->field( 'p.name' )
			->field( 'p.item_number' )
			->field( 'p.price_description' )
			->field( 'p.price' )
			->from( $wpdb->cart66_products, 'p' )
			->where( 'MD5( p.name ) = ?', $sProdGroupKey )
		;
		
		return $wpdb->get_results( strval( $oQuery ), ARRAY_A );
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
	public function getCartNumPieces( $bUseCache = TRUE ) {
		
		if ( $this->_bCart66PluginActivated ) {
			
			if (
				( $oCart = Cart66Session::get( 'Cart66Cart' ) ) && 
				(
					( NULL === $this->_iNumPieces ) || 
					( ( NULL !== $this->_iNumPieces ) && ( !$bUseCache ) )
				)
			) {
				
				$aCartItems = $oCart->getItems();
				
				$iTotal = 0;
				foreach ( $aCartItems as $oItem ) {
					$iTotal += intval( $oItem->getQuantity() );
				}
				
				$this->_iNumPieces = $iTotal;
			}
			
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $this->_iNumPieces;
	}
	
	
	//
	public function getNumOrders( $bUseCache = TRUE ) {
		
		if ( $this->_bCart66PluginActivated ) {
			
			if (
				( $iUserId = $this->_iUserId ) && 
				(
					( NULL === $this->_iNumOrders ) || 
					( ( NULL !== $this->_iNumOrders ) && ( !$bUseCache ) )
				)
			) {
				
				global $wpdb;
				
				$oQuery = new Geko_Sql_Select();
				$oQuery
					->field( 'COUNT(*)', 'num' )
					->from( $wpdb->cart66_orders, 'o' )
					->where( 'o.wp_user_id = ?', $iUserId )
				;
				
				$this->_iNumOrders = intval( $wpdb->get_var( strval( $oQuery ) ) );
			}
			
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $this->_iNumOrders;		
	}
	
	
	
	//
	public function outputAddToCart( $aProd, $aParams = array() ) {
	
		if ( $this->_bCart66PluginActivated ) {
			
			if ( $iItemNum = $aProd[ 'item_number' ] ) {
				
				//// TYPE 1 "Add to Cart" Widget, for single product
				
				// default attributes
				$aAtts = array(
					'showprice' => 'yes',
					'quantity' => 'user:1',
					'ajax' => 'yes',
					'item' => $iItemNum
				);
				
				// override atts
				$aAtts = array_merge( $aAtts, $aParams );
				
				$sRes = do_shortcode( sprintf( '[add_to_cart %s]', $this->formatAtts( $aAtts ) ) );
				echo preg_replace(
					'/(<span class="Cart66Price Cart66PriceDescription">)(.+?)(<\/span>)/',
					sprintf( '$1 %s - \$%s $3', $aProd[ 'price_description' ], $aProd[ 'price' ] ),
					$sRes
				);
				
			} elseif ( $sProdGroupKey = $aProd[ 'product_group_key' ] ) {
			
				//// TYPE 2 "Add to Cart" Widget, select from related product variations
				
				// product variations
				$sProdVarSelect = $this->getProductVarietySelectHtml( $sProdGroupKey );
				$iPostId = $aProd[ 'post_id' ];
				
				if ( $sProdVarSelect ): ?>
					<a id="tl_<?php echo $iPostId; ?>" class="Cart66AddToCart toggle-link"><img src="<?php bloginfo( 'template_directory' ); ?>/images/spacer.gif" /></a>
					<div id="tc_<?php echo $iPostId; ?>" class="toggle-content">
						<div class="inner">
							<h2>Buy this Product</h2>
							<?php echo $sProdVarSelect; ?>
							<span class="Cart66UserQuantity">
								<label for="Cart66UserQuantityInput_1">Qty: </label>
								<input id="Cart66UserQuantityInput_1" size="4" value="1" name="item_quantity" type="text" />
							</span>
							<div id="addToCart_1" class="Cart66ButtonPrimary prodlist" name="addToCart_1" value="Add to Cart">Add to Cart</div>
							<div class="clear"></div>
							<a class="toggle-close">Close</a><div class="clear"></div>
						</div>
					</div><?php
				endif;
				
			}
			
		} else {
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		}
		
		return $this;	
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


	//
	public function outputHistory( $iPage = NULL ) {
		
		if ( $this->_bCart66PluginActivated && $this->_iUserId ):
			
			global $wpdb;
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'o.ouid' )
				->field( 'o.ordered_on' )
				->field( 'o.trans_id' )
				->field( 'o.total' )
				->field( 'o.status' )
				->from( $wpdb->cart66_orders, 'o' )
				->where( 'o.wp_user_id = ?', $this->_iUserId )
				->order( 'o.ordered_on', 'DESC' )
			;
			
			if ( $this->_bHistoryPaginate ) {
				
				$iItemsPerPage = $this->_iHistoryItemsPerPage;
				$iOffset = (
					( ( $iPage ) ? ( intval( $iPage ) - 1 ) : 0 ) *
					$iItemsPerPage
				);
				$oQuery->limitOffset( $iItemsPerPage, $iOffset );

			}
			
			$results = $wpdb->get_results( strval( $oQuery ) );
			
			?>
			<table id="viewCartTable" class="order-history">
				<thead>
					<tr>
						<th class="ordernum">Order Number</th>
						<th class="date">Date</th>
						<th class="total">Total</th>
						<th class="orderstat">Order Status</th>
						<th class="receipt">Receipt</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $results as $order ):
						
						$sReceiptUrl = sprintf( '%s/store/receipt/?ouid=%s', get_bloginfo( 'url' ), $order->ouid );
						
						?>
						<tr>
							<td class="ordernum"><a href="<?php echo $sReceiptUrl; ?>" title="Click to view receipt" target="_blank"><?php echo $order->trans_id; ?></a></td>
							<td class="date"><?php echo date( 'D, j M Y - h:i A', strtotime( $order->ordered_on ) ); ?></td>
							<td class="total"><?php echo Cart66Common::currency( $order->total ); ?></td>
							<td class="orderstat"><?php echo ucwords( $order->status ); ?></td>
							<td class="receipt"><a href="<?php echo $sReceiptUrl; ?>" title="Click to view receipt" target="_blank">View Receipt</a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
			
		else:
			echo self::MSG_PLUGIN_NOT_ACTIVATED;
		endif;
		
		return $this;
	}
	
	
	// helper
	
	// format attributes for use in shortcode
	public function formatAtts( $aAtts ) {
		
		$aAttsFmt = array();
		
		foreach ( $aAtts as $sKey => $sValue ) {
			$aAttsFmt[] = sprintf( '%s="%s"', $sKey, $sValue );
		}
		
		return implode( ' ', $aAttsFmt );
	}
	
	
	//// settings
	
	public function doCheckoutTab( $oDoc ) {
		
		$oTable1 = $oDoc->find( '#cc-cart_checkout table.form-table' );
		
		$oTable1->find( 'tbody' )->append(
			Geko_String::fromOb( array( $this, 'outputSettingsFields' ), array( 'checkout_checkout' ) )
		);
		
		// populate form values
		$oTable1 = Geko_Html::populateForm( $oTable1, $this->getFormValues( array(
			'cart_cont_shop_on_checkout',
			'cart_wp_user_integration',
			'cart_wp_user_terms_checkbox',
			'cart_wp_user_terms_verbiage'
		) ), TRUE );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$oTable2 = $oDoc->find( '#cc-terms_of_service table.form-table' );
		
		$oTable2->find( 'tbody' )->append(
			Geko_String::fromOb( array( $this, 'outputSettingsFields' ), array( 'checkout_terms' ) )
		);
		
		// populate form values
		$oTable2 = Geko_Html::populateForm( $oTable2, $this->getFormValues( array(
			'cart_terms_agree_checkbox',
			'cart_terms_verbiage'
		) ), TRUE );
		
		
		
		return $oDoc;
	}
	
	
	//
	public function doNotificationsTab( $oDoc ) {
		
		$oTable1 = $oDoc->find( '#mainEmailReceiptForm table.form-table' );
		
		$oTable1->find( '#receipt_send_html_emails_yes' )->parents( 'tr' )->after(
			Geko_String::fromOb( array( $this, 'outputSettingsFields' ), array( 'notifications_receipt' ) )
		);
		
		// populate form values
		$oTable1 = Geko_Html::populateForm( $oTable1, $this->getFormValues( array(
			'receipt_html_logo'
		) ), TRUE );
		
		
		
		return $oDoc;	
	}
	
	
	//
	public function outputSettingsFields( $sSection ) {
		
		if ( 'checkout_checkout' == $sSection ): ?>
			
			<tr valign="top">
				<th scope="row">Show Continue Shopping Button on Checkout Page</th>
				<td>
					<input type="radio" value="1" id="cart_cont_shop_on_checkout_yes" name="cart_cont_shop_on_checkout" />
					<label for="cart_cont_shop_on_checkout_yes">Yes</label>
					<input type="radio" value="" id="cart_cont_shop_on_checkout_no" name="cart_cont_shop_on_checkout" />
					<label for="cart_cont_shop_on_checkout_no">No</label>
				</td>
			</tr>
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
		
		<?php elseif ( 'checkout_terms' == $sSection ): ?>
			
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

		<?php elseif ( 'notifications_receipt' == $sSection ): ?>
			
			<tr valign="top">
				<th scope="row">HTML Logo</th>
				<td>
					<input type="text" value="" id="receipt_html_logo" name="receipt_html_logo" class="regular-text" />
					<p class="description">Include full &lt;img /&gt; tag included in the HTML receipt header.</p>
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
	
	
	
	
	//// ajax overrides
	
	//
	public function ajaxTriggerCheck() {
		
		$aRes = NULL;
		
		if ( intval( get_query_var( 'cart66AjaxCartRequests' ) ) == 4 ) {
			
			$iId = 0;
			$sState = '';
			$sZip = '';
			$fTax = 0;
			$fRate = 0;
			$fTotal = 0;
			
			if (
				isset( $_POST[ 'state' ] ) && 
				isset( $_POST[ 'state_text' ] ) && 
				isset( $_POST[ 'zip' ] ) && 
				isset( $_POST[ 'gateway' ] )
			) {
				
				$iId = 1;
				
				$oCalculation = $this->getCalculation();
				
				$gateway = Cart66Ajax::loadAjaxGateway( $_POST[ 'gateway' ] );
				
				$gateway->setShipping( array(
					'state_text' => $_POST[ 'state_text' ],
					'state' => $_POST[ 'state' ],
					'zip' => $_POST[ 'zip' ] )
				);
				
				$s = $gateway->getShipping();
				
				$sState = $s[ 'state' ];
				$sZip = $s[ 'zip' ];
				
				$oCalculation->setLocation( $sState );				
				
				$oCalculation = apply_filters( get_class( $this ) . '::ajaxTriggerCheck::calculate_tax', $oCalculation, $this );
				
				$oCalculation->calculate();
				
				$fTax = $oCalculation->getTax();
				$fRate = $oCalculation->getTaxRatePercent();
				$fTotal = $oCalculation->getTotal();
				
				
			}
			
			$aRes = array(
				'id' => $iId,
				'state' => $sState,
				'zip' => $sZip,
				'tax' => Cart66Common::currency( $fTax ),
				'rate' => ( $fRate ) ? Cart66Common::tax( $fRate ) : '0.00%' ,
				'total' => Cart66Common::currency( $fTotal )
			);
		
		}
		
		if ( $aRes ) {
			echo json_encode( $aRes );
			die();
		}
	}
	
	
	
}



