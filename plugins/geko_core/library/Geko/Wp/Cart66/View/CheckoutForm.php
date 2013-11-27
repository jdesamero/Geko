<?php

//
class Geko_Wp_Cart66_View_CheckoutForm extends Geko_Wp_Cart66_View
{
	
	
	//
	public function render() {
		
		$this->_sThisFile = __FILE__;
		
		$data = $this->getParam( 'data' );
		$notices = $this->getParam( 'notices' );
		$minify = $this->getParam( 'minify' );
		
		$account = $this->getParam( 'account' );
		$errors = $this->getParam( 'errors' );
		$gatewayName = $this->getParam( 'gatewayName' );
		$b = $this->getParam( 'b' );
		$s = $this->getParam( 's' );
		$p = $this->getParam( 'p' );
		$billingCountryCode = $this->getParam( 'billingCountryCode' );
		$shippingCountryCode = $this->getParam( 'shippingCountryCode' );
		
		
		
		$oCart = $this->getSess( 'Cart' );
		
		$gateway = $data[ 'gateway' ];
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$account = isset( $account ) ? $account : FALSE;
		
		if ( CART66_PRO ) {
			
			$account = $account ? $account : new Cart66Account() ;
			
			if ( $accountId = Cart66Common::isLoggedIn() ) {
				
				$account = new Cart66Account( $accountId );
				$name = $account->firstName . '&nbsp;' . $account->lastName;
				$logout = Cart66Common::appendQueryString( 'cart66-task=logout' );
				
				?>
				<h3 class="loggedInAs">You Are Logged In As <?php echo $name; ?></h3>
				<p class="loggedInWrongMsg">If you are not <?php echo $name; ?> <a href="<?php echo $logout; ?>">Log out</a></p>
				<?php
				
				if ( empty( $b[ 'firstName' ] ) ) {
					$b[ 'firstName' ] = $account->billingFirstName;
					$b[ 'lastName' ] = $account->billingLastName;
				}
				
				if ( empty( $p[ 'email' ] ) ) {
					$p[ 'email' ] = $account->email;
				}
			}
		}
		
		//
		if ( empty( $b[ 'country' ] ) ) {
			$b[ 'country' ] = Cart66Common::getHomeCountryCode();
		}
		
		
		
		// form classes
		$aFormClasses = array( 'phorm2', 'checkout_form' );
		
		$sCoBtnDivClass = '';
		if ( count( $errors ) > 0 ) {
			// echo Cart66Common::showErrors( $errors );
			$aFormClasses[] = 'has_errors';
			$sCoBtnDivClass = 'has_errors';
		}
		
		if ( $oCart->requireShipping() && ( 'Cart66ManualGateway' != $gatewayName ) ) {
			$aFormClasses[] = 'shipping';
		}
		
		$bEnableConstantContact = FALSE;
		if ( $lists = $this->getVal( 'constantcontact_list_ids' ) && $this->getVal( 'constantcontact_username' ) ) {
			$aFormClasses[] = 'constantcontact';
			$bEnableConstantContact = TRUE;
		}
		
		$bEnableMailChimp = FALSE;
		if ( $lists = $this->getVal( 'mailchimp_list_ids' ) && $this->getVal( 'mailchimp_apikey' ) ) {
			$aFormClasses[] = 'mailchimp';
			$bEnableMailChimp = TRUE;
		}
		
		if ( $oCart->hasSubscriptionProducts() || $oCart->hasMembershipProducts() ) {
			$aFormClasses[] = 'subscription';
		}
		
		$bEnableWordpressUserIntegration = $this->getVal( 'cart_wp_user_integration' ) ? TRUE : FALSE ;
		
		
		
		//// form classes
		
		$sFormClasses = implode( ' ', $aFormClasses );
		
		$sTaxableProds = ( $oCart->hasTaxableProducts() ) ? 'true' : 'false' ;
		$sTaxBlockClass = ( $this->getSess( 'Tax' ) > 0 ) ? 'show-tax-block' : 'hide-tax-block' ;
		
		// checkout login form
		if ( $bEnableWordpressUserIntegration ) {
			$this->doCheckoutFormLogin( $errors );
		}
		
		// action hook
		do_action( $this->_sInstanceClass . '::pre_form', $this );
		
		?>
		<form action="" method="post" id="<?php echo $gatewayName ?>_form" class="<?php echo $sFormClasses; ?>">
			
			<input type="hidden" name="geko_process_source_form" value="cart66" />
			
			<input type="hidden" class="ajax-tax-cart" name="ajax-tax-cart" value="<?php echo $sTaxableProds; ?>" />
			<input type="hidden" name="cart66-gateway-name" value="<?php echo $gatewayName ?>" id="cart66-gateway-name" />
			
			<?php if ( CART66_PRO && $this->getVal( 'checkout_custom_field_display' ) && $this->getVal( 'checkout_custom_field_display' ) != 'disabled'): ?>
				<div class="checkout-custom-field">
					
					<?php if ( $this->getVal( 'checkout_custom_field_label' ) ): ?>
						<p><?php $this->echoVal( 'checkout_custom_field_label' ); ?></p>
					<?php else: ?>
						<p><?php $this->_e( 'Enter any special instructions you have for this order:' ); ?></p>
					<?php endif; ?>
					
					<?php if ( ( 'multi' == $this->getVal( 'checkout_custom_field' ) ) || !$this->getVal( 'checkout_custom_field' ) ): ?>
						<textarea id="checkout-custom-field-multi" name="payment[custom-field]"><?php Cart66Common::showValue($p['custom-field']); ?></textarea>
					<?php elseif ( 'single' == $this->getVal( 'checkout_custom_field' ) ): ?>
						<input type="text" id="checkout-custom-field-single" name="payment[custom-field]" value="<?php Cart66Common::showValue($p['custom-field']); ?>" />
					<?php endif; ?>
				
				</div>
			<?php endif; ?>
			
			<div id="ccInfo">
				
				<div id="billingInfo">
					<ul id="billingAddress" class="shortLabels">
						
						<?php if ( ( 'Cart66ManualGateway' == $gatewayName ) && !$oCart->requireShipping() ): ?>
							<li><h2><?php $this->_e( 'Order Information' ); ?></h2></li>
						<?php else: ?>
							<li><h2><?php $this->_e( 'Billing Address' ); ?></h2></li>
						<?php endif; ?>
						
						<li>
							<label for="billing-firstName"><?php $this->_e( 'First name' ); ?>:</label>
							<input type="text" id="billing-firstName" name="billing[firstName]" value="<?php Cart66Common::showValue($b['firstName']); ?>" />
						</li>
						
						<li>
							<label for="billing-lastName"><?php $this->_e( 'Last name' ); ?>:</label>
							<input type="text" id="billing-lastName" name="billing[lastName]" value="<?php Cart66Common::showValue($b['lastName']); ?>" />
						</li>
						
						<li>
							<label for="billing-address"><?php $this->_e( 'Address' ); ?>:</label>
							<input type="text" id="billing-address" name="billing[address]" value="<?php Cart66Common::showValue($b['address']); ?>" />
						</li>
						
						<li>
							<label for="billing-address2" id="billing-address2-label" class="Cart66Hidden"><?php $this->_e( 'Address 2' ); ?>:</label>
							<input type="text" id="billing-address2" name="billing[address2]" value="<?php Cart66Common::showValue($b['address2']); ?>" />
						</li>
						
						<li>
							<label for="billing-city"><?php $this->_e( 'City' ); ?>:</label>
							<input type="text" id="billing-city" name="billing[city]" value="<?php Cart66Common::showValue($b['city']); ?>">
						</li>
						
						<li><label for="billing-state_text" class="short billing-state_label"><?php $this->_e( 'State' ); ?>:</label>
							<input type="text" name="billing[state_text]" value="<?php Cart66Common::showValue( $b[ 'state' ] ); ?>" id="billing-state_text" class="ajax-tax state_text_field" />
							<select id="billing-state" class="ajax-tax required" title="State billing address" name="billing[state]">
								<option value="">&nbsp;</option>
								<?php
									
								$this->logMsg( __LINE__, 'Country code on checkout form', $billingCountryCode );
								
								$zone = Cart66Common::getZones( $billingCountryCode );
								foreach ( $zone as $code => $name ):
									$selected = ( $b[ 'state' ] == $code ) ? ' selected="selected" ' : '' ;
									?><option value="<?php echo $code; ?>" <?php echo $selected; ?> ><?php echo $name; ?></option><?php
								endforeach;
								
								?>
							</select>
            			</li>
            			
						<li id="billing_tax_update" class="tax-block <?php echo $sTaxBlockClass; ?>">
							<span class="tax-update">
								<label class="short">&nbsp;</label>
								<p class="summary-message cart66-align-center tax-update-message"><span class="tax-rate"><?php $this->echoSess( 'TaxRate' ); ?></span> <?php $this->_e( 'tax' ); ?>,  <span class="tax-amount"><?php echo Cart66Common::currency( $this->getSess( 'Tax' ) ); ?></span></p>
							</span>
						</li>
						<li>
							<label for="billing-zip" class="billing-zip_label"><?php $this->_e( 'Zip code' ); ?>:</label>
							<input type="text" id="billing-zip" name="billing[zip]" value="<?php Cart66Common::showValue($b['zip']); ?>" class="ajax-tax" />
						</li>
						
						<li>
							<label for="billing-country" class="short"><?php $this->_e( 'Country' ); ?>:</label>
							<select title="country" id="billing-country" name="billing[country]" class="billing_countries">
								<?php foreach ( Cart66Common::getCountries() as $code => $name ):
									$selected = ( $code == $billingCountryCode ) ? ' selected="selected" ' : '' ;
									?><option value="<?php echo $code; ?>" <?php echo $selected; ?> ><?php echo $name; ?></option>
								<?php endforeach; ?>
							</select>
							<?php if ( $this->getSess( 'ShippingCountryCode' ) && $this->getVal( 'international_sales' ) ): ?>
								<p class="limited-countries-label-billing summary-message cart66-align-center"><?php $this->_e( 'Available countries may be limited based on your selected shipping method' ); ?></p>
							<?php endif; ?>
						</li>
						
						<?php
						
						$optional_billing_fields = apply_filters( 'cart66_after_billing_form', '' );
						if ( is_array( $optional_billing_fields ) ) {
							foreach ( $optional_billing_fields as $field ) {
								echo Cart66Common::displayCustomFormField( $field, $b );
							}
						}
						
						?>
						
					</ul>
				</div><!-- #billingInfo -->
				
				<?php if ( $oCart->requireShipping() ): ?>
					
					<div id="shippingInfo">
						
						<ul id="shippingAddressCheckbox">
							<li><h2><?php $this->_e( 'Shipping Address' ); ?></h2></li>							
							<li>
								<label for="sameAsBilling"><?php $this->_e( 'Same as billing address' ); ?>:</label>
								<input type="checkbox" class="sameAsBilling" id="sameAsBilling" name="sameAsBilling" value="1" />
							</li>
						</ul>

						<ul id="shippingAddress" class="shippingAddress shortLabels">
						
							<li>
								<label for="shipping-firstName"><?php $this->_e( 'First name' ); ?>:</label>
								<input type="text" id="shipping-firstName" name="shipping[firstName]" value="<?php Cart66Common::showValue($s['firstName']); ?>" />
							</li>
							
							<li>
								<label for="shipping-lastName"><?php $this->_e( 'Last name' ); ?>:</label>
								<input type="text" id="shipping-lastName" name="shipping[lastName]" value="<?php Cart66Common::showValue($s['lastName']); ?>" />
							</li>
							
							<li>
								<label for="shipping-address"><?php $this->_e( 'Address' ); ?>:</label>
								<input type="text" id="shipping-address" name="shipping[address]" value="<?php Cart66Common::showValue($s['address']); ?>" />
							</li>
							
							<li>
								<label for="shipping-address2">&nbsp;</label>
								<input type="text" id="shipping-address2" name="shipping[address2]" value="<?php Cart66Common::showValue($s['address2']); ?>" />
							</li>
							
							<li>
								<label for="shipping-city"><?php $this->_e( 'City' ); ?>:</label>
								<input type="text" id="shipping-city" name="shipping[city]" value="<?php Cart66Common::showValue($s['city']); ?>" />
							</li>
							
							<li>
								<label for="shipping-state_text" class="short shipping-state_label"><?php $this->_e( 'State' ); ?>:</label>
								<input type="text" name="shipping[state_text]" value="<?php Cart66Common::showValue( $s[ 'state' ] ); ?>" id="shipping-state_text" class="ajax-tax state_text_field" />
								<select id="shipping-state" class="ajax-tax shipping_countries required" title="State shipping address" name="shipping[state]">
									<option value="">&nbsp;</option>              
									<?php
									
									$zone = Cart66Common::getZones( $shippingCountryCode );
									foreach ( $zone as $code => $name ):
										$selected = ( $s[ 'state' ] == $code ) ? ' selected="selected" ' : '' ;
										?><option value="<?php echo $code; ?>" <?php echo $selected; ?> ><?php echo $name; ?></option><?php
									endforeach;
									
									?>
								</select>
							</li>
							
							<li id="shipping_tax_update" class="tax-block <?php echo $sTaxBlockClass; ?>">
								<span class="tax-update">
									<label class="short">&nbsp;</label>
									<p class="summary-message cart66-align-center tax-update-message"><span class="tax-rate"><?php $this->echoSess( 'TaxRate' ); ?></span> <?php $this->_e( 'tax' ); ?>,  <span class="tax-amount"><?php echo Cart66Common::currency( $this->getSess( 'Tax' ) ); ?></span></p>
								</span>
							</li>
							
							<li>
								<label for="shipping-zip" class="shipping-zip_label"><?php $this->_e( 'Zip code' ); ?>:</label>
								<input type="text" id="shipping-zip" name="shipping[zip]" value="<?php Cart66Common::showValue( $s[ 'zip' ] ); ?>" class="ajax-tax" />
							</li>
							
							<li>
								<label for="shipping-country" class="short"><?php $this->_e( 'Country' ); ?>:</label>
								<select title="country" id="shipping-country" name="shipping[country]">
									<?php foreach( Cart66Common::getShippingCountries() as $code => $country_name ):
										
										$disabled = false;
										if ( is_array( $country_name ) ) {
											$disabled = isset( $country_name[ 'disabled' ] ) ? $country_name[ 'disabled' ] : 'true';
											$country_name = $country_name[ 'country' ];
										}
										
										$disabled = ( 'true' == $disabled ) ? ' disabled="disabled" ' : '' ;
										$selected = ( ( $code == $shippingCountryCode ) && !$disabled ) ? ' selected="selected" ' : '' ;
										
										?>
										<option value="<?php echo $code ?>" <?php echo $selected; ?> <?php echo $disabled; ?> ><?php echo $country_name; ?></option>
									<?php endforeach; ?>
								</select>
								<?php if ( $this->getSess( 'ShippingCountryCode' ) && $this->getVal( 'international_sales' ) ): ?>
									<p class="limited-countries-label-shipping summary-message cart66-align-center"><?php $this->_e( 'Available countries may be limited based on your selected shipping method' ); ?></p>
								<?php endif; ?>
							</li>
							<?php
							
							$optional_shipping_fields = apply_filters( 'cart66_after_shipping_form', '' );
							if ( is_array( $optional_shipping_fields ) ) {
								foreach ( $optional_shipping_fields as $field ) {
									echo Cart66Common::displayCustomFormField( $field, $s );
								}
							}
							
							?>
						</ul>
						
					</div> <!--shippingInfo-->
					
				<?php else: ?>
					<input type="hidden" id="sameAsBilling" name="sameAsBilling" value="1" />
				<?php endif; ?>
				
				<?php if ( $bEnableWordpressUserIntegration ):
					$this->doCheckoutFormCreateAccount();
				endif; ?>
				
				<?php do_action( $this->_sInstanceClass . '::form_mid', $this ); ?>
				
				<div id="paymentInfo">
					
					<ul id="contactPaymentInfo" class="shortLabels">
						
						<?php if ( 'Cart66ManualGateway' == $gatewayName ): ?>
							<li><h2><?php $this->_e( 'Contact Information' ); ?></h2></li>
						<?php else: ?>
							<li><h2><?php $this->_e( 'Payment Information' ); ?></h2></li>
						<?php endif; ?>
						
						<?php if($gatewayName != 'Cart66ManualGateway'): ?>
							
							<li>
								<label for="payment-cardType">Card Type:</label>
								<select id="payment-cardType" name="payment[cardType]">
									<?php foreach ( $gateway->getCreditCardTypes() as $name => $value ): ?>
										<option value="<?php echo $value ?>"><?php echo $name ?></option>
									<?php endforeach; ?>
								</select>
							</li>
        					
							<li>
								<label for="payment-cardNumber"><?php $this->_e( 'Card Number' ); ?>:</label>
								<input type="text" id="payment-cardNumber" name="payment[cardNumber]" value="<?php Cart66Common::showValue($p['cardNumber']); ?>" />
							</li>
							
							<li>
								<label for="payment-cardExpirationMonth"><?php $this->_e( 'Expiration' ); ?>:</label>
								<select id="payment-cardExpirationMonth" name="payment[cardExpirationMonth]">
									<option value=""></option>
									<?php 
									
									for ( $i = 1; $i <= 12; $i++ ) {
										$val = $i;
										if ( strlen( $val ) == 1 ) $val = '0' . $i;
										$selected = ( isset( $p[ 'cardExpirationMonth' ] ) && ( $val == $p[ 'cardExpirationMonth' ] ) ) ? ' selected="selected" ' : '' ;
										?><option value="<?php echo $val; ?>" <?php echo $selected; ?> ><?php echo $val; ?></option><?php
									} 
									
									?>
								</select> / 
								<select id="payment-cardExpirationYear" name="payment[cardExpirationYear]">
									<option value=""></option>
									<?php
									
									$year = date( 'Y', Cart66Common::localTs() );
									for ( $i = $year; $i <= ( $year + 12 ); $i++ ) {
										$selected = ( isset( $p[ 'cardExpirationYear' ] ) && ( $i == $p[ 'cardExpirationYear' ] ) ) ? ' selected="selected" ' : '' ;
										?><option value="<?php echo $i; ?>" <?php echo $selected; ?> ><?php echo $i; ?></option><?php
									}
									
									?>
								</select>
							</li>
							
							<li>
								<label for="payment-securityId"><?php $this->_e( 'Security ID' ); ?>:</label>
								<input type="text" id="payment-securityId" name="payment[securityId]" value="<?php Cart66Common::showValue($p['securityId']); ?>" />
								<p class="description"><?php $this->_e( 'Security code on back of card' ); ?></p>
							</li>
						
						<?php endif; ?>
							
						<li>
							<label for="payment-phone"><?php $this->_e( 'Phone' ); ?>:</label>
							<input type="text" id="payment-phone" name="payment[phone]" value="<?php Cart66Common::showValue($p['phone']); ?>" />
						</li>
						
						<li>
							<label for="payment-email"><?php $this->_e( 'Email' ); ?>:</label>
							<input type="text" id="payment-email" name="payment[email]" value="<?php Cart66Common::showValue($p['email']); ?>" />
						</li>
						
						<?php
						
						$bEnableTermsCbx = $this->getVal( 'cart_terms_agree_checkbox' ) ? TRUE : FALSE ;
						
						$sTermsCbxVerbiage = trim( $this->getVal( 'cart_terms_verbiage' ) );
						
						if ( !$sTermsCbxVerbiage ) {
							$sTermsCbxVerbiage = sprintf( '%s <a href="#">%s</a>', $this->_t( 'Agree to Account Creation' ), $this->_t( 'Terms of Service' ) );
						}
						
						if ( $bEnableTermsCbx ): ?>
							<li>
								<input type="checkbox" id="payment-termsAgree" name="payment[termsAgree]" value="1" />
								<label for="payment-termsAgree" class="wide"><?php echo $sTermsCbxVerbiage; ?></label>
							</li>
						<?php endif; ?>
						
						<?php
						
						$optional_payment_fields = apply_filters( 'cart66_after_payment_form', '' );
						if ( is_array( $optional_payment_fields ) ) {
							foreach ( $optional_payment_fields as $field ) {
								echo Cart66Common::displayCustomFormField( $field, $p );
							}
						}
						
						?>
					</ul>
					
				</div><!-- #paymentInfo -->
				
			</div><!-- #ccInfo -->
			
			<?php if ( $bEnableConstantContact ): ?>
				<?php $lists = $this->getVal( 'constantcontact_list_ids' ); ?>
				<ul id="constantContact">
					<li>
						<?php
							
						if ( !$optInMessage = $this->getVal( 'constantcontact_opt_in_message' ) ) {
							$optInMessage = $this->_t( 'Yes, I would like to subscribe to:' );
						}
						
						$lists = explode( '~', $lists );
						
						?>
						
						<p><?php echo $optInMessage; ?></p>
						<ul class="Cart66NewsletterList">
							<?php foreach( $lists as $list ):
								list( $id, $name ) = explode( '::', $list );
								?><li><input class="Cart66CheckboxList" type="checkbox" name="constantcontact_subscribe_ids[]" value="<?php echo $id; ?>" /><?php echo $name; ?></li><?php
							endforeach; ?>
						</ul><?php
						
						?>
					</li>
				</ul>
			<?php endif; ?>
			
			
			<?php if ( $bEnableMailChimp ): ?>
				<?php $lists = $this->getVal( 'mailchimp_list_ids' ); ?>
				<ul id="mailChimp">
					<li>
						<?php
						
						if ( !$optInMessage = $this->getVal( 'mailchimp_opt_in_message' ) ) {
							$optInMessage = $this->_t( 'Yes, I would like to subscribe to:' );
						}
						
						$lists = explode( '~', $lists );
						
						?>
						<p>$optInMessage</p>
						<ul class="Cart66NewsletterList MailChimpList">
							<?php foreach ( $lists as $list ):
								list( $id, $name ) = explode( '::', $list );
								?><li><input class="Cart66CheckboxList" type="checkbox" name="mailchimp_subscribe_ids[]" value="<?php echo $id; ?>" /><?php echo $name; ?></li><?php
							endforeach; ?>
						</ul>
						<?php
						
						
						$aMailChimpIds = $_POST[ 'mailchimp_subscribe_ids' ];
						
						if ( is_array( $aMailChimpIds ) && ( count( $aMailChimpIds ) > 0 ) ):
							
							$aJsonParams = array(
								'mailchimpids' => $aMailChimpIds
							);
							
							?>
							<script type="text/javascript" charset="utf-8">
								
								var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
								
								( function( $ ) {
									
									$( document ).ready( function() {
									
										$.each( oParams.mailchimpids, function( i, v ) {
											$( '.MailChimpList input[value=' + v + ']' ).attr( 'checked', 'true' );
										} );
										
									} );
									
								} )( jQuery );
							</script> 
							<?php
							
						endif;
						
						?>
					</li>
				</ul>
			<?php endif; ?>
			
			<?php if(!Cart66Common::isLoggedIn()): ?>
				<?php if ( $oCart->hasSubscriptionProducts() || $oCart->hasMembershipProducts() ): ?>
					<?php echo Cart66Common::getView( 'pro/views/account-form.php', array( 'account' => $account, 'embed' => FALSE ) ); ?>
				<?php endif; ?>
			<?php endif; ?>
	
			<div id="Cart66CheckoutButtonDiv" class="<?php echo $sCoBtnDivClass; ?>">
				<label for="Cart66CheckoutButton" class="Cart66Hidden"><?php $this->_e( 'Checkout' ); ?></label>
				<?php
					
				// 
				$cartImgPath = $this->getVal( 'cart_images_url' );
				if ( $cartImgPath ) {
					if ( strpos( strrev( $cartImgPath ), '/' ) !== 0 ) {
						$cartImgPath .= '/';
					}
					$completeImgPath = $cartImgPath . 'complete-order.png';
				}
				
				//
				$url = Cart66Common::appendWurlQueryString( 'cart66AjaxCartRequests' );
				if ( Cart66Common::isHttps() ) {
					$url = preg_replace( '/http[s]*:/', 'https:', $url );
				} else {
					$url = preg_replace( '/http[s]*:/', 'http:', $url );
				}
				
				?>
				<?php if ( $this->getVal( 'checkout_order_summary' ) ): ?>
					
					<div class="confirm-order-modal summary-message tax-block <?php echo $this->getSess( 'Tax' ) > 0 ? 'show-tax-block' : 'hide-tax-block'; ?>">
						
						<div class="cart66-align-center"><h2><?php $this->_e( 'Order Summary' ); ?></h2></div>
						
						<table class="order-summary" cellpadding="0" cellspacing="0">	
							<tbody>
								<tr>
									<td class="subtotal-column cart66-align-right"><strong><?php $this->_e( 'Subtotal' ); ?></strong>:</td>
									<td class="cart66-align-right"><?php echo Cart66Common::currency( $oCart->getSubTotal()); ?></td>
								</tr>
								<?php if ( $oCart->requireShipping() ): ?>
									<tr>
										<td class="cart66-align-right"><strong><?php $this->_e( 'Shipping' ); ?></strong>:</td>
										<td class="cart66-align-right"><?php echo Cart66Common::currency( $oCart->getShippingCost() ); ?></td>
									</tr>
								<?php endif; ?>
								<?php if ( $this->getSess( 'Promotion' ) ): ?>
									<tr>
										<td class="cart66-align-right"><strong><?php $this->_e( 'Discount' ); ?></strong>:</td>
										<td class="cart66-align-right">-&nbsp;<?php echo Cart66Common::currency( $oCart->getDiscountAmount() ); ?></td>
									</tr>
								<?php endif; ?>
								<tr>
									<td class="cart66-align-right"><strong><span class="ajax-spin"><img src="<?php echo CART66_URL; ?>/images/ajax-spin.gif" /></span> <?php $this->_e( 'Tax' ); ?>  (<span class="tax-rate"><?php $this->echoSess( 'TaxRate' ); ?></span>)</strong>:</td>
									<td class="cart66-align-right"><span class="tax-amount"><?php echo Cart66Common::currency( $this->getSess( 'Tax' ) ); ?></span></td>
								</tr>
								<tr>
									<td class="cart66-align-right"><strong><span class="ajax-spin"><img src="<?php echo CART66_URL; ?>/images/ajax-spin.gif" /></span> <?php $this->_e( 'Total' ); ?></strong>:</td>
									<td class="cart66-align-right"><span class="grand-total-amount"><?php echo Cart66Common::currency( $oCart->getGrandTotal() + $this->getSess( 'Tax' ) ); ?></span></td>
								</tr>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
				
				<input type="hidden" name="confirm_url" value="<?php echo $url; ?>" id="confirm-url" />
				
				<?php if ( $cartImgPath ): ?>
					<input id="Cart66CheckoutButton" class="confirm-order Cart66CompleteOrderButton" type="image" src='<?php echo $completeImgPath ?>' value="<?php $this->_e( 'Complete Order' ); ?>" name="Complete Order"/>
				<?php else: ?>
					<input id="Cart66CheckoutButton" class="confirm-order Cart66ButtonPrimary Cart66CompleteOrderButton" type="submit"  value="<?php $this->_e( 'Complete Order' ); ?>" name="Complete Order"/>
				<?php endif; ?>
				
				<p class="description"><?php $this->_e( 'Your receipt will be on the next page and also immediately emailed to you.' ); ?><strong><?php $this->_e( 'We respect your privacy!' ); ?></strong></p>
				
			</div>
		</form>
		<?php
		
	}
	
	
	
