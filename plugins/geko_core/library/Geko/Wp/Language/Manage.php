<?php

//
class Geko_Wp_Language_Manage extends Geko_Wp_Options_Manage
{
	protected static $aLanguages = NULL;
	protected static $aLangCodeHash = NULL;
	protected static $aLangDomainHash = NULL;
	protected static $aLangDomainCount = array();
	
	protected static $oDefaultLang = NULL;
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = 'lang_id';
	
	protected $_sSubject = 'Language';
	protected $_sDescription = 'An API/UI that handles language management.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'lang';
	protected $_sPrefix = 'geko_lang';
	
	protected $_aSubOptions = array( 'Geko_Wp_Language_String_Manage' );
	
	protected $_iEntitiesPerPage = 10;
	
	
	
	//// methods
	
	
	//
	public function add() {
		
		parent::add();
		
		Geko_Wp_Options_MetaKey::init();
		
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_languages', 'l' )
			->fieldSmallInt( 'lang_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'code', array( 'size' => 8, 'unq' ) )
			->fieldVarChar( 'title', array( 'size' => 256 ) )
			->fieldBool( 'is_default' )
			->fieldVarChar( 'domain', array( 'size' => 256 ) )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( '##pfx##geko_lang_groups', 'lg' )
			->fieldBigInt( 'lgroup_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldSmallInt( 'type_id', array( 'unsgnd', 'key' ) )
		;
		
		$this->addTable( $oSqlTable2, FALSE );
		
		
		
		$oSqlTable3 = new Geko_Sql_Table();
		$oSqlTable3
			->create( '##pfx##geko_lang_group_members', 'lgm' )
			->fieldBigInt( 'lgroup_id', array( 'unsgnd' ) )
			->fieldBigInt( 'obj_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'lang_id', array( 'unsgnd' ) )
			->indexKey( 'lgroup_member', array( 'lgroup_id', 'obj_id', 'lang_id' ) )
		;
		
		$this->addTable( $oSqlTable3, FALSE );
		
		
		
		// first-time setup, call only once
		if ( !$this->regWasInitialized() ) {
			
			$sTableName = $oSqlTable->getTableName();
			
			
			$oDb = Geko_Wp::get( 'db' );
			
			if ( 0 === $oDb->getTableNumRows( $sTableName ) ) {
				
				$sTimestamp = $oDb->getTimestamp();
				
				$oDb->insertMulti( $sTableName, array(
					array(
						'code' => 'en',
						'title' => 'English',
						'is_default' => 1,
						'date_created' => $sTimestamp,
						'date_modified' => $sTimestamp
					)
				) );
			}
			
			$this->regSetInitialized();
		}
		
		
		
		return $this;
	}
	
	
	
	
	
	//// plugin management
	
	//
	public function registerPlugins() {
		
		if ( __CLASS__ == $this->_sInstanceClass ) {
			
			$aArgs = func_get_args();
			
			foreach ( $aArgs as $sClass ) {
				$sSubClass = sprintf( '%s_%s', __CLASS__, $sClass );
				if ( @is_subclass_of( $sSubClass, __CLASS__ ) ) {
					Geko_Singleton_Abstract::getInstance( $sSubClass )->init();
				} elseif ( @is_subclass_of( $sClass, __CLASS__ ) ) {
					Geko_Singleton_Abstract::getInstance( $sClass )->init();				
				}
			}
			
		}
		
		return $this;
	}
	
	
	
	
	
	
	//// error message handling
		
	//
	public function echoNotificationHtml() {
		$this->notificationMessages();
	}
	
	//// helpers
	
	//
	public function getLanguages() {
		
		if ( !self::$aLanguages ) {
			
			$aParams = array();
			$aLangs = new Geko_Wp_Language_Query( $aParams, FALSE );
			
			foreach ( $aLangs as $oLang ) {
				
				$iLangId = $oLang->getId();
				$sLangSlug = $oLang->getSlug();
				$sLangDomain = $oLang->getDomain();
				
				self::$aLanguages[ $iLangId ] = $oLang;
				
				self::$aLangCodeHash[ $sLangSlug ] = $iLangId;
				self::$aLangDomainHash[ $sLangDomain ] = $iLangId;
				
				if ( !self::$aLangDomainCount[ $sLangDomain ] ) {
					self::$aLangDomainCount[ $sLangDomain ] = 1;
				} else {
					self::$aLangDomainCount[ $sLangDomain ]++;
				}
				
				if ( $oLang->getIsDefault() ) self::$oDefaultLang = $oLang;
			}
		}
		
		return self::$aLanguages;
	}
	
	//
	public function getLangCode( $iLangId = NULL ) {
		
		if ( $oLang = $this->getLanguage( $iLangId ) ) {
			return $oLang->getSlug();
		}
		
		return NULL;
	}
	
	//
	public function getLangCodeFromDomain( $sDomain ) {
		
		$this->getLanguages();		// initialize lang array
		
		if ( $iLangId = self::$aLangDomainHash[ $sDomain ] ) {
			return $this->getLangCode( $iLangId );
		}
		
		return NULL;
	}
	
	//
	public function getLangDomainCount( $sDomain ) {
		return self::$aLangDomainCount[ $sDomain ];
	}
	
	
	// will return the default language id
	public function getLangId( $sLangSlug = NULL ) {
		
		$this->getLanguages();		// initialize lang array
		
		if ( NULL !== $sLangSlug ) {
			$oLang = $this->getLanguage( $sLangSlug );
			return $oLang->getId();
		}
		
		if ( self::$oDefaultLang ) {
			return self::$oDefaultLang->getId();
		}
		
		return NULL;
	}
	
	//
	public function getDefLangCode() {
		$this->getLanguages();		// initialize lang array
		if ( self::$oDefaultLang ) {
			return self::$oDefaultLang->getSlug();
		}
		return NULL;
	}
	
	//
	public function isDefLang() {
		if ( self::$oDefaultLang ) {
			return ( $this->getLangCode() == self::$oDefaultLang->getSlug() );
		}
		return NULL;
	}
	
	// $mLang can either be id or slug
	public function getLanguage( $mLang = NULL ) {
		$this->getLanguages();		// initialize lang array
		
		$iLangId = FALSE;
		if ( $mLang = trim( $mLang ) ) {
			if ( preg_match( '/^[0-9]+$/', $mLang ) ) {
				$iLangId = intval( $mLang );
			} elseif ( $mLang ) {
				$iLangId = self::$aLangCodeHash[ $mLang ];
			}
		}
		
		if ( $iLangId ) {
			return self::$aLanguages[ $iLangId ];
		} else {
			return self::$oDefaultLang;
		}
	}
	
	
	
	
	
	
	
	
	
	
	//
	public function echoLanguageSelect() {
		
		$aLangs = $this->getLanguages();
		
		?><select id="geko_lang_id" name="geko_lang_id">
			<?php foreach( $aLangs as $oLang ): ?>
				<option value="<?php $oLang->echoId(); ?>"><?php $oLang->echoTitle(); ?></option>
			<?php endforeach; ?>
		</select><?php
	}
	
	//
	public function echoLanguageHidden( $iLangGroupId, $iLangId ) {
		?>
		<input type="hidden" id="geko_lgroup_id" name="geko_lgroup_id" value="<?php echo $iLangGroupId; ?>" />
		<input type="hidden" id="geko_lang_id" name="geko_lang_id" value="<?php echo $iLangId; ?>" />
		<?php
	}
	
	//
	public function getSelectorLinks(
		$iLangGroupId, $iLangId, $iCurrObjId, $sType, $aParams = array()
	) {
		
		//// get siblings

		$aSibsFmt = array();
		$aSibParams = array();
		
		// typically if the lang_group_id and lang_id are given, there is no current object
		if ( $iLangGroupId && $iLangId ) $aSibParams = array( 'lang_group_id' => $iLangGroupId );
		
		// typically, if there is an obj_id given, there is no lang_group_id and lang_id
		if ( $iCurrObjId ) $aSibParams = array( 'sibling_id' => $iCurrObjId, 'type' => $sType );
		
		// look for siblings if there is enough info
		if ( count( $aSibParams ) > 0 ) {

			$aSibs = new Geko_Wp_Language_Member_Query( $aSibParams, FALSE );
			
			// if there are siblings found, then it means there's language associations
			if ( $aSibs->count() > 0 ) {
				
				// organize siblings by language
				foreach ( $aSibs as $oSib ) {
					
					if ( $iCurrObjId && ( $oSib->getObjId() == $iCurrObjId ) ) {
						// assign values to these since they were not originally specified
						$iLangGroupId = $oSib->getLangGroupId();
						$iLangId = $oSib->getLangId();
					}
					
					$aSibsFmt[ $oSib->getLangId() ] = $oSib;
				}
				
				// get list of available languages
				$aLangs = $this->getLanguages();
				$aLinks = array();
				
				foreach( $aLangs as $oLang ) {
					
					if ( $oLang->getId() == $iLangId ) {
						
						// since already on the current item, just show the title
						$aParams[ 'title' ] = $oLang->getTitle();
						
						$aLinks[] = $this->getSelCurrLink( $aParams );
						
					} elseif ( $oSib = $aSibsFmt[ $oLang->getId() ] ) {
						
						// get link to existing sibling
						$aParams[ 'title' ] = $oLang->getTitle();
						$aParams[ 'obj_id' ] = $oSib->getObjId();
						
						$aLinks[] = $this->getSelExistLink( $aParams );
						
					} else {
						
						// get link to create item for the given language
						$aParams[ 'title' ] = $oLang->getTitle();
						$aParams[ 'lgroup_id' ] = $iLangGroupId;
						$aParams[ 'lang_id' ] = $oLang->getId();
						
						$aLinks[] = $this->getSelNonExistLink( $aParams );
						
					}
				}
				
				return $aLinks;
			}
		}
		
		return FALSE;
	}
	
	//
	public function getSelCurrLink( $aParams ) {
		return $aParams[ 'title' ];
	}
	
	// pretty useless, should be implemented by sub-class
	public function getSelExistLink( $aParams ) {
		return sprintf( '<a href="#">%s</a>', $aParams[ 'title' ] );
	}
	
	// pretty useless, should be implemented by sub-class
	public function getSelNonExistLink( $aParams ) {
		return sprintf( '<a href="#">%s</a>', $aParams[ 'title' ] );
	}
	
	
	
		
	
	
	
	//// page display
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-code">Code</th>
		<th scope="col" class="manage-column column-is-default">Is Default?</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-created">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-title"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-title"><?php echo ( $oEntity->getIsDefault() ) ? 'Yes' : 'No'; ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	
	//
	public function formFields() {
		
		?>
		<h3><?php echo $this->_sListingTitle; ?> Options</h3>
		<style type="text/css">
			
			.multi_row select.translation_key {
				width: 250px;
			}
			
			.multi_row textarea.translation_value {
				width: 350px;
			}
			
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="lang_title">Language</label></th>
				<td>
					<input id="lang_title" name="lang_title" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="lang_code">Slug</label></th>
				<td>
					<input id="lang_code" name="lang_code" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="lang_is_default">Is Default?</label></th>
				<td>
					<input id="lang_is_default" name="lang_is_default" type="checkbox" value="1" />
				</td>
			</tr>
			<tr>
				<th><label for="lang_domain">Domain</label></th>
				<td>
					<input id="lang_domain" name="lang_domain" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	// to be implemented by sub-class as needed
	public function customFields() { }

	


	//// crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'code' ] ) $aValues[ 'code' ] = $aValues[ 'title' ];
		$aValues[ 'code' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'code' ], $this->getPrimaryTable(), 'code'
		);
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		if ( !isset( $aValues[ 'is_default' ] ) ) $aValues[ 'is_default' ] = 0;
		
		return $aValues;
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sTitle = $aValues[ 'title' ];
		
		// check title
		if ( $bContinue && !$sTitle ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty title was given
		}
				
		return $bContinue;
		
	}
	
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'code' ] ) $aValues[ 'code' ] = $aValues[ 'title' ];
		if ( $aValues[ 'code' ] != $oEntity->getSlug() ) {
			$aValues[ 'code' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'code' ], $this->getPrimaryTable(), 'code'
			);
		}
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		if ( !isset( $aValues[ 'is_default' ] ) ) $aValues[ 'is_default' ] = 0;
		
		return $aValues;
	}
	
	
	
	// delete methods
	
	// hook method
	public function postDeleteAction( $aParams, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		
		// group members
		$oQuery1 = new Geko_Sql_Select();
		$oQuery1
			->field( 'l.lang_id', 'lang_id' )
			->from( '##pfx##geko_languages', 'l' )
		;
		
		$oDb->delete( '##pfx##geko_lang_group_members', array(
			'lang_id NOT IN (?)' => new Zend_Db_Expr( strval( $oQuery1 ) )
		) );
		
		
		// groups
		$oQuery2 = new Geko_Sql_Select();
		$oQuery2
			->field( 'lgm.lgroup_id', 'lgroup_id' )
			->from( '##pfx##geko_lang_group_members', 'lgm' )
		;
		
		$oDb->delete( '##pfx##geko_lang_groups', array(
			'lgroup_id NOT IN (?)' => new Zend_Db_Expr( strval( $oQuery2 ) )
		) );
		
	}
	
	
	
	//
	public function doUpdateDefaultLang( $iLangId, $bIsDefault ) {
		
		if ( $bIsDefault ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$oDb->update( '##pfx##geko_languages', array(
				'is_default = ( IF( lang_id = ?, 1, NULL ) )' => $iLangId
			) );			
		}
	}
	
	//
	public function cleanUpEmptyLangGroups( $sType, $sExcludeIdsSql, $iObjId ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		
		// delete direct
		
		$oDelete1 = new Geko_Sql_Delete();
		$oDelete1
			->from( '##pfx##geko_lang_group_members', 'm' )
			->joinInner( '##pfx##geko_lang_groups', 'g' )
				->on( 'g.lgroup_id = m.lgroup_id' )
			->where( 'g.type_id = ?', Geko_Wp_Options_MetaKey::getId( $sType ) )
			->where( 'm.obj_id = ?', $iObjId )
		;
		
		$oDb->query( strval( $oDelete1 ) );
		
		
		// delete non-existent
		
		$oDelete2 = new Geko_Sql_Delete();
		$oDelete2
			->from( '##pfx##geko_lang_group_members', 'm' )
			->joinInner( '##pfx##geko_lang_groups', 'g' )
				->on( 'g.lgroup_id = m.lgroup_id' )
			->where( 'g.type_id = ?', Geko_Wp_Options_MetaKey::getId( $sType ) )
			->where( sprintf( 'm.obj_id NOT IN( %s )', $sExcludeIdsSql ) )
		;
		
		$oDb->query( strval( $oDelete2 ) );
		
		
		// clean-up group
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'lgm.lgroup_id', 'lgroup_id' )
			->from( '##pfx##geko_lang_group_members', 'lgm' )
		;
		
		$oDb->delete( '##pfx##geko_lang_groups', array(
			'lgroup_id NOT IN (?)' => new Zend_Db_Expr( strval( $oQuery ) )
		) );
		
	}
	
	
	
}


