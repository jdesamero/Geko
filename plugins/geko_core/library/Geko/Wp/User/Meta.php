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
		
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		//// database stuff
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_user_meta_members', 'umm' )
			->fieldBigInt( 'umeta_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'member_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'member_value' )
			->fieldLongText( 'flags' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	
	//
	public function uploadPathsCallback( $aPathDetails, $sFullDocRoot, $sFullUrlRoot ) {

		if ( !self::$_aAllUploadPaths[ $sFullDocRoot ] ) {
			self::$_aAllUploadPaths[ $sFullDocRoot ][ 'full_url_root' ] = $sFullUrlRoot;
			self::$_aAllUploadPaths[ $sFullDocRoot ][ 'meta_keys' ] = array();
		}
		
		self::$_aAllUploadPaths[ $sFullDocRoot ][ 'meta_keys' ] = array_merge(
			self::$_aAllUploadPaths[ $sFullDocRoot ][ 'meta_keys' ],
			$aPathDetails[ 'meta_keys' ]
		);
		
	}
	
		
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		// add_action( 'admin_init_user', array( $this, 'install' )  );		
		add_action( 'admin_head_user', array( $this, 'addAdminHead' )  );
				
		////
		
		add_action( 'show_user_profile', array( $this, 'outputForm' ), 9 );
		add_action( 'edit_user_profile', array( $this, 'outputForm' ), 9 );
		add_action( 'personal_options_update', array( $this,'update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'update' ) );
		add_action( 'deleted_user', array( $this, 'delete' ) );
				
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
	
	
	
	
	
	//// cache helpers
	
	//
	protected function setMetaCache( $iUserId ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		// done one-time for ALL user meta sub-classes
		if ( !isset( self::$aMetaCache[ $iUserId ] ) ) {
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'um.umeta_id', 'umeta_id' )
				->field( 'um.meta_key', 'meta_key' )
				->field( 'um.meta_value', 'meta_value' )
				->from( '##pfx##usermeta', 'um' )
				->where( 'um.user_id = ?', $iUserId )
			;
			
			$aFmt = $oDb->fetchHashObj( strval( $oQuery ), 'meta_key' );
			
			if ( is_array( $aFmt ) && ( count( $aFmt ) > 0 ) ) {

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
		
		$sMetaWithPfx = '';
		if ( $sMetaKey ) {
			$sMetaWithPfx = sprintf( '%s%s', $this->getPrefixWithSep(), $sMetaKey );
		}
		
		/* /
		if ( $sMetaWithPfx ) {
			// native WP function
			return get_usermeta( $iUserId, $sMetaWithPfx );
		}
		/* */
		
		$this->setMetaCache( $iUserId );
		
		if ( $sMetaWithPfx ) {
			if ( $bAddPrefix ) $sMetaKey = $sMetaWithPfx;
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
			
			$sLabel = ( $aPart[ 'label' ] ) ? sprintf( '<label for="%s">%s</label>', $aPart[ 'name' ], $aPart[ 'label' ] ) : '' ;
			$sFieldGroup = $aPart[ 'field_group' ];
			$sDescription = ( $aPart[ 'description' ] ) ? sprintf( '<span class="description">%s</span>', $aPart[ 'description' ] ) : '' ;
			
			$sFields .= sprintf( '
				<tr>
					<th>%s</th>
					<td>%s<br />%s</td>
				</tr>
			', $sLabel, $sFieldGroup, $sDescription );
		}
		
		return $sFields;
	}
	
	//
	public function addEnctype( $oPqForm ) {
		$oPqForm[ 'form' ]->attr( 'enctype', 'multipart/form-data' );
		return $oPqForm;
	}	
	
	
	//
	public function fieldRow( $sLabel, $sName, $aParams = array(), $sType = 'text' ) {
		
		$this->_fieldRow( $sLabel, $sName, $aParams, $sType, 'p' );
		
		return $this;
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
		
		$oDb = Geko_Wp::get( 'db' );
		
		//
		$aElemsGroup = isset( $aParams[ 'elems_group' ] ) ? 
			$aParams[ 'elems_group' ] : 
			$this->getElemsGroup()
		;
		
		//
		
		if ( 'update' == $sMode ) {
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'um.*' )
				->from( '##pfx##usermeta', 'um' )
				->where( 'um.user_id = ?', $iUserId )
			;
			
			$aMeta = $oDb->fetchHashObj( strval( $oQuery ), 'meta_key' );
			
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
		$oDb = Geko_Wp::get( 'db' );
		
		$iUserId = $this->resolveUserId( $iUserId );
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'um.*' )
			->from( '##pfx##usermeta', 'um' )
		;
		
		$oDb->delete( '##geko_user_meta_members##', array(
			'umeta_id NOT IN (?)' => new Zend_Db_Expr( strval( $oQuery ) )
		) );
		
	}
	
	
	//// crud methods
	
	//
	public function cleanOrphanFiles() {
		
		foreach ( self::$_aAllUploadPaths as $sDocRoot => $aDetails ) {
			
			$aMetaFields = array();
			
			foreach ( $aDetails[ 'meta_keys' ] as $sKey ) {
				$aMetaFields[] = sprintf( "( f.meta_key = '%s%s' )", $this->getPrefixWithSep(), $sKey );
			}
			
			$sMetaFields = sprintf( '( %s ) ', implode( ' OR ', $aMetaFields ) );
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'f.meta_value', 'meta_value' )
				->from( '##pfx##usermeta', 'f' )
				->where( $sMetaFields )
			;
			
			$oDelete = new Geko_Sql_Delete();
			$oDelete
				->from( '##pfx##usermeta', 'f' )
				->where( $sMetaFields )
			;
			
			parent::cleanOrphanFiles( $oQuery, $oDelete, sprintf( '%s/', $sDocRoot ) );
			
		}
		
	}
	
	
}



