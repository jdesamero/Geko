<?php

//
class Geko_Wp_Booking_Schedule_Time_Manage extends Geko_Wp_Options_Manage
{
	
	const WEEKDAY_SUNDAY = 0;
	const WEEKDAY_MONDAY = 1;
	const WEEKDAY_TUESDAY = 2;
	const WEEKDAY_WEDNESDAY = 3;
	const WEEKDAY_THURSDAY = 4;
	const WEEKDAY_FRIDAY = 5;
	const WEEKDAY_SATURDAY = 6;
	
	
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'bksctm_id';
	
	protected $_sSubject = 'Booking Schedule Time';
	protected $_sDescription = 'Weekly schedule times.';
	protected $_sType = 'bksctm';
	
	protected $_aJsParams = array(
		'row_template' => array(
			'times' => array()
		)
	);
	
	protected $_bHasDisplayMode = FALSE;
	
	
	protected $_aTimes = array(
		'2' => '1:00 AM', '2.5' => '1:30 AM',
		'3' => '2:00 AM', '3.5' => '2:30 AM',
		'4' => '3:00 AM', '4.5' => '3:30 AM',
		'5' => '4:00 AM', '5.5' => '4:30 AM',
		'6' => '5:00 AM', '6.5' => '5:30 AM',
		'7' => '6:00 AM', '7.5' => '6:30 AM',
		'8' => '7:00 AM', '8.5' => '7:30 AM',
		'9' => '8:00 AM', '9.5' => '8:30 AM',
		'10' => '9:00 AM', '10.5' => '9:30 AM',
		'11' => '10:00 AM', '11.5' => '10:30 AM',
		'12' => '11:00 AM', '12.5' => '11:30 AM',
		'13' => '12:00 PM', '13.5' => '12:30 PM',
		'14' => '1:00 PM', '14.5' => '1:30 PM',
		'15' => '2:00 PM', '15.5' => '2:30 PM',
		'16' => '3:00 PM', '16.5' => '3:30 PM',
		'17' => '4:00 PM', '17.5' => '4:30 PM',
		'18' => '5:00 PM', '18.5' => '5:30 PM',
		'19' => '6:00 PM', '19.5' => '6:30 PM',
		'20' => '7:00 PM', '20.5' => '7:30 PM',
		'21' => '8:00 PM', '21.5' => '8:30 PM',
		'22' => '9:00 PM', '22.5' => '9:30 PM',
		'23' => '10:00 PM', '23.5' => '10:30 PM',
		'24' => '11:00 PM', '24.5' => '11:30 PM',
		'25' => '12:00 AM', '25.5' => '12:30 AM'
	);
	
	protected $_aTimeHash = NULL;
	
	protected $_aWeekdays = array(
		self::WEEKDAY_SUNDAY => 'Sunday',
		self::WEEKDAY_MONDAY => 'Monday',
		self::WEEKDAY_TUESDAY => 'Tuesday',
		self::WEEKDAY_WEDNESDAY => 'Wednesday',
		self::WEEKDAY_THURSDAY => 'Thursday',
		self::WEEKDAY_FRIDAY => 'Friday',
		self::WEEKDAY_SATURDAY => 'Saturday'
	);
	
	
	//// init
	
	
	//
	public function affix() {
		Geko_Wp_Db::addPrefix( 'geko_bkng_schedule_time' );
		return $this;
	}
	
	
	//
	public function add() {
		
		parent::add();
		
		add_action( 'admin_geko_bksch_main_fields', array( $this, 'formFields' ), 10, 2 );
		
		add_action( 'admin_geko_bkschs_add', array( $this, 'doAddAction' ) );
		add_action( 'admin_geko_bkschs_edit', array( $this, 'doEditAction' ), 10, 2 );
		add_action( 'admin_geko_bkschs_delete', array( $this, 'doDelAction' ) );
		add_filter( 'admin_geko_bkschs_getstoredopts', array( $this, 'getStoredOptions' ), 10, 2 );
		
		return $this;
	}
	
	
	// create table
	public function install() {
		
		$sSql = '
			CREATE TABLE %s
			(
				bksctm_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				bksch_id BIGINT UNSIGNED,
				weekday_id TINYINT,
				time_start VARCHAR(16),
				time_end VARCHAR(16),
				PRIMARY KEY(bksctm_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_bkng_schedule_time', $sSql );
				
		return $this;
	}
	
	
	
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {			
			wp_enqueue_script( 'geko_phpquery_formtransform_plugin_rowtemplate' );
			wp_enqueue_script( 'geko_wp_booking_schedule_time_manage' );
		}
		
		return $this;
	}
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {
			
			$oSchedule = Geko_Wp_Booking_Schedule_Manage::getInstance()->getCurrentEntity();
			$fUnits = 0;
			if ( $oSchedule ) {
				$fUnits = $oSchedule->getUnit();
			}
			
			$aJsonParams = array(
				'unit' => floatval( $fUnits ),
				'time_hash' => $this->getTimeHash(),
				'weekday' => $this->_aWeekdays
			);
			
			?>
			<style type="text/css">
				
				.error_field {
					border: solid 1px red;
				}
				
			</style>
			<script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					
					if ( 0 == oParams.unit ) {
						$( '.multi_row > p > input.add_row' ).click( function( evt ) {
							alert( 'Please save the schedule first and specify a number of units before assigning time periods.' );
							evt.stopImmediatePropagation();
							return false;
						} );
					}
					
					$.gekoWpBookingScheduleTimeManage( {
						form_sel: '#editform',
						unit: oParams.unit,
						time_hash: oParams.time_hash,
						weekday: oParams.weekday,
						get_row_tmpl_func: function( tmpl ) {
							return $.gekoWpBookingScheduleRowTmpl;
						}
					} );
					
				} );
				
