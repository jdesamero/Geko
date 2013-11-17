<?php

//
class Geko_Wp_Cart66_View_ReceiptPrintVersion extends Geko_Wp_Cart66_View
{
	
	
	//
	public function render( $data = NULL, $notices = TRUE, $minify = FALSE ) {
		
		$this->_sThisFile = __FILE__;
		
		
		
		$product = new Cart66Product();
		$order = $data[ 'order' ];
		
		
		$iOrderedOnTs = strtotime( $order->ordered_on );
		$sFormat = sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) );
		$sOrderDateTime = date( $sFormat, $iOrderedOnTs );

		
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		
		<!-- HEAD -->
		
		<head>
			
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			
			<title><?php $this->_e( 'Your Receipt' ); ?></title>
			
			<style type="text/css" media="print">
			/*<![CDATA[*/
				
				#print_button {
					display: none;
				}
				
			/*]]>*/
			</style>
			
			<style type="text/css" media="screen,print">
			/*<![CDATA[*/
				
				body {
					font-family: arial;     
					font-size: 12px;      
					color: black;   
				}       
				
				table {
					margin: 10px;   
				}
				
				h1 {
					font-size: 14px;
				}
				
				p {
					padding: 3px 0px;
				}
				
				#viewCartTable th,
				#viewCartTable td {
					padding: 5px;
				}  
				
				table .entry-details {
					width: 100%;
				}
				
				table .entry-details tbody {
					padding: 0px;
					margin: 0px;
					background-color: #fff;
				}
				
				#viewCartTable td .entry-view-field-name {
					font-weight: bold;
					margin: 0px;
				}
				
				#viewCartTable td .entry-view-field-value {
					padding-left: 25px !important;
					border: none !important;
				}
				
			/*]]>*/
			</style>
		
		</head>
		
		
		<!-- BODY -->
		
		<body>
			
			<h1><?php bloginfo( 'name' ); ?></h1>
			
			
			
			<!-- TABLE ONE -->
			
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="3"><p><strong><?php $this->_e( 'Order Number' ); ?>: <?php echo $order->trans_id ?></strong></p></td>
					<!--
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td align="left"><p><strong>Date: <?php echo date(get_option('date_format'), strtotime($order->ordered_on)); ?></strong></p></td>
					-->
				</tr>
				<?php if ( strlen( $order->bill_last_name ) > 2 ):
											
					?><tr>
						<td valign="top">
							<p>
								<strong><?php $this->_e( 'Billing Information' ); ?></strong><br />
								
								<?php echo $order->bill_first_name; ?> <?php echo $order->bill_last_name; ?><br />
								<?php echo $order->bill_address; ?><br />
								
								<?php if ( !empty( $order->bill_address2 ) ): ?>
									<?php echo $order->bill_address2; ?><br />
								<?php endif; ?>
								
								<?php if ( !empty( $order->bill_city ) ): ?>
									<?php echo $order->bill_city; ?> <?php echo $order->bill_state; ?>, <?php echo $order->bill_zip; ?><br />
								<?php endif; ?>
								
								<?php if ( !empty( $order->bill_country ) ): ?>
									<?php echo $order->bill_country; ?><br />
								<?php endif; ?>
							</p>
						</td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td valign="top">
							<p>
								<strong><?php $this->_e( 'Contact Information' ); ?></strong><br />
								<?php if ( !empty( $order->phone ) ): ?>
									Phone: <?php echo Cart66Common::formatPhone( $order->phone ); ?><br />
								<?php endif; ?>
								Email: <?php echo $order->email; ?><br />
								Date: <?php echo $sOrderDateTime; ?>
							</p>
						</td>
					</tr>
				<?php endif; ?>
				
				<tr>
					<td>
						<?php if ( $order->shipping_method != 'None' ): ?>
							<p>
								<strong><?php $this->_e( 'Shipping Information' ); ?></strong><br />
								
								<?php echo $order->ship_first_name; ?> <?php echo $order->ship_last_name; ?><br />
								<?php echo $order->ship_address; ?><br />
								
								<?php if ( !empty( $order->ship_address2 ) ): ?>
									<?php echo $order->ship_address2; ?><br />
								<?php endif; ?>
								
								<?php if ( $order->ship_city != '' ): ?>
									<?php echo $order->ship_city; ?> <?php echo $order->ship_state; ?>, <?php echo $order->ship_zip; ?><br />
								<?php endif; ?>
								
								<?php if ( !empty( $order->ship_country ) ): ?>
									<?php echo $order->ship_country ?><br />
								<?php endif; ?>
								
								<br /><em><?php $this->_e( 'Delivery via' ); ?>: <?php echo $order->shipping_method; ?></em><br />
							</p>
						<?php endif; ?>
					</td>
					
					<?php if ( strlen( $order->bill_last_name ) > 2 ): ?>
						
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						
					<?php else: ?>
						
						<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td valign="top">
							<p>
								<strong><?php $this->_e( 'Contact Information' ); ?></strong><br />
								<?php if ( !empty( $order->phone ) ): ?>
									Phone: <?php echo Cart66Common::formatPhone( $order->phone ); ?><br />
								<?php endif; ?>
								Email: <?php echo $order->email; ?><br />
								Date: <?php echo $sOrderDateTime; ?>
							</p>
						</td>
					
					<?php endif; ?>
				</tr>
				
				<?php if ( isset( $order->custom_field ) && ( $order->custom_field != '' ) ): ?>    
					<tr>
						<td colspan="3">
							<p>
								<strong><?php $this->echoVal( 'checkout_custom_field_label' ); ?></strong><br />
								<?php echo $order->custom_field; ?>
							</p>
						</td>
					</tr>
				<?php endif; ?>
			
			</table>
			
			
			
			<!-- TABLE TWO -->
			
			<table id="viewCartTable" cellspacing="0" cellpadding="0">
				
				<tr>
					<th style="text-align: left;"><?php $this->_e( 'Product' ); ?></th>
					<th style="text-align: center;"><?php $this->_e( 'Quantity' ); ?></th>
					<th style="text-align: left;"><?php $this->_e( 'Item Price' ); ?></th>
					<th style="text-align: left;"><?php $this->_e( 'Item Total' ); ?></th>
				</tr>
				
				<?php foreach ( $order->getItems() as $item ):
					
					$product->load( $item->product_id );
					$price = $item->product_price * $item->quantity;
					
					$sDescription = str_replace( "'", "&#039;", $item->description );
					
					?><tr>
						<td>
							<?php echo $sDescription; ?>
							<!-- <br/><a href="<?php $this->echoLink( $item, 'store/receipt', 'duid' ); ?>">Download</a> -->
						</td>
						<td style="text-align: center;"><?php echo $item->quantity; ?></td>
						<td><?php $this->echoCurr( $item->product_price ); ?></td>
						<td><?php $this->echoCurr( $item->product_price * $item->quantity ); ?></td>
					</tr>
					
					<?php if ( !empty( $item->form_entry_ids ) ):
						
						if ( class_exists( 'RGFormsModel' ) ):
							
							$entries = explode( ',', $item->form_entry_ids );
							
							foreach ( $entries as $entryId ):
								if ( RGFormsModel::get_lead( $entryId ) ): ?>
									<tr><td colspan="4"><div class="Cart66GravityFormDisplay"><?php echo Cart66GravityReader::displayGravityForm( $entryId ); ?></div></td></tr>
								<?php endif;
							endforeach;
						else: ?>
							<tr><td colspan="5" style="color: #955;">This order requires Gravity Forms in order to view all of the order information</td></tr>
						<?php endif;
						
					endif;
				
				endforeach; ?>
				
				<tr>
					<td class="noBorder" colspan="1">&nbsp;</td>
					<td class="noBorder" colspan="1" style='text-align: center;'>&nbsp;</td>
					<td class="noBorder" colspan="1" style='text-align: right; font-weight: bold;'><?php $this->_e( 'Subtotal' ); ?>:</td>
					<td class="noBorder" colspan="1" style="text-align: left; font-weight: bold;"><?php $this->echoCurr( $order->subtotal ); ?></td>
				</tr>
				
				<?php if ( ( 'None' != $order->shipping_method ) && ( 'Download' != $order->shipping_method ) ): ?>
					<tr>
						<td class="noBorder" colspan="1">&nbsp;</td>
						<td class="noBorder" colspan="1" style='text-align: center;'>&nbsp;</td>
						<td class="noBorder" colspan="1" style='text-align: right; font-weight: bold;'><?php $this->_e( 'Shipping' ); ?>:</td>
						<td class="noBorder" colspan="1" style="text-align: left; font-weight: bold;"><?php $this->echoCurr( $order->shipping ); ?></td>
					</tr>
				<?php endif; ?>
				
				<?php if ( $order->discount_amount > 0 ): ?>
					<tr>
						<td class="noBorder" colspan="2">&nbsp;</td>
						<td class="noBorder" colspan="1" style='text-align: right; font-weight: bold;'><?php $this->_e( 'Discount' ); ?>:</td>
						<td class="noBorder" colspan="1" style="text-align: left; font-weight: bold;">-&nbsp;<?php $this->echoCurr( $order->discount_amount ); ?></td>
					</tr>
				<?php endif; ?>
				
				<?php if ( $order->tax > 0 ): ?>
					<tr>
						<td class="noBorder" colspan="2">&nbsp;</td>
						<td class="noBorder" colspan="1" style='text-align: right; font-weight: bold;'><?php $this->_e( 'Tax' ); ?>:</td>
						<td class="noBorder" colspan="1" style="text-align: left; font-weight: bold;"><?php $this->echoCurr( $order->tax ); ?></td>
					</tr>
				<?php endif; ?>

				<tr>
					<td class="noBorder" colspan="2" style='text-align: center;'>&nbsp;</td>
					<td class="noBorder" colspan="1" style='text-align: right; font-weight: bold;'><?php $this->_e( 'Total' ); ?>:</td>
					<td class="noBorder" colspan="1" style="text-align: left; font-weight: bold;"><?php $this->echoCurr( $order->total ); ?></td>
				</tr>
			
			</table>
			
			<form>
				<input type="button" onClick="window.print();" name="print_button" id="print_button" value="Print Receipt" />
			</form>
		
		</body>
		
		
		</html>
		<?php
		
	}
	
}


