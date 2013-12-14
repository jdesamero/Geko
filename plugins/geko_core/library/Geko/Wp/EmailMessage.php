<?php

class Geko_Wp_EmailMessage extends Geko_Wp_Entity
{
	protected $_sEntityIdVarName = 'geko_emsg_id';
	protected $_sEntitySlugVarName = 'geko_emsg_slug';
	
	protected $_sEditEntityIdVarName = 'emsg_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'emsg_id' )
			->setEntityMapping( 'title', 'subject' )
			->setEntityMapping( 'content', 'body_text' )
		;
		
		return $this;
	}
	
	
	
	
	//
	public function getTheFromName() {
		if ( $sFromName = $this->getFromName() ) {
			return $sFromName;
		} else {
			return get_bloginfo( 'name' );
		}
	}
	
	//
	public function getTheFromEmail() {
		
		$sFromEmail = $this->getFromEmail();
		
		$sTransEmail = NULL;
		if (
			( $oTransport = $this->getTransport() ) && 
			( $oTrpt = $oTransport->geko_trpt )
		) {
			$sTransEmail = $oTrpt->getUsername();
			if ( !is_email( $sTransEmail ) ) $sTransEmail = NULL;
		}
		
		$sDefaultEmail = get_bloginfo( 'admin_email' );
		
		return Geko_String::coalesce( $sFromEmail, $sTransEmail, $sDefaultEmail );
	}
	
	//
	public function getTheBodyHtml() {
		if ( $this->getBodyHtmlIsRaw() ) {
			return $this->getBodyHtml();		
		} else {
			return wpautop( $this->getBodyHtml(), 1 );
		}
	}
	
	
	//
	public function getTransport() {
		$iTrptId = $this->getEntityPropertyValue( 'trpt_id' );
		return Geko_Wp_EmailMessage_Transport::factory( $iTrptId );
	}
	
	
}



