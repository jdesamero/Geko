<?php

//
class Geko_Wp_Options_Manage extends Geko_Wp_Options
{
	//
	protected $_sSubject;
	protected $_sSubjectPlural;
	protected $_sSlug;
	protected $_sDescription;
	
	protected $_sPageTitle;
	protected $_sMenuTitle;
	protected $_sListingTitle;
	
	protected $_sEntityClass;
	protected $_sQueryClass;
	protected $_sServiceClass;
	protected $_iCurrentEntityId;
	protected $_oCurrentEntity;
	protected $_sEntityIdVarName = 'entity_id';
	
	protected $_sParentEntityClass;
	protected $_sParentManageClass;
	protected $_iCurrentParentEntityId;
	protected $_oCurrentParentEntity;
	protected $_sParentEntityIdVarName = 'parent_entity_id';
		
	protected $_sIconId = 'icon-tools';
	protected $_iEntitiesPerPage = 15;
	protected $_bShowTotalItems = TRUE;
	protected $_bHasKeywordSearch = FALSE;
	
	protected $_sNamespace = 'geko';
	protected $_sType;
	protected $_sNestedType;
	protected $_sActionPrefix = '';
	protected $_sActionTarget = '';
	protected $_sAddAction;
	protected $_sEditAction;
	protected $_sDelAction;
	protected $_bSubMainFields = FALSE;
	protected $_bSubExtraFields = FALSE;
	protected $_bUpdateRelatedEntities = FALSE;			// for mix-in use only
	protected $_bExtraForms = FALSE;
	protected $_sEditFormId = 'editform';

	protected $_aCustomActions = array();
	protected $_aNormalizedCustomActions = array();
	protected $_aJsParams = array();
	protected $_aNormalizedJsParams = array();
	protected $_aJsEnqueue = array();
	
	protected $_sShortCode = '';
	
	protected $_sManagementCapability;
	protected $_bHasManagementCapability = FALSE;
	
	private static $aFixMenuInstances = array();
	private static $aMenuInstanceCurrent = array();
	
	protected $_bHasDisplayMode = TRUE;
	
	protected $_bCanImport = FALSE;
	protected $_bCanExport = FALSE;
	protected $_bCanDuplicate = FALSE;
	protected $_bCanRestore = FALSE;
	
	
	
	//// methods
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		//
		$this->_sEntityClass = Geko_Class::resolveRelatedClass(
			$this, '_Manage', '', $this->_sEntityClass
		);
		
