<?php

class Geko_Navigation_Renderer
{
	//
	public static function getView() {
		
		static $oView;
		
		if ( !is_object( $oView ) ) {
			
			$oView = new Zend_View();
			
			if ( defined( 'GEKO_VIEW_HELPER_PATH' ) ) {
				$oView->addHelperPath( sprintf( '%s/Geko/View/Helper', GEKO_VIEW_HELPER_PATH ), 'Geko_View_Helper' );
			}			
		}
		
		return $oView;
	}
	
	//
	public static function getParamKeys() {
		
		static $aParamKeys = NULL;
		
		if ( NULL == $aParamKeys ) {
			
			$aParamKeys = Geko_Array::createNormalizedHashFromKeys(
				array(
					
					// Zend_View_Helper_Navigation_Menu accepted params
					'indent',
					'ulClass',
					'minDepth',
					'maxDepth',
					'onlyActiveBranch',
					'renderParents',
					
					// Geko_View_Helper_Navigation_Menu accepted params
					'renderDepth',
					'renderRelevantOnly',
					'renderDescendants',
					'liClass',
					'defaultMenuTemplate',
					'menuTemplates',
					
					// __CLASS__ params
					'forceUlTag',
					'ulId',
					'wpCompatibility',
					'stripSpaces',
					'style',			// simpleDelimitered | slidingDoors
					
					// if style == simpleDelimitered
					'delimiter'		
					
				)
			);
			
		}
		
		return $aParamKeys;
	}
	
	//
	public static function menu( $oNav, $aParams = array() ) {
		
		$oNav->_geko_params = $aParams;
		
		//
		$oMenu = self::getView()
			->navigation()
			->menu( $oNav )
		;
		
		// normalize param keys
		$aParams = Geko_Array::normalizeParams( $aParams, self::getParamKeys() );
		
		// set defaults
		if ( FALSE == isset( $aParams[ 'wpCompatibility' ] ) ) {
			$aParams[ 'wpCompatibility' ] = TRUE;
		}
		
		// unshift params
		$bForceUlTag = self::shiftParam( $aParams, 'forceUlTag' );
		$sUlId = self::shiftParam( $aParams, 'ulId' );	
		$bWpCompatibility = self::shiftParam( $aParams, 'wpCompatibility' );		
		$bStripSpaces = self::shiftParam( $aParams, 'stripSpaces' );
		$sStyle = strtolower( self::shiftParam( $aParams, 'style' ) );
		
		if ( !isset( $aParams[ 'delimiter' ] ) ) {
			$sDelimiter = '&amp;nbsp;|&amp;nbsp;';
		} else {
			$sDelimiter = self::shiftParam( $aParams, 'delimiter' );
		}
		
		
		// params accepted by $oMenu:
		//    renderDepth (int)
		//    renderRelevantOnly (bool)
		//    renderDescendants (bool)
		//    liClass (string)
		
		
		if ( count( $aParams ) > 0 ) {
			$sOutput = trim( strval( $oMenu->renderMenu( NULL, $aParams ) ) );
		} else {
			$sOutput = trim( strval( $oMenu ) );
		}
				
		if ( $bForceUlTag && !$sOutput ) {
			$sOutput = '<ul></ul>';
		}
		
		if ( $sOutput ) {

			//// manipulate with phpQuery
			
			$oDoc = phpQuery::newDocument( $sOutput );
			$oDoc[ 'li.active > a' ]->addClass('active');
			
			$oDoc[ 'li:first-child' ]->addClass('first-child');
			$oDoc[ 'li:last-child' ]->addClass('last-child');
			
			$oDoc[ 'li > ul' ]->parent()->addClass('has-child');
			
			if ( $sUlId ) {
				$oDoc[ 'ul' ]->attr( 'id', $sUlId );
			}
			
			if ( $bWpCompatibility ) {
				$oDoc[ 'li.active > a' ]->addClass('current_item');
				$oDoc[ 'li.active' ]->addClass('current_page_item');
			}
			
			if ( isset( $aParams[ 'renderDepth' ] ) && ( $aParams[ 'renderDepth' ] > 0 ) ) {
				$oDoc[ 'ul' ]->addClass('navigation');
			}
			
			if ( 'simpledelimitered' == $sStyle ) {
				
				$aLinks = array();
				$aPqAs = $oDoc[ 'a' ];
				$iLastIdx = count( $aPqAs ) - 1;
				
				foreach ( $aPqAs as $i => $a ) {
					
					$oPqA = pq( $a );
					if ( $sParentClass = trim( $oPqA->parent()->attr( 'class' ) ) ) {
						$oPqA->addClass( $sParentClass );
					}
					
					$oPqA->removeClass( 'has-child' );
					$oPqA->removeClass( 'first-child' );
					$oPqA->removeClass( 'last-child' );
					
					if ( 0 == $i ) $oPqA->addClass( 'first-child' );
					if ( $iLastIdx == $i ) $oPqA->addClass( 'last-child' );
					
					$aLinks[] = strval( $oPqA );
				}
				
				$oDoc = phpQuery::newDocument( implode( $sDelimiter, $aLinks ) );
				
			} elseif ( 'slidingdoors' == $sStyle ) {
				
				foreach ( $oDoc[ 'a' ] as $a ) {
					$oPqA = pq( $a );
					$sHtml = $oPqA->html();
					$oPqA->html( sprintf( '<span>%s</span>', $sHtml ) );
				}
				
			}
			
			//// TO DO: add hooks to $oDoc
			
			//// convert back to string
			$sOutput = html_entity_decode( strval( $oDoc ) );
			
			if ( $bStripSpaces ) {
				$sOutput = preg_replace( '/>\s*</', '><', $sOutput );
			}
			
			return $sOutput;
		}
		
	}
	
