<?php

//
class Geko_Wp_Booking_Report extends Geko_Wp_Initialize
{
	
	//
	protected $_sManagementCapability = 'event_reports';
	protected $_bHasManagementCapability = FALSE;
	protected $_sParentManagementClass = 'Geko_Wp_Booking_Manage';
	
	
	//
	public function add() {
		
		parent::add();
		
		if (
			is_user_logged_in() && 
			$current_user && 
			$current_user->has_cap( $this->_sManagementCapability ) 
		) {
			$this->_bHasManagementCapability = TRUE;
		}
		
		return $this;
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$oWpRole = get_role( 'administrator' );
		
		if( !$oWpRole->has_cap( $this->_sManagementCapability ) ) {
			$oWpRole->add_cap( $this->_sManagementCapability );
		}
		
		wp_enqueue_style( 'geko-jquery-ui-wp' );
		wp_enqueue_script( 'geko-jquery-ui-datepicker' );
		
		add_action( 'admin_menu', array( $this, 'attachPage' ) );
		
		add_action( 'wp_dashboard_setup', array( $this, 'doDashboardWidget' ) );
		
		return $this;
	}
	
	
	//
	public function attachPage() {
		add_submenu_page( $this->_sParentManagementClass, 'Event Reports', 'Reports', $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
	}
	
	//// page display
	
	//
	public function displayPage() {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aMinMaxDate = $oDb->fetchRowAssoc( "
			SELECT			MIN( date_item ) AS min_date,
							MAX( date_item ) AS max_date
			FROM			##pfx##geko_bkng_item
		" );
		
		$aJsonParams = array(
			'file' => array(
				'cal_icon' => sprintf( '%s/themes/base/images/calendar.gif', Geko_Uri::getUrl( 'geko_styles' ) )
			),
			'min_date' => date( 'F j, Y G:i:s', strtotime( $aMinMaxDate[ 'min_date' ] ) ),
			'max_date' => date( 'F j, Y G:i:s', strtotime( $aMinMaxDate[ 'max_date' ] ) )
		);
		
		?>
		<script type="text/javascript">

			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				$( '#min_date, #max_date' ).datepicker( {
					minDate: new Date( oParams.min_date ),
					maxDate: new Date( oParams.max_date ),
					showOn: 'button',
					buttonImage: oParams.file.cal_icon,
					buttonImageOnly: true
				} );
				
			} );
			
		</script>
		<div class="wrap">

			<div id="icon-tools" class="icon32"><br /></div>		
			<h2>Event Reports</h2>
			
			<h3>Generate Report</h3>
			
			<?php echo Geko_Html::populateForm( Geko_String::fromOb( array( $this, 'echoForm' ) ), $_GET ); ?>
			
			<?php
			
			if ( $sReportType = $_GET[ 'report_type' ] ):
				
				$this->echoStyle();
				
				if ( 'booking' == $sReportType ) {
					$this->showBookingsReport();
				} elseif ( 'booking_details' == $sReportType ) {
					$this->showBookingDetailsReport();
				} elseif ( 'signups' == $sReportType ) {
					$this->showSignupsReport();				
				}
				
			endif;
			?>
			
		</div>
		<?php
		
		return $this;
	}
	
	//
	public function echoStyle() {
		?>
		<style type="text/css">
			
			.res_title {
				font-size: 14px;
				font-weight: bold;
				text-transform: uppercase;
				padding-bottom: 6px;
			}
			
			.res_table, .sub_table {
				border-collapse: collapse;
			}
			
			.res_table th,
			.res_table td {
				border-top: solid 1px lightgray;
				border-left: solid 1px lightgray;
				padding: 3px 9px;
			}
			
			.res_table th:last-child,
			.res_table td:last-child {
				border-right: solid 1px lightgray;			
			}
			
			.res_table tr:last-child th,
			.res_table tr:last-child td {
				border-bottom: solid 1px lightgray;
			}
			
			.res_table .right {
				text-align: right;
			}

			.res_table .nopadding {
				padding: 0;
			}
			
			.res_table .bold {
				font-weight: bold;
			}
			
			.sub_table td,
			.sub_table td:last-child {
				border-left: none;
				border-right: none;
			}
			
			.sub_table tr:first-child td {
				border-top: none;
			}
			
			.sub_table tr:last-child td {
				border-bottom: none;
			}
			
		</style>		
		<?php
		
		return $this;
	}
	
