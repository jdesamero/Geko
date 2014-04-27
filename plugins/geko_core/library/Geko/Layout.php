<?php

//
class Geko_Layout extends Geko_Singleton_Abstract
{
	protected $_sRenderer = 'Geko_Layout_Renderer';
	protected $_aPrefixes = array( 'Geko_' );
	
	protected $_aParams = array();
	
	protected $_bUnshift = FALSE;
	
	protected $_aMapMethods = array(
		
		'sw' => array( 'Geko_String', 'sprintfWrap' ),
		'pw' => array( 'Geko_String', 'printfWrap' ),
		'st' => array( 'Geko_String', 'truncate' ),
		'pt' => array( 'Geko_String', 'ptruncate' ),
		'sn' => 'number_format',
		'pn' => array( 'Geko_String', 'printNumberFormat' ),
		'tm' => array( 'Geko_String', 'mbTrim' ),
		
		'pf' => array( 'Geko_Html', 'populateForm' ),
		'is' => array( 'Geko_Match', 'is' )
		
	);
	
	protected $_aTemplates = array();
	
	
	
	
	
	//
	public function init( $bUnshift = FALSE ) {
		
		$this->_bUnshift = $bUnshift;
		
		
		// init best match static root class
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			
			$sClass = rtrim( $sPrefix, '_' );
			
			if ( class_exists( $sClass ) ) {
				
				$this->_aMapMethods = array_merge( $this->_aMapMethods, array(
					'regGet' => array( $sClass, 'get' ),
					'regVal' => array( $sClass, 'getVal' )
				) );
				
				break;
			}
		}
		
		
		return parent::init();
	}
	
	//
	public function reInit( $bUnshift = FALSE ) {
		
		return parent::reInit();
	}
	
	
	
	//// call hooks
	
	//
	public function preStart( $bUnshift = FALSE ) {
		
		$oRenderer = Geko_Singleton_Abstract::getInstance( $this->_sRenderer );
		
		if ( $this->_bUnshift ) {
			$oRenderer->addLayoutUnshift( $this );
		} else {
			$oRenderer->addLayout( $this );
		}	
	}
	
	
	
	//// my hooks
	
	public function end() { }					// call after rendering the layout stack
	
	
	
	// labels
	
	//
	public function _getLabel() {
		
		$aArgs = func_get_args();
		$iIdx = $aArgs[ 0 ];
		
		return $this->_aLabels[ $iIdx ];
	}
	
	//
	public function _getLabels() {
		return $this->_aLabels;
	}
	
	
	
	// HACK!!!
	public function getScriptUrls( $aOther = NULL ) {
		
		$oUrl = new Geko_Uri();
		
		$sCurPage = strval( $oUrl );
		
		$oUrl->unsetVars();
		$sCurPath = strval( $oUrl ); 

		$oUrl->setVar( 'ajax_content', 1 );
		$sAjaxContent = strval( $oUrl ); 
		
		$aRet = array(
			'url' => GEKO_STANDALONE_URL,
			'curpage' => $sCurPage,
			'curpath' => $sCurPath,
			'ajax_content' => $sAjaxContent,
			'srv' => Geko_Uri::getUrl( 'geko_app_srv' )
		);
		
		if ( is_array( $aOther ) ) {
			$aRet = array_merge( $aRet, $aOther );
		}
		
		return $aRet;
	}
	
	
		
	
	
	//// helpers
	
	//
	public function resolveClass( $sClass ) {
		
		$aClass = array( $sClass );
		
		foreach ( $this->_aPrefixes as $sPrefix ) {
			$aClass[] = sprintf( '%s%s', $sPrefix, $sClass );
		}
		
		return call_user_func_array( array( 'Geko_Class', 'existsCoalesce' ), $aClass );
	}
	
	//
	public function escapeHtml( $sValue ) {
		return htmlspecialchars( $sValue );
	}
	
	
	
	
	//// template routing methods
	
	// usage: addTemplate( <some template>, <grouping 1>, <grouping 2>, ... <n> )
	public function addTemplate() {
		
		$aArgs = func_get_args();
		
		$sTemplate = array_shift( $aArgs );
		
		$this->_aTemplates[ $sTemplate ] = $aArgs;
		
		return $this;
	}
	
	// usage: getTemplates( <grouping 1>, <grouping 2>, ... <n> )
	public function getTemplates() {

		$aArgs = func_get_args();
		$aRet = array();
		
		foreach ( $this->_aTemplates  as $sTemplate => $aGroup ) {
			$bMatch = TRUE;
			foreach ( $aArgs as $sMatchGroup ) {
				if ( !in_array( $sMatchGroup, $aGroup ) ) {
					$bMatch = FALSE;
					break;
				}
			}
			if ( $bMatch ) $aRet[] = $sTemplate;
		}
		
		return $aRet;
	}
	
	// same as getTemplates(), return imploded string
	public function getTemplateList() {
		$aArgs = func_get_args();
		$aRet = call_user_func_array( array( $this, 'getTemplates' ), $aArgs );
		return implode( '|', $aRet );
	}
	
	// convenience method
	public function isTemplateList() {
		$aArgs = func_get_args();
		return $this->is(
			call_user_func_array( array( $this, 'getTemplateList' ), $aArgs )
		);
	}
	
	//
	public function getTemplateGrouping() {
		foreach ( $this->_aTemplates as $sTemplate => $aGroup ) {
			if ( $this->is( $sTemplate ) ) {
				return $aGroup;
			}
		}
		return array();
	}
	
	
	
	
	
	//// render tags
	
	//
	public function getEnqueueScriptCb() {
		return array( Geko_Loader_ExternalFiles::getInstance(), 'enqueueScript' );
	}
	
	//
	public function enqueueScript() {
		
		$aArgs = func_get_args();
		
		foreach ( $aArgs as $sId ) {
			call_user_func( $this->getEnqueueScriptCb(), $sId );
		}
		
		return $this;
	}

	//
	public function getEnqueueStyleCb() {
		return array( Geko_Loader_ExternalFiles::getInstance(), 'enqueueStyle' );
	}
	
	//
	public function enqueueStyle() {

		$aArgs = func_get_args();

		foreach ( $aArgs as $sId ) {
			call_user_func( $this->getEnqueueStyleCb(), $sId );
		}
		
		return $this;
	}
	
	//
	public function renderStyleTags() {
		Geko_Loader_ExternalFiles::getInstance()->renderStyleTags();
		return $this;		
	}
	
	//
	public function renderScriptTags() {
		Geko_Loader_ExternalFiles::getInstance()->renderScriptTags();
		return $this;		
	}
	
	
	
	
	//// ajax content methods
	
	//
	public function echoAjaxContent() {
		
		if ( $sData = $GLOBALS[ 'HTTP_RAW_POST_DATA' ] ) {
			try {
				$_POST = $_REQUEST = Zend_Json::decode( $sData );
			} catch ( Exception $e ) { }
		}
		
		$aAjaxResponse = NULL;
		
		$sSection = trim( $_GET[ 'section' ] );
		$sMethod = '';
		
		// check for matching method
		if (
			( $sSection ) && 
			( $sMethod = sprintf( 'get%sAjax', Geko_Inflector::camelize( $sSection ) ) ) && 
			( method_exists( $this, $sMethod ) )
		) {
			$aAjaxResponse = $this->$sMethod();		
		}
		
		// check for callable handlers
		if ( $sSection && !$aAjaxResponse ) {
			$aAjaxResponse = $this->getCallableResult( 'ajax_content', $sSection );
		}
		
		if ( $aAjaxResponse ) {
			echo Zend_Json::encode( $aAjaxResponse );		
		}
	}
	
	
	
	
	//// callable result
	
	//
	public function getCallableResult( $sCaller, $sSection ) {
		
		$aRes = NULL;
		
		if ( $aHandlers = $this->_aCallableHandlers[ $sCaller ] ) {			
			foreach ( $aHandlers as $sHandler => $mParams ) {
				
				if ( is_array( $mParams ) ) {
					$sHandlerMethod = $mParams[ 'handler' ];
				}
				
				if ( !$sHandlerMethod ) {
					$sHandlerMethod = sprintf( 'call%s', Geko_Inflector::camelize( $sHandler ) );
				}
				
				$aParams = $this->_aCallables[ $sHandler ][ $sSection ];
				
				if (
					( $aParams ) && 
					( method_exists( $this, $sHandlerMethod ) )
				) {
					
					$aParams = array_merge( $aParams, array(
						'__caller' => $sCaller,
						'__section' => $sSection
					) );
					
					$aThisRes = $this->$sHandlerMethod( $aParams );
					
					if ( is_array( $aThisRes ) ) {
						if ( is_array( $aRes ) ) {
							$aRes = array_merge( $aRes, $aThisRes );
						} else {
							$aRes = $aThisRes;
						}
					}
					
				}
			}
		}
		
		return $aRes;
	}
	
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( array_key_exists( $sMethod, $this->_aMapMethods ) ) {
			
			return call_user_func_array(
				$this->_aMapMethods[ $sMethod ],
				$aArgs
			);
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'do' ) ) {
			
			call_user_func_array(
				array(
					Geko_Singleton_Abstract::getInstance( $this->_sRenderer ),
					$sMethod
				),
				$aArgs
			);
			
			return TRUE;

		} elseif ( 0 === strpos( strtolower( $sMethod ), 'ob' ) ) {
			
			// send as do*() to renderer
			$sCall = substr_replace( $sMethod, 'do', 0, 2 );
			
			ob_start();
			call_user_func_array(
				array(
					Geko_Singleton_Abstract::getInstance( $this->_sRenderer ),
					$sCall
				),
				$aArgs
			);
			$sRes = ob_get_contents();
			ob_end_clean();
			
			return $sRes;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'apply' ) ) {
			
			return call_user_func_array(
				array(
					Geko_Singleton_Abstract::getInstance( $this->_sRenderer ),
					$sMethod
				),
				$aArgs
			);
			
		} elseif ( 0 === strpos( $sMethod, 'get' ) ) {
			
			// attempt to call echo*() method if it exists
			$sCall = substr_replace( $sMethod, 'echo', 0, 3 );
			
			if ( method_exists( $this, $sCall ) ) {
				
				ob_start();
				call_user_func_array( array( $this, $sCall ), $aArgs );				
				$sRes = ob_get_contents();
				ob_end_clean();
				
				return $sRes;
				
			} else {

				$sParamKey = Geko_Inflector::underscore(
					substr_replace( $sMethod, '', 0, 3 )
				);
				
				if ( isset( $this->_aParams[ $sParamKey ] ) ) {
					return $this->_aParams[ $sParamKey ];
				} else {
					return NULL;
				}
				
			}
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'echo' ) ) {
			
			// echo results from get*() method if it exists
			$sCall = substr_replace( $sMethod, 'get', 0, 4 );
			
			if ( method_exists( $this, $sCall ) ) {
				
				$mRes = call_user_func_array( array( $this, $sCall ), $aArgs );
				echo strval( $mRes );
				return TRUE;
			
			} else {
				
				$sParamKey = Geko_Inflector::underscore(
					substr_replace( $sMethod, '', 0, 3 )
				);
				
				if ( isset( $this->_aParams[ $sParamKey ] ) ) {
					echo strval( $this->_aParams[ $sParamKey ] );
					return TRUE;
				} else {
					return FALSE;
				}
				
			}
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'set' ) ) {
			
			$sParamKey = Geko_Inflector::underscore(
				substr_replace( $sMethod, '', 0, 3 )
			);
			
			$this->_aParams[ $sParamKey ] = $aArgs[ 0 ];
			return $this;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'new' ) ) {
			
			$sClass = substr_replace( $sMethod, '', 0, 3 );
			
			if ( $sClass = $this->resolveClass( $sClass ) ) {
				$oReflect = new ReflectionClass( $sClass );
				return $oReflect->newInstanceArgs( $aArgs );
			}
			
			return NULL;
			
		} elseif ( 0 === strpos( strtolower( $sMethod ), 'one' ) ) {
			
			$sClass = substr_replace( $sMethod, '', 0, 3 );
			
			if ( $sClass = $this->resolveClass( $sClass ) ) {
				$oReflect = new ReflectionClass( $sClass );
				$oQuery = $oReflect->newInstanceArgs( $aArgs );
				if ( $oQuery instanceof Geko_Entity_Query ) {
					return $oQuery->getOne();
				}
			}
			
			return NULL;
			
		} elseif ( 0 === strpos( $sMethod, 'l_' ) ) {
			
			$iIdx = str_replace( 'l_', '', $sMethod );
			array_unshift( $aArgs, $iIdx );			// prepend
			
			return call_user_func_array( array( $this, '_getLabel' ), $aArgs );
			
		} elseif ( 0 === strpos( $sMethod, 'e_' ) ) {
			
			$iIdx = str_replace( 'e_', '', $sMethod );
			array_unshift( $aArgs, $iIdx );			// prepend
			
			echo call_user_func_array( array( $this, '_getLabel' ), $aArgs );
			
			return NULL;
			
		}

		// TO DO: add mechanism for "layout helpers"
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', __CLASS__, $sMethod ) );
	}
	
}


