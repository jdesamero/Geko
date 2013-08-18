<?php

class Geko_Wp_NavigationManagement_Page_Rewrite
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{

	//
	protected $_rwSubj;
	protected $_rwType;
	protected $_rwCmthd;		// Custom Method
	
	
    //// object methods
    
    //
    public function setRwSubj( $rwSubj ) {
        $this->_rwSubj = $rwSubj;
        return $this;
    }
	
	//
    public function getRwSubj() {
        return $this->_rwSubj;
    }
	
    //
    public function setRwType( $rwType ) {
        $this->_rwType = $rwType;
        return $this;
    }
	
	//
    public function getRwType() {
        return $this->_rwType;
    }

    //
    public function setRwCmthd( $rwCmthd ) {
        $this->_rwCmthd = $rwCmthd;
        return $this;
    }
	
	//
    public function getRwCmthd() {
        return $this->_rwCmthd;
    }
    
    
    
    
    //
    public function getHref() {
		
		if ( $sClass = $this->resolveClass() ) {
			$oRewrite = Geko_Singleton_Abstract::getInstance( $sClass );
			if ( 'single' == $this->_rwType ) {				
				
				$sSlug = apply_filters(
					__METHOD__ . '::single',
					$oRewrite->getSingleVar(),
					$oRewrite
				);
				
				return sprintf( $oRewrite->getSinglePermastruct(), $sSlug );
			} else {
				
				// TO DO: "all" is hard coded, how about other cases?
				$sSlug = apply_filters(
					__METHOD__ . '::list',
					'all',
					$oRewrite
				);
				
				return sprintf( $oRewrite->getListPermastruct(), $sSlug );
			}
		}
		
		return '';
    }
    
    //
	public function getImplicitLabel() {

		if ( $sClass = $this->resolveClass() ) {
			$oRewrite = Geko_Singleton_Abstract::getInstance( $sClass );
			if ( 'single' == $this->_rwType ) {
				if ( $sEntityClass = Geko_Class::resolveRelatedClass( $sClass, '_Rewrite' ) ) {
					
					$mKey = apply_filters(
						__METHOD__ . '::single',
						$oRewrite->getSingleVar(),
						$oRewrite
					);
					
					$oInstance = new $sEntityClass( $mKey );
					return $oInstance->getTitle();
				}				
			}
		}
		
		return $this->_rwSubj;
	}
	
	
	//
    public function toArray() {
        return array_merge(
            parent::toArray(),
            array(
            	'rw_subj' => $this->_rwSubj,
            	'rw_type' => $this->_rwType,
            	'rw_cmthd' => $this->_rwCmthd
            )
        );
    }
    
    //
    public function resolveClass() {

		$sCheck1 = $this->_rwSubj;
		$sCheck2 = 'Wp_' . $this->_rwSubj . '_Rewrite';
		$sCheck3 = 'Geko_Wp_' . $this->_rwSubj . '_Rewrite';
		
		if (
			( $sClass = Geko_Class::existsCoalesce( $sCheck1, $sCheck2, $sCheck3 ) ) && 
			( is_subclass_of( $sClass, 'Geko_Wp_Rewrite_Interface' ) )
		) {
			return $sClass;
		}
		
		return FALSE;
    }
	
	//
	public function isCurrentRewrite() {
		
		if ( $sClass = $this->resolveClass() ) {
			$oRewrite = Geko_Singleton_Abstract::getInstance( $sClass );
			if (
				( 'custom_method' == $this->_rwType ) && 
				( $sMethod = $this->_rwCmthd )
			) {
				if ( $oRewrite->$sMethod() ) return TRUE;
			} elseif ( 'list' == $this->_rwType ) {
				if ( $oRewrite->isList() ) return TRUE;
			} elseif ( 'single' == $this->_rwType ) {
				if ( $oRewrite->isSingle() ) return TRUE;
			}
		}
		
		return FALSE;
	}
	
    //
    public function isActive( $recursive = FALSE ) {
    	
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentRewrite();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}

