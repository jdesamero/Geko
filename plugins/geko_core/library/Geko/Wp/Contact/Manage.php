<?php

//
class Geko_Wp_Contact_Manage extends Geko_Wp_Options_Manage
{
	protected static $aCache = array();
	
	protected $_iObjectId = 0;
	protected $_sObjectType;
	protected $_sContactSubType = '';
	
	protected $_aFields = array(
		'first_name',
		'last_name',
		'title',
		'company_name',
		'email_address',
		'alt_email_address',
		'alt_email_address_2',
		'alt_email_address_3',
		'phone_number',
		'home_number',
		'cell_number',
		'work_number',
		'fax_number',
		'website_url',
		'facebook_url',
		'linkedin_url',
		'youtube_url',
		'lang_id'
	);
	
	protected $_aFieldLabels = array(
		'first_name' => 'First Name',
		'last_name' => 'Last Name',
		'title' => 'Title',
		'company_name' => 'Company Name',
		'email_address' => 'Email Address',
		'alt_email_address' => 'Alternate Email Address',
		'alt_email_address_2' => 'Alternate Email Address 2',
		'alt_email_address_3' => 'Alternate Email Address 3',
		'phone_number' => 'Phone Number',
		'home_number' => 'Home Number',
		'cell_number' => 'Cell Number',
		'work_number' => 'Work Number',
		'fax_number' => 'Fax Number',
		'website_url' => 'Website URL',
		'facebook_url' => 'Facebook URL',
		'linkedin_url' => 'LinkedIn URL',
		'youtube_url' => 'YouTube URL',
		'lang_id' => 'Language Preference'
	);
	
	protected $_aFieldDescriptions = array();
	
	
	//// methods
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		Geko_Wp_Options_MetaKey::init();
		
		//// register tables
		
		// address
		
