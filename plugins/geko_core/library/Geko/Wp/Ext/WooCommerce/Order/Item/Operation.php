<?php

class Geko_Wp_Ext_WooCommerce_Order_Item_Operation extends Geko_Wp_Options_Operation
{
	
	protected $_sTitle = 'Order Item Export';
	
	protected $_sSubMenuPage = 'woocommerce';
	
	protected $_aOperations = array(
		'export_order_items' => array()
	);
	
	
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		wp_enqueue_style( 'geko-jquery-ui-wp' );
		
		wp_enqueue_script( 'geko-jquery-ui-datepicker' );
		
		return $this;
	}

	
	
	
	//// front-end display methods
	
	//
	protected function preWrapDiv() {
		
		$oAggOrderItems = $this->oneExt_WooCommerce_Order_Item_Query( array(
			'aggregate_mode' => TRUE
		), FALSE );
		
		$aJsonParams = array(
			'date' => array(
				'min' => Geko_Wp_Date::formatDateForRange( $oAggOrderItems->getTransactionMinDate() ),
				'max' => Geko_Wp_Date::formatDateForRange( $oAggOrderItems->getTransactionMaxDate() )
			)
		);
		
		?>
		<style type="text/css">

			input.short {
				width: 120px;
			}
			
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
			
		</style>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				var dmn = oParams.date.min;
				var dmx = oParams.date.max;
				
				$( '#transaction_min_date, #transaction_max_date' ).datepicker( {
					minDate: new Date( dmn[ 0 ], dmn[ 1 ], dmn[ 2 ] ),
					maxDate: new Date( dmx[ 0 ], dmx[ 1 ], dmx[ 2 ] ),
					appendText: '(yyyy/mm/dd)',
					dateFormat: 'yy/mm/dd'
				} );
				
			} );
			
		</script>
		<?php
	}
	
	
	
	
	//
	protected function formFieldsExportOrderItems() {
		
		$aProds = $this->newExt_WooCommerce_Product_Query( array(), FALSE );
		
		$aStatuses = wc_get_order_statuses();
		
		?>
		<p>Export order item information (as .xls file).</p>
		<p>
			<label>Filter by Product:</label>
			<select name="product_id">
				<option value="">-- ALL --</option>
				<?php echo $aProds->implode( array( '<option value="##ProductId##">##ProductName## (SKU: ##Sku##)</option>', "\n" ) ); ?>
			</select>
		</p>
		<p>
			<label>Filter by Status:</label>
			<select name="status">
				<option value="">-- ALL --</option>
				<?php foreach ( $aStatuses as $sKey => $sLabel ): ?>
					<option value="<?php echo $sKey; ?>"><?php echo $sLabel; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="transaction_min_date">Transaction Start Date:</label>
			<input type="text" id="transaction_min_date" name="transaction_min_date" />
		</p>
		<p>
			<label for="transaction_max_date">Transaction End Date:</label>
			<input type="text" id="transaction_max_date" name="transaction_max_date" />
		</p>
		<?php
	}
	
	
	//
	public function doActionExportOrderItems( $aOperation ) {
		
		$bContinue = TRUE;
		
		if ( $bContinue ) {
		
			$sTransMinDate = sprintf( '%s 00:00:00', str_replace( '/', '-', $_REQUEST[ 'transaction_min_date' ] ) );
			$sTransMaxDate = sprintf( '%s 23:59:59', str_replace( '/', '-', $_REQUEST[ 'transaction_max_date' ] ) );
			
			$aParams = array(
				'posts_per_page' => -1,
				'showposts' => -1,
				'add_item_fields' => TRUE,
				'transaction_min_date' => $sTransMinDate,
				'transaction_max_date' => $sTransMaxDate,
				'product_id' => $_REQUEST[ 'product_id' ],
				'status' => $_REQUEST[ 'status' ],
				'orderby' => 'transaction_date',
				'order' => 'DESC'
			);
			
			$oOrders = new Geko_Wp_Ext_WooCommerce_Order_Item_Query( $aParams, FALSE );
			
			$oExcel = new Geko_Wp_Ext_WooCommerce_Order_Item_Export();
			$oExcel->exportToExcel( $oOrders );
			
			die();
		}
		
	}	
	
	
	
}


