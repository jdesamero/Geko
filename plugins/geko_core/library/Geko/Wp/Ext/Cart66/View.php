<?php

//
class Geko_Wp_Ext_Cart66_View extends Geko_Singleton_Abstract
{
	
	protected $_sThisFile = '';
	protected $_sInstanceClass = NULL;
	
	protected $_aParams = array();
	
	protected $_sCartImgPath = NULL;
	
	
	
	
	//
	protected function __construct() {
		
		$this->_sInstanceClass = get_class( $this );
		
		if (
			( NULL === $this->_sCartImgPath ) && 
			( $sCartImgPath = $this->getVal( 'cart_images_url' ) )
		) {
			
			if ( 0 !== $sCartImgPath && stripos( strrev( $sCartImgPath ), '/' ) ) {
				$sCartImgPath .= '/';
			}
			
			$this->_sCartImgPath = $sCartImgPath;
		}
		
	}
	
	//
	public function logMsg( $sLine, $sTitle, $sDetails ) {
		
		$sMsg = sprintf( '[%s - line %s] %s', basename( $this->_sThisFile ), $sLine, $sTitle );
		
		if ( $sDetails ) {
			$sMsg = sprintf( '%s: %s', $sMsg, $sDetails );
		}
		
		Cart66Common::log( $sMsg );
		
		return $this;
	}
	
	
	//
	public function init( $aParams ) {
		$this->_aParams = $aParams;
		return $this;
	}
	
	//
	public function setParam( $sKey, $mValue ) {
		$this->_aParams[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function getParam( $sKey ) {
		return $this->_aParams[ $sKey ];
	}
	
	//
	public function getCartImgPath() {
		return $this->_sCartImgPath;
	}
	
	
	
	
	// hook method
	public function render() { }
	
	
	
	//// link
	
	//
	public function getLink( $subject, $path, $var ) {
		
		$page = get_page_by_path( $path );
		$link = get_permalink( $page );
		
		$oUrl = new Geko_Uri( $link );
		$oUrl->setVar( $var, $subject->$var );
		
		return strval( $oUrl );
	}
	
	//
	public function echoLink( $item, $path, $var ) {
		echo $this->getLink( $item, $path, $var );
	}
	
	
	//// string translation
	
	//
	public function _t( $sMsg ) {
		return __( $sMsg, 'cart66' );
	}
	
	//
	public function _e( $sMsg ) {
		return _e( $sMsg, 'cart66' );
	}
	
	
	//// currency
	
	//
	public function getCurr( $val, $html ) {
		return Cart66Common::currency( $val, $html );
	}
	
	//
	public function echoCurr( $val, $html ) {
		echo Cart66Common::currency( $val, $html );
	}
	
	
	//// setting value
	
	//
	public function getVal( $key ) {
		return Cart66Setting::getValue( $key );
	}
	
	//
	public function echoVal( $key ) {
		echo Cart66Setting::getValue( $key );
	}
	
	
	//// session value
	
	//
	public function transSessKey( $sKey ) {
		$sPfx = ( 0 === strpos( $sKey, '_' ) ) ? 'cart66' : 'Cart66' ;
		return $sPfx . $sKey;
	}
	
	//
	public function getSess( $sKey ) {
		return Cart66Session::get( $this->transSessKey( $sKey ) );
	}

	//
	public function setSess( $sKey, $mVal ) {
		Cart66Session::set( $this->transSessKey( $sKey ), $mVal );
		return $this;
	}
	
	//
	public function echoSess( $sKey ) {
		echo Cart66Session::get( $this->transSessKey( $sKey ) );
	}
	
	//
	public function dropSess( $sKey ) {
		Cart66Session::drop( $this->transSessKey( $sKey ) );	
		return $this;
	}
	
	
	
	
	//// shared widgety things
	
	//
	public function displayNotification( $sKey, $sMessage, $sType = '' ) {
		
		$aClasses = array( 'alert-message', 'Cart66AjaxMessage' );
		
		if ( 'error' == $sType ) {
			$aClasses[] = 'alert-error';
		}
		
		$sClass = implode( ' ', $aClasses );
		
		if ( $this->getSess( $sKey ) ): ?>
			<div class="<?php echo $sClass; ?>">
				<p style="text-align: center;"><?php $this->_e( $sMessage ); ?></p>
			</div>
			<br clear="all" />
			<?php
			$this->dropSess( $sKey );
		endif;
		
		return $this;
	}
	
	
	//
	public function displayContinueShoppingBtn( $sContinueShoppingImg = '', $sTargetTag = '' ) {
		
		if ( $sContinueShoppingImg ): ?>
			<a href="<?php $this->echoSess( 'LastPage' ); ?>" class="Cart66CartContinueShopping" <?php echo $sTargetTag; ?> ><img src="<?php echo $sContinueShoppingImg; ?>" /></a>
		<?php else: ?>
			<a href="<?php $this->echoSess( 'LastPage' ); ?>" class="Cart66ButtonSecondary Cart66CartContinueShopping" title="<?php $this->_e( 'Continue Shopping' ); ?>" <?php echo $sTargetTag; ?> ><?php $this->_e( 'Continue Shopping' ); ?></a>
		<?php endif;
		
	}
	
	
}


