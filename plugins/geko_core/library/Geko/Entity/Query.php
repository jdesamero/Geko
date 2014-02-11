<?php

abstract class Geko_Entity_Query
	implements Iterator, ArrayAccess, Countable
{
	
	protected $_aParams = array();
	protected $_bAddToDefaultParams = TRUE;
	protected $_aData = array();							// arbitrary data
	
	protected $_sSqlQuery;
	protected $_iTotalRows;
	
	protected $_aEntities = array();
	protected $_iPos = 0;	
	
	protected $_sEntityClass = '';
	protected $_sManageClass = '';
	
	protected $_sDefaultField = 'Link';
	protected $_bProfileQuery = FALSE;
	protected $_bIsDefaultQuery = FALSE;
	
	protected $_aSubsets = array();
	
	/*
	 * if $mParams is NULL, use default query
	 * if $mParams is *not* NULL, look at $bAddToDefaultParams whether to:
	 *     - (true, default) Merge with default params
	 *     - (false) overwrite default params
	 * new Geko_Wp_Entity_Query( NULL, FALSE ) will force an empty initial state
	 */
	
	//
	public function __construct( $mParams = NULL, $bAddToDefaultParams = TRUE, $aData = array() ) {
		
		$aParams = array();
		$this->_bAddToDefaultParams = $bAddToDefaultParams;
		$this->_aData = $aData;
		
		$this->_sEntityClass = Geko_Class::resolveRelatedClass(
			$this, '_Query', '', $this->_sEntityClass
		);

		$this->_sManageClass = Geko_Class::resolveRelatedClass(
			$this->_sEntityClass, '', '_Manage', $this->_sManageClass
		);
		
		$this->_bIsDefaultQuery = ( !$mParams && $bAddToDefaultParams );
		
		if ( $mParams || is_array( $mParams ) || $bAddToDefaultParams ) {
			
			if ( !$mParams ) {
				
				$aParams = $this->getDefaultParams();
			
			} else {
				
				// format $mParams to $aParams
				if ( is_scalar( $mParams ) ) {
					parse_str( $mParams, $aParams );	// assuming $mParams is in a query string format
				} elseif ( is_array( $mParams ) ) {
					$aParams = $mParams;
				}
				
				// perform merge with default params if required
				if ( $bAddToDefaultParams ) {
					$aParams = array_merge(
						$this->getDefaultParams(),
						$aParams
					);
				}
				
			}
			
			$this->_bProfileQuery = ( $aParams[ '__profile_query' ] ) ? TRUE : FALSE;
			$this->_aParams = $this->modifyParams( $aParams );
			$this->init();
		}
		
	}
	
	
	
	//
	public function modifyParams( $aParams ) {
		return $aParams;
	}
	
	// implement by sub-class to process $aParams
	public function init() {
		
		// $this->_sSqlQuery = $this->constructQuery( $this->_aParams );
		$this->_aEntities = $this->getEntities( $this->_aParams );
		$this->_iTotalRows = $this->getFoundRows();
		
		return $this;
	}
	
	//
	public function setData( $sKey, $mValue ) {
		$this->_aData[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function getData( $sKey = NULL ) {
		if ( NULL !== $sKey ) return $this->_aData[ $sKey ];
		return $this->_aData;
	}
	
	
	
	//// initial helpers
	
	//
	public function getDefaultParams() {
		return array();
	}
	
	//
	public function wrapEntity( $iPos ) {
		
		$oEntity = $this->_aEntities[ $iPos ];
		
		if ( $oEntity instanceof Geko_Entity ) return $oEntity;
		
		if ( is_array( $this->_aParams[ '__merge_with_entity' ] ) ) {
			$oEntity = array_merge(
				(array) $oEntity,
				$this->_aParams[ '__merge_with_entity' ]
			);
		}
		
		$sEntityClass = ( $this->_sEntityClass ) ? $this->_sEntityClass : 'Geko_Entity';
		
		return new $sEntityClass( $oEntity, $this );
	}
	
	
	
	
	//// working with sets
	
	// Depracated, use setDefaultField() instead
	public function setDefaultImplodeField( $sDefaultImplodeField ) {
		return $this->setDefaultField( $sDefaultImplodeField );
	}
	
	//
	public function setDefaultField( $sDefaultField ) {
		$this->_sDefaultField = $sDefaultField;
		return $this;
	}
	
	/*
	 * An gather pattern is a string that contains ##<method suffix>##
	 * method suffix is a method name that starts with "get" minus the "get" part
	 * for instance: getId -> Id, getTheContent -> TheContent, getLink -> Link
	 */
	public function gather() {
		
		$aArgs = func_get_args();
		
		// $aArg[ 0 ] is a string that contains a bunch of patterns
		// $aArg[ 1 ] is an array of args to call for pattern 0
		// $aArg[ 2 ] for pattern 1, and so on
		
		$aRegs = array();
		$aRet = array();
		$bSimple = TRUE;
		
		$mPattern = array_shift( $aArgs );
		
		if ( is_array( $mPattern ) ) {
			$sPattern = $mPattern[ 0 ];
		} else {
			$sPattern = $mPattern;
		}
			
		if ( FALSE !== strpos( $sPattern, '%s' ) ) {
			$sPattern = sprintf( $sPattern, '##' . $this->_sDefaultField . '##' );
		}
		
		if ( preg_match_all( '/##([a-zA-Z0-9_]+)##/s', $sPattern, $aArgs ) ) {
			$aReplace = $aArgs[ 0 ];
			$aSuffixes = $aArgs[ 1 ];
			$bSimple = FALSE;
		}
		
		// gather
		foreach ( $this as $oEntity ) {
			if ( $bSimple ) {
				if ( method_exists( $oEntity, $this->_sDefaultField ) ) {
					$sOut = call_user_func_array(
						array( $oEntity, $this->_sDefaultField ),
						$aArgs[ 0 ]
					);
				} else {
					$sOut = '';
				}
			} else {
				$sOut = $sPattern;
				foreach ( $aSuffixes as $i => $sSuffix ) {
					
					$sMethod = 'get' . $sSuffix;
					$sEntityProperty = Geko_Inflector::underscore( $sSuffix );
					
					$bReplace = TRUE;
					
					if (
						( method_exists( $oEntity, $sMethod ) ) && 
						( 'value' != $sEntityProperty )
					) {
						$sReplacement = call_user_func_array( array( $oEntity, $sMethod ), $aArgs[ $i ] );
					} elseif ( $oEntity->hasEntityProperty( $sEntityProperty ) ) {
						// see if a corresponding entity value can be found
						$sReplacement = $oEntity->getEntityPropertyValue( $sEntityProperty );
					} else {
						$bReplace = FALSE;
					}
					
					if ( $bReplace ) {
						$aArgs[ $i ] = ( is_array( $aArgs[ $i ] ) ) ? $aArgs[ $i ] : array();
						$sOut = str_replace( $aReplace[ $i ], $sReplacement, $sOut );
					}
					
				}
			}
			$aRet[] = $sOut;
		}
		
		return $aRet;
	}
	
	//
	public function implode() {
		
		$aArgs = func_get_args();
		
		// $aArg[ 0 ] is a string that contains a bunch of patterns
		// $aArg[ 1 ] is an array of args to call for pattern 0
		// $aArg[ 2 ] for pattern 1, and so on
		
		$aRegs = array();
		$sDelim = NULL;
		
		$mPattern = array_shift( $aArgs );
		
		if ( is_array( $mPattern ) ) {
			$sPattern = $mPattern[ 0 ];
			$sDelim = $mPattern[ 1 ];
		} elseif (
			( is_string( $mPattern ) ) && 
			( FALSE === strpos( $mPattern, '%s' ) ) && 
			( preg_match( '/##([a-zA-Z0-9_]+)##/s', $mPattern ) )
		) {
			$sDelim = $sPattern;	// use the given pattern as delimiter
		}
		
		if ( NULL === $sDelim ) $sDelim = ', ';
		
		array_unshift( $aArgs, $mPattern );
		
		return implode( $sDelim, call_user_func_array( array( $this, 'gather' ), $aArgs ) );
	}
	
	
	//// accessors
	
	// should be result of SQL_CALC_FOUND_ROWS
	public function getTotalRows() {
		return $this->_iTotalRows;
	}
	
	//
	public function getSqlQuery() {
		return $this->_sSqlQuery;
	}
	
	//
	public function getParams() {
		return $this->_aParams;
	}
	
	//
	public function setParam( $sKey, $mValue ) {
		$this->_aParams[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function isEmpty() {
		return ( count( $this->_aEntities ) ) ? FALSE : TRUE;
	}
	
	//
	public function setProfileQuery( $bProfileQuery ) {
		$this->_bProfileQuery = $bProfileQuery;
		return $this;
	}
	
	//
	public function setRawEntities( $aEntities ) {
		$this->_aEntities = $aEntities;
		$this->_iTotalRows = count( $aEntities );
		return $this;
	}
	
	//
	public function getRawEntities() {
		return $this->_aEntities;
	}
	
	//
	public function getIsDefaultQuery() {
		return $this->_bIsDefaultQuery;
	}
	
	//
	public function getOne() {
		
		if ( $this->count() > 0 ) {
			return $this->wrapEntity( 0 );
		}
		
		return new $this->_sEntityClass;
	}
	
	
	//// Iterator interface methods
	
	//
	public function rewind() {
		$this->_iPos = 0;
	}
	
	//
	public function current() {
		return $this->wrapEntity( $this->_iPos );
	}

	//
	public function key() {
		return $this->_iPos;
	}
	
	//
	public function next() {
		++$this->_iPos;
	}

	//
	public function valid() {
		return isset( $this->_aEntities[ $this->_iPos ] );
	}
	
	
	//// ArrayAccess interface methods
	
	//
	public function offsetSet( $iOffset, $mValue ) {
		$this->_aEntities[ $iOffset ] = $mValue;
	}
	
	//
	public function offsetExists( $iOffset ) {
		return isset( $this->_aEntities[ $iOffset ] );
	}
	
	//
	public function offsetUnset( $iOffset ) {
		unset( $this->_aEntities[ $iOffset ] );
	}
	
	//
	public function offsetGet( $iOffset ) {
		return isset( $this->_aEntities[ $iOffset ] ) ? 
			$this->wrapEntity( $iOffset ) :
			NULL;
	}
	
	
	//// Countable interface methods
	
	//
	public function count() {
		return count( $this->_aEntities );
	}
	
	
	
	//// query methods
	
	// to be implemented by sub-classes that have to
	// query the database directly
	public function constructQuery( $aParams = NULL, $bReturnObject = FALSE ) {
		
		if ( NULL === $aParams ) {
			$aParams = $this->_aParams;
		}
		
		$oQuery = NULL;
		
		if ( $this->_bUseManageQuery && $this->_sManageClass ) {
			$oMng = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
			$oQuery = $oMng->getPrimaryTable()->getSelect();
		}
		
		if ( !$oQuery ) $oQuery = $this->createSqlSelect();
		
		// further manipulate by sub-class
		$oQuery = $this->modifyQuery( $oQuery, $aParams );
		
		return ( $bReturnObject ) ? $oQuery : strval( $oQuery ) ;
	}
	
	
	// can be overridden by superclass
	public function createSqlSelect() {
		return new Geko_Sql_Select();
	}
	
	
	// this part has to be completely database vendor agnostic!!!
	public function modifyQuery( $oQuery, $aParams ) {
		
		// $oQuery is an instance of Geko_Sql_Select
		
		
		//// distinct option
		
		if ( $aParams[ 'distinct' ] ) {
			$oQuery->distinct( TRUE );
		}
		
		
		//// sorting
		
		if ( is_array( $aParams[ 'orderby' ] ) ) {
			
			$aOrderBy = $aParams[ 'orderby' ];
			
			foreach ( $aOrderBy as $sKey => $sOrder ) {
				$oQuery->order( $sKey, $sOrder, $sKey );			
			}
			
		} else {
			
			$sOrderBy = $aParams[ 'orderby' ];
			$sOrderDir = $aParams[ 'order' ];
			
			// sorting direction
			if ( $sOrderDir ) {
				
				$sOrderDir = strtoupper( $sOrderDir );
				
				if ( !in_array( $sOrderDir, array( 'ASC', 'DESC', 'NONE' ) ) ) {
					$sOrderDir = 'ASC';
				} elseif ( 'NONE' == $sOrderDir ) {
					$sOrderDir = '';
				}
				
			} else {
				$sOrderDir = 'ASC';		// default
			}
			
			//
			if ( 'random' == $sOrderBy ) {
				
				// random
				$oQuery = $this->modifyQueryOrderRandom( $oQuery, $aParams );
				
			} elseif ( $sOrderBy ) {
				
				// arbitrary
				$oQuery->order( $sOrderBy, $sOrderDir, $sOrderBy );
			}
		}
		
		
		//// limit/offset
		
		if ( $iLimit = $aParams[ 'limit' ] ) {
			$oQuery->limit( $iLimit );
		}
		
		if ( $iOffset = $aParams[ 'offset' ] ) {
			$oQuery->offset( $iOffset );
		}
		
		return $oQuery;
	}
	
	// manipulate query object for random ordering
	public function modifyQueryOrderRandom( $oQuery, $aParams ) {
		
		// random, MySQL only!!!
		$oQuery->order( 'RAND()', '', 'random' );
		
		return $oQuery;
	}
	
	
	//
	public function getEntityQuery( $mParam ) {
		return ( is_array( $mParam ) ) ?
			$this->constructQuery( $mParam ) :
			$mParam
		;
	}
	
	//
	public function getFoundRows() {
		return count( $this->_aEntities );
	}
	
	//
	public function getEntities( $mParam ) {
		
		if ( $this->_bProfileQuery ) {
			echo $this->getEntityQuery( $mParam );
		}
		
		return array();
	}
	
	//
	public function getSingleEntity( $mParam ) {
		
		$aEntities = $this->getEntities( $mParam );
		if ( $aEntities[ 0 ] ) {
			return $aEntities[ 0 ];
		} else {
			return NULL;
		}
	}
	
	
	
	//// hooks
	
	// hook to allow subsets to be modified after init
	public function modifySubset( $sField ) {
		
	}
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//// gather/implode
		
		if ( 0 === strpos( strtolower( $sMethod ), 'gather' ) ) {
			$sField = substr_replace( $sMethod, '', 0, 6 );
			$sCallMethod = 'gather';
		}
		
		if ( 0 === strpos( strtolower( $sMethod ), 'implode' ) ) {
			$sField = substr_replace( $sMethod, '', 0, 7 );
			$sCallMethod = 'implode';
		}
		
		if ( $sField ) {
			if ( count( $this ) > 0 ) {
				
				$sPattern = '##' . $sField . '##';
				$mPattern = array_shift( $aArgs );
				
				if ( is_null( $mPattern ) ) {
					$mPattern = $sPattern; 
				} elseif ( is_string( $mPattern ) ) {
					if ( FALSE !== strpos( $mPattern, '%s' ) ) {
						$mPattern = array( $mPattern, '' );
					} else {
						$mPattern = array( $sPattern, $mPattern );
					}
				}
				
				if ( is_array( $mPattern ) && FALSE !== strpos( $mPattern[ 0 ], '%s' ) ) {
					$mPattern[ 0 ] = sprintf( $mPattern[ 0 ], $sPattern );
				}
				
				return $this->$sCallMethod( $mPattern, $aArgs );
				
			} else {
				
				return ( 'implode' == $sCallMethod ) ? '' : array();
				
			}
		}
		
		//// subset
		
		if ( 0 === strpos( strtolower( $sMethod ), 'subset' ) ) {
			
			// $aArgs[ 0 ] - subset key
			// $aArgs[ 1 ] - custom callback if "__Custom"
			// $aArgs[ 2 ] - field name if "__Custom"
			
			$sSuffix = substr_replace( $sMethod, '', 0, 6 );
			$sField = ( '__Custom' == $sSuffix ) ? $aArgs[ 2 ] : $sSuffix;
			
			// gather
			
			if ( !is_array( $this->_aSubsets[ $sField ] ) ) {
				
				$aSubset = array();
				
				foreach ( $this as $oEntity ) {
					
					$sMethod = 'get' . $sSuffix;
					$sEntityProperty = Geko_Inflector::underscore( $sSuffix );
					
					$bGroup = TRUE;
					
					if ( '__Custom' == $sSuffix ) {
						// $aArgs[ 1 ] is a custom callback
						$mGroupVal = call_user_func( $aArgs[ 1 ], $oEntity, $aArgs );
					} elseif ( method_exists( $oEntity, $sMethod ) ) {
						$mGroupVal = $oEntity->$sMethod();
					} elseif ( $oEntity->hasEntityProperty( $sEntityProperty ) ) {
						// see if a corresponding entity value can be found
						$mGroupVal = $oEntity->getEntityPropertyValue( $sEntityProperty );
					} else {
						$bGroup = FALSE;
					}
					
					if ( $bGroup ) $aSubset[ $mGroupVal ][] = $oEntity;
					
				}
				
				$this->_aSubsets[ $sField ] = $aSubset;
				$this->modifySubset( $sField );
				
			}
			
			$sEntityQueryClass = get_class( $this );
			$oQuery = new $sEntityQueryClass( NULL, FALSE );
			
			if ( '__ALL__' == $aArgs[ 0 ] ) {
				$aAll = array();
				foreach ( $this->_aSubsets[ $sField ] as $aSubset ) $aAll = array_merge( $aAll, $aSubset );
				return $oQuery->setRawEntities( $aAll );
			} elseif ( ( FALSE !== $aArgs[ 0 ] ) && ( NULL !== $aArgs[ 0 ] ) ) {
				return $oQuery->setRawEntities( $this->_aSubsets[ $sField ][ $aArgs[ 0 ] ] );			
			}
			
			// no subset key was given, so return the keys that where gathered for the field
			return array_keys( $this->_aSubsets[ $sField ] );
		}
		
		throw new Exception( 'Invalid method ' . get_class( $this ) . '::' . $sMethod . '() called.' );
	}
	
	//
	public function __toString() {
		return $this->implode( '%s' );
	}
	
	
	
}

