<?php

//
class Geko_Wp_User_Photo extends Geko_Wp_User_Meta
{
	
	protected $_bHasFileUpload = TRUE;
	protected $_aUploadPaths = array(
		'/wp-content/userimages' => array(
			'auto_resolve' => TRUE,
			'meta_keys' => array( 'user_photo' )
		)
	);
		
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
				
		add_filter( 'get_avatar', array( $this, 'getAvatar' ), 10, 5 );
		
		return $this;
	}
	
	
	
	//// accessors
	
	//
	public function getAvatar( $sAvatarSrc, $mIdOrEmail, $iSize, $sDefault, $sAlt ) {
		
		if ( is_object( $mIdOrEmail ) ) {
			$iUserId = $mIdOrEmail->ID;
		} else {
			$iUserId = intval( $mIdOrEmail );
		}
		
		if ( $sFile = $this->getPhotoDoc( $iUserId ) ) {
			
			$oDoc = phpQuery::newDocument( $sAvatarSrc );
			
			$sThumbUrl = Geko_Wp::getThumbUrl( array(
				'src' => $sFile,
				'w' => $iSize,
				'h' => $iSize,
				'zc' => 1
			) );
			
			$oDoc[ 'img' ]->attr( 'src', $sThumbUrl );
			
			$sAvatarSrc = strval( $oDoc );
		}
		
		return $sAvatarSrc;
	}
	
	//
	public function getTitle() {
		return 'User Photo';
	}
	
	
	
	
	//// front-end display methods
	
	//
	public function formFields() {
		
		$this->fieldRow( 'Upload Image', 'user_photo', array(
			'thumb_width' => 175,
			'thumb_height' => 225
		), 'image_upload' );
		
	}
	
	
		
	
}


