<?php

//
class Geko_Sql_Select
{
	//// constants
	
	// query
	const OPTION = 0;
	const FIELD = 1;
	const FROM = 2;
	const JOIN = 3;
	const ON = 4;
	const WHERE = 5;
	const GROUP = 6;
	const HAVING = 7;
	const UNION = 8;
	const ORDER = 9;
	const LIMIT = 10;
	const OFFSET = 11;
	
	// special flags
	const KVP = 1;			// key-value pair
	
	
	// properties
	protected $_bDistinct = FALSE;
	protected $_aOptions = array();
	protected $_aFields = array();
	protected $_aFrom = array();
	protected $_aJoins = array();
	protected $_aOn = array();
	protected $_mCurrentJoinKey = '';
	protected $_aWhere = array();
	protected $_aGroup = array();
	protected $_aHaving = array();
	protected $_aUnion = array();
	protected $_aOrder = array();
	protected $_iLimit = FALSE;
	protected $_iOffset = FALSE;
	
	protected $_aProperties = array();			// allow to set arbitrary properties associated
												// to a query, useful if it is being passed
												// along and modified
	
	protected $_oDb = NULL;
	
	
	
	//// constructor
	public function __construct( $oDb = NULL ) {
		$this->_oDb = $oDb;
	}
	
	
	
	//// accessors
	
