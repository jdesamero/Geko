<?php

//
class Geko_Wp_EmailMessage_Queue
{
	private static $bCalledInit = FALSE;
	private static $bCalledInstall = FALSE;
	
	//
	public static function init() {
		
		if ( !self::$bCalledInit ) {
			
			// register db functions only once
			Geko_Wp_Db::addPrefix('geko_emsg_queue');
			Geko_Wp_Db::addPrefix('geko_emsg_queue_meta');
			
			self::$bCalledInit = TRUE;
			
		}
		
	}
	
	//
	public static function install() {
		
		if ( !self::$bCalledInstall ) {
			
			$sSql = '
				CREATE TABLE %s
				(
					queue_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					email VARCHAR(255),
					delivery_date DATETIME,
					emsg_id BIGINT UNSIGNED,
					batch_id BIGINT UNSIGNED,
					PRIMARY KEY(queue_id),
					KEY emsg_id(emsg_id)
				)
			';
			
			Geko_Wp_Db::createTable( 'geko_emsg_queue', $sSql );
			
			$sSql = '
				CREATE TABLE %s
				(
					queue_id BIGINT UNSIGNED,
					meta_key VARCHAR(255),
					value LONGTEXT
				)
			';
			
			Geko_Wp_Db::createTable( 'geko_emsg_queue_meta', $sSql );
			
			self::$bCalledInstall = TRUE;
			
		}	
	}
	
	//
	public static function cycle( $iInterval = 30, $iLimit = 5 ) {
		
		do_action( 'geko_wp_emsg_queue_cycle_start' );
		
		$iCycles = 0;
		
		do {
			$iCycles++;
			$iNumRows = self::process( $iInterval = 30, $iLimit = 5 );
		} while ( $iNumRows );
		
		do_action( 'geko_wp_emsg_queue_cycle_end', $iCycles );		
	}
	
	//
	public static function process( $iInterval = 30, $iLimit = 5 ) {
		
		global $wpdb;
		
		$sSql = $wpdb->prepare(
			"	SELECT			*
				FROM			$wpdb->geko_emsg_queue q
				WHERE			( q.delivery_date <= %s ) OR 
								( q.delivery_date IS NULL )
				ORDER BY		q.delivery_date ASC
				LIMIT			%d
			",
			Geko_Db_Mysql::getTimestamp(),
			$iLimit
		);
		
		$aRes = $wpdb->get_results( $sSql );
		
		if ( $iCount = count( $aRes ) ) {
		
			$aIds = array();
			foreach ( $aRes as $oQueueItem ) $aIds[] = $oQueueItem->queue_id;
			
			$sSql = "
				SELECT			*
				FROM			$wpdb->geko_emsg_queue_meta m
				WHERE			" . Geko_Wp_Db::prepare( ' ( m.queue_id ##d## ) ', $aIds ) . "
			";
			
			$aMeta = $wpdb->get_results( $sSql );
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
					->addRecipient( $oQueueItem->email, $aMetaFmt[ $iQueueId ]['__recipient_name'] )
					->send()
				;
				
				$wpdb->query( $wpdb->prepare(
					"DELETE FROM $wpdb->geko_emsg_queue WHERE queue_id = %d",
					$iQueueId
				) );

				$wpdb->query( $wpdb->prepare(
					"DELETE FROM $wpdb->geko_emsg_queue_meta WHERE queue_id = %d",
					$iQueueId
				) );
				
				sleep( $iInterval );
			}
			
		}
		
		do_action( 'geko_wp_emsg_queue_process', $iCount );
		
		return $iCount;
	}
	
	
	//
	public static function add( $aParams ) {
		
		global $wpdb;
		
		if ( $aParams['emsg_slug'] ) {
			$aParams['emsg_id'] = $wpdb->get_var( $wpdb->prepare(
				"SELECT emsg_id FROM $wpdb->geko_email_message WHERE slug = %s",
				$aParams['emsg_slug']
			) );
		}
		
		if ( $aParams['email'] && $aParams['emsg_id'] ) {
			
			$wpdb->insert(
				$wpdb->geko_emsg_queue,
				array(
					'email' => $aParams['email'],
					'emsg_id' => $aParams['emsg_id']
				)
			);

			$iQueueId = $wpdb->get_var('SELECT LAST_INSERT_ID()');
			
			if ( $aParams['name'] ) {
				$wpdb->insert(
					$wpdb->geko_emsg_queue_meta,
					array(
						'queue_id' => $iQueueId,
						'meta_key' => '__recipient_name',
						'value' => $aParams['name']
					)
				);
			}
			
			if ( is_array( $aParams['meta'] ) ) {
				foreach ( $aParams['meta'] as $sKey => $sValue ) {
					$wpdb->insert(
						$wpdb->geko_emsg_queue_meta,
						array(
							'queue_id' => $iQueueId,
							'meta_key' => $sKey,
							'value' => $sValue
						)
					);					
				}
			}
			
		}
		
	}
	
}


