<?php

//
class Geko_Wp_NavigationManagement_PageManager_Page
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $aPagesNorm = array();
	
	
	
	//
	public function init() {
		
		$aPagesNorm = array();
		
		$aParams = array(
			'post_type' => 'page',
			'showposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC'
		);
		
		$aParams = apply_filters( 'admin_geko_wp_nav_pg_query_params', $aParams );
		
		$aPages = new Geko_Wp_Post_Query( $aParams, FALSE );
		
		foreach ( $aPages as $oPage ) {
			$aPagesNorm[ $oPage->getId() ] = array(
				'title' => $oPage->getTheTitle(),
				'link' => $oPage->getUrl()
			);
		}
		
		$this->setPagesNorm( $aPagesNorm );
		
	}
	
	//
	public function setPagesNorm( $aPagesNorm ) {
		$this->aPagesNorm = $aPagesNorm;
		return $this;
	}
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams['page_id'] = key( $this->aPagesNorm );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData['page_params'] = $this->aPagesNorm;
		
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
		<label for="##nvpfx_type##page_id">Page Title</label>
		<select name="##nvpfx_type##page_id" id="##nvpfx_type##page_id" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Page';
    }
    
	
}

