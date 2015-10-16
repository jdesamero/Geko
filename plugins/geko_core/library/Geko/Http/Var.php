<?php

// class for handling HTTP $_GET/$_POST/$_REQUEST/$GLOBALS[ 'HTTP_RAW_POST_DATA' ]
class Geko_Http_Var
{

	// typically if $GLOBALS[ 'HTTP_RAW_POST_DATA' ] is set, it would contain JSON formatted data
	// at least when using backbone.js
	public static function formatHttpRawPostData() {
		
		static $bOnce = TRUE;
		
		if ( $bOnce ) {
			
			if ( $sData = file_get_contents( 'php://input' ) ) {
				
				// normalize to $_POST/$_GET/$_REQUEST data
				try {
					
					$_POST = Geko_Json::decode( $sData );
					$_GET = Geko_Uri::getGlobal()->getVars();
					
					$_REQUEST = array_merge( $_POST, $_GET );
					
				} catch ( Exception $e ) { }
			}
						
			$bOnce = FALSE;
		}
		
	}
	
	
	
}
