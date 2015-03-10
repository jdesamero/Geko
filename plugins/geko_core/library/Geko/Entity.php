<?php

//
abstract class Geko_Entity
{
	protected $_oPrimaryTable = NULL;
	
	protected $_oEntity;
	protected $_oQuery;
	protected $_aData = array();
	protected $_aQueryParams = NULL;
	
	protected $_sEntityClass = '';
	protected $_sQueryClass = '';
	protected $_sRewriteClass = '';
	protected $_sManageClass = '';
	protected $_sMetaClass = '';
	protected $_sServiceClass = '';
	
	protected $_sEntityIdVarName = '';
	protected $_aEntityIdVarNames = array();			// multi-key support
	protected $_sEntitySlugVarName = '';
	
	protected $_sEditEntityIdVarName = 'entity_id';
	protected $_sEditParentEntityIdVarName = 'parent_entity_id';
	
	protected $_bAllowNonExistentEntities = TRUE;
	protected $_sEntityPropertyPrefix = '';
	
	protected $_aEntityPropertyNames = array(
		'title' => 'title',
		'slug' => 'slug',
		'content' => 'content',
		'excerpt' => 'excerpt',
		'date_created' => 'date_created',
		'date_modified' => 'date_modified',
		'date_created_gmt' => 'date_created_gmt',
		'date_modified_gmt' => 'date_modified_gmt',
		'parent_entity_id' => 'parent_entity_id'
	);
	
	protected $_sFileBaseDir = '';
	protected $_aFileSubdirMap = array();
	
	protected $_aDelegates = array();
	protected $_aPlugins = array();
	
	
	
	
	
	//// construction
	
	//
	public function __construct( $mEntity = NULL, $oQuery = NULL, $aData = array(), $aQueryParams = NULL ) {
		
		// $aQueryParams is used with getEntityFromId or getEntityFromSlug
		
		// default related classes
		
		$this->_sEntityClass = get_class( $this );
		
		$this->_sQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Query', $this->_sQueryClass
		);
		
