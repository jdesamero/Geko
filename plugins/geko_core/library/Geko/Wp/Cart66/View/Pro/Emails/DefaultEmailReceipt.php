<?php

//
class Geko_Wp_Cart66_View_Pro_Emails_DefaultEmailReceipt extends Geko_Wp_Cart66_View
{
	
	
	//
	public function render() {
		
		$this->_sThisFile = __FILE__;
		
		$data = $this->getParam( 'data' );
		$notices = $this->getParam( 'notices' );
		$minify = $this->getParam( 'minify' );
		
		
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		$html = $data[ 1 ];
		$test = $data[ 2 ];
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		
		
		
		if ( $test ) {
			
			$subject = $this->_t( 'TEST - Email Receipt' );
			$order = new Geko_Wp_Cart66_Mock_Order();
			
		} else {
			
			$subject = $this->getVal( 'receipt_subject' );
			$order = $data[ 0 ];
		}
		
		
		
		if ( CART66_PRO && $html ) {
			$this->outputHtml( $subject, $order );
		} else {
			$this->outputText( $subject, $order );
		}
	

	}
	
	
	
	//
	public function outputHtml( $subject, $order ) {
		
		$iOrderTs = strtotime( $order->ordered_on );
		
		$sOrderDate = date( get_option( 'date_format' ), $iOrderTs );
		$sOrderDateTime = date( sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) ), $iOrderTs );
		
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		
		<!-- HEAD -->
		
		<head>
			
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			
			<title><?php echo $subject; ?></title>
			
			<style type="text/css">
				
				span.yshortcuts {
					color: #000;
					background-color: none;
					border: none;
				}
				
				span.yshortcuts:hover,
				span.yshortcuts:active,
				span.yshortcuts:focus {
					color: #000;
					background-color: none;
					border: none;
				}
				
				@media only screen and (max-device-width: 480px) {
				
				}
				
				@media only screen and (min-device-width: 768px) and (max-device-width: 1024px)  {
				
				}
				
				
				.table_title {
					text-transform: uppercase;
					color: #333;
					background-color: #f1f1f1;
					border-top :1px solid #fff;
					border-bottom: 1px solid #dfdfdf;
					padding: 7px 7px 8px;
					text-align: left;
					line-height: 14px;
					font-size: 14px;
					font-weight: bold;
				}
				
				.table_border_top {
					border-top: 1px solid #fff;
					border-bottom: 1px solid #dfdfdf;
					color: #555;
					font-size: 12px;
					padding: 4px 7px;
					vertical-align: top;
				}
				
				.table_td_bg {
					color: #555;
					font-size: 12px;
					padding: 4px 7px;
					vertical-align: top;
				}

				.align_left {
					text-align: left;
				}
				
				.align_center {
					text-align: center;
				}
				
				.align_right {
					text-align: right;
				}
				
			</style>
			
		</head>
		
		
		<!-- BODY -->
		
		<body style="font-family: Arial, Verdana, sans-serif;"><div id="body_style">
			
			<!-- Start Main Table -->
			<table width="100%" height="100%"  cellpadding="0" cellspacing="0" style="padding: 20px 0px 20px 0px" bgcolor="#ffffff"><tr align="center"><td>
						
				<!-- Start Header -->
				<table width="562" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="color: #333; font-weight:bold;  padding: 16px 0px 16px 14px; font-family: Arial, Verdana, sans-serif; ">
					<tr>
						<td>
							<span style="font-size: 20px;"><?php $this->_e( 'Order Number' ); ?>: <?php echo $order->trans_id; ?></span>
						</td>
						<td style="font-weight:normal;font-size: 11px;">
							<span style="font-weight:bold;"><?php $this->_e( 'Purchased' ); ?></span>
							<br /><?php echo $sOrderDate; ?>
						</td>
					</tr>
				</table>
				<!-- End Header -->
	
				<!-- Start Message Intro -->
				<?php if ( $this->getVal( 'receipt_message_intro' ) ): ?>
					<table width="562" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="font-family: Arial, Verdana, sans-serif; padding: 10px 25px 0px 15px; font-size: 14px; color:#333;">
						<tr>
							<td><?php $this->echoVal( 'receipt_html_email' ); ?></td>
						</tr>
					</table>
				<?php endif; ?>
				<!-- End Message Intro -->
				
				<!-- Start Ribbon -->
				<table cellpadding="0" cellspacing="0"  width="562"  bgcolor="#f9f9f9">
					<tr>
						<td bgcolor="#f9f9f9" style="font-family: Arial, Verdana, sans-serif; padding: 10px 25px 0px 15px; font-size: 12px; color:#333;width:48%;text-align:left;vertical-align:top;" >
							<span style="text-transform: uppercase; font-size: 18px; font-weight: bold;"><?php $this->_e( 'Billing Information' ); ?></span>
							<br />
							<br />
							<span style="font-weight: bold;">
								
								<?php echo $order->bill_first_name; ?> <?php echo $order->bill_last_name; ?><br />
								<?php echo $order->bill_address; ?><br />
	
								<?php if(!empty($order->bill_address2)): ?>
									<?php echo $order->bill_address2; ?><br />
								<?php endif; ?>
	
								<?php echo $order->bill_city; ?> <?php echo $order->bill_state; ?><?php echo $order->bill_zip != null ? ',' : ''; ?> <?php echo $order->bill_zip; ?><br />
								<?php echo $order->bill_country; ?><br />
								
								<?php if(is_array($additional_fields = maybe_unserialize($order->additional_fields)) && isset($additional_fields['billing'])): ?><br />
									<?php foreach($additional_fields['billing'] as $af): ?>
										<?php echo $af['label']; ?>: <?php echo $af['value']; ?><br />
									<?php endforeach; ?>
								<?php endif; ?>
								
							</span>
						</td>
						<td bgcolor="#f9f9f9" style="font-family: Arial, Verdana, sans-serif; padding: 10px 25px 0px 15px; font-size: 12px; color:#333;width:51%;text-align:left;vertical-align:top;" >
							<span style="text-transform: uppercase; font-size: 18px; font-weight: bold;"><?php $this->_e( 'Contact Information' ); ?></span>
							<br />
							<br />
							<span style="font-weight: bold;">
								
								<?php if ( !empty( $order->phone ) ): ?>
									<?php echo $this->_t( 'Phone' ); ?>: <?php echo Cart66Common::formatPhone( $order->phone ); ?><br />
								<?php endif; ?>
								
								<?php $this->_e( 'Email' ); ?>: <?php echo $order->email; ?><br />
								<?php $this->_e( 'Date' ); ?>: <?php echo $sOrderDateTime; ?><br /><br />
								
								<?php if(is_array($additional_fields = maybe_unserialize($order->additional_fields)) && isset($additional_fields['payment'])): ?><br />
									<?php foreach($additional_fields['payment'] as $af): ?>
										<?php echo $af['label']; ?>: <?php echo $af['value']; ?><br />
									<?php endforeach; ?>
								<?php endif; ?>
							</span>
						</td>
					</tr>

					<?php if($order->shipping_method != 'None'): ?>
						<tr>
							<td bgcolor="#f9f9f9" style="font-family: Arial, Verdana, sans-serif; padding: 10px 25px 0px 15px; font-size: 12px; color:#333;text-align:left;vertical-align:top;">
								
								<?php if($order->hasShippingInfo()): ?>
									
									<span style="text-transform: uppercase; font-size: 18px; font-weight: bold;"><?php $this->_e( 'Shipping Information' ); ?></span>
									<br />
									<br />

									<span style="font-weight:bold;">
										
										<?php echo $order->ship_first_name; ?> <?php echo $order->ship_last_name; ?><br />
										<?php echo $order->ship_address; ?><br/>
	
										<?php if(!empty($order->ship_address2)): ?>
											<?php echo $order->ship_address2; ?><br />
										<?php endif; ?>
										
										<?php if($order->ship_city != ''): ?>
											<?php echo $order->ship_city ?> <?php echo $order->ship_state ?>, <?php echo $order->ship_zip ?><br />
										<?php endif; ?>
										
										<?php if(!empty($order->ship_country)): ?>
											<?php echo $order->ship_country ?><br />
										<?php endif; ?>
										
										<?php if(is_array($additional_fields = maybe_unserialize($order->additional_fields)) && isset($additional_fields['shipping'])): ?><br />
											<?php foreach($additional_fields['shipping'] as $af): ?>
												<?php echo $af['label']; ?>: <?php echo $af['value']; ?><br />
											<?php endforeach; ?>
										<?php endif; ?>
										
									</span>
									
									<br /><em><?php $this->_e( 'Delivery via' ); ?>: <?php echo $order->shipping_method ?></em><br />
									
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
	
	
					<?php if(isset($order->custom_field) && $order->custom_field != ''): ?>
						<tr>
							<td bgcolor="#f9f9f9" style="font-family: Arial, Verdana, sans-serif; padding: 10px 25px 0px 15px; font-size: 12px; color:#333;text-align:left;vertical-align:top;" colspan="2">
								<?php if ( $this->getVal( 'checkout_custom_field_label' ) ): ?>
									<strong><?php $this->echoVal( 'checkout_custom_field_label' ); ?></strong>
								<?php else: ?>
									<strong><?php $this->_e( 'Enter any special instructions you have for this order:' ); ?></strong>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td bgcolor="#f9f9f9" style="font-family: Arial, Verdana, sans-serif; padding: 10px 25px 0px 15px; font-size: 12px; color:#333;text-align:left;vertical-align:top;" colspan="2">
								<?php echo $order->custom_field; ?>
							</td>
						</tr>
					<?php endif; ?>
					
					<tr>
						<td bgcolor="#f9f9f9" width="562" height="13"></td>
					</tr>
				
				</table>
				<!-- End Ribbon -->
				
				<div style="margin-bottom:1.5714em;margin-top:1.5714em;">
	
					<!-- Start Products Table  -->
					<table cellpadding="0" cellspacing="0" width="562" bgcolor="#FFFFFF" style="border:1px solid #dfdfdf;background-color:#f9f9f9;-webkit-border-radius:3px;-moz-border-radius:3px;-ms-border-radius:3px;-o-border-radius:3px;border-radius:3px;border-spacing:0;clear:both;">
						
						<tr>
							<td class="table_title"><?php $this->_e( 'Product' ); ?></td>
							<td class="table_title align_center"><?php $this->_e( 'Quantity' ); ?></td>
							<td class="table_title align_right"><?php $this->_e( 'Item Price' ); ?></td>
							<td class="table_title align_right"><?php $this->_e( 'Item Total' ); ?></td>
						</tr>
						
						<?php $hasDigital = FALSE; ?>
						
						<?php foreach( $order->getItems() as $item ):
							
							$product = new Cart66Product();
							$product->load( $item->product_id );
							
							$price = $item->product_price * $item->quantity;
							
							?>
							<tr>
								<td class="table_border_top">
									
									<?php if ( $this->getVal( 'display_item_number_receipt' ) ): ?>
										<?php echo $item->item_number; ?>
									<?php endif; ?>
									
									<b><?php echo $item->description; ?></b>
									
									<?php if ( $product->isDigital() ): ?>
										<br /><a href="<?php $this->echoLink( $item, 'store/receipt', 'duid' ); ?>"><?php $this->_e( 'Download' ); ?></a>
									<?php endif; ?>
									
								</td>
								<td class="table_border_top align_center">
									<?php echo $item->quantity; ?>
								</td>
								<td class="table_border_top align_right">
									<?php $this->echoCurr( $item->product_price ); ?>
								</td>
								<td class="table_border_top align_right">
									<?php $this->echoCurr( $price ); ?>
								</td>
							</tr>
							<?php
							
							if ( !empty( $item->form_entry_ids ) && class_exists( 'RGFormsModel' ) ):
								$entries = explode( ',', $item->form_entry_ids );
								foreach ( $entries as $entryId ):
									if ( RGFormsModel::get_lead( $entryId ) ):
										?><tr><td colspan="4" style="background-color:#ffffff;"><div class="Cart66GravityFormDisplay"><?php echo Cart66GravityReader::displayGravityForm( $entryId, FALSE, TRUE ); ?></div></td></tr><?php
									endif;
								endforeach;
							endif; ?>
						<?php endforeach; ?>
						
						<!-- Start Subtotal -->
						<tr>
							<td colspan="3" class="table_td_bg align_right"><?php $this->_e( 'Subtotal' ); ?></td>
							<td class="table_td_bg align_right"><?php $this->echoCurr( $order->subtotal ); ?></td>
						</tr>
						<!-- End Subtotal -->
						
						<?php if ( $order->shipping_method != 'None' ): ?>
							
							<!-- Start Shipping -->
							<tr>
								<td colspan="3" class="table_td_bg align_right"><?php $this->_e( 'Shipping' ); ?></td>
								<td class="table_td_bg align_right"><?php $this->echoCurr( $order->shipping ); ?></td>
							</tr>
							<!-- End Shipping -->
							
						<?php endif; ?>
		
						<?php if ( $order->discount_amount > 0 ): ?>
		
							<!-- Start Coupon -->
							<tr>
								<td colspan="3" class="table_td_bg align_right"><?php $this->_e( 'Discount' ); ?></td>
								<td class="table_td_bg align_right">-&nbsp;<?php $this->echoCurr( $order->discount_amount ); ?></td>
							</tr>
							<!-- End Coupon -->
							
						<?php endif;?>
		
						<?php if($order->tax > 0): ?>
		
							<!-- Start Tax -->
							<tr>
								<td colspan="3" class="table_td_bg align_right"><?php $this->_e( 'Tax' ); ?></td>
								<td class="table_td_bg align_right"><?php $this->echoCurr( $order->tax ); ?></td>
							</tr>
							<!-- End Tax -->
							
						<?php endif; ?>
		
						<!-- COUPON & TAX -->
		
						<!-- Start Grand Total -->
						<tr>
							<td colspan="3" class="table_td_bg align_right"><?php $this->_e( 'Total' ); ?></td>
							<td class="table_td_bg align_right"><?php $this->echoCurr( $order->total ); ?></td>
						</tr>
						<!-- End Grand Total -->
						
						<?php if ( $order->notes ): ?>
							<tr><td colspan="4" class="table_td_bg table_border_top">&nbsp;<br />&nbsp;<br /></td></tr>
							<tr><th colspan="4" class="table_td_bg table_border_top align_left"><?php $this->_e( 'Notes' ); ?>:</th></tr>
							<tr><td colspan="4" class="table_td_bg "><?php echo nl2br( $order->notes ); ?></td></tr>
						<?php endif; ?>
						
					</table>
					<!-- End Products Table -->
					
				</div>
				
				<!-- Start Footer -->
				<table cellpadding="0" cellspacing="0" width="562" height="100">
					<tr>
						<?php $link = $this->getLink( $order, 'store/receipt', 'ouid' ); ?>
						<td bgcolor="#f9f9f9" style="font-size: 11px; font-family: Arial, Verdana, sans-serif; color:#333; padding-left: 15px; width:350px;">
							<?php if ( $hasDigital ): ?>
								<span style="text-transform: uppercase; font-size: 16px; font-weight: bold;"><?php $this->_e( 'View Receipt Online and Download Order' ); ?></span>
								<br /><br />
								<?php $this->_e( 'Click the link below to view your receipt online and download your order' ); ?>.<br />
								<a href="<?php echo $link; ?>" style="color:#333"><?php echo $link; ?></a><br />
							<?php else: ?>
								<span style="text-transform: uppercase; font-size: 16px; font-weight: bold;"><?php _e('View Receipt Online', 'cart66'); ?></span>
								<br /><br />
								<?php $this->_e( 'Click the link below to view your receipt online' ); ?>.<br />
								<a href="<?php echo $link; ?>" style="color:#333"><?php echo $link; ?></a><br />
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td bgcolor="#f9f9f9" height="20"></td>
					</tr>
				</table>
				<!-- End Footer -->
				
			</td></tr></table>
			<!-- End Main Table -->
			
		</div></body>
		
		</html>
		<?php
	}
		
	
	
	//
	public function outputText( $subject, $order ) {
		
		$msg = sprintf( "%s: %s\n\n", $this->_t( 'ORDER NUMBER' ), $order->trans_id );
		
		$hasDigital = FALSE;
		
		if ( $order && ( $order instanceof Geko_Wp_Cart66_Mock_Order ) ) {
			$product = new Geko_Wp_Cart66_Mock_Product();
		} else {
			$product = new Cart66Product();
		}
		
		foreach ( $order->getItems() as $item ) {
			
			$product->load( $item->product_id );
				
			if ( $product->isDigital() ) {
				$hasDigital = TRUE;
			}
			
			$price = $item->product_price * $item->quantity;
			// $msg .= sprintf( '%s: %s %s', $this->_t( 'Item' ), $item->item_number, $item->description );
			$msg .= sprintf( '%s: ', $this->_t( 'Item' ) );
			
			if ( $this->getVal( 'display_item_number_receipt' ) ) {
				$msg .= sprintf( '%s ', $item->item_number );
			}
			
			$msg .= sprintf( "%s\n", $item->description );
			
			if ( $product->isDigital() ) {				
				$msg .= sprintf( "%s\n\n", $this->getLink( $item, 'store/receipt', 'duid' ) );
			}
			
			if ( $item->quantity > 1 ) {
				$msg .= sprintf( "%s: %d\n", $this->_t( 'Quantity' ), $item->quantity );
			}
			
			$msg .= sprintf( "%s: %s\n", $this->_t( 'Item Price' ), $this->getCurr( $item->product_price, FALSE ) );
			$msg .= sprintf( "%s: %s\n\n", $this->_t( 'Item Total' ), $this->getCurr( $item->product_price * $item->quantity, FALSE ) );
			
			if ( $product->isGravityProduct() ) {
				$msg .= Cart66GravityReader::displayGravityForm( $item->form_entry_ids, TRUE );
			}
		}
		
		
		if ( ( $order->shipping_method != 'None' ) && ( $order->shipping_method != 'Download' ) ) {
			$msg .= sprintf( "%s: %s\n", $this->_t( 'Shipping' ), $this->getCurr( $order->shipping, FALSE ) );
		}
		
		if ( $order->discount_amount > 0 ) {
			$msg .= sprintf( "%s: - %s\n", $this->_t( 'Discount' ), $this->getCurr( $order->discount_amount, FALSE ) );
		}
		
		if ( $order->tax > 0 ) {
			$msg .= sprintf( "%s: %s\n", $this->_t( 'Tax' ), $this->getCurr( $order->tax, FALSE ) );
		}
		
		$msg .= sprintf( "\n%s: %s\n", $this->_t( 'TOTAL' ), $this->getCurr( $order->total, FALSE ) );
		
		if ( ( $order->shipping_method != 'None' ) && ( $order->shipping_method != 'Download' ) ) {
			
			$msg .= sprintf( "\n\n%s\n\n", $this->_t( 'SHIPPING INFORMATION' ) );
			
			$msg .= sprintf( "%s %s\n%s\n", $order->ship_first_name, $order->ship_last_name, $order->ship_address );
			
			if ( !empty( $order->ship_address2 ) ) {
				$msg .= sprintf( "%s\n", $order->ship_address2 );
			}
			
			$msg .= sprintf( "%s %s %s\n%s\n", $order->ship_city, $order->ship_state, $order->ship_zip, $order->ship_country );
			
			if ( is_array( $additional_fields = maybe_unserialize( $order->additional_fields ) ) && isset( $additional_fields[ 'shipping' ] ) ) {
				foreach ( $additional_fields[ 'shipping' ] as $af ) {
					$msg .= sprintf( "%s: %s\n", html_entity_decode( $af[ 'label' ] ), $af[ 'value' ] );
				}
			}
			
			$msg .= sprintf( "\n%s: %s\n", $this->_t( 'Delivery via' ), $order->shipping_method );
		}
		
		$msg .= sprintf( "\n\n%s\n\n", $this->_t( 'BILLING INFORMATION' ) );
		
		$msg .= sprintf( "%s %s\n%s\n", $order->bill_first_name, $order->bill_last_name, $order->bill_address );
		
		if ( !empty( $order->bill_address2 ) ) {
			$msg .= $order->bill_address2 . "\n";
		}
		
		$msg .= $order->bill_city . ' ' . $order->bill_state;
		$msg .= $order->bill_zip != null ? ', ' : ' ';
		$msg .= $order->bill_zip . "\n" . $order->bill_country . "\n";
		
		if ( is_array( $additional_fields = maybe_unserialize( $order->additional_fields ) ) && isset( $additional_fields[ 'billing' ] ) ) {
			foreach( $additional_fields[ 'billing' ] as $af ) {
				$msg .= html_entity_decode( $af[ 'label' ] ) . ': ' . $af[ 'value' ] . "\n";
			}
		}
		
		if ( !empty( $order->phone ) ) {
			$phone = Cart66Common::formatPhone( $order->phone );
			$msg .= "\n" . $this->_t( 'Phone' ) . ": $phone\n";
		}
		
		if ( !empty( $order->email ) ) {
			$msg .= $this->_t( 'Email' ) . ': ' . $order->email . "\n";
		}
		
		if ( is_array( $additional_fields = maybe_unserialize( $order->additional_fields ) ) && isset( $additional_fields[ 'payment' ] ) ) {
			foreach( $additional_fields[ 'payment' ] as $af ) {
				$msg .= html_entity_decode( $af[ 'label' ] ) . ': ' . $af[ 'value' ] . "\n";
			}
		}
		
		if ( isset( $order->custom_field ) && ( '' != $order->custom_field ) ) {
			if ( $this->getVal( 'checkout_custom_field_label' ) ) {
				$msg .= "\n" . $this->getVal( 'checkout_custom_field_label' );
			} else {
				$msg .= "\n" . $this->_t( 'Enter any special instructions you have for this order:' );
			}
			$msg .= "\n" . $order->custom_field . "\n";
		}
		
		$link = $this->getLink( $order, 'store/receipt', 'ouid' );
		
		if ( $hasDigital ) {
			$msg .= "\n" . $this->_t( 'DOWNLOAD LINK' ) . "\n" . $this->_t( 'Click the link below to download your order.' ) . "\n$link";
		} else {
			$msg .= "\n" . $this->_t( 'VIEW RECEIPT ONLINE' ) . "\n" . $this->_t( 'Click the link below to view your receipt online.' ) . "\n$link";
		}
		
		$msgIntro = $this->getVal( 'receipt_intro' ) && !$this->getVal( 'enable_advanced_notifications' ) ? $this->getVal( 'receipt_intro' ) : '';
		$msgIntro .= $this->getVal( 'receipt_message_intro' ) && $this->getVal( 'enable_advanced_notifications' ) ? $this->getVal( 'receipt_plain_email' ) : '';
		
		$msg = $msgIntro . " \n----------------------------------\n\n" . $msg;
		echo $msg;
		
	}
	
	
	
}


