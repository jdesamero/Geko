<?php
/*
 * "geko_core/library/Geko/App/Finance/Import.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Import
{
	
	protected $_sCsvImportFile = '';
	protected $_bSkipFirstLine = TRUE;
	
	protected $_aDescriptionMap = array();
	protected $_aGroupMap = array();
	
	protected $_iOwnerId = 0;
	protected $_iAccountId = 0;
	
	
	
	//
	public function __construct( $aParams ) {
		
		$this->_iOwnerId = intval( $aParams[ 'owner_id' ] );
		
		$this->_sCsvImportFile = $sCsvImportFile = $aParams[ 'file' ];
		
		if ( $aDescMap = $aParams[ 'description_map' ] ) {
			$this->_aDescriptionMap = $aDescMap;
		}
		
		if ( $aGroupMap = $aParams[ 'group_map' ] ) {
			$this->_aGroupMap = $aGroupMap;
		}
		
		
		// the import file would have a standard notation:
		// account_slug-startYYYYMMDD-endYYYYMMDD.csv
		
		$sFilePart = pathinfo(  $sCsvImportFile, PATHINFO_FILENAME );
		
		list( $sAccountSlug, $iStartDate, $iEndDate ) = explode( '-', $sFilePart );
		
		
		$this->_iAccountId = $this->getAccountId( $sAccountSlug );
		
	}
	
	
	// get account id from slug
	public function getAccountId( $sAccountSlug ) {

		$aAccounts = Geko_App_Finance::_getAccounts( $this->_iOwnerId );
		
		$oAccount = $aAccounts->subsetoneSlug( $sAccountSlug );
		
		return $oAccount->getId();
	}
	
	
	//
	public function parseCsvData() {
		
		$aFormatted = array();
		
		$aCsv = array_map( 'str_getcsv', file( $this->_sCsvImportFile ) );
		
		foreach ( $aCsv as $aRow ) {
			
			if ( $this->_bSkipFirstLine ) {
				$this->_bSkipFirstLine = FALSE;
				continue;
			}
			
			if ( $aRowFmt = $this->formatCsvRow( $aRow ) ) {
				$aFormatted[] = $aRowFmt;
			}
			
		}
		
		$aFormatted = $this->groupItems( $aFormatted );
		
		return $aFormatted;
	}
	
	
	
	//
	protected function getGrouping( $sDetails ) {
		
		foreach ( $this->_aGroupMap as $sKey => $aParams ) {
			
			// $iDebitCredit not used here
			list( $iDebitCredit, $sPrefix ) = $aParams;

			if ( 0 === strpos( $sDetails, $sPrefix ) ) {
				
				return $sKey;
			}
			
		}
		
		return FALSE;
	}
	
	
	
	//
	protected function groupItems( $aFormatted ) {
		
		$aGrouped = array();
		$aSortOrder = array();
		
		
		// group fees together
		
		foreach ( $aFormatted as $i => $aEntry ) {
			
			$sDetails = $aEntry[ 'details' ];
			$sDateEntered = $aEntry[ 'date_entered' ];
			
			if ( $sGroupKey = $this->getGrouping( $sDetails ) ) {
				
				$sKey = sprintf( '%s:%s', $sGroupKey, $sDateEntered );
				
				
				if ( !array_key_exists( $sKey, $aGrouped ) ) {
					
					$aSortOrder[ $sKey ] = 1;
					$aGrouped[ $sKey ] = $aEntry;
					
				} else {
					
					$aItems = $aEntry[ 'items' ];
					
					$aSortOrder[ $sKey ]++;
					$aItems[ 0 ][ 'sort_order' ] = $aSortOrder[ $sKey ];
					
					$aGrouped[ $sKey ][ 'items' ] = array_merge(
						$aGrouped[ $sKey ][ 'items' ],
						$aItems
					);
					
				}
				
				unset( $aFormatted[ $i ] );
			}
			
		}
		
		
		// create auto expense entry for bank fees
		
		$aGroupedFmt = array();
		
		foreach ( $aGrouped as $sKey => $aEntry ) {
			
			list( $sGroupKey ) = explode( ':', $sKey );
			list( $iDebitCredit, $sDetails ) = $this->_aGroupMap[ $sGroupKey ];
			
			$aItemsFmt = array();
			
			$aItems = $aEntry[ 'items' ];
			
			$fTotal = 0;
			
			foreach ( $aItems as $aItem ) {
				
				$fTotal += $aItem[ 'amount' ];
				
				$aItem[ 'sort_order' ]++;
				
				$aItemsFmt[] = $aItem;
			}
			
			array_unshift( $aItemsFmt, array(
				'account_id' => $this->getAccountId( $sGroupKey ),
				'details' => $sDetails,
				'long_details' => '',
				'external_reference' => '',
				'debit_credit' => intval( $iDebitCredit ),
				'amount' => $fTotal,
				'sort_order' => 1
			) );
			
			$aEntry[ 'items' ] = $aItemsFmt;
			
			$aGroupedFmt[] = $aEntry;
		}
		
		
		
		$aFormatted = array_merge( $aFormatted, $aGroupedFmt );
		
		return $aFormatted;
	}
	
	
	
	//
	protected function getMapped( $sMatch, $aMap ) {
		
		foreach ( $aMap as $sKey => $sTrans ) {
			
			if ( 0 === strpos( $sMatch, $sKey ) ) {
				return $sTrans;
			}
			
		}
		
		return FALSE;
	}
	
	
	//
	protected function getMappedDescription( $sDescription ) {
		return $this->getMapped( $sDescription, $this->_aDescriptionMap );
	}
	
	
	
	//
	protected function formatCsvRow( $aRow ) {
		
		// output formatted entry/item, should be like this
		/* /
		array(
			'details' => '',
			'date_entered' => '',
			'owner_id' => $this->_iOwnerId,
			'items' => array(
				array(
					'account_id' => $this->_iAccountId,
					'details' => '',
					'long_details' => '',
					'external_reference' => '',
					'debit_credit' => 0,
					'amount' => 0,
					'sort_order' => 1
				)
			)
		);
		/* */
		
		if ( !$aRow[ 'owner_id' ] ) {
			$aRow[ 'owner_id' ] = $this->_iOwnerId;
		}
		
		if ( is_array( $aItems = $aRow[ 'items' ] ) ) {
			
			foreach ( $aItems as $i => $aItem ) {
				
				if ( !$aItem[ 'account_id' ] ) {
					$aItems[ $i ][ 'account_id' ] = $this->_iAccountId;
				}
				
			}
			
			$aRow[ 'items' ] = $aItems;
		}
		
		return $aRow;
	}
	
	
	// 
	public function commitData() {
	
		$aFormatted = $this->parseCsvData();
		
		foreach ( $aFormatted as $aRow ) {
			
			$aItems = $aRow[ 'items' ];
			
			unset( $aRow[ 'items' ] );
			
			$oEntry = new Geko_App_Finance_Entry();
			$oEntry
				->setEntityPropertyValue( $aRow )
				->save()
			;
			
			$iEntryId = intval( $oEntry->getId() );
			
			foreach ( $aItems as $aItem ) {
				
				$aItem[ 'entry_id' ] = $iEntryId;
				
				$oItem = new Geko_App_Finance_Item();
				$oItem
					->setEntityPropertyValue( $aItem )
					->save()
				;
				
			}
		}
		
		return $this;
	}
	
	
	//
	public function debug() {
	
		$aFormatted = $this->parseCsvData();
		
		foreach ( $aFormatted as $aEntry ) {
			
			printf( "%s | %s\n", $aEntry[ 'date_entered' ], $aEntry[ 'details' ] );
			
			foreach ( $aEntry[ 'items' ] as $aItem ) {
				
				printf(
					"\t%s | %d | %s | %d | %s | %d\n\t%s\n",
					$aItem[ 'details' ],
					$aItem[ 'account_id' ],
					$aItem[ 'external_reference' ],
					$aItem[ 'debit_credit' ],
					$aItem[ 'amount' ],
					$aItem[ 'sort_order' ],
					$aItem[ 'long_details' ]
				);
				
			}
			
			echo "\n";
		}
		
		return $this;
	}
	
	

}


