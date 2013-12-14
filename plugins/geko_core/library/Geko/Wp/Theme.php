<?php

// static class container for WP functions for Geek Oracle themes
class Geko_Wp_Theme
{	
	private static $sThemePrefix = NULL;
	private static $aThemeData = NULL;
	
	
	
	// no constructor
	private function __construct()
	{
	
	}
	
	
	// get the theme prefix (ie: folder name)
	public static function getPrefix()
	{
		if (NULL === self::$sThemePrefix) {
			self::$sThemePrefix = Geko_String::rstr(TEMPLATEPATH, '/');
		}
		
		return self::$sThemePrefix;
	}
	
	
	// just do it for the current theme, and get the prefix while we're at it
	public static function get_current_data()
	{
		if (NULL === self::$aThemeData) {
			// get the current theme data
			self::$aThemeData = get_theme_data(TEMPLATEPATH . '/style.css');
			self::$aThemeData['Prefix'] = self::getPrefix();
		}
		
		return self::$aThemeData;
	}
	
	
	
	// override get_option() function to use theme namespacing
	public static function option($sKey)
	{
		return get_option(self::getPrefix() . '-' . $sKey);
	}
	
	
	
	// override update_option() function to use theme namespacing
	public static function update_option($sKey, $mValue)
	{
		return update_option(self::getPrefix() . '-' . $sKey, $mValue);
	}
	
}


