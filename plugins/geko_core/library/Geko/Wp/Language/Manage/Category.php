<?php
/*
 * "geko_core/library/Geko/Wp/Language/Manage/Category.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Language_Manage_Category extends Geko_Wp_Language_Manage
{
	
	protected $_aSubOptions = array();
	
	protected $_sFilterLangCode = '';
	
	
	
	//// accessors
	
	// HACK!!!
	public function _getCatId() {
		return intval( $_GET[ 'tag_ID' ] );
	}
	
	
	
	//
	public function add() {
		
		parent::add();
		
		add_action( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
		
		return $this;
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		// category
		add_action( 'admin_category_add_fields_pq', array( $this, 'addCategorySelector' ) );
		add_action( 'admin_category_edit_fields_pq', array( $this, 'editCategorySelector' ) );
		
		add_action( 'create_term', array( $this, 'saveCategory' ), 10, 2 );
		add_action( 'edit_terms', array( $this, 'saveCategory' ), 10, 2 );
		
		add_action( 'admin_init', array( $this, 'saveCategorySibling' ) );
		add_action(	'delete_term', array( $this, 'deleteCategory' ), 10, 2 );
		
		add_action( 'admin_init_category_edit', array( $this, 'filterCatEditAdminCategories' ) );
		add_action( 'admin_init_category_list', array( $this, 'filterCatListAdminCategories' ) );
		add_action( 'admin_head_category_list', array( $this, 'addCategorySelectorJs' ) );
		
		// post
		add_action( 'admin_init_post_add', array( $this, 'filterPostAdminCategories' ) );
		add_action( 'admin_init_post_edit', array( $this, 'filterPostAdminCategories' ) );
		
		
		return $this;
	}
	
	
	//
	public function attachPage() { }

	
	//// error message handling
	
	//
	protected function getNotificationMsgs() {
		return array(
			'm101' => 'Category was added successfully.',
			'm102' => 'Category was updated successfully.'
		);
	}
	
	
	
	//// category hook methods
	
	
	//
	public function addCategorySelectorJs() {
		
		$oUrl = new Geko_Uri();
		$oUrl->unsetVar( 'cat_lang_id' );
		
		$aJsonParams = array(
			'thisurl' => strval( $oUrl )
		);
		
		?><script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				$( '#geko_lang_id' ).change( function() {
					window.location = oParams.thisurl + '&cat_lang_id=' + $( this ).val();
				} );
				
			} );
			
		</script><?php
		
	}
	
	
	//
	public function addCategorySelector( $oPq ) {
		
		$aVals = array();
		if ( $iLangId = $_REQUEST[ 'cat_lang_id' ] ) {
			$aVals[ 'geko_lang_id' ] = $iLangId;
		}
		
		$oPq->find( 'form' )->prepend( sprintf(
			'<div class="form-field">
				<label for="geko_lang_id">Language</label>
				%s
				<input type="hidden" name="geko_lgroup_id" value="%d" />
			</div>',
			Geko_Html::populateForm( $this->getLanguageSelect(), $aVals ),
			intval( $_REQUEST[ 'cat_lgroup_id' ] )
		 ) );
		
		return $oPq;
	}
	
	//
	public function editCategorySelector( $oPq ) {
		
		$iLangId = intval( $_GET[ 'cat_lang_id' ] );
		$iLangGroupId = intval( $_GET[ 'cat_lgroup_id' ] );
		$iCatId = $this->_getCatId();
		$bNewSibling = ( !$iLangGroupId || !$iLangId ) ? FALSE : TRUE;
		
		$aLinks = $this->getSelectorLinks(
			$iLangGroupId,
			$iLangId,
			$iCatId,
			'category'
		);
		
		// determine if a language is assigned to category
		if ( $aLinks ) {
			
			$sField .= implode( ' | ', $aLinks );
			
			if ( $bNewSibling ) {
				$sField .= $this->getLanguageHidden( $iLangGroupId, $iLangId );
			}
			
		} else {
			$sField .= $this->getLanguageSelect();
		}
		
		// manipulate $oPq
		$oPq->prepend( $this->getNotificationHtml() );
		
		$oPq->find( 'form#edittag > table' )->prepend( sprintf( '
			<tr class="form-field">
				<th valign="top" scope="row"><label for="geko_lang_id">Language</label></th>
				<td>%s</td>
			</tr>
		', $sField ) );
				
		if ( ( 'edit' == $_GET[ 'action' ] ) && ( $this->_getCatId() ) ) {
			$oUrl = new Geko_Uri();
			$oPq->find( 'input[name="_wp_original_http_referer"]' )->val( strval( $oUrl ) );
		}
		
		return $oPq;
	}
	
	//
	public function getSelExistLink( $aParams ) {
		
		$sLink = '<a href="%s/wp-admin/edit-tags.php?action=edit&taxonomy=%s&post_type=%s&tag_ID=%d">%s</a>';		
		
		$sTaxonomy = trim( $_GET[ 'taxonomy' ] );
		if ( !$sTaxonomy ) $sTaxonomy = 'category';
		
		$sPostType = trim( $_GET[ 'post_type' ] );
		if ( !$sPostType ) $sPostType = 'post';
				
		return sprintf( $sLink, Geko_Wp::getUrl(), $sTaxonomy, $sPostType, $aParams[ 'obj_id' ], $aParams[ 'title' ] );
	}
	
	//
	public function getSelNonExistLink( $aParams ) {

		$sLink = '<a href="%s/wp-admin/edit-tags.php?taxonomy=%s&post_type=%s&cat_lgroup_id=%d&cat_lang_id=%d">%s</a>';		

		$sTaxonomy = trim( $_GET[ 'taxonomy' ] );
		if ( !$sTaxonomy ) $sTaxonomy = 'category';
		
		$sPostType = trim( $_GET[ 'post_type' ] );
		if ( !$sPostType ) $sPostType = 'post';
		
		return sprintf( $sLink, Geko_Wp::getUrl(), $sTaxonomy, $sPostType, $aParams[ 'lgroup_id' ], $aParams[ 'lang_id' ], $aParams[ 'title' ] );
	}
	
	//
	public function saveCategory( $iTermId, $iTermTaxonomyId ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$iLangId = Geko_String::coalesce( intval( $_REQUEST[ 'cat_lang_id' ] ), intval( $_REQUEST[ 'geko_lang_id' ] ) );
		
		if ( $iLangId ) {

			$iLangGroupId = Geko_String::coalesce( intval( $_REQUEST[ 'cat_lgroup_id' ] ), intval( $_REQUEST[ 'geko_lgroup_id' ] ) );
			
			if ( !$iLangGroupId ) {
				
				// create a lang group
				$oDb->insert( '##pfx##geko_lang_groups', array(
					'type_id' => Geko_Wp_Options_MetaKey::getId( 'category' )
				) );
				
				// create a lang group member
				$iLangGroupId = $oDb->lastInsertId();
			}
			
			$oDb->insert( '##pfx##geko_lang_group_members', array(
				'lgroup_id' => $iLangGroupId,
				'obj_id' => $iTermId,
				'lang_id' => $iLangId
			) );
			
		}
		
		if ( ( 'editedcat' == $_POST[ 'action' ] ) || ( 'editedtag' == $_POST[ 'action' ] ) ) $this->triggerNotifyMsg( 'm102' );		
	}
	
	//
	public function saveCategorySibling() {
		
		if (
			( $sReferer = $_POST[ '_wp_http_referer' ] ) && 
			( $oUrl = new Geko_Uri( sprintf( 'http://%s%s', $_SERVER[ 'SERVER_NAME' ], $sReferer ) ) ) && 
			(
				( FALSE !== strpos( $sReferer, '/wp-admin/categories.php' ) ) || 
				( FALSE !== strpos( $sReferer, '/wp-admin/edit-tags.php' ) )
			)
		) {
			if (
				( 'edit' == $oUrl->getVar( 'action' ) ) &&
				$oUrl->hasVar( 'cat_lgroup_id' ) &&
				$oUrl->hasVar( 'cat_lang_id' )
			) {
				// $iCatId = wp_insert_category( $_POST );
				$mCatId = wp_insert_term( $_POST[ 'name' ], 'category', $_POST );
				$mCatId = ( is_array( $mCatId ) ) ? $mCatId[ 'term_id' ] : $mCatId;
				
				$oUrl
					->unsetVar( 'cat_lgroup_id' )
					->unsetVar( 'cat_lang_id' )
				;
				
				$oUrl->setVar( 'tag_ID', $mCatId );
				
				$this->triggerNotifyMsg( 'm101' );
				
				header( sprintf( 'Location: %s', strval( $oUrl ) ) );
				die();
			}
		}
	}
	
	//
	public function deleteCategory( $iTermId, $iTermTaxonomyId ) {
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 't.term_id', 'term_id' )
			->from( '##pfx##terms', 't' )
		;
		
		$this->cleanUpEmptyLangGroups( 'category', strval( $oQuery ), $iTermId );
	}
	
	
	
	//// filter categories by language
	
	// adjusts the "Category" meta box when adding/editing a post 
	public function filterPostAdminCategories() {
		
		$sLangCode = '';
		
		if ( $iPostId = $_REQUEST[ 'post' ] ) {
			$oObj = Geko_Wp_Language_Member::getOne( array( 'obj_id' => $iPostId, 'type' => 'post' ), FALSE );
			if ( $oObj->isValid() ) $sLangCode = $oObj->getLangCode();
		} elseif ( $iLangId = $_REQUEST[ 'post_lang_id' ] ) {
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		}
		
		if ( $sLangCode ) $this->_sFilterLangCode = $sLangCode;
		
		add_filter( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
	}
	
	// adjusts the "Parent" dropdown when adding a category
	public function filterCatListAdminCategories() {
		
		$this->getLanguages();		// initialize lang array
		
		$sLangCode = '';
		
		if ( $iLangId = $_REQUEST[ 'cat_lang_id' ] ) {
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		} else {
			$sLangCode = self::$oDefaultLang->getSlug();
		}
		
		if ( $sLangCode ) $this->_sFilterLangCode = $sLangCode;
		
		add_filter( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
	}
	
	// adjusts the "Parent" dropdown when editing a category
	public function filterCatEditAdminCategories() {
		
		$sLangCode = '';
		
		if ( $iCatId = $_REQUEST[ 'tag_ID' ] ) {
			$oObj = Geko_Wp_Language_Member::getOne( array( 'obj_id' => $iCatId, 'type' => 'category' ), FALSE );
			if ( $oObj->isValid() ) $sLangCode = $oObj->getLangCode();
		} elseif ( $iLangId = $_REQUEST[ 'cat_lang_id' ] ) {
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		}
		
		if ( $sLangCode ) $this->_sFilterLangCode = $sLangCode;
		
		add_filter( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
	}
	
	// works with the 'get_terms' filter
	public function categoryFilterQuery( $aTerms, $aTx, $aArgs ) {
		
		$this->getLanguages();		// initialize lang array
		
		if ( $this->_sFilterLangCode ) $aArgs[ 'lang' ] = $this->_sFilterLangCode;
		
		
		// $sTaxonomy ??? use this for something ???
		if (
			( $sTaxonomy = $aTx[ 0 ] ) && 
			( $sLangCode = $aArgs[ 'lang' ] )
		) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$bLangIsDefault = ( self::$oDefaultLang->getSlug() == $sLangCode );
			
			$oMetaQuery = new Geko_Sql_Select();
			$oMetaQuery
				->field( 'mk.mkey_id', 'mkey_id' )
				->from( '##pfx##geko_meta_key', 'mk' )
				->where( 'meta_key = ?', 'category' )
			;
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'm.obj_id', 'obj_id' )
				->from( '##pfx##geko_lang_group_members', 'm' )
				->joinLeft( '##pfx##geko_lang_groups', 'g' )
					->on( 'g.lgroup_id = m.lgroup_id' )
				->joinLeft( '##pfx##geko_languages', 'l' )
					->on( 'l.lang_id = m.lang_id' )
				->where( 'g.type_id = ?', $oMetaQuery )
				->where( sprintf( 'l.code %s= ?', ( $bLangIsDefault ? '!' : '' ) ), $sLangCode )
			;
			
			
			$aCatIds = $oDb->fetchCol( strval( $oQuery ) );
			
			$aFiltered = array();
			
			foreach ( $aTerms as $oTerm ) {
				$bInArray = in_array( $oTerm->term_id, $aCatIds );
				if (
					( $bLangIsDefault && !$bInArray ) || 
					( !$bLangIsDefault && $bInArray )
				) {
					$aFiltered[] = $oTerm;
				}
			}
			
			$aTerms = $aFiltered;
		}
		
		return $aTerms;	
	}
	
	
}


