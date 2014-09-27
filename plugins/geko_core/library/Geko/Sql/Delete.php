<?php

//
class Geko_Sql_Delete
{
	
	//// constants
	
	// query
	const FROM = 1;
	const JOIN = 2;
	const ON = 3;
	const WHERE = 4;
	
	// special flags
	const KVP = 1;			// key-value pair
	
	
	// properties
	protected $_aFrom = array();
	protected $_aJoins = array();
	protected $_aOn = array();
	protected $_mCurrentJoinKey = '';
	protected $_aWhere = array();
	
	protected $_oDb = NULL;
	
	
	
	//// constructor
	public function __construct( $oDb = NULL ) {
		
		if ( NULL === $oDb ) {
			$oDb = Geko::get( 'db' );
		}
		
		$this->_oDb = $oDb;
	}
	
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
	public function join( $mValue, $sKey = NULL, $sType = 'JOIN', $iFlag = NULL ) {
		
		if ( NULL === $sKey ) {
			$this->_aJoins[] = array( $mValue, $sType, $iFlag );
			end( $this->_aJoins );
			$this->_mCurrentJoinKey = key( $this->_aJoins );
			reset( $this->_aJoins );
		} else {
			$this->_aJoins[ $sKey ] = array( $mValue, $sType, $iFlag );
			$this->_mCurrentJoinKey = $sKey;
		}
		
		return $this;
	}
	
	//
	public function joinLeft( $mValue, $sKey = NULL ) {
		return $this->join( $mValue, $sKey, 'LEFT JOIN' );
	}
	
	//
	public function joinInner( $mValue, $sKey = NULL ) {
		return $this->join( $mValue, $sKey, 'INNER JOIN' );
	}
	
	//
	public function joinOuter( $mValue, $sKey = NULL ) {
		return $this->join( $mValue, $sKey, 'OUTER JOIN' );
	}
	
	//
	public function joinStraight( $mValue, $sKey = NULL ) {
		return $this->join( $mValue, $sKey, 'STRAIGHT_JOIN' );
	}
	
	
	
	// IMPORTANT: This binds to the last join method that was called
	public function on( $sExpression, $mArgs = NULL, $sKey = NULL, $sConjunction = 'AND' ) {
		
		if ( NULL !== $sKey ) {
			// supply an associative index
			$this->_aOn[ $this->_mCurrentJoinKey ][ $sKey ] = array( array( $sExpression, $mArgs ), $sConjunction );
		} else {
			$this->_aOn[ $this->_mCurrentJoinKey ][] = array( array( $sExpression, $mArgs ), $sConjunction );
		}
		
		return $this;
	}
	
