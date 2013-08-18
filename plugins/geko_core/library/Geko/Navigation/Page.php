<?php

//
abstract class Geko_Navigation_Page
	extends Zend_Navigation_Page
	implements Geko_Navigation_PageInterface
{
	//
	protected $_cssClass;
	protected $_inactive;
	protected $_hide;
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
			return 'Link to ' . $this->getLabel();
		}	
	}
	
	//
	public function getVisible() {
		
		if ( $this->_hide ) {
			$this->_visible = FALSE;
		} else {
			$this->_visible = TRUE;		
		}
		
		return $this->_visible;
	}
	
	
	//
    public function toArray() {
        return array_merge(
            parent::toArray(),
            array(
            	'css_class' => $this->_cssClass,
                'inactive' => $this->_inactive,
                'hide' => $this->_hide
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


