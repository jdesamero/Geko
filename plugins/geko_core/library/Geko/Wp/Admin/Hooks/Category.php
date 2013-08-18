<?php

//
class Geko_Wp_Admin_Hooks_Category extends Geko_Wp_Admin_Hooks_PluginAbstract
{	
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/categories.php' ) ) {

			// < v3.0
			
			$this
				->setValue( 'version', 2 )
				->setValue( 'parent_id', 'category_parent' )
			;
			
			$aRet[] = 'category';
			
			if ( 'edit' == $oUrl->getVar( 'action' ) ) {
				$aRet[] = 'category_edit';
			} else {
				$aRet[] = 'category_list';
			}
			
			return $aRet;
			
		} elseif (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/edit-tags.php' ) ) && 
			( 'category' == $oUrl->getVar( 'taxonomy' ) )
		) {
						
			// >= 3.0

			$this
				->setValue( 'version', 3 )
				->setValue( 'parent_id', 'parent' )
			;
			
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
	public function applyFilters( $sContent, $sState )
	{
		if ( 'category_edit' == $sState ) {
			
			if ( 2 == $this->getValue( 'version' ) ) {
				
				$sContent = $this->replace(
					$sContent,
					'admin_category_edit_fields_pq',
					'/<form name="editcat" id="editcat".+?<\/form>/s'
				);
				
			} else {
				
				$sContent = $this->replace(
					$sContent,
					'admin_category_edit_fields_pq',
					'/<form name="edittag" id="edittag".+?<\/form>/s'
				);
				
			}
			
		}
		
		if ( 'category_list' == $sState ) {
			
			if ( 2 == $this->getValue( 'version' ) ) {
				
				$sContent = $this->replace(
					$sContent,
					'admin_category_add_fields_pq',
					'/<form name="addcat" id="addcat".+?<\/form>/s'
				);
				
			} else {
				
				$sContent = $this->replace(
					$sContent,
					'admin_category_add_fields_pq',
					'/<form id="addtag".+?<\/form>/s'
				);
				
			}
			
		}
		
		return $sContent;
	}
	
}


