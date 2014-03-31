<?php

//
class Geko_Wp_Language_Manage_Category extends Geko_Wp_Language_Manage
{
	protected $bHasTagId = TRUE;
	
	protected $_aSubOptions = array();
	
	protected $sFilterLangCode = '';
	
	
	
	//// accessors
	
	// HACK!!!
	public function _getCatId() {
		return ( $this->bHasTagId ) ?
			intval( Geko_String::coalesce( $_GET[ 'tag_ID' ], $_GET[ 'cat_ID' ] ) ) :
			0
		;
	}
	
	
	
	//
	public function add() {
		
		parent::add();
		
		// HACK!!!
		if ( !$_GET[ 'tag_ID' ] ) {
			$_GET[ 'tag_ID' ] = 1;
			$_REQUEST[ 'tag_ID' ] = 1;
			$this->bHasTagId = FALSE;
		}
		
		add_action( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
		
		return $this;
	}
	
	
	//
	public function affixAdmin() {
		
		// category
		add_action( 'admin_category_add_fields_pq', array( $this, 'addCategorySelector' ) );
		add_action( 'admin_category_edit_fields_pq', array( $this, 'editCategorySelector' ) );
		add_action( 'create_category', array( $this, 'saveCategory' ), 10, 2 );
		add_action( 'edit_category', array( $this, 'saveCategory' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'saveCategorySibling' ) );
		add_action(	'delete_category', array( $this, 'deleteCategory' ), 10, 2 );
		
		add_action( 'admin_init_category_edit', array( $this, 'filterCatEditAdminCategories' ) );
		add_action( 'admin_init_category_list', array( $this, 'filterCatListAdminCategories' ) );
		add_action( 'admin_head_category_list', array( $this, 'addCategorySelectorJs' ) );
		
		// post
		add_action( 'admin_init_post_add', array( $this, 'filterPostAdminCategories' ) );
		add_action( 'admin_init_post_edit', array( $this, 'filterPostAdminCategories' ) );
		
		Geko_Hooks::addFilter( 'admin_page_source', array( $this, 'tweakTitle' ) );
		
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
	public function tweakTitle( $sContent ) {
		
		$oUrl = new Geko_Uri;
		$sUrlPath = $oUrl->getPath();
		
		if (
			( FALSE !== strpos( $sUrlPath, '/wp-admin/categories.php' ) ) || 
			(
				( FALSE !== strpos( $sUrlPath, '/wp-admin/edit-tags.php' ) ) && 
				( 'category' == $oUrl->getVar( 'taxonomy' ) )
			)
		) {
			if ( ( 'edit' == $_GET['action'] ) && $_GET['cat_lgroup_id'] && $_GET['cat_lang_id'] ) {
				$sContent = str_replace(
					array( '<h2>Edit Category</h2>', '<input type="submit" class="button-primary" name="submit" value="Update">' ),
					array( '<h2>Add Category</h2>', '<input type="submit" class="button-primary" name="submit" value="Add">' ),
					$sContent
				);
			}
		}
		
		return $sContent;
	}
	
	//
	public function addCategorySelectorJs() {
		
		$oUrl = new Geko_Uri();
		$oUrl->unsetVar('cat_lang_id');
		
		?><script type="text/javascript">
			
			jQuery(document).ready(function($) {
				
				$('#geko_lang_id').change( function() {
					window.location = '<?php echo strval( $oUrl ); ?>&cat_lang_id=' + $(this).val();
				} );
				
			});
			
		</script><?php
		
	}
	
	//
	public function addCategorySelector( $oPq ) {
		
		$aVals = array();
		if ( $iLangId = $_REQUEST['cat_lang_id'] ) {
			$aVals['geko_lang_id'] = $iLangId;
		}
		
		$oPq->find('form')->prepend('
			<div class="form-field">
				<label for="geko_lang_id">Language</label>
				' . Geko_Html::populateForm( $this->getLanguageSelect(), $aVals ) . '
			</div>
		');
		
		return $oPq;
	}
	
	//
	public function editCategorySelector( $oPq ) {
		
		$iLangId = intval( $_GET['cat_lang_id'] );
		$iLangGroupId = intval( $_GET['cat_lgroup_id'] );
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
		
		$sCatId = ( $_GET['cat_ID'] ) ? 'editcat' : 'edittag';
		
		$oPq->find('form#' . $sCatId . ' > table')->prepend('
			<tr class="form-field">
				<th valign="top" scope="row"><label for="geko_lang_id">Language</label></th>
				<td>' . $sField . '</td>
			</tr>
		');
		
		// HACK!!!
		if ( FALSE == $this->bHasTagId ) {
			$oPq->find('input[name=tag_ID]')->attr( 'value', '' );
			$oPq->find('input[name=action]')->attr( 'value', 'add-tag' );
			$oPq->find('#_wpnonce')->attr( 'value', wp_create_nonce('add-tag') );
			$oPq->find('#name')->attr( 'value', '' );
			$oPq->find('#slug')->attr( 'value', '' );
		}
		
		if ( ( 'edit' == $_GET['action'] ) && ( $this->_getCatId() ) ) {
			$oUrl = new Geko_Uri();
			$oPq->find('input[name="_wp_original_http_referer"]')->val( strval( $oUrl ) );
		}
		
		return $oPq;
	}
	
	//
	public function getSelExistLink( $aParams ) {
		
		if ( $_GET[ 'cat_ID' ] ) {
			$sLink = '<a href="%s/wp-admin/categories.php?action=edit&cat_ID=%d">%s</a>';
		} else {
			$sLink = '<a href="%s/wp-admin/edit-tags.php?action=edit&taxonomy=category&post_type=post&tag_ID=%d">%s</a>';		
		}
		
		return sprintf( $sLink, Geko_Wp::getUrl(), $aParams[ 'obj_id' ], $aParams[ 'title' ] );
	}
	
	//
	public function getSelNonExistLink( $aParams ) {

		if ( $_GET[ 'cat_ID' ] ) {
			$sLink = '<a href="%s/wp-admin/categories.php?action=edit&cat_lgroup_id=%d&cat_lang_id=%d">%s</a>';
		} else {
			$sLink = '<a href="%s/wp-admin/edit-tags.php?action=edit&taxonomy=category&post_type=post&cat_lgroup_id=%d&cat_lang_id=%d">%s</a>';		
		}
		
		return sprintf( $sLink, Geko_Wp::getUrl(), $aParams[ 'lgroup_id' ], $aParams[ 'lang_id' ], $aParams[ 'title' ] );
	}
	
	//
	public function saveCategory( $iTermId, $iTermTaxonomyId ) {
		
		global $wpdb;
				
		if ( $iLangId = intval( $_POST[ 'geko_lang_id' ] ) ) {
			
			if ( !$iLangGroupId = intval( $_POST[ 'geko_lgroup_id' ] ) )
			{
				// create a lang group
				$wpdb->insert(
					$wpdb->geko_lang_groups,
					array( 'type_id' => Geko_Wp_Options_MetaKey::getId( 'category' ) )
				);
				
				// create a lang group member
				$iLangGroupId = $wpdb->insert_id;
			}
			
			$wpdb->insert(
				$wpdb->geko_lang_group_members,
				array(
					'lgroup_id' => $iLangGroupId,
					'obj_id' => $iTermId,
					'lang_id' => $iLangId
				)
			);
			
		}
		
		if ( ( 'editedcat' == $_POST[ 'action' ] ) || ( 'editedtag' == $_POST[ 'action' ] ) ) $this->triggerNotifyMsg( 'm102' );		
	}
	
	//
	public function saveCategorySibling() {
		
		if (
			( $sReferer = $_POST['_wp_http_referer'] ) && 
			( $oUrl = new Geko_Uri( 'http://' . $_SERVER['SERVER_NAME'] . $sReferer ) ) && 
			(
				( FALSE !== strpos( $sReferer, '/wp-admin/categories.php' ) ) || 
				(
					( FALSE !== strpos( $sReferer, '/wp-admin/edit-tags.php' ) ) && 
					( 'category' == $oUrl->getVar( 'taxonomy' ) )
				)
			)
		) {
			if (
				( 'edit' == $oUrl->getVar('action') ) &&
				$oUrl->hasVar('cat_lgroup_id') &&
				$oUrl->hasVar('cat_lang_id')
			) {
				// $iCatId = wp_insert_category( $_POST );
				$mCatId = wp_insert_term( $_POST['name'], 'category', $_POST );
				$mCatId = ( is_array( $mCatId ) ) ? $mCatId['term_id'] : $mCatId;
				
				$oUrl
					->unsetVar('cat_lgroup_id')
					->unsetVar('cat_lang_id')
				;
				
				$sCatIdVar = ( FALSE !== strpos( $sReferer, '/wp-admin/categories.php' ) ) ? 'cat_ID' : 'tag_ID';
				$oUrl->setVar( $sCatIdVar, $mCatId );
				
				$this->triggerNotifyMsg( 'm101' );
				
				header( 'Location: ' . strval( $oUrl ) );
				die();
			}
		}
	}
	
	//
	public function deleteCategory( $iTermId, $iTermTaxonomyId ) {
		
		global $wpdb;
		
		$sSql = "SELECT term_id FROM $wpdb->terms";
		
		$this->cleanUpEmptyLangGroups( 'category', $sSql, $iTermId );
	}
	
	
	
	//// filter categories by language
	
	// adjusts the "Category" meta box when adding/editing a post 
	public function filterPostAdminCategories() {
		
		$sLangCode = '';
		
		if ( $iPostId = $_REQUEST['post'] ) {
			$oObj = Geko_Wp_Language_Member::getOne( array( 'obj_id' => $iPostId, 'type' => 'post' ), FALSE );
			if ( $oObj->isValid() ) $sLangCode = $oObj->getLangCode();
		} elseif ( $iLangId = $_REQUEST['post_lang_id'] ) {
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		}
		
		if ( $sLangCode ) $this->sFilterLangCode = $sLangCode;
		
		add_filter( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
	}
	
	// adjusts the "Parent" dropdown when adding a category
	public function filterCatListAdminCategories() {
		
		$this->getLanguages();		// initialize lang array
		
		$sLangCode = '';
		
		if ( $iLangId = $_REQUEST['cat_lang_id'] ) {
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		} else {
			$sLangCode = self::$oDefaultLang->getSlug();
		}
		
		if ( $sLangCode ) $this->sFilterLangCode = $sLangCode;
		
		add_filter( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
	}
	
	// adjusts the "Parent" dropdown when editing a category
	public function filterCatEditAdminCategories() {
		
		$sLangCode = '';
		
		if ( $iCatId = $_REQUEST['tag_ID'] ) {
			$oObj = Geko_Wp_Language_Member::getOne( array( 'obj_id' => $iCatId, 'type' => 'category' ), FALSE );
			if ( $oObj->isValid() ) $sLangCode = $oObj->getLangCode();
		} elseif ( $iLangId = $_REQUEST['cat_lang_id'] ) {
			$sLangCode = $this->getLanguage( $iLangId )->getSlug();
		}
		
		if ( $sLangCode ) $this->sFilterLangCode = $sLangCode;
		
		add_filter( 'get_terms', array( $this, 'categoryFilterQuery' ), 10, 3 );
	}
	
	// works with the 'get_terms' filter
	public function categoryFilterQuery( $aTerms, $aTx, $aArgs ) {
		
		$this->getLanguages();		// initialize lang array
		
		if ( $this->sFilterLangCode ) $aArgs['lang'] = $this->sFilterLangCode;
		
		if (
			( 'category' == $aTx[0] ) && 
			( $sLangCode = $aArgs['lang'] )
		) {
			global $wpdb;
			
			$bLangIsDefault = ( self::$oDefaultLang->getSlug() == $sLangCode );
			
			$aCatIds = $wpdb->get_col("
				SELECT			m.obj_id
				FROM			$wpdb->geko_lang_group_members m
				LEFT JOIN		$wpdb->geko_lang_groups g
					ON			g.lgroup_id = m.lgroup_id
				LEFT JOIN		$wpdb->geko_languages l
					ON			l.lang_id = m.lang_id
				WHERE			( g.type_id = ( SELECT mkey_id FROM $wpdb->geko_meta_key WHERE meta_key = 'category' ) ) AND 
								( l.code " . ( $bLangIsDefault ? '!' : '' ) . "= '$sLangCode' )
			");
			
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


