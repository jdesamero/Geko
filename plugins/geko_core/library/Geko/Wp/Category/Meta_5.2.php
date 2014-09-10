<?php

// should be abstract
class Geko_Wp_Category_Meta extends Geko_Wp_Options_Meta
{
	protected static $aMetaCache = array();
	protected static $aInheritable = array();
	protected static $bUseTermTaxonomy = TRUE;
	
	protected $bGetInheritable = FALSE;
	
	protected $_bHasDisplayMode = TRUE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		Geko_Wp_Options_MetaKey::init();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_term_meta', 'tm' )
			->fieldBigInt( 'tmeta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'term_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd' ) )
			->fieldLongText( 'meta_value' )
			->fieldBool( 'inherit', array( 'default' => 1 ) )
			->indexKey( 'term_mkey_id', array( 'term_id', 'mkey_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( '##pfx##geko_term_meta_members', 'tmm' )
			->fieldBigInt( 'tmeta_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'member_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'member_value' )
			->fieldLongText( 'flags' )
		;
		
		$this->addTable( $oSqlTable2, FALSE );
		
		
		// register hierarchy functions
		Geko_Wp_TermTaxonomy::init();
		
		return $this;
	}
	
	// create table
	public function install() {
		
		global $wpdb;
		
		parent::install();
		
		Geko_Wp_Options_MetaKey::install();
		
		$this->createTableOnce();
		$this->createTableOnce( $wpdb->geko_term_meta_members );
		
		// create hierarchy functions
		if ( self::$bUseTermTaxonomy ) {
			Geko_Wp_TermTaxonomy::install();
		}
		
		return $this;
		
	}
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_filter( 'admin_init_category', array( $this, 'install' ) );
		add_filter( 'admin_head_category', array( $this, 'addAdminHead' ) );
		
		add_filter( 'admin_category_add_fields_pq', array( $this, 'setupAddFields' ) );
		add_filter( 'admin_category_edit_fields_pq', array( $this, 'setupEditFields' ) );
		
		add_action( 'create_category', array( $this, 'insert' ), 10, 2 );
		add_action(	'edit_category', array( $this, 'update' ), 10, 2 );
		add_action(	'delete_category', array( $this, 'delete' ), 10, 2 );
		
		return $this;
	}
	
	
		
	//
	public static function setUseTermTaxonomy( $bUseTermTaxonomy ) {
		self::$bUseTermTaxonomy = $bUseTermTaxonomy;
	}
	
	
	//// accessors
	
	// HACK!!!
	public function _getCatId() {
		return intval( Geko_String::coalesce( $_GET[ 'tag_ID' ], $_GET[ 'cat_ID' ] ) );
	}
	
	//
	public function getMeta( $iTermId, $sMetaKey = '' ) {
		return $this->_getMeta( $iTermId, $sMetaKey );
	}

	//
	public function getInheritValue( $iTermId, $sMetaKey = '' ) {
		return $this->_getMeta( $iTermId, $sMetaKey, TRUE );
	}
	
	//
	public function _getMeta( $iTermId, $sMetaKey = '', $bInheritValue = FALSE ) {
		
		global $wpdb;
		
		if ( !isset( self::$aMetaCache[ $iTermId ] ) ) {
			$this->setMetaCache( $iTermId );
		}
		
		$sValueKey = ( $bInheritValue ) ? 'inherit' : 'meta_value';
		
		if ( $sMetaKey ) {
			
			if ( isset( self::$aMetaCache[ $iTermId ][ $sMetaKey ] ) ) {
				return self::$aMetaCache[ $iTermId ][ $sMetaKey ][ $sValueKey ];
			} else {
				return ( 'inherit' == $sValueKey ) ? 1 : NULL;
			}
			
		} else {
			
			// get an array of values
			$aPart = array();
			if ( isset( self::$aMetaCache[ $iTermId ] ) ) {
				foreach ( self::$aMetaCache[ $iTermId ] as $sKey => $aMeta ) {
					$aPart[ $sKey ] = $aMeta[ $sValueKey ];
				}
			}
			return $aPart;
			
		}
		
	}
	
	//
	public function getInheritedValue( $iTermId, $sMetaKey = '' ) {
		
		$this->setAncestorMetaCache( $iTermId );
		$this->setInheritable();
		
		if ( $sMetaKey ) {
			
			return $this->resolveInheritedValue( $iTermId, $sMetaKey );
		
		} else {

			// get an array of values
			$aPart = array();
			
			// $aMeta is not being used
			foreach ( self::$aMetaCache[ $iTermId ] as $sKey => $aMeta ) {
				$aPart[ $sKey ] = $this->resolveInheritedValue( $iTermId, $sKey );
			}
			
			return $aPart;
			
		}
	}
	
	
	//
	protected function resolveInheritedValue( $iTermId, $sMetaKey ) {
		
		if ( !isset( self::$aInheritable[ $sMetaKey ] ) ) {
			return $this->getMeta( $iTermId, $sMetaKey );
		} else {
			$iCurrentTermId = $iTermId;
			while ( isset( self::$aMetaCache[ $iCurrentTermId ] ) ) {
				
				$iParentTermId = self::$aMetaCache[ $iCurrentTermId ][ '_parent_term_id' ][ 'meta_value' ];
				
				if (
					( 0 === $iParentTermId ) || 
					( !$this->getInheritValue( $iCurrentTermId, $sMetaKey ) )
				) {
					return  $this->getMeta( $iCurrentTermId, $sMetaKey );
				}
				
				// continue to parent
				$iCurrentTermId = $iParentTermId;
			}
		}
	}
	
	
	//
	protected function setInheritable() {
		
		if ( !$this->bGetInheritable ) {
			$aParts = $this->extractParts();
			foreach ( $aParts as $aPart ) {
				if ( $aPart[ 'inheritable' ] ) {
					self::$aInheritable[ $aPart[ 'name' ] ] = 1;
				}
			}
			$this->bGetInheritable = TRUE;
		}
		
		return $this;
	}
	
	
	//
	public function getStoredOptions() {
		
		if ( $iCatId = $this->_getCatId() ) {
			
			$aMeta = array();
			$aElemsGroup = parent::getElemsGroup();			// yields correct result!
			$aMetaCache = self::getMeta( $iCatId );
			
			foreach ( $aElemsGroup as $sMetaKey => $aElem ) {
				if ( isset( $aMetaCache[ $sMetaKey ] ) ) {
					$aMeta[ $sMetaKey ] = $aMetaCache[ $sMetaKey ];
				}
			}
			
			return $aMeta;
			
		} else {
			return array();
		}
	}
	
	
	
	
	//// cache helpers
	
	//
	protected function setMetaCache( $aTermIds ) {
		
		global $wpdb;
		
		if ( !is_array( $aTermIds ) ) {
			$aTermIds = array( intval( $aTermIds ) );		// wrap as array
		}
		
		$sQuery = "
			SELECT			m.term_id,
							n.meta_key,
							m.meta_value,
							m.inherit,
							m.tmeta_id
			FROM			$wpdb->geko_term_meta m
			
			LEFT JOIN		$wpdb->geko_meta_key n
				ON			n.mkey_id = m.mkey_id
			
			WHERE			m.term_id IN (" . implode( ',', $aTermIds) . ")
			
			UNION
			
			SELECT			t.term_id AS term_id,
							'_parent_term_id' AS meta_key,
							t.parent AS meta_value,
							1 AS inherit,
							0 AS tmeta_id
			FROM			$wpdb->term_taxonomy t
			
			WHERE			t.term_id IN (" . implode( ',', $aTermIds) . ") AND 
							t.taxonomy = 'category'
		";
		
		$aFmt = $wpdb->get_results( $sQuery );
		
		////
		$aSubVals = $this->gatherSubMetaValues( $aFmt, 'geko_term_meta_members', 'tmeta_id' );
				
		foreach ( $aFmt as $oItem ) {
			
			if ( isset( $aSubVals[ $oItem->tmeta_id ] ) ) {
				$mMetaValue = $aSubVals[ $oItem->tmeta_id ];
			} elseif ( '_parent_term_id' == $oItem->meta_key ) {
				$mMetaValue = intval( $oItem->meta_value );
			} else {
				$mMetaValue = maybe_unserialize( $oItem->meta_value );
			}
			
			self::$aMetaCache[ $oItem->term_id ][ $oItem->meta_key ] = array(
				'meta_value' => $mMetaValue,
				'inherit' => $oItem->inherit
			);
		}
		
		return $this;
	}
	
	//
	protected function setAncestorMetaCache( $iTermId ) {
		
		global $wpdb;
		
		// perform an ancestry check
		
		$bGetAncestors = FALSE;
		$iTermIdCheck = $iTermId;
		
		if ( isset( self::$aMetaCache[ $iTermIdCheck ] ) ) {
			while ( isset( self::$aMetaCache[ $iTermIdCheck ] ) ) {
				$iParentTermId = self::$aMetaCache[ $iTermIdCheck ][ '_parent_term_id' ][ 'meta_value' ];
				if ( $iParentTermId ) {
					if ( !isset( self::$aMetaCache[ $iParentTermId ] ) ) {
						$bGetAncestors = TRUE;
						break;
					}
				}
				$iTermIdCheck = $iParentTermId;
			}
		} else {
			$bGetAncestors = TRUE;
		}
		
		// if !$bGetAncestors it means that the full ancestry chain is already cached
		if ( $bGetAncestors ) {
			
			if ( self::$bUseTermTaxonomy ) {
				
				// get all ancestors
				$sQuery = "
					SELECT		$wpdb->term_taxonomy_path( '/', t.term_id ) AS path
					FROM		$wpdb->term_taxonomy t
					WHERE		t.term_id = %d 
				";
				
				$sIds = $wpdb->get_var( $wpdb->prepare( $sQuery, $iTermId ) );
				
				// gather ids to be queried
				$aIds = explode( '/', $sIds );
				$aIdsFiltered = array();
				
			} else {
			
				$aIds = $wpdb->get_col( "SELECT term_id FROM $wpdb->terms" );
			
			}
			
			foreach ( $aIds as $iTermId ) {
				if ( $iTermId && !isset( self::$aMetaCache[ $iTermId ] ) ) {
					$aIdsFiltered[] = $iTermId;
				}
			}
			
			$this->setMetaCache( $aIdsFiltered );
		}
		
		return $this;
	}
		
	
	
	
	//// front-end display methods
	
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		Geko_Once::run( sprintf( '%s::js', __METHOD__ ), array( $this, 'adminHeadJs' ) );
		
	}
	
