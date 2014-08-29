<?php

// static class container for WP $post object functions
// object oriented wrapper for a local $post object
class Geko_Wp_Post extends Geko_Wp_Entity
{
	private static $oPost = NULL;
	
	
	
	
	
	// set the $post object to work on
	public static function set( &$oPost ) {
		self::$oPost =& $oPost;
	}
	
	
	
	// get the global $post object if self::set() was not called
	public static function get() {
		
		global $post;
		
		if ( NULL == self::$oPost ) {
			return $post;
		} else {
			return self::$oPost;
		}
	}
	
	
	
	// reset self::$oPost
	public static function reset() {
		self::$oPost = NULL;
	}
	
	
	
	// boolean checking functions
	
	// check if current post is a page
	public static function is_page() {
		if ( 'page' == self::get()->post_type ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	// check if current post is a post
	public static function is_post() {
		if ( 'post' == self::get()->post_type ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	
	// always returns an array, but in most cases we just want a single value
	public static function get_post_custom_values( $sKey ) {
		
		$mValue = get_post_custom_values( $sKey, self::get()->ID );
		
		if ( is_array( $mValue ) ) {
			if ( count( $mValue ) == 1 ) {
				return $mValue[ 0 ];
			} else {
				return $mValue;
			}
		} else {
			return $mValue;
		}
	}
	
	
	
	// a smarter way to get an excerpt, use "post_excerpt" if it exists, otherwise use "post_content"
	// strips tags, specify truncation length, specify "ellipsis"
	
	// returns string
	public static function get_the_excerpt( $iTruncateLength = 250, $sEllipsis = '...' ) {
		if ( '' != self::get()->post_excerpt ) {
			return self::get()->post_excerpt;
		} else {
			$sText = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', self::get()->post_content );
			return Geko_String::truncate( strip_tags( $sText ), $iTruncateLength, ' ', $sEllipsis );
		}
	}

	// echo version
	public static function the_excerpt( $iTruncateLength = 250, $sEllipsis = '...' ) {
		echo self::get_the_excerpt( $iTruncateLength, $sEllipsis );
	}
	
	
	
	// appends a period to the title, unless it already ends with a punctuation mark
	
	// returns string
	public static function get_the_title_with_period() {
		
		$sLastChar = substr( self::get()->post_title, strlen( self::get()->post_title ) - 1 );
		$sPuncs = '!?,.';
		
		if ( FALSE !== strpos( $sPuncs, $sLastChar ) ) {
			return self::get()->post_title;
		} else {
			// add dot at the end
			return sprintf( '%s.', self::get()->post_title );
		}
	}
	
	// echo version
	public static function the_title_with_period() {
		echo self::get_the_title_with_period();
	}
	
	
	
	// see if author has first name and last name, if not use short name
	public static function get_the_author() {
		
		global $post;
		
		$sFirstName = get_the_author_firstname();
		$sLastName = get_the_author_lastname();
		
		if ( ( '' != $sFirstName ) && ( '' != $sLastName ) ) {
			return sprintf( '%s %s', $sFirstName, $sLastName );
		} else {
			return get_the_author();
		}
	}	
	
	// echo version
	public static function the_author() {
		global $post;
		echo self::get_the_author();
	}
	
	
	
	// echo version
	public static function the_content_only() {
		global $post;
		echo wpautop( get_the_content() );
	}
	
	
	
	// for testing
	public static function dump() {
		var_dump( self::get() );
	}
	
	
	
	
	//// implement concrete methods for post
	
	protected $_sEntityIdVarName = 'p';
	protected $_sEntitySlugVarName = 'name';
	
	protected $_sCategoryEntityClass = 'Geko_Wp_Category';
	protected $_sCategoryQueryClass = '';
	
	protected $_sTagEntityClass = 'Geko_Wp_Tag';
	protected $_sTagQueryClass = '';
	
	protected $_sMediaEntityClass = 'Geko_Wp_Media';
	protected $_sMediaQueryClass = '';
	
	protected $_sCommentEntityClass = 'Geko_Wp_Comment';
	protected $_sCommentQueryClass = '';
	
	protected $_sAuthorEntityClass = 'Geko_Wp_Author';
	protected $_sAuthorQueryClass = '';
	
	protected $_aPages = NULL;
	
	//
	public function __construct( $mEntity = NULL, $oQuery = NULL, $aData = array(), $aQueryParams = NULL ) {
		
		parent::__construct( $mEntity, $oQuery, $aData, $aQueryParams );
		
		$this->_sCategoryQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sCategoryEntityClass, '', '_Query', $this->_sCategoryQueryClass
		);
		
		$this->_sTagQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sTagEntityClass, '', '_Query', $this->_sTagQueryClass
		);
		
		$this->_sMediaQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sMediaEntityClass, '', '_Query', $this->_sMediaQueryClass
		);
		
		$this->_sCommentQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sCommentEntityClass, '', '_Query', $this->_sCommentQueryClass
		);
		
		$this->_sAuthorQueryClass = Geko_Class::resolveRelatedClass(
			$this->_sAuthorEntityClass, '', '_Query', $this->_sAuthorQueryClass
		);
	}
	
