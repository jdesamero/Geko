<?php

//
class Geko_Wp_Language_Member extends Geko_Wp_Entity
{	
	
	//
	public function getTheSiblings() {
		
		$oRet = new Geko_Wp_Language_Member_Query( NULL, FALSE );
		
		if ( $sSiblings = $this->getEntityPropertyValue( 'siblings' ) ) {
			
			$oLangMgm = Geko_Wp_Language_Manage::getInstance();
			
			$aObjSibs = explode( ',', $sSiblings );
			$aObjSibsFmt = array();
			
			foreach ( $aObjSibs as $sObjSib ) {
				
				list( $iLangId, $iObjId ) = explode( ':', $sObjSib );
				$oLang = $oLangMgm->getLanguage( $iLangId );
				
				$aObjSibsFmt[] = array(
					'lang_group_id' => $this->getLangGroupId(),
					'obj_id' => $iObjId,
					'lang_id' => $iLangId,
					'type_id' => $this->getTypeId(),
					'lang_code' => $oLang->getSlug(),
					'lang_title' => $oLang->getTitle(),
					'lang_is_default' => $oLang->getIsDefault()
				);
				
			}
			
			$oRet->setRawEntities( $aObjSibsFmt );
		}
		
		return $oRet;
	}
	
	//
	public function getType() {
		return Geko_Wp_Options_MetaKey::getKey( $this->getTypeId() );
	}
	
	
}