	//
	public function setProperty( $sKey, $mValue ) {
		$this->_aProperties[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function getProperty( $sKey ) {
		return $this->_aProperties[ $sKey ];
	}
	
	//
	public function hasProperty( $sKey ) {
		return isset( $this->_aProperties[ $sKey ] );
	}
	
	//
	public function unsetProperty( $sKey = NULL ) {
		
		if ( NULL === $sKey ) {
			unset( $this->_aProperties );
			$this->_aProperties = array();
		} else {
			unset( $this->_aProperties[ $sKey ] );
		}
		
		return $this;
	}
	
	
	
	//// query methods
	
	// standard methods
	
	//
	public function distinct( $bDistinct ) {
		
		$this->_bDistinct = $bDistinct;
		return $this;
	}
	
	//
	public function option( $sValue, $sKey = NULL ) {
		
		if ( NULL === $sKey ) {
			$this->_aOptions[] = $sValue;
		} else {
			$this->_aOptions[ $sKey ] = $sValue;		
		}
		
		return $this;
	}
	
	//
	public function field( $mValue, $sKey = NULL, $iFlag = NULL ) {
		
		if ( NULL === $sKey ) {
			$this->_aFields[] = array( $mValue, $iFlag );
		} else {
			$this->_aFields[ $sKey ] = array( $mValue, $iFlag );
		}
		
		return $this;
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
	
	//
	public function orWhere( $sExpression, $mArgs = NULL, $sKey = NULL ) {
		return $this->where( $sExpression, $mArgs, $sKey, 'OR' );
	}
	
	//
	public function group( $sField ) {
		
		$this->_aGroup[] = $sField;
		return $this;
	}
	
	//
	public function having( $sExpression, $mArgs = NULL, $sKey = NULL, $sConjunction = 'AND' ) {
		
		if ( NULL !== $sKey ) {
			// supply an associative index
			$this->_aHaving[ $sKey ] = array( array( $sExpression, $mArgs ), $sConjunction );
		} else {
			$this->_aHaving[] = array( array( $sExpression, $mArgs ), $sConjunction );
		}
		
		return $this;
	}
	
	//
	public function orHaving( $sExpression, $mArgs = NULL, $sKey = NULL ) {		
		return $this->having( $sExpression, $mArgs, $sKey, 'OR' );
	}
	
	//
	public function order( $sField, $sDirection = 'ASC', $sKey = NULL ) {
		
		if ( NULL !== $sKey ) {
			// supply an associative index
			$this->_aOrder[ $sKey ] = trim( sprintf( '%s %s', $sField, $sDirection ) );
		} else {
			$this->_aOrder[] = trim( sprintf( '%s %s', $sField, $sDirection ) );
		}
		
		return $this;
	}
	
	//
	public function limit( $iLimit ) {
		
		$this->_iLimit = $iLimit;
		return $this;
	}
	
	//
	public function offset( $iOffset ) {
		
		$this->_iOffset = $iOffset;
		return $this;
	}
	
	//
	public function limitOffset( $iLimit, $iOffset ) {
		
		return $this
			->limit( $iLimit )
			->offset( $iOffset )
		;
	}
	
	//
	public function union( $mSelect, $sKey = NULL ) {
		
		if ( NULL !== $sKey ) {
			// supply an associative index
			$this->_aUnion[ $sKey ] = $mSelect;
		} else {
			$this->_aUnion[] = $mSelect;		
		}
		
		return $this;
	}
	
	
	// kvp methods
	
	//
	public function fieldKvp( $mValue, $sKey = NULL ) {
		
		return $this
			->field( $mValue, $sKey, self::KVP )
			->join( $mValue, $sKey, 'KVP', self::KVP )			// invoke a kvp join
		;
	}
	
	//
	public function joinKvp( $mValue, $sKey = NULL, $sType = 'JOIN' ) {
		return $this->join( $mValue, $sKey, $sType, self::KVP );
	}
	
	//
	public function joinLeftKvp( $mValue, $sKey = NULL ) {
		return $this->joinKvp( $mValue, $sKey, 'LEFT JOIN' );
	}
	
	//
	public function joinInnerKvp( $mValue, $sKey = NULL ) {
		return $this->joinKvp( $mValue, $sKey, 'INNER JOIN' );
	}
	
	//
	public function joinOuterKvp( $mValue, $sKey = NULL ) {
		return $this->joinKvp( $mValue, $sKey, 'OUTER JOIN' );
	}
	
	//
	public function joinStraightKvp( $mValue, $sKey = NULL ) {
		return $this->joinKvp( $mValue, $sKey, 'STRAIGHT_JOIN' );
	}
	
	
	
	
	//// unset
	
	// standard methods
	
	//
	public function unsetClause( $iClause = NULL, $mIndex = NULL, $mSubIndex = NULL ) {
		
		switch ( $iClause ) {
			
			case self::OPTION :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aOptions );
					$this->_aOptions = array();
				} else {
					unset( $this->_aOptions[ $mIndex ] );	
				}
				
				break;
				
			case self::FIELD :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aFields );
					$this->_aFields = array();
				} else {
					unset( $this->_aFields[ $mIndex ] );	
				}
				
				break;
				
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
				
			case self::GROUP :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aGroup );
					$this->_aGroup = array();
				} else {
					unset( $this->_aGroup[ $mIndex ] );
				}
				
				break;
				
			case self::HAVING :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aHaving );
					$this->_aHaving = array();
				} else {
					unset( $this->_aHaving[ $mIndex ] );
				}
				
				break;
				
			case self::UNION :
				
				if ( NULL === $mIndex) {
					unset( $this->_aUnion );
					$this->_aUnion = array();
				} else {
					unset( $this->_aUnion[ $mIndex ] );
				}
				
				break;
				
			case self::ORDER :
				
				if ( NULL === $mIndex ) {
					unset( $this->_aOrder );
					$this->_aOrder = array();
				} else {
					unset( $this->_aOrder[ $mIndex ] );
				}
				
				break;
				
			case self::LIMIT :
				
				$this->_iLimit = FALSE;
				break;
				
			case self::OFFSET :
				
				$this->_iOffset = FALSE;
				break;
			
			default :
				
				// unset all
				unset( $this->_aFields );
				$this->_aFields = array();
				
				unset( $this->_aFrom );
				$this->_aFrom = array();
				
				unset( $this->_aJoins );
				$this->_aJoins = array();
				
				unset( $this->_aOn );
				$this->_aOn = array();
					
				unset( $this->_aWhere );
				$this->_aWhere = array();
				
				unset( $this->_aGroup );
				$this->_aGroup = array();
				
				unset( $this->_aHaving );
				$this->_aHaving = array();
				
				unset( $this->_aUnion );
				$this->_aUnion = array();
				
				unset( $this->_aOrder );
				$this->_aOrder = array();
				
				$this->_iLimit = FALSE;
				$this->_iOffset = FALSE;
				
		}
		
		return $this;
	}
	
	//
	public function unsetOption( $mIndex = NULL ) {
		return $this->unsetClause( self::OPTION, $mIndex );
	}
	
	//
	public function unsetField( $mIndex = NULL ) {
		return $this->unsetClause( self::FIELD, $mIndex );
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
	
	//
	public function unsetGroup( $mIndex = NULL ) {
		return $this->unsetClause( self::GROUP, $mIndex );
	}
	
	//
	public function unsetHaving( $mIndex = NULL ) {
		return $this->unsetClause( self::HAVING, $mIndex );
	}
	
	//
	public function unsetUnion( $mIndex = NULL ) {
		return $this->unsetClause( self::UNION, $mIndex );
	}
	
	//
	public function unsetOrder( $mIndex = NULL ) {
		return $this->unsetClause( self::ORDER, $mIndex );
	}
	
	//
	public function unsetLimit() {
		return $this->unsetClause( self::LIMIT );
	}
	
	//
	public function unsetOffset() {
		return $this->unsetClause( self::OFFSET );
	}
	
	//
	public function unsetLimitOffset() {
		
		return $this
			->unsetLimit()
			->unsetOffset()
		;
	}
	
	
	
	
	
	
	
	
	//// has
	
	// standard methods
	
	//
	public function hasClause( $iClause = NULL, $mIndex = NULL, $mSubIndex = NULL ) {
		
		switch ( $iClause ) {
			
			case self::OPTION :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aOptions ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aOptions[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::FIELD :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aFields ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aFields[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
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
				
			case self::GROUP :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aGroup ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aGroup[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::HAVING :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aHaving ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aHaving[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::UNION :
				
				if ( NULL === $mIndex) {
					return ( count( $this->_aUnion ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aUnion[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::ORDER :
				
				if ( NULL === $mIndex ) {
					return ( count( $this->_aOrder ) > 0 ) ? TRUE : FALSE ;
				} else {
					return ( $this->_aOrder[ $mIndex ] ) ? TRUE : FALSE ;
				}
				
			case self::LIMIT :
				
				return ( FALSE !== $this->_iLimit ) ? TRUE : FALSE ;
				
			case self::OFFSET :

				return ( FALSE !== $this->_iOffset ) ? TRUE : FALSE ;
				
		}
		
		return NULL;
	}
	
	//
	public function hasOption( $mIndex = NULL ) {
		return $this->hasClause( self::OPTION, $mIndex );
	}
	
	//
	public function hasField( $mIndex = NULL ) {
		return $this->hasClause( self::FIELD, $mIndex );
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
	
	//
	public function hasGroup( $mIndex = NULL ) {
		return $this->hasClause( self::GROUP, $mIndex );
	}
	
	//
	public function hasHaving( $mIndex = NULL ) {
		return $this->hasClause( self::HAVING, $mIndex );
	}
	
	//
	public function hasUnion( $mIndex = NULL ) {
		return $this->hasClause( self::UNION, $mIndex );
	}
	
	//
	public function hasOrder( $mIndex = NULL ) {
		return $this->hasClause( self::ORDER, $mIndex );
	}
	
	//
	public function hasLimit() {
		return $this->hasClause( self::LIMIT );
	}
	
	//
	public function hasOffset() {
		return $this->hasClause( self::OFFSET );
	}
	
	//
	public function hasLimitOffset() {
		return ( $this->hasLimit() && $this->hasOffset() ) ? TRUE : FALSE ;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	// kvp methods
	
	//
	public function unsetFieldKvp( $mIndex = NULL ) {
		
		return $this
			->unsetField( $mIndex )
			->unsetJoin( $mIndex )
		;
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
	
	
	//
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
	
	//
	private function createKvpJoinClause( $aKvpJoin, $sKey ) {
		
		$sOutput = '';
		$aRegs = array();
		
		list( $sColumn, $sType, $iFlag ) = $aKvpJoin;
		
		if (
			( 'KVP' == $sType ) && 
			( is_string( $sColumn ) ) && 		// just making sure
			( preg_match( '/^(([0-9a-zA-Z_-]*[a-zA-Z_-]+)([0-9]+))\./', $sColumn, $aRegs ) ) && 
			( $sJoinKey = $aRegs[2] . '*' ) && 
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
	private function formatKvpArg( $sFind, $sReplace, $mArg ) {
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
		
		$sOutput = 'SELECT ';
		
		// distinct clause
		if ( TRUE == $this->_bDistinct ) {
			$sOutput .= 'DISTINCT ';
		}
		
		// options
		if ( count( $this->_aOptions ) > 0 ) {	
			$sOutput .= implode( ' ', $this->_aOptions ) . ' ';
		}
		
		// fields
		if ( count( $this->_aFields ) > 0 ) {	
			$sOutput .= $this->createFieldList( $this->_aFields );
		} else {
			$sOutput .= ( count( $this->_aFrom ) > 0 ) ? '* ' : '1 ';
		}
		
		// from clause
		if ( count( $this->_aFrom ) > 0 ) {
			$sOutput .= sprintf( 'FROM %s ', $this->createFieldList( $this->_aFrom ) );
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
			$sOutput .= sprintf( 'WHERE %s ', $this->createExpressionList( $this->_aWhere ) );
		}
		
		// group by clauses
		if ( count( $this->_aGroup ) > 0 ) {
			$sOutput .= sprintf( 'GROUP BY %s ', implode( ', ', $this->_aGroup ) );
		}
		
		// having clauses
		if ( count( $this->_aHaving ) > 0 ) {
			$sOutput .= sprintf( 'HAVING %s ', $this->createExpressionList( $this->_aHaving ) );
		}
		
		// union clauses
		foreach ( $this->_aUnion as $mSelect ) {
			$sOutput .= sprintf( 'UNION %s ', strval( $mSelect ) );
		}
		
		// order by clauses
		if ( count( $this->_aOrder ) > 0 ) {
			$sOutput .= sprintf( 'ORDER BY %s ', implode( ', ', $this->_aOrder ) );
		}
		
		// limit
		if ( FALSE !== $this->_iLimit ) {
			$sOutput .= sprintf( 'LIMIT %s ', $this->_iLimit );
		}
		
		// offset
		if ( FALSE !== $this->_iOffset ) {
			$sOutput .= sprintf( 'OFFSET %s ', $this->_iOffset );
		}
		
		// auto-prefix replacement
		if ( $oDb = $this->_oDb ) {
			$sOutput = $oDb->replacePrefixPlaceholder( $sOutput );
		}
		
		return trim( $sOutput );
	}
	
	
}


