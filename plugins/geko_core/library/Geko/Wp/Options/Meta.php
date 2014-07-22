<?php

// base class for meta options
class Geko_Wp_Options_Meta extends Geko_Wp_Options
{	
	
	protected $_sSlug;
	protected $_sParentFieldName = '';
	
	
	// handling file uploads
	
	protected $_bHasFileUpload = FALSE;
	protected $_aUploadPaths = array();
	protected $_aUpKeys = array();
	
	protected $_sUploadDir = '';
	protected $_sFullDocRoot = '';
	protected $_sFullUrlRoot = '';
	
	
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$aThemeInfo = Geko_Wp_Theme::get_current_data();
		
		$this->_sPrefix = Geko_String::coalesce( $this->_sPrefix, $aThemeInfo[ 'Prefix' ] );
		
	}
	
	//
	public function add() {
		
		parent::add();
		
		
		
		//// file upload stuff
		
		if ( $this->_bHasFileUpload ) {
						
			if ( count( $this->_aUploadPaths ) > 0 ) {
				
				// consolidate upload paths into central array
				foreach ( $this->_aUploadPaths as $sPath => $aPathDetails ) {
					
					if ( $aPathDetails[ 'auto_resolve' ] ) {
						
						$sFullDocRoot = sprintf( '%s%s', Geko_PhpQuery_FormTransform_Plugin_File::getDefaultFileDocRoot(), $sPath );
						$sFullUrlRoot = sprintf( '%s%s', Geko_PhpQuery_FormTransform_Plugin_File::getDefaultFileUrlRoot(), $sPath );
						
						$this->_aUploadPaths[ $sPath ][ 'full_doc_root' ] = $sFullDocRoot;
						$this->_aUploadPaths[ $sPath ][ 'full_url_root' ] = $sFullUrlRoot;
					
					} else {
						
						$this->_aUploadPaths[ $sPath ][ 'full_doc_root' ] = $sFullDocRoot = $sPath;
						$sFullUrlRoot = $aPathDetails[ 'full_url_root' ];
					}
					
					$this->uploadPathsCallback( $aPathDetails, $sFullDocRoot, $sFullUrlRoot );
				}
				
				// track the keys numerically
				$this->_aUpKeys = array_keys( $this->_aUploadPaths );
				
			}
			
			$this->_sUploadDir = $this->getUploadPath();
			$this->_sFullDocRoot = $this->getFullDocRoot( $this->_sUploadDir );
			$this->_sFullUrlRoot = $this->getFullUrlRoot( $this->_sUploadDir );
			
		}
		
		
		
		//
		if ( $oSubMng = $this->_oSubOptionParent ) {
			
			$sSubAction = $oSubMng->getActionPrefix();
			$this->_sSlug = $oSubMng->getSlug();
			
			add_action( sprintf( '%s_main_fields', $sSubAction ), array( $this, 'outputForm' ), 10, 2 );
			add_action( sprintf( '%s_main_fields_%s', $sSubAction, $this->_sSlug ), array( $this, 'outputForm' ), 10, 3 );
			
			add_action( sprintf( '%s_extra_fields', $sSubAction ), array( $this, 'outputForm' ), 10, 2 );
			add_action( sprintf( '%s_extra_fields_%s', $sSubAction, $this->_sSlug ), array( $this, 'outputForm' ), 10, 3 );
			
			add_action( sprintf( '%s_add', $sSubAction ), array( $this, 'insert' ) );
			add_action( sprintf( '%s_add_%s', $sSubAction, $this->_sSlug ), array( $this, 'insertType' ) );
			
			add_action( sprintf( '%s_edit', $sSubAction ), array( $this, 'update' ), 10, 2 );
			add_action( sprintf( '%s_edit_%s', $sSubAction, $this->_sSlug ), array( $this, 'updateType' ), 10, 2 );
			
			add_action( sprintf( '%s_delete', $sSubAction ), array( $this, 'delete' ) );
			add_action( sprintf( '%s_delete_%s', $sSubAction, $this->_sSlug ), array( $this, 'deleteType' ) );
			
		}
		
		return $this;
	}
	
	// hook methods
	
	//
	public function uploadPathsCallback( $aPathDetails, $sFullDocRoot, $sFullUrlRoot ) {
	
	}
	
	
	
	
	//// front-end display methods
	
	//
	public function outputForm( $oEntity, $sAction, $sSlug = '' ) {
		$this->aPassParams = array( $oEntity, $sAction, $sSlug );
		parent::outputForm();
	}
	
	//
	protected function formFields() {
		
		list( $oEntity, $sAction, $sSlug ) = $this->aPassParams;
		
		if ( 'main' == $sAction ) {
			
			if ( !$sSlug ) {
				$this->formFieldsMain();			
			} elseif ( $sSlug == $this->_sSlug ) {
				$this->formFieldsMainType();
			}
			
		} elseif ( 'extra' == $sAction ) {
			
			if ( !$sSlug ) {
				$this->formFieldsExtra();
			} elseif ( $sSlug == $this->_sSlug ) {
				$this->formFieldsExtraType();			
			}
			
		}
	}
	
	//
	protected function formFieldsMain() { }
	protected function formFieldsMainType() { }
	protected function formFieldsExtra() { }
	protected function formFieldsExtraType() { }
	
	
	
	
	
	
	//// form field generation shortcuts
	
	//
	public function _fieldRow( $sLabel, $sName, $aParams = array(), $sType = 'text', $sRowType = 'tr' ) {
		
		$sMethod = sprintf( 'field%s', Geko_Inflector::camelize( $sType ) );
		
		if ( method_exists( $this, $sMethod ) ) {
			
			if ( 'p' == $sRowType ): ?>
				
				<p>
					<label class="main" for="<?php echo $sName; ?>"><?php echo $sLabel; ?></label> 
					<?php $this->$sMethod( $sName, $aParams ); ?>
				</p>
			
			<?php else: ?>
				
				<tr>
					<th><label for="<?php echo $sName; ?>"><?php echo $sLabel; ?></label></th>
					<td><?php $this->$sMethod( $sName, $aParams ); ?></td>
				</tr>			
			
			<?php endif;
			
		}
		
		return $this;
	}
	
	//
	public function fieldRow( $sLabel, $sName, $aParams = array(), $sType = 'text' ) {
		
		$this->_fieldRow( $sLabel, $sName, $aParams, $sType );
		
		return $this;
	}

	
	
	//
	public function fieldText( $sName, $aParams = array() ) {
		
		$sClass = $this->fieldClass( $aParams[ 'class' ], 'regular-text' );
		
		printf( '<input id="%s" name="%s" type="text" %s value="" />', $sName, $sName, $sClass );
		
		return $this;
	}
	
	//
	public function fieldCheckbox( $sName, $aParams = array() ) {
		
		$sValue = $aParams[ 'value' ];
		if ( NULL === $sValue ) {
			$sValue = 1;
		}

		$sClass = $this->fieldClass( $aParams[ 'class' ] );
		
		printf( '<input id="%s" name="%s" type="checkbox" value="%s" %s />', $sName, $sName, $sValue, $sClass );
		
		return $this;
	}
	
	//
	public function fieldSelect( $sName, $aParams = array() ) {
		
		$sClass = $this->fieldClass( $aParams[ 'class' ] );
		
		$sMultiple = '';
		if ( $aParams[ 'multiple' ] ) {
			$sMultiple = ' multiple="multiple" ';
		}
		
		
		printf( '<select id="%s" name="%s" %s %s>', $sName, $sName, $sMultiple, $sClass );
		
		if ( $sEmptyLabel = $aParams[ 'default_empty_label' ] ) {
			printf( '<option value="">%s</option>', $sEmptyLabel );
		}
		
		if ( $aQuery = $aParams[ 'query' ] ) {
			
			if ( !$sQryVal = $aParams[ 'query_value' ] ) {
				$sQryVal = '##Id##';
			}
			
			if ( !$sQryLbl = $aParams[ 'query_label' ] ) {
				$sQryLbl = '##Title##';
			}
			
			echo $aQuery->implode( array( sprintf( '<option value="%s">%s</option>', $sQryVal, $sQryLbl ), '' ) );
		}
		
		echo '</select>';
		
		return $this;
	}
	
	//
	public function fieldImageUpload( $sName, $aParams = array() ) {
		
		$sClass = $this->fieldClass( $aParams[ 'class' ] );
		
		$sNameWithPfx = sprintf( '%s%s', $this->getPrefixWithSep(), $sName );
		
		if ( !$iWidth = $aParams[ 'thumb_width' ] ) {
			$iWidth = 200;
		}
		
		if ( !$iHeight = $aParams[ 'thumb_height' ] ) {
			$iHeight = 200;		
		}
		
		?>
		<input type="file" id="<?php echo $sName; ?>" name="<?php echo $sName; ?>" _file_upload_dir="<?php echo $this->_sUploadDir; ?>" />
		<label class="side">(jpg, jpeg, gif, or png)</label><br />
		<span _bind_to="<?php echo $sNameWithPfx; ?>" _thumb_width="<?php echo $iWidth; ?>" _thumb_height="<?php echo $iHeight; ?>"></span>
		<?php
		
		return $this;
	}
	
	//
	public function fieldClass( $sClass, $sDefaultClass = '' ) {
		
		$sConcatClass = trim( sprintf( '%s %s', $sDefaultClass, $sClass ) );
		
		if ( $sConcatClass ) {
			return sprintf( ' class="%s" ', $sConcatClass );
		}
		
		return '';
	}
	
	
	
	
	
	
	////
	
	
	//
	public function getMetaData( $aParams = array() ) {
		
		global $wpdb;
		
		$sField = $this->_sParentFieldName;
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( sprintf( 'm.%s', $sField ) )
			->field( 'k.meta_key' )
			->field( 'm.meta_value' )
			->from( $this->_sPrimaryTable, 'm' )
			->joinLeft( $wpdb->geko_meta_key, 'k' )
				->on( 'k.mkey_id = m.mkey_id' )
		;
		
		if ( $aParams[ 'parent_ids' ] ) {
			$oQuery->where( sprintf( 'm.%s * ($)', $sField ), $aParams[ 'parent_ids' ] );
		}
		
		$aRes = $wpdb->get_results( strval( $oQuery ) );
		
		$aMetaData = array();
		foreach ( $aRes as $oRes ) {
			$aMetaData[ $oRes->$sField ][ $oRes->meta_key ] = $oRes->meta_value;
		}
		
		return $aMetaData;
	}

	
	
	//// crud methods
	
	//
	protected function gatherSubMetaValues( $aItems, $sMetaMemberTable, $sMetaIdFieldName ) {
		
		global $wpdb;
		
		$aMetaIds = array();
		foreach ( $aItems as $oItem ) {
			$aMetaIds[] = $oItem->$sMetaIdFieldName;
		}
		
		$sMetaMemberTable = $wpdb->$sMetaMemberTable;
		$aSubFmt = $wpdb->get_results( sprintf( "
			SELECT			*
			FROM			$sMetaMemberTable
			WHERE			%s
			ORDER BY		member_id, member_value
		", Geko_Wp_Db::prepare( sprintf( ' ( %s ##d## ) ', $sMetaIdFieldName ), $aMetaIds ) ) );
		
		$aSubVals = array();
		foreach ( $aSubFmt as $oSubItem ) {
			$aSubVals[ $oSubItem->$sMetaIdFieldName ][] = $oSubItem;
		}
		
		return $aSubVals;
	}
	
	// hook method
	
	
	
	//
	protected function commitMetaData( $aParams, $aDataVals = NULL, $aFileVals = NULL ) {
		
		global $wpdb;
		
		$aElemsGroup = $aParams[ 'elems_group' ];
		$aMeta = $aParams[ 'meta_data' ];
		$iEntityId = intval( $aParams[ 'entity_id' ] );
		$bUseMkeyId = $aParams[ 'use_mkey_id' ] ? TRUE : FALSE;
		
		$sMetaTable = $aParams[ 'meta_table' ];
		$sMetaMemberTable = $aParams[ 'meta_member_table' ];
		$sMetaEntityIdFieldName = $aParams[ 'meta_entity_id_field_name' ];
		$sMetaIdFieldName = $aParams[ 'meta_id_field_name' ];
		
		$sMetaMemberTable = $wpdb->$sMetaMemberTable;
		
		$aSubEntities = array();
		
		// if $aDataVals is not NULL, then flag it
		$bGetDataKeys = ( NULL !== $aDataVals ) ? TRUE : FALSE;
		
		//// HACKISH!!!
		if ( $aDataVals && $aFileVals ) {
			// reconcile corresponding $_POST values for $_FILES
			foreach ( $aFileVals as $sKey => $sValue ) {
				if ( 0 === strpos( $sValue, '_FILES::' ) ) {
					$sRealKey = str_replace( '_FILES::', '', $sValue );
					$aFileVals[ $sKey ] = $_FILES[ $sRealKey ];
					if ( !$aDataVals[ $sKey ] ) {
						$aDataVals[ $sKey ] = $_POST[ $sRealKey ];
						if ( $mDel = $_POST[ sprintf( 'del-%s', $sRealKey ) ] ) {
							$aDataVals[ sprintf( 'del-%s', $sKey ) ] = $mDel;
						}
					}
				}
			}
		}
		
		// set data and file values
		$aDataVals = $this->getDataValues( $aDataVals );
		$aFileVals = $this->getFileValues( $aFileVals );
		
		// set data keys
		$aDataKeys = ( $bGetDataKeys ) ? array_keys( $aDataVals ) : FALSE;
		
		//
		foreach ( $aElemsGroup as $sMetaKey => $aElem ) {
			
			// skip this form element if "_skip_save" flag is set
			if (
				( is_object( $aElem[ 'elem' ] ) && $aElem[ 'elem' ]->attr( '_skip_save' ) ) || 
				( is_array( $aElem[ 'elem' ] ) && $aElem[ 'elem' ][ 0 ]->attr( '_skip_save' ) )
			) {
				continue;
			}
			
			// skip elements not part of explicit data vals
			if ( $aDataKeys && ( !in_array( $sMetaKey, $aDataKeys ) ) ) {
				continue;
			}
			
			// process element
			
			if (
				( is_object( $aElem[ 'elem' ] ) ) && 
				( $aElem[ 'elem' ]->attr( '_member_ids' ) )
			) {
				$aSubVals = ( $aDataVals[ $sMetaKey ] ) ? Zend_Json::decode( Geko_String::stripSlashesDeep( $aDataVals[ $sMetaKey ] ) ) : array();
				$sValue = NULL;
			} elseif (
				( 'select:multiple' == $aElem[ 'type' ] ) || 
				(
					( 'input:checkbox' == $aElem[ 'type' ] ) && 
					( is_array( $aElem[ 'elem' ] ) ) && 
					( count( $aElem[ 'elem' ] ) > 1 )
				)
			) {
				$aSubVals = is_array( $aDataVals[ $sMetaKey ] ) ? $aDataVals[ $sMetaKey ] : array();
				$sValue = NULL;
			} else {
				$aSubVals = NULL;
				$sValue = maybe_serialize( Geko_String::stripSlashesDeep( $aDataVals[ $sMetaKey ] ) );
			}
			
			$oMeta = $aMeta[ $sMetaKey ];
			
			if ( 'input:file' == $aElem[ 'type' ] ) {
				$sValue = $this->handleFileUpload(
					$sMetaKey, $aElem[ 'full_doc_root' ], $oMeta->meta_value, $sValue, $aDataVals, $aFileVals
				);
			}
			
			// setup $aVals, $aKeys
			$iSubEntityId = NULL;
			
			$aVals = array();
			$aVals = $this->commitMetaDataValue( $aVals, $oMeta, $sMetaKey, $aParams );
			
			$aKeys = array();
			
			if ( NULL !== $sValue ) $aVals[ 'meta_value' ] = $sValue;
						
			if ( !$oMeta ) {
				
				// insert meta value
				
				$aVals[ $sMetaEntityIdFieldName ] = $iEntityId;
				
				if ( $bUseMkeyId ) {
					$aVals[ 'mkey_id' ] = Geko_Wp_Options_MetaKey::getId( $sMetaKey );
				} else {
					$aVals[ 'meta_key' ] = $sMetaKey;				
				}
				
				$wpdb->insert( $wpdb->$sMetaTable, $aVals );
				
				$iSubEntityId = $wpdb->insert_id;
				
			} elseif (
				( $oMeta->meta_value != $sValue ) || 
				( NULL !== $aSubVals ) || 
				( $this->commitMetaDataValueChanged( $aVals, $oMeta ) )
			) {
				
				// update meta value
				
				$aKeys[ $sMetaIdFieldName ] = $oMeta->$sMetaIdFieldName;
				
				$wpdb->update( $wpdb->$sMetaTable, $aVals, $aKeys );
				
				$iSubEntityId = $oMeta->$sMetaIdFieldName;
				
			}
			
			if ( ( NULL !== $aSubVals ) || ( $this->commitMetaDataHasSubValues( $aElem ) ) ) {
				$aSubEntities[ $iSubEntityId ] = $this->commitMetaDataSubValues(
					$iSubEntityId, $aSubVals, $aElem, $sMetaIdFieldName
				);
			}
			
		}
		
		if ( count( $aSubEntities ) > 0 ) {
			
			// clean-up existing
			$wpdb->query( sprintf( '
					DELETE FROM			%s
					WHERE				%s
				',
				$sMetaMemberTable,
				Geko_Wp_Db::prepare( sprintf( ' ( %s ##d## ) ', $sMetaIdFieldName ), array_keys( $aSubEntities ) )
			) );
			
			// re-insert
			foreach ( $aSubEntities as $iSubEntityId => $aSubVals ) {
				foreach ( $aSubVals as $aRowData ) {
					$wpdb->insert( $sMetaMemberTable, $aRowData );
				}
			}
			
		}
		
	}
	
	// hook method
	protected function commitMetaDataValue( $aVals, $oMeta ) {
		return $aVals;
	}
	
	// hook method
	protected function commitMetaDataValueChanged( $aVals, $oMeta ) {
		return TRUE;
	}
	
	// hook method
	protected function commitMetaDataHasSubValues( $aElem ) {
		return FALSE;
	}
	
	// hook method
	protected function commitMetaDataSubValues( $iSubEntityId, $aSubVals, $aElem, $sMetaIdFieldName ) {
		// format $aSubVals for inserting
		$aRows = array();
		foreach ( $aSubVals as $mValue ) {
			if ( $mValue ) {
				$aRow[ $sMetaIdFieldName ] = $iSubEntityId;
				if ( preg_match( '/^[0-9]+$/', $mValue ) ) {
					$aRow[ 'member_id' ] = $mValue;					// number
				} else {
					$aRow[ 'member_value' ] = $mValue;				// string
				}
				$aRows[] = $aRow;
			}
		}
		return $aRows;
	}
	
	
	
	// get data values
	protected function getDataValues( $aDataVals ) {
		
		if ( NULL !== $aDataVals ) {
			
			// $aDataVals is not prefixed for ease of use, so it must be prefixed to
			// match up with the prefixing scheme of the elems group
			$sPrefix = $this->getPrefixForDoc();
			$aPrefixed = array();
			
			foreach ( $aDataVals as $sKey => $mVal ) {
				if ( 0 === strpos( $sKey, 'del-' ) ) {
					// del-varname becomes del-prefix-varname
					$sPfKey = substr_replace( $sKey, $sPrefix, 4, 0 );
				} else {
					// varname becomes prefix-varname
					$sPfKey = sprintf( '%s%s', $sPrefix, $sKey );
				}
				$aPrefixed[ $sPfKey ] = $mVal;
			}
			
			return $aPrefixed;
			
		}
		
		// use $_POST array for values
		return $_POST;	
	}
	
	// get file values
	protected function getFileValues( $aFileVals ) {
		
		if ( NULL !== $aFileVals ) {
			
			// same as $aDataVals in getDataValues()
			$sPrefix = $this->getPrefixForDoc();
			$aPrefixed = array();
			
			foreach ( $aFileVals as $sKey => $mVal ) {
				$aPrefixed[ sprintf( '%s%s', $sPrefix, $sKey ) ] = $mVal;
			}
			
			return $aPrefixed;
			
		}
		
		// use $_FILES array for values
		return $_FILES;
	}
	
	
	//// file handling methods
	
	// returns the value of the newly uploaded file, or '' if file was deleted
	protected function handleFileUpload( $sMetaKey, $sUploadDir, $sDbValue, $sPostValue, $aDataVals, $aFileVals ) {
		
		$sValue = $sDbValue;										// set return value to current DB value
		
		// if delete flag was set, then delete file
		if ( $aDataVals[ sprintf( 'del-%s', $sMetaKey ) ] ) {
			
			// delete existing file
			if (
				$sDbValue && 
				is_file( $sFile = sprintf( '%s/%s', $sUploadDir, $sDbValue ) )
			) {
				unlink( $sFile );
			}
			
			$sValue = '';											// set return value to empty to delete from db
			
		}
		
		// handle file upload
		if (
			( $aFileVals[ $sMetaKey ] ) && 
			( UPLOAD_ERR_OK == $aFileVals[ $sMetaKey ][ 'error' ] ) &&
			$sUploadDir
		) {
			
			if ( !is_dir( $sUploadDir ) ) {
				mkdir( $sUploadDir, 0755, TRUE );					// attempt to make directory
			}
			
			if ( is_dir( $sUploadDir ) ) {
				
				// delete existing file because it is being replaced
				if (
					$sDbValue && 
					is_file( $sFile = sprintf( '%s/%s', $sUploadDir, $sDbValue ) )
				) {
					unlink( $sFile );
				}
				
				// get a unique filename before saving
				$sSavefile = Geko_File::getUniqueName( $aFileVals[ $sMetaKey ][ 'name' ], $sUploadDir );
				
				// move uploaded file
				$sTmpFile = $aFileVals[ $sMetaKey ][ 'tmp_name' ];
				if ( is_uploaded_file( $sTmpFile ) ) {
					if ( move_uploaded_file( $sTmpFile, sprintf( '%s/%s', $sUploadDir, $sSavefile ) ) ) {
						$sValue = $sSavefile;						// file name has been potentially changed
					}
				} else {
					// allow spoofing of $_FILES array
					if ( rename( $sTmpFile, sprintf( '%s/%s', $sUploadDir, $sSavefile ) ) ) {
						$sValue = $sSavefile;
					}
				}
			}
			
		}
		
		return $sValue;
	}
	
	
	//
	public function cleanOrphanFiles( $sFilesDbSql, $sCleanupSql, $sFileDir ) {
		
		global $wpdb;
		
		// get all the files in the database
		$aFilesDb = $wpdb->get_col( $sFilesDbSql );
		$aFilesDb = array_diff( $aFilesDb, array( '' ) );					// remove empty values
		
		// get list of actual files
		$aFiles = array_diff(
			scandir( $sFileDir ),
			array( '.', '..' )
		);
		
		// remove files in the db that don't actually exist
		// go through each file
		foreach ( $aFilesDb as $i => $sFile ) {
			$iBefore = count( $aFiles );									// count all actual files
			$aFiles = array_diff( $aFiles, array( $sFile ) );				// remove matching file
			$iAfter = count( $aFiles );										// count again
			if ( $iBefore != $iAfter ) unset( $aFilesDb[ $i ] );			// remove from db file list
		}
		
		// cleanup db with whatever was left over from $aFilesDb (since these do not actually exist)
		foreach ( $aFilesDb as $sFile ) {
			$wpdb->query( $wpdb->prepare( $sCleanupSql, $sFile ) );
		}
		
		// cleanup files with whatever was left over from $aFiles (since these do not exist in the db)
		foreach ( $aFiles as $sFile ) {
			unlink( sprintf( '%s%s', $sFileDir, $sFile ) );
		}
		
	}
	
	
	
	
	//// image handling
	
	// helper accessors for $this->_aUploadPaths
	
	//
	public function getHasFileUpload() {
		return $this->_bHasFileUpload;
	}
	
	//
	public function getUploadDir() {
		return $this->_sUploadDir;
	}
	
	//
	public function getUploadPath( $iIdx = 0 ) {
		return $this->_aUpKeys[ $iIdx ];
	}
	
	//
	public function getFullDocRoot( $sPath ) {
		return $this->_aUploadPaths[ $sPath ][ 'full_doc_root' ];
	}

	//
	public function getFullUrlRoot( $sPath ) {
		return $this->_aUploadPaths[ $sPath ][ 'full_url_root' ];
	}
	
	
	
	
	//// image display helpers
	
	//
	public function getPhotoPath( $iItemId, $sMetaKey = '', $sPathType = 'full_url_root' ) {
		
		$sFullPathRoot = '';
		
		if ( !$sMetaKey ) {
			
			// use the first meta key of the first upload path as default
			foreach ( $this->_aUploadPaths as $aPath ) {
				if ( $sMetaKey = $aPath[ 'meta_keys' ][ 0 ] ) {
					$sFullPathRoot = $aPath[ $sPathType ];
					break;
				}
			}
			
		} else {
			
			foreach ( $this->_aUploadPaths as $aPath ) {
				foreach ( $aPath[ 'meta_keys' ] as $sMk ) {
					if ( $sMetaKey == $sMk ) {
						$sFullPathRoot = $aPath[ $sPathType ];
						break;					
					}
				}
			}
			
		}
		
		if ( !$sMetaKey || !$sFullPathRoot ) return '';
		
		if ( $sFile = $this->getMeta( $iItemId, $sMetaKey, TRUE ) ) {
			return sprintf( '%s/%s', $sFullPathRoot, $sFile );
		}
		
		return '';
	}
	
	//
	public function getPhotoPaths( $iItemId, $sPathType = 'full_url_root' ) {
		
		$aPaths = array();
		
		foreach ( $this->_aUploadPaths as $aPath ) {
			foreach( $aPath[ 'meta_keys' ] as $sKey ) {
				if ( $sFile = $this->getMeta( $iItemId, $sKey, TRUE ) ) {
					$aPaths[] = sprintf( '%s/%s', $aPath[ $sPathType ], $sFile );
				}
			}
		}
		
		return $aPaths;
	}
	
	//
	public function getPhotoUrl( $iItemId, $sMetaKey = '' ) {
		return $this->getPhotoPath( $iItemId, $sMetaKey );
	}
	
	//
	public function getPhotoUrls( $iItemId ) {
		return $this->getPhotoPaths( $iItemId );
	}
	
	//
	public function getPhotoDoc( $iItemId, $sMetaKey = '' ) {
		return $this->getPhotoPath( $iItemId, $sMetaKey, 'full_doc_root' );
	}
	
	//
	public function getPhotoDocs( $iItemId ) {
		return $this->getPhotoPaths( $iItemId, 'full_doc_root' );
	}	
	
	
	
	
	
	
	
	
	//
	public function insert( $oEntity ) {
		$this->save( $oEntity );
	}
	
	//
	public function insertType( $oEntity ) {
		$this->save( $oEntity, 'insert', $this->_sSlug );
	}
	
	//
	public function update( $oEntity, $oNewEnt ) {
		$this->save( $oNewEnt, 'update' );
	}
	
	//
	public function updateType( $oEntity, $oNewEnt ) {
		$this->save( $oNewEnt, 'update', $this->_sSlug );
	}
	
	//
	public function delete( $oEntity ) { }
	
	//
	public function deleteType( $oEntity ) { }
	
	
	
}


