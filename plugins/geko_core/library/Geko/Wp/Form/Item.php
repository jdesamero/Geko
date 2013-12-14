<?php

class Geko_Wp_Form_Item extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'fmitm_id';
	// protected $_sEntitySlugVarName = '';
	
	protected $_sEditEntityIdVarName = 'fmitm_id';
	
	
	protected static $oForm = NULL;
	protected static $aItems = NULL;
	protected static $aItemHash = NULL;
	protected static $aItemValues = NULL;
	protected static $aItemValueHash = NULL;
	
	
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'title', 'title' )
			->setEntityMapping( 'content', 'description' )
			->setEntityMapping( 'section_id', 'fmsec_id' )
			->setEntityMapping( 'item_type_id', 'fmitmtyp_id' )
			->setData( 'lang_meta_fields', array( 'title', 'help' ) )
		;
		
		return $this;
	}
	
	
	
	//// for use in rendering
	
	//
	public function getElemId() {
		return $this->getEntityPropertyValue( 'slug' );
	}

	//
	public function getElemName() {
		return $this->getEntityPropertyValue( 'slug' );
	}
	
	
	
	
	//// for conditional logic
	
	//
	public static function _setForm( $oForm ) {
		self::$oForm = $oForm;
	}
	
	// generates an item hash
	public static function _setItems( $aItems = NULL ) {
		
		if ( ( NULL == $aItems ) && ( NULL == self::$aItems ) && ( self::$oForm ) ) {
			$aItems = self::$oForm->getFormItems();
		}
		
		if ( $aItems ) {	
			self::$aItems = $aItems;
			
			$aItemHash = array();
			foreach ( $aItems as $oItem ) {
				$aItemHash[ $oItem->getId() ] = $oItem;
			}
			
			self::$aItemHash = $aItemHash;
		}
	}
	
	// generates an item value hash
	public static function _setItemValues( $aItemValues = NULL ) {

		if ( ( NULL == $aItemValues ) && ( NULL == self::$aItemValues ) && ( self::$oForm ) ) {
			$aItemValues = self::$oForm->getFormItemValues();
		}
		
		if ( $aItemValues ) {	
			self::$aItemValues = $aItemValues;
			
			$aItemValueHash = array();
			foreach ( $aItemValues as $oItmVal ) {
				$aItemValueHash[ $oItmVal->getFmitmId() ][ $oItmVal->getFmitmvalIdx() ] = $oItmVal;
			}
			
			self::$aItemValueHash = $aItemValueHash;
		}
	}
	
	//
	public static function _getItemHash() {
		if ( NULL == self::$aItemHash ) self::_setItems();
		return self::$aItemHash;
	}
	
	//
	public static function _getItemValueHash() {
		if ( NULL == self::$aItemValueHash ) self::_setItemValues();
		return self::$aItemValueHash;		
	}
	
	//
	public function getTheValidation( $aItemHash = NULL, $aItemValueHash = NULL ) {
		
		if ( NULL == $aItemHash ) $aItemHash = self::_getItemHash();
		if ( NULL == $aItemValueHash ) $aItemValueHash = self::_getItemValueHash();
		
		$sValidation = $this->getValidation();
		$iParItemId = $this->getParentItmId();
		$iParItmValIdx = $this->getParentItmvalidxId();
		
		if ( $aItemHash && $aItemValueHash && $iParItemId ) {
			
			$oParItem = $aItemHash[ $iParItemId ];
			$oChoice = $aItemValueHash[ $iParItemId ][ $iParItmValIdx ];
			
			$sUlId = sprintf( 'sub-%s-%s', $oParItem->getSlug(), $oChoice->getSlug() );
			
			if ( $sValidation ) $sValidation .= ',';
			$sValidation .= $sUlId;
		}
		
		return $sValidation;
	}
	
	
	
}


