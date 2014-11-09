<?php

//
class Geko_Sql_Table_Field
{
	
	protected $_sFieldName = '';
	protected $_aParams = '';
	
	
	
	//
	public function __construct( $sFieldName, $aParams ) {
		$this->_sFieldName = $sFieldName;
		$this->_aParams = $aParams;
	}
	
	//
	public function getFieldName() {
		return $this->_sFieldName;
	}
	
	// alias of getFieldName()
	public function getName() {
		return $this->getFieldName();
	}
	
	//
	public function getFieldType() {
		return $this->_aParams[ 0 ];
	}

	// alias of getFieldType()
	public function getType() {
		return $this->getFieldType();
	}
	
	//
	public function isFieldType( $sType ) {
		return ( $this->_aParams[ 0 ] == strtolower( trim( $sType ) ) ) ? TRUE : FALSE ;
	}
	
	
	
	//
	public function isBool() {
		return ( 'bool' == $this->_aParams[ 0 ] ) ? TRUE : FALSE ;
	}
	
	//
	public function isInt() {
		
		if ( in_array( $this->_aParams[ 0 ], array(
			'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'bit',
			'year'
		) ) ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	//
	public function isFloat() {
		
		if ( in_array( $this->_aParams[ 0 ], array(
			'real', 'float', 'double', 'decimal', 'numeric'
		) ) ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	//
	public function isStr() {
		
		if ( in_array( $this->_aParams[ 0 ], array(
			'char', 'varchar',
			'tinytext', 'text', 'mediumtext', 'longtext',
			'date', 'time', 'datetime', 'timestamp'
		) ) ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	//
	public function isText() {
		
		if ( in_array( $this->_aParams[ 0 ], array(
			'tinytext', 'text', 'mediumtext', 'longtext'
		) ) ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	//
	public function isPrimaryKey() {
		
		if ( in_array( 'prky', $this->_aParams ) ) {
			return TRUE;
		}
		
		return FALSE;
	}
	
	//
	public function getDefaultValue() {
		
		if ( $mDefaultValue = $this->_aParams[ 'default' ] ) {
			return $mDefaultValue;
		}
		
		return NULL;
	}
	
	
}


