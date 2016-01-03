<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Integration/Page/Page.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Integration_Page_Page
	extends Geko_Wp_NavigationManagement_Page_Page
	implements Geko_Integration_Client_SubscriberInterface, Geko_Integration_Service_ActionInterface
{
	protected static $aPageIds = array();
	protected static $oResponseItem;
	

	
	//// static methods
	
	//
	public static function subscribe( $oWpClient ) {
		self::$oResponseItem = $oWpClient->setRequest( __CLASS__, array(self::$aPageIds) );
	}
	
	//
	public static function getPageIds()
	{
		return self::$aPageIds;
	}
		
	// Wordpress specific code
	public static function exec( $aPageIds ) {
		
		$aRes = array();
		foreach ($aPageIds as $i => $a) {
			// $a is unused
			$aRes[$i] = array(
				'title' => get_the_title( $i ),
				'link' => get_permalink( $i )
			);
		}
		
		return $aRes;
		
	}
	
	//
	public static function test()
	{
		print_r (self::$oResponseItem);
		print_r (self::$aPageIds);
	}
	
    
    
    
    //// object methods
    
    //
    public function setPageId($pageId)
    {
    	// collect page ids as they are set
    	self::$aPageIds[$pageId] = NULL;
        return parent::setPageId($pageId);
    }
	
    //
    public function getHref()
    {
    	if ( is_object(self::$oResponseItem) ) {
			$aRes = self::$oResponseItem->get();
			return $aRes[ $this->_pageId ]['link'];
		} else {
			return NULL;
		}
    }
	
	//
	public function getImplicitLabel()
	{
    	if ( is_object(self::$oResponseItem) ) {
			$aRes = self::$oResponseItem->get();
			return $aRes[ $this->_pageId ]['title'];	
		} else {
			return NULL;
		}
	}
	
	//
    public function isCurrentPage()
    {
		$oCommon = Geko_Integration_Common::getInstance();
		if ( 'wp-theme' == $oCommon->getAppKey() ) {
			return parent::isCurrentPage();
		} else {
			return FALSE;
		}
    }
	
	
}

