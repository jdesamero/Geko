<?php

//
class Gloc_Layout_Widgets extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Pages:'
	);
	
	
	
	//
	public function filterTitle( $sTitle ) {
		return Geko_Wp::getTitleTag( $sTitle );	
	}
	
	//
	public function filterBodyClass( $sClass ) {
		return Geko_Wp::getBodyClass( $sClass );
	}

	//
	public function filterPostClass( $sClass ) {
		return Geko_Wp::getPostClass( $sClass );	
	}
	
	//
	public function filterDefaultQueryParams( $aParams = array() ) {
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		
		if ( $sLangCode = $oResolver->getCurLang( FALSE ) ) {
			$aParams[ $oResolver->getLangQueryVar() ] = $sLangCode;
		}
		
		return $aParams;
	}
	
	//
	public function echoPagination() {
		
		$aArgs = func_get_args();
		
		?><div class="navigation"><?php
		
			if ( $sNavHtml = Geko_Wp_Ext_PageNavi::get( $aArgs[ 0 ] ) ) {
				echo $sNavHtml;
			} else {
				posts_nav_link();
			}
			
		?></div><?php		
	}
	
	
	
	//
	public function echoNavMenu() {
		
		$aArgs = func_get_args();
		
		if ( class_exists( 'Geko_Wp_NavigationManagement' ) ) {
			
			call_user_func_array( array( Geko_Wp_NavigationManagement::getInstance(), 'renderMenu' ), $aArgs );
		
		} elseif ( function_exists( 'wp_nav_menu' ) ) {
			
			// print_r( $aParams );
			
			$aWpNav = array();
			
			list( $sLocation, $aGekoNav ) = $aParams;
			
			$aWpNav[ 'theme_location' ] = $sLocation;
			if ( isset( $aGekoNav[ 'renderDepth' ] ) ) {
				$aWpNav[ 'depth' ] = intval( $aGekoNav[ 'renderDepth' ] ) + 1;
			}
			
			wp_nav_menu( $aWpNav );
			
		}
	}
	
	//
	public function echoNavBreadcrumb() {
		
		$aParams = func_get_args();
		
		if ( class_exists( 'Geko_Wp_NavigationManagement' ) ) {
			call_user_func_array( array( Geko_Wp_NavigationManagement::getInstance(), 'renderBreadcrumb' ), $aParams );
		} else {
			// TO DO: use native wordpress nav breadcrumbs?
		}
	}
	
	//
	public function echoNavClassChain() {
		
		$aParams = func_get_args();
		
		if ( class_exists( 'Geko_Wp_NavigationManagement' ) ) {
			call_user_func_array( array( Geko_Wp_NavigationManagement::getInstance(), 'renderClassChain' ), $aParams );
		} else {
			// TO DO: use native wordpress nav breadcrumbs?
		}
	}
	
	//
	public function echoNavLang() {
		if ( class_exists( 'Geko_Wp_NavigationManagement_Language' ) ) {
			Geko_Wp_NavigationManagement_Language::getInstance()->render();
		}
	}
	
	
	
	//
	public function echoCommentsTemplate() {
		the_post();
		comments_template();
	}
	
	//
	public function echoLinkPages() {
		wp_link_pages( array(
			'before' => sprintf( '<div class="page-link">%s', $this->l_101() ),
			'after' => '</div>',
			'next_or_number' => 'number'
		) );
	}
	
	//
	public function getSearchTerm() {
		return wp_specialchars( stripslashes( $_GET[ 's' ] ), TRUE );
	}
	
	//
	public function echoHiddenSearchFields() {
		if ( class_exists( 'Geko_Wp_Language_Resolver' ) ) {
			Geko_Wp_Language_Resolver::getInstance()->echoLangHiddenField();
		}
	}
	
}


