<?php
/*
 * "geko_core/library/Geko/Wp/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * static class category related stuff
 * object oriented wrapper for a local $post object
 */

//
class Geko_Wp_Category extends Geko_Wp_Entity
{
	
	
	// get the current category object
	// $sKey is either a category name or slug
	public static function get_cat( $sKey, $bReturnId = FALSE ) {
		
		// try using slug first
		$oCat = get_term_by( 'slug', $sKey, 'category' );
		
		// try using category name
		if ( !$oCat ) {
			$oCat = get_term_by( 'name', $sKey, 'category' );
		}
		
		return ( $bReturnId ) ?
			( ( $oCat ) ? $oCat->term_id : 0 ) : 
			( ( $oCat ) ? $oCat : NULL )
		;
	}
	
	// $sKey is either a category name or slug
	// the standard get_category_ID() function works with the category name only
	public static function get_ID( $mKey ) {
		
		if ( is_int( $mKey ) ) {
			return $mKey;
		}
		
		if ( is_array( $mKey ) ) {
			
			$aRet = array();
			
			foreach ( $mKey as $sKey ) {
				$aRet[] = self::get_ID( $sKey );
			}
			
			return $aRet;
		}
		
		return self::get_cat( $mKey, TRUE );		
	}
	
	// $sKey is either a category name or slug
	// the standard in_category() function works with the category name only
	private static function _in( $sKey, $iPostId = NULL ) {
		
		global $post;
		
		if ( NULL === $iPostId ) $iPostId = $post->ID;
		
		if ( empty( $sKey ) ) return FALSE;
		
		$iCatId = self::get_ID( $sKey );
		
		$aCats = get_object_term_cache( $iPostId, 'category' );
		
		if ( FALSE === $aCats ) {
			$aCats = wp_get_object_terms( $iPostId, 'category' );
		}
		
		$aCatKeys = array();
		foreach ( $aCats as $oCat ) {
			$aCatKeys[] = $oCat->term_id;
		}
		
		if ( in_array( $iCatId, $aCatKeys ) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	// allows to take a list of categories, either as comma delimited or as an array
	public static function in( $mArg, $iPostId = NULL ) {
		
		if ( is_int( $mArg ) ) {
			
			if ( self::_in( $mArg, $iPostId ) ) {
				return TRUE;
			}
		}
		
		if ( FALSE == is_array( $mArg ) ) {
			$mArg = explode( ',', $mArg );
		}
		
		foreach ( $mArg as $sKey ) {
			
			if ( is_string( $sKey ) ) {
				$sKey = trim( $sKey );
			}
			
			if ( self::_in( $sKey, $iPostId ) ) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	// get order query args from string with format eg:
	// company|title|asc;event|start_date|desc
	public static function getOrderQueryArgs( $sRules, $sSlug = '' ) {
		
		$sQueryOrderArgs = '';
		
		$aRules = explode( ';', $sRules );
		
		foreach ( $aRules as $sRule ) {
			
			$aRule = explode( ',', $sRule );
			$sCatSlug = strtolower( trim( $aRule[ 0 ] ) );
			
			if ( '' != $sSlug ) {
				$bArg = ( $sSlug == $sCatSlug );
			} else {
				$bArg = self::in( $sCatSlug );
			}
			
			// ordering
			if ( $bArg ) {

				$sSortField = strtolower( trim( $aRule[ 1 ] ) );
				$sSortDir = strtoupper( trim( $aRule[ 2 ] ) );
				if ( '' == $sSortDir ) $sSortDir = 'ASC';
				
				$sQueryOrderArgs = sprintf( '&orderby=%s&order=%s', $sSortField, $sSortDir );
			}
			
		}
		
		return $sQueryOrderArgs;
	}
	
	
	// get an imploded list of id's of all of the descendants of given category list
	public static function getDescendantCategoryIds( $sCatList ) {
		
		$sDescendantIds = '';
		$aParentIds = explode( ',', $sCatList );
		
		foreach ( $aParentIds as $iParentCatId ) {
			
			$aDescendantIds = get_term_children( trim( $iParentCatId ), 'category' );
			
			if ( '' != $sDescendantIds ) $sDescendantIds .= ',';
			
			$sDescendantIds .= implode( ',', $aDescendantIds );
		}
		
		return $sDescendantIds;
	}
	
	
	
	
	
	//// implement concrete methods for category
	
	//
	public function getEntityFromId( $iEntityId ) {
		return get_term( $iEntityId );
	}
	
	//
	public function getEntityFromSlug( $sEntitySlug ) {
		
		// TO DO: ??????
		
		if ( FALSE !== strpos( $sEntitySlug, '/' ) ) {
			return get_category_by_path( $sEntitySlug );
		} else {
			return get_category_by_slug( $sEntitySlug );
		}
	}

	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'term_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'content', 'description' )
			->setEntityMapping( 'excerpt', 'description' )
			->setEntityMapping( 'parent_entity_id', 'category_parent' )
		;
		
		return $this;
	}
	
	//
	public function getPermalink() {
		return get_term_link( $this->_oEntity );
	}
	
	
	//
	public function getDefaultEntityValue() {
		
		global $wp_query;
		
		$aQv = $wp_query->query_vars;
		
		
		if ( is_category() ) {
			
			// if category aliasing is activated, return the apparent category
			$oCatAlias = Geko_Wp_Category_Alias::getInstance();
			if ( $oCatAlias->getCalledInit() ) {
				return $oCatAlias->getApparentCat()->getRawEntity();
			}
			
			return self::get_ID( $aQv[ 'category_name' ] );
		
		} elseif ( $sTaxonomy = $aQv[ 'taxonomy' ] ) {
			
			return get_term_by( 'slug', $aQv[ 'term' ], $sTaxonomy );
		}
		
		return NULL;
	}
	
	
	//
	public function getRawMeta( $sMetaKey ) {
		
		$oMeta = Geko_Singleton_Abstract::getInstance( $this->_sMetaClass );
		
		// can't use $this->getId(), will cause a nasty infinite loop
		return $oMeta->getInheritedValue(
			$this->getEntityPropertyValue( 'id' ), sprintf( '%s%s', $oMeta->getPrefixWithSep(), $sMetaKey )
		);
	}
	
	
}


