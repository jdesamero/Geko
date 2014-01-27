<?php

//
class Geko_Wp_Ext_Cart66_View_Cart extends Geko_Wp_Ext_Cart66_View
{
	
	
	//
	public function getFullMode() {
		
		$data = $this->getParam( 'data' );
		
		return ( isset( $data[ 'mode' ] ) && ( 'read' == $data[ 'mode' ] ) ) ? FALSE : TRUE ;
	}
	
	
	//
	public function render() {
		
		$this->_sThisFile = __FILE__;
		
		$data = $this->getParam( 'data' );
		$notices = $this->getParam( 'notices' );
		$minify = $this->getParam( 'minify' );
		
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$oGekoCart66 = Geko_Wp_Ext_Cart66::getInstance();
		
		$oCalculation = $oGekoCart66->getCalculation();
		
		do_action( $this->_sInstanceClass . '::init_start', $oCalculation, $this );
		
		$oCalculation
			->setData( $data )
			->calculate()
		;
		
		$oCart = $this->getSess( 'Cart' );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		
		
		$bIframeMode = $oGekoCart66->getIframeMode();
		
		$sTargetTag = '';
		if ( $bIframeMode ) {
			$sTargetTag = ' target="_top" ';
		}
		
		
		$items = $oCart->getItems();
		$shippingMethods = $oCart->getShippingMethods();
		
		$promotion = $this->getSess( 'Promotion' );
		$product = new Cart66Product();
		
		
		$cartPage = get_page_by_path( 'store/cart' );
		$checkoutPage = get_page_by_path( 'store/checkout' );
		$setting = new Cart66Setting();
		
		
		// Try to return buyers to the last page they were on when the click to continue shopping
		if ( 1 == $this->getVal( 'continue_shopping' ) ) {
			
			// force the last page to be store home
			$lastPage = $this->getVal( 'store_url' ) ? $this->getVal( 'store_url' ) : get_bloginfo( 'url' );
			$this->setSess( 'LastPage', $lastPage );
			
		} else {
			
			if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) && isset( $_POST[ 'task' ] ) && ( 'addToCart' == $_POST[ 'task' ] ) ){
				$lastPage = $_SERVER[ 'HTTP_REFERER' ];
				$this->setSess( 'LastPage', $lastPage );
			}
			
