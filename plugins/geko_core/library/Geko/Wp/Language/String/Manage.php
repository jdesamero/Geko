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
	
	protected $_aTransCache = NULL;
	protected $_aTransHash = NULL;
	
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
			->fieldBigInt( 'trans_str_id', array( 'unsgnd' ) )
			->fieldChar( 'trans_key', array( 'key', 'size' => 32 ) )
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
		
		
		if ( is_array( $aRes = $oLayout->_getLabels() ) ) {
			
			foreach ( $aRes as $sValue ) {
				
				if ( $sValue = trim( $sValue ) ) {
					
					$iTransStrId = $this->getTransId( $sValue );	// commit value to db
					$mRet[ $iTransStrId ] = $sValue;
				}
			}
			
		}
		
		
		return $mRet;
	}
	
	
	
	
	
	//// translation methods
	
	
	// load trans cache from db
	public function getTransCache() {
		
		if ( NULL === $this->_aTransCache ) {
			
			$oMng = Geko_Wp_Language_Manage::getInstance()->init();
			
			$aQuery = new Geko_Wp_Language_String_Query( array(
				'lang_id' => $oMng->getLangId()		// default lang_id
			), FALSE );
			
			//
			foreach ( $aQuery as $oLangStr ) {
				$this->_aTransCache[ $oLangStr->getId() ] = $oLangStr->getContent();
				$this->_aTransHash[ $oLangStr->getTransKey() ] = $oLangStr->getId();
			}
		}
		
		return $this->_aTransCache;
	}
	
	//
	public function getTransHash() {
		
		$this->getTransCache();			// init trans hash/cache arrays
		
		return $this->_aTransHash;
	}
	
	// get the translation id (trans_str_id)
	public function getTransId( $sValue ) {
		
		if ( $sValue = trim( $sValue ) ) {
		
			$this->getTransCache();			// init trans hash/cache arrays
			
			$sTransKey = md5( $sValue );
			
			if ( !( $iTransStrId = $this->_aTransHash[ $sTransKey ] ) ) {
				
				$oMng = Geko_Wp_Language_Manage::getInstance()->init();
				
				// commit to db
				$aRet = $this->doAddAction( array(), array(
					'val' => $sValue,
					'lang_id' => $oMng->getLangId(),
					'trans_key' => $sTransKey
				) );
				
				$iTransStrId = $aRet[ 'entity_id' ];
				
				// update cache
				$this->_aTransCache[ $iTransStrId ] = $sValue;
				$this->_aTransHash[ $sTransKey ] = $iTransStrId;
			}
			
			return $iTransStrId;
		}
		
		return NULL;
	}
	
	// get the translation id (trans_str_id)
	public function getTransStr( $iTransStrId ) {
		
		$this->getTransCache();			// init trans hash/cache arrays
		
		return $this->_aTransCache[ $iTransStrId ];
	}
	
	
	
	
	
	//// page display
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		$oMng = Geko_Wp_Language_Manage::getInstance()->init();
			
		$aTranslateKeys = $this->getTranslateKeys();
		natcasesort( $aTranslateKeys );
		// print_r( $aTranslateKeys );
		
		if ( $oEntity->getId() != $oMng->getLangId() ): ?>
			
			<tr>
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
								<td><select id="lang-str[][trans_str_id]" name="lang-str[][trans_str_id]" class="translation_key">
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
			</tr>
		
		<?php else: ?>
			
			<tr>
				<th><label>Translatable Values</label></th>
				<td>
					<ul class="geko-ul">
						<?php foreach( $aTranslateKeys as $sKey ): ?>
							<li><?php echo htmlspecialchars( $sKey ); ?></li>
						<?php endforeach; ?>						
					</ul>
					<input type="hidden" name="lang-str-__skip-update__" value="1" />
				</td>
			</tr>
			
		<?php endif;
		
	}
	
	
	
}