	//
	public function getEntityFromId( $iEntityId ) {
		
		if ( !is_array( $this->_aQueryParams ) ) {
			return get_post( $iEntityId );
		}
		
		return parent::getEntityFromId( $iEntityId );
	}
	
	/*
	// TO DO: there seems to be no native get_post_from_slug()
	public function getEntityFromSlug( $sEntitySlug ) {
		return NULL;
	}
	*/
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'title', 'post_title' )
			->setEntityMapping( 'slug', 'post_name' )
			->setEntityMapping( 'content', 'post_content' )
			->setEntityMapping( 'excerpt', 'post_excerpt' )
			->setEntityMapping( 'date_created', 'post_date' )
			->setEntityMapping( 'date_modified', 'post_modified' )
			->setEntityMapping( 'date_created_gmt', 'post_date_gmt' )
			->setEntityMapping( 'date_modified_gmt', 'post_modified_gmt' )
			->setEntityMapping( 'parent_entity_id', 'post_parent' )
		;
		
		return $this;
	}
	
	//
	public function retPermalink() {
		return get_permalink( $this->getId() );
	}
	
	//
	public function getTheEditUrl() {
		return get_edit_post_link( $this->getId() );
	}
	
	
	
	//
	public function getRawMeta( $sMetaKey ) {
		return get_post_meta( $this->getId(), $sMetaKey, TRUE );
	}
	
	//
	public function getMetaMulti( $sMetaKey ) {
		return get_post_meta( $this->getId(), $sMetaKey );
	}
	
	
	
	//// accessors for related entities
	
	//
	public function getCategoryEntityClass() {
		return $this->_sCategoryEntityClass;
	}
	
	//
	public function getCategoryQueryClass() {
		return $this->_sCategoryQueryClass;
	}
	
	//
	public function getTagEntityClass() {
		return $this->_sTagEntityClass;
	}
	
	//
	public function getTagQueryClass() {
		return $this->_sTagQueryClass;
	}
	
	//
	public function getMediaEntityClass() {
		return $this->_sMediaEntityClass;
	}
	
	//
	public function getMediaQueryClass() {
		return $this->_sMediaQueryClass;
	}

	//
	public function getCommentEntityClass() {
		return $this->_sCommentEntityClass;
	}
	
	//
	public function getCommentQueryClass() {
		return $this->_sCommentQueryClass;
	}
	
	//
	public function getAuthorEntityClass() {
		return $this->_sAuthorEntityClass;
	}
	
	//
	public function getAuthorQueryClass() {
		return $this->_sAuthorQueryClass;
	}
	
	
	
	//
	public function getCategories( $mParams = NULL ) {
		
		if ( ( $this->isValid() ) && ( 'post' == $this->getPostType() ) ) {
			
			if ( is_string( $mParams ) ) {
				$aParams = array();
				parse_str( $mParams, $aParams );
			} else {
				$aParams = is_array( $mParams ) ? $mParams : array();
			}
			
			$aCatIds = wp_get_post_categories( $this->getId(), $aParams );
			
			$aParams = array_merge( $aParams, array( 'include' => $aCatIds ) );
			
			// hack
			if ( $aParams[ 'exclude' ] ) {
				$aParams[ 'include' ] = array_diff( $aParams[ 'include' ], explode( ',', $aParams[ 'exclude' ] ) );
				unset( $aParams[ 'exclude' ] );
			}
			
			// another hack
			if ( $iAncestorId = $aParams[ 'child_of' ] ) {
				foreach ( $aParams[ 'include' ] as $i => $iCatId ) {
					if ( !cat_is_ancestor_of( $iAncestorId, $iCatId ) ) {
						unset( $aParams[ 'include' ][ $i ] );
					}
				}
				unset( $aParams[ 'child_of' ] );
			}
			
			// format as comma delimited string
			$aParams[ 'include' ] = implode( ',', $aParams[ 'include' ] );
			
			// if nothing to include, then return no categories
			if ( !$aParams[ 'include' ] ) $aParams[ 'number' ] = 0;
			
			return new $this->_sCategoryQueryClass( $aParams );
		}
		
		return new $this->_sCategoryQueryClass( NULL, FALSE );
	}
	
	//
	public function getCategory() {
		
		$oCat = NULL;
		
		// get the category the post belongs to; if more than one, use the first
		$aCats = $this->getCategories();
		if ( $aCats->count() > 0 ) $oCat = $aCats[ 0 ];
		
		$oCat = apply_filters( sprintf( '%s::getCategory', $this->_sEntityClass ), $oCat, $this );
		
		return $oCat;
	}
	
	//
	public function inCategory( $mArgs ) {
		return Geko_Wp_Category::in( $mArgs, $this->getId() );
	}
	
	//
	public function getTags( $mParams = NULL ) {
		
		$aTags = wp_get_post_tags( $this->getId(), $mParams );
		
		$oQuery = new $this->_sTagQueryClass( NULL, FALSE );
		$oQuery->setRawEntities( $aTags );
		
		return $oQuery;
	}
	
	//
	public function getAttachments( $mParams = array() ) {
		
		if ( is_string( $mParams ) ) parse_str( $mParams, $aParams );
		else $aParams = $mParams;
		
		$aParams = $this->modifyGetAttachmentParams( $aParams );
		return new $this->_sMediaQueryClass( $aParams, FALSE );
	}
	
	//
	protected function modifyGetAttachmentParams( $aParams ) {
		
		// assert post id
		$aParams[ 'p' ] = $this->getId();
		
		// image groups
		if ( $sKey = $aParams[ 'file_group' ] ) {
			
			$aParams[ 'has_file_ids' ] = TRUE;		// Hack!!!
			
			if ( !is_array( $aFileIds = $this->getMetaMemberIds( $sKey ) ) ) {
				$aFileIds = $this->getMetaFromJson( $sKey );
			}
			
			$aParams[ 'file_ids' ] = $aFileIds;
		}
		
		return $aParams;
	}
	
	//
	public function getComments( $mParams = array() ) {
		
		if ( is_string( $mParams ) ) parse_str( $mParams, $aParams );
		else $aParams = $mParams;
		
		$aParams = array_merge(
			array( 'post_id' => $this->getId() ),
			$aParams
		);
		
		return new $this->_sCommentQueryClass( $aParams, FALSE );
	}
	
	
	//
	public function getCommentCount() {
		return intval( $this->getEntityPropertyValue( 'comment_count' ) );
	}
	
	// !!! Depends on Geko_Wp_Query_Hooks_Comment::register()
	public function getLatestCommentDate( $sDateFormat = '' ) {
		return $this->dateFormat(
			$this->getEntityPropertyValue( 'comment_latest_date' ),
			$sDateFormat
		);
	}
	
	// !!! Depends on Geko_Wp_Query_Hooks_Comment::register()
	public function getLatestActivityDate( $sDateFormat = '' ) {
		return $this->dateFormat(
			$this->getEntityPropertyValue( 'latest_activity_date' ),
			$sDateFormat
		);
	}
	
	//
	public function getDefaultEntityValue() {
		
		global $wp_query;
		
		if ( Geko_Wp::is( 'single|page' ) ) {
			if ( is_array( $this->_aQueryParams ) ) {
				return $wp_query->post->ID;
			} else {
				return $wp_query->post;			
			}
		}
		
		return NULL;
	}
	
	// Hack!!!
	public function getTheContent() {
		
		global $id;
		
		$iOrigId = $id;
		
		$id = $this->getId();
		
		setup_postdata( (object) $this->getRawEntity() );
		
		/* */
		ob_start();
		the_content();
		$sRet = ob_get_contents();
		ob_end_clean();
		/* */
		
		$id = $iOrigId;
		
		return $sRet;
	}
	
	// 
	public function getTheExcerpt( $iLimit, $sBreak = ' ', $sPad = '...' ) {
		
		if ( $sExcerpt = $this->getExcerpt() ) {
			return $sExcerpt;
		} else {
			$sText = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $this->getContent() );
			return Geko_String::truncate(
				strip_tags( $sText ), $iLimit, $sBreak, $sPad
			);
		}
	}
	
	//
	public function getAuthor() {
		return new $this->_sAuthorEntityClass( $this->getPostAuthor() );
	}
	
	//
	public function getPage( $iPageNum, $bApplyAutoP = TRUE ) {
		
		if ( NULL === $this->_aPages ) {
		
			global $id, $pages;
			
			$iOrigId = $id;
			
			$id = $this->getId();
			
			setup_postdata( (object) $this->getRawEntity() );
			$this->_aPages = $pages;
			
			$id = $iOrigId;
		
		}
		
		$sRet = $this->_aPages[ $iPageNum ];
		if ( $bApplyAutoP ) $sRet = wpautop( $sRet, 1 );
		
		return $sRet;
	}
	
	//
	public function getNumPages() {
		$this->getPage( 0 );		// initialize
		return count( $this->_aPages );
	}
	
}