	//
	public static function breadcrumbs( $oNav, $aParams = array() ) {
		
		$oNav->_geko_params = $aParams;
		
		$oBreadcrumb = self::getView()
			->navigation()
			->breadcrumbs( $oNav )
		;
		
		if ( $aParams[ 'separator' ] ) {
			$oBreadcrumb->setSeparator( $aParams[ 'separator' ] );
		}
		
		if ( $aParams[ 'linkLast' ] ) {
			$oBreadcrumb->setLinkLast( $aParams[ 'linkLast' ] );
		}
		
		$sOutput = trim( strval( $oBreadcrumb ) );
		
		if ( !$sOutput && $aParams[ 'showRoot' ] ) {
			
			foreach ( $oNav as $oItem ) {
				
				if ( $oItem->isActive() ) {
					
					$sClass = 'breadcrumb_root';
					
					if ( ( method_exists( $oItem, 'getCssClass' ) ) && ( $sNavClass = $oItem->getCssClass() ) ) {
						$sClass .= sprintf( ' %s', $sNavClass );
					}
					
					$sOutput = sprintf( '<span class="%s">%s</span>', $sClass, strval( $oItem ) );
					
					break;
				}
			}
		}
		
		return $sOutput;
	}
	
	//
	public static function classChain( $oNav, $aParams = array() ) {
		
		$oNav->_geko_params = $aParams;
		
		return trim( self::classChainIterate( $oNav, $aParams ) );
	}
	
	//
	public static function classChainIterate( $oNav, $aParams = array() ) {
		
		$sOut = '';
		foreach ( $oNav as $oItem ) {
			
			if ( $oItem->isActive( TRUE ) ) {
				
				$sOut .= $oItem->getCssClass();
				
				if ( $oItem->hasChildren() ) {
					$sOut .= sprintf( ' %s', self::classChainIterate( $oNav->getChildren(), $aParams ) );
				}
				
				break;
			}
			
		}
		
		return $sOut;
	}
	
	//
	public static function getActiveDepth( $oNav, $aParams = array() ) {
		
		$oNav->_geko_params = $aParams;
		
		$oIterator = new RecursiveIteratorIterator(
			$oNav, RecursiveIteratorIterator::SELF_FIRST
		);
		
		foreach ( $oIterator as $oPage ) {
			if ( $oPage->isActive( FALSE ) ) {
				return $oIterator->getDepth();
			}
		}
		
		return NULL;
	}
	
	//
	public static function getActiveParent( $oNav, $aParams = array() ) {
		
		$oNav->_geko_params = $aParams;
		
		$oIterator = new RecursiveIteratorIterator(
			$oNav, RecursiveIteratorIterator::SELF_FIRST
		);
		
		foreach ( $oIterator as $oPage ) {
			if ( $oPage->isActive( FALSE ) ) {
				return $oPage->getParent();
			}
		}
		
		return NULL;
	}
	
	//
	protected static function shiftParam( &$aParams, $sKey ) {
		
		if ( isset( $aParams[ $sKey ] ) ) {
			$mRet = $aParams[ $sKey ];
			unset( $aParams[ $sKey ] );
			return $mRet;
		} else {
			return NULL;
		}
		
	}
	
	//
	public static function filterNavParams( $aNavParams ) {
		
		$aFiltered = array();
		
		foreach ( $aNavParams as $i => $aNavItem ) {
			
			if ( isset( $aNavItem[ 'type' ] ) ) {
				
				if ( !Geko_Class::isSubclassOf( $aNavItem[ 'type' ], 'Zend_Navigation_Page' ) ) {
					$aNavItem[ 'type' ] = 'Zend_Navigation_Page_Uri';	
				}
				
				if ( isset( $aNavItem[ 'pages' ] ) ) {
					$aNavItem[ 'pages' ] = self::filterNavParams( $aNavItem[ 'pages' ] );
				}
				
				$aFiltered[] = $aNavItem;
			}
		}
		
		return $aFiltered;
	}
	
}



