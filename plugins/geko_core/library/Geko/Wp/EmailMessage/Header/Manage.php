<?php

// listing
class Geko_Wp_EmailMessage_Header_Manage extends Geko_Wp_Options_Manage
{

	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = 'hdr_id';
	
	protected $_sSubject = 'Custom Headers';
	protected $_sDescription = 'Custom headers to add to the email message.';
	protected $_sType = 'emsg-hdr';
	
	protected $_aJsParams = array(
		'row_template' => array()
	);
	
	protected $_bSubMainFields = TRUE;
	protected $_bHasDisplayMode = FALSE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_emsg_header', 'h' )
			->fieldBigInt( 'hdr_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'name', array( 'size' => 256 ) )			// "key" is a reserved MySQL Word
			->fieldLongText( 'val' )
			->fieldBool( 'multi' )
			->fieldBigInt( 'emsg_id', array( 'unsgnd', 'key' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
		
	}
	
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	//// page display
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		if ( 'pre' == $sSection ):
		
			?><tr>
				<th><label for="emsg-hdr">Custom Headers</label></th>
				<td class="multi_row emsg-hdr">
					<table>
						<thead>
							<tr>
								<th></th>
								<th>Header</th>
								<th>Value</th>
								<th>Multiple</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr class="row" _row_template="emsg-hdr">
								<td><a href="#" class="del_row">Del</a></td>
								<td><input type="text" id="emsg-hdr[][name]" name="emsg-hdr[][name]" class="emsg-hdr_name" /></td>
								<td><input type="text" id="emsg-hdr[][val]" name="emsg-hdr[][val]" class="emsg-hdr_val" /></td>
								<td><input type="checkbox" id="emsg-hdr[][multi]" name="emsg-hdr[][multi]" class="emsg-hdr_multi" value="1" /></td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="button" value="Add" class="add_row" class="button-primary" /></p>					
				</td>
			</tr><?php
			
		endif;
	}

	
	
}

