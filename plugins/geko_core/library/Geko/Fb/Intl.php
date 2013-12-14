<?php

//
class Geko_Fb_Intl
{
	const KEYS = '__keys';
	const HASH = '__hash';
	
	//
	private static $sCurrLocale = array();
	private static $aTrans = array();
	
	//
	public static function init() {
		
		$sCurrUserLocale = '';
		if ( FB_USER_ID ) {
			// TO DO: Make this a singleton
			$oCurrUser = new Geko_Fb_User( FB_USER_ID );
			$sCurrUserLocale = $oCurrUser->getLocale();
		}
		
		self::$sCurrLocale = Geko_String::coalesce(
			$sCurrUserLocale,
			$_GET['locale'],
			$_REQUEST['fb_sig_locale'],
			FB_APP_DEFAULT_LOCALE
		);
	}
	
	//
	public static function addTrans( $sLocale, $aValues ) {
		
		self::$aTrans[ $sLocale ] = $aValues;
		
		if ( self::KEYS == $sLocale ) {
			self::$aTrans[ self::HASH ] = array_flip( $aValues );
		}
		
	}
	
	//
	public static function getCurrLocale() {
		return self::$sCurrLocale;
	}
	
	
	//
	public static function getText( $sKey )
	{
		// the value of $sKey is typically the exact text for the default language
		
		// translate $sKey into a "short key" and see if there is a corresponding entry in the current language
		if (
			( $sTransKey = self::$aTrans[ self::KEYS ][ $sKey ] ) && 
			( $sTranslated = self::$aTrans[ self::$sCurrLocale ][ $sTransKey ] )
		) {
			return $sTranslated;
		}
		
		// use $sKey as a "short key" and see if there is a corresponding entry in the current language
		if ( $sTranslated = self::$aTrans[ self::$sCurrLocale ][ $sKey ] ) {
			return $sTranslated;
		}
		
		// if the default locale is the same as the current locale, use $sKey as a "short key" and see if there is a corresponding entry in the "hash" array
		if (
			( FB_APP_DEFAULT_LOCALE == self::$sCurrLocale ) && 
			( $sTranslated = self::$aTrans[ self::HASH ][ $sKey ] )
		) {
			return $sTranslated;
		}
		
		// default, simply return $sKey (no translations were performed)
		return $sKey;
	}
	
	//
	public static function getTextAll( $sKey )
	{
		$aRet = array();
		foreach ( self::$aTrans as $sLocale => $aValues ) {
			if ( $mValue = $aValues[ $sKey ] ) {
				$aRet[ $sLocale ] = $mValue;
			}
		}
		return $aRet;
	}
	
}


