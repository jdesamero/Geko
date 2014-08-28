<?php

require_once( sprintf(
	'%s/external/libs/pearpkgs/PHPUnit-3.4.14/library/PHPUnit/Framework.php',
	dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
) );

//
class Geko_PhpUnit_TestResult extends PHPUnit_Framework_TestResult
{
	
	protected $_aListenerHash = array();
	
	//
	public function addListener( PHPUnit_Framework_TestListener $listener, $sKey = '' ) {
		parent::addListener( $listener );
		if ( $sKey ) {
			$this->_aListenerHash[ $sKey ] = count( $this->listeners ) - 1;
		}
		return $this;
	}
    
	//
	public function getListener( $sKey ) {
		$iIdx = $this->_aListenerHash[ $sKey ];
		return $this->listeners[ $iIdx ];
	}
	
	
	//
	public function outputTable() {
		
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
				<tr>
					<?php foreach ( $aCounts as $a ): ?>
						<th><?php echo $a[ 0 ]; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<?php foreach ( $aCounts as $k => $a ):
						
						$sMethod = $a[ 1 ];
						$sClass = $k;
						$iRes = $this->$sMethod();
						
						if ( $iRes && ( $k != 'test_count' ) ) {
							$sClass .= ' error';
						}
						
						?><td class="<?php echo $sClass; ?>"><?php echo $iRes; ?></td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
		<?php
		
	}
	
	//
	public function __toString() {
		return Geko_String::fromOb( array( $this, 'outputTable' ) );
	}
	
	
}


