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
			$sThumbUrl = sprintf( '%s?src=%s&w=%d&h=%d&zc=1', Geko_Uri::getUrl( 'geko_thumb' ), urlencode( $sFile ), $iSize, $iSize );
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
		?><p>
			<label class="main">Upload Image</label> 
			<input type="file" id="user_photo" name="user_photo" _file_upload_dir="<?php echo $this->_sUploadDir; ?>" />
			<label class="side">(jpg, jpeg, gif, or png)</label><br />
			<span _bind_to="<?php echo $this->getPrefixWithSep(); ?>user_photo" _thumb_width="150" _thumb_height="200"></span>
		</p><?php
	}
	
	
		
	
}


