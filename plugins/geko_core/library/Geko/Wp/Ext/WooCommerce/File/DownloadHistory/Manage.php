<?php

// download history implementation that does not rely on orders
class Geko_Wp_Ext_WooCommerce_File_DownloadHistory_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_sSubject = 'WooCommerce File Download History';
	
	protected $_bDisableAttachPage = TRUE;
	
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_wc_file_download_history', 'h' )
			->fieldBigInt( 'dlh_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'file_id', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'user_id', array( 'unsgnd', 'notnull' ) )
			->fieldDateTime( 'date_created' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
	
	
}

