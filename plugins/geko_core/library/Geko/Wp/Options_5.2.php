<?php

// base class for options
class Geko_Wp_Options extends Geko_Wp_Initialize
{	
	protected $_aOptionKeys = array();
	protected $_bPrefixFormElems = TRUE;
	protected $_aParts = NULL;
	
	protected $_sBindToClass = '';						// in '/wp-admin/admin.php?page=', 'page' corresponds to a class
														// allows related classes to bind to main class by defining this property
	
	protected $_sPrefix = '';
	protected $_sPrefixSeparator = '-';
	
	protected $_sCurrentPage = '';
	protected $_sAddModeSuffix = '_Create';
	protected $_bHasDisplayMode = FALSE;
	protected $_sCurrentDisplayMode = '';				//  typically: list | add | edit
	protected $_sSubAction = 'admin';
	
	protected $_aSubOptions = array();
	protected $_sSubOptionParentClass = '';
	protected $_oSubOptionParent;
	
	protected $_sTabGroupTitle = '';
	protected $_aTabGroup = array();
	
	protected $_aTables = array();
	protected $_sPrimaryTable = '';
	
	protected static $sCurrentPage = '';
	
	
	//// init
	
	//
	public function init() {
		
		parent::init();
		
		//// sub options
		
		foreach ( $this->_aSubOptions as $mSubOption ) {
			if ( is_string( $mSubOption ) && class_exists( $mSubOption ) ) {
				Geko_Singleton_Abstract::getInstance( $mSubOption )
					->setSubOptionParentClass( $this->_sInstanceClass )
					->init()
				;
			} elseif ( is_object( $mSubOption ) ) {
				$mSubOption
					->setManage( $this )
					->init()
				;
			}
		}
		
		//// prep tab group
		
		if ( count( $this->_aTabGroup ) > 0 ) {
			foreach ( $this->_aTabGroup as $mSubOption ) {
				if (
					( is_string( $mSubOption ) ) &&
					( $this->_sInstanceClass != $mSubOption ) &&
					( class_exists( $mSubOption ) )
				) {
					Geko_Singleton_Abstract::getInstance( $mSubOption )->init();
				}
			}
		}
		
		return $this;
	}
	
	
	public function attachPage() { }					// typically calls functions that create new pages in the admin
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$this->_sCurrentDisplayMode = Geko_Wp_Admin_Hooks::getDisplayMode();
		
		$this->_sCurrentPage = Geko_String::coalesce( self::$sCurrentPage, $_REQUEST[ 'page' ] );
		
		if ( count( $this->_aTabGroup ) > 0 ) {
			array_unshift( $this->_aTabGroup, $this->_sInstanceClass );
		}
		
		//// call hooks
		
		if ( $sTargetClass = Geko_String::coalesce( $this->_sBindToClass, $this->_sSubOptionParentClass ) ) {
			
			// init
			$sInitHook = sprintf( 'admin_init_%s::%s', $this->_sSubAction, $sTargetClass );
			
			// head
			$sHeadHook = sprintf( 'admin_head_%s::%s', $this->_sSubAction, $sTargetClass );
			
			add_action( 'admin_menu', array( $this, 'attachSubPage' ) );
			
		} else {
			
			//// will trigger doAdmin*() actions for current page
			
			// init
	
			$sInitHook = sprintf( 'admin_init_%s::%s' , $this->_sSubAction, $this->_sInstanceClass );
			
			add_action( sprintf( 'admin_init_%s', $this->_sSubAction ), array( $this, 'doAdminInit' ) );
						
			// head
			
			$sHeadHook = sprintf( 'admin_head_%s::%s', $this->_sSubAction, $this->_sInstanceClass );
			
			add_action( sprintf( 'admin_head_%s', $this->_sSubAction ), array( $this, 'doAdminHead' ) );
						
			// menu
			
			add_action( 'admin_menu', array( $this, 'attachPage' ) );
		}
		
		
		//// add actions
		
		// init
		add_action( $sInitHook, array( $this, 'install' ) );
		add_action( sprintf( '%s_Create', $sInitHook ), array( $this, 'install' ) );
				
		// head
		add_action( $sHeadHook, array( $this, 'addAdminHead' ) );
		add_action( sprintf( '%s_Create', $sHeadHook ), array( $this, 'addAdminHead' ) );
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
		