			</script><?php
		}
		
		return $this;
	}
	
	
	//
	public function outputAdminHeadMainJs() {
		?>
		
		oParams.js_params.row_template[ 'times' ].submit_func = function( tmpl ) {
			$.gekoWpBookingScheduleRowTmpl = tmpl;
		}
		
		<?php
	}

	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	
	//
	public function getStoredOptions( $aRet, $oBksch ) {
		
		$aTimesFmt = array();
		
		$aTimes = new Geko_Wp_Booking_Schedule_Time_Query( array( 'bksch_id' => $oBksch->getId() ) );
		
		foreach ( $aTimes as $oTime ) {
			$aTimesFmt[ $oTime->getId() ] = array(
				'weekday_id' => $oTime->getWeekdayId(),
				'start' => $oTime->getTimeStart(),
				'end' => $oTime->getTimeEnd()
			);
		}
		
		$aRet[ 'times' ] = $aTimesFmt;
		
		return $aRet;
	}
	
	//
	public function getTimes() {
		return $this->_aTimes;
	}
	
	//
	public function getTimeHash() {
		
		if ( !$this->_aTimeHash ) {
			$aTimeHash = array();
			foreach ( $this->_aTimes as $sKey => $sTime ) {
				$aTimeHash[ $sTime ] = $sKey;
			}
			$this->_aTimeHash = $aTimeHash;
		}
		
		return $this->_aTimeHash;
	}
	
	
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		if ( 'pre' == $sSection ):
			
			$aTimes = $this->getTimes();
			
			?><tr>
				<th><label>Times</label></th>
				<td class="multi_row times">
					<table>
						<thead>
							<tr>
								<th></th>
								<th>Day of Week</th>
								<th>Start Time</th>
								<th>End Time</th>
							</tr>
						</thead>
						<tbody>
							<tr class="row" _row_template="times">
								<td><a href="#" class="del_row">Del</a></td>
								<td><select id="times[][weekday_id]" name="times[][weekday_id]" class="weekday">
									<?php foreach ( $this->_aWeekdays as $i => $sDay ): ?>
										<option value="<?php echo $i; ?>"><?php echo $sDay; ?></option>
									<?php endforeach; ?>
								</select></td>
								<td><select id="times[][start]" name="times[][start]" class="start">
									<?php foreach ( $aTimes as $sTime ): ?>
										<option value="<?php echo $sTime; ?>"><?php echo $sTime; ?></option>
									<?php endforeach; ?>
								</select></td>				
								<td><select id="times[][end]" name="times[][end]" class="end">
									<?php foreach ( $aTimes as $sTime ): ?>
										<option value="<?php echo $sTime; ?>"><?php echo $sTime; ?></option>
									<?php endforeach; ?>
								</select></td>						
							</tr>
						</tbody>
					</table>
					<p class="submit"><input type="button" value="Add" class="add_row" class="button-primary" /></p>					
				</td>
			</tr><?php
		
		endif;
		
	}
	
	
	
	
	
	//// crud methods
	
	//
	public function doAddAction( $oBksch ) {
		$this->updateTimes( $oBksch->getId() );
	}

	//
	public function doEditAction( $oBksch, $oUpdatedBksch ) {
		$this->updateTimes( $oBksch->getId() );		
	}
	
	//
	public function doDelAction( $oBksch ) {
		
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"	DELETE FROM				$wpdb->geko_bkng_schedule_time
				WHERE					bksch_id = %d
			",
			$oBksch->getId()
		) );		
	}
	
	//
	protected function updateTimes( $iBkschId ) {
		
		global $wpdb;
		
		$aTimes = new Geko_Wp_Booking_Schedule_Time_Query( array( 'bksch_id' => $iBkschId ) );
		
		foreach ( $aTimes as $oTime ) {
			$aTimesFmt[ $oTime->getId() ] = $oTime;
		}
		
		if ( is_array( $aPostTimes = $_POST[ 'times' ] ) ) {
			
			// update
			foreach ( $aTimesFmt as $iId => $oTime ) {
				if ( $aPostTime = $aPostTimes[ $iId ] ) {
					$wpdb->update(
						$wpdb->geko_bkng_schedule_time,
						array(
							'weekday_id' => $aPostTime[ 'weekday_id' ],
							'time_start' => $aPostTime[ 'start' ],
							'time_end' => $aPostTime[ 'end' ]
						),
						array( 'bksctm_id' => $iId ),
						array( '%d', '%s', '%s' ),
						array( '%d' )
					);
					unset( $aTimesFmt[ $iId ] );
					unset( $aPostTimes[ $iId ] );
				}
			}
			
			// insert
			foreach ( $aPostTimes as $aPostTime ) {
				$wpdb->insert(
					$wpdb->geko_bkng_schedule_time,
					array(
						'weekday_id' => $aPostTime[ 'weekday_id' ],
						'time_start' => $aPostTime[ 'start' ],
						'time_end' => $aPostTime[ 'end' ],
						'bksch_id' => $iBkschId
					),
					array( '%d', '%s', '%s', '%d' )
				);
			}
			
		}
		
		// delete
		$aDelIds = array();
		foreach ( $aTimesFmt as $iId => $oTime ) $aDelIds[] = $iId;
		
		if ( count( $aDelIds ) > 0 ) {
			$wpdb->query( $wpdb->prepare(
				"	DELETE FROM				$wpdb->geko_bkng_schedule_time
					WHERE					bksctm_id IN (" . implode( ',', $aDelIds ) . ") AND 
											bksch_id = %d
				",
				$iBkschId
			) );
		}
		
	}
	
	
	
}



