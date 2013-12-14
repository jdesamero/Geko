<?php

//
class Geko_PhpUnit
{
	
	const MODE_REGULAR = 1;
	const MODE_COMPACT = 2;
	
	
	//
	public static function getModeId( $sKey ) {
		
		$aModes = array(
			'regular' => self::MODE_REGULAR,
			'compact' => self::MODE_COMPACT
		);
		
		if ( $iMode = $aModes[ $sKey ] ) return $iMode;
		return self::MODE_COMPACT;
	}
	
	//
	public static function getModeKey( $iMode ) {

		$aModes = array(
			self::MODE_REGULAR => 'regular',
			self::MODE_COMPACT => 'compact'
		);
		
		if ( $sKey = $aModes[ $iMode ] ) return $sKey;
		return 'compact';	
	}
	
	//
	public static function getClasses( $sPath, $sClassNamespace ) {
		
		$sPath = $sPath . '/' . $sClassNamespace;
		
		$aClasses = array();
		$oItr = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $sPath ) );
		while ( $oItr->valid() ) {
			$sPath = $oItr->key();
			$aRegs = array();
			if ( preg_match( '/(' . $sClassNamespace . '.+?)\.php/', $sPath, $aRegs ) ) {
				$aClasses[] = str_replace( '/', '_', $aRegs[ 1 ] );
			}
			$oItr->next();
		}
		
		sort( $aClasses );
		return $aClasses;
	}
	
	
	//	$sPath is path to unit test classes
	public static function run( $sPath, $sClassNamespace, $iMode = self::MODE_REGULAR ) {
		
		$aClasses = self::getClasses( $sPath, $sClassNamespace );
		
		if ( self::MODE_COMPACT == $iMode ) {
			self::runCompact( $aClasses );
		} else {
			self::runRegular( $aClasses );		
		}	
	}
	
	//
	public static function runRegular( $aClasses ) {
		
		// create jump links
		?>
		<ul class="geko_phpunit jumplinks">
			<?php foreach ( $aClasses as $sClass ): ?>
				<?php if ( class_exists( $sClass ) ): ?>
					<li><a href="#<?php echo strtolower( $sClass ); ?>"><?php echo $sClass; ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php
		
		// go through tests
		foreach ( $aClasses as $sClass ) {
		
			if ( class_exists( $sClass ) ) {
								
				// Create a test suite that contains the tests
				// from the ArrayTest class.
				$oSuite = new PHPUnit_Framework_TestSuite( $sClass );
				 
				// Create a test result and attach a SimpleTestListener
				// object as an observer to it.
				$oResult = new Geko_PhpUnit_TestResult();
				
				$oResult->addListener( new Geko_PhpUnit_TestListener_Query() );
				
				// Run the tests.
				$oResult = $oSuite->run( $oResult );
				
				// get the most recent listener instance
				$aListener = Geko_PhpUnit_TestListener_Query::getInstance();
				
				// output
				?>
				<hr />
				<div class="geko_phpunit container">
					
					<h2><a name="<?php echo strtolower( $sClass ); ?>"><?php echo $sClass; ?></a></h2>

					<?php echo strval( $oResult ); ?>
					<br />
					
					<?php echo strval( $aListener ); ?>
					<br />
					
				</div>
				<?php
			}
		}
		
	}


	//
	public static function runCompact( $aClasses ) {
		
		$aCounts = array(
			'test_count' => array( 'Tests', 'count' ),
			'not_implemented_count' => array( 'Not implemented', 'notImplementedCount' ),
			'skipped_count' => array( 'Skipped', 'skippedCount' ),
			'failure_count' => array( 'Failure', 'failureCount' ),
			'error_count' => array( 'Errors', 'errorCount' )
		);
		
		?>
		<table class="geko_phpunit results">
			<thead>
				<tr class="heading">
					<th class="test">Class</th>
					<?php foreach ( $aCounts as $a ): ?>
						<th><?php echo $a[ 0 ]; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $aClasses as $sClass ): ?>
					<?php if ( class_exists( $sClass ) ):
	
						// Create a test suite that contains the tests
						// from the ArrayTest class.
						$oSuite = new Geko_PhpUnit_TestSuite( $sClass );
						 
						// Create a test result and attach a SimpleTestListener
						// object as an observer to it.
						$oResult = new Geko_PhpUnit_TestResult();
						
						$oResult->addListener( new Geko_PhpUnit_TestListener_Query(), 'geko' );
						
						// Run the tests.
						$oResult = $oSuite->run( $oResult );
						
						// get the most recent listener instance
						$aListener = $oResult->getListener( 'geko' );
					
						?>
						<tr class="results">
							<td class="test"><?php echo $sClass; ?></td>
							<?php foreach ( $aCounts as $k => $a ):
								
								$sMethod = $a[ 1 ];
								$sClass = $k;
								$iRes = $oResult->$sMethod();
								
								if ( $iRes && ( $k != 'test_count' ) ) {
									$sClass .= ' error';
								}
								
								?><td class="<?php echo $sClass; ?>"><?php echo $iRes; ?></td>
							<?php endforeach; ?>
						</tr>
						<tr class="messages">
							<td colspan="6"><?php echo strval( $aListener ); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr class="footer">
					<th class="test">Class</th>
					<?php foreach ( $aCounts as $a ): ?>
						<th><?php echo $a[ 0 ]; ?></th>
					<?php endforeach; ?>
				</tr>
			</tfoot>
		</table>
		<?php
		
	}
	
}

