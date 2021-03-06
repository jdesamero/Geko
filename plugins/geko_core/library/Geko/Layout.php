<?php
/*
 * "geko_core/library/Geko/Layout.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

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
	
	protected $_mBodyClass = NULL;
	protected $_mStyles = NULL;
	protected $_mScripts = NULL;
		
	protected $_aTemplates = array();
	
	protected $_sAjaxSection = NULL;
	
	
	
	
	
	//
	public function init( $bUnshift = FALSE ) {
		
		$this->_bUnshift = $bUnshift;
		
		return parent::init();
	}
	
	//
	public function reInit( $bUnshift = FALSE ) {
		
		return parent::reInit();
	}
	
	
	
	//// call hooks
	
	//
	public function preStart( $bUnshift = FALSE ) {
		
		
		//// init best match static root class
		
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
		
		
		//// set up the renderer class
		
		$oRenderer = Geko_Singleton_Abstract::getInstance( $this->_sRenderer );
		
		if ( $this->_bUnshift ) {
			$oRenderer->addLayoutUnshift( $this );
		} else {
			$oRenderer->addLayout( $this );
		}
		
	}
	
	
	// implement hook method
	public function start() {
		
		parent::start();
		
		Geko_Http_Var::formatHttpRawPostData();
		
		$oRenderer = Geko_Singleton_Abstract::getInstance( $this->_sRenderer );
		
		if ( $oRenderer->isAjaxContent() ) {
			$this->resolveAjaxSection();
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
	
	
	
	//
	public function getScriptUrls( $aOther = NULL ) {
		
		// do nothing for now
		return $aOther;
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
	
	//
	public function redirect( $sUrl ) {
		
		if ( $sUrl ) {
			header( sprintf( 'Location: %s', $sUrl ) );
			die();
		}
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
		
		foreach ( $this->_aTemplates as $sTemplate => $aGroup ) {
			
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
	
	
	
	
	
	//// layout parts
	
	//
	public function echoEnqueue() {
		
		if ( NULL !== $this->_mStyles ) {
			
			if ( is_string( $this->_mStyles ) ) {
				$aStyles = Geko_Array::explodeTrimEmpty( ' ', $this->_mStyles );
			} else {
				$aStyles = $this->_mStyles;
			}
			
			call_user_func_array( array( $this, 'enqueueStyle' ), $aStyles );
		}
		
		if ( NULL !== $this->_mScripts ) {
			
			if ( is_string( $this->_mScripts ) ) {
				$aScripts = Geko_Array::explodeTrimEmpty( ' ', $this->_mScripts );
			} else {
				$aScripts = $this->_mScripts;
			}
			
			call_user_func_array( array( $this, 'enqueueScript' ), $aScripts );
		}
		
		if ( is_array( $this->_aTemplates ) ) {
			
			// rework array
			$aTemplates = $this->_aTemplates;
			$this->_aTemplates = array();			// reset
			
			foreach ( $aTemplates as $mKey => $mTmpl ) {
				
				if ( is_string( $mTmpl ) ) {
					
					$aTmpl = Geko_Array::explodeTrimEmpty( ' ', $mTmpl );
				
				} elseif ( is_array( $mTmpl ) ) {
					
					array_unshift( $mTmpl, $mKey );
					$aTmpl = $mTmpl;
				}
				
				// this populates $this->_aTemplates
				call_user_func_array( array( $this, 'addTemplate' ), $aTmpl );				
			}
			
		}
	}
	
	// body class
	public function filterBodyClass( $sBodyClass ) {
		
		if ( NULL !== $this->_mBodyClass ) {
			
			if ( is_array( $this->_mBodyClass ) ) {
				$sMergeBodyClass = implode( ' ', $this->_mBodyClass );
			} else {
				$sMergeBodyClass = $this->_mBodyClass;		
			}
			
			
			if ( FALSE !== strpos( $sMergeBodyClass, '##body_class##' ) ) {
				$sMergeBodyClass = str_replace( '##body_class##', $this->getBodyClassCb(), $sMergeBodyClass );
			}
			
			if ( FALSE !== strpos( $sMergeBodyClass, '##browser_detect##' ) ) {
				$sMergeBodyClass = str_replace( '##browser_detect##', Geko_Browser::bodyClass(), $sMergeBodyClass );
			}
			
			if ( FALSE !== strpos( $sMergeBodyClass, '##template_grouping##' ) ) {
				$sMergeBodyClass = str_replace( '##template_grouping##', implode( ' ', $this->getTemplateGrouping() ), $sMergeBodyClass );
			}
			
			$sMergeBodyClass = $this->modifyMergeBodyClass( $sMergeBodyClass );
			
			$sBodyClass = trim( sprintf( '%s %s', trim( $sBodyClass ), trim( $sMergeBodyClass ) ) );
		
		}
		
		return $sBodyClass;
	}
	
	// hook method
	public function modifyMergeBodyClass( $sMergeBodyClass ) {
		return $sMergeBodyClass;
	}
	
	
	// to be implemented by sub-class
	public function getBodyClassCb() {
		return '';
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
		
		$aAjaxResponse = NULL;
		
		$sSection = $this->_sAjaxSection;
		$sMethod = '';
		
		// check for matching method
		if (
			( $sSection ) && 
			( $sMethod = $this->resolveAjaxSectionMethod( $sSection ) ) && 
			( method_exists( $this, $sMethod ) )
		) {
			$aAjaxResponse = $this->$sMethod();		
		}
		
		// check for callable handlers
		if ( $sSection && !$aAjaxResponse ) {
			$aAjaxResponse = $this->getCallableResult( 'ajax_content', $sSection );
		}
		
		if ( $aAjaxResponse ) {
			echo Geko_Json::encode( $aAjaxResponse );		
		}
	}
	
	//
	public function resolveAjaxSectionMethod( $sSection ) {
		
		return sprintf( 'get%sAjax', Geko_Inflector::camelize( $sSection ) );
	}
	
	
	//
	public function resolveAjaxSection() {
		
		if ( NULL === $this->_sAjaxSection ) {
			
			$this->_sAjaxSection = trim( $_REQUEST[ 'section' ] );
		}
		
		return $this->_sAjaxSection;
	}
	
	
	//
	public function getAjaxSection() {
		
		return $this->_sAjaxSection;
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
		
		} elseif ( $sCreateType = Geko_Class::callCreateType( $sMethod ) ) {
			
			return Geko_Class::callCreateInstance( $sCreateType, $sMethod, $aArgs, $this->_aPrefixes );
			
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