		$this->_sRewriteClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Rewrite', $this->_sRewriteClass
		);
		
		$this->_sManageClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Manage', $this->_sManageClass
		);
		
		$this->_sMetaClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Meta', $this->_sMetaClass
		);
		
		$this->_sServiceClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Service', $this->_sServiceClass
		);
		
		
		
		// default entity mappings
		
		$this->setEntityMapping( 'id', $this->_sEntityIdVarName );
		
		if ( is_array( $aKeyFields = $this->getEntityMapping( 'id' ) ) ) {
			$this->_aEntityIdVarNames = $aKeyFields;
		}
		
		
		// assignments
		$this->_aData = $aData;
		$this->_aQueryParams = $aQueryParams;
		
		
		// init methods called by sub-class
		
		$this->init();
		
		if ( is_null( $mEntity ) ) {
			$mEntity = $this->getDefaultEntityValue();
		}
		
		// formatEntity() may set $this->_oQuery
		$this->_oEntity = $this->formatEntity( $mEntity );
		
		// query was provided via the constructor
		if ( $oQuery ) $this->_oQuery = $oQuery;
		
		if ( $this->_oQuery ) {
			$this->_oPrimaryTable = $this->_oQuery->getPrimaryTable();
		}
		
		$this->constructEnd();
	}
	
	// hook to "format" entity, in case object was not given
	public function formatEntity( $mEntity ) {
		
		if ( is_scalar( $mEntity ) ) {
			if ( $mEntityId = $this->assertEntityId( $mEntity ) ) {
				$mEntity = $this->getEntityFromId( $mEntityId );
			} else {
				// assuming $mEntity is slug
				$mEntity = $this->getEntityFromSlug( strval( $mEntity ) );
			}
		}
		
		if ( is_array( $mEntity ) ) $mEntity = ( object ) $mEntity;
		if ( !is_object( $mEntity ) ) $mEntity = NULL;
		
		return $mEntity;
	}
	
	//
	public function assertEntityId( $mEntity ) {
		
		// multi-key support
		if (
			( count( $this->_aEntityIdVarNames ) > 0 ) && 
			( preg_match( '/^([0-9:]+)$/', $mEntity ) )
		) {
			return explode( ':', $mEntity );
		}
		
		if (
			( preg_match( '/^[0-9]+$/', $mEntity ) ) &&
			( $mEntityId = intval( $mEntity ) )
		) {
			return $mEntityId;
		}
		
		return NULL;
	}
	
	// implement by sub-class to call setEntityMapping()
	public function init() {
		return $this;
	}
	
	// do stuff after calling formatEntity()
	public function constructEnd() {
		
		$this->doPluginAction(
			'constructEnd',
			$this,
			$this->_oEntity,
			$this->_oQuery,
			$this->_aData,
			$this->_aQueryParams,
			$this->_oPrimaryTable
		);
		
		
		return $this;
	}
	
	
	//
	public function getEntityFromId( $mEntityId ) {
		
		$aParams = array();
		
		// multi-key support
		if (
			( count( $this->_aEntityIdVarNames ) > 0 ) && 
			( is_array( $aIds = $mEntityId ) )
		) {
			foreach ( $this->_aEntityIdVarNames as $i => $sField ) {
				$aParams[ $sField ] = $aIds[ $i ];
			}
		} else {
			// default, single key
			$aParams = array( $this->_sEntityIdVarName => $mEntityId );
		}
		
		return $this->querySingleEntity( $aParams );
	}
	
	//
	public function getEntityFromSlug( $sEntitySlug ) {
		
		$aParams = array( $this->_sEntitySlugVarName => $sEntitySlug );
		
		return $this->querySingleEntity( $aParams );
	}
	
	// hook method
	public function modifySingleEntityQueryParams( $aParams ) {
		return $aParams;
	}
	
	//
	public function querySingleEntity( $aParams ) {
		
		if ( is_array( $this->_aQueryParams ) ) {
			$aParams = array_merge( $aParams, $this->_aQueryParams );
		}
		
		$aParams = $this->modifySingleEntityQueryParams( $aParams );
		
		$oQuery = new $this->_sQueryClass( $aParams, FALSE );
		
		$oEntity = $oQuery->getOne();
		
		if ( $oEntity->isValid() ) {
			
			$this->_oQuery = $oQuery;
			
			return $oEntity->getRawEntity();
		}
		
		return NULL;
	}
	
	
	// does not have to return an object
	// can be any value that can be passed to formatEntity()
	public function getDefaultEntityValue() {
		
		if ( $this->_sRewriteClass ) {
			return Geko_Singleton_Abstract::getInstance(
				$this->_sRewriteClass
			)->getSingleVar();
		} else {
			return NULL;
		}
	}
	
	
	
	//
	public function setData( $sKey, $mValue ) {
		$this->_aData[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function getData( $sKey ) {
		return $this->_aData[ $sKey ];
	}
	
	
	
	
	//
	public function addDelegate( $sClassName ) {
		
		$this->_aDelegates[] = Geko_Delegate::create( $sClassName, $this );
		
		return $this;
	}
	
	
	
	
	//// initial helpers
	
	//
	public function setEntityMapping( $sIndex, $sProperty ) {
		
		// multi-key support
		$mProperty = ( FALSE !== strpos( $sProperty, ':' ) ) ?
			 explode( ':', $sProperty ) : $sProperty
		;
		
		$this->_aEntityPropertyNames[ $sIndex ] = $mProperty;
		return $this;
	}
	
	//
	public function getEntityMapping( $sIndex ) {
		return $this->_aEntityPropertyNames[ $sIndex ];
	}
	
	//
	public function getEntityPropertyValue( $sIndex ) {
		
		$mProperty = $this->getEntityMapping( $sIndex );
		if ( !$mProperty ) $mProperty = $sIndex;
		
		
		//// get results
		
		$mContent = NULL;
		
		// muti-key support
		if ( is_array( $mProperty ) ) {
			
			$aRet = array();
			
			foreach ( $mProperty as $sMultiIndex ) {
				
				// force strval since we're imploding
				$aRet[] = strval( $this->getEntityPropertyValue( $sMultiIndex ) );
			}
			
			$mContent = implode( ':', $aRet );
			$sProperty = implode( ':', $mProperty );
			
		} else {
			
			$sProperty = $mProperty;
			$mContent = $this->_oEntity->$sProperty;
		}
		
		
		// apply plugin method
		$mContent = $this->applyPluginFilter( 'getEntityPropertyValue', $mContent, $sProperty, $sIndex, $this );
		
		
		return $mContent;
	}
	
	//
	public function hasEntityProperty( $sIndex ) {

		$mProperty = $this->getEntityMapping( $sIndex );
		if ( !$mProperty ) $mProperty = $sIndex;
		
		// muti-key support
		if ( is_array( $mProperty ) ) {
			foreach ( $mProperty as $sMultiIndex ) {
				if ( !$this->hasEntityProperty( $sMultiIndex ) ) {
					return FALSE;
				}
			}
			return TRUE;
		}
		
		return property_exists( $this->_oEntity, $mProperty );	
	}
	
	// allow for the removal of prefix to simplify access to the property
	public function setEntityPropertyPrefix( $sPrefix ) {
		
		$iReplacements = 1;
		foreach ( $this->_oEntity as $sKey => $mValue ) {
			if ( 0 === strpos( $sKey, $sPrefix ) ) {
				// property with matching prefix found
				$this->setEntityMapping(
					str_replace( $sPrefix, '', $sKey, $iReplacements ),
					$sKey
				);
			}
		}
		
		$this->_sEntityPropertyPrefix = $sPrefix;
		
		return $this;
	}
	
	// if set to TRUE, suppresses errors from call'd non-existent entities
	public function setAllowNonExistentEntities( $bAllowNonExistentEntities ) {
		$this->_bAllowNonExistentEntities = $bAllowNonExistentEntities;
		return $this;
	}
	
	// multi-key support
	public function getIds() {
		
		$aIds = array();
		
		if ( count( $this->_aEntityIdVarNames ) > 0 ) {
			
			foreach ( $this->_aEntityIdVarNames as $sField ) {
				$aIds[ $sField ] = $this->getEntityPropertyValue( $sField );
			}
			
		} else {
			
			// !!!!!!!!! may be flaky
			$aIds[ $this->_sEntityIdVarName ] = $this->getEntityPropertyValue( 'id' );
		}
		
		return $aIds;
	}
	
	//// raw entity accessors
	
	//
	public function getRawEntity() {
		return $this->_oEntity;
	}
	
	//
	public function isValid() {
		return ( $this->_oEntity ) ? TRUE : FALSE;
	}

	//
	public function hasParentQuery() {
		return ( $this->_oQuery ) ? TRUE : FALSE;
	}

	//
	public function getParentQuery() {
		return $this->_oQuery;
	}
	
	//
	public function getParentEntity() {
		$iParentId = ( $this->getParentEntityId() ) ? $this->getParentEntityId() : NULL;
		return new $this->_sEntityClass( $iParentId );
	}
	
	//
	public function initRawEntity() {
		
		if ( !$this->_oEntity ) {
			$this->_oEntity = new StdClass;
		}
		
		return $this->getRawEntity();
	}
	
	//
	public function getRawEntityKeys() {
		return array_keys( get_object_vars( $this->_oEntity ) );
	}
	
	
	
	// should be a mix-in
	public function getPrimaryTable() {
		
		if ( NULL === $this->_oPrimaryTable ) {
			
			if ( $this->_sManageClass ) {
				
				$oMng = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
				
				if ( !$oMng->getCalledInit() ) {
					$oMng->init();
				}
				
				if ( $oTable = $oMng->getPrimaryTable() ) {
					$this->_oPrimaryTable = $oTable;
				} else {
					$this->_oPrimaryTable = FALSE;
				}
			}
			
		}
		
		return $this->_oPrimaryTable;
	}
	
	
	
	
	//// to be implemented by sub-class
	
	//
	public function retPermalink() {
		if ( $this->_sRewriteClass ) {
			return sprintf(
				Geko_Singleton_Abstract::getInstance(
					$this->_sRewriteClass
				)->getSinglePermastruct(),
				$this->getSlug()
			);			
		} else {
			return '';
		}
	}
	
	//
	public function getRawMeta( $sMetaKey ) {
		
		$mValue = NULL;
		
		$mValue = $this->applyPluginFilter(
			'getRawMeta',
			$mValue,
			$sMetaKey,
			$this->_oEntity,
			$this->_oQuery,
			$this->_aData,
			$this->_aQueryParams,
			$this->_oPrimaryTable,
			$this
		);
		
		return $mValue;
	}
	
	
	
	//// helper accessors
	
	// try to get a value, in the following order:
	// entity property value, meta value, method call
	public function getValue() {
		
		$aArgs = func_get_args();
		$sKey = array_shift( $aArgs );
		
		if ( !$sKey ) $sKey = 'value';			// take care of possible __call()
		
		$mValue = $this->getEntityPropertyValue( $sKey );
		
		if ( !$mValue ) {
			
			$mValue = $this->getMeta( $sKey );
			
			if ( !$mValue && !in_array( $sKey, array( 'value', 'meta' ) ) ) {
				
				$sMethod = sprintf( 'get%s', Geko_Inflector::camelize( $sKey ) );
				
				if ( method_exists( $this, $sMethod ) ) {
					$mValue = call_user_func_array( array( $this, $sMethod ), $aArgs );
				}
			}
		}
		
		return $mValue;
	}
	
	// alias of getPermalink()
	public function getUrl() {
		return $this->getPermalink();
	}
	
	// wrap the url as a Geko_Uri object
	public function getUrlObj() {
		return new Geko_Uri( $this->getUrl() );
	}
	
	// create <a> tag using getUrl() and getTitle()
	public function getLink() {
		return sprintf(
			'<a href="%s">%s</a>',
			$this->getUrl(),
			$this->getTitle()			
		);
	}
	
	// "filtered" version of getTitle()
	public function getTheTitle() {
		return $this->getTitle();
	}
	
	// alias of getContent
	public function getDescription() {
		return $this->getContent();
	}
	
	// getContent() with the_content filters applied
	public function getTheContent() {
		return $this->getContent();
	}
	
	// 
	public function getTheExcerpt( $iLimit, $sBreak = ' ', $sPad = '...' ) {
		
		if ( $sExcerpt = $this->getExcerpt() ) {
			return $sExcerpt;
		} else {
			return Geko_String::truncate(
				strip_tags( $this->getContent() ), $iLimit, $sBreak, $sPad
			);
		}
	}
	
	
	//
	public function getMeta( $sMetaKey ) {
		
		if ( isset( $this->_oEntity->$sMetaKey ) ) {
			return $this->_oEntity->$sMetaKey;
		}
		
		if (
			( $this->_sEntityPropertyPrefix ) && 
			( $mValue = $this->getRawMeta( sprintf( '%s%s', $this->_sEntityPropertyPrefix, $sMetaKey ) ) )
		) {
			return $mValue;
		}
		
		return $this->getRawMeta( $sMetaKey );
	}
	
	// access a meta value that is expected to be JSON formatted
	public function getMetaFromJson( $sMetaKey ) {
		
		$mValue = $this->getMeta( $sMetaKey );
		
		try {
			if ( $mValue ) return Zend_Json::decode( $mValue );
		} catch ( Exception $e ) {
			return NULL;		
		}
		
		return NULL;
	}
	
	// explode a line delimited meta value into an array
	public function getMetaAsArray( $sMetaKey ) {
		return Geko_Array::explodeTrimEmpty( "\n", $this->getMeta( $sMetaKey ) );
	}
	
	
	
	
	// to be implemented by sub-class
	public function getEditUrl() {
		return '';
	}
	
	//
	public function getEditLink() {
		return Geko_String::sprintfWrap(
			'<a href="%s" target="_blank">Edit</a>', $this->getEditUrl()
		);
	}
		
	// to be implemented by sub-class
	// a function that is aware of current logged in user
	public function getTheEditUrl() {
		return '';
	}
	
	// a function that is aware of current logged in user
	public function getTheEditLink() {
		return Geko_String::sprintfWrap(
			'<a href="%s" target="_blank">Edit</a>', $this->getTheEditUrl()
		);
	}
	
	

	
	
	
	////// helper methods
	
	//
	public function dateTimeFormat( $sSqlDateTime, $sFormat ) {
		// TO DO: default $sFormat???
		return $this->mysql2Date( $sSqlDateTime, $sFormat );
	}
	
	//
	public function dateFormat( $sSqlDateTime, $sFormat = '' ) {
		// TO DO: default $sFormat???
		return $this->mysql2Date( $sSqlDateTime, $sFormat );
	}
	
	//
	public function timeFormat( $sSqlDateTime, $sFormat = '' ) {
		// TO DO: default $sFormat???
		return $this->mysql2Date( $sSqlDateTime, $sFormat );
	}
	
	//
	public function mysql2Date( $sSqlDateTime, $sFormat ) {
		return date( $sFormat, strtotime( $sSqlDateTime ) );	
	}
	
	//
	public function escapeHtml( $sValue ) {
		return htmlspecialchars( $sValue );
	}
	
	//
	public function boolFormat( $mValue, $sTrueLabel = '', $sFalseLabel = '' ) {
		
		$sTrueLabel = ( $sTrueLabel ) ? $sTrueLabel : 'Yes';
		$sFalseLabel = ( $sFalseLabel ) ? $sFalseLabel : 'No';
		
		return intval( $mValue ) ? $sTrueLabel : $sFalseLabel ;
	}
	
	
	
	
	
	//// url and file handling/resolution

	//
	public function _getFileLoc( $sValue, $sProp, $sBase, $sDirSep ) {
		
		if ( $sValue ) {
			
			$sPropFmt = Geko_Inflector::underscore( $sProp );
			$sSubDir = '';
			
			if ( isset( $this->_aFileSubdirMap[ $sPropFmt ] ) ) {
				$sSubDir = $this->_aFileSubdirMap[ $sPropFmt ];
				$sSubDir = str_replace( '##base_dir##', $this->_sFileBaseDir, $sSubDir );
			} else {
				// default
				$sSubDir = sprintf( '%s%s%s', $this->_sFileBaseDir, $sDirSep, $sPropFmt );
			}
			
			return sprintf( '%s%s%s%s%s', $sBase, $sDirSep, $sSubDir, $sDirSep, $sValue );
		}
		
		return '';	
	}
	
	
	// TO DO: probably not correct
	public function _getBaseUrl() {
		return Geko_Uri::getBase();
	}
	
	// $aArgs are un-used
	public function _getFileUrl( $sValue, $sProp, $aArgs ) {
		return $this->_getFileLoc( $sValue, $sProp, $this->_getBaseUrl(), '/' );
	}
	
	// ie: document root
	// TO DO: probably not correct
	public function _getBasePath() {
		return $_SERVER[ 'DOCUMENT_ROOT' ];
	}
	
	//
	public function _getFilePath( $sValue, $sProp, $aArgs ) {
		return $this->_getFileLoc( $sValue, $sProp, $this->_getBasePath(), DIRECTORY_SEPARATOR );
	}
	
	//
	public function _getFileSize( $sValue, $sProp, $aArgs ) {
		if ( $sFile = $this->_getFilePath( $sValue, $sProp, $aArgs ) ) {
			return Geko_File::getSizeFormatted( $sFile );
		}
		return '';	
	}
	
	//
	public function _getFileIs( $sValue, $sProp, $aArgs ) {
		if ( $sFile = $this->_getFilePath( $sValue, $sProp, $aArgs ) ) {
			return is_file( $sFile );
		}
		return FALSE;	
	}
	
	
	
	
	
	//// formatting helper methods
	
	//
	public function mysql2DateFormat() {
		
		$aArgs = func_get_args();
		$sDate = call_user_func_array( array( $this, 'getValue' ), $aArgs );
		
		if ( $sFormat = $aArgs[ 1 ] ) return $this->mysql2Date( $sDate, $sFormat );
		
		return $sDate;
	}
	
	//
	public function nl2brFormat() {
		
		$aArgs = func_get_args();
		$sValue = call_user_func_array( array( $this, 'getValue' ), $aArgs );
		
		return nl2br( trim( $sValue ) );
	}
	
	//
	public function getValueFromJson() {		
		
		$aArgs = func_get_args();
		$sValue = call_user_func_array( array( $this, 'getValue' ), $aArgs );
		
		if ( $sValue ) return Zend_Json::decode( $sValue );
		
		return NULL;
	}
	
	// explode a line delimited meta value into an array
	public function getValueAsArray() {
		
		$aArgs = func_get_args();
		$sValue = call_user_func_array( array( $this, 'getValue' ), $aArgs );
		
		return Geko_Array::explodeTrimEmpty( "\n", $sValue );
	}
	
	
	
	
	
	//// plugin methods (should be a mix-in)
	
	// common with Geko_Entity, Geko_Entity_Query, Geko_Delegate
	
	//
	public function addPlugin( $sClassName, $mParams = NULL ) {
		
		Geko_Plugin::add( $sClassName, $mParams, $this, &$this->_aPlugins, 'setupEntity' );
		
		return $this;
	}
	
	//
	public function applyPluginFilter() {
		
		$aArgs = func_get_args();
		
		return Geko_Plugin::applyFilter( $aArgs, $this->_aPlugins );
	}
	
	//
	public function doPluginAction() {
		
		$aArgs = func_get_args();
		
		Geko_Plugin::doAction( $aArgs, $this->_aPlugins );
		
		return $this;
	}
	
	
	
	
	
	//// static methods
	
	// convenience factory method getting a single populated entity using the query class
	public static function getOne( $mParams = NULL, $bAddToDefaultParams = TRUE ) {
		
		$sEntityClass = get_called_class();
		$sQueryClass = Geko_Class::resolveRelatedClass(
			$sEntityClass, '', '_Query'
		);
		
		$aPosts = new $sQueryClass( $mParams, $bAddToDefaultParams );
		if ( $aPosts->count() > 0 ) {
			return $aPosts[ 0 ];
		}
		
		return new $sEntityClass();		
	}
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		
		
		// go through delegates
		
		if ( $fDelegate = Geko_Delegate::findMatch( $this->_aDelegates, $sMethod ) ) {
			return call_user_func_array( $fDelegate, $aArgs );
		}
		
		
		
		// check if __call is being invoked directly, to prevent possible infinite loops
		$bDirect = FALSE;
		if ( 0 === strpos( $sMethod, 'thisget' ) ) {
			$sMethod = substr_replace( $sMethod, 'get', 0, 7 );
			$bDirect = TRUE;
		}
		
		//
		if ( 0 === strpos( $sMethod, 'get' ) ) {
			
			
			// Auto date/time formatting
			if ( 0 === strpos( $sMethod, 'getDateTime' ) ) {
				
				$sPropName = sprintf( 'date_%s', Geko_Inflector::underscore( substr( $sMethod, 11 ) ) );
				
				if ( $this->hasEntityProperty( $sPropName ) ) {
					
					return $this->dateTimeFormat(
						$this->getEntityPropertyValue( $sPropName ),
						$aArgs[ 0 ]
					);
				}
				
			} elseif ( 0 === strpos( $sMethod, 'getTime' ) ) {
				
				$sPropName = sprintf( 'date_%s', Geko_Inflector::underscore( substr( $sMethod, 7 ) ) );

				if ( $this->hasEntityProperty( $sPropName ) ) {
					
					return $this->timeFormat(
						$this->getEntityPropertyValue( $sPropName ),
						$aArgs[ 0 ]
					);
				}
				
			} elseif ( 0 === strpos( $sMethod, 'getDate' ) ) {
				
				$sPropName = sprintf( 'date_%s', Geko_Inflector::underscore( substr( $sMethod, 7 ) ) );

				if ( $this->hasEntityProperty( $sPropName ) ) {
					
					return $this->dateFormat(
						$this->getEntityPropertyValue( $sPropName ),
						$aArgs[ 0 ]
					);
				}
				
			} elseif ( 0 === strpos( $sMethod, 'getBool' ) ) {			// format boolean to human-readable

				$sPropName = Geko_Inflector::underscore( substr( $sMethod, 7 ) );
				
				if ( $this->hasEntityProperty( $sPropName ) ) {
					
					return $this->boolFormat(
						$this->getEntityPropertyValue( $sPropName ),
						$aArgs[ 0 ], $aArgs[ 1 ]
					);
				}
				
			}
			
			
			// attempt to call get*() method if it exists (!)
			// this is to allow routing of direct invocation of __call()
			if ( $bDirect && method_exists( $this, $sMethod ) ) {
				return call_user_func_array( array( $this, $sMethod ), $aArgs );
			}
			
			// attempt to call ret*() method if it exists
			$sCall = substr_replace( $sMethod, 'ret', 0, 3 );
			if ( method_exists( $this, $sCall ) ) {
				return call_user_func_array( array( $this, $sCall ), $aArgs );
			}
						
			// attempt to call echo*() method if it exists
			$sCall = substr_replace( $sMethod, 'echo', 0, 3 );
			if ( method_exists( $this, $sCall ) ) {
				return Geko_String::fromOb( array( $this, $sCall ), $aArgs );
			}
			
			
			//// file/timestamp formatting
			
			$aRegs = array();
			
			if ( preg_match( '/^get([a-zA-Z0-9]+)(Url|Path|Size|Ts)$/', $sMethod, $aRegs ) ) {
				
				$sFormatSubject = $aRegs[ 1 ];
				$sFormatSuffix = $aRegs[ 2 ];
				
				if ( in_array( $sFormatSuffix, array( 'Url', 'Path', 'Size' ) ) ) {

					$sCall = sprintf( 'file%s%s', strtolower( $sFormatSuffix ), $sFormatSubject );
					return $this->__call( $sCall, $aArgs );
					
				} elseif ( 'Ts' == $sFormatSuffix ) {
					
					// format db timestamp to unix timestamp
					$sDbTimeStamp = $this->getEntityPropertyValue(
						Geko_Inflector::underscore( $sFormatSubject )
					);
					
					// if there are any digits between 1 to 9, then timestamp is not 0
					return ( preg_match( '/[1-9]+/', $sDbTimeStamp ) ) ? strtotime( $sDbTimeStamp ) : 0 ;
				}
				
			}
			
			
			// see if a corresponding entity value can be found
			$sEntityProperty = Geko_Inflector::underscore( substr( $sMethod, 3 ) );
			if ( $mValue = $this->getValue( $sEntityProperty ) ) {
				return $mValue;
			}
			
			
			// prevent exeception from being thrown
			if ( $this->_bAllowNonExistentEntities ) return NULL;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'echo' ) ) {
			
			// echo results from get*() method
			$sCall = substr_replace( $sMethod, 'thisget', 0, 4 );			
			echo strval( $this->__call( $sCall, $aArgs ) );
			
			return TRUE;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'escget' ) ) {
			
			// apply escapeHtml() method to result of get*()
			$sCall = substr_replace( $sMethod, 'thisget', 0, 6 );
			
			return $this->escapeHtml( $this->__call( $sCall, $aArgs ) );
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'escecho' ) ) {

			// apply escapeHtml() method to echo of get*()
			$sCall = substr_replace( $sMethod, 'thisget', 0, 7 );
			echo $this->escapeHtml( $this->__call( $sCall, $aArgs ) );
			
			return TRUE;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'fileurl' ) ) {
			
			// return the full URL, assuming entity property corresponds to a file
			$sProp = substr( $sMethod, 7 );
			$sCall = sprintf( 'thisget%s', $sProp );
			
			return $this->_getFileUrl( $this->__call( $sCall, $aArgs ), $sProp, $aArgs );
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'filepath' ) ) {
			
			// return the full URL, assuming entity property corresponds to a file
			$sProp = substr( $sMethod, 8 );
			$sCall = sprintf( 'thisget%s', $sProp );
			
			return $this->_getFilePath( $this->__call( $sCall, $aArgs ), $sProp, $aArgs );
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'filesize' ) ) {
			
			// return the full URL, assuming entity property corresponds to a file
			$sProp = substr( $sMethod, 8 );
			$sCall = sprintf( 'thisget%s', $sProp );
			
			return $this->_getFileSize( $this->__call( $sCall, $aArgs ), $sProp, $aArgs );
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'fileis' ) ) {
			
			// return the full URL, assuming entity property corresponds to a file
			$sProp = substr( $sMethod, 6 );
			$sCall = sprintf( 'thisget%s', $sProp );
			
			return $this->_getFileIs( $this->__call( $sCall, $aArgs ), $sProp, $aArgs );
			
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', $this->_sEntityClass, $sMethod ) );
	}
	
	//
	public function __toString() {
		return $this->getTitle();
	}
	
}



