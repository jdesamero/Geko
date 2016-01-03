<?php

//
class Geko_Wp_NavigationManagement_PageManager_Page
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $_aPageParams = array();
	
	
	
	//
	public function init() {
		
		$aTypes = array( 'page' );
		
		if ( $sCustomTypes = $this->_aParams[ 'add_custom_types' ] ) {
			$aTypes = array_merge( $aTypes, Geko_Array::explodeTrim( ',', $sCustomTypes ) );
		}
		
		
		//// pages
		
		$aPagesNorm = array();
		
		$aParams = array(
			'post_type' => $aTypes,
			'showposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC'
		);
		
		$aParams = apply_filters( 'admin_geko_wp_nav_pg_query_params', $aParams );
		
		$aPages = new Geko_Wp_Post_Query( $aParams, FALSE );
		
		foreach ( $aPages as $oPage ) {
			$aPagesNorm[ $oPage->getId() ] = array(
				'title' => $oPage->getTheTitle(),
				'link' => $oPage->getUrl(),
				'type' => $oPage->getPostType()
			);
		}
		
		
		//// page types
		
		$aTypesFmt = array();
		
		foreach ( $aTypes as $sType ) {
			$oType = get_post_type_object( $sType );
			$aTypesFmt[ $sType ] = $oType->labels->singular_name;
		}
		
		
		$this->setPageParams( array(
			'pages_norm' => $aPagesNorm,
			'page_types' => $aTypesFmt,
			'page_types_count' => count( $aTypesFmt )
		) );
		
	}
	
	
	//
	public function setPageParams( $aPagesNorm ) {
		
		$this->_aPageParams = $aPagesNorm;
		
		return $this;
	}
	
	//
	public function getPageParams( $aPagesNorm ) {
		return $this->_aPageParams;
	}
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams[ 'page_id' ] = key( $this->_aPageParams );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData[ 'page_params' ] = $this->_aPageParams;
		
		return $aData;
	}
	
	
	
	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: lightpink; border: dotted 1px darkred; }
		<?php
	}
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##page_type">Page Type</label>
		<select name="##nvpfx_type##page_type" id="##nvpfx_type##page_type" class="text ui-widget-content ui-corner-all"></select>
		
		<label for="##nvpfx_type##page_id">Page Title</label>
		<select name="##nvpfx_type##page_id" id="##nvpfx_type##page_id" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Page';
    }
    
	
}

