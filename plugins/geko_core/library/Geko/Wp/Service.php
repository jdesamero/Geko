<?php

//
class Geko_Wp_Service extends Geko_Service
{
	
	
	//// email delivery stuff
	
	//
	public function deliverMail( $sSlug, $aParams ) {
	
		$oDeliverMail = new Geko_Wp_EmailMessage_Delivery( $sSlug );
		
		if ( is_array( $aRecipients = $aParams[ 'recipients' ] ) ) {
			$oDeliverMail->addRecipients( $aRecipients );
		}
		
		if ( is_array( $aMergeParams = $aParams[ 'merge_params' ] ) ) {
			$oDeliverMail->setMergeParams( $aMergeParams );
		}
		
		if ( is_array( $aFiles = $aParams[ 'files' ] ) ) {
			
			foreach ( $aFiles as $sFile ) {
				
				if ( $sFilePath = $_FILES[ $sFile ][ 'tmp_name' ] ) {
					$oDeliverMail->addAttachment( array(
						'path' => $sFilePath,
						'type' => $_FILES[ $sFile ][ 'type' ],
						'name' => $_FILES[ $sFile ][ 'name' ]
					) );
				}
			}
		}
		
		return $oDeliverMail;
	}
	
	
		
}