		$sTableName = 'geko_contact';
		Geko_Wp_Db::addPrefix( $sTableName );
	
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'c' )
			->fieldBigInt( 'contact_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'object_id' )
			->fieldSmallInt( 'objtype_id' )
			->fieldSmallInt( 'subtype_id' )
			->fieldVarChar( 'first_name', array( 'size' => 256 ) )
			->fieldVarChar( 'last_name', array( 'size' => 256 ) )
			->fieldVarChar( 'title', array( 'size' => 256 ) )
			->fieldVarChar( 'company_name', array( 'size' => 256 ) )
			->fieldVarChar( 'email_address', array( 'size' => 256 ) )
			->fieldVarChar( 'alt_email_address', array( 'size' => 256 ) )
			->fieldVarChar( 'alt_email_address_2', array( 'size' => 256 ) )
			->fieldVarChar( 'alt_email_address_3', array( 'size' => 256 ) )
			->fieldVarChar( 'phone_number', array( 'size' => 64 ) )
			->fieldVarChar( 'home_number', array( 'size' => 64 ) )
			->fieldVarChar( 'cell_number', array( 'size' => 64 ) )
			->fieldVarChar( 'work_number', array( 'size' => 64 ) )
			->fieldVarChar( 'fax_number', array( 'size' => 64 ) )
			->fieldLongText( 'website_url' )
			->fieldLongText( 'facebook_url' )
			->fieldLongText( 'linkedin_url' )
			->fieldLongText( 'youtube_url' )
			->fieldSmallInt( 'lang_id', array( 'unsgnd', 'notnull' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
	}
	
	// create table
	public function install() {
		
		parent::install();
		
		Geko_Wp_Options_MetaKey::install();
		
		$this->createTableOnce();
		
		return $this;		
	}
	
	
	//
	public function attachPage() { }
	
	
	//
	public function initEntities( $oMainEnt = NULL, $aParams = array() ) {
		
		if ( !$this->_oCurrentEntity && $this->_iObjectId ) {
			
			$aParams[ 'object_id' ] = $this->_iObjectId;
			
			if ( $this->_sObjectType ) {
				$aParams[ 'object_type' ] = $this->_sObjectType;
			}
			
			if ( $this->_sContactSubType ) {
				$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sContactSubType );
			}
			
			$this->_oCurrentEntity = call_user_func(
				array( $this->_sEntityClass, 'getOne' ), $aParams, FALSE
			);
			
			if ( $this->_oCurrentEntity->isValid() ) {
				$this->_iCurrentEntityId = $this->_oCurrentEntity->getId();
			}
			
		}
		
		return $this;
	}
	
	
	
	
	//// accessors
	
	//
	public function getObjectType( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getObjectType();
		}
		return $this->_sObjectType;
	}
	
	//
	public function getContactSubType( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getContactSubType();
		}
		return $this->_sContactSubType;
	}
	
	// return a prefix
	public function getPrefix( $oPlugin = NULL ) {
		return ( $oPlugin ) ? $oPlugin->getPrefix() : parent::getPrefix( $oPlugin );
	}
	
	//
	public function getSectionLabel( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getSectionLabel();
		}
		return $this->_sSectionLabel;
	}
	
	//
	public function getManage( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getManage();
		}
		return $this;
	}
	
	//
	public function getFields( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getFields();
		}
		return $this->_aFields;
	}
	
	//
	public function setFields( $aFields ) {
		$this->_aFields = $aFields;
		return $this;
	}
	
	//
	public function setFieldLabels( $aFieldLabels, $bOverride = TRUE ) {
		if ( $bOverride ) {
			$this->_aFieldLabels = array_merge( $this->_aFieldLabels, $aFieldLabels );
		} else {
			$this->_aFieldLabels = $aFieldLabels;
		}
		return $this;
	}
	
	//
	public function getFieldLabels( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getFieldLabels();
		}
		return $this->_aFieldLabels;
	}
	
	//
	public function setFieldDescriptions( $aFieldDescriptions ) {
		$this->_aFieldDescriptions = $aFieldDescriptions;
		return $this;
	}
	
	//
	public function getFieldDescriptions( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getFieldDescriptions();
		}
		return $this->_aFieldDescriptions;
	}
	
	
	
	//
	public function getPrimaryTable() {
		
		if ( $this->_sInstanceClass != __CLASS__ ) {
			$oMng = Geko_Singleton_Abstract::getInstance( __CLASS__ );
			return $oMng->getPrimaryTable();
		}
		
		return parent::getPrimaryTable();
	}
	
	
	
	//// load
	
	//
	public function getStoredOptions( $oPlugin = NULL ) {
		
		$aRet = array();
		
		$oCurrentEntity = $this->getCurrentEntity( $oPlugin );
		$aFields = $this->getFields( $oPlugin );
		$sPrefix = $this->getPrefixForDoc( $oPlugin );
		
		if ( $oCurrentEntity && $oCurrentEntity->isValid() ) {
			
			foreach ( $aFields as $sField ) {
				if ( $oCurrentEntity->hasEntityProperty( $sField ) ) {
					$sPostKey = $sPrefix . $sField;
					$aRet[ $sPostKey ] = $oCurrentEntity->getEntityPropertyValue( $sField );
				}
			}
			
		}
		
		return $aRet;
	}
	
	//
	public function getStoredSubOptions( $aRet, $oMainEnt, $oPlugin = NULL ) {
		
		$sType = $this->getObjectType( $oPlugin );
		$sSubType = $this->getContactSubType( $oPlugin );
		$sPrefix = $this->getPrefixForDoc( $oPlugin );
		
		$aRet = parent::getStoredSubOptions( $aRet, $oMainEnt, $oPlugin );
		$aSubRet = $aRet[ $sType ];
		
		if ( ( is_array( $aSubRet ) ) && ( count( $aSubRet ) > 0 ) ) {
		
			$aSubRes = $this->getSubEntities( array(
				'object_id' => array_keys( $aSubRet ),
				'object_type' => $sType,
				'sub_type' => $sSubType
			) );
			
			if ( count( $aSubRes ) > 0 ) {
				$aFields = $this->getFields( $oPlugin );
				foreach ( $aSubRes as $aRow ) {
					$iObjectId = $aRow[ 'object_id' ];
					foreach ( $aFields as $sField ) {
						$aSubRet[ $iObjectId ][ $sPrefix . $sField ] = $aRow[ $sField ];
					}
				}
				$aRet[ $sType ] = $aSubRet;			// re-assign
			}
			
		}
		
		return $aRet;
	}
	
	
	
	
	//// output functions
	
	//
	public function outputLanguageSelectHtml( $sFormId = 'lang_id', $sEmptyValLabel = NULL, $oPlugin = NULL ) {
		
		global $wpdb;
		
		if ( NULL === $sEmptyValLabel ) {
			$aFieldLabels = $this->getFieldLabels( $oPlugin );
			$sEmptyValLabel = 'Select a ' . $aFieldLabels[ 'lang_id' ];
		}

		$aParams = array(
			'showposts' => -1,
			'posts_per_page' => -1
		);
		$aLangs = new Geko_Wp_Language_Query( $aParams, FALSE );
		
		?>
		<select id="<?php echo $sFormId; ?>" name="<?php echo $sFormId; ?>" class="cont_lang_id">
			<?php if ( $sEmptyValLabel ): ?>
				<option value="" class="default"><?php echo $sEmptyValLabel; ?></option>
			<?php endif; ?>
			<?php foreach ( $aLangs as $oLang ): ?>
				<option value="<?php $oLang->echoId(); ?>"><?php $oLang->echoTitle(); ?></option>
			<?php endforeach; ?>
		</select>		
		<?php
	}
	
	//
	public function formFieldRow( $sField, $sLabel, $sDescription, $oPlugin = NULL ) {
		?>
		<p>
			<label class="main"><?php echo $sLabel; ?></label>
			<?php $this->formField( $sField ); ?>
			<?php if ( $sDescription ): ?>
				<label class="description"><?php echo $sDescription; ?></label>
			<?php endif; ?>
		</p>
		<?php	
	}
	
	//
	public function formField( $sField, $sFieldName = NULL, $sFieldClass = '', $oPlugin = NULL ) {
		
		if ( NULL === $sFieldName ) $sFieldName = $sField;
		
		$sClass = 'text';
		if ( $sFieldClass ) $sClass .= ' ' . $sFieldClass;
		
		if ( 'lang_id' == $sField ):
			$this->outputLanguageSelectHtml( $sFieldName, NULL, $oPlugin );
		else:
			?><input id="<?php echo $sFieldName; ?>" name="<?php echo $sFieldName; ?>" type="text" class="<?php echo $sClass; ?>" value="" /><?php
		endif;
	}
	
	//
	public function formFields( $oPlugin = NULL ) {
		
		$aFields = $this->getFields( $oPlugin );
		$aFieldLabels = $this->getFieldLabels( $oPlugin );
		$aFieldDescriptions = $this->getFieldDescriptions( $oPlugin );

		$oCurrentEntity = $this->getCurrentEntity( $oPlugin );
		
		if ( $oCurrentEntity ) do_action( 'admin_geko_contact_main_fields', $oCurrentEntity, 'pre' );
		
		foreach ( $aFields as $sField ) {
			$this->formFieldRow( $sField, $aFieldLabels[ $sField ], $aFieldDescriptions[ $sField ], $oPlugin );	
		}
		
		if ( $oCurrentEntity ) do_action( 'admin_geko_contact_main_fields', $oCurrentEntity, 'main' );
		
	}
	
	
	//
	public function subMainFieldTitles( $oPlugin = NULL ) {
		
		$aFields = $this->getFields( $oPlugin );
		$aFieldLabels = $this->getFieldLabels( $oPlugin );
		
		foreach ( $aFields as $sField ):
			?><th class="cont-th-<?php echo $sField; ?>"><?php echo $aFieldLabels[ $sField ]; ?></th><?php
		endforeach;
		
	}
	
	//
	public function subMainFieldColumns( $oPlugin = NULL ) {
		
		$aFields = $this->getFields( $oPlugin );
		$sPrefix = $this->getPrefixWithSep( $oPlugin );
		$sSubType = $this->getContactSubType( $oPlugin );
		
		foreach ( $aFields as $sField ):
			?><td><?php $this->formField( $sField, sprintf( '%s[][%s%s]', $sSubType, $sPrefix, $sField ), sprintf( '%s_%s', $sSubType, $sField ), $oPlugin ); ?></td><?php
		endforeach;
		
	}
	
	
	//// form processing/injection methods
	
	// plug into the add category form
	public function setupFields( $oPlugin = NULL ) {
		
		$aParts = $this->extractParts( $oPlugin );
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			
			$sLabel = Geko_String::sw( '<label for="%s$1">%s$0</label>', $aPart[ 'label' ], $aPart[ 'name' ] );
			$sFieldGroup = Geko_String::sw( '%s<br />', $aPart[ 'field_group' ] );
			
			$sFields .= '
				<tr class="form-field"' . $sRowId . '>
					<th>' . $sLabel . '</th>
					<td>
						' . $sFieldGroup . '
						' . Geko_String::sw( '<span class="description">%s</span>', $aPart[ 'description' ] ) . '
					</td>
				</tr>
			';
		}
		
		return $sFields;
	}
	
	//
	public function changeDoc( $oDoc ) {
		$oDoc[ 'input.text' ]->addClass( 'regular-text' );
	}
	
	// function outputForm( $oPlugin ) or function outputForm( $oEntity, Section, $oPlugin )
	public function outputForm( $mArg1 = NULL, $mArg2 = NULL, $mArg3 = NULL ) {

		if ( is_a( $mArg1, 'Geko_Wp_Options_Plugin' ) ) {
			$oPlugin = $mArg1;
		} elseif ( is_a( $mArg3, 'Geko_Wp_Options_Plugin' ) ) {
			$oPlugin = $mArg3;
		}
		
		?>
		<h3><?php echo $this->getSectionLabel( $oPlugin ); ?></h3>
		<?php $this->preFormFields( $oPlugin ); ?>
		<table class="form-table">
			<?php echo $this->setupFields( $oPlugin ); ?>
		</table>
		<?php
	}
	
	
	
	
	// save the data
	public function save( $aParams, $sMode = 'insert', $aVals = NULL, $oPlugin = NULL ) {
		
		global $wpdb;
		
		$aKeys = array();
		
		// prepare params
		
		$sObjectType = $this->getObjectType( $oPlugin );
		$sContactSubType = $this->getContactSubType( $oPlugin );
		$aFieldList = $this->getFields( $oPlugin );
		$sPrefix = $this->getPrefixForDoc( $oPlugin );
		
		
		if ( !$aParams[ 'object_id' ] ) $aParams[ 'object_id' ] = $this->_iObjectId;
		if ( !$aParams[ 'object_type' ] ) $aParams[ 'object_type' ] = $sObjectType;
		
		if ( $aParams[ 'object_type' ] ) {
			$aParams[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'object_type' ] );
		}
		
		if ( $aParams[ 'sub_type' ] && !$sContactSubType ) {
			$sContactSubType = $aParams[ 'sub_type' ];
		}
		
		if ( $sContactSubType ) {
			$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $sContactSubType );
		}
		
		$bUpdate = FALSE;
		if ( 'update' == $sMode ) {
			
			$oSql = new Geko_Sql_Select();
			$oSql
				->field( 1, 'test' )
				->from( $wpdb->geko_contact )
			;
			
			if ( $aParams[ 'subtype_id' ] ) {
				$oSql->where( 'subtype_id = ?', $aParams[ 'subtype_id' ] );
				$aKeys[ 'subtype_id' ] = $aParams[ 'subtype_id' ];
			}
			
			if ( $aParams[ 'contact_id' ] ) {
				
				$oSql->where( 'contact_id = ?', $aParams[ 'contact_id' ] );
				
				if ( $wpdb->get_var( strval( $oSql ) ) ) {
					$aKeys[ 'contact_id' ] = $aParams[ 'contact_id' ];
					$bUpdate = TRUE;
				}
			
			}
			
			if ( $aParams[ 'object_id' ] && $aParams[ 'objtype_id' ] ) {
				
				$oSql
					->where( 'object_id = ?', $aParams[ 'object_id' ] )
					->where( 'objtype_id = ?', $aParams[ 'objtype_id' ] )
				;
				
				if ( $wpdb->get_var( strval( $oSql ) ) ) {
					$aKeys[ 'object_id' ] = $aParams[ 'object_id' ];
					$aKeys[ 'objtype_id' ] = $aParams[ 'objtype_id' ];
					$bUpdate = TRUE;
				}
				
			}
			
		}
		
		
		// data
		if ( NULL === $aVals ) {
			
			// use $_POST array for values, minding the prefix
			$aVals = array();
			
			// get list of fields with expected values
			$aFields = $aFieldList;			// TO DO: Add any auto-field stuff
			
			foreach ( $aFields as $sField ) {
				$sPostKey = $sPrefix . $sField;
				if ( isset( $_POST[ $sPostKey ] ) ) {
					$aVals[ $sField ] = stripslashes( $_POST[ $sPostKey ] );
				}
			}
			
		}
		
		
		// if specified, resolve language id
		if ( $sLangCode = strtolower( $aVals[ 'lang_code' ] ) ) {
			if ( !$aVals[ 'lang_id' ] ) {
				$oLangMng = Geko_Wp_Language_Manage::getInstance();
				$aVals[ 'lang_id' ] = $oLangMng->getLangId( $sLangCode );
			}
			unset( $aVals[ 'lang_code' ] );
		}
		
		
		if ( $bUpdate ) {
			
			$wpdb->update( $wpdb->geko_contact, $aVals, $aKeys );
			
		} else {
			
			// insert
			$aVals[ 'object_id' ] = $aParams[ 'object_id' ];
			$aVals[ 'objtype_id' ] = $aParams[ 'objtype_id' ];
			if ( $aParams[ 'subtype_id' ] ) $aVals[ 'subtype_id' ] = $aParams[ 'subtype_id' ];
			
			$wpdb->insert( $wpdb->geko_contact, $aVals );
		}
		
		
	}
	
	//
	public function delete( $oPlugin = NULL ) {
		
		global $wpdb;
		
		$sObjectType = $this->getObjectType( $oPlugin );
		
		$oSqlDelete = new Geko_Sql_Delete();
		$oSqlDelete
			->from( $wpdb->geko_contact )
			->where( 'object_id = ?', $this->_iObjectId )
			->where( 'objtype_id = ?', Geko_Wp_Options_MetaKey::getId( $sObjectType ) )
		;
		
		$wpdb->query( strval( $oSqlDelete ) );
		
	}
	
	
	//// sub crud methods
	
	//
	public function doSubAddAction( $oMainEnt, $aParams, $oPlugin = NULL ) {
		if ( $this->getUpdateRelatedEntities( $oPlugin ) ) {
			$this->updateRelatedEntities( $oPlugin );
		} else {
			$aParams[ 'object_id' ] = $oMainEnt->getId();
			$this->save( $aParams, 'insert', NULL, $oPlugin );
		}
	}
	
	//
	public function doSubEditAction( $oMainEnt, $oUpdMainEnt, $aParams, $oPlugin = NULL ) {
		if ( $this->getUpdateRelatedEntities( $oPlugin ) ) {
			$this->updateRelatedEntities( $oPlugin );
		} else {
			$aParams[ 'object_id' ] = $oUpdMainEnt->getId();
			$this->save( $aParams, 'update', NULL, $oPlugin );
		}
	}
	
	//
	public function doSubDelAction( $oMainEnt, $aParams, $oPlugin = NULL ) {
		$this->_iObjectId = $oMainEnt->getId();
		$this->delete( $oPlugin );		
	}
	
	
	
	
	// $aQueryParams ???
	// $aPostData ???
	// $aParams ???
	// updateRelatedEntities( $aQueryParams, $aPostData, $aParams )
	public function updateRelatedEntities( $oPlugin = NULL ) {
		
		global $wpdb;
		
		$sObjectType = $this->getObjectType( $oPlugin );
		
		$oMng = $this->getManage( $oPlugin );
		$sManageClass = get_class( $oMng );
		
		if ( is_array( $aSub = $_POST[ $sObjectType ] ) ) {
			
			$aInsIds = $wpdb->aInsertIds[ $sManageClass ];
			
			$sPrefix = $this->getPrefixForDoc( $oPlugin );
			$sContactSubType = $this->getContactSubType( $oPlugin );
			$aFieldList = $this->getFields( $oPlugin );
			
			foreach ( $aSub as $i => $aRow ) {
				$aVals = array();
				$aParams = array( 'object_id' => ( ( $aInsIds[ $i ] ) ? $aInsIds[ $i ] : $i ) );
				foreach ( $aFieldList as $sField ) {
					$aVals[ $sField ] = $aRow[ $sPrefix . $sField ];
				}
				$this->save( $aParams, 'update', $aVals, $oPlugin );
			}
						
		}
		
		$aDelIds = $wpdb->aDeleteIds[ $sManageClass ];
		if ( count( $aDelIds ) > 0 ) {
			foreach ( $aDelIds as $iObjectId ) {
				$this->_iObjectId = $iObjectId;
				$this->delete( $oPlugin );
			}
		}
	
	}
	
	
	
	
	////// rail functionality
	
	//
	public function getDetailFields( $oPlugin = NULL ) {
		
		// add some auto fields
		$aDetailFields = array(
			'object_id' => array(
				'auto' => TRUE
			),
			'objtype_id' => array(
				'auto' => TRUE
			),
			'subtype_id' => array(
				'auto' => TRUE
			)
		);
		
		$aFields = $this->getFields( $oPlugin );
		$aFieldLabels = $this->getFieldLabels( $oPlugin );
		
		foreach ( $aFields as $sField ) {
			$aFieldParams = array( 'title' => $aFieldLabels[ $sField ] );
			
			// TO DO: add "salutation"
			
			if ( in_array( $sField, array( 'lang_id' ) ) ) {
				
				$aFieldParams[ 'type' ] = 'select';
				
				$aFieldParams[ 'empty_choice' ] = array(
					'label' => 'Select a ' . $aFieldLabels[ $sField ],
					'atts' => array(
						'class' => 'default'
					)
				);
				
				if ( 'lang_id' == $sField ) {
					
					$aChoices = array();

					$aParams = array(
						'showposts' => -1,
						'posts_per_page' => -1
					);
					$aLangs = new Geko_Wp_Language_Query( $aParams, FALSE );
					
					foreach ( $aLangs as $oLang ) {
						$aChoices[ $oLang->getId() ] = $oLang->getTitle();
					}
					
				}
				
				$aFieldParams[ 'choices' ] = $aChoices;
			}
			$aDetailFields[ $sField ] = $aFieldParams;
		}
				
		return $this->_formatFields( $aDetailFields );
	}


	// plugin hook
	public function getDetailEntity( $oParEnt, $oPlugin = NULL ) {
		
		if ( $oParEnt ) {
			
			$sQueryClass = $this->_sQueryClass;
			
			$sObjectType = $this->getObjectType( $oPlugin );
			$sContactSubType = $this->getContactSubType( $oPlugin );
			
			$aParams = array(
				'object_id' => $oParEnt->getId(),
				'objtype_id' => Geko_Wp_Options_MetaKey::getId( $sObjectType ),
				'subtype_id' => Geko_Wp_Options_MetaKey::getId( $sContactSubType )
			);
			
			$aEntities = new $sQueryClass( $aParams, FALSE );
			
			if ( $aEntities->getTotalRows() == 1 ) {
				return $aEntities->getOne();
			}
		
		}
		
	}
	
	
	//
	public function modifyDetailValues( $aPostVals, $oPlugin = NULL ) {
		
		if ( $oManage = $this->getManage( $oPlugin ) ) {
			$aPostVals[ 'object_id' ] = $oManage->getTargetEntityId();
		}
		
		$sObjectType = $this->getObjectType( $oPlugin );
		$aPostVals[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $sObjectType );

		$sContactSubType = $this->getContactSubType( $oPlugin );
		$aPostVals[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $sContactSubType );
		
		return $aPostVals;
	}
	
	//
	public function checkDetailValues( $aPostVals, $oPlugin = NULL ) {
		
		if ( $aPostVals[ 'object_id' ] ) return TRUE;
		
		return FALSE;
	}
	
	
	
	
	
}

