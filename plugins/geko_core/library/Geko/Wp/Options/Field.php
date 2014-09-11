<?php

//
class Geko_Wp_Options_Field
{
	
	protected $_aField = array();
	protected $_oDbField = NULL;
	
	
	//// static methods
	
	//
	public static function wrapSqlField( $oDbField = NULL, $aParams = array() ) {
		
		$aField = array();
		
		if (
			( is_object( $oDbField ) ) && 
			( is_a( $oDbField, 'Geko_Sql_Table_Field' ) )
		) {
			$aField[ '__db_field' ] = $oDbField;
			if ( !$aField[ '__field' ] ) {
				$aField[ '__field' ] = $oDbField->getFieldName();
			}
		}
		
		$aField = array_merge( $aField, $aParams );
		
		return new Geko_Wp_Options_Field( $aField );
	}
	
	
	//// methods
	
	//
	public function __construct( $aField ) {
		$this->_aField = $aField;
		if ( $oDbField = $aField[ '__db_field' ] ) {
			$this->_oDbField = $oDbField;
		}
	}
	
	//
	public function getName() {
		return $this->_aField[ '__field' ];
	}
	
	//
	public function setName( $sFieldName ) {
		$this->_aField[ '__field' ] = $sFieldName;
		return $this;
	}
	
	//
	public function getLink() {
		return $this->_aField[ 'link' ];
	}
	
	//
	public function isAuto() {
		return ( $this->_aField[ 'auto' ] ) ? TRUE : FALSE ;
	}

	//
	public function isAutoDb() {
		return ( $this->_aField[ 'auto_db' ] ) ? TRUE : FALSE ;
	}
	
	//
	public function getTitle() {
		if ( !$sTitle = $this->_aField[ 'title' ] ) {
			$sTitle = Geko_Inflector::humanize( $this->_aField[ '__field' ] );
		}
		return $sTitle;
	}
	
	//
	public function hasDbField() {
		return ( $this->_oDbField ) ? TRUE : FALSE ;
	}
	
	
	
	//
	public function getFormat() {
		if ( $this->isInt() ) return '%d';
		elseif ( $this->isFloat() ) return '%f';
		return '%s';
	}
	
	//
	public function getFormattedValue( $mValue ) {
		if ( $this->isInt() ) $mValue = intval( $mValue );
		elseif ( $this->isFloat() ) $mValue = floatval( $mValue );
		else $mValue = strval( stripslashes( $mValue ) );
		return $mValue;
	}
	
	
	//
	public function isPrimaryKey() {
		if ( $this->hasDbField() ) {
			return $this->_oDbField->isPrimaryKey();
		}
		return FALSE;
	}

	//
	public function isText() {
		if ( $this->hasDbField() ) {
			return $this->_oDbField->isText();
		}
		return FALSE;
	}	
	
	
	////// rail functionality
	
	protected $_oManage;
	
	//
	public function setManage( $oManage ) {
		$this->_oManage = $oManage;
		return $this;
	}
	
	//
	public function getSmartType() {
		return $this->_aField[ 'type' ];
	}
	
	// resolve widget type
	// if $bTextOnly is TRUE, method will only return text
	//    (and not other types like objects, arrays, etc)
	public function getWidgetType( $bTextOnly = FALSE ) {
		
		$aConcreteTypes = array(
			'text', 'textarea', 'radio', 'checkbox', 'checkbox_multi',
			'select', 'select_multi', 'hidden', 'span'
		);
		
		$sType = $this->getSmartType();
		
		// check if type is concrete
		if ( in_array( $sType, $aConcreteTypes ) ) {
			return $sType;
		}
		
		// check if type if a plugin
		if (
			( class_exists( $sType ) ) && 
			( is_subclass_of( $sType, 'Geko_Wp_Options_Plugin' ) )
		) {
			if ( !$bTextOnly ) {
				$oPlugin = new $sType();
				if ( $oManage = $this->_oManage ) {
					$oPlugin->setManage( $oManage );
				}
				$oPlugin->setProperties( $this->_aField );
				return $oPlugin;
			} else {
				return 'plugin';
			}
		}
		
		$sField = $this->getName();
		
		if ( 'Geko_Wp_Enumeration' == $sType ) {
			return 'select';
		} elseif ( in_array( $sField, array( 'date_created', 'date_modified' ) ) ) {
			return 'span';
		} elseif ( $this->isPrimaryKey() ) {
			return 'hidden';
		} elseif ( $this->isText() ) {
			return 'textarea';
		}
		
		// default
		return 'text';
	}
	
