<?php

//
class Geko_Wp_NavigationManagement_Page_Custom
	extends Geko_Navigation_Page_ImplicitLabelAbstract
{

	//
	protected static $_aCache = array();
	
	protected $_customSubject;
	protected $_customParams;
	
	
	
	//// object methods
	
	//
	public function setCustomSubject( $customSubject ) {
		$this->_customSubject = $customSubject;
		return $this;
	}
	
	//
	public function getCustomSubject() {
		return $this->_customSubject;
	}
	
	
	//
	public function setCustomParams( $customParams ) {
		$this->_customParams = $customParams;
		return $this;
	}
	
	//
	public function getCustomParams() {
		return $this->_customParams;
	}
	
	
	
	//
	public function getHref() {
		return apply_filters( __METHOD__, '', $this->_customParams, $this );
	}
	
	//
	public function getImplicitLabel() {
		return apply_filters( __METHOD__, $this->_customSubject, $this->_customParams, $this );
	}
	
	
	//
	public function toArray() {
		return array_merge(
			parent::toArray(),
			array(
				'custom_subject' => $this->_customSubject,
				'custom_params' => $this->_customParams
			)
		);
	}
	
	//
	public function isCurrentCustom() {		
		return apply_filters( __METHOD__, FALSE, $this->_customParams, $this );
	}
	
	//
	public function isActive( $recursive = FALSE ) {
		
		if ( $this->_inactive ) {
			$this->_active = FALSE;
		} else {
			$this->_active = $this->isCurrentCustom();
		}
		
		return parent::isActive( $recursive );
	}
	
	
}

