<?php

//
class Geko_Wp_EmailMessage_Queue extends Geko_Singleton_Abstract
{
	
	protected $_bCalledInstall = FALSE;
	
	protected $_oQueueTable = NULL;
	protected $_oQueueMetaTable = NULL;
	
	
	
	//
	public function start() {
		
		parent::start();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_emsg_queue', 'q' )
			->fieldBigInt( 'queue_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'email', array( 'size' => 256 ) )
			->fieldDateTime( 'delivery_date' )
			->fieldBigInt( 'emsg_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldBigInt( 'batch_id' )
		;
		
		$this->_oQueueTable = $oSqlTable;
		
		
		
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( '##pfx##geko_emsg_queue_meta', 'qm' )
			->fieldInt( 'queue_id', array( 'unsgnd' ) )
			->fieldVarChar( 'meta_key', array( 'size' => 256 ) )
			->fieldLongText( 'value' )
		;
		
		$this->_oQueueMetaTable = $oSqlTable2;
		
		
	}
	
	//
	public function install() {
		
		if ( !$this->_bCalledInstall ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$oDb->tableCreateIfNotExists( $this->_oQueueTable );
			$oDb->tableCreateIfNotExists( $this->_oQueueMetaTable );
			
			
			$this->_bCalledInstall = TRUE;
		}
		
	}
	
	//
	public function cycle( $iInterval = 30, $iLimit = 5 ) {
		
		do_action( 'geko_wp_emsg_queue_cycle_start' );
		
		$iCycles = 0;
		
		do {
			$iCycles++;
			$iNumRows = $this->process( $iInterval = 30, $iLimit = 5 );
		} while ( $iNumRows );
		
		do_action( 'geko_wp_emsg_queue_cycle_end', $iCycles );		
	}
	
	//
	public function process( $iInterval = 30, $iLimit = 5 ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'q.*' )
			->from( '##pfx##geko_emsg_queue', 'q' )
			->where( 'q.delivery_date <= ?', $oDb->getTimestamp() )
			->orWhere( 'q.delivery_date IS NULL' )
			->limit( $iLimit )
		;
		
		$aRes = $oDb->fetchAllObj( strval( $oQuery ) );
		
		if ( $iCount = count( $aRes ) ) {
			
			$aIds = array();
			foreach ( $aRes as $oQueueItem ) $aIds[] = $oQueueItem->queue_id;
			
			$oMetaQuery = new Geko_Sql_Select();
			$oMetaQuery
				->field( 'm.*' )
				->from( '##pfx##geko_emsg_queue_meta', 'm' )
				->where( 'm.queue_id * ($)', $aIds )
			;
			
			$aMeta = $oDb->fetchAllObj( strval( $oMetaQuery ) );
			$aMetaFmt = array();
			
			foreach ( $aMeta as $oMeta ) {
				$aMetaFmt[ $oMeta->queue_id ][ $oMeta->meta_key ] = $oMeta->value;
			}
			
			foreach ( $aRes as $oQueueItem ) {
				
				$iQueueId = $oQueueItem->queue_id;
				
				$oDelivery = new Geko_Wp_EmailMessage_Delivery(
					$oQueueItem->emsg_id, array(), $aMetaFmt[ $iQueueId ]
				);
				
				$oDelivery
					->setMode( 'queued' )
					->addRecipient( $oQueueItem->email, $aMetaFmt[ $iQueueId ][ '__recipient_name' ] )
					->send()
				;
				
				$oDb->delete( '##pfx##geko_emsg_queue', array(
					'queue_id = ?' => $iQueueId
				) );
				
				$oDb->delete( '##pfx##geko_emsg_queue_meta', array(
					'queue_id = ?' => $iQueueId
				) );
				
				sleep( $iInterval );
			}
			
		}
		
		do_action( 'geko_wp_emsg_queue_process', $iCount );
		
		return $iCount;
	}
	
	
	//
	public function add( $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( $aParams[ 'emsg_slug' ] ) {
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'e.emsg_id', 'emsg_id' )
				->from( '##pfx##geko_email_message', 'e' )
				->where( 'e.slug = ?', $aParams[ 'emsg_slug' ] )
			;
			
			$aParams[ 'emsg_id' ] = $oDb->fetchOne( strval( $oQuery ) );
		}
		
		if ( $aParams[ 'email' ] && $aParams[ 'emsg_id' ] ) {
			
			$oDb->insert( '##pfx##geko_emsg_queue', array(
				'email = ?' => $aParams[ 'email' ],
				'emsg_id = ?' => $aParams[ 'emsg_id' ]
			) );
			
			$iQueueId = $oDb->lastInsertId();
			
			if ( $aParams[ 'name' ] ) {
				
				$oDb->insert( '##pfx##geko_emsg_queue_meta', array(
					'queue_id' => $iQueueId,
					'meta_key' => '__recipient_name',
					'value' => $aParams[ 'name' ]
				) );
			}
			
			if ( is_array( $aParams[ 'meta' ] ) ) {
				
				foreach ( $aParams[ 'meta' ] as $sKey => $sValue ) {
					
					$oDb->insert( '##pfx##geko_emsg_queue_meta', array(
						'queue_id' => $iQueueId,
						'meta_key' => $sKey,
						'value' => $sValue
					) );
				}
			}
			
		}
		
	}
	
}