	//// display methods
	
	// add
	
	//
	public function isSkipAddMode() {
		$sField = $this->getName();
		return ( 
			( $this->isAuto() ) || 
			( $this->isPrimaryKey() ) ||
			( in_array( $sField, array( 'date_created', 'date_modified' ) ) )
		);
	}
	
	//
	public function getAddModeRowClasses() {
		return '';
	}
	
	// edit
	
	//
	public function isSkipEditMode() {
		$sField = $this->getName();
		return ( $this->isAuto() );
	}
		
	//
	public function getEditModeRowClasses() {
		if ( $this->isPrimaryKey() ) return 'hidden';
	}
	
	// details
	
	//
	public function isSkipDetailMode() {
		$sField = $this->getName();
		return ( $this->isAuto() );
	}
	
	//
	public function getDetailModeRowClasses() {
		if ( $this->isPrimaryKey() ) return 'hidden';	
	}
	
	// widget
	
	//
	public function getWidgetParams() {
		
		$sField = $this->getName();
		
		$aParams = array();
		
		$sSmartType = $this->getSmartType();
		if ( 'Geko_Wp_Enumeration' == $sSmartType ) {
			$aParams[ 'empty_choice' ] = '- Select -';
			$aEnum = Geko_Wp_Enumeration_Query::getSet( $sField );
			$aChoices = array();
			foreach ( $aEnum as $oEnum ) {
				$aChoices[ $oEnum->getId() ] = $oEnum->getTitle();
			}
			$aParams[ 'choices' ] = $aChoices;
		}
		
		$sConcreteType = $this->getWidgetType( TRUE );
		
		$aValidParams = array(
			'select' => array(
				'empty_choice',
				'choices'
			)
		);
		
		if ( $aParamsCheck = $aValidParams[ $sConcreteType ] ) {
			foreach ( $aParamsCheck as $sParam ) {
				if ( !$aParams[ $sParam ] ) {
					$aParams[ $sParam ] = $this->_aField[ $sParam ];
				}
			}
		}
		
		return $aParams;
	}
	
	//// crud methods
	
	//
	public function isAutoInsert() {
		$sField = $this->getName();
		return in_array( $sField, array( 'date_created', 'date_modified' ) );
	}
	
	//
	public function isSkipInsert() {
		
		if (
			( $this->isAutoDb() ) || 
			( $this->isPrimaryKey() )
		) return TRUE;
		
		// $sField = $this->getName();
		return FALSE;
	}
	
	//
	public function isAutoUpdate() {
		$sField = $this->getName();
		return in_array( $sField, array( 'date_modified' ) );
	}

	//
	public function isSkipUpdateField() {
		
		$sField = $this->getName();
		if (
			( $this->isAutoDb() ) || 
			( in_array( $sField, array( 'date_created' ) ) )
		) return TRUE;
		
		return FALSE;
	}
	
	//
	public function isWhereUpdate() {
		
		if (
			( $this->isAuto() ) || 
			( $this->isPrimaryKey() )
		) return TRUE;
		
		// $sField = $this->getName();
		return FALSE;
	}
	
	//
	public function getSmartFormattedValue( $mValue ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sField = $this->getName();
		
		if ( in_array( $sField, array( 'date_created', 'date_modified' ) ) ) {
			return $oDb->getTimestamp();
		}
		
		return $this->getFormattedValue( $mValue );
	}
	
	//
	public function returnsUpdateValue() {
		$sField = $this->getName();
		return in_array( $sField, array( 'date_modified' ) );
	}
	
	//
	public function isWhereDelete() {
		
		if (
			( $this->isAuto() ) || 
			( $this->isPrimaryKey() )
		) return TRUE;
		
		// $sField = $this->getName();
		return FALSE;
	}
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//// delegate calls to Geko_Sql_Table_Field
		if ( $oDbField = $this->_oDbField ) {
			if ( method_exists( $oDbField, $sMethod ) ) {
				return call_user_func_array(
					array( $oDbField, $sMethod ),
					$aArgs
				);
			}
		}
		
		throw new Exception( 'Invalid method ' . __CLASS__ . '::' . $sMethod . '() called.' );
	}
	
	
}


