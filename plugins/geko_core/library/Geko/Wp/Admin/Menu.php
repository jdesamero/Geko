<?php

class Geko_Wp_Admin_Menu
{
	
	protected static $aMenuItems = array();
	
	protected static $aTabGroups = array();
	protected static $aTabLookup = array();
	
	
	
	//// entity subs
	
	//
	public static function addMenu( $sHandle, $sMenuTitle, $sUrl, $aParams = array() ) {
		
		$oCurUrl = new Geko_Uri();
		$oMenuUrl = new Geko_Uri( $sUrl );
		
		if ( !isset( $aParams[ 'current' ] ) ) {
			$bCurrent = $oMenuUrl->sameVars( $oCurUrl );
		} else {
			$bCurrent = $aParams[ 'current' ];
		}
		
		if (
			( FALSE === strpos( $sUrl, 'http://' ) ) && 
			( FALSE === strpos( $sUrl, 'https://' ) )
		) {
			$sUrl = Geko_Wp::getUrl() . $sUrl;
		}
		
		self::$aMenuItems[ $sHandle ][] = array(
			'menu_title' => $sMenuTitle,
			'url' => $sUrl,
			'current' => $bCurrent,
			'params' => $aParams
		);
	}
	
	//
	public static function showMenu( $sHandle, $sStyle = 'filter' ) {
		
		if (
			isset( self::$aMenuItems[ $sHandle ] ) && 
			count( self::$aMenuItems[ $sHandle ] ) > 1
		) {
			
			$aMenuItems = self::$aMenuItems[ $sHandle ];
			
			if ( 'filter' == $sStyle ):
				?>
				<div class="filter">
					<ul class="subsubsub">
						<?php foreach ( $aMenuItems as $i => $aMenu ):
							$sDelim = ( $i ) ? '| ' : '';
							$sCurrentCssClass = ( $aMenu[ 'current' ] ) ? 'class="current"' : '';
							?><li><?php echo $sDelim; ?><a <?php echo $sCurrentCssClass; ?> href="<?php echo $aMenu[ 'url' ]; ?>"><span><?php echo $aMenu[ 'menu_title' ]; ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<br class="clear" />
				<?php
			endif;
		
		}
	}
	
	
	//// nav tabs
	
	// register the tab group and create a lookup array
	public static function addTabGroup( $aTabGroup ) {
		self::$aTabGroups[] = $aTabGroup;
		$iPos = count( self::$aTabGroups ) - 1;
		foreach ( $aTabGroup as $sHandle ) {
			self::$aTabLookup[ $sHandle ] = $iPos;
		}
	}
	
	// get tab group for the handle, if it exists
	public static function inTabGroup( $sHandle ) {
		return ( array_key_exists( $sHandle, self::$aTabLookup ) ) ? TRUE : FALSE ;
	}
	
	// basically, the first item of the tab group
	public static function getTabParent( $sHandle ) {
		if ( self::inTabGroup( $sHandle ) ) {
			$iPos = self::$aTabLookup[ $sHandle ];
			return self::$aTabGroups[ $iPos ][ 0 ];
		}
		return NULL;
	}
	
	//
	public static function showNavTabs( $sHandle ) {
		if ( self::inTabGroup( $sHandle ) ) {
			$iPos = self::$aTabLookup[ $sHandle ];
			$aTabGroup = self::$aTabGroups[ $iPos ];
			?>
			<h2 class="nav-tab-wrapper">
				<?php foreach( $aTabGroup as $sNavHandle ):
					$oMng = Geko_Singleton_Abstract::getInstance( $sNavHandle );
					$sActiveClass = ( $sNavHandle == $sHandle ) ? 'nav-tab-active' : '';
					?><a class="nav-tab <?php echo $sActiveClass; ?>" href="<?php echo $oMng->getAdminUrl(); ?>"><?php echo $oMng->getPageTitle(); ?></a>
				<?php endforeach; ?>
			</h2>
			<?php
		}
	}
	
	
}


