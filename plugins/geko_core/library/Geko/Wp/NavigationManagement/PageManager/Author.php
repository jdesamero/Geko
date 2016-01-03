<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/PageManager/Author.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_PageManager_Author
	extends Geko_Navigation_PageManager_ImplicitLabelAbstract
{
	//
	protected $aAuthorsNorm = array();
	
	
	
	//
	public function init() {
		
		$aAuthorsNorm = array();
		
		$aParams = array();
		$aParams = apply_filters( 'admin_geko_wp_nav_author_query_params', $aParams );
		
		$aAuthors = new Geko_Wp_Author_Query( $aParams, FALSE );
		
		foreach ( $aAuthors as $oAuthor ) {
			$aAuthorsNorm[ $oAuthor->getId() ] = array(
				'title' => $oAuthor->getTheTitle(),
				'link' => $oAuthor->getUrl()
			);
		}
		
		$this->setAuthorsNorm( $aAuthorsNorm );
		
	}
	
	//
	public function setAuthorsNorm( $aAuthorsNorm ) {
		$this->aAuthorsNorm = $aAuthorsNorm;
		return $this;
	}
	
	
	
	
	
	//
	public function getDefaultParams() {
		
		$aParams = parent::getDefaultParams();
		$aParams['author_id'] = key( $this->aAuthorsNorm );
		
		return $aParams;
	}
	
	
	//
	public function getManagementData()
	{	
		$aData = parent::getManagementData();
		$aData['author_params'] = $this->aAuthorsNorm;
		
		return $aData;
	}

	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: wheat; border: dotted 1px brown; }
		<?php
	}
	
	
	//
	public function outputHtml()
	{
		?>
		<label for="##nvpfx_type##author_id">Author</label>
		<select name="##nvpfx_type##author_id" id="##nvpfx_type##author_id" class="text ui-widget-content ui-corner-all"></select>
		<?php
	}
	
	
	//
    public static function getDescription() {
    	return 'Wordpress Author';
    }
    
}

