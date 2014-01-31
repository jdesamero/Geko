<?php

//
class Geko_Navigation_Page_Uri
	extends Zend_Navigation_Page_Uri
	implements Geko_Navigation_PageInterface
{
	//
	protected $_cssClass;
	protected $_inactive;
	protected $_hide;
	protected $_strictMatch;
	protected $_ignoreVars;
	
	protected $_origOptions = array();
	
	
	//
    public function setCssClass( $cssClass ) {
        $this->_cssClass = $cssClass;
        return $this;
    }
	
	//
    public function getCssClass() {
        return $this->_cssClass;
    }
	
	
	//
    public function setInactive( $inactive ) {
        $this->_inactive = $inactive;
        return $this;
    }
	
	//
    public function getInactive() {
        return $this->_inactive;
    }
    
    
    //
    public function setHide( $hide ) {
        $this->_hide = $hide;
        return $this;
    }
	
	//
    public function getHide() {
        return $this->_hide;
    }
	


	//
    public function setStrictMatch( $strictMatch ) {
        $this->_strictMatch = $strictMatch;
        return $this;
    }
	
	//
    public function getStrictMatch() {
        return $this->_strictMatch;
    }
    

	//
    public function setIgnoreVars( $ignoreVars ) {
        $this->_ignoreVars = $ignoreVars;
        return $this;
    }
	
	//
    public function getIgnoreVars() {
        return $this->_ignoreVars;
    }
    
    
    
    
	//
    public function setOptions( array $options ) {
        $this->_origOptions = $options;
        unset( $this->_origOptions[ 'pages' ] );
        return parent::setOptions( $options );
    }	
	
	//
	public function getOrigOptions() {
		return $this->_origOptions;
	}

	//
	public function getImplicitOptions() {
		return array();
	}
	
	//
	public function getTitle() {
		if ( $this->_title ) {
			return $this->_title;
		} else {
			return sprintf( 'Link to %s', $this->getLabel() );
		}	
	}
	
	
	//
	public function isCurrentUri() {
		
		$oCurUri = new Geko_Uri( $this->getCurUriCompare() );
		$oMyUri = new Geko_Uri( $this->getMyUriCompare() );
		
		if (
			$oMyUri->sameHost( $oCurUri ) &&
			$oMyUri->samePath( $oCurUri ) &&
			$oMyUri->sameVars( $oCurUri, $this->_strictMatch, $this->_ignoreVars )
		) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	// hook methods
	
	//
	public function getMyUriCompare() {
		return $this->_uri;
	}
	
	//
	public function getCurUriCompare() {
		return NULL;
	}
	
	
	
	
	//
	public function isActive($recursive = FALSE) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentUri();
		}
		
        return parent::isActive( $recursive );
	}
	
	
	
	
	//
    public function toArray() {
        return array_merge(
            parent::toArray(),
            array(
            	'css_class' => $this->_cssClass,
                'inactive' => $this->_inactive,
                'hide' => $this->_hide,
                'strict_match' => $this->_strictMatch,
                'ignore_vars' => $this->_ignoreVars
            )
        );
    }
    
    
    //
	public function getRootNavContainer() {
		$oItem = $this;
		while (
			( method_exists( $oItem, 'getParent' ) ) && 
			( $oParent = $oItem->getParent() )
		) {
			$oItem = $oParent;
		}
		return $oItem;
	}
    
}


