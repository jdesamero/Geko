<?php

class Geko_Wp_EmailMessage_Storage_Log extends Geko_Wp_Log
{
	
	//
	public function getDeliveryStatusDetails() {
		return Geko_Wp_EmailMessage_Storage_Message::_getDeliveryStatusCode(
			$this->getEntityPropertyValue( 'status_code' )
		);
	}
	
	//
	public function getDateParsedFmt( $sFormat = 'D, j M Y H:i:s' ) {
		return $this->mysql2DateFormat( 'date_parsed', $sFormat );
	}
	
}


