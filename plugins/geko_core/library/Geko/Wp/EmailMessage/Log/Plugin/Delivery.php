<?php

//
class Geko_Wp_EmailMessage_Log_Plugin_Delivery extends Geko_Wp_Log_Plugin
{
	//
	public function add() {
		
		parent::add();
		
		add_action( 'geko_wp_emsg_delivery_send', array( $this, 'logGekoWpEmsgDeliverySend' ), 10, 2 );
		add_action( 'geko_wp_emsg_delivery_fail', array( $this, 'logGekoWpEmsgDeliveryFail' ), 10, 2 );
		
		return $this;
	}
	
	//
	public function logGekoWpEmsgDeliverySend( $oEmsg, $aMergeParams ) {
		$this->logGekoWpEmsgDelivery( $oEmsg, $aMergeParams, 'send' );
	}
	
	//
	public function logGekoWpEmsgDeliveryFail( $oEmsg, $aMergeParams ) {
		$this->logGekoWpEmsgDelivery( $oEmsg, $aMergeParams, 'fail' );
	}
	
	//
	public function logGekoWpEmsgDelivery( $oEmsg, $aMergeParams, $sStatus ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sDateTime = $oDb->getTimestamp();
		
		if ( !$aMergeParams[ '__scheduled_delivery_date' ] ) {
			$aMergeParams[ '__scheduled_delivery_date' ] = $sDateTime;
		}
		
		$aParams = array(
			'delivery_status' => $sStatus,
			'email_address' => $aMergeParams[ '__recipient_email' ],
			'emsg_id' => $oEmsg->getId(),
			'emsg_slug' => $oEmsg->getSlug(),
			'scheduled_delivery_date' => $aMergeParams[ '__scheduled_delivery_date' ],
			'actual_delivery_date' => $sDateTime
		);
		
		unset( $aMergeParams[ '__recipient_email' ] );
		unset( $aMergeParams[ '__scheduled_delivery_date' ] );
		
		unset( $aMergeParams[ '__bloginfo_url' ] );
		unset( $aMergeParams[ '__bloginfo_name' ] );
		unset( $aMergeParams[ '__bloginfo_description' ] );
		unset( $aMergeParams[ '__bloginfo_admin_email' ] );
		unset( $aMergeParams[ '__bloginfo_stylesheet_url' ] );
		unset( $aMergeParams[ '__bloginfo_stylesheet_directory' ] );
		unset( $aMergeParams[ '__bloginfo_template_url' ] );
		unset( $aMergeParams[ '__bloginfo_template_directory' ] );
		unset( $aMergeParams[ '__bloginfo_server' ] );
		
		$aParams[ 'meta' ] = $aMergeParams;
		
		$this->_oParentLog->insert( $aParams );
		
	}
	
}


