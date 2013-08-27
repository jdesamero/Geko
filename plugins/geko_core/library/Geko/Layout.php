<?php

//
class Geko_Layout extends Geko_Singleton_Abstract
{
	protected $_sRenderer = 'Geko_Layout_Renderer';
	protected $_aParams = array();
	
	protected $_aMapMethods = array(
		
		'sw' => array( 'Geko_String', 'sprintfWrap' ),
		'pw' => array( 'Geko_String', 'printfWrap' ),
		'st' => array( 'Geko_String', 'truncate' ),
		'pt' => array( 'Geko_String', 'ptruncate' ),
		'sn' => 'number_format',
		'pn' => array( 'Geko_String', 'printNumberFormat' ),
		'tm' => array( 'Geko_String', 'mbTrim' ),
		
		'pf' => array( 'Geko_Html', 'populateForm' )
		
	);
	
	protected $_bIntrospect = FALSE;
	protected $_aExcludeFromIntrospection = array();
	
	//
	public function init( $bUnshift = FALSE ) {
		
		$oRenderer = Geko_Singleton_Abstract::getInstance( $this->_sRenderer );
		
		if ( $bUnshift ) {
			$oRenderer->addLayoutUnshift( $this );
		} else {
			$oRenderer->addLayout( $this );
		}
		
		return $this;
	}
	
	
	
	// labels
	
	//
	public function _getLabel( $iIdx ) {
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
			'ajax_content' => $sAjaxContent
		);
		
		if ( is_array( $aOther ) ) {
			$aRet = array_merge( $aRet, $aOther );
		}
		
		return $aRet;
	}
	
	
	
	// used to trigger methods implemented by concrete classes to determine
	// "constant" values triggered by certain functions so certain admin
	// functionality can be automated
	// DEPRACATED: Can break things easily when called
	public function introspect() {
		
		// set introspect mode
		$this->_bIntrospect = TRUE;
		
		$sClass = get_class( $this );
		
		$oReflect = new ReflectionClass( $sClass );
		
		$aMethods = $oReflect->getMethods();
		
		foreach ( $aMethods as $oMethod ) {
			if (
				( $oMethod->isPublic() ) && 
				( 0 == $oMethod->getNumberOfRequiredParameters() ) && 
				( $sMethod = $oMethod->getName() ) && 
				( $sClass == $oMethod->getDeclaringClass()->getName() ) && 
				( !in_array( $sMethod, $this->_aExcludeFromIntrospection ) )
			) {
				// invoke method to trigger introspection
				ob_start();
				$oMethod->invoke( $this );
				ob_end_clean();
			}
		}
		
		// unset introspect mode
		$this->_bIntrospect = FALSE;
		
	}
	
	
	//// helpers
	
	//
	public function resolveClass( $sClass ) {
		return Geko_Class::existsCoalesce( $sClass, 'Geko_' . $sClass );
	}
	
	//
	public function escapeHtml( $sValue ) {
		return htmlspecialchars( $sValue );
	}
	
	
	
	
	//// render tags
	
	//
	public function enqueueScript() {
		
		$aArgs = func_get_args();
		
		foreach ( $aArgs as $sId ) {
			Geko_Loader_ExternalFiles::getInstance()->enqueueScript( $sId );
		}
		
		return $this;
	}
	
	//
	public function enqueueStyle() {

		$aArgs = func_get_args();

		foreach ( $aArgs as $sId ) {
			Geko_Loader_ExternalFiles::getInstance()->enqueueStyle( $sId );		
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
		
		$sSection = trim( $_GET[ 'section' ] );
		$sMethod = '';
		
		if ( $sSection ) {
			$sMethod = sprintf( 'get%sAjax', Geko_Inflector::camelize( $sSection ) );
			if ( !method_exists( $this, $sMethod ) ) $sMethod = '';
		}
		
		if ( $sMethod ) {
			$aAjaxResponse = $this->$sMethod();
			echo Zend_Json::encode( $aAjaxResponse );
		}
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
			return $this->_getLabel( $iIdx );
			
		} elseif ( 0 === strpos( $sMethod, 'e_' ) ) {
			
			$iIdx = str_replace( 'e_', '', $sMethod );
			echo $this->_getLabel( $iIdx );
			return NULL;
			
		}

		// TO DO: add mechanism for "layout helpers"
		
		throw new Exception( 'Invalid method ' . __CLASS__ . '::' . $sMethod . '() called.' );
	}
	
}


