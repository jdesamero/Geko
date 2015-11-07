<?php

// used against Zend 1.8.0

class Geko_View_Helper_Navigation_Menu extends Zend_View_Helper_Navigation_Menu
{
	
	
	protected $_renderDepth = NULL;
	protected $_renderRelevantOnly = FALSE;
	protected $_renderDescendants = FALSE;
	protected $_liClass;
	protected $_defaultMenuTemplate;
	protected $_menuTemplates = array();
	
	protected $_options = array();
	
	
	
	//
	protected function setRenderDepth( $iDepth = 0 ) {
		$this->_renderDepth = $iDepth;
		return $this;
	}

	//
	protected function getRenderDepth() {
		return $this->_renderDepth;
	}
	
	
	
	//
	protected function setRenderRelevantOnly( $bFlag = TRUE ) {
		$this->_renderRelevantOnly = (bool) $bFlag;
		return $this;
	}

	//
	protected function getRenderRelevantOnly() {
		return $this->_renderRelevantOnly;
	}
	
	
	
	//
	protected function setRenderDescendants( $bFlag = TRUE ) {
		$this->_renderDescendants = (bool) $bFlag;
		return $this;
	}

	//
	protected function getRenderDescendants() {
		return $this->_renderDescendants;
	}
	
	
	
	//
	protected function setLiClass( $sLiClass ) {
		$this->_liClass = $sLiClass;
		return $this;
	}

	//
	protected function getLiClass() {
		return $this->_liClass;
	}
    
    
    
    // $mDefaultMenuTemplate can be class or object instance
    protected function setDefaultMenuTemplate( $mDefaultMenuTemplate ) {
        $this->_defaultMenuTemplate = $mDefaultMenuTemplate;
        return $this;
    }
    
    //
    protected function getDefaultMenuTemplate() {
        return $this->_defaultMenuTemplate;
    }
    
    
    
    //
    protected function setMenuTemplates( $aMenuTemplates ) {
        $this->_menuTemplates = $aMenuTemplates;
        return $this;
    }
    
    //
    protected function getMenuTemplates() {
        return $this->_menuTemplates;
    }
    
    
    
    
	//
	protected function inActiveBranch( Zend_Navigation_Page $oPage, $iDepthCheck = 0 ) {
		
		if ( FALSE == $oPage->isActive( TRUE ) ) {
			
			// check parent
			if ( ( $oParent = $oPage->getParent() ) instanceof Zend_Navigation_Page ) {
				
				if ( 0 == $iDepthCheck ) {
					// stop checking
					return $oParent->isActive(TRUE);
				} else {
					// recursive
					return self::inActiveBranch( $oParent, $iDepthCheck-- );	// decrement
				}
				
			} else {
				return FALSE;
			}
			
		} else {
			return TRUE;
		}
	}
	
	
	