			if ( !$this->getSess( 'LastPage' ) ) {
				// If the last page is not set, use the store url
				$lastPage = $this->getVal( 'store_url' ) ? $this->getVal( 'store_url' ) : get_bloginfo( 'url' );
				$this->setSess( 'LastPage', $lastPage );
			}  
		}
		
		$fullMode = $this->getFullMode();
		
		
		//// override images
		
		if ( $cartImgPath = $this->getCartImgPath() ) {
			$continueShoppingImg = $cartImgPath . 'continue-shopping.png';
			$updateTotalImg = $cartImgPath . 'update-total.png';
			$calculateShippingImg = $cartImgPath . 'calculate-shipping.png';
			$applyCouponImg = $cartImgPath . 'apply-coupon.png';
		}
		
		
		if ( $this->getSess( 'InvalidOptions' ) ): ?>
			<div id="Cart66InvalidOptions" class="alert-message alert-error Cart66Unavailable">
				<h2 class="header"><?php $this->_e( 'Invalid Product Options' ); ?></h2>
				<p><?php 
					$this->echoSess( 'InvalidOptions' );
					$this->dropSess( 'InvalidOptions' );
				?></p>
			</div>
		<?php endif; ?>
		
		<?php if ( count( $items ) ): ?>
			
			<?php if ( $this->getSess( 'InventoryWarning' ) && $fullMode ):
				
				$this->echoSess( 'InventoryWarning' );
				$this->dropSess( 'InventoryWarning' );
			
			endif; ?>
			
			<?php if ( number_format( $this->getVal( 'minimum_amount' ), 2, '.', '' ) > number_format( $oCart->getSubTotal(), 2, '.', '' ) && $this->getVal( 'minimum_cart_amount' ) == 1): ?>
				<div id="minAmountMessage" class="alert-message alert-error Cart66Unavailable">
					<?php echo ( $this->getVal( 'minimum_amount_label' ) ) ? $this->getVal( 'minimum_amount_label' ) : 'You have not yet reached the required minimum amount in order to checkout.' ?>
				</div>
			<?php endif;?>
			
			<?php if ( $this->getSess( 'ZipWarning' ) ): ?>
				
				<div id="Cart66ZipWarning" class="alert-message alert-error Cart66Unavailable">
					<h2 class="header"><?php $this->_e( 'Please Provide Your Zip Code' ); ?></h2>
					<p><?php $this->_e( 'Before you can checkout, please provide the zip code for where we will be shipping your order and click' ); ?> "<?php $this->_e( 'Calculate Shipping' ); ?>".</p>
					<?php $this->dropSess( 'ZipWarning' ); ?>
					<input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
				</div>
			
			<?php elseif ( $this->getSess( 'ShippingWarning' ) ): ?>
				
				<div id="Cart66ShippingWarning" class="alert-message alert-error Cart66Unavailable">
					<h2 class="header"><?php $this->_e( 'No Shipping Service Selected' ); ?></h2>
					<p><?php $this->_e( 'We cannot process your order because you have not selected a shipping method. If there are no shipping services available, we may not be able to ship to your location.' ); ?></p>
					<?php $this->dropSess( 'ShippingWarning' ); ?>
					<input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
				</div>
				
			<?php elseif ( $this->getSess( 'CustomFieldWarning' ) ): ?>
			  
				<div id="Cart66CustomFieldWarning" class="alert-message alert-error Cart66Unavailable">
					<h2 class="header"><?php $this->_e( 'Custom Field Error' ); ?></h2>
					<p><?php $this->_e( 'We cannot process your order because you have not filled out the custom field required for these products:' ); ?></p>
					<ul>
						<?php foreach( $this->getSess( 'CustomFieldWarning' ) as $customField ): ?>
							<li><?php echo $customField; ?></li>
						<?php endforeach;?>
					</ul>
					<input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
				</div>
			  
			<?php endif; ?>

			<?php if ( $this->getSess( 'SubscriptionWarning' ) ): ?>
				<div id="Cart66SubscriptionWarning" class="alert-message alert-error Cart66Unavailable">
					<h2 class="header"><?php $this->_e( 'Too Many Subscriptions' ); ?></h2>
					<p><?php $this->_e( 'Only one subscription may be purchased at a time.' ); ?></p>
					<?php $this->dropSess( 'SubscriptionWarning' ); ?>
					<input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
				</div>
			<?php endif; ?>

			<?php
			
			if ( $accountId = Cart66Common::isLoggedIn() ):
				
				$account = new Cart66Account( $accountId );
				
				if ( $sub = $account->getCurrentAccountSubscription() ):
					if ( $sub->isPayPalSubscription() && $oCart->hasPayPalSubscriptions() ):
						?>
						<p id="Cart66SubscriptionChangeNote"><?php $this->_e( 'Your current subscription will be canceled when you purchase your new subscription.' ); ?></p>
						<?php
					endif;
				endif;
				
			endif;
			
			do_action( $this->_sInstanceClass . '::pre_form', $oCalculation, $this );
			
			?>
			
			<form id="Cart66CartForm" action="" method="post">
				
				<input type="hidden" name="task" value="updateCart" />
				
				<table id="viewCartTable">
					<colgroup>
						<col class="col1" />
						<col class="col2" />
						<col class="col3" />
						<col class="col4" />
					</colgroup>
					<thead>
						<tr>
							<th class="cart66-product-col"><?php $this->_e( 'Product' ); ?></th>
							<th class="cart66-align-center cart66-qty-col"><?php $this->_e( 'Quantity' ); ?></th>
							<th class="cart66-align-right cart66-price-col"><?php $this->_e( 'Item Price' ); ?></th>
							<th class="cart66-align-right cart66-total-col"><?php $this->_e( 'Item Total' ); ?></th>
						</tr>
					</thead>
					<tbody>
						
						<?php do_action( $this->_sInstanceClass . '::form_start', $oCalculation, $this ); ?>
						
						<?php foreach( $items as $itemIndex => $item ):
							
							$this->logMsg( __LINE__, 'Item option info', $item->getOptionInfo() );
							
							$product->load( $item->getProductId() );
							$price = $item->getProductPrice() * $item->getQuantity();
							
							$sProdTitle = sprintf( '%s - %s', $item->getFullDisplayName(), $item->getProductPriceDescription() );
							
							$sNbbClass = '';
							if ( $item->hasAttachedForms() ) {
								$sNbbClass = 'noBottomBorder';
							}
							
							?><tr>
								<td class="<?php echo $sNbbClass; ?>" >
									
									<?php if ( $this->getVal( 'display_item_number_cart' ) ): ?>
										<span class="cart66-cart-item-number"><?php echo $item->getItemNumber(); ?></span>
									<?php endif; ?>
									
									<?php if (
										( '' != $item->getProductUrl() ) && 
										( 1 == $this->getVal( 'product_links_in_cart' ) ) && 
										( $fullMode )
									): ?>
										<a class="product_url" href="<?php echo $item->getProductUrl(); ?>" <?php echo $sTargetTag; ?> ><?php echo $sProdTitle; ?></a>
									<?php else: ?>
										<?php echo $sProdTitle; ?>
									<?php endif; ?>
									
									<?php echo $item->getCustomField( $itemIndex, $fullMode ); ?>
									
									<?php $this->dropSess( 'CustomFieldWarning' ); ?>
									
								</td>
								
								<?php if ( $fullMode ):
									
									$removeItemImg = CART66_URL . '/images/remove-item.png';
									
									if ( $cartImgPath ) {
										$removeItemImg = $cartImgPath . 'remove-item.png';
									}
									
									?>
									<td class="<?php echo $sNbbClass; ?>" >
										
										<?php if ( $item->isSubscription() || $item->isMembershipProduct() || ( 1 == $product->is_user_price ) ): ?>
											<span class="subscriptionOrMembership"><?php echo $item->getQuantity() ?></span>
										<?php else: ?>
											<input type="text" name="quantity[<?php echo $itemIndex ?>]" value="<?php echo $item->getQuantity(); ?>" class="itemQuantity" />
										<?php endif; ?>
										
										<?php
										
										$oRemoveUrl = new Geko_Uri( get_permalink( $cartPage->ID ) );
										$oRemoveUrl
											->setVar( 'task', 'removeItem' )
											->setVar( 'itemIndex', $itemIndex )
											->setVar( 'mode', $_GET[ 'mode' ] )
										;
										
										?>
										<a href="<?php echo strval( $oRemoveUrl ); ?>" title="<?php $this->_e( 'Remove item from cart' ); ?>"><img src="<?php echo $removeItemImg; ?>" alt="<?php $this->_e( 'Remove Item' ); ?>" /></a>
										
									</td>
								<?php else: ?>
									<td class="cart66-align-center <?php echo $sNbbClass; ?>"><?php echo $item->getQuantity(); ?></td>
								<?php endif; ?>
								
								<td class="cart66-align-right <?php echo $sNbbClass; ?>"><?php $this->echoCurr( $item->getProductPrice() ); ?></td>
								<td class="cart66-align-right <?php echo $sNbbClass; ?>"><?php $this->echoCurr( $price ); ?></td>
							</tr>
							<?php if ( $item->hasAttachedForms() ): ?>
								<tr>
									<td colspan="4">
										<a href="#" class="showEntriesLink" rel="entriesFor_<?php echo $itemIndex; ?>"><?php $this->_e( 'Show Details' ); ?> <?php #echo count($item->getFormEntryIds()); ?></a>
										<div id="entriesFor_<?php echo $itemIndex; ?>" class="showGfFormData" style="display: none;">
											<?php echo $item->showAttachedForms( $fullMode ); ?>
										</div>
									</td>
								</tr>
							<?php endif;?>      
						<?php endforeach; ?>
						
						<?php if ( $oCart->requireShipping() ): ?>
							
							<?php if ( CART66_PRO && $this->getVal( 'use_live_rates' ) ): ?>
								
								<?php $zipStyle = "style=''"; ?>
								
								<?php if ( $fullMode ): ?>
								
									<?php if ( $this->getSess( '_shipping_zip' ) ): ?>
										
										<?php $zipStyle = "style='display: none;'"; ?>
										
										<tr id="shipping_to_row">
											<th colspan="4" class="alignRight">
												
												<?php $this->_e( 'Shipping to' ); ?> <?php $this->echoSess( '_shipping_zip' ); ?>
												
												<?php if ( $this->getVal( 'international_sales' ) ) {
													$this->echoSess( '_shipping_country_code' );
												} ?>
												
												(<a href="#" id="change_shipping_zip_link"><?php $this->_e( 'change' ); ?></a>)
												
												&nbsp;
												
												<?php
													$liveRates = $oCart->getLiveRates();
													$rates = $liveRates->getRates();
													
													$this->logMsg( __LINE__, 'LIVE RATES', print_r( $rates, TRUE ) );
													
													$selectedRate = $liveRates->getSelected();
													// $shipping = $oCart->getShippingCost();
												?>
												
												<select name="live_rates" id="live_rates">
													<?php foreach( $rates as $rate ):
														
														$sSelectedAtt = '';
														if ( $selectedRate->service == $rate->service ) {
															$sSelectedAtt = ' selected="selected" ';
														}
														
														$sOptTitle = $rate->service;
														if ( FALSE !== $rate->rate ) {
															$sOptTitle .= ': $' . $rate->rate;
														}
														
														?>
														<option value="<?php echo $rate->service; ?>" <?php echo $sSelectedAtt; ?> ><?php echo $sOptTitle; ?></option>
													<?php endforeach; ?>
												</select>
											
											</th>
										</tr>
									
									<?php endif; ?>
									
									<tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
										<th colspan="4" class="alignRight"><?php $this->_e( 'Enter Your Zip Code' ); ?>:
											
											<input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />
											
											<?php if ( $this->getVal( 'international_sales' ) ):
												
												$customCountries = Cart66Common::getCustomCountries();
												
												?><select name="shipping_country_code">
													<?php foreach ( $customCountries as $code => $name ): ?>
														<option value="<?php echo $code; ?>"><?php echo $name; ?></option>
													<?php endforeach; ?>
												</select>
												
											<?php else: ?>
												<input type="hidden" name="shipping_country_code" value="<?php echo Cart66Common::getHomeCountryCode(); ?>" id="shipping_country_code">
											<?php endif; ?>
											
											<?php if ( $cartImgPath && Cart66Common::urlIsLIve( $calculateShippingImg ) ): ?>
												<input class="Cart66CalculateShippingButton" type="image" src="<?php echo $calculateShippingImg ?>" value="<?php $this->_e( 'Calculate Shipping' ); ?>" name="calculateShipping" />
											<?php else: ?>
												<input type="submit" name="calculateShipping" value="<?php $this->_e( 'Calculate Shipping' ); ?>" id="shipping_submit" class="Cart66CalculateShippingButton Cart66ButtonSecondary" />
											<?php endif; ?>
										
										</th>
									</tr>
								
								<?php else:  // Cart in read mode ?>
									
									<tr>
										<th colspan="4" class="alignRight">
											<?php
												$liveRates = $oCart->getLiveRates();
												if ( $liveRates && $this->getSess( '_shipping_zip' ) && $this->getSess( '_shipping_country_code' ) ) {
													$selectedRate = $liveRates->getSelected();
													printf( '%s %s %s %s', $this->_t( 'Shipping to' ), $this->getSess( '_shipping_zip' ), $selectedRate->service );
												}
											?>
										</th>
									</tr>
								
								<?php endif; // End cart in read mode ?>
							
							<?php  else: ?>
								
								<?php if ( count( $shippingMethods ) > 1 && $fullMode ): ?>
									
									<tr>
										<th colspan="4" class="alignRight"><?php $this->_e( 'Shipping Method' ); ?>: &nbsp;
											
											
											<?php if ( $this->getVal( 'international_sales' ) ):
												
												$customCountries = Cart66Common::getCustomCountries();
												
												?>
												<select name="shipping_country_code" id="shipping_country_code">
													<?php foreach ( $customCountries as $code => $name ):
														
														$sSelectedAtt = '';
														if ( $code == $this->getSess( 'ShippingCountryCode' ) ) {
															$sSelectedAtt = ' selected="selected" ';
														}
														
														?>
														<option value="<?php echo $code; ?>" <?php echo $sSelectedAtt; ?> ><?php echo $name; ?></option>
													<?php endforeach; ?>
												</select>
											<?php else: ?>
												<input type="hidden" name="shipping_country_code" value="<?php echo Cart66Common::getHomeCountryCode(); ?>" id="shipping_country_code">
											<?php endif; ?>
											
											
											<select name="shipping_method_id" id="shipping_method_id">
												<?php foreach ( $shippingMethods as $name => $id ):
												
													$method_class = 'methods-country ';
													$method = new Cart66ShippingMethod( $id );
													$methods = unserialize( $method->countries );
													
													if ( is_array( $methods ) ) {
														foreach ( $methods as $code => $country ) {
															$method_class .= $code . ' ';
														}
													}
													
													if ( 'select' == $id ) {
														$method_class = 'select';
													} elseif ( 'methods-country ' == $method_class ) {
														$method_class = 'all-countries';
													}
													
													$method_class = trim( $method_class );
													
													$sSelectedAtt = '';
													if ( $id == $oCart->getShippingMethodId() ) {
														$sSelectedAtt = ' selected="selected" ';
													}
													
													?>
													<option class="<?php echo $method_class; ?>" value="<?php echo $id; ?>" <?php echo $sSelectedAtt; ?> ><?php echo $name; ?></option>
												<?php endforeach; ?>
											</select>
										</th>
									</tr>
								
								<?php elseif ( !$fullMode ): ?>
									
									<tr>
										<th colspan="4" class="alignRight"><?php $this->_e( 'Shipping Method' ); ?>: 
										<?php
											$method = new Cart66ShippingMethod( $oCart->getShippingMethodId() );
											echo $method->name;
										?>
										</th>
									</tr>
								
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
						
						<!-- SUB-TOTAL -->
						
						<tr class="subtotal">
							<?php if ( $fullMode ): ?>
								<td>&nbsp;</td>
								<td>
									<?php if ( $cartImgPath && Cart66Common::urlIsLIve( $updateTotalImg ) ): ?>
										<input class="Cart66UpdateTotalButton" type="image" src="<?php echo $updateTotalImg ?>" value="<?php $this->_e( 'Update Total' ); ?>" name="updateCart"/>
									<?php else: ?>
										<input type="submit" name="updateCart" value="<?php $this->_e( 'Update Total' ); ?>" class="Cart66UpdateTotalButton Cart66ButtonSecondary" />
									<?php endif; ?>
								</td>
							<?php else: ?>
								<td colspan="2">&nbsp;</td>
							<?php endif; ?>
							<td class="alignRight strong"><?php $this->_e( 'Subtotal' ); ?>:</td>
							<td class="strong cart66-align-right"><?php echo $oCalculation->getCurrSubTotal(); ?></td>
						</tr>
												
						<!-- DISCOUNT ONE -->

						<?php if ( $promotion ):
							
							$sPromoName = ( $promotion->name ) ? $promotion->name : $this->getSess( 'PromotionCode' );
							$sTitle = sprintf( '%s (%s):', $this->_t( 'Coupon' ), $sPromoName );
							
							?>
							<tr class="coupon">
								<td colspan="3" class="alignRight strong"><?php echo $sTitle; ?></td>
								<td class="strong cart66-align-right">
									-&nbsp;<?php echo $oCalculation->getCurrDiscountOne(); ?>
								</td>
							</tr>
						<?php endif; ?>
						
						<?php do_action( $this->_sInstanceClass . '::form_discount', $oCalculation, $this ); ?>
												
						<!-- SHIPPING -->
						
						<?php if ( $oCart->requireShipping() ): ?>
							<tr class="shipping">
								<td colspan="3" class="alignRight strong"><?php $this->_e( 'Shipping' ); ?>:</td>
								<td class="strong cart66-align-right"><?php echo $oCalculation->getCurrShipping(); ?></td>
							</tr>
						<?php endif; ?>
						
						<!-- TAX -->
						
						<?php
						
						$sTaxRowClass = ( $oCalculation->getTax() > 0 ) ? 'show-tax-row' : 'hide-tax-row' ;
						
						?><tr class="tax-row <?php echo $sTaxRowClass; ?>">
							<td colspan="3" class="alignRight strong">
								<span class="ajax-spin"><img src="<?php echo CART66_URL; ?>/images/ajax-spin.gif" /></span> 
								<?php $this->_e( 'Tax' ); ?> (<span class="tax-rate"><?php echo $oCalculation->getTaxRatePercent(); ?>%</span>):
							</td>
							<td class="strong tax-amount cart66-align-right"><?php echo $oCalculation->getCurrTax(); ?></td>
						</tr>
						
						<!-- TOTAL -->
						
						<tr class="total">
							<?php if ( $oCart->getNonSubscriptionAmount() > 0): ?>
								<td class="alignRight" colspan="2">
									
									<?php if ( $fullMode && Cart66Common::activePromotions() ): ?>
										<p class="haveCoupon"><?php $this->_e( 'Do you have a coupon?' ); ?></p>
										
										<?php if ( $this->getSess( 'PromotionErrors' ) ):
											$promoErrors = $this->getSess( 'PromotionErrors' );
											foreach ( $promoErrors as $type => $error ): ?>
												<p class="promoMessage warning"><?php echo $error; ?></p>
											<?php endforeach;?>
											<?php $oCart->clearPromotion();
										endif; ?>
									
										<div id="couponCode"><input type="text" name="couponCode" value="" /></div>
										
										<div id="updateCart">
											<?php if ( $cartImgPath && Cart66Common::urlIsLIve( $applyCouponImg ) ): ?>
												<input class="Cart66ApplyCouponButton" type="image" src="<?php echo $applyCouponImg ?>" value="<?php $this->_e( 'Apply Coupon' ); ?>" name="updateCart"/>
											<?php else: ?>
												<input type="submit" name="updateCart" value="<?php $this->_e( 'Apply Coupon' ); ?>" class="Cart66ApplyCouponButton Cart66ButtonSecondary" />
											<?php endif; ?>
										</div>
										
									<?php endif; ?>
								</td>
							<?php else: ?>
								<td colspan="2">&nbsp;</td>
							<?php endif; ?>
							<td class="alignRight strong Cart66CartTotalLabel"><span class="ajax-spin"><img src="<?php echo CART66_URL; ?>/images/ajax-spin.gif" /></span> <?php $this->_e( 'Total' ); ?>:</td>
							<td class="strong grand-total-amount cart66-align-right"><?php echo $oCalculation->getCurrTotal(); ?></td>
						</tr>
						
						<?php do_action( $this->_sInstanceClass . '::form_end', $oCalculation, $this ); ?>
						
					</tbody>
				</table>
			</form>
			
			<?php do_action( $this->_sInstanceClass . '::post_form', $oCalculation, $this ); ?>
			
			<?php if ( $fullMode ): ?>
			
				<div id="viewCartNav">
					
					<div id="continueShopping">
						<?php $this->displayContinueShoppingBtn( $continueShoppingImg, $sTargetTag ); ?>
					</div>
					
					<?php
					
					// dont show checkout until terms are accepted (if necessary)
					
					if (
						( $this->getVal( 'require_terms' ) != 1 ) ||
						( ( $this->getVal( 'require_terms' ) == 1 ) && (
							( isset( $_POST[ 'terms_acceptance' ] ) ) || 
							( Cart66Session::get( 'terms_acceptance' ) == 'accepted' )
						) )
					):
						
						if ( $this->getVal( 'require_terms' ) == 1 ) {
							Cart66Session::set( 'terms_acceptance', 'accepted', TRUE );        
						}
						
						$checkoutImg = FALSE;
						if ( $cartImgPath ) {
							$checkoutImg = $cartImgPath . 'checkout.png';
						}
						
						
						if ( number_format( $this->getVal( 'minimum_amount' ), 2, '.', '' ) > number_format( $oCart->getSubTotal(), 2, '.', '' ) && ( 1 == $this->getVal( 'minimum_cart_amount' ) ) ):
							// do nothing
						else: ?>
							<div id="checkoutShopping">
								
								<?php $checkoutUrl = $this->getVal( 'auth_force_ssl' ) ? str_replace( 'http://', 'https://', get_permalink( $checkoutPage->ID ) ) : get_permalink( $checkoutPage->ID ); ?>
								
								<?php if($checkoutImg): ?>
									<a id="Cart66CheckoutButton" href="<?php echo $checkoutUrl; ?>" <?php echo $sTargetTag; ?> ><img src="<?php echo $checkoutImg ?>" /></a>
								<?php else: ?>
									<a id="Cart66CheckoutButton" href="<?php echo $checkoutUrl; ?>" class="Cart66ButtonPrimary" title="Continue to Checkout" <?php echo $sTargetTag; ?> ><?php $this->_e( 'Checkout' ); ?></a>
								<?php endif; ?>
							
							</div><?php
						endif;
					else: ?>
						<div id="Cart66CheckoutReplacementText">
							<?php $this->echoVal( 'cart_terms_replacement_text' );  ?>
						</div><?php
					endif;
					
					if ( CART66_PRO && ( 1 == $this->getVal( 'require_terms' ) ) && ( !isset( $_POST[ 'terms_acceptance' ] ) && ( Cart66Session::get( 'terms_acceptance' ) != 'accepted' ) ) ) {
						echo Cart66Common::getView( 'pro/views/terms.php', array( 'location' => 'Cart66CartTOS' ) );
					} 
				
				?></div>
			<?php endif; ?>

		<?php else: ?>
			
			<div id="emptyCartMsg">
				<h3><?php $this->_e( 'Your Cart Is Empty' ); ?></h3>
				<?php $this->displayContinueShoppingBtn( $continueShoppingImg, $sTargetTag ); ?>
			</div>
			
			<?php
			
			if ( $promotion ) {
				$oCart->clearPromotion();
			}
			
			Cart66Session::drop( 'terms_acceptance' );
			
			?>
			
		<?php endif;
		
	}
	
}