		return $this;
	}
	
	
	//
	public function attachSubPage() {
		
		if ( $this->_sSubMenuPage ) {
			add_submenu_page( $this->_sSubMenuPage, $this->_sPageTitle, $this->_sMenuTitle, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
		}
	}
	
	
	//
	public function addAdminHead( $oPlugin = NULL ) {
		
		parent::addAdminHead( $oPlugin );
		
		Geko_Once::run( sprintf( '%s::js', __METHOD__ ), array( $this, 'adminHeadJs0' ) );
		
	}
	
	//
	public function doAdminHook( $sType ) {
		if ( $this->isCurrentPage() ) {
			$sClass = Geko_String::coalesce( $this->_sCurrentPage, $this->_sInstanceClass );
			$sAction = sprintf( 'admin_%s_%s::%s', $sType, $this->_sSubAction, $sClass );
			do_action( $sAction );
		}
	}
	
	//
	public function doAdminInit() {
		$this->doAdminHook( 'init' );
	}
	
	//
	public function doAdminHead() {
		$this->doAdminHook( 'head' );
	}

	
	
	//// accessors
	
	//
	public function setPrefixFormElems( $bPrefixFormElems ) {
		$this->_bPrefixFormElems = $bPrefixFormElems;
		return $this;
	}
	
	// only populated after calling inject()
	public function getPrefixWithSep( $oPlugin = NULL ) {
		return sprintf( '%s%s', $this->getPrefix( $oPlugin ), $this->getPrefixSeparator( $oPlugin ) );
	}
	
	//
	public function getOptionKeys() {
		return $this->_aOptionKeys;
	}
	
	// Depracated: use getOptionKeys() instead
	public function getOptionList() {
		return $this->getOptionKeys();
	}
	
	
	// return a prefix
	public function getPrefix( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getPrefix();
		}
		return $this->_sPrefix;
	}
	
	
	// return a string that separates the prefix and variable name
	public function getPrefixSeparator( $oPlugin = NULL ) {
		return $this->_sPrefixSeparator;
	}
	
	
	// apply prefix with separator to field 
	public function applyPrefix( $sFieldName, $oPlugin = NULL ) {
		return sprintf( '%s%s', $this->getPrefixWithSep( $oPlugin ), $sFieldName );
	}
	
	
	//
	public function getAdminUrl() {
		return sprintf( '%s?page=%s', Geko_Uri::getUrl( 'wp_admin' ), $this->_sInstanceClass );
	}

	
	
	
	
	
	// return an associative array that will be used to populate (inject) the blank form
	public function getStoredOptions( $oPlugin = NULL ) {
		return array();
	}
	
	//
	public function getPrefixForDoc( $oPlugin = NULL ) {
		return ( $this->_bPrefixFormElems ) ? $this->getPrefixWithSep( $oPlugin ) : '';
	}
	
	//
	public function getBindToInstance() {
		
		if ( $this->_sBindToClass ) {
			return Geko_Singleton_Abstract::getInstance( $this->_sBindToClass );
		}
		
		return NULL;
	}
	
	//
	public function getSubOptionParentInstance() {
		
		if ( $this->_sSubOptionParentClass ) {
			return Geko_Singleton_Abstract::getInstance( $this->_sSubOptionParentClass );
		}
		
		return NULL;
	}
	
	//
	public function addSubOption( $mSubOption ) {
		$this->_aSubOptions[] = $mSubOption;
		return $this;
	}
		
	//
	public function isDisplayMode( $mParams ) {
		
		if ( $this->_bHasDisplayMode ) {
			
			// do an actual check
			
			if ( is_string( $mParams ) ) {
				$aParams = Geko_Array::explodeTrim( '|', $mParams, array( 'remove_empty' => TRUE ) );
			} else {
				$aParams = $mParams;
			}
			
			foreach ( $aParams as $sParam ) {
				if ( $sParam == $this->_sCurrentDisplayMode ) return TRUE;
			}
			
		} else {
			
			// delegate to the "target class" which must provide a concrete method
			$sTargetClass = Geko_String::coalesce( $this->_sBindToClass, $this->_sSubOptionParentClass );
			
			if ( $oTarget = $this->assertTargetClass( $sTargetClass ) ) {
				return $oTarget->isDisplayMode( $mParams );
			}
			
		}
				
		return FALSE;
	}
	
	//
	public function getDisplayMode() {
		
		if ( !$this->_bHasDisplayMode ) {
			// delegate to the "target class" which must provide a concrete method
			$sTargetClass = Geko_String::coalesce( $this->_sBindToClass, $this->_sSubOptionParentClass );
			
			if ( $oTarget = $this->assertTargetClass( $sTargetClass ) ) {
				return $oTarget->getDisplayMode();
			}		
		}
		
		return $this->_sCurrentDisplayMode;
	}
	
	
	//
	public function isCurrentPage() {
		
		// delegate to the "target class" which must provide a concrete method
		if ( $oTarget = $this->assertTargetClass( $this->_sSubOptionParentClass ) ) {
			return $oTarget->isCurrentPage();
		}
		
		return (
			( $this->_sCurrentPage == $this->_sInstanceClass ) || 
			( $this->_sCurrentPage == sprintf( '%s%s', $this->_sInstanceClass, $this->_sAddModeSuffix ) )
		);
	}
	
	//
	public function setSubOptionParentClass( $sSubOptionParentClass ) {
		
		$this->_sSubOptionParentClass = $sSubOptionParentClass;
		$this->_oSubOptionParent = Geko_Singleton_Abstract::getInstance( $sSubOptionParentClass );
		
		return $this;
	}
	
	//
	public function assertTargetClass( $sTargetClass ) {
		
		if (
			( $sTargetClass ) && ( class_exists( $sTargetClass ) ) && 
			( $sTargetClass != $this->_sInstanceClass )
		) {
			$oTarget = Geko_Singleton_Abstract::getInstance( $sTargetClass );
			if ( is_a( $oTarget, __CLASS__ ) ) return $oTarget;
		}
		
		return NULL;
	}
	
	
	
	
	
	//// TO DO: consolidate with Geko_App_Entity_Manage
	
	
	//// table functions
	
	// ensure that an <sql table object> is returned
	public function resolveTable( $mSqlTable ) {
		
		if ( is_string( $mSqlTable ) ) {
			$oSqlTable = $this->getTable( $mSqlTable );
		} elseif ( is_a( $mSqlTable, 'Geko_Sql_Table' ) ) {
			$oSqlTable = $mSqlTable;
		} else {
			$oSqlTable = NULL;
		}
		
		return $oSqlTable;
	}
	
	// ensure that a <table name> is returned
	public function resolveTableName( $mSqlTable ) {

		if ( is_string( $mSqlTable ) ) {
			$sTableName = $mSqlTable;
		} elseif ( is_a( $mSqlTable, 'Geko_Sql_Table' ) ) {
			$sTableName = $mSqlTable->getTableName();
		} else {
			$sTableName = '';
		}
		
		return $sTableName;
	}
	
	// create database table using the <sql table object> or <table name>
	public function createTable( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			return Geko_Wp_Db::createTable( $oSqlTable );
		}
		
		return FALSE;
	}
	
	//
	public function createTableOnce( $mSqlTable = NULL ) {
		
		if ( !$mSqlTable ) {
			$mSqlTable = $this->getPrimaryTable();
		}
		
		$oSqlTable = NULL;
		if ( $mSqlTable instanceof Geko_Sql_Table ) {
			$oSqlTable = $mSqlTable;
		} else {
			$oSqlTable = $this->resolveTable( $mSqlTable );
		}
		
		if ( $oSqlTable ) {
			return Geko_Once::run( $oSqlTable->getTableName(), array( $this, 'createTable' ), array( $oSqlTable ) );
		}
		
		return FALSE;
	}
	
	
	// register an <sql table object> into _aTables property
	// use <table name> as a key
	// if second arg is TRUE, register as _sPrimaryTable property
	public function addTable( $oSqlTable, $bPrimaryTable = TRUE ) {
		
		$sTableName = $oSqlTable->getTableName();
		
		$this->_aTables[ $sTableName ] = $oSqlTable;
		
		if ( $bPrimaryTable ) {
			
			$this->_sPrimaryTable = $sTableName;
			
			if ( !$this->_sEntityIdVarName && ( $oPkf = $oSqlTable->getPrimaryKeyField() ) ) {
				$this->_sEntityIdVarName = $oPkf->getFieldName();
			}
		}
		
 		return $this;
	}
	
	// get matching <table name> from _aTables
	// if <table name> is not provided, get the primary table
	public function getTable( $sTableName = '' ) {
		
		if ( $sTableName ) {
			return $this->_aTables[ $sTableName ];
		}
		
		return $this->getPrimaryTable();
	}
	
	// get _sPrimaryTable from _aTables
	public function getPrimaryTable() {
		
		Geko_Debug::out( $this->_sInstanceClass, __METHOD__ );
		
		$mRes = $this->_aTables[ $this->_sPrimaryTable ];
		
		Geko_Debug::out( gettype( $mRes ), sprintf( '%s::result', __METHOD__ ) );
		
		return $mRes;	
	}
	
	// drop the provided <sql table object> or <table name> from the database
	public function dropTable( $mSqlTable ) {
		
		global $wpdb;
				
		if ( $sTableName = $this->resolveTableName( $mSqlTable ) ) {
			$wpdb->query( sprintf( 'DROP TABLE %s', $sTableName ) );
		}
		
		return $this;
	}
	
	// drop the provided <sql table object> or <table name> from the database then create it
	public function resetTable( $mSqlTable ) {
		
		$this
			->dropTable( $mSqlTable )
			->createTable( $mSqlTable )
		;
		
		return $this;
	}
	
	// get an array of <sql table field objects> from the <sql table object>
	// wrapped as an <options field object>
	public function getTableFields( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			
			$aRet = array();

			// format db fields as Geko_Wp_Options_Field
			$aDbFields = $oSqlTable->getFields( TRUE );
			
			foreach ( $aDbFields as $sField => $oDbField ) {
				$aRet[ $sField ] = Geko_Wp_Options_Field::wrapSqlField( $oDbField );
			}
			
			return $aRet;
		}
		
		return array();
	}

	// get an array of key <sql table field objects> from the <sql table object>
	// wrapped as an <options field object>
	public function getTableKeyFields( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			
			$aRet = array();

			// format db fields as Geko_Wp_Options_Field
			$aDbFields = $oSqlTable->getKeyFields( TRUE );
			
			foreach ( $aDbFields as $sField => $oDbField ) {
				$aRet[ $sField ] = Geko_Wp_Options_Field::wrapSqlField( $oDbField );
			}
			
			return $aRet;
		}
		
		return array();
	}
	
	// get the <options field objects> of the <primary table>
	public function getPrimaryTableFields() {
		return $this->getTableFields( $this->getPrimaryTable() );
	}

	// get the key <options field objects> of the <primary table>
	public function getPrimaryTableKeyFields() {
		return $this->getTableKeyFields( $this->getPrimaryTable() );
	}
	
	// get the primary <options field object> of the given <sql table object>
	public function getTablePrimaryKeyField( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			
			if ( $oPkf = $oSqlTable->getPrimaryKeyField() ) {
				return Geko_Wp_Options_Field::wrapSqlField( $oPkf );
			}
		}
		
		return NULL;
	}
	
	// get the primary <options field object> of the primary <sql table object>
	public function getPrimaryTablePrimaryKeyField() {
		return $this->getTablePrimaryKeyField( $this->getPrimaryTable() );
	}
	
	
	
	
	
	
	
	
	//// error message handling
	
	//
	protected function triggerNotifyMsg() {
		$sNotifyKey = sprintf( '%snotify_msg_id', $this->_sInstanceClass );
		$_SESSION[ $sNotifyKey ] = func_get_args();
	}
	
	//
	protected function triggerErrorMsg() {
		$sErrorKey = sprintf( '%serror_msg_id', $this->_sInstanceClass );
		$_SESSION[ $sErrorKey ] = func_get_args();
	}

	//
	protected function triggerDebugMsg( $sDebugData ) {
		$sDebugKey = sprintf( '%sdebug', $this->_sInstanceClass );
		$_SESSION[ $sDebugKey ] = $sDebugData;
	}
	
	//
	public function notificationMessages() {
		
		$sNotifyKey = sprintf( '%snotify_msg_id', $this->_sInstanceClass );
		$sErrorKey = sprintf( '%serror_msg_id', $this->_sInstanceClass );
		$sDebugKey = sprintf( '%sdebug', $this->_sInstanceClass );
		
		if ( $aArgs = $_SESSION[ $sNotifyKey ] ):
			
			$iMsgId = $aArgs[ 0 ];
			
			$aMsgs = $this->getNotificationMsgs();
			$sMsg = ( $aMsgs[ $iMsgId ] ) ? $aMsgs[ $iMsgId ] : 'Unknown notification triggered.';
			$aArgs[ 0 ] = $sMsg;
			
			$sMsg = call_user_func_array( 'sprintf', $aArgs );
			
			?><div class="updated fade below-h2" id="message"><p><?php echo $sMsg; ?></p></div><?php
			unset( $_SESSION[ $sNotifyKey ] );
		endif; ?>
		
		<?php if ( $aArgs = $_SESSION[ $sErrorKey ] ):
			
			$iMsgId = $aArgs[ 0 ];
			
			$aMsgs = $this->getErrorMsgs();
			$sMsg = ( $aMsgs[ $iMsgId ] ) ? $aMsgs[ $iMsgId ] : 'Unknown error occured.';
			$aArgs[ 0 ] = $sMsg;

			$sMsg = call_user_func_array( 'sprintf', $aArgs );
			
			?><div class="error below-h2" id="notice"><p><?php echo $sMsg; ?></p></div><?php
			unset( $_SESSION[ $sErrorKey ] );
		endif;
		
		if ( $sDebugMsg = $_SESSION[ $sDebugKey ] ):
			?><div class="error below-h2" id="notice"><p><?php echo $sDebugMsg; ?></p></div><?php
			unset( $_SESSION[ $sDebugKey ] );
		endif;
		
	}
	
	// to be implemented by sub-class
	protected function getNotificationMsgs() {
		return array();
	}
	
	// to be implemented by sub-class
	protected function getErrorMsgs() {
		return array();
	}
	
	
	
	
	
	
	
	//// front-end display methods
	
	protected function preFormFields( $oPlugin = NULL ) { }				// echo stuff before the "wrap" div
	protected function formFields( $oPlugin = NULL ) { }				// form field html
	
	// hackish, allow public access
	public function _formFields( $oPlugin ) {
		return $this->formFields( $oPlugin );
	}
	
	// echo form, may need to wrap explicitly or not, the sub-class can decide
	public function outputForm( $oPlugin = NULL ) {
		$this->preFormFields( $oPlugin );
		echo $this->inject( TRUE, $oPlugin );
	}

	
	
	
	
	
	//// form processing/injection methods
	
	// shared javascript
	// TO DO: this should 
	public function adminHeadJs0() {
		
		?><script type="text/javascript">		
			
			jQuery.fn.extend( {
				showX: function ( fade, delay ) {
					if ( !delay ) delay = 200;
					
					if ( !fade ) this.show();	
					else this.fadeIn( delay );
				},
				hideX: function ( fade, delay ) {
					if ( !delay ) delay = 200;
					
					if ( !fade ) this.hide();	
					else this.fadeOut( delay );
				}
			} )
			
		</script><?php
		
		return $this;
	}
	
	
	//
	protected function getFormDoc( $oPlugin = NULL ) {
		return Geko_PhpQuery_FormTransform::createDoc( Geko_String::fromOb(
			array( $this, '_formFields' ), array( $oPlugin )
		) );
	}
	
	// inject values into the form
	protected function inject( $bReturnString = TRUE, $oPlugin = NULL ) {
		
		// get the form as a phpQuery object
		$oDoc = $this->getFormDoc( $oPlugin );
		
		// get the values what will be used to populate the form
		$aStoredOptions = $this->getStoredOptions( $oPlugin );
		
		// manipulate the doc and stored options by plugins
		list( $oDoc, $aStoredOptions ) = Geko_PhpQuery_FormTransform::modifyDoc( $oDoc, $aStoredOptions );
		
		$this->changeDoc( $oDoc );
		
		// pick out all the form elements
		$aElemsGroup = Geko_PhpQuery_FormTransform::getGroupedFormElems(
			$oDoc->find( 'input, textarea, select, label' ),
			$this->getPrefixForDoc( $oPlugin )
		);
		
		foreach ( $aElemsGroup as $sPrefixedGroupName => $aElem ) {
			
			// check if option exists
			if ( isset( $aStoredOptions[ $sPrefixedGroupName ] ) ) {
				Geko_PhpQuery_FormTransform::setElemValue(
					$aElem,
					$this->changeStoredOption( $aStoredOptions[ $sPrefixedGroupName ], $aElem )
				);
			} else {
				Geko_PhpQuery_FormTransform::setElemDefaultValue( $aElem );
			}
		}
		
		// clean up any special non-html tags
		Geko_PhpQuery_FormTransform::cleanUpNonHtml( $oDoc );
		
		$this->_aOptionKeys = array_keys( $aElemsGroup );
		
		$this->changeDocPostInject( $oDoc, $aStoredOptions );
		
		//
		if ( $bReturnString ) {
			return strval( $oDoc );
		} else {
			return $oDoc;			
		}
	}
	
	// hooks
	protected function changeDoc( $oDoc ) { }											// manipuate the $oDoc
	protected function changeDocPostInject( $oDoc, $aStoredOptions ) { }				// manipuate after injection
	protected function changeStoredOption( $mValue, $aElem ) {							// manipulate stored option value
		
		if (
			( is_object( $aElem[ 'elem' ] ) ) && 
			( $aElem[ 'elem' ]->attr( '_member_ids' ) )
		) {
			$aRet = array();
			foreach ( $mValue as $oItem ) {
				$aRet[] = Geko_String::coalesce( $oItem->member_id, $oItem->member_value );
			}
			return Zend_Json::encode( $aRet );		
		} elseif ( 
			( 'select:multiple' == $aElem[ 'type' ] ) && 
			( is_array( $mValue ) ) && 
			( is_object( $mValue[ 0 ] ) )
		) {
			// format return value for select multiple
			$aRet = array();
			foreach ( $mValue as $oItem ) {
				$aRet[] = Geko_String::coalesce( $oItem->member_id, $oItem->member_value );
			}
			return $aRet;
		}
		
		return $mValue;
	}
	
	
	// works with <p> notation items
	protected function extractParts( $oPlugin = NULL ) {
		
		if ( $oPlugin ) {
			return $this->getParts( $oPlugin );
		}
		
		if ( NULL == $this->_aParts ) {
			$this->_aParts = $this->getParts();
		}
		
		return $this->_aParts;
	}
	
	//
	public function getParts( $oPlugin = NULL ) {
		
		$oDoc = $this->inject( FALSE, $oPlugin );
		$aParts = array();
		
		foreach ( $oDoc[ 'p' ] as $oElemP ) {
			
			$oPqP = pq( $oElemP );
			
			$sLabel = trim( strval( $oPqP[ 'label.main' ]->html() ) );
			$sDescription = trim( strval( $oPqP[ 'label.description' ]->html() ) );
			
			if ( $sLabel ) $oPqP[ 'label.main' ]->remove();
			if ( $sDescription ) $oPqP[ 'label.description' ]->remove();
			
			foreach ( $oPqP[ 'input' ] as $oElemInput ) {
				$oPqInput = pq( $oElemInput );
				$oPqInput->addClass( $oPqInput->attr( 'type' ) );
			}
			
			$sFieldGroup = $oPqP->html();
			
			// get the name of the field group
			$sName = $oPqP->find( 'input, select, textarea' )->attr( 'name' );
			
			// sanitize the name
			if ( FALSE !== ( $iPos = strpos( $sName, '[ ' ) ) ) {
				$sName = substr( $sName, 0, $iPos );
			}
			
			$aPart = array(
				'label' => $sLabel,
				'name' => $sName,
				'field_group' => $sFieldGroup,
				'description' => $sDescription
			);
			
			$aPart = $this->extractPart( $aPart, $oPqP, $oPlugin );
			
			$aParts[] = $aPart;
			
		}
		
		return $aParts;
		
	}
	
	// sub-classes may implement this to further add to $aPart
	protected function extractPart( $aPart, $oPqP, $oPlugin = NULL ) {
		return $aPart;
	}
	
	
	//
	protected function getElemsGroup() {
		
		$oDoc = $this->getFormDoc();

		// pick out all the form elements
		return Geko_PhpQuery_FormTransform::getGroupedFormElems(
			$oDoc->find( 'input, textarea, select' ),
			$this->getPrefixForDoc()
		);
		
	}
		
	
	
	//// helper methods
	
	// for use in creating "signatures" for the wp admin menu hooks
	public function sanitize( $sValue ) {
		return str_replace( array( ' ', "'", '"', '-', '_' ), '', $sValue );
	}
	
	
	//
	public function resolveClass( $sClass ) {
		return Geko_Class::existsCoalesce( $sClass, sprintf( 'Gloc_%s', $sClass ), sprintf( 'Geko_Wp_%s', $sClass ) );
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( strtolower( $sMethod ), 'new' ) ) {
			
			$sClass = substr_replace( $sMethod, '', 0, 3 );
			
			if ( $sClass = $this->resolveClass( $sClass ) ) {
				$oReflect = new ReflectionClass( $sClass );
				return $oReflect->newInstanceArgs( $aArgs );
			}
			
			return NULL;
			
		}
		
		return parent::__call( $sMethod, $aArgs );
	}
	
	
	
	
	
	//////// rail functionality
	
	protected $_aListingFields = array();
	protected $_aDetailFields = array();
	
	protected $_aDbEntities = array();
	
	
	
	//// layout hooks
	
	//
	public function layoutTrigger( $sTriggerMethod, $oPlugin = NULL ) {
		
		$aFields = $this->getDetailFields( $oPlugin );
		foreach ( $aFields as $sField => $oField ) {
			
			$oField->setManage( $this );
			if ( is_a( $oWidget = $oField->getWidgetType(), 'Geko_Wp_Options_Plugin' ) ) {
				$aPlugins[ $sField ] = $oWidget;
				$oWidget->$sTriggerMethod();
			}
		}
		
		return $this;
	}
	
	//
	public function layoutEnqueue( $oPlugin = NULL ) {
		// trigger plugin enqueues
		$this->layoutTrigger( 'layoutEnqueue', $oPlugin );
		return $this;
	}
	
	//
	public function layoutHeadLate( $oPlugin = NULL ) {
		// trigger plugin head late
		$this->layoutTrigger( 'layoutHeadLate', $oPlugin );
		return $this;
	}
	
	
	
	//// form rendering
	
	//
	public function renderListing( $aEntities ) {
		$aFields = $this->getListingFields();
		?>
		<table class="geko-list-table">
			<tr>
				<?php foreach ( $aFields as $sField => $oField ): ?>
					<th><?php $this->renderTitle( $oField ); ?></th>
				<?php endforeach; ?>
			</tr>
			<?php foreach ( $aEntities as $oEntity ): ?>
				<tr>
					<?php foreach ( $aFields as $sField => $oField ): ?>
						<td><?php $this->renderValue( $oField, $oEntity ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}
	
	//
	public function getDetailTable( $oEntity = NULL, $bReadOnly = FALSE ) {

		$aFields = $this->getDetailFields();
		
		$oTable = _ge( 'table', array( 'class' => 'geko-detail-table' ) );
		foreach ( $aFields as $sField => $oField ) {
			
			if ( $bReadOnly ) {
				if ( $oField->isSkipDetailMode() ) continue;			
			} else {
				if ( $oEntity ) {
					if ( $oField->isSkipEditMode() ) continue;			
				} else {
					if ( $oField->isSkipAddMode() ) continue;			
				}
			}
			
			$oTable = $this->getDetailRow( $oTable, $oField, $oEntity, $bReadOnly );
			
		}
		
		return $oTable;
		
	}
	
	//
	public function getDetailRow( $oTable, $oField, $oEntity, $bReadOnly ) {
		
		$oField->setManage( $this );
		if ( is_object( $mWidget = $oField->getWidgetType() ) ) {
			
			// plugin hook
			$oTable = $mWidget->addDetailFields( $oTable, $oEntity, $bReadOnly );
			
		} else {
			
			$oTr = _ge( 'tr' );
			$oTr->addClass( $oField->getDetailModeRowClasses() );
			
			// field label
			$oTh = _ge( 'th' );
			$oTh->append( $this->srenderTitle( $oField ) );
			$oTr->append( $oTh );
							
			// widget
			$oTd = _ge( 'td' );
			
			if ( $bReadOnly ) {
				$oTd->append( $this->srenderValue( $oField, $oEntity ) );
			} else {
				$oTd->append( $this->getFormWidget( $oField, $oEntity ) );
			}
			
			$oTr->append( $oTd );
			
			$oTable->append( $oTr );
			
		}
		
		return $oTable;
	}
	
	//
	public function renderDetail( $oEntity ) {
		
		$oTable = $this->getDetailTable( $oEntity, TRUE );
		echo strval( $oTable );
		
	}
	
	//
	public function renderDetailForm( $oEntity = NULL ) {
		
		?>
		<form id="detailform">
			
			<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
			<div class="error"></div>
			<div class="success"></div>
			
			<?php
			
			$oTable = $this->getDetailTable( $oEntity );
			
			$oTd = _ge( 'td', array( 'colspan' => 2 ) );
			
			$oInput = _ge( 'input', array( 'type' => 'reset', 'value' => 'Reset' ) );
			$oTd->append( $oInput );
			
			$oInput = _ge( 'input', array( 'type' => 'submit', 'value' => 'Submit' ) );
			$oTd->append( $oInput );
			
			$oTr = _ge( 'tr' )->append( $oTd );
			
			$oTable->append( $oTr );
			
			echo strval( $oTable );
			
			?>
			
		</form>
		<?php
	}
	
	// plugin hook
	public function addDetailFields( $oTable, $oParEnt, $bReadOnly, $oPlugin = NULL ) {
		
		$aFields = $this->getDetailFields( $oPlugin );
		$oEntity = $this->getDetailEntity( $oParEnt, $oPlugin );
		
		foreach ( $aFields as $sField => $oField ) {
			
			if ( $bReadOnly ) {
				if ( $oField->isSkipDetailMode() ) continue;			
			} else {
				if ( $oEntity ) {
					if ( $oField->isSkipEditMode() ) continue;			
				} else {
					if ( $oField->isSkipAddMode() ) continue;			
				}
			}
			
			$oTable = $this->getDetailRow( $oTable, $oField, $oEntity, $bReadOnly );
		}
		
		return $oTable;
	}
	
	// plugin hook
	public function getDetailEntity( $oParEnt, $oPlugin = NULL ) {
	
	}
	
	
	//// field formatters
	
	//
	public function _formatFields( $aFields ) {
		
		$aDbFields = array();
		if ( $oPriTable = $this->getPrimaryTable() ) {
			$aDbFields = $oPriTable->getFields( TRUE );
		}
		
		$aRet = array();
		
		foreach ( $aFields as $sField => $aField ) {
			$oField = Geko_Wp_Options_Field::wrapSqlField( $aDbFields[ $sField ], $aField );
			$oField->setName( $sField );
			$aRet[ $sField ] = $oField;
		}
		
		return $aRet;
	}
	
	//
	public function getListingFields() {
		return $this->_formatFields( $this->_aListingFields );
	}
	
	//
	public function getDetailFields( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getDetailFields();
		}
		return $this->_formatFields( $this->_aDetailFields );
	}
	
	//// render helpers
	
	//
	public function srenderTitle( $oField ) {
		return $oField->getTitle();
	}
	
	//
	public function renderTitle( $oField ) {	
		echo $this->srenderTitle( $oField );
	}
	
	//
	public function srenderValue( $oField, $oEntity ) {
		
		if ( !$oField || !$oEntity ) return NULL;
		
		$sField = $oField->getName();
		$sValue = $oEntity->getValue( $sField );
		
		if ( $sLink = $oField->getLink() ) {
			
			$sUrl = sprintf(
				'%s%s?%s=%d',
				Geko_Wp::getUrl(), $sLink, $this->_sEntityIdVarName, $oEntity->getId()
			);
			
			$sValue = sprintf( '<a href="%s">%s</a>', $sUrl, $sValue );
		}
		
		return $sValue;
	}

	//
	public function renderValue( $oField, $oEntity ) {
		echo $this->srenderValue( $oField, $oEntity );
	}
	
	// implement delegators/hooks
	public function getFormWidget( $oField, $oEntity = NULL, $oPlugin = NULL ) {
		
		$mValue = NULL;
		if ( $oEntity ) {
			$mValue = $this->srenderValue( $oField, $oEntity );
		}
		
		$sWidgetType = $oField->getWidgetType();
		
		$sField = $oField->getName();
		
		$aAtts = array(
			'id' => $sField,
			'name' => $sField
		);
		
		return _gw( $sWidgetType, $aAtts, $mValue, $oField->getWidgetParams() )->get();		
	}
	
	
	//
	public function renderFormWidget( $oField, $oEntity = NULL ) {
		echo strval( $this->getFormWidget( $oField, $oEntity ) );
	}
	
	
	
	//// crud methods
	
	
	
	// target entity
	
	//
	public function resolveEntity( $aParams ) {
		if ( $sQueryClass = $this->_sQueryClass ) {
			$aRes = new $sQueryClass( $aParams, FALSE );
			if ( $aRes->getTotalRows() == 1 ) {
				return $aRes->getOne();
			}
		}
		return NULL;
	}
	
	// standard contexts are current, insert, update, delete
	public function setTargetEntity( $oEntity, $sContext = '' ) {
		$this->_aDbEntities[ 'current' ] = $oEntity;
		if ( $sContext ) {
			$this->_aDbEntities[ $sContext ] = $oEntity;
		}
		return $this;
	}
	
	//
	public function getTargetEntity( $sContext = '' ) {
		if ( $sContext ) {
			return $this->_aDbEntities[ $sContext ];
		}
		return $this->_aDbEntities[ 'current' ];
	}
	
	// array
	public function getTargetEntityIds( $sContext = '' ) {
		if ( $oEntity = $this->getTargetEntity( $sContext ) ) {
			return $oEntity->getIds();
		}
	}
	
	// scalar
	public function getTargetEntityId() {
		if ( $oEntity = $this->getTargetEntity( $sContext ) ) {
			return $oEntity->getId();
		}
	}
	
	// default hook methods
	public function modifyDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $aPostVals;
	}
	
	// default hook methods
	public function checkDetailValues( $aPostVals, $oPlugin = NULL ) {
		return FALSE;
	}
	
	
	
	// insert
	
	//
	public function insertDetails( $oPlugin = NULL ) {
		
		global $wpdb;
		
		$aPostVals = $_POST;
		$aPostVals = $this->modifyInsertDetailValues( $aPostVals, $oPlugin );

		if ( !$this->checkInsertDetailValues( $aPostVals, $oPlugin ) ) {
			return FALSE;
		}
		
		$aFields = $this->getDetailFields( $oPlugin );
		
		$aValues = array();
		$aFormat = array();
		$aAuto = array();
		$sPkField = '';
		
		$aPlugins = array();
		foreach ( $aFields as $sField => $oField ) {
			
			// gather any plugins
			$oField->setManage( $this );
			if ( is_a( $oWidget = $oField->getWidgetType(), 'Geko_Wp_Options_Plugin' ) ) {
				$aPlugins[ $sField ] = $oWidget;
				continue;
			}

			if ( $oField->isPrimaryKey() ) $sPkField = $sField;
			
			if ( !isset( $aPostVals[ $sField ] ) && !$oField->isAutoInsert() ) continue;
			
			// skip this
			if ( $oField->isSkipInsert() ) continue;
			
			$mValue = $aPostVals[ $sField ];
						
			$aValues[ $sField ] = $oField->getSmartFormattedValue( $mValue );
			$aFormat[] = $oField->getFormat();
			
			if ( $oField->isAuto() ) {
				$aAuto[ $sField ] = $aValues[ $sField ];
			}
			
		}
				
		$bRes = $wpdb->insert(
			$this->_sPrimaryTable,
			$aValues,
			$aFormat
		);
		
		if ( $bRes ) {
			
			if ( $sPkField ) $aAuto[ $sPkField ] = $wpdb->insert_id;
			
			if ( $oEntity = $this->resolveEntity( $aAuto ) ) {
				$this->setTargetEntity( $oEntity, 'insert' );
			}
			
			// go through plugins
			foreach ( $aPlugins as $sField => $oWidget ) {
				$oWidget->insertDetails();
			}
			
		}
		
		return $bRes;
	}
	
	// hook method
	public function modifyInsertDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $this->modifyDetailValues( $aPostVals, $oPlugin );
	}
	
	// hook method
	public function checkInsertDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $this->checkDetailValues( $aPostVals, $oPlugin );
	}
	
	
	// update
	
	//
	public function updateDetails( $oPlugin = NULL ) {
		
		global $wpdb;
		
		$this->_aUpdateIds = array();
		
		$aPostVals = $_POST;
		$aPostVals = $this->modifyUpdateDetailValues( $aPostVals, $oPlugin );
		
		if ( !$this->checkUpdateDetailValues( $aPostVals, $oPlugin ) ) {
			return FALSE;
		}

		$aFields = $this->getDetailFields( $oPlugin );
		
		$aValues = array();
		$aFormat = array();
		
		$aWhere = array();
		$aWhereFormat = array();
		
		$aPlugins = array();
		foreach ( $aFields as $sField => $oField ) {
			
			// gather any plugins
			$oField->setManage( $this );
			if ( is_a( $oWidget = $oField->getWidgetType(), 'Geko_Wp_Options_Plugin' ) ) {
				$aPlugins[ $sField ] = $oWidget;
				continue;
			}
			
			if ( !isset( $aPostVals[ $sField ] ) && !$oField->isAutoUpdate() ) continue;
			
			// skip this
			if ( $oField->isSkipUpdateField() ) continue;
			
			$mValue = $aPostVals[ $sField ];
			
			// write "where"
			if ( $oField->isWhereUpdate() ) {
				
				$aWhere[ $sField ] = $oField->getSmartFormattedValue( $mValue );
				$aWhereFormat[] = $oField->getFormat();
								
				continue;
			} 
			
			$aValues[ $sField ] = $oField->getSmartFormattedValue( $mValue );
			$aFormat[] = $oField->getFormat();
			
		}
		
		// test if entity exists
		if ( !$oEntity = $this->resolveEntity( $aWhere ) ) {
			// entity does not exist, route to insert method
			return $this->insertDetails( $oPlugin );
		}
		
		// track entity before updating
		$this->setTargetEntity( $oEntity, 'pre_update' );
		
		//
		$bRes = $wpdb->update(
			$this->_sPrimaryTable,
			$aValues, $aWhere,
			$aFormat, $aWhereFormat
		);
		
		if ( $bRes ) {
			
			if ( $oEntity = $this->resolveEntity( $aWhere ) ) {
				$this->setTargetEntity( $oEntity, 'update' );
			}
			
			// go through plugins
			foreach ( $aPlugins as $sField => $oWidget ) {
				$oWidget->updateDetails();
			}
			
		}
		
		return $bRes;
	}
	
	//
	public function getUpdateValues( $oPlugin = NULL ) {
		
		$aRet = array();
		
		$aFields = $this->getPrimaryTableFields();
		$oEntity = $this->getTargetEntity();
		
		foreach ( $aFields as $sField => $oField ) {
			if ( !$oField->returnsUpdateValue() ) continue;
			$aRet[ $sField ] = $this->srenderValue( $oField, $oEntity );
		}
		
		return $aRet;
	}
	
	// hook method
	public function modifyUpdateDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $this->modifyDetailValues( $aPostVals, $oPlugin );
	}
	
	// hook method
	public function checkUpdateDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $this->checkDetailValues( $aPostVals, $oPlugin );
	}
	
	
	// delete
	
	//
	public function deleteDetails( $oPlugin = NULL ) {
		
		global $wpdb;
		
		$aPostVals = $_POST;
		$aPostVals = $this->modifyDeleteDetailValues( $aPostVals, $oPlugin );
		
		print_r( $aPostVals );
		
		if ( !$this->checkDeleteDetailValues( $aPostVals, $oPlugin ) ) {
			return FALSE;
		}
		
		$aFields = $this->getDetailFields( $oPlugin );

		$aWhere = array();
		$aWhereFormat = array();
		
		$aPlugins = array();
		foreach ( $aFields as $sField => $oField ) {
			
			// gather any plugins
			$oField->setManage( $this );
			if ( is_a( $oWidget = $oField->getWidgetType(), 'Geko_Wp_Options_Plugin' ) ) {
				$aPlugins[ $sField ] = $oWidget;
				continue;
			}
			
			if ( !$oField->isWhereDelete() ) continue;
			
			$mValue = $aPostVals[ $sField ];
			
			$aWhere[ $sField ] = $oField->getSmartFormattedValue( $mValue );
			$aWhereFormat[] = $oField->getFormat();
			
		}
		
		$oSqlDelete = new Geko_Sql_Delete();
		$oSqlDelete->from( $this->_sPrimaryTable );
		
		$i = 0;
		foreach ( $aWhere as $sField => $mValue ) {
			$oSqlDelete->where( $wpdb->prepare(
				sprintf( '( %s = %s )', $sField, $aWhereFormat[ $i ] ),
				$mValue
			) );
		}
		
		// track the entity before it is deleted
		$oEntity = $this->resolveEntity( $aWhere );
		
		$bRes = $wpdb->query( $oSqlDelete );
		
		if ( $bRes ) {
			
			if ( $oEntity ) $this->setTargetEntity( $oEntity, 'delete' );
			
			// go through plugins
			foreach ( $aPlugins as $sField => $oWidget ) {
				$oWidget->deleteDetails();
			}
			
		}
		
		return $bRes;
		
	}
	
	// hook method
	public function modifyDeleteDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $this->modifyDetailValues( $aPostVals, $oPlugin );
	}
	
	// hook method
	public function checkDeleteDetailValues( $aPostVals, $oPlugin = NULL ) {
		return $this->checkDetailValues( $aPostVals, $oPlugin );
	}
	
	
	
}


