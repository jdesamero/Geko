<?php

class Geko_Wp_NavigationManagement_Page_Category
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_catId;
    
    
    //// object methods
    
    //
    public function setCatId( $catId ) {
        $this->_catId = $catId;
        return $this;
    }
	
	//
    public function getCatId() {
        return $this->_catId;
    }
    
    
    
    
    
    
    //
    public function getHref() {
        return get_category_link( $this->_catId );
    }
	
	//
	public function getImplicitLabel() {
		$oCat = get_category( $this->_catId );
		return ( is_object( $oCat ) ) ? $oCat->name : '';
	}
	
	
	//
    public function toArray() {
        return array_merge(
            parent::toArray(),
            array( 'cat_id' => $this->_catId )
        );
    }
    
    //
    public function isCurrentCategory() {
		return apply_filters(
			__METHOD__ . '::category',
			is_category( $this->_catId ),
			$this->_catId
		);
    }
    
    //
    public function isActive( $recursive = FALSE ) {
    	
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentCategory();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}

