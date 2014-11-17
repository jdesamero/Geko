<?php

// used against Zend 1.8.0

class Geko_View_Helper_Navigation_Menu extends Zend_View_Helper_Navigation_Menu
{
	
	
	protected $_renderDepth = null;
	protected $_renderRelevantOnly = false;
	protected $_renderDescendants = false;
	protected $_liClass;
	protected $_defaultMenuTemplate;
	protected $_menuTemplates = array();
	
	protected $_options = array();
	
	
	
	//
	protected function setRenderDepth($iDepth = 0) {
		$this->_renderDepth = $iDepth;
		return $this;
	}

	//
	protected function getRenderDepth() {
		return $this->_renderDepth;
	}
	
	
	
	//
	protected function setRenderRelevantOnly($flag = true) {
		$this->_renderRelevantOnly = (bool) $flag;
		return $this;
	}

	//
	protected function getRenderRelevantOnly() {
		return $this->_renderRelevantOnly;
	}
	
	
	
	//
	protected function setRenderDescendants($flag = true) {
		$this->_renderDescendants = (bool) $flag;
		return $this;
	}

	//
	protected function getRenderDescendants() {
		return $this->_renderDescendants;
	}
	
	
	
	//
	protected function setLiClass( $liClass ) {
		$this->_liClass = $liClass;
		return $this;
	}

	//
	protected function getLiClass() {
		return $this->_liClass;
	}
    
    
    
    //
    protected function setDefaultMenuTemplate( $defaultMenuTemplate ) {
        $this->_defaultMenuTemplate = $defaultMenuTemplate;
        return $this;
    }
    
    //
    protected function getDefaultMenuTemplate() {
        return $this->_defaultMenuTemplate;
    }
    
    
    
    //
    protected function setMenuTemplates( $menuTemplates ) {
        $this->_menuTemplates = $menuTemplates;
        return $this;
    }
    
    //
    protected function getMenuTemplates() {
        return $this->_menuTemplates;
    }
    
    
    
    
	//
	protected function inActiveBranch( Zend_Navigation_Page $oPage, $iDepthCheck = 0 )
	{
		if ( FALSE == $oPage->isActive(TRUE) ) {
			// check parent
			if ( ( $parent = $oPage->getParent() ) instanceof Zend_Navigation_Page ) {
				if ( 0 == $iDepthCheck ) {
					// stop checking
					return $parent->isActive(TRUE);
				} else {
					// recursive
					return self::inActiveBranch( $parent, $iDepthCheck-- );	// decrement
				}
			} else {
				return FALSE;
			}
		} else {
			return TRUE;
		}
	}
	
	
	
	//
    protected function _renderMenu(Zend_Navigation_Container $container,
                                   $ulClass,
                                   $indent,
                                   $innerIndent,
                                   $minDepth,
                                   $maxDepth,
                                   $onlyActive,
                                   $expandSibs,
                                   $ulId,
                                   $addPageClassToLi,
                                   $activeClass,
                                   $parentClass,
                                   $renderParentClass)
    {
        $html = '';

        // find deepest active
        if ($found = $this->findActive($container, $minDepth, $maxDepth)) {
            $foundPage = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
                            RecursiveIteratorIterator::SELF_FIRST);
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }
		
		
		
		$options = $this->_options;
		
		
		
        // iterate container
        
        $oTemplateStack = new Geko_Navigation_Renderer_TemplateStack( $this, $options['defaultMenuTemplate'] );
        $oTemplateStack->setTemplates( $options['menuTemplates'] );
        
        $prevDepth = -1;
        