	//
	protected function _renderMenu(
		Zend_Navigation_Container $oContainer, $sUlClass, $sIndent, $sInnerIndent, $iMinDepth, $iMaxDepth,
		$bOnlyActive, $bExpandSibs, $sUlId, $bAddPageClassToLi, $sActiveClass, $sParentClass, $bRenderParentClass
	) {
		
		$sHtml = '';
		
		// find deepest active
		if ( $aFound = $this->findActive( $oContainer, $iMinDepth, $iMaxDepth ) ) {
			$oFoundPage = $aFound[ 'page' ];
			$iFoundDepth = $aFound[ 'depth' ];
		} else {
			$oFoundPage = NULL;
		}
		
		
		// create iterator
		$oIterator = new RecursiveIteratorIterator( $oContainer, RecursiveIteratorIterator::SELF_FIRST );
		
		if ( is_int( $iMaxDepth ) ) {
			$oIterator->setMaxDepth( $iMaxDepth );
		}
		
		
		
		$aOptions = $this->_options;
		
		
		
		// iterate container
		
		$oTemplateStack = new Geko_Navigation_Renderer_TemplateStack( $this, $aOptions[ 'defaultMenuTemplate' ] );
		$oTemplateStack->setTemplates( $aOptions[ 'menuTemplates' ] );
		
		$iPrevDepth = -1;
		
		
		//
		foreach ( $oIterator as $oPage ) {
			
			$iDepth = $oIterator->getDepth();
			$bIsActive = $oPage->isActive( TRUE );
			
			if (
				(
					( $oPage instanceof Geko_Navigation_Page ) || 
					( $oPage instanceof Geko_Navigation_Page_Uri )
				) &&
				( $oPage->getHide() )
			) {
				continue;
			}
			
			
			if ( $iDepth < $iMinDepth || !$this->accept( $oPage ) ) {
				
				// page is below minDepth or not accepted by acl/visibilty
				continue;
				
			} else if ( $bExpandSibs && $iDepth > $iMinDepth ) {
				
				// page is not active itself, but might be in the active branch
				
				$bAccept = FALSE;
				
				if ( $oFoundPage ) {
					
					if ( $oFoundPage->hasPage( $oPage ) ) {
						
						// accept if page is a direct child of the active page
						$bAccept = TRUE;
						
					} else if ( $oPage->getParent()->isActive( TRUE ) ) {
						
						// page is a sibling of the active branch...
						$bAccept = TRUE;
					}
				}
				
				if ( !$bIsActive && !$bAccept ) {
					continue;
				}
				
			} else if ( $bOnlyActive && !$bIsActive ) {
				
				// page is not active itself, but might be in the active branch
				$bAccept = FALSE;
				
				if ( $oFoundPage ) {
					
					if ( $oFoundPage->hasPage( $oPage ) ) {
						
						// accept if page is a direct child of the active page
						$bAccept = TRUE;
						
					} else if ( $oFoundPage->getParent()->hasPage( $oPage ) ) {
						
						// page is a sibling of the active page...
						if (
							( !$oFoundPage->hasPages() ) || (
								( is_int( $iMaxDepth ) ) && 
								( ( $iFoundDepth + 1 ) > $iMaxDepth )
							)
						) {
							// accept if active page has no children, or the
							// children are too deep to be rendered
							$bAccept = TRUE;
						}
					}
				}
				
				if ( !$bAccept ) {
					continue;
				}
			}
			
			
			
			
			$oParent = $oPage->getParent();
			
			
			// rendering depth was specified
			if ( NULL !== $aOptions[ 'renderDepth' ] ) {		
				
				if ( $aOptions[ 'renderDescendants' ] ) {
					
					// render specified menu if active and all its descendants
					// $iDepthCheck ensures we only check up to the current depth, otherwise we trigger active on unwanted items
					
					if ( $aOptions[ 'renderDepth' ] > $iDepth ) {
						$iDepthCheck = $iDepth - $aOptions[ 'renderDepth' ];
					} else {
						$iDepthCheck = 0;
					}
					
					if (
						( $iDepth < $aOptions[ 'renderDepth' ] ) ||
						( !self::inActiveBranch( $oPage, $iDepthCheck ) )
					) {
						continue;
					}
					
				} else {
					
					// only render the specified depth (for stratified menus)
					if (
						( $aOptions[ 'renderDepth' ] != $iDepth ) || (
							( FALSE == ( $oParent instanceof Zend_Navigation ) ) &&
							( FALSE == $oParent->isActive( TRUE ) )
						)					
					) {
						continue;
					}
					
				}
				
			}
			
			if (
				( $aOptions[ 'renderRelevantOnly' ] ) && 
				( FALSE == ( $oParent instanceof Zend_Navigation ) ) &&
				( FALSE == $oParent->isActive( TRUE ) )
			) {
				continue;
			}
			
			
			$sUlClassRep = str_replace( '##depth##', $iDepth, $sUlClass );
			$sLiClassRep = str_replace( '##depth##', $iDepth, $aOptions[ 'liClass' ] );
			
			
			
			// make sure indentation is correct
			$iDepth -= $iMinDepth;
			$sMyIndent = sprintf( '%s%s', $sIndent, str_repeat( $sInnerIndent, $iDepth * 2 ) );
			
			
			$oCurTemplate = $oTemplateStack->get( $iDepth );
			
			
			if ( $iDepth > $iPrevDepth ) {
				
				$aAttribs = array();
				
				// start new ul tag
				if ( 0 == $iDepth ) {
					$aAttribs = array(
						'class' => $sUlClass,
						'id' => $sUlId
					);
				}
				
				// We don't need a prefix for the menu ID (backup)
				$bSkipValue = $this->_skipPrefixForId;
				$this->skipPrefixForId();

				// $sHtml .= sprintf( '%s<ul%s>%s', $sMyIndent, $this->_htmlAttribs( $aAttribs ), $this->getEOL() );
				
				// start new container
				$sHtml .= $oCurTemplate->containerStart( array(
					'depth' => $iDepth,
					'page' => $oPage,
					'ulClass' => $sUlClassRep,
					'isActive' => $bIsActive
				) );
				
				
				// Reset prefix for IDs
				$this->_skipPrefixForId = $bSkipValue;
				
			} else if ( $iPrevDepth > $iDepth ) {
				
				// close li/ul tags until we're at current depth
				for ( $i = $iPrevDepth; $i > $iDepth; $i-- ) {
					
					/* /
					$sInd = sprintf( '%s%s', $sIndent, str_repeat( $sInnerIndent, $i * 2 ) );
					$sHtml .= sprintf( '%s%s</li>%s', $sInd, $sInnerIndent, $this->getEOL() );
					$sHtml .= sprintf( '%s</ul>%s', $sInd, $this->getEOL() );
					/* */
					
					$oTemplate = $oTemplateStack->get( $i );
					$sHtml .= $oTemplate->itemEnd( array( 'depth' => $i, 'page' => $oPage ) );
					$sHtml .= $oTemplate->containerEnd( array( 'depth' => $i, 'page' => $oPage ) );
				}
				
				// close previous li tag
				// $sHtml .= sprintf( '%s%s</li>%s', $sMyIndent, $sInnerIndent, $this->getEOL() );
				$sHtml .= $oCurTemplate->itemEnd( array( 'depth' => $iDepth, 'page' => $oPage, 'isActive' => $bIsActive ) );
				
			} else {
				
				// close previous li tag
				// $sHtml .= sprintf( '%s%s</li>%s', $sMyIndent, $sInnerIndent, $this->getEOL() );
				$sHtml .= $oCurTemplate->itemEnd( array( 'depth' => $iDepth, 'page' => $oPage, 'isActive' => $bIsActive ) );
				
			}
			
			// render li tag and page
			$aLiClasses = array();
			
			// Is page active?
			if ( $bIsActive ) {
				$aLiClasses[] = $sActiveClass;
				$sLiClassRep .= ' active';
			}
			
			// Add CSS class from page to LI?
			if ( $bAddPageClassToLi ) {
				$aLiClasses[] = $oPage->getClass();
			}
			
			// Add CSS class for parents to LI?
			if ( $bRenderParentClass && $oPage->hasChildren() ) {
				
				// Check max depth
				if (
					(
						( is_int( $iMaxDepth ) ) &&
						( ( $iDepth + 1 ) < $iMaxDepth )
					) || ( !is_int( $iMaxDepth ) )
				) {
					$aLiClasses[] = $sParentClass;
				}
			}
			
			/* /
			$sHtml .= sprintf(
				'%s%s<li%s>%s%s%s%s%s',
				$sMyIndent,
				$sInnerIndent,
				$this->_htmlAttribs( array( 'class' => implode( ' ', $aLiClasses ) ) ),
				$this->getEOL(),
				$sMyIndent,
				str_repeat( $sInnerIndent, 2 ),
				$this->htmlify( $oPage ),
				$this->getEOL()
			);
			/* */
			
			
			$sHtml .= $oCurTemplate->itemStart( array( 'depth' => $iDepth, 'page' => $oPage, 'liClass' => $sLiClassRep, 'isActive' => $bIsActive ) );
			$sHtml .= $oCurTemplate->link( array( 'depth' => $iDepth, 'page' => $oPage, 'isActive' => $bIsActive ) );
			
			// store as previous depth for next iteration
			$iPrevDepth = $iDepth;
		}
		
		
		//
		if ( $sHtml ) {
			
			// done iterating container; close open ul/li tags
			
			for ( $i = ( $iPrevDepth + 1 ) ; $i > 0; $i-- ) {
				
				/* /
				$sMyIndent = sprintf(
					'%s%s',
					$sIndent,
					str_repeat( sprintf( '%s%s', $sInnerIndent, $sInnerIndent ), $i - 1 )
				);
				
				$sHtml .= sprin$sMyIndenttf(
					'%s%s</li>%s%s</ul>%s' 
					$sMyIndent,
					$sInnerIndent,
					$this->getEOL(),
					$sMyIndent,
					$this->getEOL()
				);
				/* */
				
				$oTemplate = $oTemplateStack->get( $i );
				$sHtml .= $oTemplate->itemEnd( array( 'depth' => $i, 'page' => $oPage ) );
				$sHtml .= $oTemplate->containerEnd( array( 'depth' => $i, 'page' => $oPage ) );
				
			}
			
			$sHtml = rtrim( $sHtml, $this->getEOL() );
		}
		
		return $sHtml;
	}
	
	
	//
	protected function _normalizeOptions( array $aOptions = array() ) {
		
		$aOptions = parent::_normalizeOptions( $aOptions );
		
		if ( !isset( $aOptions[ 'renderDepth' ] ) ) {
			$aOptions[ 'renderDepth' ] = $this->getRenderDepth();
		}
		
		if ( !isset( $aOptions[ 'renderRelevantOnly' ] ) ) {
			$aOptions[ 'renderRelevantOnly' ] = $this->getRenderRelevantOnly();
		}
		
		if ( !isset($aOptions[ 'renderDescendants' ] ) ) {
			$aOptions[ 'renderDescendants' ] = $this->getRenderDescendants();
		}
		
		if ( !isset( $aOptions[ 'liClass' ] ) ) {
			$aOptions[ 'liClass' ] = $this->getLiClass();
		}
		
		if ( !isset( $aOptions[ 'defaultMenuTemplate' ] ) ) {
			$aOptions[ 'defaultMenuTemplate' ] = $this->getDefaultMenuTemplate();
		}
		
		if ( !isset( $aOptions[ 'menuTemplates' ] ) ) {
			$aOptions[ 'menuTemplates' ] = $this->getMenuTemplates();
		}
		
		$this->_options = $aOptions;
		
		return $aOptions;
	}


	
}


