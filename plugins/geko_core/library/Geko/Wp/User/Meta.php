<?php

//
class Geko_Wp_User_Meta extends Geko_Wp_Options_Meta
{
	private static $_bHasFileUploadAdded = FALSE;
	private static $_aAllUploadPaths = array();
	protected static $aMetaCache = array();
	
	protected $_sPrefixSeparator = '_';
	protected $_bHasDisplayMode = TRUE;
	protected $_iUserId = NULL;
	
	// handling file uploads
	
	protected $_bHasFileUpload = FALSE;
	protected $_aUploadPaths = array();
	protected $_aUpKeys = array();
	
	protected $_sUploadDir = '';
	protected $_sFullDocRoot = '';
	protected $_sFullUrlRoot = '';
	
	
	
	
	//// init
	
	//
	public function affix() {
		
		global $wpdb;
		
		$sTableName = 'geko_user_meta_members';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'umm' )
			->fieldBigInt( 'umeta_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'member_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'member_value' )
			->fieldLongText( 'flags' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
	}
	
	//
	public function install() {
		$this->createTable( $this->getPrimaryTable() );
		return $this;
	}
	
	
	
	//
	public function add() {
		
		parent::add();
		
		if ( $this->_bHasFileUpload ) {
						
			if ( count( $this->_aUploadPaths ) > 0 ) {
				
				// consolidate upload paths into central array
				foreach ( $this->_aUploadPaths as $sPath => $aPathDetails ) {
					
					if ( $aPathDetails[ 'auto_resolve' ] ) {
						$sFullDocRoot = Geko_PhpQuery_FormTransform_Plugin_File::getDefaultFileDocRoot() . $sPath;
						$sFullUrlRoot = Geko_PhpQuery_FormTransform_Plugin_File::getDefaultFileUrlRoot() . $sPath;
						$this->_aUploadPaths[ $sPath ][ 'full_doc_root' ] = $sFullDocRoot;
						$this->_aUploadPaths[ $sPath ][ 'full_url_root' ] = $sFullUrlRoot;
					} else {
						$this->_aUploadPaths[ $sPath ][ 'full_doc_root' ] = $sFullDocRoot = $sPath;
						$sFullUrlRoot = $aPathDetails[ 'full_url_root' ];
					}
					
					if ( !self::$_aAllUploadPaths[ $sFullDocRoot ] ) {
						self::$_aAllUploadPaths[ $sFullDocRoot ][ 'full_url_root' ] = $sFullUrlRoot;
						self::$_aAllUploadPaths[ $sFullDocRoot ][ 'meta_keys' ] = array();
					}
					
					self::$_aAllUploadPaths[ $sFullDocRoot ][ 'meta_keys' ] = array_merge(
						self::$_aAllUploadPaths[ $sFullDocRoot ][ 'meta_keys' ],
						$aPathDetails[ 'meta_keys' ]
					);
					
				}
				
				// track the keys numerically
				$this->_aUpKeys = array_keys( $this->_aUploadPaths );
				
			}
			
			$this->_sUploadDir = $this->getUploadPath();
			$this->_sFullDocRoot = $this->getFullDocRoot( $this->_sUploadDir );
			$this->_sFullUrlRoot = $this->getFullUrlRoot( $this->_sUploadDir );
			
		}
		
		return $this;
	}
	
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
	
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_init_user', array( $this, 'coft_install' )  );		
		add_action( 'admin_head_user', array( $this, 'coft_affixAdminHead' )  );
		add_action( 'admin_head_user', array( $this, 'co_addAdminHead' )  );
				
		////
		
		add_action( 'show_user_profile', array( $this, 'outputForm' ), 9 );
		add_action( 'edit_user_profile', array( $this, 'outputForm' ), 9 );
		add_action( 'personal_options_update', array( $this,'update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update' ) );
		add_action( 'deleted_user', array( $this, 'delete' ) );
		
		//// custom columns
		
		add_action( 'manage_users_columns', array( $this, 'columnTitle' ) );
		add_action( 'manage_users_custom_column', array( $this, 'columnValue' ), 10, 3 );
		
		//
		if ( $this->_bHasFileUpload ) {
			if ( !self::$_bHasFileUploadAdded ) {
				// do this once
				add_action( 'admin_user_fields_pq', array( $this, 'addEnctype' ) );
				add_action( 'edit_user_profile_update', array( $this, 'cleanOrphanFiles' ) );
				self::$_bHasFileUploadAdded = TRUE;
			}
		}
		