	// TO DO: This stuff should be enqueued
	public function adminHeadJs() {
		
		$sParentId = Geko_Wp_Admin_Hooks::getCurrentPlugin()->getValue( 'parent_id' );
		
		?><style type="text/css">
			
			.form-field input.checkbox,
			.form-field input.radio {
				width: 20px;
			}
			
			label.side {
				display: inline;
			}
			
			.inherit-toggle label {
				font-style: italic;
			}
			
			#wpcontent select.multi {
				height: 6em;
			}
			
		</style>
		
		<script type="text/javascript">
			
			var ajaxUpdateCallbacks = new Array();
			var parent_id_sel = '#<?php echo $sParentId; ?>';
			
			jQuery( document ).ready( function( $ ) {
				
				// setup
				var catParentId;
				
				var updateFields = function( fade, delay ) {
					
					catParentId = parseInt( $( parent_id_sel ).val() );
					
					$( '.form-field.inheritable' ).each( function() {
						var fieldGroup = $( this ).find( '.field-group' );
						var inheritToggle = $( this ).find( '.inherit-toggle' );
						
						if ( -1 == catParentId ) {
							fieldGroup.showX( fade, delay );
							inheritToggle.hideX( fade, delay );
						} else {
							if ( inheritToggle.find( '.checkbox' ).attr( 'checked' ) ) fieldGroup.hideX( fade, delay );
							else fieldGroup.showX( fade, delay );
							
							inheritToggle.showX( fade, delay );
						}
					} );
					
				}
				
				updateFields();									// update with no effects
				ajaxUpdateCallbacks.push( updateFields );		// register so it's triggered during an ajax request
				
				$( parent_id_sel ).change( function () {
					catParentId = parseInt( $( this ).val() );
					updateFields( true );		// update with effects
				} );
				
				$( '.inherit-toggle .checkbox' ).click( function () {
					var fieldGroup = $( this ).parent().parent().find( '.field-group' );
					
					if ( $( this ).attr( 'checked' ) ) fieldGroup.fadeOut( 200 );
					else fieldGroup.fadeIn( 200 );					
				} );
				
				$( '#addcat' ).ajaxComplete( function ( evt, req, settings) {
					if ( 'add-cat' == settings.action ) {
						
						$( '.inherit-toggle .checkbox' ).each( function() {
							$( this ).attr( 'checked', 'checked' );
						} );
						
						$.each( ajaxUpdateCallbacks, function() {
							this();
						} );
					}
				} );
				
			} );
			
