<?php

// abstract
class Geko_Wp_Role_Meta extends Geko_Wp_Options_Meta
{
	protected static $aMetaCache = array();
	
	protected $_sGroupTypeSlug = 'role';
	
	// protected $aPassParams = array();
	
	
	
	//// init
	
	//
	public function affix() {
		
		parent::add();
		
		Geko_Wp_Db::addPrefix( 'geko_role_meta' );
		Geko_Wp_Db::addPrefix( 'geko_role_meta_members' );
		
		return $this;
	}
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_geko_roles_add_fields', array( $this, 'outputAddFields' ) );
		add_action( 'admin_geko_roles_edit_fields', array( $this, 'outputEditFields' ) );
		
		add_action( 'admin_geko_roles_add', array( $this, 'insert' ) );
		add_action( 'admin_geko_roles_edit', array( $this, 'update' ), 10, 2 );
		add_action( 'admin_geko_roles_delete', array( $this, 'delete' ) );
		
		return $this;
	}
	
	// create table
	public function install() {
		
		// create tables
		$sSql = '
			CREATE TABLE %s
			(
				rmeta_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				role_id BIGINT UNSIGNED,
				mkey_id SMALLINT UNSIGNED,
				meta_value LONGTEXT,
				PRIMARY KEY(rmeta_id),
				KEY role_mkey_id (role_id, mkey_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_role_meta', $sSql );

		// create tables
		$sSql = '
			CREATE TABLE %s
			(
				rmeta_id BIGINT UNSIGNED,
				member_id BIGINT UNSIGNED,
				member_value LONGTEXT,
				flags LONGTEXT,
				KEY rmeta_id (rmeta_id),
				KEY member_id (member_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_role_meta_members', $sSql );
		
		return $this;
	}
	
	
	//// accessors
	
	//
	public function getStoredOptions() {
		
		$iRoleId = intval( $_GET[ 'role_id' ] );		// Hacky!
		
		if ( $iRoleId ) {
			
			$this->setMetaCache( $iRoleId );
			
			$aMeta = array();
			$aElemsGroup = parent::getElemsGroup();			// yields correct result!
			$aMetaCache = self::$aMetaCache[ $iRoleId ];
			
			foreach ($aElemsGroup as $sMetaKey => $aElem) {
				if ( isset( $aMetaCache[ $sMetaKey ] ) ) {
					$aMeta[ $sMetaKey ] = $aMetaCache[ $sMetaKey ];
				}
			}
			
			return $aMeta;
		
		} else {
			return array();
		}
	}
	
	
	//
	public function getMeta( $iRoleId, $sMetaKey = '' ) {
		
		$this->setMetaCache( $iRoleId );
		
		if ( $sMetaKey ) {
			return self::$aMetaCache[ $iRoleId ][ $this->getPrefixWithSep() . $sMetaKey ];
		} else {
			return self::$aMetaCache[ $iRoleId ];
		}
	}
	

	//// cache helpers
	
	//
	protected function setMetaCache( $iRoleId ) {
		
		if ( !isset( self::$aMetaCache[ $iRoleId ] ) ) {
			
			global $wpdb;
			
			$aFmt = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			r.rmeta_id,
										h.meta_key,
										r.meta_value
						FROM			$wpdb->geko_role_meta r
						LEFT JOIN		$wpdb->geko_meta_key h
							ON			h.mkey_id = r.mkey_id
						WHERE			r.role_id = %d
					",
					$iRoleId
				),
				'meta_key'
			);
			
			////
			$aSubVals = $this->gatherSubMetaValues( $aFmt, 'geko_role_meta_members', 'rmeta_id' );
			
			foreach ( $aFmt as $sKey => $oItem ) {
				if ( isset( $aSubVals[ $oItem->rmeta_id ] ) ) {
					$aFmt[ $sKey ] = $aSubVals[ $oItem->rmeta_id ];
				} else {
					$aFmt[ $sKey ] = maybe_unserialize( $oItem->meta_value );				
				}
			}
			
			self::$aMetaCache[ $iRoleId ] = $aFmt;
		}
	}
	
	
	
	
	
	
	
	
	//// form processing/injection methods
	
	// plug into the add category form
	public function setupFields( $sMode = 'add' ) {
		
		$aParts = $this->extractParts();
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			
			$sLabel = Geko_String::sw( '<label for="%s$1">%s$0</label>', $aPart[ 'label' ], $aPart[ 'name' ] );
			$sFieldGroup = Geko_String::sw( '%s<br />', $aPart[ 'field_group' ] );
			
			if ( 'edit' == $sMode ) {
				$sFields .= '
					<tr class="form-field">
						<th scope="row" valign="top">' . $sLabel . '</th>
						<td>
							' . $sFieldGroup . '
							' . Geko_String::sw( '<span class="description">%s</span>', $aPart[ 'description' ] ) . '
						</td>
					</tr>
				';
			} else {
				$sFields .= '
					<div class="form-field">
						' . $sLabel . '
						' . $sFieldGroup . '
						' . Geko_String::sw( '<p>%s</p>', $aPart[ 'description' ] ) . '
					</div>
				';
			}
		}
		
		echo $sFields;
	}
	
	// plug into the add role form
	public function outputAddFields() {
		$this->setupFields();
	}
	
	// plug into the edit role form
	// $oRole is unused
	public function outputEditFields( $oRole ) {
		$this->setupFields( 'edit' );
	}
	
	
	
	
	
	//// crud methods
	
	//
	public function insert( $oRole ) {
		$this->save( $oRole );
	}
	
	public function update( $oOldGroup, $oNewRole ) {
		$this->save( $oNewRole, 'update' );
	}
	
	public function delete( $oRole ) {
		
		// cleanup all orphaned metadata
		global $wpdb;
		
		// meta
		$wpdb->query("
			DELETE FROM		$wpdb->geko_role_meta
			WHERE			role_id NOT IN (
				SELECT			role_id
				FROM			$wpdb->geko_roles
			)
		");
		
		// members
		$wpdb->query("
			DELETE FROM		$wpdb->geko_role_meta_members
			WHERE			rmeta_id NOT IN (
				SELECT			rmeta_id
				FROM			$wpdb->geko_role_meta
			)
		");
		
	}
	
	// save the data
	public function save(
		$oRole, $sMode = 'insert', $aParams = NULL, $aDataVals = NULL, $aFileVals = NULL
	) {
		
		global $wpdb;
		
		//
		$aElemsGroup = isset( $aParams[ 'elems_group' ] ) ? 
			$aParams[ 'elems_group' ] : 
			$this->getElemsGroup()
		;
		
		$iRoleId = $oRole->getId();
		
		if ( 'update' == $sMode ) {
			$aMeta = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			r.rmeta_id,
										h.meta_key,
										r.meta_value
						FROM			$wpdb->geko_role_meta r
						LEFT JOIN		$wpdb->geko_meta_key h
							ON			h.mkey_id = r.mkey_id
						WHERE			r.role_id = %d
					",
					$iRoleId
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
				'entity_id' => $iRoleId,
				'meta_table' => 'geko_role_meta',
				'meta_member_table' => 'geko_role_meta_members',
				'meta_entity_id_field_name' => 'role_id',
				'meta_id_field_name' => 'rmeta_id',
				'use_mkey_id' => TRUE
			),
			$aDataVals,
			$aFileVals
		);

		// clear the meta cache
		unset( self::$aMetaCache[ $iRoleId ] );
		
	}
	
	
	
}