		return $this;
	}
	
	
	
	//
	public function columnTitle( $aColumn ) {
		return $aColumn;
	}
	
	//
	public function columnValue( $sValue, $sColumnName, $iUserId ) {
		return $sValue;
	}
	
	
	
	//// accessors
	
	// force a user id
	public function setUserId( $iUserId ) {
		$this->_iUserId = $iUserId;
		return $this;
	}
	
	//
	public function resolveUserId( $iUserId = NULL ) {
		if ( NULL === $iUserId ) {
			global $user_id;
			return Geko_String::coalesce( $this->_iUserId, $user_id, $_GET[ 'user_id' ] );
		}
		return $iUserId;
	}
	
	//
	public function getStoredOptions() {
		
		if ( $iUserId = $this->resolveUserId() ) {
			
			$this->setMetaCache( $iUserId );
			
			$aMeta = array();
			$aElemsGroup = parent::getElemsGroup();			// yields correct result!
			
			foreach ( $aElemsGroup as $sMetaKey => $aElem ) {
				$aMeta[ $sMetaKey ] = $this->getMeta( $iUserId, $sMetaKey );
			}
			
			return $aMeta;
			
		} else {
			return array();
		}
	}
	
	
	
	
	
	//// image display helpers
	
	//
	public function getPhotoPath( $iUserId, $sMetaKey = '', $sPathType = 'full_url_root' ) {
		
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
		
		if ( $sFile = $this->getMeta( $iUserId, $sMetaKey, TRUE ) ) {
			return $sFullPathRoot . '/' . $sFile;
		}
		
		return '';
	}
	
	//
	public function getPhotoPaths( $iUserId, $sPathType = 'full_url_root' ) {
		
		$aPaths = array();
		
		foreach ( $this->_aUploadPaths as $aPath ) {
			foreach( $aPath[ 'meta_keys' ] as $sKey ) {
				if ( $sFile = $this->getMeta( $iUserId, $sKey, TRUE ) ) {
					$aPaths[] = $aPath[ $sPathType ] . '/' . $sFile;
				}
			}
		}
		
		return $aPaths;
	}
	
	//
	public function getPhotoUrl( $iUserId, $sMetaKey = '' ) {
		return $this->getPhotoPath( $iUserId, $sMetaKey );
	}
	
	//
	public function getPhotoUrls( $iUserId ) {
		return $this->getPhotoPaths( $iUserId );
	}
	
	//
	public function getPhotoDoc( $iUserId, $sMetaKey = '' ) {
		return $this->getPhotoPath( $iUserId, $sMetaKey, 'full_doc_root' );
	}
	
	//
	public function getPhotoDocs( $iUserId ) {
		return $this->getPhotoPaths( $iUserId, 'full_doc_root' );
	}	
	
	
	
	
	
	//// cache helpers
	
	//
	protected function setMetaCache( $iUserId ) {
		
		// done one-time for ALL user meta sub-classes
		if ( !isset( self::$aMetaCache[ $iUserId ] ) ) {
			
			global $wpdb;
			
			$aFmt = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			umeta_id,
										meta_key,
										meta_value
						FROM			$wpdb->usermeta
						WHERE			user_id = %d
					",
					$iUserId
				),
				'meta_key'
			);
			
			////
			$aRet = array();
			$aSubVals = $this->gatherSubMetaValues( $aFmt, 'geko_user_meta_members', 'umeta_id' );
			
			foreach ( $aFmt as $sKey => $oItem ) {
				$aRet[ $sKey ][ 0 ] = $oItem->umeta_id;
				if ( isset( $aSubVals[ $oItem->umeta_id ] ) ) {
					$aRet[ $sKey ][ 1 ] = $aSubVals[ $oItem->umeta_id ];
				} else {
					$aRet[ $sKey ][ 1 ] = maybe_unserialize( $oItem->meta_value );				
				}
			}
			
			self::$aMetaCache[ $iUserId ] = $aRet;
		}
		
	}
	
	
	//
	public function getMeta( $iUserId = NULL, $sMetaKey = '', $bAddPrefix = FALSE ) {
		$iUserId = $this->resolveUserId( $iUserId );
		return $this->_getMetaValue( $iUserId, $sMetaKey, $bAddPrefix );
	}
	
	//
	public function getMetaId( $iUserId = NULL, $sMetaKey = '', $bAddPrefix = FALSE ) {
		$iUserId = $this->resolveUserId( $iUserId );
		return $this->_getMetaValue( $iUserId, $sMetaKey, $bAddPrefix, 0 );
	}
	
	//
	public function _getMetaValue( $iUserId = NULL, $sMetaKey = '', $bAddPrefix = FALSE, $iRowIdx = 1 ) {
		
		$iUserId = $this->resolveUserId( $iUserId );
		
		/* /
		if ( $sMetaKey ) {
			// native WP function
			return get_usermeta( $iUserId, $this->getPrefixWithSep() . $sMetaKey );
		}
		/* */
		
		$this->setMetaCache( $iUserId );
		
		if ( $sMetaKey ) {
			if ( $bAddPrefix ) $sMetaKey = $this->getPrefixWithSep() . $sMetaKey;
			return self::$aMetaCache[ $iUserId ][ $sMetaKey ][ $iRowIdx ];
		} else {
			
			$aMetaFmt = array();
			$aMetaCache = self::$aMetaCache[ $iUserId ];
			
			foreach ( $aMetaCache as $sKey => $aRow ) {
				$aMetaFmt[ $sKey ] = $aRow[ $iRowIdx ];
			}
			
			return $aMetaFmt;
		}
	}
	
	//
	protected function unsetMetaCache( $iUserId = NULL ) {
		if ( NULL === $iUserId ) {
			self::$aMetaCache = array();
		} elseif ( $iUserId && isset( self::$aMetaCache[ $iUserId ] ) ) {
			unset( self::$aMetaCache[ $iUserId ] );
		}
	}
	
	
	
	//// front-end display methods
	
	// called to output form
	public function outputForm() {
		
		$this->preFormFields();
		
		?>
		<h3><?php echo $this->getTitle(); ?></h3>
		
		<table class="form-table">
			<?php echo $this->formatFields(); ?>
		</table>
		<?php
		
		return $this;
	}
	
	
	//// form processing/injection methods
	
	// plug into the edit category form
	public function formatFields() {
		
		$aParts = $this->extractParts();
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			
			$sLabel = ( $aPart[ 'label' ] ) ? '<label for="' . $aPart[ 'name' ] . '">' . $aPart[ 'label' ] . '</label>' : '';
			$sFieldGroup = $aPart[ 'field_group' ];
			$sDescription = ( $aPart[ 'description' ] ) ? '<span class="description">' . $aPart[ 'description' ] . '</span>' : '';
			
			$sFields .= '
				<tr>
					<th>' . $sLabel . '</th>
					<td>
						' . $sFieldGroup . '<br />
						' . $sDescription . '
					</td>
				</tr>
			';
		}
		
		return $sFields;
	}
	
	//
	public function addEnctype( $oPqForm ) {
		$oPqForm[ 'form' ]->attr( 'enctype', 'multipart/form-data' );
		return $oPqForm;
	}	
	
	
	
	
	//// crud methods
	
	//
	public function insert( $iUserId = NULL ) {
		$iUserId = $this->resolveUserId( $iUserId );
		$this->save( $iUserId );
	}
	
	//
	public function update( $iUserId = NULL ) {
		$iUserId = $this->resolveUserId( $iUserId );
		$this->save( $iUserId, 'update' );
	}
	
	// save the data
	// 3rd, 4th, and 5th param only used when invoked directly
	public function save(
		$iUserId, $sMode = 'insert', $aParams = NULL, $aDataVals = NULL, $aFileVals = NULL
	) {
		
		global $wpdb;
		
		//
		$aElemsGroup = isset( $aParams[ 'elems_group' ] ) ? 
			$aParams[ 'elems_group' ] : 
			$this->getElemsGroup()
		;
		
		//
		
		if ( 'update' == $sMode ) {
			$aMeta = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"SELECT * FROM $wpdb->usermeta WHERE user_id = %d",
					$iUserId
				),
				'meta_key'
			);
		} else {
			$aMeta = array();
		}
		
		$this->commitMetaData(
			array(
				'elems_group' => $aElemsGroup,
				'meta_data' => $aMeta,
				'entity_id' => $iUserId,
				'meta_table' => 'usermeta',
				'meta_member_table' => 'geko_user_meta_members',
				'meta_entity_id_field_name' => 'user_id',
				'meta_id_field_name' => 'umeta_id'
			),
			$aDataVals,
			$aFileVals
		);
		
		$this->unsetMetaCache( $iUserId );
	}
	
	
	//
	public function delete( $iUserId = NULL ) {
		
		// cleanup all orphaned metadata
		global $wpdb;
		
		$iUserId = $this->resolveUserId( $iUserId );
		
		// members
		$wpdb->query("
			DELETE FROM		$wpdb->geko_user_meta_members
			WHERE			umeta_id NOT IN (
				SELECT			umeta_id
				FROM			$wpdb->usermeta
			)
		");
		
	}
	
	
	//// crud methods
	
	//
	public function cleanOrphanFiles() {
		
		global $wpdb;
		
		foreach ( self::$_aAllUploadPaths as $sDocRoot => $aDetails ) {
			
			$aMetaFields = array();
			
			foreach ( $aDetails[ 'meta_keys' ] as $sKey ) {
				$aMetaFields[] = "( f.meta_key = '" . $this->getPrefixWithSep() . $sKey . "' )";
			}
			
			$sMetaFields = '( ' . implode( ' OR ', $aMetaFields ) . ' ) ';
			
			parent::cleanOrphanFiles(
				"	SELECT				f.meta_value
					FROM				$wpdb->usermeta f
					WHERE				$sMetaFields
				",
				"	DELETE FROM 		$wpdb->usermeta f
					WHERE				$sMetaFields AND
										( f.meta_value = %s )
				",
				$sDocRoot . '/'
			);
			
		}
		
	}
	
	
}



