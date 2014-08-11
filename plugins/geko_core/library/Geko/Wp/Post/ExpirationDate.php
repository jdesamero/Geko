<?php

//
class Geko_Wp_Post_ExpirationDate extends Geko_Wp_Options
{	
	protected $_bHasDisplayMode = TRUE;
	
	
	//// init
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		// deprecated:
		// Geko_Wp_Post_ExpirationDate_QueryHooks::register();
		
		// add functionality to entity and queries
		
		$aPrefixes = array( 'Gloc_', 'Geko_Wp_' );
		
		$sPostEntityClass = Geko_Class::getBestMatch( $aPrefixes, array( 'Post' ) );		
		add_action( sprintf( '%s::init', $sPostEntityClass ), array( $this, 'initEntity' ) );
		
		$sPostQueryClass = Geko_Class::getBestMatch( $aPrefixes, array( 'Post_Query' ) );		
		add_action( sprintf( '%s::init', $sPostQueryClass ), array( $this, 'initQuery' ) );
		
		
		//// install table
		
		$sTableName = 'geko_expiry';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'e' )
			->fieldBigInt( 'post_id', array( 'unsgnd', 'key' ) )
			->fieldDateTime( 'start_date' )
			->fieldDateTime( 'expiry_date' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	//
	public function initEntity( $oPost ) {
		$oPost->addDelegate( 'Geko_Wp_Post_ExpirationDate_Delegate' );
	}
	
	//
	public function initQuery( $oQuery ) {
		$oQuery->addPlugin( 'Geko_Wp_Post_ExpirationDate_QueryPlugin' );
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_menu', array( $this, 'attachPage' ) );
		
		add_action( 'admin_init_post', array( $this, 'install' ) );		
		add_action( 'admin_head_post', array( $this, 'addAdminHead' ) );
		
		add_action( 'save_post', array( $this, 'savePostdata' ) );	
		add_action( 'delete_post', array( $this, 'deletePostdata' ) );
		
		return $this;
	}
	
	
	
	//
	public function addAdminHead() {
		
		if ( $this->isDisplayMode( 'add|edit' ) ):
			
			?><style type="text/css">
				.gexp th.label {
					padding-right: 20px;
					text-align: left;
				}
			</style>
			
			<script type="text/javascript">
				
				jQuery(document).ready(function($) {
					
					$('#gexp-start-check, #gexp-expiry-check').click( function() {
						
						var pfx = $(this).attr( 'id' ).replace( '-check', '' );
						var sel =
							'#' + pfx + '-mon, ' + '#' + pfx + '-dy, ' + 
							'#' + pfx + '-yr, ' + '#' + pfx + '-hr, ' + 
							'#' + pfx + '-min, ' + '#' + pfx + '-ampm'
						;
						
						if ( $(this).is( ':checked' ) ) {
							$( sel ).removeAttr( 'disabled' );
						} else {
							$( sel ).attr( 'disabled', 'disabled' );
						}
						
					} );
					
				});
				
			</script><?php
			
		endif;
		
		return $this;
	}
	
	// Adds a custom section to the "advanced" Post and Page edit screens
	public function attachPage() {
		
		if ( function_exists( 'add_meta_box' ) ) {			
			$this->addMetaBox( 'post', 'advanced' );
			//$this->add_meta_box('page', 'advanced');
		} else {
			add_action( 'dbx_post_advanced', array( $this, 'oldCustomBox' ) );
			//add_action( 'dbx_page_advanced', array( $this, 'old_custom_box' ) );
		}
	}
	
	//
	private function addMetaBox( $page = 'post', $context = 'advanced' ) {
		
		add_meta_box(
			'geko-expiry',
			__( 'Expiration Date', 'geko-expiry_textdomain' ),
			array( $this, 'innerCustomBox' ),
			$page,
			$context
		);		
	}
	
	
	
	//// front-end display methods
	
	// Prints the edit form for pre-WordPress 2.5 post/page
	public function oldCustomBox() {
		?>
		<div class="dbx-b-ox-wrapper">
			<fieldset id="geko-expiry_fieldsetid" class="dbx-box">
				<div class="dbx-h-andle-wrapper">
					<h3 class="dbx-handle"><?php echo __( 'Expiration Date', 'geko-expiry_textdomain' ); ?></h3>
				</div>
				<div class="dbx-c-ontent-wrapper"><div class="dbx-content">
					<?php $this->innerCustomBox(); ?>
				</div></div>
			</fieldset>
		</div>
		<?php
	}
	
	
	//
	private function dateFields( $prefix, $label, $iTs = NULL ) {
		
		////// arrays
		
		$aMonths = array(
			1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December'
		);
		
		$aDays = array();
		for ( $i = 1; $i <= 31; $i++ ) {
			$aDays[] = $i;
		}
		
		$aYears = array();
		for ( $i = 1990; $i <= 2020; $i++ ) {
			$aYears[] = $i;
		}
		
		$aHours = array( 12 => 12 );		// start at 12
		for ( $i = 1; $i <= 11; $i++ ) {
			$aHours[] = sprintf( "%'02d", $i );
		}
		
		$aMins = array();
		for ( $i = 0; $i <= 55; $i += 5 ) {
			$aMins[] = sprintf( "%'02d", $i );
		}
		
		$aAmPm = array( 'AM', 'PM' );
		
		////// current value
		
		if ( NULL == $iTs ) {
			
			$iTs = time();
			$sDisable = ' disabled="disabled" ';
			$sCbxCheck = '';
		
		} else {
			
			// $iTs has a value
			$sDisable = '';
			$sCbxCheck = ' checked="checked" ';
		}
		
		$iCurMonth = date( 'n', $iTs );
		$iCurDay = date( 'j', $iTs );
		$iCurYear = date( 'Y', $iTs );
		$iCurHour = date( 'h', $iTs );
		$iCurMin = round( intval( date( 'i', $iTs ) ) / 5 ) * 5;
		$sCurAmPm = date( 'A', $iTs );
		
		?>
			<tr>
				<td><input type="checkbox" id="<?php echo $prefix; ?>-check" name="<?php echo $prefix; ?>-check" <?php echo $sCbxCheck; ?> /></td>
				<th class="label"><?php echo $label; ?></th>
				<td>
					<select id="<?php echo $prefix; ?>-mon" name="<?php echo $prefix; ?>-mon" <?php echo $sDisable; ?> >
						<?php foreach( $aMonths as $i => $sMonth ): ?>
							<option value="<?php echo $i; ?>" <?php echo ( $iCurMonth == $i ) ? 'selected="selected"' : '' ; ?> ><?php echo $sMonth; ?></option>
						<?php endforeach; ?>
					</select>
					<select id="<?php echo $prefix; ?>-dy" name="<?php echo $prefix; ?>-dy" <?php echo $sDisable; ?> >
						<?php foreach( $aDays as $iDay ): ?>
							<option value="<?php echo $iDay; ?>" <?php echo ( $iCurDay == $iDay ) ? 'selected="selected"' : '' ; ?> ><?php echo $iDay; ?></option>
						<?php endforeach; ?>
					</select>,
					<select id="<?php echo $prefix; ?>-yr" name="<?php echo $prefix; ?>-yr" <?php echo $sDisable; ?> >
						<?php foreach( $aYears as $iYear ): ?>
							<option value="<?php echo $iYear; ?>" <?php echo ( $iCurYear == $iYear ) ? 'selected="selected"' : '' ; ?> ><?php echo $iYear; ?></option>
						<?php endforeach; ?>
					</select>
					@
					<select id="<?php echo $prefix; ?>-hr" name="<?php echo $prefix; ?>-hr" <?php echo $sDisable; ?> >
						<?php foreach( $aHours as $iHour ): ?>
							<option value="<?php echo $iHour; ?>" <?php echo ( $iCurHour == $iHour ) ? 'selected="selected"' : '' ; ?> ><?php echo $iHour; ?></option>
						<?php endforeach; ?>
					</select>
					:
					<select id="<?php echo $prefix; ?>-min" name="<?php echo $prefix; ?>-min" <?php echo $sDisable; ?> >
						<?php foreach( $aMins as $iMin ): ?>
							<option value="<?php echo $iMin; ?>" <?php echo ( $iCurMin == $iMin ) ? 'selected="selected"' : '' ; ?> ><?php echo $iMin; ?></option>
						<?php endforeach; ?>
					</select>
					<select id="<?php echo $prefix; ?>-ampm" name="<?php echo $prefix; ?>-ampm" <?php echo $sDisable; ?> >
						<?php foreach( $aAmPm as $sLabel ): ?>
							<option value="<?php echo $sLabel; ?>" <?php echo ( $sCurAmPm == $sLabel ) ? 'selected="selected"' : '' ; ?> ><?php echo $sLabel; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		<?php
	}
	
	
	// Prints the inner fields for the custom post/page section
	public function innerCustomBox() {
		
		global $post;
		global $wpdb;
		
		$aRes = $wpdb->get_row( "
			SELECT
				e.start_date AS start_date,
				e.expiry_date AS expiry_date
			FROM
				$wpdb->geko_expiry e
			WHERE
				e.post_id = $post->ID
		", ARRAY_A );
		
		// var_dump( $aRes );
		
		// Use nonce for verification
		?>
		
		<input type="hidden" name="geko-expiry_noncename" id="geko-expiry_noncename" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />
		<!-- The actual fields for data entry -->
		
		<!-- <p><?php // echo date( 'l jS \of F Y h:i:s A' ); ?></p> -->
		
		<table class="gexp">
			<?php $this->dateFields( 'gexp-start', 'Start Date:', strtotime( $aRes[ 'start_date' ] ) ); ?>
			<?php $this->dateFields( 'gexp-expiry', 'Expiration Date:', strtotime( $aRes[ 'expiry_date' ] ) ); ?>
		</table>
		
		<?php
		//var_dump($post);
	}
	
	
	//// helpers
	
	//
	private function getMysqlDateFromPost( $prefix ) {
		
		$iHr = $_POST[ sprintf( '%s-hr', $prefix ) ];
		if ( 'PM' == $_POST[ sprintf( '%s-ampm', $prefix ) ] ) {
			if ( 12 != $iHr ) $iHr += 12;			
		} else {
			if ( 12 == $iHr ) $iHr = 0;
		}
		
		return sprintf(
			"%s-%'02d-%'02d %'02d:%'02d:00",
			$_POST[ sprintf( '%s-yr', $prefix ) ],
			$_POST[ sprintf( '%s-mon', $prefix ) ],
			$_POST[ sprintf( '%s-dy', $prefix ) ],
			$iHr,
			$_POST[ sprintf( '%s-min', $prefix ) ]
		);
	}
	
	//
	private function getMysqlDateInsertValue( $prefix ) {
		
		if ( $_POST[ sprintf( '%s-check', $prefix ) ] ) {
			return sprintf( "'%s'", $this->getMysqlDateFromPost( $prefix ) );
		} else {
			return 'NULL';
		}
	}
	
	
	
	
	//// crud methods
	
	// When the post is saved, saves our custom data
	public function savePostdata( $post_id ) {
		
		global $wpdb;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		
		if ( FALSE == wp_verify_nonce( $_POST[ 'geko-expiry_noncename' ], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}
		
		if ( 'page' == $_POST[ 'post_type' ] ) {
			
			if ( FALSE == current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( FALSE == current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		
		/* /
		if ( $_POST[ 'gexp-start-check' ] ) {
			print 'Has Start: ';
			print $this->getMysqlDateFromPost( 'gexp-start' );
		} else {
			print 'Has No Start';		
		}
		
		print '<br /><br /><br />';

		if ( $_POST[ 'gexp-expiry-check' ] ) {
			print 'Has Expiry: ';
			print $this->getMysqlDateFromPost( 'gexp-expiry' );
		} else {
			print 'Has No Expiry';
		}

		print '<br /><br /><br />';

		print $wpdb->geko_expiry;
		/* */
		
		//// DB stuff
		
		// Clean-up first
		$wpdb->query( sprintf( 'DELETE FROM %s WHERE post_id = %d', $wpdb->geko_expiry, $post_id ) );
		
		// Insert if present
		if ( $_POST[ 'gexp-start-check' ] || $_POST[ 'gexp-expiry-check' ] ) {
			
			$wpdb->insert( $wpdb->geko_expiry, array(
				'post_id' => $post_id,
				'start_date' => $this->getMysqlDateInsertValue( 'gexp-start' ),
				'expiry_date' => $this->getMysqlDateInsertValue( 'gexp-expiry' )
			) );
			
		}
		
		return TRUE;	// ???
	}
	
	// When post is deleted
	public function deletePostdata( $post_id ) {
		
		global $wpdb;
		
		$wpdb->query( sprintf( 'DELETE FROM %s WHERE post_id = %d', $wpdb->geko_expiry, $post_id ) );
		
		return TRUE;	// ???
	}
	
	
	
}