		//
		$this->_sQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Query', $this->_sQueryClass
		);
		
		//
		$this->_sServiceClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Service', $this->_sServiceClass
		);
		
		//
		if ( $this->_sParentEntityClass ) {
			$this->_sParentManageClass = Geko_Class::resolveRelatedClass(
				$this->_sParentEntityClass, '', '_Manage', $this->_sParentManageClass
			);
		}
		
		//
		if ( !$this->_sActionPrefix ) {
			$this->_sActionPrefix = 'admin_' . $this->_sNamespace . '_' . $this->_sType;
		}
		
		// normalize any custom actions
		foreach ( $this->_aCustomActions as $sKey => $aAction ) {
			
			$aActionNormalized = array();
			
			$aActionNormalized[ 'mode' ] = ( $aAction[ 'mode' ] ) ? $aAction[ 'mode' ] : 'edit' ;
			$aActionNormalized[ 'req_key' ] = ( $aAction[ 'req_key' ] ) ? $aAction[ 'req_key' ] : $sKey ;
			$aActionNormalized[ 'method' ] = ( $aAction[ 'method' ] ) ? $aAction[ 'method' ] : ( 'do' . Geko_Inflector::camelize( $sKey ) . 'Action' ) ;
			
			//// hidden_field: name, value
			
			if ( $mHiddenFld = $aAction[ 'hidden_field' ] ) {
				
				$sName = '';
				$sValue = '';
				
				if ( is_string( $mHiddenFld ) ) {
					$sName = $mHiddenFld;
				} elseif ( is_array( $mHiddenFld ) ) {
					if ( $mHiddenFld[ 'name' ] ) $sName = $mHiddenFld[ 'name' ];
					if ( $mHiddenFld[ 'value' ] ) $sValue = $mHiddenFld[ 'value' ];
				}
				
				if ( !$sName ) $sName = $sKey;
				if ( !$sValue ) $sValue = '0';
				
				$aActionNormalized[ 'hidden_field' ] = array(
					'name' => $sName,
					'value' => $sValue
				);
			}
			
			//// button: id, btn_title
			
			if ( $mButton = $aAction[ 'button' ] ) {

				$sId = '';
				$sBtnTitle = '';
				
				if ( is_string( $mButton ) ) {
					$sId = $mButton;
				} elseif ( is_array( $mButton ) ) {
					if ( $mButton[ 'id' ] ) $sId = $mButton[ 'id' ];
					if ( $mButton[ 'btn_title' ] ) $sBtnTitle = $mButton[ 'btn_title' ];
				}
				
				if ( !$sId ) $sId = $sKey . '_btn';
				if ( !$sBtnTitle ) $sBtnTitle = Geko_Inflector::humanize( $sKey );
				
				$aActionNormalized[ 'button' ] = array(
					'id' => $sId,
					'btn_title' => $sBtnTitle
				);
			}
			
			//// dialog: id
			
			if ( $mDialog = $aAction[ 'dialog' ] ) {

				$sId = '';
				
				if ( is_string( $mDialog ) ) {
					$sId = $mDialog;
				} elseif ( is_array( $mDialog ) ) {
					if ( $mDialog[ 'id' ] ) $sId = $mDialog[ 'id' ];
				}
				
				if ( !$sId ) $sId = $sKey;
				
				$aActionNormalized[ 'dialog' ] = array( 'id' => $sId );
			}
			
			$this->_aNormalizedCustomActions[ $sKey ] = $aActionNormalized;
		}
		
		// normalize any javascript parameters
		foreach ( $this->_aJsParams as $sKey => $aParams ) {
			
			$aParamsNormalized = array();
			
			if ( 'row_template' == $sKey ) {
				
				$this->_aJsEnqueue = Geko_Array::pushUnique( $this->_aJsEnqueue, 'geko_phpquery_formtransform_plugin_rowtemplate' );
				
				if ( 0 == count( $aParams ) ) {
					$aParams[ $this->_sType ] = array();
				}
				
				foreach ( $aParams as $sItemKey => $aItmPrms ) {
					
					$sGroupName = ( $aItmPrms[ 'group_name' ] ) ? $aItmPrms[ 'group_name' ] : $sItemKey;
					
					$aItem = array(
						'group_sel' => ( $aItmPrms[ 'group_sel' ] ) ? $aItmPrms[ 'group_sel' ] : '#editform td.multi_row.' . $sGroupName,
						'group_name' => $sGroupName,
						'row_container_sel' => ( $aItmPrms[ 'row_container_sel' ] ) ? $aItmPrms[ 'row_container_sel' ] : '> table',
						'row_sel' => ( $aItmPrms[ 'row_sel' ] ) ? $aItmPrms[ 'row_sel' ] : '> tbody > tr.row',
						'row_template_sel' => ( $aItmPrms[ 'row_template_sel' ] ) ? $aItmPrms[ 'row_template_sel' ] : '> tbody > tr._row_template',
						'add_row_sel' => ( $aItmPrms[ 'add_row_sel' ] ) ? $aItmPrms[ 'add_row_sel' ] : '> p > input.add_row',
						'del_row_sel' => ( $aItmPrms[ 'del_row_sel' ] ) ? $aItmPrms[ 'del_row_sel' ] : '> tbody > tr.row > td > a.del_row'					
					);
					
					if ( isset( $aItmPrms[ 'sortable' ] ) ) {
						
						$this->_aJsEnqueue = Geko_Array::pushUnique( $this->_aJsEnqueue, 'geko-jquery-ui-sortable' );
						
						$aSrtPrms = $aItmPrms[ 'sortable' ];
						$aSortable = array(
							'sort_sel' => ( $aSrtPrms[ 'sort_sel' ] ) ? $aSrtPrms[ 'sort_sel' ] : 'table > tbody',
							'rank_sel' => ( $aSrtPrms[ 'rank_sel' ] ) ? $aSrtPrms[ 'rank_sel' ] : '.' . $sGroupName . '_rank',
							'col_sel' => ( $aSrtPrms[ 'col_sel' ] ) ? $aSrtPrms[ 'col_sel' ] : 'table > thead > tr > th.sort',
							'col_class' => ( $aSrtPrms[ 'col_class' ] ) ? $aSrtPrms[ 'col_class' ] : 'sort',
							'col_pfx' => ( $aSrtPrms[ 'col_pfx' ] ) ? $aSrtPrms[ 'col_pfx' ] : $sGroupName . '-col_',
							'fld_pfx' => ( $aSrtPrms[ 'fld_pfx' ] ) ? $aSrtPrms[ 'fld_pfx' ] : $sGroupName . '_'
						);
						$aItem[ 'sortable' ] = $aSortable;
					}

					if ( isset( $aItmPrms[ 'toggle_column' ] ) ) {
						$aTgCol = $aItmPrms[ 'toggle_column' ];
						$aToggleColumn = array(
							'btn_sel' => ( $aTgCol[ 'btn_sel' ] ) ? $aTgCol[ 'btn_sel' ] : '.' . $sGroupName . '_toggle_column',
							'id_pfx' => ( $aTgCol[ 'id_pfx' ] ) ? $aTgCol[ 'id_pfx' ] : $sGroupName . '-tc_',
							'col_pfx' => ( $aTgCol[ 'col_pfx' ] ) ? $aTgCol[ 'col_pfx' ] : $sGroupName . '-col_'
						);
						$aItem[ 'toggle_column' ] = $aToggleColumn;
					}
					
					$aParamsNormalized[ $sItemKey ] = $aItem;
				}
				
			} elseif ( 'conditional_toggle' == $sKey ) {
				
				foreach ( $aParams as $sItemKey => $aItmPrms ) {
					
					$aItem = array(
						'widget_id' => ( $aItmPrms[ 'widget_id' ] ) ? $aItmPrms[ 'widget_id' ] : $sItemKey,
						'widget_cont_sel' => ( $aItmPrms[ 'widget_cont_sel' ] ) ? $aItmPrms[ 'widget_cont_sel' ] : 'td',		// widget container selector
						'group_sel' => ( $aItmPrms[ 'group_sel' ] ) ? $aItmPrms[ 'group_sel' ] : '#editform',
						'cond_sel' => ( $aItmPrms[ 'cond_sel' ] ) ? $aItmPrms[ 'cond_sel' ] : '.cond',
						'desc_sel' => ( $aItmPrms[ 'desc_sel' ] ) ? $aItmPrms[ 'desc_sel' ] : 'span.description'
					);
					
					$aConds = array();
					
					if ( $sEnumKey = $aItmPrms[ 'enum' ] ) {
						
						$sSlugPfx = ( $aItmPrms[ 'slug_pfx' ] ) ? $aItmPrms[ 'slug_pfx' ] : $sEnumKey . '-';
						
						$aEnum = Geko_Wp_Enumeration_Query::getSet( $sEnumKey );
						
						foreach ( $aEnum as $oEnum ) {
							$sSlug = str_replace( $sSlugPfx, '', $oEnum->getSlug() );
							$aConds[ $sSlug ] = array(
								'val' => $oEnum->getValue(),
								'desc' => $oEnum->getContent()
							);
						}
						
					}
					
					$aItem[ 'conditions' ] = $aConds;
					
					// TO DO: other types
					
					$aParamsNormalized[ $sItemKey ] = $aItem;
				}
				
			}
			
			$this->_aNormalizedJsParams[ $sKey ] = $aParamsNormalized;			
		}
		
	}
	
	//
	public function add() {
		
		parent::add();
		
		global $current_user;
		
		//		
		$this->_sSlug = $this->_sSlug ? $this->_sSlug : sanitize_title( $this->_sSubject );
		$this->_sSubjectPlural = $this->_sSubjectPlural ? $this->_sSubjectPlural : Geko_Inflector::pluralize( $this->_sSubject );
		$this->_sManagementCapability = 'manage_' . str_replace( '-', '_', strtolower( $this->_sEntityClass ) );
		
		// see if the current user has management capabilities and store it
		if (
			is_user_logged_in() && 
			$current_user && 
			$current_user->has_cap( $this->_sManagementCapability ) 
		) {
			$this->_bHasManagementCapability = TRUE;
		}
		
		// shortcode handler
		if ( $this->_sShortCode ) {
			add_shortcode( $this->_sShortCode, array( $this, 'applyShortCode' ) );
		}
				
		// trigger sub-actions
		if ( $oSubMng = $this->_oSubOptionParent ) {
			
			$sSubAction = $oSubMng->getActionPrefix();
			
			add_action( $sSubAction . '_init_entities', array( $this, 'initEntities' ), 10, 2 );
			add_filter( $sSubAction . '_getstoredopts', array( $this, 'getStoredSubOptions' ), 10, 3 );
			add_filter( $sSubAction . '_getdefaultopts', array( $this, 'getDefaultSubOptions' ), 10, 2 );
			
			add_action( $sSubAction . '_add', array( $this, 'doSubAddAction' ), 10, 2 );
			add_action( $sSubAction . '_edit', array( $this, 'doSubEditAction' ), 10, 4 );
			add_action( $sSubAction . '_delete', array( $this, 'doSubDelAction' ), 10, 2 );
			
			if ( $this->_bSubMainFields ) {
				
				add_action( $sSubAction . '_main_fields', array( $this, 'formFields' ), 10, 3 );
				add_action( $sSubAction . '_sub_main_field_titles', array( $this, 'subMainFieldTitles' ), 10, 2 );			// ???
				add_action( $sSubAction . '_sub_main_field_columns', array( $this, 'subMainFieldColumns' ), 10, 2 );			// ???
				add_filter( $sSubAction . '_getstoredsubopts', array( $this, 'getStoredSubOptions' ), 10, 3 );
				
				add_action( $sSubAction . '_subadd', array( $this, 'doSubAddAction' ), 10, 3 );
				add_action( $sSubAction . '_subedit', array( $this, 'doSubEditAction' ), 10, 4 );
				add_action( $sSubAction . '_subdelete', array( $this, 'doSubDelAction' ), 10, 3 );
				
			}
			
			if ( $this->_bSubExtraFields ) {
				add_action( $sSubAction . '_extra_fields', array( $this, 'outputForm' ), 10, 2 );
			}
			
			if ( $this->_bExtraForms ) {
				add_action( $sSubAction . '_extra_forms', array( $this, 'extraForms' ), 10, 2 );
			}
						
		} else {

			if ( $this->_bExtraForms ) {
				add_action( $this->_sActionPrefix . '_extra_forms', array( $this, 'extraForms' ), 10, 2 );
			}
			
		}
		
		return $this;
	}
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_init', array( $this, 'doActions' ) );
		
		//
		$this->_sPageTitle = $this->_sPageTitle ? $this->_sPageTitle : $this->_sSubjectPlural;
		$this->_sMenuTitle = $this->_sMenuTitle ? $this->_sMenuTitle : $this->_sSubjectPlural;
		$this->_sListingTitle = $this->_sListingTitle ? $this->_sListingTitle : $this->_sSubject;
		
		$this->_sAddAction = $this->_sAddAction ? $this->_sAddAction : 'add' . $this->_sType;
		$this->_sEditAction = $this->_sEditAction ? $this->_sEditAction : 'edit' . $this->_sType;
		$this->_sDelAction = $this->_sDelAction ? $this->_sDelAction : 'delete' . $this->_sType;
		
		
		// list mode is default
		$this->_sCurrentDisplayMode = 'list';
		
		// correct $this->_sCurrentPage so that it corresponds to an actual class
		if (
			( strrpos( $this->_sCurrentPage, $this->_sAddModeSuffix ) ) == 
			( $iLen = strlen( $this->_sCurrentPage ) - strlen( $this->_sAddModeSuffix ) )
		) {
			$this->_sCurrentDisplayMode = 'add';
			$this->_sCurrentPage = substr( $this->_sCurrentPage, 0, $iLen );
		}
		
		// check for current page
		if ( $this->isCurrentPage() ) {
			
			// same as instance class
			
			// detected for edit mode (there is a value for current entity id)
			if ( $this->_iCurrentEntityId = $this->getCurrentEntityId() ) {
				$this->_sCurrentDisplayMode = 'edit';
			}
			
			if ( $this->_sParentManageClass ) {
				$this->_iCurrentParentEntityId = $this->getCurrentParentEntityId();	
			}
			
		} elseif (
			class_exists( $this->_sCurrentPage ) && 
			( $this->_sCurrentPage instanceof Geko_Singleton_Abstract )
		) {
			
			$sClass = __CLASS__;
			$oCurPage = Geko_Singleton_Abstract::getInstance( $this->_sCurrentPage );
			
			if ( $oCurPage instanceof $sClass ) {
				
				// unset the display mode since this class is not the current "page"
				$this->_sCurrentDisplayMode = '';
				
				// determine the relationship of this class to the current "page"
				if ( $this->_sInstanceClass == $oCurPage->getParentManageClass() ) {
					
					// the parent of current page
					$this->_iCurrentEntityId = $oCurPage->getCurrentParentEntityId();
					
				} elseif ( $this->_sParentManageClass == $oCurPage->getParentManageClass() ) {
					
					// sibling of current page
					$this->_iCurrentParentEntityId = $oCurPage->getCurrentParentEntityId();
					
				} elseif ( $this->_sCurrentPage == $this->_sParentManageClass ) {
					
					// child of current page
					$this->_iCurrentParentEntityId = $oCurPage->getCurrentEntityId();	
					
				}
				
			}
			
		}
		
		// trigger tab group
		if ( count( $this->_aTabGroup ) > 0 ) {
			if ( !$this->_sTabGroupTitle ) $this->_sTabGroupTitle = $this->_sMenuTitle;
			Geko_Wp_Admin_Menu::addTabGroup( $this->_aTabGroup );
		}
		
		// perform corrections to the menu if class has a parent management class
		if ( $this->_sParentManageClass || Geko_Wp_Admin_Menu::inTabGroup( $this->_sInstanceClass ) ) {
			add_action( 'admin_page_source', array( $this, 'fixMenu' ) );
		}
		
		// assign management capabilities to the admin role
		$oWpRole = get_role( 'administrator' );
		if ( !$oWpRole->has_cap( $this->_sManagementCapability ) ) {
			$oWpRole->add_cap( $this->_sManagementCapability );
		}
		
		// $oWpRole->remove_cap( 'manage_' . str_replace( '-', '_', strtolower( $this->_sInstanceClass ) ) );
		// $oWpRole->remove_cap( '' );
		
		
		return $this;
	}
	
	
	//
	public function applyShortCode( $aAtts ) {
		return '';
	}
	


	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();

		if ( $this->isDisplayMode( 'add|edit' ) ) {
			foreach ( $this->_aJsEnqueue as $sScriptHandle ) {
				wp_enqueue_script( $sScriptHandle );
			}
		}
		
		wp_enqueue_script( 'geko_wp_options_manage' );
		
		wp_enqueue_style( 'geko-jquery-wpadmin' );
		
		return $this;
	}
	
	//
	public function addAdminHead( $oPlugin = NULL ) {
		
		parent::addAdminHead( $oPlugin );
		
		$aParams = array(
			'script' => array(
				'curpage' => strval( Geko_Uri::getGlobal() )
			),
			'mng' => array(
				'type' => $this->_sType,
				'nested_type' => $this->_sNestedType,				
				'subject' => $this->_sSubject,
				'display_mode' => $this->getDisplayMode(),
				'edit_form_id' => $this->_sEditFormId
			),
			'can' => array(
				'import' => $this->_bCanImport,
				'export' => $this->_bCanExport,
				'duplicate' => $this->_bCanDuplicate,
				'restore' => $this->_bCanRestore
			),
			'custom_actions' => $this->_aNormalizedCustomActions,
			'js_params' => $this->_aNormalizedJsParams
		);
		
		?><script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aParams ); ?>;
				
				<?php $this->outputAdminHeadMainJs(); ?>
				
				$.gekoWpOptionsManage( oParams );
				
			} );
			
		</script><?php
		
		return $this;
	}
	
	//
	public function outputAdminHeadMainJs() { }
	
	
	
	//
	public function initEntities() {
		
		if ( $this->_iCurrentEntityId && !$this->_oCurrentEntity ) {
			$this->_oCurrentEntity = new $this->_sEntityClass( $this->_iCurrentEntityId );
			if ( $this->_oCurrentEntity->isValid() ) {
				do_action( $this->_sActionPrefix . '_init_entities', $this->_oCurrentEntity );
			}
		}
		
		if ( $this->_iCurrentParentEntityId && !$this->_oCurrentParentEntity ) {
			$this->_oCurrentParentEntity = Geko_Singleton_Abstract::getInstance( $this->_sParentManageClass )
				->initEntities()
				->getCurrentEntity()
			;
		}
		
		return $this;
	}
	
	//
	public function attachPage() {
		
		$this->initEntities();
		
		if ( $this->enablePage() ) {
			
			if ( count( $this->_aTabGroup ) > 0 ) {
				add_menu_page( $this->_sTabGroupTitle, $this->_sTabGroupTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
			}
			
			if ( Geko_Wp_Admin_Menu::inTabGroup( $this->_sInstanceClass ) ) {
				
				// add as sub-items of tab group
				$sSubmenuHandle = Geko_Wp_Admin_Menu::getTabParent( $this->_sInstanceClass );
				add_submenu_page( $sSubmenuHandle, $this->_sPageTitle, $this->_sMenuTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
				add_submenu_page( $sSubmenuHandle, '', '', $this->_sManagementCapability, $this->_sInstanceClass . $this->_sAddModeSuffix, array( $this, 'detailsPage' ) );
				
			} else {
			
				if ( $this->_sParentEntityClass ) {
					
					$sSubmenuHandle = $this->_sParentManageClass;
					$sEditTitle = $sAddTitle = '';
					
					$aParams = array();
					if ( $this->_sInstanceClass == $this->_sCurrentPage ) {
						$aParams[ 'current' ] = TRUE;
						self::$aMenuInstanceCurrent[ $this->_sParentManageClass ] = TRUE;
					}
					
					$sUrl = sprintf( '%s?page=%s&%s=%d', Geko_Uri::getUrl( 'wp_admin' ), $this->_sInstanceClass, $this->_sParentEntityIdVarName, $this->_iCurrentParentEntityId );
					Geko_Wp_Admin_Menu::addMenu( $this->_sParentManageClass, $this->_sMenuTitle, $sUrl, $aParams );
					
				} else {
					
					if ( $iEntityId = $this->_iCurrentEntityId ) {
						$sUrl = sprintf( '%s?page=%s&%s=%d', Geko_Uri::getUrl( 'wp_admin' ), $this->_sEntityIdVarName, $iEntityId );
						Geko_Wp_Admin_Menu::addMenu( $this->_sInstanceClass, 'Details', $sUrl );
					}
					
					$sSubmenuHandle = $this->_sInstanceClass;
					$sEditTitle = 'Edit';
					$sAddTitle = 'Add New';
					
					add_menu_page( $this->_sPageTitle, $this->_sMenuTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
				}
				
				add_submenu_page( $sSubmenuHandle, $this->_sPageTitle, $sEditTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
				add_submenu_page( $sSubmenuHandle, $this->_sPageTitle, $sAddTitle, $this->_sManagementCapability, $this->_sInstanceClass . $this->_sAddModeSuffix, array( $this, 'detailsPage' ) );
			}
			
		}
		
	}
	
	
	
	//// accessors
	
	// implemented by main entities
	public function getStoredOptions( $oPlugin = NULL ) {
		
		$aRet = array();
		
		if ( $oEntity = $this->_oCurrentEntity ) {
			
			$aFields = $this->getPrimaryTableFields();
			
			foreach ( $aFields as $oField ) {
				$sFieldName = $oField->getFieldName();
				$aRet[ $this->_sType . '_' . $sFieldName ] = $oEntity->getEntityPropertyValue( $sFieldName );
			}
			
			// hook method
			$aRet = $this->modifyStoredOptions( $aRet, $oEntity );
			$sAction = $this->_sActionPrefix . '_getstoredopts';
			
			$aRet = apply_filters( $sAction, $aRet, $oEntity, $this );
			
			if ( $oEntity->hasEntityProperty( 'slug' ) ) {
				$aRet = apply_filters( $sAction . '_' . $oEntity->getSlug(), $aRet, $oEntity );
			}
			
		} else {
			$aRet = $this->getDefaultOptions( $aRet, $oPlugin );
		}
		
		return $aRet;
	}
	
	// implemented by sub-entities
	public function getStoredSubOptions( $aRet, $oMainEnt, $oPlugin = NULL ) {
		
		if ( $oMainMng = $this->_oSubOptionParent ) {
			
			$aRet[ $this->_sType ] = $this->getSubEntities(
				$this->getStoredSubOptionParams( $oMainMng, $oMainEnt )
			);
			
			$sAction = $this->_sActionPrefix . '_getstoredsubopts';
			$aRet = apply_filters( $sAction, $aRet, $oMainEnt, $this );
			
		}
		
		return $aRet;
	}
	
	//
	public function getStoredSubOptionParams( $oMainMng, $oMainEnt ) {
		
		$aParams = array(
			$oMainMng->getEntityIdVarName() => $oMainEnt->getId(),
			'showposts' => -1,
			'posts_per_page' => -1
		);
		
		return $aParams;
	}
	
	
	
	//// default option hooks
	
	//
	public function getDefaultOptions( $aRet, $oPlugin = NULL ) {
		$sAction = $this->_sActionPrefix . '_getdefaultopts';
		$aRet = apply_filters( $sAction, $aRet, $this );
		return $aRet;
	}
	
	//
	public function getDefaultSubOptions( $aRet ) {
		return $aRet;
	}
	
	//
	public function modifyStoredOptions( $aRet, $oEntity ) {
		if ( $this->_sNestedType ) {
			
			// TO DO: add hook
			$aParams = array(
				'parent_id' => $oEntity->getId(),
				'showposts' => -1,
				'posts_per_page' => -1
			);
			
			$aRet[ $this->_sNestedType ] = $this->getSubEntities( $aParams );
		}
		return $aRet;
	}
	
	// helper
	public function getSubEntities( $aParams ) {
		
		$aSubFmt = array();
		
		if ( $sQueryClass = $this->_sQueryClass ) {
			
			$aFields = $this->getPrimaryTableFields();
			$aParams = $this->modifySubEntityParams( $aParams );
			$aSubs = new $sQueryClass( $aParams, FALSE );
			
			foreach ( $aSubs as $i => $oSubItem ) {
				$mSubId = ( $this->_sEntityIdVarName ) ? $oSubItem->getId() : $i;
				$aRow = array();
				foreach ( $aFields as $mField ) {
					$sFieldName = ( is_object( $mField ) ) ? $mField->getFieldName() : $mField;
					$aRow[ $sFieldName ] = $oSubItem->getEntityPropertyValue( $sFieldName );
				}
				$aRow = $this->modifySubEntityValues( $aRow, $oSubItem );
				$aSubFmt[ $mSubId ] = $aRow;
			}
			
		}
		
		return $aSubFmt;
	}
	
	// helper hook
	public function modifySubEntityParams( $aParams ) {
		return $aParams;
	}
	
	// helper hook
	public function modifySubEntityValues( $aRow, $oSubItem ) {
		return $aRow;
	}
	
	
	
	//
	public function getCurrentEntityId() {
		if ( $this->_oCurrentEntity ) return $this->_oCurrentEntity->getId();
		return intval( Geko_String::coalesce( $_GET[ $this->_sEntityIdVarName ], $_POST[ $this->_sEntityIdVarName ] ) );
	}
	
	//
	public function getCurrentParentEntityId() {
		return intval( Geko_String::coalesce( $_GET[ $this->_sParentEntityIdVarName ], $_POST[ $this->_sParentEntityIdVarName ] ) );
	}
	
	//
	public function getParentManageClass() {
		return $this->_sParentManageClass;
	}
	
	//
	public function getDetailsMenuHandle() {
		return $this->_sParentManageClass ? $this->_sParentManageClass : $this->_sInstanceClass;
	}
	
	//
	public function userHasManagementCapability() {
		return $this->_bHasManagementCapability;
	}
	
	//
	public function enablePage() {
		
		if ( $this->_sParentEntityClass ) {
			return ( $this->_oCurrentParentEntity ) ? TRUE : FALSE;
		}
		
		return TRUE;
	}
	
	//
	public function getPageNum() {
		return ( $_GET[ 'pagenum' ] ) ? intval( $_GET[ 'pagenum' ] ) : 1;
	}

	//
	public function getCurrentEntity( $oPlugin = NULL ) {
		
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getCurrentEntity();
		}
		
		return $this->_oCurrentEntity;
	}
	
	//
	public function setCurrentEntity( $oEntity ) {
		$this->_oCurrentEntity = $oEntity;
		$this->_iCurrentEntityId = $oEntity->getId();
		return $this;
	}
	
	//
	public function setSubject( $sSubject ) {
		$this->_sSubject = $sSubject;
		return $this;
	}
	
	//
	public function getSubject() {
		return $this->_sSubject;
	}

	//
	public function getSubjectPlural() {
		return $this->_sSubjectPlural;
	}
	
	//
	public function getEntityIdVarName() {
		return $this->_sEntityIdVarName;
	}
	
	//
	public function getActionPrefix() {
		return $this->_sActionPrefix;
	}
	
	//
	public function getSlug() {
		return $this->_sSlug;
	}
	
	//
	public function getEntityClass() {
		return $this->_sEntityClass;
	}
	
	//
	public function getType() {
		return $this->_sType;
	}
	
	//
	public function getNamespace() {
		return $this->_sNamespace;
	}
	
	//
	public function getPageTitle() {
		return $this->_sPageTitle;
	}
	
	//
	public function getMenuTitle() {
		return $this->_sMenuTitle;
	}
		
	// return a prefix
	public function getUpdateRelatedEntities( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getUpdateRelatedEntities();
		}
		return $this->_bUpdateRelatedEntities;
	}
	
	
	
	//// error message handling
	
	//
	protected function getNotificationMsgs() {
		return array(
			'm101' => $this->_sSubject . ' added successfully.',
			'm102' => $this->_sSubject . ' was updated successfully.',
			'm103' => $this->_sSubject . ' was deleted successfully.',
			'm104' => $this->_sSubject . ' was duplicated successfully.',
			'm105' => '%d of %d ' . $this->_sSubjectPlural . ' was imported successfully!',
			'm106' => $this->_sSubject . ' was restored successfully.'
		);
	}
	
	//
	protected function getErrorMsgs() {
		return array(
			'm201' => 'Please specify a title for the ' . strtolower( $this->_sSubject ) . '.',
			'm202' => 'Bad ' . strtolower( $this->_sSubject ) . ' id given. Operation cannot be completed.',
			'm203' => 'A type was not specified for the ' . strtolower( $this->_sSubject ) . '.',
			'm204' => "You don't have enough privileges to manage " . strtolower( $this->_sSubjectPlural ) . '.',
			'm205' => 'The specified ' . $this->_sSubject . ' cannot be duplicated!',
			'm206' => 'Please specify a name for the ' . $this->_sSubject . ' to be duplicated!',
			'm207' => 'Failed to duplicate the ' . $this->_sSubject . '! Please try again.',
			'm208' => 'There was no ' . $this->_sSubject . ' import file uploaded. Please upload a valid %s file!',
			'm209' => 'There was an error with the ' . $this->_sSubject . ' import file upload. Please try again.',
			'm210' => 'Incorrect ' . $this->_sSubject . ' import file uploaded. Please upload a valid %s file!',
			'm211' => 'An invalid ' . $this->_sSubject . ' was specified!',
			'm212' => 'There was an error parsing the ' . $this->_sSubject . ' import file. Please try again.',
			'm213' => '%d of %d ' . $this->_sSubjectPlural . ' was partially imported. Please check for any errors.',
			'm214' => 'Unable to restore the ' . $this->_sSubject . '. Please try again.'
		);
	}
	
	
	
	
	//// DOM manipulation methods
	
	//
	public function fixMenu( $sContent ) {
		
		if ( $this->_sParentManageClass ) {
			$sHandle = $this->_sParentManageClass;
		} elseif ( Geko_Wp_Admin_Menu::inTabGroup( $this->_sInstanceClass ) ) {
			$sHandle = Geko_Wp_Admin_Menu::getTabParent( $this->_sInstanceClass );
		}
		
		if ( !self::$aFixMenuInstances[ $sHandle ] ) {
						
			$sMatchToken = ' id="toplevel_page_' . $sHandle . '"';
			
			if ( FALSE !== strpos( $sContent, $sMatchToken ) ) {
				
				$sContent = preg_replace( '/<li(.+?)(' . $sMatchToken . ')>/', '<li\2\1>', $sContent );
				
				$aChunks = Geko_String::extractDelimitered(
					$sContent, array( '<li', $sMatchToken ), '</li>', '##%d##', 0
				);
				
				if ( is_array( $aChunks ) ) {
					
					$oDoc = phpQuery::newDocument( $aChunks[ 1 ][ 0 ] );
					
					foreach ( $oDoc[ 'a' ] as $oElem ) {
						$oPq = pq( $oElem );
						if ( !$oPq->html() ) $oPq->parent()->remove();
					}
					
					if ( self::$aMenuInstanceCurrent[ $sHandle ] ) {
						$oDoc[ 'li.wp-first-item, li.wp-first-item a' ]->addClass( 'current' );
					}
					
					$sContent = str_replace( '##0##', strval( $oDoc ), $aChunks[ 0 ] );
					
				}
				
			}
			
			self::$aFixMenuInstances[ $sHandle ] = TRUE;
		}
		
		return $sContent;
	}
	
	
	
	
	
	//// front-end display methods
	
	// hook method
	public function modifyListingParams( $aParams ) {
		return $aParams;
	}
	
	//
	public function listingPage() {
		
		// call hook method
		$aParams = $this->modifyListingParams( array(
			'paged' => $this->getPageNum(),
			'posts_per_page' => $this->_iEntitiesPerPage
		) );
		
		$oUrl = new Geko_Uri();
		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		$sThisUrl = strval( $oUrl );
		
		$sQueryClass = $this->_sQueryClass;
		$aEntities = new $sQueryClass( $aParams );
		
		$iTotalRows = $aEntities->getTotalRows();
		$sPaginateLinks = $this->getPaginationLinks( $iTotalRows );
		
		$sAction = 'add' . $this->_sType;
		$sNonceField = $this->_sInstanceClass . $sAction;
		
		?>
		<div class="wrap">
			
			<?php $this->outputHeading(); ?>
			
			<form id="<?php echo $this->_sNamespace; ?>-<?php echo $this->_sType; ?>-filter" method="get" action="">
				
				<?php if ( $this->_bHasKeywordSearch ): ?>
					<p class="search-box">
						<label class="screen-reader-text" for="post-search-input">Search <?php echo $this->_sSubjectPlural; ?>:</label>
						<input id="post-search-input" type="search" value="<?php echo htmlentities( $_GET[ 's' ] ); ?>" name="s" />
						<input id="search-submit" class="button" type="submit" value="Search <?php echo $this->_sSubjectPlural; ?>" name="" />
					</p>
				<?php endif; ?>
				
				<?php echo Geko_String::sw( '<input type="hidden" name="page" value="%s">', $_GET[ 'page' ] ); ?>
				
				<div class="tablenav">
					<?php $this->echoFilterSelects(); ?>
					<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
					<br class="clear"/>
				</div>
				
				<table class="widefat fixed" cellspacing="0">
				
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-title"><?php echo $this->_sListingTitle; ?></th>
							<?php $this->columnTitle(); ?>
						</tr>
					</thead>
					
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-title"><?php echo $this->_sListingTitle; ?></th>	
							<?php $this->columnTitle(); ?>
						</tr>
					</tfoot>
					
					<?php
					
					foreach ( $aEntities as $oEntity ):

						$oUrl
							->setVar( $this->_sEntityIdVarName, $oEntity->getId() )
							->unsetVar( 'action' )
						;
						$sEditLink = strval( $oUrl );
						
						$oUrl->setVar( 'action', $this->_sDelAction );
						$sDeleteLink = strval( $oUrl );
						
						if ( function_exists( 'wp_nonce_url' ) ) {
							$sDeleteLink = wp_nonce_url( $sDeleteLink,  $this->_sInstanceClass . $this->_sDelAction );
							$sDeleteLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
						}
						
						?><tbody>
							<tr id="<?php echo $this->_sType; ?>-<?php $oEntity->echoId(); ?>" class='alternate author-self status-publish iedit' valign="top">
								<th scope="row" class="check-column"><input type="checkbox" name="<?php echo $this->_sType; ?>[]" value="<?php $oEntity->echoId(); ?>" /></th>
								<td class="<?php echo $this->_sType; ?>-title column-title">
									<strong><a class="row-title" href="<?php echo $sEditLink; ?>" title="<?php echo htmlspecialchars( $oEntity->getTitle() ); ?>"><?php echo htmlspecialchars( $oEntity->getTitle() ); ?></a></strong><br />
									<div class="row-actions">
										<span class="edit"><a href="<?php echo $sEditLink; ?>">Edit</a></span>
										<!-- TO DO: implement delete restrictions -->
										<?php if ( TRUE ): ?>
											<span class="delete"> | <a class="delete:the-list:<?php echo $this->_sType; ?>-<?php $oEntity->echoId(); ?> submitdelete" href="<?php echo $sDeleteLink; ?>">Delete</a></span>
										<?php endif; ?>
									</div>
								</td>
								<?php $this->columnValue( $oEntity ); ?>
							</tr>
						</tbody><?php
					endforeach;
					
					?>
					
				</table>

				<div class="tablenav">
					<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
					<br class="clear"/>
				</div>
				
			</form>
			
			<form><p class="submit">
				<?php $this->outputAddButton(); ?>
			</p></form>
			
			<?php if ( $this->_bCanImport ): ?>
				<div class="dialog" id="import_<?php echo $this->_sType; ?>_form">
					<form id="dlgform" name="<?php echo $sAction; ?>" method="post" action="<?php echo $sThisUrl; ?>" class="validate" enctype="multipart/form-data">
						
						<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( $sNonceField ); ?>
						<input type="hidden" name="action" value="<?php echo $sAction; ?>">
						
						<fieldset class="ui-helper-reset">
							<div>
								<label for="import_<?php echo $this->_sType; ?>_file">Import <?php echo $this->_sSubject; ?></label>
								<input type="file" name="import_<?php echo $this->_sType; ?>_file" id="import_<?php echo $this->_sType; ?>_file" value="" />
							</div>
						</fieldset>
						
						<input type="hidden" name="import_<?php echo $this->_sType; ?>" value="1" />
						
					</form>
				</div>			
			<?php endif; ?>
			
		</div>
		
		<?php
	}
	
	//
	public function detailsPage( $oEntity = NULL ) {
		
		// default to "add" mode
		$iEntityId = 0;
		$sOp = 'add';
		$sSubmit = 'Add';
		
		// edit mode
		if ( $oEntity ) {
			$iEntityId = $oEntity->getId();
			$sOp = 'edit';
			$sSubmit = 'Update';
		}
		
		$sAction = $sOp . $this->_sType;
		$sNonceField = $this->_sInstanceClass . $sAction;
		
		
		
		$oUrl = new Geko_Uri();

		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		?>
		<div class="wrap">
			
			<?php $this->outputHeading(); ?>
			
			<form id="editform" name="<?php echo $sAction; ?>" method="post" action="<?php echo $oUrl; ?>" class="validate" enctype="multipart/form-data">
				
				<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( $sNonceField ); ?>
				
				<input type="hidden" name="action" value="<?php echo $sAction; ?>">
				<?php echo Geko_String::sw( '<input type="hidden" name="%s$1" value="%d$0">', $iEntityId, $this->_sEntityIdVarName ); ?>
				
				<?php
					
					$this->outputForm();
					
					$sAction = $this->_sActionPrefix . '_extra_fields';
					do_action( $sAction, $oEntity, 'extra' );
					if ( $this->_sSlug ) do_action( $sAction . '_' . $this->_sSlug, $oEntity, 'extra', $this->_sSlug );
				
				?>
				
				<?php if ( $this->_bCanDuplicate ): ?>
					<input type="hidden" name="duplicate_<?php echo $this->_sType; ?>" />
					<input type="hidden" name="duplicate_<?php echo $this->_sType; ?>_title" />				
				<?php endif; ?>
				<?php if ( $this->_bCanExport ): ?>
					<input type="hidden" name="export_<?php echo $this->_sType; ?>" />
				<?php endif; ?>
				
				<?php $this->outputFormSubmit( $sSubmit ); ?>
				
			</form>
			
			<?php
				$sAction = $this->_sActionPrefix . '_extra_forms';
				do_action( $sAction, $oEntity );
			?>
			
		</div>
		<?php
	}
	
	// to be implemented by subclass
	protected function columnTitle() { }
	protected function columnValue( $oEntity ) { }
	
	//
	public function displayPage() {
		
		if (
			( $this->_oCurrentEntity ) && 
			( $this->_oCurrentEntity->isValid() )
		) {
			$this->detailsPage( $this->_oCurrentEntity );			
		} else {
			$this->listingPage();			
		}
	}

	//
	public function outputPageSubHeading() {
		
		if ( $this->_oCurrentParentEntity && $this->_oCurrentParentEntity->isValid() ):
			?>
			<h3><?php $this->_oCurrentParentEntity->echoTitle(); ?></h3>
			<?php
		endif;
		
	}
	
	//// TO DO: Override Check!!!!!!
	public function outputAddButton() {
		?>
		
		<?php if ( $this->_sParentEntityClass || Geko_Wp_Admin_Menu::inTabGroup( $this->_sInstanceClass ) ):
			
			$oUrl = new Geko_Uri();
			$oUrl->setVar( 'page', $oUrl->getVar( 'page' ) . '_Create' );
			
			?>
			<input type="button" class="button-primary" value="Add <?php echo $this->_sSubject; ?>" onclick="window.location='<?php echo strval( $oUrl ); ?>'" />
		<?php endif; ?>
		<?php if ( $this->_bCanImport ): ?>
			<input type="button" class="button-primary" id="import_<?php echo $this->_sType; ?>_btn" value="Import <?php echo $this->_sSubject; ?>" />
		<?php endif; ?>
		
		<?php
	}
	
	//
	public function outputHeading() {
		
		?>
		<div id="<?php echo $this->_sIconId; ?>" class="icon32"><a name="heading_top"></a><br /></div>
		<?php
		
		if ( Geko_Wp_Admin_Menu::inTabGroup( $this->_sInstanceClass ) ):
			
			Geko_Wp_Admin_Menu::showNavTabs( $this->_sInstanceClass );
			
		else: ?>
			<h2><?php
				if ( $this->_oCurrentEntity ) {
					echo 'Edit ' . $this->_sSubject;
				} else {
					echo $this->isDisplayMode( 'add' ) ? 'Add ' . $this->_sSubject : $this->_sPageTitle;
				}
			?></h2>		
		<?php endif;
		
		$this->notificationMessages();
		Geko_Wp_Admin_Menu::showMenu( $this->getDetailsMenuHandle() );
		$this->outputListingFilterSection();
		$this->outputPageSubHeading();
		$this->outputStyles();
		
	}
	
	
	
	//// form submit area
	
	// TO DO: Override Check!!!!!!
	public function outputFormSubmit( $sSubmit ) {
		
		$sSubmitLabel = $sSubmit . ' ' . $this->_sSubject;
		
		$this->outputBeforeSubmitDefault();
		$this->outputBeforeSubmit();
		
		?>
		
		<p class="submit">
			
			<input type="submit" class="button-primary" name="submit" value="<?php echo $sSubmitLabel; ?>" />
			
			<?php if ( $this->isDisplayMode( 'edit' ) ): ?>
				
				<?php if ( $this->_bCanDuplicate ): ?>
					<input type="button" class="button-primary" id="duplicate_<?php echo $this->_sType; ?>_btn" value="Duplicate <?php echo $this->_sSubject; ?>" />
				<?php endif; ?>
				<?php if ( $this->_bCanExport ): ?>
					<input type="button" class="button-primary" id="export_<?php echo $this->_sType; ?>_btn" value="Export <?php echo $this->_sSubject; ?>" />		
				<?php endif; ?>
				<?php if ( $this->_bCanRestore ): ?>
					<input type="button" class="button-primary" id="restore_<?php echo $this->_sType; ?>_btn" value="Restore <?php echo $this->_sSubject; ?>" />		
				<?php endif; ?>
				
			<?php endif; ?>
			
			<span class="btn_spacer"></span>
			
			<?php $this->outputAppendSubmitDefault(); ?>
			<?php $this->outputAppendSubmit(); ?>
		</p>
		
		<?php
	}
	
	// to be implemented by sub-class
	
	public function outputBeforeSubmitDefault() {
		?>
		<!-- custom hidden fields -->
		<?php foreach ( $this->_aNormalizedCustomActions as $sKey => $aAction ):
			$sActMode = $aAction[ 'mode' ];
			if ( $this->isDisplayMode( 'edit' ) && ( 'edit' == $sActMode ) && ( $aHiddenFld = $aAction[ 'hidden_field' ] ) ):
				?><input type="hidden" name="<?php echo $aHiddenFld[ 'name' ]; ?>" value="<?php echo $aHiddenFld[ 'value' ]; ?>" /><?php
			endif;
		endforeach; ?>
		<?php
	}
	
	public function outputAppendSubmitDefault() {
		?>
		<!-- custom action buttons -->
		<?php foreach ( $this->_aNormalizedCustomActions as $sKey => $aAction ):
			$sActMode = $aAction[ 'mode' ];
			if ( $this->isDisplayMode( 'edit' ) && ( 'edit' == $sActMode ) && ( $aButton = $aAction[ 'button' ] ) ):
				?> &nbsp; <input type="button" class="button-primary" id="<?php echo $aButton[ 'id' ]; ?>" value="<?php echo $aButton[ 'btn_title' ]; ?>" /> &nbsp; <?php
			endif;
		endforeach; ?>		
		<?php
	}
		
	public function outputBeforeSubmit() { }
	public function outputAppendSubmit() { }
	
	
	
	
	
	//
	public function getFilterSelects() {
		return array();
	}
	
	//
	public function echoFilterSelects() {
		
		$aSelects = $this->getFilterSelects();
		
		if ( count( $aSelects ) > 0 ):
			?>
			<div class="alignleft actions">
				<?php foreach ( $aSelects as $sKey => $aSelect ):
					
					$aAtts = array( 'id' => $sKey, 'name' => $sKey );
					if ( is_array( $aSelect[ 'select' ] ) ) {
						if ( is_array( $aSelect[ 'atts' ] ) && ( count( $aSelect ) == 2 ) ) {
							$aAtts = array_merge( $aAtts, $aSelect[ 'atts' ] );				
						}
						if ( !$aSelect[ 'atts' ] && ( count( $aSelect ) == 1 ) ) {
							$aSelect = $aSelect[ 'select' ];
						}
					}
					
					$sName = $aAtts[ 'name' ];
					
					?>
					<select <?php echo Geko_Html::formatAsAtts( $aAtts ); ?> >
						<?php foreach ( $aSelect as $sValue => $sLabel ):
							
							$sSelected = ( $sValue == $_GET[ $sName ] ) ? 'selected="selected"' : '' ;
							
							?>
							<option value="<?php echo $sValue; ?>" <?php echo $sSelected; ?> ><?php echo $sLabel; ?></option>
						<?php endforeach; ?>
					</select>
				<?php endforeach; ?>
				<input id="post-query-submit" class="button" type="submit" value="Filter" name="" />
			</div>
			<?php
		endif;
	}
	
	//
	public function getPaginationLinks( $iTotalRows ) {
		
		$sPaginateLinks = paginate_links( array(
			'base' => add_query_arg( 'pagenum', '%#%' ),
			'format' => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total' => ceil( $iTotalRows / $this->_iEntitiesPerPage ),
			'current' => $this->getPageNum()
		) );
		
		if ( $sPaginateLinks && $this->_bShowTotalItems ) {
			$sPaginateLinks = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
				number_format_i18n( ( $this->getPageNum() - 1 ) * $this->_iEntitiesPerPage + 1 ),
				number_format_i18n( min( $this->getPageNum() * $this->_iEntitiesPerPage, $iTotalRows ) ),
				number_format_i18n( $iTotalRows ),
				$sPaginateLinks
			);
		}
		
		return $sPaginateLinks;
	}
	
	// hook method
	public function customFieldsPre() {
		$oEntity = $this->_oCurrentEntity;
		$sAction = $this->_sActionPrefix . '_main_fields';
		do_action( $sAction, $oEntity, 'pre' );
		if ( $this->_sSlug ) do_action( $sAction . '_' . $this->_sSlug, $oEntity, 'pre', $this->_sSlug );
	}
	
	// hook method
	public function customFieldsMain() {
		$oEntity = $this->_oCurrentEntity;
		$sAction = $this->_sActionPrefix . '_main_fields';
		do_action( $sAction, $oEntity, 'main' );
		if ( $this->_sSlug ) do_action( $sAction . '_' . $this->_sSlug, $oEntity, 'main', $this->_sSlug );
	}
	
	//
	public function parentEntityField() {
		if ( $this->_iCurrentParentEntityId ):
			?><input type="hidden" id="parent_id" name="parent_id" value="<?php echo $this->_iCurrentParentEntityId; ?>" /><?php
		endif;	
	}
	
	//
	public function formDateFields() {
		$oEntity = $this->_oCurrentEntity;
		if ( $oEntity ): ?>
			<tr>
				<th>Date Created</th>
				<td><?php $oEntity->echoDateTimeCreated(); ?></td>
			</tr>
			<tr>
				<th>Date Modified</th>
				<td><?php $oEntity->echoDateTimeModified(); ?></td>
			</tr>
		<?php endif;
	}
	
	//// TO DO: Override Check!!!!!!
	public function extraForms( $oEntity ) {
		
		// default to "add" mode
		$iEntityId = 0;
		$sOp = 'add';
		$sSubmit = 'Add';
		
		// edit mode
		if ( $oEntity ) {
			$iEntityId = $oEntity->getId();
			$sOp = 'edit';
			$sSubmit = 'Update';
		}
		
		$sAction = $sOp . $this->_sType;
		$sNonceField = $this->_sInstanceClass . $sAction;
		
		
		
		$oUrl = new Geko_Uri();

		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		$sDupTitleFld = 'duplicate_' . $this->_sType . '_title';
		
		?>
		
		<?php if ( $this->_bCanDuplicate ): ?>
			<div class="dialog" id="duplicate_<?php echo $this->_sType; ?>">
				<form>
					<fieldset class="ui-helper-reset">
						<div>
							<label for="<?php echo $sDupTitleFld; ?>">Title</label>
							<input type="text" name="<?php echo $sDupTitleFld; ?>" id="<?php echo $sDupTitleFld; ?>" value="" />
						</div>
					</fieldset>
				</form>
			</div>		
		<?php endif; ?>
		
		<?php if ( $this->_bCanRestore ): ?>
			<div class="dialog" id="restore_<?php echo $this->_sType; ?>_form">
				<form id="dlgform" name="<?php echo $sAction; ?>" method="post" action="<?php echo $oUrl; ?>" class="validate" enctype="multipart/form-data">
					
					<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( $sNonceField ); ?>
					<input type="hidden" name="action" value="<?php echo $sAction; ?>">
					<?php echo Geko_String::sw( '<input type="hidden" name="%s$1" value="%d$0">', $iEntityId, $this->_sEntityIdVarName ); ?>
					
					<fieldset class="ui-helper-reset">
						<div>
							<label for="restore_<?php echo $this->_sType; ?>_file">Restore <?php echo $this->_sSubject; ?></label>
							<input type="file" name="restore_<?php echo $this->_sType; ?>_file" id="restore_<?php echo $this->_sType; ?>_file" value="" />
						</div>
					</fieldset>
					
					<input type="hidden" name="restore_<?php echo $this->_sType; ?>" value="1" />
					
				</form>
			</div>
		<?php endif; ?>
		
		<!-- custom action dialogs -->
		<?php foreach ( $this->_aNormalizedCustomActions as $sKey => $aAction ):
			$sActMode = $aAction[ 'mode' ];
			if ( ( 'edit' == $sActMode ) && ( $aDialog = $aAction[ 'dialog' ] ) ):
				?>
				<div class="dialog" id="<?php echo $aDialog[ 'id' ]; ?>">
					<div class="inner"></div>
				</div>		
				<?php
			endif;
		endforeach; ?>
		
		<?php	
	}

	
	
	
	
	// to be implemented by sub-class
	public function formFields( $oEntity = NULL, $sSection = NULL, $oPlugin = NULL ) { }
	public function subMainFieldTitles( $oPlugin = NULL ) { }
	public function subMainFieldColumns( $oPlugin = NULL ) { }
	public function outputListingFilterSection() { }
	public function outputStyles() { }
	
	
	
	//// main crud methods
	
	//
	public function doActions() {
		
		@session_start();
		
		$sAction = $_REQUEST[ 'action' ];
		$sActionTarget = Geko_String::coalesce( $this->_sActionTarget, $this->_sInstanceClass );
		
		if ( class_exists( $sActionTarget ) ) {
			$oMng = Geko_Singleton_Abstract::getInstance( $sActionTarget );
			$sEntityIdVarName = $oMng->getEntityIdVarName();
		} else {
			$sEntityIdVarName = $this->_sEntityIdVarName;
		}
		
		if (
			( $this->_sCurrentPage == $sActionTarget ) && 
			( $this->_sAddAction == $sAction || $this->_sEditAction == $sAction || $this->_sDelAction == $sAction )
		) {
			
			global $wp_roles, $wpdb;
			
			$aParams = array();
			
			$bContinue = TRUE;
			$aParams[ 'action' ] = $sAction;
			
			if ( !$this->_bHasManagementCapability ) {
				$bContinue = FALSE;
				$this->triggerErrorMsg( 'm204' );											// not allowed
			}
			
			$aParams[ 'referer' ] = $_REQUEST[ '_wp_http_referer' ];
			$aParams[ 'entity_id' ] = intval( $_REQUEST[ $sEntityIdVarName ] );
			
			if ( $bContinue ) {
				
				if (
					( $this->_sAddAction == $sAction ) &&
					( check_admin_referer( $sActionTarget . $this->_sAddAction ) )
				) {
					
					if ( !$mRes = $this->doCustomActions( 'add', $aParams ) ) {
						if ( $this->_bCanImport && $_POST[ 'import_' . $this->_sType ] ) {
							$aParams = $this->doImportAction( $aParams );
						} else {
							$aParams = $this->doAddAction( $aParams );
						}
					} else {
						$aParams = $mRes;
					}
					
				} elseif (
					( $this->_sEditAction == $sAction ) &&
					( check_admin_referer( $sActionTarget . $this->_sEditAction ) ) && 
					( $aParams[ 'entity_id' ] )
				) {
					
					if ( !$mRes = $this->doCustomActions( 'edit', $aParams ) ) {
						if ( $this->_bCanDuplicate && $_POST[ 'duplicate_' . $this->_sType ] ) {
							$aParams = $this->doDuplicateAction( $aParams );
						} elseif ( $this->_bCanExport && $_POST[ 'export_' . $this->_sType ] ) {
							$aParams = $this->doExportAction( $aParams );
						} elseif ( $this->_bCanRestore && $_POST[ 'restore_' . $this->_sType ] ) {
							$aParams = $this->doRestoreAction( $aParams );
						} else {
							$aParams = $this->doEditAction( $aParams );
						}
					} else {
						$aParams = $mRes;
					}
					
				} elseif (
					( $this->_sDelAction == $sAction ) &&
					( check_admin_referer( $sActionTarget . $this->_sDelAction ) ) && 
					( $aParams[ 'entity_id' ] )
				) {
					
					if ( !$mRes = $this->doCustomActions( 'delete', $aParams ) ) {
						$aParams = $this->doDelAction( $aParams );
					} else {
						$aParams = $mRes;
					}
					
				}
			}
			
			if ( $aParams[ 'referer' ] ) {
				header( 'Location: ' . $aParams[ 'referer' ] );
				die();
			}
		}
		
	}
	
	
	//
	public function doCustomActions( $sMode, $aParams ) {
		
		foreach ( $this->_aNormalizedCustomActions as $sKey => $aAction ) {
			
			$sActMode = $aAction[ 'mode' ];
			$sReqKey = $aAction[ 'req_key' ];
			$sMethod = $aAction[ 'method' ];
			
			if ( ( $sMode == $sActMode ) && ( $_REQUEST[ $sReqKey ] ) && ( method_exists( $this, $sMethod ) ) ) {
				return $this->$sMethod( $aParams );
			}
			
		}
		
		return FALSE;
	}
	
	
	// insert methods main

	//
	public function getInsertData( $aParams, $aValues = NULL ) {
		$aValues = ( NULL !== $aValues ) ? $aValues : $this->getValidPostVals() ;
		return $this->getFormattedPostData(
			$aParams,
			$this->modifyInsertPostVals( $aValues )
		);
	}
	
	// hook
	public function modifyInsertPostVals( $aPostValues ) {
		return $aPostValues;
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		return TRUE;
	}
	
	//
	public function doAddAction( $aParams, $aValues = NULL ) {
		
		global $wpdb;
		
		$aInsertData = $this->getInsertData( $aParams, $aValues );
		
		if ( $this->getInsertContinue( $aInsertData, $aParams ) ) {
			
			// update the database first
			$wpdb->insert(
				$this->_sPrimaryTable,
				$aInsertData[ 0 ],
				$aInsertData[ 1 ]
			);
			
			$aParams[ 'entity_id' ] = $wpdb->insert_id;
			
			// rewrite the referer url
			$oUrl = new Geko_Uri( $aParams[ 'referer' ] );
			$oUrl
				->setVar( $this->_sEntityIdVarName, $aParams[ 'entity_id' ] )
				->setVar( 'page', $this->_sInstanceClass )
			;
			
			$aParams[ 'referer' ] = strval( $oUrl );
			
			$sEntityClass = $this->_sEntityClass;			
			$oInsertedEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			// hook method
			$this->postAddAction( $aParams, $oInsertedEntity );
			
			$sAction = $this->_sActionPrefix . '_add';
			do_action( $sAction, $oInsertedEntity, $aParams );
			if ( $this->_sSlug ) do_action( $sAction . '_' . $this->_sSlug, $oInsertedEntity, $aParams, $this->_sSlug );				
			
			$this->triggerNotifyMsg( 'm101' );										// success!!!
		}
		
		return $aParams;
	}
	
	// hook method
	public function postAddAction( $aParams, $oInsertedEntity ) {
		$this->updateNestedEntities( $oInsertedEntity, $aParams );
	}
	
	
	
	
	// import methods main
	
	//
	public function doImportAction( $aParams ) {
		
		$bContinue = TRUE;
		
		$aFile = $_FILES[ 'import_' . $this->_sType . '_file' ];
		
		if ( $bContinue && !$aFile ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm208', '.txt' );
		}
		
		if ( $bContinue && $aFile[ 'error' ] ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm209' );
		}
		
		if ( $bContinue && ( 'text/plain' != $aFile[ 'type' ] ) ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm210', '.txt' );
		}
		
		if ( $bContinue ) {
			
			if ( $aRes = $this->parseDataFromImportFile( $aFile ) ) {
				
				$iCount = count( $aRes );
				$iTrack = 0;
				
				foreach ( $aRes as $aData ) {
					if ( $this->importSerialized( $aData ) ) $iTrack++;
				}
				
				if ( $iCount == $iTrack ) {
					$this->triggerNotifyMsg( 'm105', $iTrack, $iCount );
				} else {
					$this->triggerErrorMsg( 'm213', $iTrack, $iCount );
				}
				
			} else {
				$this->triggerErrorMsg( 'm212' );
			}
		}
		
		return $aParams;
	}	
	
	
	
	
	
	
	// update methods main
	
	//
	public function getUpdateData( $aParams, $oEntity ) {
		$aData = $this->getFormattedPostData(
			$aParams,
			$this->modifyUpdatePostVals( $this->getValidPostVals(), $oEntity )
		);
		$oPkf = $this->getPrimaryTablePrimaryKeyField();
		return array(
			$aData[ 0 ],
			array( $oPkf->getFieldName() => $aParams[ 'entity_id' ] ),
			$aData[ 1 ],
			array( $oPkf->getFormat() )
		);
	}
	
	// hook
	public function modifyUpdatePostVals( $aPostValues, $oEntity ) {
		return $aPostValues;
	}

	//
	public function getUpdateContinue( $aUpdateData, $aParams, $oEntity ) {
		return $this->getInsertContinue( array( $aUpdateData[ 0 ], $aUpdateData[ 1 ] ), $aParams );
	}
	
	//
	public function doEditAction( $aParams ) {
		
		global $wpdb;
		
		$bContinue = TRUE;
		
		// check the entity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad entity id given
		}
		
		$aUpdateData = $this->getUpdateData( $aParams, $oEntity );
		
		if ( $bContinue && $this->getUpdateContinue( $aUpdateData, $aParams, $oEntity ) ) {
			
			// update the database first
			$wpdb->update(
				$this->_sPrimaryTable,
				$aUpdateData[ 0 ],
				$aUpdateData[ 1 ],
				$aUpdateData[ 2 ],
				$aUpdateData[ 3 ]
			);
			
			$sEntityClass = $this->_sEntityClass;			
			$oUpdatedEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
			
			// hook method
			$this->postEditAction( $aParams, $oEntity, $oUpdatedEntity );
			
			$sAction = $this->_sActionPrefix . '_edit';
			do_action( $sAction, $oEntity, $oUpdatedEntity, $aParams );
			if ( $this->_sSlug ) do_action( $sAction . '_' . $this->_sSlug, $oEntity, $oUpdatedEntity, $aParams, $this->_sSlug );
			
			$this->triggerNotifyMsg( 'm102' );										// success!!!
			
			// die();		///////////////////// !!!!!!!!!!!!!!! UNLOCK
		}
		
		return $aParams;
	}
	
	// hook method
	public function postEditAction( $aParams, $oEntity, $oUpdatedEntity ) {
		$this->updateNestedEntities( $oEntity, $aParams );
	}
	
	
	
	// duplicate methods main
	
	//
	public function doDuplicateAction( $aParams ) {
		
		$bContinue = TRUE;
		
		// check the entity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		$sDuplicateTitle = $_POST[ 'duplicate_' . $this->_sType . '_title' ];
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm205' );
		}

		if ( $bContinue && !$sDuplicateTitle ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm206' );
		}
		
		if ( $bContinue ) {
			
			$aDupParams = array(
				'entity_id' => $oEntity->getId(),
				'entity' => $oEntity,
				'title' => $sDuplicateTitle
			);
			
			if ( !$aRes = $this->duplicateSerialized( $aDupParams ) ) {
				
				$this->triggerErrorMsg( 'm207' );
				
			} else {
				
				// rewrite the referer url
				$oUrl = new Geko_Uri( $aParams[ 'referer' ] );
				$oUrl
					->setVar( $this->_sEntityIdVarName, $aRes[ 'dup_entity_id' ] )
					->setVar( 'page', $this->_sInstanceClass )
				;
				
				$aParams[ 'referer' ] = strval( $oUrl );
				
				$this->triggerNotifyMsg( 'm104' );
			}
		}
		
		return $aParams;
	}
	
	
	
	
	
	// export methods main
	
	//
	public function doExportAction( $aParams ) {
		
		$bContinue = TRUE;
		
		// check the entity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm205' );
		}
		
		if ( $bContinue ) {
			
			$aRes = array();
			
			// serialize one form
			$aExportParams = array(
				'entity_id' => $oEntity->getId(),
				'entity' => $oEntity
			);
			
			$aRes[] = $this->exportSerialize( $aExportParams ); 
			
			// TO DO: export multiple forms in one file
			/* pseudo-code
			foreach ( ... ) {
				...
				$aRes[] = $this->exportSerialize( ... );		
			}
			*/
			
			$this->generateExportFile( $aRes, $oEntity );
		}
	}
	
	
	
	
	// restore main methods
	
	//
	public function doRestoreAction( $aParams ) {
		
		$bContinue = TRUE;
				
		// check the entity id given
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		$aFile = $_FILES[ 'restore_' . $this->_sType . '_file' ];
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm205' );
		}
		
		if ( $bContinue && !$aFile ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm208', '.txt' );
		}
		
		if ( $bContinue && $aFile[ 'error' ] ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm209' );
		}
		
		if ( $bContinue && ( 'text/plain' != $aFile[ 'type' ] ) ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm210', '.txt' );
		}
		
		
		if ( $bContinue ) {
			
			if ( $aRes = $this->parseDataFromImportFile( $aFile ) ) {
				
				$aRestoreParams = array(
					'entity_id' => $oEntity->getId(),
					'entity' => $oEntity,
					'data' => $aRes[ 0 ]
				);
				
				if ( $this->restoreSerialized( $aRestoreParams ) ) {
					$this->triggerNotifyMsg( 'm106' );
				} else {
					$this->triggerErrorMsg( 'm214' );			
				}
				
			} else {
				$this->triggerErrorMsg( 'm212' );
			}
		}
		
		return $aParams;		
	}
	
	
	
	
	
	
	// delete methods main

	//
	public function getDeleteContinue( $aParams, $oEntity ) {
		return TRUE;
	}
	
	//
	public function doDelAction( $aParams ) {
		
		global $wpdb;

		$bContinue = TRUE;
		
		// check the entity id given
		$sEntityClass = $this->_sEntityClass;
		$oEntity = new $sEntityClass( $aParams[ 'entity_id' ] );
		
		if ( !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );										// bad entity id given
		}
		
		if ( $bContinue && $this->getDeleteContinue( $aParams, $oEntity ) ) {
			
			$oPk = $this->getPrimaryTablePrimaryKeyField();
			
			$oSqlDelete = new Geko_Sql_Delete();
			$oSqlDelete
				->from( $this->_sPrimaryTable )
				->where( $oPk->getFieldName() . ' = ?', $aParams[ 'entity_id' ] )
			;
			
			$wpdb->query( $oSqlDelete );
			
			// hook method
			$this->postDeleteAction( $aParams, $oEntity );
			
			$sAction = $this->_sActionPrefix . '_delete';
			do_action( $sAction, $oEntity, $aParams );
			if ( $this->_sSlug ) do_action( $sAction . '_' . $this->_sSlug, $oEntity, $aParams, $this->_sSlug );
			
			$this->triggerNotifyMsg( 'm103' );										// success!!!
			
		}
		
		return $aParams;
	}
	
	// hook method
	public function postDeleteAction( $aParams, $oEntity ) {
		
		global $wpdb;

		if ( $this->_sNestedType ) {
			
			$sMainEntPkfName = 'parent_id';
			$oSubEntTable = $this->getPrimaryTable();
			
			if ( $oSubEntTable->hasField( $sMainEntPkfName ) ) {
			
				$oSqlDelete = new Geko_Sql_Delete();
				$oSqlDelete
					->from( $this->_sPrimaryTable )
					->where( $sMainEntPkfName . ' = ?', $oMainEnt->getId() )
				;
				
				$wpdb->query( strval( $oSqlDelete ) );
			}
		}
		
	}
	
	
	
	//// sub crud methods
	
	// add methods sub
	
	//
	public function doSubAddAction( $oMainEnt, $aParams, $oPlugin = NULL ) {
		$this->updateSubEntities( $oMainEnt, $aParams );
		$sAction = $this->_sActionPrefix . '_subadd';
		apply_filters( $sAction, $oMainEnt, $aParams );
	}
	
	//
	public function modifySubInsertData( $aValues, $aParams ) {
		return $aValues;
	}
	
	// edit methods sub
	
	//
	public function doSubEditAction( $oMainEnt, $oUpdMainEnt, $aParams, $oPlugin = NULL ) {
		$this->updateSubEntities( $oUpdMainEnt, $aParams );	
		$sAction = $this->_sActionPrefix . '_subedit';
		apply_filters( $sAction, $oMainEnt, $oUpdMainEnt, $aParams );
	}
	
	//
	public function modifySubUpdateData( $aValues, $aParams, $oEntity ) {
		return $aValues;
	}
	
	// delete methods sub
	
	//
	public function doSubDelAction( $oMainEnt, $aParams, $oPlugin = NULL ) {
		
		global $wpdb;

		if ( $oMainMng =  $this->_oSubOptionParent ) {
			
			$oMainEntTable = $oMainMng->getPrimaryTable();
			$oMainEntPkf = $oMainMng->getPrimaryTablePrimaryKeyField();
			$sMainEntPkfName = $oMainEntPkf->getFieldName();
			
			$oSubEntTable = $this->getPrimaryTable();
			
			if ( $oSubEntTable->hasField( $sMainEntPkfName ) ) {
			
				$oSqlDelete = new Geko_Sql_Delete();
				$oSqlDelete
					->from( $this->_sPrimaryTable )
					->where( $sMainEntPkfName . ' = ?', $oMainEnt->getId() )
				;
				
				$wpdb->query( strval( $oSqlDelete ) );
			}
		}
		
		$sAction = $this->_sActionPrefix . '_subdelete';
		apply_filters( $sAction, $oMainEnt, $aParams );
		
	}
	
	
	
	// related entities on a different table
	public function updateSubEntities( $oMainEnt, $aParams ) {
		
		if ( $oMainMng = $this->_oSubOptionParent ) {
			
			$aPostData = NULL;
			
			if ( $mPostData = $_POST[ $this->_sType ] ) {
				// assuming json
				if ( is_string( $mPostData ) ) {
					$aPostData = Zend_Json::decode( stripslashes( $mPostData ) );
				} elseif ( is_array( $mPostData ) ) {
					$aPostData = $mPostData;
				}
			}
			
			if ( !is_array( $aPostData ) ) $aPostData = array();
			
			$oMainEntTable = $oMainMng->getPrimaryTable();
			$oMainEntPkf = $oMainMng->getPrimaryTablePrimaryKeyField();
			$sMainEntPkfName = $oMainEntPkf->getFieldName();
			
			$iMainEntId = $oMainEnt->getId();
			$aParams[ 'main_entity_pk_field' ] = $sMainEntPkfName;
			$aParams[ 'main_entity_format' ] = $oMainEntPkf->getFormat();
			$aParams[ 'main_entity_id' ] = $iMainEntId;
			
			$aQueryParams = array(
				$sMainEntPkfName => $iMainEntId,
				'showposts' => -1,
				'posts_per_page' => -1
			);
						
			$this->updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
			
		}
	}
	
	// nested entities on the same table (via parent_id)
	public function updateNestedEntities( $oMainEnt, $aParams ) {

		if ( $this->_sNestedType ) {
			
			$aPostData = $_POST[ $this->_sNestedType ];
			if ( !is_array( $aPostData ) ) $aPostData = array();
			
			// TO DO: hard-coded right now
			$iMainEntId = $oMainEnt->getId();
			$aParams[ 'main_entity_pk_field' ] = 'parent_id';
			$aParams[ 'main_entity_format' ] = '%d';
			$aParams[ 'main_entity_id' ] = $iMainEntId;
			
			$aQueryParams = array(
				'parent_id' => $iMainEntId,
				'showposts' => -1,
				'posts_per_page' => -1
			);
			
			$this->updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
			
		}
	
	}
	
	
	// helpers
	
	// iterate through $_POST and find values with default prefix
	public function getValidPostVals() {
		$aValues = array();
		$sPrefix = $this->_sType . '_';
		foreach ( $_POST as $sKey => $mValue ) {
			if ( 0 === strpos( $sKey, $sPrefix ) ) {
				$sShortKey = substr_replace( $sKey, '', 0, strlen( $sPrefix ) );
				$aValues[ $sShortKey ] = $mValue;
			}
		}
		return $aValues;
	}
	
	//
	public function getFormattedPostData( $aParams, $aPostVals ) {
		
		if ( NULL === $aPostVals ) {
			// default behavior
			$sPrefix = $this->_sType . '_';
			$aPostVals = $_POST;
		} else {
			$sPrefix = '';		
		}
		
		$aValues = array();
		$aFormat = array();
		
		$aFields = $this->getPrimaryTableFields();
		
		foreach ( $aFields as $oField ) {
			$sFieldName = $oField->getFieldName();
			$sKey = $sPrefix . $sFieldName;
			if ( isset( $aPostVals[ $sKey ] ) ) {
				$aValues[ $sFieldName ] = $oField->getFormattedValue( $aPostVals[ $sKey ] );
				$aFormat[] = $oField->getFormat();
			}
		}
		
		return array( $aValues, $aFormat );
	}
	
	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		global $wpdb;
		
		if ( $sQueryClass = $this->_sQueryClass ) {
			
			$aDataFmt = array();
			
			$sMainEntPkfName = $aParams[ 'main_entity_pk_field' ];
			$sMainEntPkfFormat = $aParams[ 'main_entity_format' ];
			$mMainEntId = $aParams[ 'main_entity_id' ];
			$iMainEntId = ( is_scalar( $mMainEntId ) ) ? intval( $mMainEntId ) : 0 ;
			
			// do checks
			
			$aKeyFields = $this->getPrimaryTableKeyFields();
			
			if ( !$this->updateRelatedContinue( $aKeyFields ) ) return FALSE;
			
			$aSubs = new $sQueryClass( $aQueryParams, FALSE );
			
			foreach ( $aSubs as $oSubItem ) {
				$aDataFmt[ $oSubItem->getId() ] = $oSubItem;
			}
			
			// Track Ids
			if ( !is_array( $wpdb->aSubItemIds ) ) $wpdb->aSubItemIds = array();
			$wpdb->aSubItemIds[ $this->_sInstanceClass ] = array_keys( $aDataFmt );
			
			// update
			foreach ( $aDataFmt as $mId => $oSubItem ) {
				if ( $aPostUpd = $aPostData[ $mId ] ) {
					$aPostUpd = $this->modifySubUpdateData( $aPostUpd, $aParams, $oSubItem );
					$aUpdateVals = $this->getFormattedPostData( $aParams, $aPostUpd );
					$aUpdateWhere = $this->updateRelatedWhere( $mId, $aKeyFields );
					$wpdb->update(
						$this->_sPrimaryTable,
						$aUpdateVals[ 0 ],
						$aUpdateWhere[ 0 ],
						$aUpdateVals[ 1 ],
						$aUpdateWhere[ 1 ]
					);
					unset( $aDataFmt[ $mId ] );
					unset( $aPostData[ $mId ] );
				}
			}
			
			// insert
			$aInsIds = array();
			foreach ( $aPostData as $mId => $aPostIns ) {
				
				$aPostIns = $this->modifySubInsertData( $aPostIns, $aParams );
				$aInsertVals = $this->getFormattedPostData( $aParams, $aPostIns );
				
				if ( $iMainEntId ) {
					$aInsertVals[ 0 ][ $sMainEntPkfName ] = $iMainEntId;
					$aInsertVals[ 1 ][] = $sMainEntPkfFormat;
				}
				
				$wpdb->insert(
					$this->_sPrimaryTable,
					$aInsertVals[ 0 ],
					$aInsertVals[ 1 ]
				);
				
				$aInsIds[ $mId ] = $this->updateRelatedInsertId( $aInsertVals );
			}
			
			// Track Ids
			if ( !is_array( $wpdb->aInsertIds ) ) $wpdb->aInsertIds = array();
			$wpdb->aInsertIds[ $this->_sInstanceClass ] = $aInsIds;
			
			// delete
			$aDelIds = array();
			foreach ( $aDataFmt as $mId => $oSubItem ) $aDelIds[] = $mId;
			
			if ( $mMainEntId && ( count( $aDelIds ) > 0 ) ) {
				
				$oSqlDelete = new Geko_Sql_Delete();
				$oSqlDelete
					->from( $this->_sPrimaryTable )
					->where( $sMainEntPkfName . ' * ($)', $mMainEntId )
				;
				
				$oSqlDelete = $this->updateRelatedDelete( $oSqlDelete, $aDelIds, $aKeyFields );
				
				$wpdb->query( strval( $oSqlDelete ) );
				
				// Track Ids
				if ( !is_array( $wpdb->aDeleteIds ) ) $wpdb->aDeleteIds = array();
				$wpdb->aDeleteIds[ $this->_sInstanceClass ] = $aDelIds;
				
			}
			
		}
		
	}
	
	// more hooks
	
	//
	public function updateRelatedContinue( $aKeyFields ) {
		return ( count( $aKeyFields ) > 0 ) ? TRUE : FALSE;
	}
	
	//
	public function updateRelatedWhere( $mId, $aKeyFields ) {
		
		// multi-key support
		$aIds = explode( ':', $mId );
		
		$aValues = array();
		$aFormat = array();
		
		$i = 0;
		foreach ( $aKeyFields as $oField ) {
			$aValues[ $oField->getName() ] = $aIds[ $i ];
			$aFormat[] = $oField->getFormat();
			$i++;
		}
		
		return array( $aValues, $aFormat );
		
	}
	
	//
	public function updateRelatedDelete( $oSqlDelete, $aDelIds, $aKeyFields ) {
		
		$aWhere = array();
		foreach ( $aDelIds as $mId ) {
			
			$aIds = explode( ':', $mId );
			
			$i = 0;
			$aAnd = array();
			foreach ( $aKeyFields as $oField ) {
				$aAnd[] = sprintf(
					'( %s = ' . $oField->getFormat() . ' )',
					$oField->getName(),
					$aIds[ $i ]
				);
				$i++;
			}
			
			$aWhere[] = '( ' . implode( ' AND ', $aAnd ) . ' )';
			
		}
		
		$oSqlDelete->where( '( ' . implode( ' OR ', $aWhere ) . ' )' );
		
		return $oSqlDelete;
	}
	
	//
	public function updateRelatedInsertId( $aInsertVals ) {
		
		global $wpdb;
		
		return $wpdb->insert_id;
	}
	
	
	
	
	// import/export/duplicate serialization methods
	
	// essentially useless stub ???
	public function exportSerialize( $aParams = array() ) {
		
		$iEntityId = $aParams[ 'entity_id' ];
		if ( $iEntityId && !$aParams[ 'entity' ] ) {
			$sEntityClass = $this->_sEntityClass;
			$aParams[ 'entity' ] = new $sEntityClass( $iEntityId );
		}
		
		$aSerialized = array();
		
		//// DO STUFF!!!
		
		return $aSerialized;
	}
	
	//
	public function importSerialized( $aSerialized ) {
		return FALSE;
	}
	
	//
	public function duplicateSerialized( $aParams ) {
		return $this->importSerialized( $this->exportSerialize( $aParams ) );
	}
	
	//
	public function restoreSerialized( $aParams ) {

		$aData = $aParams[ 'data' ];
		
		$iEntityId = $aParams[ 'entity_id' ];
		if ( $iEntityId ) {
			
			if ( !$aParams[ 'entity' ] ) {
				$sEntityClass = $this->_sEntityClass;
				$aParams[ 'entity' ] = new $sEntityClass( $iEntityId );
			}
			
			if ( $oEntity = $aParams[ 'entity' ] ) {
				
				$aData[ 'entity_id' ] = $iEntityId;
				
				if ( $oEntity->hasEntityProperty( 'slug' ) ) {
					$aData[ 'slug' ] = $oEntity->getSlug();
				}
			}
		}
		
		return $this->importSerialized( $aData );
	}
	
	//
	public function parseDataFromImportFile( $aFile ) {
		
		$sFile = ABSPATH . 'wp-content/tmp/' . $this->_sType . '_import.txt';
		
		if ( move_uploaded_file( $aFile[ 'tmp_name' ], $sFile ) ) {
		
			$sData = file_get_contents( $sFile );
			$sRegex = sprintf( '/-- START DATA: %s --(.+)-- END DATA: %s --/ms', $this->_sInstanceClass, $this->_sInstanceClass );
			
			unlink( $sFile );
			
			$aRegs = array();
			if ( preg_match( $sRegex, $sData, $aRegs ) ) {
				return Zend_Json::decode( trim( $aRegs[ 1 ] ) );
			}
		}
		
		return FALSE;
	}
	
	//
	public function generateExportFile( $aRes, $oEntity = NULL ) {
		
		$sData = Zend_Json::encode( $aRes );
		
		if ( $oEntity && $oEntity->hasEntityProperty( 'slug' ) ) {
			$sSlug = $oEntity->getSlug();
		} else {
			$sSlug = 'export';
		}
		
		$sFilename = $this->_sType . '_' . $sSlug . '_' . date( 'Y-m-d' ) . '.txt';
		
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $sFilename . '";' );
		
		$aHeaders = array(
			'Date Created' => date( 'l, F j, Y - h:i:s A' ),
			'Site' => get_bloginfo( 'name' ),
			'Site URL' => get_bloginfo( 'url' ),
			'Class' => $this->_sInstanceClass
		);
		
		// hook method
		$aHeaders = $this->modifyExportFileHeaders( $aHeaders );
		
		foreach ( $aHeaders as $sKey => $sValue ) {
			echo sprintf( "%s: %s\n", $sKey, $sValue );	
		}
		
		echo sprintf( "\n-- START DATA: %s --\n", $this->_sInstanceClass );
		echo $sData;
		echo sprintf( "\n-- END DATA: %s --\n", $this->_sInstanceClass );
		
		die();	
	}
	
	// hook method
	public function modifyExportFileHeaders( $aHeaders ) {
		return $aHeaders;
	}
	
	
	
}