        foreach ($iterator as $page) {
            
            $depth = $iterator->getDepth();
            $isActive = $page->isActive(true);
            
            if (
            	(
					($page instanceof Geko_Navigation_Page) ||
					($page instanceof Geko_Navigation_Page_Uri)
				) && (
					$page->getHide()
				)
            ) {
				continue;
            }
            
            if ($depth < $minDepth || !$this->accept($page)) {
                // page is below minDepth or not accepted by acl/visibilty
                continue;
            } else if ($expandSibs && $depth > $minDepth) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($page->getParent()->isActive(true)) {
                        // page is a sibling of the active branch...
                        $accept = true;
                    }
                }
                if (!$isActive && !$accept) {
                    continue;
                }
            } else if ($onlyActive && !$isActive) {
                // page is not active itself, but might be in the active branch
                $accept = false;
                if ($foundPage) {
                    if ($foundPage->hasPage($page)) {
                        // accept if page is a direct child of the active page
                        $accept = true;
                    } else if ($foundPage->getParent()->hasPage($page)) {
                        // page is a sibling of the active page...
                        if (!$foundPage->hasPages() ||
                            is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                            // accept if active page has no children, or the
                            // children are too deep to be rendered
                            $accept = true;
                        }
                    }
                }

                if (!$accept) {
                    continue;
                }
            }
            
            
            
            
            $parent = $page->getParent();
            
            
            // rendering depth was specified
            if ( null !== $options['renderDepth'] ) {        
            	
            	if ( $options['renderDescendants'] ) {
            		
            		// render specified menu if active and all its descendants
            		// $iDepthCheck ensures we only check up to the current depth, otherwise we trigger active on unwanted items
            		
            		if ( $options['renderDepth'] > $depth ) {
            			$iDepthCheck = $depth - $options['renderDepth'];
            		} else {
            			$iDepthCheck = 0;
            		}
            		
            		if (
            			( $depth < $options['renderDepth'] ) ||
            			( !self::inActiveBranch( $page, $iDepthCheck ) )
            		) {
            			continue;
            		}
            		
            	} else {
            		
            		// only render the specified depth (for stratified menus)
            		if (
						( $options['renderDepth'] != $depth ) || (
							( false == ($parent instanceof Zend_Navigation) ) &&
							( false == $parent->isActive(true) )
						)            		
            		) {
            			continue;
            		}
            		
            	}
            	
            }
            
            if (
            	( $options['renderRelevantOnly'] ) && 
            	( false == ($parent instanceof Zend_Navigation) ) &&
            	( false == $parent->isActive(true) )
            ) {
            	continue;
            }
            
            
            $ulClassRep = str_replace( '##depth##', $depth, $ulClass );
            $liClassRep = str_replace( '##depth##', $depth, $options['liClass'] );
            
            
            
            // make sure indentation is correct
            $depth   -= $minDepth;
            $myIndent = $indent . str_repeat($innerIndent, $depth * 2);
			
			
			$oCurTemplate = $oTemplateStack->get( $depth );
			
			
            if ($depth > $prevDepth) {
                $attribs = array();

                // start new ul tag
                if (0 == $depth) {
                    $attribs = array(
                        'class' => $ulClass,
                        'id'    => $ulId,
                    );
                }

                // We don't need a prefix for the menu ID (backup)
                $skipValue = $this->_skipPrefixForId;
                $this->skipPrefixForId();

                /* $html .= $myIndent . '<ul'
                                   . $this->_htmlAttribs($attribs)
                                   . '>'
                                   . $this->getEOL(); */
                
				// start new container
				$html .= $oCurTemplate->containerStart( array( 'depth' => $depth, 'page' => $page, 'ulClass' => $ulClassRep, 'isActive' => $isActive ) );
                
                
                // Reset prefix for IDs
                $this->_skipPrefixForId = $skipValue;
            
            } else if ($prevDepth > $depth) {
                
                // close li/ul tags until we're at current depth
                for ($i = $prevDepth; $i > $depth; $i--) {
                    
                    /* $ind   = $indent . str_repeat($innerIndent, $i * 2);
                    $html .= $ind . $innerIndent . '</li>' . $this->getEOL();
                    $html .= $ind . '</ul>' . $this->getEOL(); */
                    
                    $oTemplate = $oTemplateStack->get( $i );
                    $html .= $oTemplate->itemEnd( array( 'depth' => $i, 'page' => $page ) )
                          .  $oTemplate->containerEnd( array( 'depth' => $i, 'page' => $page ) );
                }
                
                // close previous li tag
                // $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
                $html .= $oCurTemplate->itemEnd( array( 'depth' => $depth, 'page' => $page, 'isActive' => $isActive ) );
                
            } else {
                
                // close previous li tag
                // $html .= $myIndent . $innerIndent . '</li>' . $this->getEOL();
                $html .= $oCurTemplate->itemEnd( array( 'depth' => $depth, 'page' => $page, 'isActive' => $isActive ) );
                
            }

            // render li tag and page
            $liClasses = array();
            // Is page active?
            if ($isActive) {
                $liClasses[] = $activeClass;
                $liClassRep .= ' active';
            }
            // Add CSS class from page to LI?
            if ($addPageClassToLi) {
                $liClasses[] = $page->getClass();
            }
            // Add CSS class for parents to LI?
            if ($renderParentClass && $page->hasChildren()) {
                // Check max depth
                if ((is_int($maxDepth) && ($depth + 1 < $maxDepth))
                    || !is_int($maxDepth)
                ) {
                    $liClasses[] = $parentClass;
                }
            }

            /* $html .= $myIndent . $innerIndent . '<li'
                   . $this->_htmlAttribs(array('class' => implode(' ', $liClasses)))
                   . '>' . $this->getEOL()
                   . $myIndent . str_repeat($innerIndent, 2)
                   . $this->htmlify($page)
                   . $this->getEOL(); */
            
            $html .= $oCurTemplate->itemStart( array( 'depth' => $depth, 'page' => $page, 'liClass' => $liClassRep, 'isActive' => $isActive ) ) 
                  .  $oCurTemplate->link( array( 'depth' => $depth, 'page' => $page, 'isActive' => $isActive ) );
            
            // store as previous depth for next iteration
            $prevDepth = $depth;
        }

        if ($html) {
            
            // done iterating container; close open ul/li tags
            
            for ($i = $prevDepth+1; $i > 0; $i--) {
                
                /* $myIndent = $indent . str_repeat($innerIndent . $innerIndent, $i - 1);
                $html    .= $myIndent . $innerIndent . '</li>' . $this->getEOL()
                         . $myIndent . '</ul>' . $this->getEOL(); */
                
                $oTemplate = $oTemplateStack->get( $i );
                $html .= $oTemplate->itemEnd( array( 'depth' => $i, 'page' => $page ) )
                      .  $oTemplate->containerEnd( array( 'depth' => $i, 'page' => $page ) );
                
            }
            
            $html = rtrim($html, $this->getEOL());
        }

        return $html;
    }
    

    //
    protected function _normalizeOptions(array $options = array())
    {
    	$options = parent::_normalizeOptions( $options );
    	
        if ( !isset($options['renderDepth']) ) {
            $options['renderDepth'] = $this->getRenderDepth();
        }

        if ( !isset($options['renderRelevantOnly']) ) {
            $options['renderRelevantOnly'] = $this->getRenderRelevantOnly();
        }

        if ( !isset($options['renderDescendants']) ) {
            $options['renderDescendants'] = $this->getRenderDescendants();
        }
		
        if ( !isset($options['liClass']) ) {
            $options['liClass'] = $this->getLiClass();
        }
        
        if ( !isset($options['defaultMenuTemplate']) ) {
            $options['defaultMenuTemplate'] = $this->getDefaultMenuTemplate();
        }
        
        if ( !isset($options['menuTemplates']) ) {
            $options['menuTemplates'] = $this->getMenuTemplates();
        }
        
        $this->_options = $options;
        
    	return $options;
    }


    
}


