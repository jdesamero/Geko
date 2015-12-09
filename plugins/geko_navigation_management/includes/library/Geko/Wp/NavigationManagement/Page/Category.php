<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/Page/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

class Geko_Wp_NavigationManagement_Page_Category
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_iCatId;
    protected $_oCat;
    
    
    //// object methods
    
    //
    public function setCatId( $iCatId ) {
        
        $this->_iCatId = $iCatId;
        $this->_oCat = get_term( $iCatId );
        
        return $this;
    }
	
	//
    public function getCatId() {
        return $this->_iCatId;
    }
    
    
    
    
    
    
    //
    public function getHref() {

		$oCat = $this->_oCat;
		
		return ( $oCat ) ? get_term_link( $oCat ) : '' ;
    }
	
	//
	public function getImplicitLabel() {
		
		$oCat = $this->_oCat;
		
		return ( $oCat ) ? $oCat->name : '' ;
	}
	
	
	
	//
    public function toArray() {
        
        return array_merge(
            parent::toArray(),
            array( 'cat_id' => $this->_iCatId )
        );
    }
    
    //
    public function isCurrentCategory() {
		
		$oCat = $this->_oCat;
		
		$bIsCurrentCategory = FALSE;
		$sTaxonomy = $oCat->taxonomy;
		
		if ( 'category' == $sTaxonomy ) {
			$bIsCurrentCategory = is_category( $this->_iCatId );
		} else {
			$bIsCurrentCategory = ( $oCat ) ? is_tax( $sTaxonomy, $this->_iCatId ) : FALSE ;
		}
		
		return apply_filters(
			sprintf( '%s::category', __METHOD__ ),
			$bIsCurrentCategory,
			$this->_iCatId
		);
    }
    
    
    //
    public function isActive( $bRecursive = FALSE ) {
    	
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentCategory();
		}
		
		return parent::isActive( $bRecursive );
	}
	
	
}

