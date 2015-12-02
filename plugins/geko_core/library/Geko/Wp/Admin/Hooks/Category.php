<?php
/*
 * "geko_core/library/Geko/Wp/Admin/Hooks/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Admin_Hooks_Category extends Geko_Wp_Admin_Hooks_PluginAbstract
{	
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/edit-tags.php' ) ) {
			
			$this->setValue( 'parent_id', 'parent' );
			
			$aRet[] = 'category';
			
			if ( 'edit' == $oUrl->getVar( 'action' ) ) {
				$aRet[] = 'category_edit';
			} else {
				$aRet[] = 'category_list';
			}
			
			return $aRet;
			
		}
		
		return FALSE;
	}
	
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		if ( 'category_edit' == $sState ) {
							
			$sContent = $this->replace(
				$sContent,
				'admin_category_edit_fields_pq',
				'/<form name="edittag" id="edittag".+?<\/form>/s'
			);
			
		}
		
		if ( 'category_list' == $sState ) {			
				
			$sContent = $this->replace(
				$sContent,
				'admin_category_add_fields_pq',
				'/<form id="addtag".+?<\/form>/s'
			);
			
		}
		
		return $sContent;
	}
	
}


