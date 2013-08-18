<?php

//
class Geko_Wp_NavigationManagement_PageManager_Category
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $aCatsNorm = array();
	
	
	
	//
	public function init() {
		
		$aCatsNorm = array();
		
		$aParams = array( 'hide_empty' => FALSE );
		$aParams = apply_filters( 'admin_geko_wp_nav_cat_query_params', $aParams );
		
		$aCats = new Geko_Wp_Category_Query( $aParams, FALSE );
		
		foreach ( $aCats as $oCat ) {
			$aCatsNorm[ $oCat->getId() ] = array(
				'title' => $oCat->getTheTitle(),
				'link' => $oCat->getUrl()
			);
		}
		
		$this->setCatsNorm( $aCatsNorm );
		
	}
	
	//
	public function setCatsNorm( $aCatsNorm ) {
		$this->aCatsNorm = $aCatsNorm;
		return $this;
	}
	
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams['cat_id'] = key( $this->aCatsNorm );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData() {
		
		$aData = parent::getManagementData();
		$aData['cat_params'] = $this->aCatsNorm;
		
		return $aData;
	}

	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: palegreen; border: dotted 1px darkgreen; }
		<?php
	}
	
	
	//
	public function outputHtml() {
		?>
		<label for="##nvpfx_type##cat_id">Category Title</label>
		<select name="##nvpfx_type##cat_id" id="##nvpfx_type##cat_id" class="text ui-widget-content ui-corner-all"></select>		
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Category';
    }
    
}

