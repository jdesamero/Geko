<?php

//
class Geko_Wp_Integration_NavigationManagement_PageManager_Page
	extends Geko_Wp_NavigationManagement_PageManager_Page
	implements Geko_Integration_Client_SubscriberInterface, Geko_Integration_Service_ActionInterface
{
	
	protected static $aInstances = array();
	protected static $oResponseItem;
	
	
	
	//// static methods
	
	//
	public static function subscribe( $oWpClient ) {
		self::$oResponseItem = $oWpClient->setRequest( __CLASS__ );
		$oWpClient->setCallback( array(__CLASS__, 'setPagesNormStatic'), array() );
	}

	//
	public static function setPagesNormStatic() {
		if ( is_object(self::$oResponseItem) ) {
			foreach ( self::$aInstances as $oInstance ) {
				$oInstance->setPagesNorm( self::$oResponseItem->get() );
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
		.type-##type## { background-color: lightpink; border: solid 1px darkred; }
		<?php
	}
	
	// Wordpress specific code
	public static function exec() {
		
		$aPagesNorm = array();
		
		$aPages = query_posts(array(
			'post_type' => 'page',
			'showposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC'
		));
		
		foreach ($aPages as $oPage) {
			$aPagesNorm[ $oPage->ID ] = array(
				'title' => $oPage->post_title,
				'link' => get_permalink( $oPage->ID )
			);
		}
		wp_reset_query();
		
		return $aPagesNorm;
		
	}
	

}

