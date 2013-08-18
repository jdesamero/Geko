<?php

//
class Geko_Sql_Delete
{
	
	//// constants
	
	
	// properties
	protected $_aFrom = array();
	protected $_aWhere = array();
	
	
	
	//// constructor
	public function __construct() { }
	
	//
	public function from( $mValue, $sKey = NULL, $iFlag = NULL ) {
		
		if ( NULL === $sKey ) {
			$this->_aFrom[] = array( $mValue, $iFlag );
		} else {
			$this->_aFrom[ $sKey ] = array( $mValue, $iFlag );
		}
		
		return $this;
	}
	
	//
	public function where( $sExpression, $mArgs = NULL, $sKey = NULL, $sConjunction = 'AND' ) {
		
		if ( NULL !== $sKey ) {
			// supply an associative index
			$this->_aWhere[ $sKey ] = array( array( $sExpression, $mArgs ), $sConjunction );
		} else {
			$this->_aWhere[] = array( array( $sExpression, $mArgs ), $sConjunction );
		}
		
		return $this;
	}
	
	
	
	
	////// helper functions to assemble query
	
	//
	private function encloseExpression( $mValue ) {
		
		if ( $mValue instanceof Geko_Sql_Select ) {
			return sprintf( '(%s)', strval( $mValue ) );
		} elseif ( is_array( $mValue ) ) {
			return $this->evaluateExpressionPair( $mValue );
		} else {
			return $mValue;
		}
	}
	
	// !!! TO DO: Repeated verbatim from Geko_Sql_Select !!!
	public function evaluateExpressionPair( $aExpressionPair ) {
		
		$aArgs = func_get_args();
		
		if ( ( count( $aArgs ) == 1 ) && ( is_array( $aArgs[ 0 ] ) ) ) {
			list( $sExpression, $mArgs ) = $aArgs[ 0 ];
		} elseif ( ( count( $aArgs ) == 2 ) && ( is_string( $aArgs[ 0 ] ) ) ) {
			$sExpression = $aArgs[ 0 ];
			$mArgs = $aArgs[ 1 ];
		}
		
		if ( NULL === $mArgs ) {
			
			return $sExpression;
		
		} elseif ( is_array( $mArgs ) ) {
			
			// evaluate instances of Geko_Sql_Callback as string
			foreach ( $mArgs as $i => $mArg ) {
				if ( $mArg instanceof Geko_Sql_Callback ) $mArgs[ $i ] = $mArg->evaluate();
			}
			
			// replace and values
			$aReplace = array();
			$aValues = array();
			
			
			// quoted and slashed IN
			$sQuoted = implode( "', '", Geko_String_Slashes::addDeep( $mArgs ) );
			
			if ( 1 == count( $mArgs ) ) {
				$aReplace[] = '* (?)';
				$aValues[] = sprintf( "= '%s'", $sQuoted );			
			} else {
				$aReplace[] = '* (?)';
				$aValues[] = sprintf( "IN ('%s')", $sQuoted );
			}
			
			$aReplace[] = '(?)';
			$aValues[] = sprintf( "('%s')", $sQuoted );
			
			
			// un-quoted and un-slashed IN
			$sUnquoted = implode( ', ', $mArgs );
			
			if ( 1 == count( $mArgs ) ) {
				$aReplace[] = '* ($)';
				$aValues[] = sprintf( '= %s', $sUnquoted );			
			} else {
				$aReplace[] = '* ($)';
				$aValues[] = sprintf( 'IN (%s)', $sUnquoted );
			}
			
			$aReplace[] = '($)';
			$aValues[] = sprintf( '(%s)', $sUnquoted );
			
			
			// replace :symbol with array value array('symbol' => 'value')
			foreach ( $mArgs as $sSymbol => $sValue )
			{
				$aReplace[] = sprintf( ':%s', $sSymbol );
				$aValues[] = sprintf( "'%s'", Geko_String_Slashes::add( $sValue ) );
			}
			
			//
			return str_replace( $aReplace, $aValues, $sExpression );
			
		} elseif ( $mArgs instanceof Geko_Sql_Select ) {
			
			return str_replace( '?', sprintf( '(%s)', strval( $mArgs ) ), $sExpression );
			
		} else {
			
			// evaluate Geko_Sql_Callback as string
			if ( $mArgs instanceof Geko_Sql_Callback ) $mArgs = $mArgs->evaluate();
			
			// replace and values
			$aReplace = array();
			$aValues = array();
			
			// ? gets quoted and slashes added
			$sQuoted = Geko_String_Slashes::add( $mArgs );
			
			$aReplace[] = '* (?)';
			$aValues[] = sprintf( "= '%s'", $sQuoted );
			
			$aReplace[] = '?';
			$aValues[] = sprintf( "'%s'", $sQuoted );
			
			// $ is unchanged
			$aReplace[] = '* ($)';
			$aValues[] = sprintf( '= %s', $mArgs );
			
			$aReplace[] = '$';
			$aValues[] = $mArgs;
			
			//
			return str_replace( $aReplace, $aValues, $sExpression );
			
		}
	}
	
	//
	private function createFieldList( $aFields ) {
		
		$sOutput = '';
		
		foreach ( $aFields as $mKey => $aField ) {
			
			// $aField, 0: value, 1: flag
			
			if ( is_string( $mKey ) ) {
				$sOutput .= sprintf( '%s AS %s, ', $this->encloseExpression( $aField[ 0 ] ), $mKey );
			} else {
				$sOutput .= $this->encloseExpression( $aField[ 0 ] ) . ', ';
			}
		}
		
		// trim the trailing ', ' and re-introduce a ' '
		$sOutput = rtrim( $sOutput, ', ' ) . ' ';
		
		return $sOutput;
	}
	
	//
	private function createExpressionList( $aExpressions ) {
		
		$sOutput = '';
		
		foreach ( $aExpressions as $sKey => $aValue ) {
			
			list( $mExpression, $sAndOr ) = $aValue;
			
			if ( '' != $sOutput ) {
				$sOutput .= sprintf( ' %s ', $sAndOr );
			}
			
			$sOutput .= sprintf( '(%s) ', $this->encloseExpression( $mExpression ) );
		}
		
		return $sOutput;
	}
	
	
	
	// output the completed query
	public function __toString() {
		
		//print_r($this);
		
		$sOutput = 'DELETE ';

		// aliases
		if ( count( $this->_aFrom ) > 0 ) {
			foreach ( $this->_aFrom as $mKey => $aField ) {
				if ( is_string( $mKey ) ) {
					$sOutput .= $mKey . ', ';
				}
			}
			$sOutput = rtrim( $sOutput, ', ' ) . ' ';
		}
		
		// from clause
		if ( count( $this->_aFrom ) > 0 ) {
			$sOutput .= 'FROM ';
			$sOutput .= $this->createFieldList( $this->_aFrom );
		}
		
		// where clauses
		if ( count( $this->_aWhere ) > 0 ) {
			$sOutput .= 'WHERE ' . $this->createExpressionList( $this->_aWhere );
		}
		
		return trim( $sOutput );
		
	}
	
}