	//// additional functionality
	
	//
	public function doCheckoutFormLogin( $errors ) {
		
		if ( !is_user_logged_in() ):
			
			$bHasErrors = ( count( $errors ) > 0 ? TRUE : FALSE );
			
			$aStatus = array();
			if ( class_exists( 'Gloc_Service_Profile' ) ) {
				$aStatus = array(
					'login' => Gloc_Service_Profile::STAT_LOGIN,
					'not_activated' => Gloc_Service_Profile::STAT_NOT_ACTIVATED
				);
			}
			
			$aJsonParams = array(
				'has_errors' => $bHasErrors,
				'errors' => $errors,
				'dont_create_account' => ( $_POST[ 'createacc-dont-create' ] ? TRUE : FALSE ),
				'script' => Geko_Wp::getScriptUrls(),
				'status' => $aStatus
			);
			
			$aFormClasses = array( 'phorm2' );
			
			if ( $bHasErrors ) {
				$aFormClasses[] = 'has_errors';
			}
			
			$sFormClasses = implode( ' ', $aFormClasses );
			
			?>
			
			<style type="text/css">
				
				form.checkout_form,
				#Cart66CheckoutButtonDiv {
					display: none;
				}
				
				form#checkoutLoginForm.has_errors {
					display: none;
				}
				
				form.checkout_form.has_errors,
				#Cart66CheckoutButtonDiv.has_errors {
					display: block;
				}
				
			</style>
			
			<script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					
					var checkoutForm = $( 'form.checkout_form' );
					var checkoutButtonDiv = $( '#Cart66CheckoutButtonDiv' );
					
					var createAccDiv = $( '#checkoutCreateAccount' );
					
					var loginForm = $( '#checkoutLoginForm' );
					
					
					/* toggle login form */
					
					var showCheckout = function() {
						loginForm.hide();
						checkoutForm.show();
						checkoutButtonDiv.show();					
					};
					
					var hideCheckout = function() {
						checkoutForm.hide();
						checkoutButtonDiv.hide();
						loginForm.show();					
					};					
					
					
					loginForm.find( 'a.next_step' ).click( function() {
						showCheckout();
						return false;
					} );
					
					createAccDiv.find( 'a.log_in' ).click( function() {
						hideCheckout();
						return false;
					} );
					
					
					/* login form functionality */
					
					
					loginForm.gekoAjaxForm( {
						status: oParams.status,
						process_script: oParams.script.process,
						action: '&action=Gloc_Service_Profile&subaction=login',
						validate: function( form, errors ) {
							
							var email = form.getTrimVal( '#chklog-email' );
							var password = form.getTrimVal( '#chklog-pass' );
							
							if ( !email ) {
								errors.push( 'Please enter your email address' );
								form.errorField( '#chklog-email' );
							} else {
								if ( !form.isEmail( email ) ) {
									errors.push( 'Please enter a valid email address' );
									form.errorField( '#chklog-email' );
								}
							}
							
							if ( !password ) {
								errors.push( 'Please enter a password' );
								form.errorField( '#chklog-pass' );
							} else {
								if ( password.length < 6 ) {
									errors.push( 'Password must be at least 6 characters long' );
									form.errorField( '#chklog-pass' );
								}
							}
							
							return errors;
							
						},
						process: function( form, res, status ) {
							if ( status.login == parseInt( res.status ) ) {
								/* reload page */
								window.location = oParams.script.curpage;
							} else if ( status.not_activated == parseInt( res.status ) ) {
								form.error( 'Please activate your account first.' );
							} else {
								form.error( 'Login failed. Please try again.' );
							}
						}
					} );
					
					
					/* toggle create account */
					
					var liPass = createAccDiv.find( '#createacc-pass' ).closest( 'li' );
					var liConfPass = createAccDiv.find( '#createacc-confirm-pass' ).closest( 'li' );
					var liTerms = createAccDiv.find( '#createacc-terms-agree' ).closest( 'li' );
					
					var cbxDontCreate = createAccDiv.find( '#createacc-dont-create' );
					
					cbxDontCreate.click( function() {
						
						var cbx = $( this );
						
						if ( cbx.is( ':checked' ) ) {
							liPass.hide();
							liConfPass.hide();
							liTerms.hide();
						} else {
							liPass.show();
							liConfPass.show();
							liTerms.show();
						}
						
					} );
					
					
					/* init */
					
					var errorHash = {
						'Create Account Password': 'createacc-pass',
						'Create Account Confirm Password': 'createacc-confirm-pass',
						'Create Account Terms of Use': 'createacc-terms-agree',
						'Email': 'payment-email',
						'Payment Card Number': 'payment-cardNumber',
						'Payment phone': 'payment-phone',
						'Payment Terms of Use': 'payment-termsAgree'
					};
					
					if ( oParams.has_errors ) {
						
						/* showCheckout(); */
						
						$.each( errorHash, function( k, v ) {
							
							var match = oParams.errors[ k ];
							if ( match ) {
								checkoutForm.find( '#' + v ).addClass( 'errorField' );
							}
							
						} );
					};
					
					if ( oParams.dont_create_account ) {
						cbxDontCreate.attr( 'checked', 'checked' );
						liPass.hide();
						liConfPass.hide();
						liTerms.hide();
					}
					
				} );
				
			</script>
			
			<form class="<?php echo $sFormClasses; ?>" id="checkoutLoginForm">
				
				<input type="hidden" name="geko_process_source_form" value="cart66" />
				
				<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
				<div class="error"></div>
				<div class="success"></div>
				
				<ul id="checkoutLogin" class="shortLabels">
					
					<li>
						<h2><?php $this->_e( 'Already Have an Account?' ); ?></h2>
						<p><?php $this->_e( 'Please log in below:' ); ?></p>
					</li>
					
					<li>
						<label for="chklog-email"><?php $this->_e( 'Email:' ); ?></label>
						<input type="text" id="chklog-email" name="chklog-email" value="" />
					</li>
					
					<li>
						<label for="chklog-pass"><?php $this->_e( 'Password:' ); ?></label>
						<input type="password" id="chklog-pass" name="chklog-pass" value="" />
					</li>
					
				</ul>
				
				<p>
					<input type="submit" value="<?php $this->_e( 'Login' ); ?>" />
					&nbsp;<small><a href="<?php bloginfo( 'url' ); ?>/login/forgot-password/"><?php $this->_e( 'Forgot Password?' ); ?></a></small>
				</p>
				
				<p><?php $this->_e( "Don't Have an Account?" ); ?> <a href="#" class="next_step"><?php $this->_e( 'Proceed to Next Step' ); ?></a></p>
				
			</form>
			
			<?php
		endif;
		
	}
	
	//
	public function doCheckoutFormCreateAccount() {
		
		if ( !is_user_logged_in() ):
			
			$bEnableTerms = $this->getVal( 'cart_wp_user_terms_checkbox' ) ? TRUE : FALSE ;
			
			$sTermsVerbiage = trim( $this->getVal( 'cart_wp_user_terms_verbiage' ) );
			
			if ( !$sTermsVerbiage ) {
				$sTermsVerbiage = sprintf( '%s <a href="#">%s</a>', $this->_t( 'Agree to Account Creation' ), $this->_t( 'Terms of Service' ) );
			}
			
			wp_nonce_field( 'cart66-registration-form' );
			
			?>
			
			<div id="checkoutCreateAccount">
				
				<input type="hidden" id="createacc-active" name="createacc-active" value="1" />
				
				<ul class="shortLabels">
					
					<li><h2><?php $this->_e( 'Create Account' ); ?></h2></li>
		
					<li>
						<label for="createacc-dont-create"><?php $this->_e( "Don't Create Account:" ); ?></label>
						<input type="checkbox" id="createacc-dont-create" name="createacc-dont-create" value="1" />
					</li>
					
					<li>
						<label for="createacc-pass"><?php $this->_e( 'Password:' ); ?></label>
						<input type="password" class="<?php echo $sPassError; ?>" id="createacc-pass" name="createacc-pass" value="<?php echo htmlspecialchars( $_POST[ 'createacc-pass' ] ); ?>" />
					</li>
					
					<li>
						<label for="createacc-confirm-pass"><?php $this->_e( 'Confirm Password:' ); ?></label>
						<input type="password" class="<?php echo $sConfirmPassError; ?>" id="createacc-confirm-pass" name="createacc-confirm-pass" value="<?php echo htmlspecialchars( $_POST[ 'createacc-confirm-pass' ] ); ?>" />
					</li>
					
					<?php if ( $bEnableTerms ): ?>
						<li>
							<input type="checkbox" id="createacc-terms-agree" name="createacc-terms-agree" value="1" />
							<label for="createacc-terms-agree" class="wide"><?php echo $sTermsVerbiage; ?></label>
						</li>
					<?php endif; ?>
					
				</ul>
				
				<p><?php $this->_e( 'Already Have an Account?' ); ?> <a href="#" class="log_in"><?php $this->_e( 'Log In' ); ?></a></p>
			
			</div>
			
		<?php endif;
		
	}
	
	
	
	
}
