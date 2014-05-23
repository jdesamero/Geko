<?php

// listing
class Geko_Wp_EmailMessage_Recipient_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'rcpt_id';
	
	protected $_sSubject = 'Recipients';
	protected $_sDescription = 'Email message recipients.';
	protected $_sType = 'emsg-rcpt';
	protected $_sActionTarget = 'Geko_Wp_EmailMessage_Manage';
	
	protected $_aCustomActions = array(
		'send_test' => array()
	);
	
	protected $_aJsParams = array(
		'row_template' => array()
	);
	
	protected $_bSubMainFields = TRUE;
	protected $_bHasDisplayMode = FALSE;
	
	
	
	//// init
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		$sTableName = 'geko_emsg_recipients';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'r' )
			->fieldBigInt( 'rcpt_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'name' )
			->fieldVarChar( 'email', array( 'size' => 255 ) )
			->fieldBool( 'active' )
			->fieldBigInt( 'emsg_id', array( 'unsgnd', 'key' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	// create table
	public function install() {
		$this->createTable( $this->getPrimaryTable() );
		return $this;
	}
	
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {			
			wp_enqueue_script( 'geko_wp_emailmessage_recipient_manage' );
		}
		
		return $this;
	}
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {
			
			$oUrl = new Geko_Uri();
			$sThisUrl = strval( $oUrl );
			
			$sTheAction = sprintf( 'edit%s', $this->_sType );
			$oUrl
				->setVar( 'action', $sTheAction )
				->setVar( 'send_test', 1 )
			;
			
			$sSendTestLink = strval( $oUrl );
			$sSendTestLink = htmlspecialchars_decode( wp_nonce_url( $sSendTestLink, sprintf( '%s%s', $this->_sActionTarget, $sTheAction ) ) );
			
			$aParams = array(
				'test_link' => $sSendTestLink,
				'group_name' => $this->_sType
			);
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aParams ); ?>;
					
					$.gekoWpEmailMessageRecipientManage( oParams );
					
				} );
				
			</script><?php
		}
		
		return $this;
	}
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	//// page display
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		if ( 'pre' == $sSection ):
			
			?><tr>
				<th><label for="emsg_recipients">Recipients</label></th>
				<td class="multi_row emsg-rcpt">
					<table>
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Email Address</th>
								<th>Active</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr class="row" _row_template="emsg-rcpt">
								<td><a href="#" class="del_row">Del</a></td>
								<td><input type="text" id="emsg-rcpt[][name]" name="emsg-rcpt[][name]" class="recipient_name" /></td>
								<td><input type="text" id="emsg-rcpt[][email]" name="emsg-rcpt[][email]" class="recipient_email" /></td>						
								<td><input type="checkbox" id="emsg-rcpt[][active]" name="emsg-rcpt[][active]" value="1" /></td>						
								<td><a href="#" class="recipients_send_test">Send Test</a></td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="button" value="Add" class="add_row" class="button-primary" /></p>					
				</td>
			</tr><?php
		
		endif;
		
	}
	
	
	
	//// crud methods

	//
	public function modifySubInsertData( $aValues, $aParams ) {
		if ( !isset( $aValues[ 'active' ] ) ) $aValues[ 'active' ] = 0;
		return $aValues;
	}
		
	//
	public function modifySubUpdateData( $aValues, $aParams, $oEntity ) {
		return $this->modifySubInsertData( $aValues, $aParams );
	}
	
	
	
	//// action methods
	
	//
	public function doSendTestAction( $aParams ) {
		
		$oEntity = $this->getSubOptionParentInstance()->getCurrentEntity();
		
		$oDeliver = new Geko_Wp_EmailMessage_Delivery( $oEntity );
		$oDeliver
			->setMode( 'test' )
			->addRecipient( $_GET[ 'email' ], $_GET[ 'name' ] )
			->send()
		;
		
		echo Zend_Json::encode( array( 'status' => 1 ) );
		
		die();
	}
	
	
	
}

