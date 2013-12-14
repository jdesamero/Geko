<?php

class Geko_Wp_Form extends Geko_Wp_Entity
{
	protected static $aItemTypes;
	
	protected $_sEntityIdVarName = 'geko_form_id';
	protected $_sEntitySlugVarName = 'geko_form_slug';
	
	protected $_sEditEntityIdVarName = 'form_id';
	
	// language handling
	protected $oLang;
	protected $iLangId;
	protected $aFormMeta = NULL;
	protected $aPlaceholders = NULL;
	
	
	
	//
	public static function getItemTypes() {

		if ( !self::$aItemTypes ) {
			self::$aItemTypes = new Geko_Wp_Form_ItemType_Query();
		}
		
		return self::$aItemTypes;
	}
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'form_id' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}
	
	
	
	//// query related entities
	
	//
	public function setLanguage( $oLang ) {
		$this->oLang = $oLang;
		$this->iLangId = $oLang->getId();
		return $this;
	}
	
	//
	public function setPlaceholders( $aPlaceholders ) {
		$this->setData( 'placeholders', $aPlaceholders );
		return $this;
	}
	
	//
	public function getFormMeta( $mContext, $mLang = NULL ) {
		
		// gather form meta values, if not set
		if ( NULL === $this->aFormMeta ) {
			
			// query form meta values and group
			$aMeta = new Geko_Wp_Form_ItemMetaValue_Query( array(
				'showposts' => -1,
				'posts_per_page' => -1,
				'form_id' => $this->getId()
			), FALSE );
			
			$aContextIds = Geko_Wp_Form_MetaData::_getContextIds();
			
			foreach ( $aContextIds as $iContextId ) {
				
				$aContextMeta = $aMeta->subsetContextId( $iContextId );
				$aMetaFmt = array();
				
				foreach ( $aContextMeta as $oMeta ) {
					$iContextEntityId = $oMeta->getContextEntityId();
					$iLangId = $oMeta->getLangId();
					$sSlug = $oMeta->getSlug();
					if ( isset( $aMetaFmt[ $iLangId ][ $iContextEntityId ][ $sSlug ] ) ) {
						$mCurVal = Geko_Array::wrap( $aMetaFmt[ $iLangId ][ $iContextEntityId ][ $sSlug ] );
						$mCurVal[] = $oMeta->getValue();
						$aMetaFmt[ $iLangId ][ $iContextEntityId ][ $sSlug ] = $mCurVal;
					} else {
						$aMetaFmt[ $iLangId ][ $iContextEntityId ][ $sSlug ] = $oMeta->getValue();	
					}
				}
				
				$this->aFormMeta[ $iContextId ] = $aMetaFmt;
			}
		}
		
		// determine context id
		if ( preg_match( '/^[0-9]+$/', $mContext ) ) {
			$iContextId = $mContext;
		} else {
			$iContextId = Geko_Wp_Form_MetaData::_getContextId( $mContext );
		}
		
		// determine language id
		if ( NULL !== $mLang ) {
			$mLang = trim( $mLang );
			if ( preg_match( '/^[0-9]+$/', $mLang ) ) {
				$iLangId = $mLang;
			} else {
				$oLangMng = Geko_Wp_Language_Manage::getInstance()->init();
				$iLangId = $oLangMng->getLangId( $mLang );
			}
		} else {
			$iLangId = $this->iLangId;
		}
		
		return $this->aFormMeta[ $iContextId ][ $iLangId ];
	}
	
	// fetch only if current language is not the default language
	public function getLangMeta( $mContext ) {
		if ( ( $oLang = $this->oLang ) && ( !$oLang->getIsDefault() ) ) {
			return $this->getFormMeta( $mContext );
		}
		return NULL;
	}
	
	// fetch language independent meta data (stored in the default language)
	public function getDefaultMeta( $mContext ) {
		$oLangMng = Geko_Wp_Language_Manage::getInstance()->init();
		$iDefLangId = $oLangMng->getLangId();
		return $this->getFormMeta( $mContext, $iDefLangId );
	}
	
	//
	public function getFormSections() {
		
		$aData = array();
		$aData[ 'lang_meta' ] = $this->getLangMeta( 'section' );
		$aData[ 'placeholders' ] = $this->getData( 'placeholders' );
		
		$aParams = array(
			'form_id' => $this->getId(),
			'orderby' => 'rank',
			'order' => 'ASC',
			'showposts' => -1,
			'posts_per_page' => -1
		);
		
		return new Geko_Wp_Form_Section_Query( $aParams, FALSE, $aData );
	}
	
	//
	public function getFormItems() {
		
		$aData = array();
		$aData[ 'lang_meta' ] = $this->getLangMeta( 'question' );
		$aData[ 'placeholders' ] = $this->getData( 'placeholders' );
		
		$aParams = array(
			'form_id' => $this->getId(),
			'orderby' => 'rank',
			'order' => 'ASC',
			'showposts' => -1,
			'posts_per_page' => -1
		);
		
		return new Geko_Wp_Form_Item_Query( $aParams, FALSE, $aData );
	}
	
	//
	public function getFormItemValues() {
		
		$aData = array();
		$aData[ 'lang_meta' ] = $this->getLangMeta( 'choice' );
		$aData[ 'placeholders' ] = $this->getData( 'placeholders' );
		
		$aParams = array(
			'form_id' => $this->getId(),
			'orderby' => 'rank',
			'order' => 'ASC',
			'showposts' => -1,
			'posts_per_page' => -1
		);
		
		return new Geko_Wp_Form_ItemValue_Query( $aParams, FALSE, $aData );
	}
	
	//
	public function getDefaultResponses() {
		
		$aRet = array();
		
		$aParams = array(
			'form_id' => $this->getId(),
			'add_form_item_slug' => TRUE,
			'add_item_type' => TRUE,
			'showposts' => -1,
			'posts_per_page' => -1
		);
		
		$aVals = new Geko_Wp_Form_ItemValue_Query( $aParams, FALSE );
		
		foreach ( $aVals as $oVal ) {
			
			$sItemSlug = $oVal->getFormItemSlug();
			$sValueSlug = $oVal->getSlug();
			$sValueLabel = $oVal->getLabel();
			$bIsDefault = $oVal->getIsDefault();
			$sItemType = $oVal->getItemType();
			
			if ( $bIsDefault ) {
				if ( 'checkbox' == $sItemType ) {
					$aRet[ $sItemSlug ][] = 1;				
				} else {
					$aRet[ $sItemSlug ][] = $sValueSlug;
				}
			} elseif (
				( ( 'text' == $sItemType ) || ( 'textarea' == $sItemType ) ) && 
				( $sValueLabel )
			) {
				$aRet[ $sItemSlug ][] = $sValueLabel;			
			}
		}
		
		return $aRet;
	}
	
	//
	public function getItemKeys() {
		
		$aRet = array();
		
		$aParams = array(
			'form_id' => $this->getId(),
			'showposts' => -1,
			'posts_per_page' => -1
		);
		
		$aItems = new Geko_Wp_Form_Item_Query( $aParams, FALSE );
		
		foreach ( $aItems as $oItem ) {
			if ( $sKey = $oItem->getSlug() ) $aRet[] = $sKey;
		}
		
		return $aRet;
	}
	
	
	
	//// for use in rendering
	
	//
	public function getRenderer() {
		$oRender = Geko_Wp_Form_Render::getInstance();
		$oRender->setForm( $this );
		return $oRender;
	}
	
	//
	public function getElemId() {
		return $this->getEntityPropertyValue( 'slug' );
	}
	
	
	
}