		</script><?php
		
	}
	
	
	
	// do nothing, implement by singleton instance if needed
	public function instancePreFormFields() { }
	
	
	
	
	
	//// form processing/injection methods
	
	// plug into the add category form
	public function setupAddFields( $oCatDoc ) {
		
		$aParts = $this->extractParts();
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			$sFields .= '
				<div class="' . $aPart[ 'row_class' ] . '">
					' . $aPart[ 'label' ] . '
					<div class="' . $aPart[ 'field_group_class' ] . '">
						' . $aPart[ 'field_group' ] . '
					</div>
					' . $aPart[ 'inherit_toggle' ] . '
					' . Geko_String::sw( '<p>%s</p>', $aPart[ 'description' ] ) . '
				</div>
			';
		}
		
		Geko_PhpQuery::last( $oCatDoc[ 'div.form-field' ] )->after( $sFields );
		
		return $oCatDoc;
	}
	
	// plug into the edit category form
	public function setupEditFields( $oCatDoc ) {
		
		$aParts = $this->extractParts();
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			$sFields .= '
				<tr class="' . $aPart[ 'row_class' ] . '">
					<th scope="row" valign="top">' . $aPart[ 'label' ] . '</th>
					<td>
						<div class="' . $aPart[ 'field_group_class' ] . '">
							' . $aPart[ 'field_group' ] . '
						</div>
						' . $aPart[ 'inherit_toggle' ] . '
					' . Geko_String::sw( '<span class="description">%s</span>', $aPart[ 'description' ] ) . '
					</td>
				</tr>
			';
		}
		
		Geko_PhpQuery::last( $oCatDoc[ 'tr.form-field' ] )->after( $sFields );
		
		return $oCatDoc;
	}
	
	// implement this to get _is_inheritable
	public function extractPart( $aPart, $oPqP ) {
		
		$sLabel = Geko_String::sw( '<label for="%s$1">%s$0</label>', $aPart[ 'label' ], $aPart[ 'name' ] );
		
		$sInheritToggle = '';
		$sFieldGroupClass = '';
		$sRowClass = 'form-field';
		
		if ( $oPqP->attr( '_is_inheritable' ) ) {
			
			$aPart[ 'inheritable' ] = TRUE;
			
			$sToggleId = 'inherit-' . $aPart[ 'name' ];
			
			$sChecked = '';
			if ( $iCatId = $this->_getCatId() ) {
				if ( $this->getInheritValue( $iCatId, $aPart[ 'name' ] ) ) {
					$sChecked = 'checked="checked"';
				}
			} else {
				$sChecked = 'checked="checked"';
			}
			
			$sInheritToggle = '
				<div class="inherit-toggle">
					<input type="checkbox" class="checkbox" id="' . $sToggleId . '" name="' . $sToggleId . '" ' . $sChecked . ' value="1" /> 
					<label class="side" for="' . $sToggleId . '">Inherit from parent</label>
				</div>
			';
			$sFieldGroupClass = 'field-group';
			$sRowClass .= ' inheritable';
			
			
		}
		
		$aPart[ 'row_class' ] = $sRowClass;
		$aPart[ 'field_group_class' ] = $sFieldGroupClass;
		$aPart[ 'label' ] = $sLabel;
		$aPart[ 'inherit_toggle' ] = $sInheritToggle;
				
		return $aPart;
	}
	
	
	
	
	//// crud methods
	
	//
	public function insert( $iTermId, $iTermTaxonomyId ) {
		$this->save( $iTermId, $iTermTaxonomyId );
	}
	
	//
	public function update( $iTermId, $iTermTaxonomyId ) {
		$this->save( $iTermId, $iTermTaxonomyId, 'update' );	
	}
	
	
	// save the data
	public function save(
		$iTermId, $iTermTaxonomyId, $sMode = 'insert', $aParams = NULL, $aDataVals = NULL, $aFileVals = NULL
	) {
		
		global $wpdb;
		
		//
		$aElemsGroup = isset( $aParams[ 'elems_group' ] ) ? 
			$aParams[ 'elems_group' ] : 
			$this->getElemsGroup()
		;
		
		if ( 'update' == $sMode ) {
			$aMeta = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			m.term_id,
										n.meta_key,
										m.meta_value,
										m.inherit,
										m.tmeta_id
						FROM				$wpdb->geko_term_meta m
					
						LEFT JOIN		$wpdb->geko_meta_key n
							ON			n.mkey_id = m.mkey_id
					
						WHERE			m.term_id = %d
					",
					$iTermId
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
				'entity_id' => $iTermId,
				'meta_table' => 'geko_term_meta',
				'meta_member_table' => 'geko_term_meta_members',
				'meta_entity_id_field_name' => 'term_id',
				'meta_id_field_name' => 'tmeta_id',
				'use_mkey_id' => TRUE
			),
			$aDataVals,
			$aFileVals
		);
		
		// clear the meta cache
		unset( self::$aMetaCache[ $iTermId ] );
		
	}
	
	//
	protected function commitMetaDataValue( $aVals, $oMeta, $sMetaKey, $aParams ) {
		$aVals[ 'inherit' ] = intval( $_POST[ 'inherit-' . $sMetaKey ] );
		return $aVals;
	}
	
	//
	protected function commitMetaDataValueChanged( $aVals, $oMeta ) {
		return ( $oMeta->inherit != $aVals[ 'inherit' ] );
	}
	
	
	
	//
	public function delete( $iTermId, $iTermTaxonomyId ) {
		
		// cleanup all orphaned metadata
		global $wpdb;
				
		// meta
		$wpdb->query("
			DELETE FROM		$wpdb->geko_term_meta
			WHERE			term_id NOT IN (
				SELECT			term_id
				FROM			$wpdb->terms
			)
		");
		
		// members
		$wpdb->query("
			DELETE FROM		$wpdb->geko_term_meta_members
			WHERE			tmeta_id NOT IN (
				SELECT			tmeta_id
				FROM			$wpdb->geko_term_meta
			)
		");
		
	}
	
	
	
	//// debugging
	
	//
	public function debug() {
		
		/* /
		//$this->getMeta( 42 );
		
		$this->getInheritedValue( $this->_getCatId() );
		
		//$this->getInheritedValue( 37 );
		
		if ( self::$aMetaCache ) {
			print_r( self::$aMetaCache );
		} else {
			var_dump( self::$aMetaCache );
		}
		/* */
		
		
		/* /
		$aParts = $this->extractParts();
		
		echo $this->getInheritedValue( $this->_getCatId(), 'ilpa-listing_page_fields' ) . '<br />';
		echo $this->getInheritedValue( $this->_getCatId(), 'ilpa-details_page_fields' ) . '<br />';
		echo $this->getInheritedValue( $this->_getCatId(), 'ilpa-my_fruits' ) . '<br />';
		echo $this->getInheritedValue( $this->_getCatId(), 'ilpa-backup_daily' ) . '<br />';
		echo $this->getInheritedValue( $this->_getCatId(), 'ilpa-my_fishes' ) . '<br />';
		/* */
		
		// print_r( $aParts );
		
	}
	
	
}



