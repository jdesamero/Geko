<?php

//
class Geko_Wp_NavigationManagement_Page_Author
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{
	
	//
	protected $_authorId;
    
    
    //// object methods
    
    //
    public function setAuthorId( $authorId ) {
        $this->_authorId = $authorId;
        return $this;
    }
	
	//
    public function getAuthorId() {
        return $this->_authorId;
    }
    
    
    
    
    
    
    //
    public function getHref() {
        return get_author_posts_url( $this->_authorId );
    }
	
	//
	public function getImplicitLabel() {
		$oAuthor = new Geko_Wp_Author( $this->_authorId );
		return ( is_object( $oAuthor ) ) ?
			Geko_String::coalesce( $oAuthor->getFullName(), $oAuthor->getTitle() ) :
			''
		;
	}
	
	
	//
    public function toArray() {
        return array_merge(
            parent::toArray(),
            array( 'author_id' => $this->_authorId )
        );
    }
    
    //
    public function isCurrentAuthor() {
		return apply_filters(
			__METHOD__ . '::author',
			is_author( $this->_authorId ),
			$this->_authorId
		);
    }
    
    //
    public function isActive( $recursive = FALSE ) {
    	
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentAuthor();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}

