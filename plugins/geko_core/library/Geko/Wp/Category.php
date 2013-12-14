<?php

// static class category related stuff
// object oriented wrapper for a local $post object
class Geko_Wp_Category extends Geko_Wp_Entity
{
	
	
	// get the current category object
	// $sKey is either a category name or slug
	public static function get_cat($sKey)
	{
		// try using slug first
		$cat = get_term_by('slug', $sKey, 'category');
		if ($cat) {
			return $cat;
		}
		
		// try using category name
		$cat = get_term_by('name', $sKey, 'category');
		if ($cat) {
			return $cat;
		}
		
		return NULL;
	}
	
	// $sKey is either a category name or slug
	// the standard get_category_ID() function works with the category name only
	public static function get_ID($sKey)
	{
		$cat = self::get_cat($sKey);
		if ($cat) {
			return $cat->term_id;
		} else {
			return 0;
		}
	}
	
	// $sKey is either a category name or slug
	// the standard in_category() function works with the category name only
	private static function _in( $sKey, $iPostId = NULL )
	{
		global $post;
		
		if ( NULL === $iPostId ) $iPostId = $post->ID;
		
		if (empty($sKey)) return FALSE;
		
		$cat_ID = self::get_ID($sKey);
		
		$categories = get_object_term_cache( $iPostId, 'category' );
		
		if (FALSE === $categories) {
			$categories = wp_get_object_terms( $iPostId, 'category' );
		}
		
		$aCatKeys = array();
		foreach ($categories as $cat) {
			$aCatKeys[] = $cat->term_id;
		}
		
		if ( in_array($cat_ID, $aCatKeys) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	// allows to take a list of categories, either as comma delimited or as an array
	public static function in( $mArg, $iPostId = NULL )
	{
		if ( FALSE == is_array( $mArg ) ) {
			$mArg = explode( ',', $mArg );
		}
		
		foreach ( $mArg as $sKey ) {
			if ( self::_in( trim( $sKey ), $iPostId ) ) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	// get order query args from string with format eg:
	// company|title|asc;event|start_date|desc
	public static function getOrderQueryArgs($sRules, $sSlug = '')
	{
		$sQueryOrderArgs = '';
		
		$aRules = explode(';', $sRules);
		
		foreach ($aRules as $sRule) {
			
			$aRule = explode(',', $sRule);
			$sCatSlug = strtolower(trim($aRule[0]));
			
			if ('' != $sSlug) {
				$bArg = ($sSlug == $sCatSlug);
			} else {
				$bArg = Geko_Wp_Category::in($sCatSlug);
			}
			
			// ordering
			if ($bArg) {

				$sSortField = strtolower(trim($aRule[1]));
				$sSortDir = strtoupper(trim($aRule[2]));
				if ('' == $sSortDir) $sSortDir = 'ASC';
				
				$sQueryOrderArgs = '&orderby=' . $sSortField . '&order=' . $sSortDir;
			}
			
		}
		
		return $sQueryOrderArgs;
	}
	
	
	// get an imploded list of id's of all of the descendants of given category list
	public static function getDescendantCategoryIds($sCatList)
	{
		$sDescendantIds = '';
		$aParentIds = explode(',', $sCatList);
		foreach ($aParentIds as $iParentCatId) {
			$aDescendantIds = get_term_children(trim($iParentCatId), 'category');
			if ('' != $sDescendantIds) $sDescendantIds .= ',';
			$sDescendantIds .= implode(',', $aDescendantIds);
		}
		return $sDescendantIds;
	}
	
	
	
	
	
	//// implement concrete methods for category
	
	//
	public function getEntityFromId( $iEntityId )
	{
		return get_category( $iEntityId );
	}

	//
	public function getEntityFromSlug( $sEntitySlug )
	{
		if ( FALSE !== strpos( $sEntitySlug, '/' ) ) {
			return get_category_by_path( $sEntitySlug );
		} else {
			return get_category_by_slug( $sEntitySlug );
		}
	}

	//
	public function init()
	{
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
	public function getPermalink()
	{
		return get_category_link( $this->getId() );
	}
	
	
	//
	public function getDefaultEntityValue()
	{
		global $wp_query;
		
		if ( is_category() ) {
			
			// if category aliasing is activated, return the apparent category
			$oCatAlias = Geko_Wp_Category_Alias::getInstance();
			if ( $oCatAlias->getCalled_affix() ) {
				return $oCatAlias->getApparentCat()->getRawEntity();
			}
			
			return Geko_Wp_Category::get_ID( $wp_query->query_vars['category_name'] );
		}
		
		return NULL;
	}
	
	
	//
	public function getRawMeta( $sMetaKey )
	{
		$oMeta = Geko_Singleton_Abstract::getInstance( $this->_sMetaClass );
		
		return $oMeta->getInheritedValue(
			$this->getId(), $oMeta->getPrefixWithSep() . $sMetaKey
		);
	}
	
}


