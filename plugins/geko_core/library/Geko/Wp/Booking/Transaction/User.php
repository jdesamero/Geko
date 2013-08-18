<?php

class Geko_Wp_Booking_Transaction_User extends Geko_Wp_Options_Manage
{
	
	protected $_sParentEntityClass = 'Wp_Member';

	protected $_sSubject = 'Transactions';
	protected $_sSlug = 'user-transactions';
	
	protected $_sObjectType = 'user';

	
	
	//// page display
	
	//
	public function displayPage() {
		
		?>
		<div class="wrap">

			<div id="icon-users" class="icon32"><br /></div>		
			<h2>User Transactions</h2>
			
			<?php Geko_Wp_Admin_Menu::showMenu( $this->getDetailsMenuHandle() ); ?>
			
			<style type="text/css">
				
				.res_table, .sub_table {
					border-collapse: collapse;
				}
				
				.res_table th,
				.res_table td {
					border-top: solid 1px lightgray;
					border-left: solid 1px lightgray;
					padding: 3px 9px;
				}
				
				.res_table th:last-child,
				.res_table td:last-child {
					border-right: solid 1px lightgray;			
				}
				
				.res_table tr:last-child td {
					border-bottom: solid 1px lightgray;
				}
				
				.res_table .right {
					text-align: right;
				}
	
				.res_table .nopadding {
					padding: 0;
				}
				
				.res_table .bold {
					font-weight: bold;
				}
				
				.sub_table td,
				.sub_table td:last-child {
					border-left: none;
					border-right: none;
				}
				
				.sub_table tr:first-child td {
					border-top: none;
				}
				
				.sub_table tr:last-child td {
					border-bottom: none;
				}
				
			</style>
			
			<?php $this->showTransactionsReport(); ?>
			
		</div>
		<?php
		
	}

	//
	public function showTransactionsReport() {
		
		global $wpdb;
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 't.details' )
			->field( "DATE_FORMAT( t.date_created, '%a - %b %e, %Y %l:%i %p' )", 'transaction_date' )
			->field( 'IF( t.transaction_type_id = 2, t.amount * -1, t.amount )', 'amount' )
			->from( $wpdb->geko_bkng_transaction, 't' )
			->where( 't.user_id = ?', $this->_iCurrentParentEntityId )
			->where( 't.status_id = 1' )
			->order( 't.date_created', 'DESC' )
		;
		
		$aFormat = array(
			'details' => array(
				'title' => 'Details'
			),
			'transaction_date' => array(
				'title' => 'Transaction Date',
				'td_class' => 'right'
			),
			'amount' => array(
				'title' => 'Amount',
				'td_class' => 'right',
				'sum' => TRUE
			)
		);
		
		$sTitle = 'Transactions for ' . $this->_oCurrentParentEntity->getFullName();
		
		$sTotalRow = '<tr>
			<td colspan="2" class="bold right">Total</td>
			<td class="bold right">##amount##</td>
		</tr>';
		
		$this->showTable( $oQuery, $aFormat, $sTitle, $sTotalRow );
		
	}
	
	//
	public function showTable( $oQuery, $aFormat = array(), $sTitle = '', $sTotalRow = '' ) {
		Geko_Wp_Booking_Report::getInstance()->showTable( $oQuery, $aFormat, $sTitle, $sTotalRow );
	}
	
}


