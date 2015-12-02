<?php
/*
 * "geko_core/library/Geko/Wp/Language/Manage/Post.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Language_Manage_Post extends Geko_Wp_Language_Manage
{
	protected $_aSubOptions = array();
	
	protected $_sFilterLangCode = '';
	protected $_sPostQueryClass = '';
	
	protected $_aPostCache = array();
	
	
	
	
	//
	public function add() {
		
		parent::add();
		
		Geko_Wp_Language_Manage_Post_QueryHooks::register();		
		
		$aPrefixes = array( 'Gloc_', 'Geko_Wp_' );
		
		$sPostQueryClass = Geko_Class::getBestMatch( $aPrefixes, array( 'Post_Query' ) );
		add_action( sprintf( '%s::init', $sPostQueryClass ), array( $this, 'initQuery' ) );
		
		$this->_sPostClass = Geko_Class::getBestMatch( $aPrefixes, array( 'Post' ) );
		
		
		return $this;
	}
	
	//
	public function initQuery( $oQuery ) {
		$oQuery->addPlugin( 'Geko_Wp_Language_Manage_Post_QueryPlugin' );
	}
	
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'delete_post', array( $this, 'deletePost' ) );		
		add_action( 'save_post', array( $this, 'savePost' ) );
		add_action( 'admin_init', array( $this, 'addPostMetabox' ) );
		
		add_filter( 'manage_posts_columns', array( $this, 'addCustomColumn' ) );
		add_filter( 'manage_pages_columns', array( $this, 'addCustomColumn' ) );
		
		add_action( 'manage_posts_custom_column', array( $this, 'addCustomColumnValues' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( $this, 'addCustomColumnValues' ), 10, 2 );
		
		add_action( 'admin_init_post_list', array( $this, 'modifyRequest' ) );
		add_action( 'admin_init_page_list', array( $this, 'modifyRequest' ) );
		
		// page
		add_action( 'admin_init_page_add', array( $this, 'filterPageAdminPages' ) );
		add_action( 'admin_init_page_edit', array( $this, 'filterPageAdminPages' ) );
		
		return $this;
	}
	
	//
	public function modifyRequest() {
		add_filter( 'request', array( $this, 'addQueryVars' ) );
	}
	
	
	//
	public function attachPage() { }
	
	
	
	////// accessors
	
	//
	public function getPost( $iPostId ) {
		
		if ( !$this->_aPostCache[ $iPostId ] ) {
			
			$this->_aPostCache[ $iPostId ] = call_user_func(
				array( $this->_sPostClass, 'getOne' ),
				array( 'p' => $iPostId )
			);
		}
		
		return $this->_aPostCache[ $iPostId ];
	}
	
	
	
	
	////// actions and filters
	
	
	//// details
	
	//
	public function addPostMetabox() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrl = strval( $oUrl );
		
		$sPostType = '';
		
		if ( FALSE !== strpos( $sUrl, 'wp-admin/post-new.php' ) ) {
			
			$sPostType = trim( $_GET[ 'post_type' ] );
			if ( !$sPostType ) $sPostType = 'post';
			
		} elseif ( FALSE !== strpos( $sUrl, 'wp-admin/post.php' ) ) {
			
			if ( $iPostId = intval( $_GET[ 'post' ] ) ) {
				$sPostType = get_post_type( $iPostId );
			}
			
		}
		
		if ( $sPostType ) {
			add_meta_box( 'geko-language', __( 'Language', 'geko-expiry_textdomain' ), array( $this, 'addPostSelector' ), $sPostType, 'side' );	
		}
	}
	
	//
	public function addPostSelector() {
		
		global $post;
		
		$iLangId = intval( $_GET[ 'post_lang_id' ] );
		$iLangGroupId = intval( $_GET[ 'post_lgroup_id' ] );
		$iPostId = intval( $_GET[ 'post' ] );
		$bNewSibling = ( !$iLangGroupId || !$iLangId ) ? FALSE : TRUE ;
		$sType = $post->post_type;
		
		$aLinks = $this->getSelectorLinks(
			$iLangGroupId,
			$iLangId,
			$iPostId,
			'post',
			array( 'type' => $sType )
		);
		
		// determine if a language is assigned to post
		if ( $aLinks ) {
			
			echo implode( ' | ', $aLinks );
			
			if ( $bNewSibling ) {			
				$this->echoLanguageHidden( $iLangGroupId, $iLangId );
			}
			
		} else {
			$this->echoLanguageSelect();
		}
		
	}
	
	//
	public function getSelExistLink( $aParams ) {
		return sprintf(
			'<a href="%s/wp-admin/post.php?action=edit&post=%d">%s</a>',
			Geko_Wp::getUrl(),
			$aParams[ 'obj_id' ],
			$aParams[ 'title' ]
		);
	}
	
	//
	public function getSelNonExistLink( $aParams ) {
		
		global $post;
		
		return sprintf(
			'<a href="%s/wp-admin/post-new.php?post_lgroup_id=%d&post_lang_id=%d%s">%s</a>',
			Geko_Wp::getUrl(),
			$aParams[ 'lgroup_id' ],
			$aParams[ 'lang_id' ],
			( 'post' != $aParams[ 'type' ] ) ? sprintf( '&post_type=%s', $aParams[ 'type' ] ) : '' ,
			$aParams[ 'title' ]
		);
	}
	
	
	
	
	//// listing
	
	//
	public function addCustomColumn( $aDefaults ) {
		
		// cb, title, author, categories, tags, comments, date
		
		$aReorder = array(
			'cb' => $aDefaults[ 'cb' ],
			'title' => $aDefaults[ 'title' ],
			'lang' => 'Language'
		);
		
		unset( $aDefaults[ 'cb' ] );
		unset( $aDefaults[ 'title' ] );
		
		return array_merge( $aReorder, $aDefaults );
	}
	
	//
	public function addCustomColumnValues( $sColumnName, $iId ) {
		
		if ( 'lang' == $sColumnName ) {
		
			global $post;
			static $oUrl = NULL;
			
			$oPost = $this->getPost( $post->ID );
			
			if ( $sLangCode = $oPost->getLangCode() ):
			
				if ( NULL === $oUrl ) $oUrl = new Geko_Uri();
				$oUrl->setVar( 'lang', $sLangCode );
				
				?><a href="<?php echo strval( $oUrl ); ?>"><?php $oPost->echoLangTitle(); ?></a><?php
			else:
				?>No Language<?php
			endif;
			
		}
		
	}
	
	//
	public function addQueryVars( $aQueryVars ) {
		
		$aQueryVars[ 'add_lang_fields' ] = 1;
		
		$oResolver = Geko_Wp_Language_Resolver::getInstance();
		
		if ( $sLangCode = $oResolver->getCurLang() ) {
			$aQueryVars[ $oResolver->getLangQueryVar() ] = $sLangCode;
		}
		
		return $aQueryVars;
	}
	
	
	//// commit
	
	//
	public function savePost( $iPostId, $aVals = NULL ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		// set vals
		if ( NULL === $aVals ) {
			
			// use $_POST array for values, minding the prefix
			$aVals = array();
			
			// list of recognized fields
			$aFields = array( 'geko_lang_id', 'geko_lgroup_id' );
			foreach ( $aFields as $sField ) {
				if ( isset( $_POST[ $sField ] ) ) {
					$aVals[ $sField ] = stripslashes( $_POST[ $sField ] );
				}
			}
			
		}
		
		// save post
		
		$oPost = get_post( $iPostId );
		
		if (
			( 'inherit' != $oPost->post_status ) && 
			( $iLangId = intval( $aVals[ 'geko_lang_id' ] ) )
		) {
			
			if ( !$iLangGroupId = intval( $aVals[ 'geko_lgroup_id' ] ) ) {
				
				// create a lang group
				$oDb->insert( '##pfx##geko_lang_groups', array(
					'type_id' => Geko_Wp_Options_MetaKey::getId( 'post' )
				) );
				
				// create a lang group member
				$iLangGroupId = $oDb->lastInsertId();
			}
			
			$oDb->insert( '##pfx##geko_lang_group_members', array(
				'lgroup_id' => $iLangGroupId,
				'obj_id' => $iPostId,
				'lang_id' => $iLangId
			) );
			
		}
		
		return TRUE;
	}
	
	// clean-up
	public function deletePost( $iPostId ) {
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'p.ID', 'ID' )
			->from( '##pfx##posts', 'p' )
		;
		
		$this->cleanUpEmptyLangGroups( 'post', strval( $oQuery ), $iPostId );
	}
	
	
	
	//// filter pages by language
	
	//
	public function filterPageAdminPages() {
		
		$sLangCode = '';
		
		if ( $iPostId = $_REQUEST[ 'post' ] ) {
			
			$oObj = Geko_Wp_Language_Member::getOne( array( 'obj_id' => $iPostId, 'type' => 'post' ), FALSE );
			if ( $oObj->isValid() ) $sLangCode = $oObj->getLangCode();
			
		} elseif ( $iLangId = $_REQUEST[ 'post_lang_id' ] ) {
			
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		}
		
		if ( $sLangCode ) $this->_sFilterLangCode = $sLangCode;
		
		add_filter( 'get_pages', array( $this, 'pageFilterQuery' ), 10, 2 );
	}
	
	// works with the 'get_pages' filter
	public function pageFilterQuery( $aPages, $aArgs ) {
		
		$this->getLanguages();		// initialize lang array
		
		if ( $this->_sFilterLangCode ) $aArgs[ 'lang' ] = $this->_sFilterLangCode;
		
		if ( $sLangCode = $aArgs[ 'lang' ] ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$bLangIsDefault = ( self::$oDefaultLang->getSlug() == $sLangCode );
			
			$oMetaKeyQuery = new Geko_Sql_Select();
			$oMetaKeyQuery
				->field( 'mk.mkey_id', 'mkey_id' )
				->from( '##pfx##geko_meta_key', 'mk' )
				->where( 'mk.meta_key = ?', 'post' )
			;
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'm.obj_id', 'obj_id' )
				->from( '##pfx##geko_lang_group_members', 'm' )
				->joinLeft( '##pfx##geko_lang_groups', 'g' )
					->on( 'g.lgroup_id = m.lgroup_id' )
				->joinLeft( '##pfx##geko_languages', 'l' )
					->on( 'l.lang_id = m.lang_id' )
				->where( 'g.type_id = ?', $oMetaKeyQuery )
				->where( sprintf( 'l.code %s= ?', ( $bLangIsDefault ? '!' : '' ) ), $sLangCode )
			;
			
			$aPageIds = $oDb->fetchCol( strval( $oQuery ) );
			
			$aFiltered = array();
			
			foreach ( $aPages as $oPage ) {
				
				$bInArray = in_array( $oPage->ID, $aPageIds );
				
				if (
					( $bLangIsDefault && !$bInArray ) || 
					( !$bLangIsDefault && $bInArray )
				) {
					$aFiltered[] = $oPage;
				}
			}
			
			$aPages = $aFiltered;
			
		}
		
		return $aPages;	
	}

	
}


