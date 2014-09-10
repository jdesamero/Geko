<?php

// abstract
class Geko_Wp_Post_Meta extends Geko_Wp_Options_Meta
{
	
	protected static $aMetaCache = array();
	
	protected $_bHasDisplayMode = TRUE;
	
	protected $_iPostId = NULL;
	protected $_oCurPost = NULL;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_post_meta_members', 'pmm' )
			->fieldBigInt( 'meta_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'member_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'member_value' )
			->fieldLongText( 'flags' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	//
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_menu', array( $this, 'attachPage' ) );
		
		add_action( 'admin_init_post', array( $this, 'install' )  );
		add_action( 'admin_head_post', array( $this, 'addAdminHead' )  );
		
		add_action( 'wp_insert_post', array( $this, 'insert' ) );
		
		return $this;
	}
	
	
	//
	public function attachPage() {
		if ( TRUE == function_exists( 'add_meta_box' ) ) {
			add_meta_box( sanitize_title( $this->_sInstanceClass ), sprintf( '%s Custom Settings', $this->aThemeData[ 'Name' ] ), array( $this, 'outputForm' ), 'post', 'normal' );
		}
	}	
	
	
	
	
	//// accessors
	
	//
	public function getStoredOptions() {
		
		global $post;
		
		if ( $iPostId = $post->ID ) {
			
			$this->setMetaCache( $iPostId );
			
			$aMeta = array();
			$aElemsGroup = parent::getElemsGroup();			// yields correct result!
			
			foreach ( $aElemsGroup as $sMetaKey => $aElem ) {
				$aMeta[ $sMetaKey ] = $this->getMeta( $iPostId, $sMetaKey );
			}
			
			return $aMeta;
			
		} else {
			return array();
		}
	}
	
	//
	public function getCurPost() {
		$iPostId = $this->resolvePostId();
		if ( !$this->_oCurPost && $iPostId ) {
			$this->_oCurPost = $this->newPost( $iPostId );
		}
		return $this->_oCurPost;
	}
	
	
	
	
	
	
	//// cache helpers
	
	//
	protected function setMetaCache( $iPostId ) {
		
		// done one-time for ALL post meta sub-classes
		if ( !isset( self::$aMetaCache[ $iPostId ] ) ) {
			
			global $wpdb;
			
			$aFmt = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			meta_id,
										meta_key,
										meta_value
						FROM			$wpdb->postmeta
						WHERE			post_id = %d
					",
					$iPostId
				),
				'meta_key'
			);
			
			////
			$aRet = array();
			$aSubVals = $this->gatherSubMetaValues( $aFmt, 'geko_post_meta_members', 'meta_id' );
			
			foreach ( $aFmt as $sKey => $oItem ) {
				$aRet[ $sKey ][ 0 ] = $oItem->meta_id;
				if ( isset( $aSubVals[ $oItem->meta_id ] ) ) {
					$aRet[ $sKey ][ 1 ] = $aSubVals[ $oItem->meta_id ];
				} else {
					$aRet[ $sKey ][ 1 ] = maybe_unserialize( $oItem->meta_value );				
				}
			}
			
			self::$aMetaCache[ $iPostId ] = $aRet;
			
		}
		
	}
	
	// force a post id
	public function setPostId( $iPostId ) {
		$this->_iPostId = $iPostId;
		return $this;
	}
	
	//
	public function resolvePostId( $iPostId = NULL ) {
		if ( NULL === $iPostId ) {
			return Geko_String::coalesce( $this->_iPostId, $_REQUEST[ 'post' ], $_REQUEST[ 'post_ID' ] );
		}
		return $iPostId;
	}
	
	//
	public function getMeta( $iPostId = NULL, $sMetaKey = '', $bAddPrefix = FALSE ) {
		$iPostId = $this->resolvePostId( $iPostId );
		return $this->_getMetaValue( $iPostId, $sMetaKey, $bAddPrefix );
	}
	
	//
	public function getMetaId( $iPostId = NULL, $sMetaKey = '', $bAddPrefix = FALSE ) {
		$iPostId = $this->resolvePostId( $iPostId );
		return $this->_getMetaValue( $iPostId, $sMetaKey, $bAddPrefix, 0 );
	}
	
	//
	public function _getMetaValue( $iPostId = NULL, $sMetaKey = '', $bAddPrefix = FALSE, $iRowIdx = 1 ) {
		
		$iPostId = $this->resolvePostId( $iPostId );
		
		/* /
		if ( $sMetaKey ) {
			// native WP function
			return get_post_meta( $iPostId, $this->getPrefixWithSep() . $sMetaKey );
		}
		/* */
		
		$this->setMetaCache( $iPostId );
		
		if ( $sMetaKey ) {
			if ( $bAddPrefix ) $sMetaKey = $this->getPrefixWithSep() . $sMetaKey;
			return self::$aMetaCache[ $iPostId ][ $sMetaKey ][ $iRowIdx ];
		} else {
			
			$aMetaFmt = array();
			$aMetaCache = self::$aMetaCache[ $iPostId ];
			
			foreach ( $aMetaCache as $sKey => $aRow ) {
				$aMetaFmt[ $sKey ] = $aRow[ $iRowIdx ];
			}
			
			return $aMetaFmt;
		}
	}
	
	//
	protected function unsetMetaCache( $iPostId = NULL ) {
		if ( NULL === $iPostId ) {
			self::$aMetaCache = array();
		} elseif ( $iPostId && isset( self::$aMetaCache[ $iPostId ] ) ) {
			unset( self::$aMetaCache[ $iPostId ] );
		}
	}
	
	
	
	
	
	
	//// crud methods
	
	// save the data
	public function insert( $iPostID, $aDataVals = NULL, $aFileVals = NULL ) {
		
		$oPost = get_post( $iPostID );
		if ( 'revision' == $oPost->post_type ) {
			if ( $oPost->post_parent ) {
				$oParent = get_post( $oPost->post_parent );
				if ( 'page' != $oParent->post_type ) return; 
			}
		}
		
		$this->save( $iPostID, 'update', NULL, $aDataVals, $aFileVals );
		
	}
	
	
	
	
	// save the data
	// 3rd, 4th, and 5th param only used when invoked directly
	public function save(
		$iPostId, $sMode = 'insert', $aParams = NULL, $aDataVals = NULL, $aFileVals = NULL
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
					"SELECT * FROM $wpdb->postmeta WHERE post_id = %d",
					$iPostId
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
				'entity_id' => $iPostId,
				'meta_table' => 'postmeta',
				'meta_member_table' => 'geko_post_meta_members',
				'meta_entity_id_field_name' => 'post_id',
				'meta_id_field_name' => 'meta_id'
			),
			$aDataVals,
			$aFileVals
		);
		
		$this->unsetMetaCache( $iPostId );
	}

	
	
}