	//
	public function echoForm() {

		$oUrl = new Geko_Uri();
		$oUrl->unsetVars();
		$sAction = strval( $oUrl );
		
		$sReportType = $_GET[ 'report_type' ];
		
		?>
		<form id="export_report_form" action="<?php echo $sAction; ?>" method="get">
			<table class="form-table">
				<tr>
					<th><label for="min_date">Start Date</label></th>
					<td><input type="text" id="min_date" name="min_date" /></td>
				</tr>
				<tr>
					<th><label for="max_date">End Date</label></th>
					<td><input type="text" id="max_date" name="max_date" /></td>
				</tr>
				<tr>
					<th><label for="report_type">Report Type</label></th>
					<td><select id="report_type" name="report_type">
						<option value="">- Select Report Type -</option>
						<option value="booking">Bookings</option>
						<option value="booking_details">Booking Details</option>
						<option value="signups">Signups</option>
					</select></td>
				</tr>
				<?php if ( ( 'booking' == $sReportType ) || ( 'booking_details' == $sReportType ) ):
					
					$aEvents = new Geko_Wp_Booking_Query();
					
					?><tr>
						<th><label for="event">Event</label></th>
						<td><select id="event" name="event">
							<option value="">- All -</option>
							<?php echo $aEvents->implode( '<option value="##Id##">##Name##</option>' ); ?>
						</select></td>
					</tr>
					<?php if ( $iBkngId = $_GET[ 'event' ] ):
						
						$aSchedules = new Geko_Wp_Booking_Schedule_Query( array(
							'parent_id' => $iBkngId
						), FALSE );
						
						?><tr>
							<th><label for="schedule">Scehdule</label></th>
							<td><select id="schedule" name="schedule">
								<option value="">- All -</option>
								<?php echo $aSchedules->implode( '<option value="##Id##">##Name##</option>' ); ?>
							</select></td>
						</tr>					
					<?php endif; ?>
				<?php endif; ?>
			</table>
			<input type="hidden" name="page" id="page" />
			<p class="submit"><input type="submit" class="button-primary" value="Generate Report"></p>
		</form>
		<?php
		
		return $this;
	}
	
	
	//
	public function showBookingsReport( $aParams = NULL ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( NULL === $aParams ) $aParams = $_GET;
		
		$oPurchasesQuery = new Geko_Sql_Select();
		
		$oPurchasesQuery
			->field( 'bp.bkitm_id' )
			->field( 'bp.amount' )
			->from( '##pfx##geko_bkng_transaction', 'bp' )
			->joinLeft( '##pfx##geko_bkng_transaction', 'bc' )
				->on( 'bc.orig_trn_id = bp.bktrn_id' )
				->on( 'bc.status_id = 1' )
				->on( 'bc.transaction_type_id = 2' )
			->joinLeft( '##pfx##geko_bkng_item', 'bi' )
				->on( 'bi.bkitm_id = bp.bkitm_id' )
			->where( 'bp.status_id = 1' )
			->where( 'bp.transaction_type_id = 1' )
			->where( 'bc.orig_trn_id IS NULL' )
		;

		$oQuery = new Geko_Sql_Select();
		
		$oQuery
			->field( 'b.name', 'product_name' )
			->field( 's.name', 'schedule_name' )
			->field( 'SUM( s.slots )', 'total_slots' )
			->field( 'COUNT( p.bkitm_id )', 'total_slots_taken' )
			->field( 'SUM( p.amount )', 'revenue' )
			->from( '##pfx##geko_bkng_item', 'i' )
			->joinLeft( '##pfx##geko_bkng_schedule', 's' )
				->on( 's.bksch_id = i.bksch_id' )
			->joinLeft( '##pfx##geko_booking', 'b' )
				->on( 'b.bkng_id = s.bkng_id' )
			->joinLeft( $oPurchasesQuery, 'p' )
				->on( 'p.bkitm_id = i.bkitm_id' )
			->group( 'i.bksch_id' )
		;
		
		//
		if ( $sDate = $aParams[ 'min_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
			$oPurchasesQuery->where( 'bi.date_item >= ?', $sDbTs );
			$oQuery->where( 'i.date_item >= ?', $sDbTs );
		}

		//
		if ( $sDate = $aParams[ 'max_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
			$oPurchasesQuery->where( 'bi.date_item <= ?', $sDbTs );
			$oQuery->where( 'i.date_item <= ?', $sDbTs );
		}
		
		//
		if ( $iBkngId = $aParams[ 'event' ] ) {
			$oQuery->where( 'b.bkng_id = ?', $iBkngId );
		}
		
		//
		if ( $iBkSchId = $aParams[ 'schedule' ] ) {
			$oPurchasesQuery->where( 'bi.bksch_id = ?', $iBkSchId );
			$oQuery->where( 'i.bksch_id = ?', $iBkSchId );
		}
		
		$aFormat = array(
			'product_name' => array(
				'title' => 'Product'
			),
			'schedule_name' => array(
				'title' => 'Schedule'
			),
			'total_slots' => array(
				'title' => 'Total Slots',
				'td_class' => 'right',
				'sum' => TRUE
			),
			'total_slots_taken' => array(
				'title' => 'Total Slots Taken',
				'td_class' => 'right',
				'sum' => TRUE
			),
			'revenue' => array(
				'title' => 'Revenue',
				'td_class' => 'right',
				'sum' => TRUE
			)
		);
		
		$aTableParams = array();
		$aTableParams[ 'title' ] = 'Bookings';
		$aTableParams[ 'total_row' ] = '<tr>
			<td colspan="2" class="bold right">Total</td>
			<td class="bold right">##total_slots##</td>
			<td class="bold right">##total_slots_taken##</td>
			<td class="bold right">##revenue##</td>
		</tr>';
		
		$aTableParams = array_merge( $aTableParams, $aParams[ 'table_params' ] );
		
		$this->showTable( $oQuery, $aFormat, $aTableParams );
		
		return $this;
		
	}
	
	
	//
	public function showBookingDetailsReport( $aParams = NULL ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( NULL === $aParams ) $aParams = $_GET;
		
		$oPurchasesQuery = new Geko_Sql_Select();
		
		$oPurchasesQuery
			->field( 'bp.bkitm_id' )
			->field( 'bp.amount' )
			->field( 'COUNT(*)', 'slots_taken' )
			->from( '##pfx##geko_bkng_transaction', 'bp' )
			->joinLeft( '##pfx##geko_bkng_transaction', 'bc' )
				->on( 'bc.orig_trn_id = bp.bktrn_id' )
				->on( 'bc.status_id = 1' )
				->on( 'bc.transaction_type_id = 2' )
			->joinLeft( '##pfx##geko_bkng_item', 'bi' )
				->on( 'bi.bkitm_id = bp.bkitm_id' )
			->where( 'bp.status_id = 1' )
			->where( 'bp.transaction_type_id = 1' )
			->where( 'bc.orig_trn_id IS NULL' )
			->group( 'bp.bkitm_id' )
		;
		
		$oQuery = new Geko_Sql_Select();
		
		$oQuery
			->field( 'i.bkitm_id' )
			->field( "CONCAT( b.name, ' - ', s.name )", 'event' )
			->field( "CONCAT( DATE_FORMAT( i.date_item, '%a - %b %e, %Y' ), ' ', i.time_start, ' - ', i.time_end )", 'event_date' )
			->field(
				"STR_TO_DATE( CONCAT( DATE_FORMAT( i.date_item, '%Y-%m-%d' ), ' ', i.time_start ), '%Y-%m-%d %l:%i %p' )",
				'datetime_start'
			)
			->field( 's.slots' )
			->field( 'p.slots_taken' )
			->field( '1', 'occupied_by' )
			->from( '##pfx##geko_bkng_item', 'i' )
			->joinLeft( '##pfx##geko_bkng_schedule', 's' )
				->on( 's.bksch_id = i.bksch_id' )
			->joinLeft( $oPurchasesQuery, 'p' )
				->on( 'p.bkitm_id = i.bkitm_id' )
			->joinLeft( '##pfx##geko_booking', 'b' )
				->on( 'b.bkng_id = s.bkng_id' )
			->order( 'datetime_start', 'ASC' )
		;		
		
		$oUserQuery = new Geko_Sql_Select();
		
		$oUserQuery
			->field( 'bp.bkitm_id' )
			->field( 'bp.user_id' )
			->field( 'fn.meta_value', 'first_name' )
			->field( 'ln.meta_value', 'last_name' )
			->field( "DATE_FORMAT( bp.date_created, '%a - %b %e, %Y %l:%i %p' )", 'date_created' )
			->from( '##pfx##geko_bkng_transaction', 'bp' )
			->joinLeft( '##pfx##geko_bkng_transaction', 'bc' )
				->on( 'bc.orig_trn_id = bp.bktrn_id' )
				->on( 'bc.status_id = 1' )
				->on( 'bc.transaction_type_id = 2' )
			->joinLeft( '##pfx##geko_bkng_item', 'bi' )
				->on( 'bi.bkitm_id = bp.bkitm_id' )
			->joinLeft( '##pfx##usermeta', 'fn' )
				->on( 'fn.user_id = bp.user_id' )
				->on( 'fn.meta_key = ?', 'first_name' )
			->joinLeft( '##pfx##usermeta', 'ln' )
				->on( 'ln.user_id = bp.user_id' )
				->on( 'ln.meta_key = ?', 'last_name' )
			->where( 'bp.status_id = 1' )
			->where( 'bp.transaction_type_id = 1' )
			->where( 'bc.orig_trn_id IS NULL' )
		;

		//
		if ( $sDate = $aParams[ 'min_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
			$oPurchasesQuery->where( 'bi.date_item >= ?', $sDbTs );
			$oQuery->where( 'i.date_item >= ?', $sDbTs );
			$oUserQuery->where( 'bi.date_item >= ?', $sDbTs );
		}

		//
		if ( $sDate = $aParams[ 'max_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
			$oPurchasesQuery->where( 'bi.date_item <= ?', $sDbTs );
			$oQuery->where( 'i.date_item <= ?', $sDbTs );
			$oUserQuery->where( 'bi.date_item <= ?', $sDbTs );
		}
		
		//
		if ( $iBkngId = $aParams[ 'event' ] ) {
			$oQuery->where( 'b.bkng_id = ?', $iBkngId );
		}
		
		//
		if ( $iBkSchId = $aParams[ 'schedule' ] ) {
			$oPurchasesQuery->where( 'bi.bksch_id = ?', $iBkSchId );
			$oQuery->where( 'i.bksch_id = ?', $iBkSchId );
			$oUserQuery->where( 'bi.bksch_id = ?', $iBkSchId );
		}
		
		// $this->showTable( $oUserQuery, array(), 'Users' );
		
		$aResFmt = array();
		$aRes = $oDb->fetchAllAssoc( strval( $oUserQuery ) );
		
		// group together
		foreach ( $aRes as $aItem ) {
			$aResFmt[ $aItem[ 'bkitm_id' ] ][] = $aItem;
		}
		
		// format into table
		foreach ( $aResFmt as $i => $aItems ) {
			$sOut = '<table class="sub_table">';
			foreach ( $aItems as $aItem ) {
				$sOut .= sprintf(
					'<tr><td><a href="%s?page=Geko_Wp_Booking_Transaction_User&parent_entity_id=%d">%s %s</a></td><td>%s</td></tr>',
					Geko_Uri::getUrl( 'wp_admin' ),
					$aItem[ 'user_id' ],					
					$aItem[ 'first_name' ],
					$aItem[ 'last_name' ],
					$aItem[ 'date_created' ]
				);
			}
			$sOut .= '</table>';
			$aResFmt[ $i ] = $sOut;
		}
		
		$aFormat = array(
			'bkitm_id' => array(
				'hide' => TRUE
			),
			'event' => array(
				'title' => 'event'
			),
			'event_date' => array(
				'title' => 'Event Date/Time'
			),
			'slots' => array(
				'title' => 'Slots',
				'td_class' => 'right'
			),
			'slots_taken' => array(
				'title' => 'Slots Taken',
				'td_class' => 'right'
			),
			'datetime_start' => array(
				'hide' => TRUE
			),
			'occupied_by' => array(
				'title' => 'Occupied By',
				'data' => $aResFmt,
				'data_key' => 'bkitm_id',
				'td_class' => 'nopadding'
			)
		);
		
		$aTableParams = array();
		$aTableParams[ 'title' ] = 'Booking Details';
		
		$aTableParams = array_merge( $aTableParams, $aParams[ 'table_params' ] );
		
		$this->showTable( $oQuery, $aFormat, $aTableParams );
		
		return $this;
		
	}
	
	//
	public function showSignupsReport( $aParams = NULL ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( NULL === $aParams ) $aParams = $_GET;
		
		$oQuery = new Geko_Sql_Select();
		
		$oQuery
			->field( 'fn.meta_value', 'first_name' )
			->field( 'ln.meta_value', 'last_name' )
			->field( "DATE_FORMAT( u.user_registered, '%a - %b %e, %Y %l:%i %p' )", 'date_registered' )
			->from( '##pfx##users', 'u' )
			->joinLeft( '##pfx##usermeta', 'fn' )
				->on( 'fn.user_id = u.ID' )
				->on( 'fn.meta_key = ?', 'first_name' )
			->joinLeft( '##pfx##usermeta', 'ln' )
				->on( 'ln.user_id = u.ID' )
				->on( 'ln.meta_key = ?', 'last_name' )
			->order( 'u.user_registered', 'ASC' )
		;
		
		//
		if ( $sDate = $aParams[ 'min_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
			$oQuery->where( 'u.user_registered >= ?', $sDbTs );
		}
		
		//
		if ( $sDate = $aParams[ 'max_date' ] ) {
			// convert date to MySQL timestamp
			$sDbTs = $oDb->getTimestamp( strtotime( $sDate ) );
			$oQuery->where( 'u.user_registered <= ?', $sDbTs );
		}
		
		$aFormat = array(
			'first_name' => array(
				'title' => 'First Name'
			),
			'last_name' => array(
				'title' => 'Last Name'
			),
			'date_registered' => array(
				'title' => 'Date Registered'
			)
		);
		
		$aTableParams = array();
		$aTableParams[ 'title' ] = 'Signups';
		$aTableParams[ 'total_row' ] = '<tr>
			<td colspan="2" class="bold right">Total</td>
			<td class="bold right">##__count##</td>
		</tr>';
		
		$aTableParams = array_merge( $aTableParams, $aParams[ 'table_params' ] );
		
		$this->showTable( $oQuery, $aFormat, $aTableParams );
		
		return $this;
		
	}
	
	//
	public function showTable( $oQuery, $aFormat = array(), $aParams = array() ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sTitle = $aParams[ 'title' ];
		$sTotalRow = $aParams[ 'total_row' ];
		$sTitleTag = $aParams[ 'title_tag' ];
		if ( !$sTitleTag ) $sTitleTag = '<h3>%s</h3>';
		
		$aRes = $oDb->fetchAllAssoc( strval( $oQuery ) );
		
		$aFields = array_keys( $aRes[ 0 ] );
		
		if ( $sTitle ):
			echo sprintf( $sTitleTag, $sTitle );
		endif;
		
		if ( count( $aRes ) > 0 ):
			?>
			<table class="res_table">
				<tr>
					<?php foreach ( $aFields as $sField ):
						$sTitle = $aFormat[ $sField ][ 'title' ];
						$sThClass = $aFormat[ $sField ][ 'th_class' ];
						if ( !$aFormat[ $sField ][ 'hide' ] ):
							?><th class="<?php echo $sThClass; ?>"><?php echo ( $sTitle ) ? $sTitle : $sField; ?></th><?php
						endif;
					endforeach; ?>
				</tr>
				<?php foreach ( $aRes as $aRow ): ?>
					<tr>
						<?php foreach ( $aRow as $sKey => $sValue ):
							$sTdClass = $aFormat[ $sKey ][ 'td_class' ];
							if ( !$aFormat[ $sKey ][ 'hide' ] ):
								?><td class="<?php echo $sTdClass; ?>"><?php
									
									if ( is_array( $aData = $aFormat[ $sKey ][ 'data' ] ) ) {
										$iKey = $aRow[ $aFormat[ $sKey ][ 'data_key' ] ];
										echo $aData[ $iKey ];
									} else {
										echo $sValue;
									}
									
									if ( $aFormat[ $sKey ][ 'sum' ] ) {
										$aFormat[ $sKey ][ 'sum_res' ] += floatval( $sValue );
									}
									
								?></td><?php
							endif;
						endforeach; ?>
					</tr>			
				<?php endforeach; ?>
				<?php
				
				if ( $sTotalRow ) {
					foreach ( $aFormat as $sKey => $aField ) {
						if ( $aField[ 'sum' ] ) {
							$sTotalRow = str_replace( sprintf( '##%s##', $sKey ), $aField[ 'sum_res' ], $sTotalRow );
						}
					}
					$sTotalRow = str_replace( '##__count##', count( $aRes ), $sTotalRow );					
					echo $sTotalRow;
				}
				
				?>
			</table>
			<?php
			
		else:
			?><p>There were no matching statistics for the selected time period. Please try again.</p><?php
		endif;
		
		return $this;
	}
	
	
	//
	public function doDashboardWidget() {
		wp_add_dashboard_widget(
			'statistics_dashboard_widget',
			'Statistics',
			array( $this, 'doDashboardWidgetContent' )
		);
	}
	
	//
	public function doDashboardWidgetContent() {
		
		$this->echoStyle();
		
		/* /
		$aSignupParams = array(
			'table_params' => array(
				'title_tag' => '<div><strong>%s</strong></div>'
			)
		);
		
		$oReport = Geko_Wp_Booking_Report::getInstance()
			->init()
			->echoStyle()
			// ->showSignupsReport( $aSignupParams )
		;
		/* */
		
		// -------------------------------------------------------------------------------------- //
		
		// Signups
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'COUNT(*)', 'num' )
			->from( '##pfx##users', 'u' )
			->joinLeft( '##pfx##usermeta', 'um' )
				->on( 'um.user_id = u.ID' )
				->on( 'um.meta_key = ?', '_geko_role_id' )
			->joinLeft( '##pfx##geko_roles', 'r' )
				->on( 'r.role_id = um.meta_value' )
			->where( 'r.slug = ?', 'customer' )
		;
		
		$iTotalSignups = $oDb->fetchOne( strval( $oQuery ) );
		
		$oQuery->where( "DATE_FORMAT( u.user_registered, '%c' ) = ?", date( 'n' ) );
		
		$iSignupsForMonth = $oDb->fetchOne( strval( $oQuery ) );
		
		// -------------------------------------------------------------------------------------- //
		
		$oTrMg = Geko_Wp_Booking_Transaction_Manage::getInstance();
		$oStQuery = $oTrMg->getSlotsTakenQuery();
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'COUNT(*)', 'total' )
			->field( 'SUM( IF( ( bs.slots_taken IS NULL ) || ( bs.slots_taken = 0 ), 1, 0 ) )', 'is_empty' )
			->field( 'SUM( IF( bs.slots_taken = bsc.slots, 1, 0 ) )', 'is_full' )
			->field( 'SUM( IF( ( bs.slots_taken IS NOT NULL ) && ( bs.slots_taken != 0 ) && ( bs.slots_taken < bsc.slots ), 1, 0 ) )', 'is_partial' )
			->from( '##pfx##geko_bkng_item', 'bi' )
			->joinLeft( $oStQuery, 'bs' )
				->on( 'bs.bkitm_id = bi.bkitm_id' )
			->joinLeft( '##pfx##geko_bkng_schedule', 'bsc' )
				->on( 'bsc.bksch_id = bi.bksch_id' )
			->where( "DATE_FORMAT( bi.date_item, '%V' ) = ?", date( 'W' ) )
		;
		
		$oWeeklySignups = $oDb->fetchRowObj( strval( $oQuery ) );
		
		$oQuery
			->unsetWhere()
			->where( "DATE_FORMAT( bi.date_item, '%c' ) = ?", date( 'n' ) )
		;
		
		$oMonthlySignups = $oDb->fetchRowObj( strval( $oQuery ) );
		
		$oQuery
			->unsetWhere()
			->field( 'bk.name' )
			->joinLeft( '##pfx##geko_booking', 'bk' )
				->on( 'bk.bkng_id = bsc.bkng_id' )
			->group( 'bk.name' )
		;
		
		$aProds = $oDb->fetchAllObj( strval( $oQuery ) );
		
		// -------------------------------------------------------------------------------------- //
		
		$sCurWeek = date( 'D, M j, Y', strtotime( 'last sunday' ) );
		$sCurMonth = date( 'F' );
		
		?>
		
		<div class="res_title">Signups</div>
		<table class="res_table">
			<tr>
				<th>Signups this month (<?php echo $sCurMonth; ?>)</th>
				<td><?php echo $iSignupsForMonth; ?></td>
			</tr>
			<tr>
				<th>Total signups</th>
				<td><?php echo $iTotalSignups; ?></td>
			</tr>
		</table>
		
		<br /><br />
		
		<div class="res_title">Bookings for current week/month</div>
		<table class="res_table">
			<tr>
				<td>&nbsp;</td>
				<th>Total</th>
				<th>Full</th>
				<th>Empty</th>
				<th>Partial</th>
			</tr>
			<tr>
				<th>Events for this week (<?php echo $sCurWeek; ?>)</th>
				<td><?php echo $oWeeklySignups->total; ?></td>
				<td><?php echo $oWeeklySignups->is_full; ?></td>
				<td><?php echo $oWeeklySignups->is_empty; ?></td>
				<td><?php echo $oWeeklySignups->is_partial; ?></td>
			</tr>
			<tr>
				<th>Events for this month (<?php echo $sCurMonth; ?>)</th>
				<td><?php echo $oMonthlySignups->total; ?></td>
				<td><?php echo $oMonthlySignups->is_full; ?></td>
				<td><?php echo $oMonthlySignups->is_empty; ?></td>
				<td><?php echo $oMonthlySignups->is_partial; ?></td>
			</tr>
		</table>		
		
		<br /><br />
		<div class="res_title">Bookings by event type</div>
		<table class="res_table">
			<tr>
				<td>&nbsp;</td>
				<th>Total</th>
				<th>Full</th>
				<th>Empty</th>
				<th>Partial</th>
			</tr>
			<?php foreach ( $aProds as $oProd ): ?>
				<tr>
					<th><?php echo $oProd->name; ?></th>
					<td><?php echo $oProd->total; ?></td>
					<td><?php echo $oProd->is_full; ?></td>
					<td><?php echo $oProd->is_empty; ?></td>
					<td><?php echo $oProd->is_partial; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>	
			
		<?php
		
	}
	
	
}