	// IMPORTANT: This binds to the last join method that was called
	public function orOn( $sExpression, $mArgs = NULL, $sKey = NULL ) {		
		return $this->on( $sExpression, $mArgs, $sKey, 'OR' );
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
	
	
	
	//// unset
	
	//
	public function unsetClause( $iClause = NULL, $mIndex = NULL, $mSubIndex = NULL ) {
		
		switch ( $iClause ) {
			
			case self::FROM :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aFrom );
					$this->_aFrom = array();
				} else {
					unset( $this->_aFrom[ $mIndex ] );	
				}
				
				break;
				
			case self::JOIN :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aJoins );
					$this->_aJoins = array();
				} else {
					unset( $this->_aJoins[ $mIndex ] );
				}
				
				break;
				
			case self::ON :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aOn );
					$this->_aOn = array();
				} else {
					if ( NULL === $mSubIndex ) {
						unset( $this->_aOn[ $mIndex ] );
					} else {
						unset( $this->_aOn[ $mIndex ][ $mSubIndex ] );
					}
				}
				
				break;
				
			case self::WHERE :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aWhere );
					$this->_aWhere = array();
				} else {
					unset( $this->_aWhere[ $mIndex ] );
				}
				
				break;
				
			default :
				
				unset( $this->_aFrom );
				$this->_aFrom = array();
				
				unset( $this->_aJoins );
				$this->_aJoins = array();
				
				unset( $this->_aOn );
				$this->_aOn = array();

				unset( $this->_aWhere );
				$this->_aWhere = array();
				
		}
		
		return $this;
	}
	
	
	//
	public function unsetFrom( $mIndex = NULL ) {
		return $this->unsetClause( self::FROM, $mIndex );
	}
	
	//
	public function unsetJoin( $mIndex = NULL ) {
		
		return $this
			->unsetClause( self::JOIN, $mIndex )
			->unsetOn( $mIndex )
		;
	}
	
	//
	public function unsetOn( $mIndex = NULL, $mSubIndex = NULL ) {
		return $this->unsetClause( self::ON, $mIndex, $mSubIndex );
	}
	
	//
	public function unsetWhere( $mIndex = NULL ) {
		return $this->unsetClause( self::WHERE, $mIndex );
	}
	
	
	
	
	
	
	//// has
	
	// standard methods
	
	//
	public function hasClause( $iClause = NULL, $mIndex = NULL, $mSubIndex = NULL ) {
		
		switch ( $iClause ) {
			
			case self::FROM :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aFrom ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aFields[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::JOIN :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aJoins ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aJoins[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::ON :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aOn ) > 0 ) ? TRUE : FALSE ;
				} else {
					if ( NULL === $mSubIndex ) {
						return ( $this->_aOn[ $mIndex ] ) ? TRUE : FALSE ;
					} else {
						return ( $this->_aOn[ $mIndex ][ $mSubIndex ] ) ? TRUE : FALSE ;
					}
				}
			
			case self::WHERE :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aWhere ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aWhere[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
		}
		
		return NULL;
	}
	
	//
	public function hasFrom( $mIndex = NULL ) {
		return $this->hasClause( self::FROM, $mIndex );
	}
	
	//
	public function hasJoin( $mIndex = NULL ) {
		return $this->hasClause( self::JOIN, $mIndex );
	}
	
	//
	public function hasOn( $mIndex = NULL, $mSubIndex = NULL ) {
		return $this->hasClause( self::ON, $mIndex, $mSubIndex );
	}

	//
	public function hasWhere( $mIndex = NULL ) {
		return $this->hasClause( self::WHERE, $mIndex );
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
				$sOutput .= sprintf( '%s, ', $this->encloseExpression( $aField[ 0 ] ) );
			}
		}
		
		// trim the trailing ', ' and re-introduce a ' '
		$sOutput = sprintf( '%s ', rtrim( $sOutput, ', ' ) );
		
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
	
	//
	protected function createKvpJoinClause( $aKvpJoin, $sKey ) {
		
		$sOutput = '';
		$aRegs = array();
		
		list( $sColumn, $sType, $iFlag ) = $aKvpJoin;
		
		if (
			( 'KVP' == $sType ) && 
			( is_string( $sColumn ) ) && 		// just making sure
			( preg_match( '/^(([0-9a-zA-Z_-]*[a-zA-Z_-]+)([0-9]+))\./', $sColumn, $aRegs ) ) && 
			( $sJoinKey = sprintf( '%s*', $aRegs[ 2 ] ) ) && 
			( $aJoinValue = $this->_aJoins[ $sJoinKey ] )
		) {
			
			$sRealJoinKey = $aRegs[1];
			
			list( $mRealTable, $sRealType ) = $aJoinValue;
			
			// reformat join expression
			$sOutput .= sprintf( '%s %s AS %s ON ', $sRealType, $this->encloseExpression( $mRealTable ), $sRealJoinKey );
			
			// reformat on expression
			$aOnFmt = array();
			foreach ( $this->_aOn[ $sJoinKey ] as $aOn ) {
				
				// replace with real join key
				$aOnItem[ 0 ][ 0 ] = str_replace( $sJoinKey, $sRealJoinKey, $aOn[ 0 ][ 0 ] );
				$aOnItem[ 1 ] = $aOn[ 1 ];		// and/or
				
				// replace * with field key
				if ( $mArgs = $aOn[ 0 ][ 1 ] ) {
					if ( is_array( $mArgs ) ) {
						foreach ( $mArgs as $mJoinArg ) {
							$aOnItem[ 0 ][ 1 ][] = $this->formatKvpArg( '*', $sKey, $mJoinArg );
						}
					} else {
						$aOnItem[ 0 ][ 1 ] = $this->formatKvpArg( '*', $sKey, $mArgs );
					}
				}
				
				$aOnFmt[] = $aOnItem;
			}
			
			$sOutput .= $this->createExpressionList( $aOnFmt );
		}
		
		return $sOutput;
	}
	
	
	//
	protected function formatKvpArg( $sFind, $sReplace, $mArg ) {
		if ( is_string( $mArg ) ) {
			return str_replace( $sFind, $sReplace, $mArg );
		} elseif ( $mArg instanceof Geko_Sql_Callback ) {
			$oCb = clone $mArg;
			$aArgs = $oCb->getArgs();
			foreach ( $aArgs as $i => $mSubArg ) {
				if ( is_string( $mSubArg ) ) $aArgs[ $i ] = str_replace( $sFind, $sReplace, $mSubArg );
			}
			return $oCb->setArgs( $aArgs );
		} else {
			return '';
		}
	}

	
	
	// output the completed query
	public function __toString() {
		
		//print_r($this);
		
		$sOutput = 'DELETE ';

		// aliases
		if ( count( $this->_aFrom ) > 0 ) {
			
			foreach ( $this->_aFrom as $mKey => $aField ) {
				if ( is_string( $mKey ) ) {
					$sOutput .= sprintf( '%s, ', $mKey );
				}
			}
			
			$sOutput = sprintf( '%s ', rtrim( $sOutput, ', ' ) );
		}
		
		// from clause
		if ( count( $this->_aFrom ) > 0 ) {
			$sOutput .= 'FROM ';
			$sOutput .= $this->createFieldList( $this->_aFrom );
		}
		
		// join clauses
		foreach ( $this->_aJoins as $sKey => $aValue ) {
			
			list( $mTable, $sType, $iFlag ) = $aValue;
			
			if ( self::KVP == $iFlag ) {
				$sOutput .= $this->createKvpJoinClause( $aValue, $sKey );
			} else {
				$sOutput .= sprintf( '%s %s AS %s ON ', $sType, $this->encloseExpression( $mTable ), $sKey );
				$sOutput .= $this->createExpressionList( $this->_aOn[ $sKey ] );
			}
		}
		
		// where clauses
		if ( count( $this->_aWhere ) > 0 ) {
			$sOutput .= sprintf( 'WHERE %s', $this->createExpressionList( $this->_aWhere ) );
		}
		
		// auto-prefix replacement
		if ( $oDb = $this->_oDb ) {
			$sOutput = $oDb->replacePrefixPlaceholder( $sOutput );
		}
		
		return trim( $sOutput );
		
	}
	
}

