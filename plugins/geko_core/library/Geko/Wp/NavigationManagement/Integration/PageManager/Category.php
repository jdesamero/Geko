<?php
/*
 * "geko_core/library/Geko/Wp/NavigationManagement/Integration/PageManager/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_NavigationManagement_Integration_PageManager_Category
	extends Geko_Wp_NavigationManagement_PageManager_Category
	implements Geko_Integration_Client_SubscriberInterface, Geko_Integration_Service_ActionInterface
{
	
	protected static $aInstances = array();
	protected static $oResponseItem;
	
	
	
	//// static methods
	
	//
	public static function subscribe( $oWpClient ) {
		self::$oResponseItem = $oWpClient->setRequest( __CLASS__ );
		$oWpClient->setCallback( array(__CLASS__, 'setCatsNormStatic'), array() );
	}

	//
	public static function setCatsNormStatic() {
		if ( is_object(self::$oResponseItem) ) {
			foreach ( self::$aInstances as $oInstance ) {
				$oInstance->setCatsNorm( self::$oResponseItem->get() );
			}
		}
	}
	
	
	//// other methods
	
	//
	public function __construct( $iIndex ) {
		parent::__construct( $iIndex );
		self::$aInstances[] = $this;
	}
	
	
	// has to be overridden to prevent native WP calls
	public function init() { }
	
	//
	public function outputStyle() {
		?>	
		.type-##type## { background-color: palegreen; border: solid 1px darkgreen; }
		<?php
	}
	
	// Wordpress specific code
	public static function exec() {
		
		$aCatsNorm = array();
		$aCats = get_categories( array('hide_empty' => FALSE) );
		foreach ($aCats as $oCat) {
			$aCatsNorm[ $oCat->cat_ID ] = array(
				'title' => $oCat->name,
				'link' => get_category_link( $oCat->cat_ID )
			);
		}
		
		return $aCatsNorm;
		
	}
	

}

