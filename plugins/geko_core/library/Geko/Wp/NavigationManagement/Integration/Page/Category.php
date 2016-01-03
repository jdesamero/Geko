<?php
/*
 * "geko_navigation_management/includes/library/Geko/Wp/NavigationManagement/Integration/Page/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Integration_Page_Category
	extends Geko_Wp_NavigationManagement_Page_Category
	implements Geko_Integration_Client_SubscriberInterface, Geko_Integration_Service_ActionInterface
{
	protected static $aCatIds = array();
	protected static $oResponseItem;
	

	
	//// static methods
	
	//
	public static function subscribe( $oWpClient ) {
		self::$oResponseItem = $oWpClient->setRequest( __CLASS__, array(self::$aCatIds) );
	}
	
	//
	public static function getCatIds()
	{
		return self::$aCatIds;
	}
		
	// Wordpress specific code
	public static function exec( $aCatIds ) {
		
		$aRes = array();
		foreach ($aCatIds as $i => $a) {
			
			$oCat = get_category(  $i );
			if ( is_object( $oCat ) ) {
				$aRes[$i] = array(
					'title' => $oCat->name,
					'link' => get_category_link( $i )
				);
			}
			
		}
		
		return $aRes;
		
	}
	
	//
	public static function test()
	{
		print_r (self::$oResponseItem);
		print_r (self::$aCatIds);
	}
	
    
    
    
    //// object methods
    
    //
    public function setCatId($catId)
    {
    	// collect page ids as they are set
    	self::$aCatIds[$catId] = NULL;
        return parent::setCatId($catId);
    }
	
    //
    public function getHref()
    {
    	if ( is_object(self::$oResponseItem) ) {
			$aRes = self::$oResponseItem->get();
			return $aRes[ $this->_catId ]['link'];
		} else {
			return NULL;
		}
    }
	
	//
	public function getImplicitLabel()
	{
    	if ( is_object(self::$oResponseItem) ) {
			$aRes = self::$oResponseItem->get();
			return $aRes[ $this->_catId ]['title'];	
		} else {
			return NULL;
		}
	}
	
	//
	public function isCurrentCategory()
	{
		$oCommon = Geko_Integration_Common::getInstance();
		if ( 'wp-theme' == $oCommon->getAppKey() ) {
			return parent::isCurrentCategory();
		} else {
			return FALSE;
		}
	}
	
}


