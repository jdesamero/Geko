<?php

// listing
class Geko_Wp_Language_String_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = 'str_id';
	
	protected $_sSubject = 'Strings';
	protected $_sDescription = 'String values that can be translated.';
	protected $_sType = 'lang-str';
	protected $_sPrefix = 'geko_lang_str';
	
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
		
		$sTableName = 'geko_lang_strings';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 's' )
			->fieldBigInt( 'str_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongtext( 'val' )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'lang_id', array( 'unsgnd' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;	
	}
	
	
	// create table
	public function install() {	
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	//// translate string methods
	
	// retrieve list of translatable strings from the template
	public function getTranslateKeys() {
		
		$oTmpl = Geko_Wp_Template::getInstance();
		return $oTmpl->getTemplateValues( array(
			'prefix' => sprintf( '%s-translate_keys', $this->getPrefix() ),
			'callback' => array( $oTmpl, 'introspectTemplateValuesCallback' ),
			'introspect_callback' => array( $this, 'tsIntrospectCallback' )
		) );
		
	}
	
	//
	public function tsIntrospectCallback( $mRet, $oLayout, $aParams ) {
		
		if ( !is_array( $mRet ) ) $mRet = array(); 
		
		/* /
		// DEPRACATED: Wp_Layout::getTranslatedValues()
		if ( is_array( $aRes = $oLayout->getTranslatedValues() ) ) {
			foreach ( $aRes as $sKey => $b ) {
				if ( $sKey = trim( $sKey ) ) {
					$mRet[ Geko_Wp_Options_MetaKey::getId( $sKey ) ] = $sKey;
				}
			}
		}
		/* */
		
		if ( is_array( $aRes = $oLayout->_getLabels() ) ) {
			foreach ( $aRes as $sValue ) {
				if ( $sValue = trim( $sValue ) ) {
					$mRet[ Geko_Wp_Options_MetaKey::getId( $sValue ) ] = $sValue;
				}
			}
		}
		
		return $mRet;
	}

	
	
	//// page display
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		$aTranslateKeys = $this->getTranslateKeys();
		natcasesort( $aTranslateKeys );
		// print_r( $aTranslateKeys );
		
		?><tr>
			<th><label for="lang_lang-str">Translations</label></th>
			<td class="multi_row lang-str">
				<table>
					<thead>
						<tr>
							<th></th>
							<th>Key</th>
							<th>Translation</th>
						</tr>
					</thead>
					<tbody>
						<tr class="row" _row_template="lang-str">
							<td><a href="#" class="del_row">Del</a></td>
							<td><select id="lang-str[][mkey_id]" name="lang-str[][mkey_id]" class="translation_key">
								<?php foreach( $aTranslateKeys as $iId => $sKey ): ?>
									<option value="<?php echo $iId; ?>"><?php echo $sKey; ?></option>
								<?php endforeach; ?>
							</select></td>
							<td><textarea id="lang-str[][val]" name="lang-str[][val]" class="translation_value"></textarea></td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><input type="button" value="Add" class="add_row" class="button-primary" /></p>					
			</td>
		</tr><?php
		
	}
	
	
	
}


