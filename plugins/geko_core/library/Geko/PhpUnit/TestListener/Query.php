<?php

require_once( sprintf(
	'%s/external/libs/pearpkgs/PHPUnit-3.4.14/library/PHPUnit/Framework.php',
	dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) )
) );

//
class Geko_PhpUnit_TestListener_Query
	extends Geko_Entity_Query
	implements PHPUnit_Framework_TestListener
{
	
	const STAT_ERROR = 1;
	
	//
	protected $_oSuite;
	protected $_oResult;
	
	
	
	
	//
	public function getTestResults() {
		return $this->_aEntities;
	}
	
	//
	public function getSuiteName() {
		return ( $this->_oSuite ) ? $this->_oSuite->getName() : '' ;
	}
	
	//
	public function getSuite() {
		return $this->_oSuite;
	}
	
	//
	public function getSection( $sMethod ) {
		return Geko_Inflector::humanize( Geko_Inflector::underscore(
			str_replace( __CLASS__ . '::', '', $sMethod )
		) );
	}
	
	//
	public function getIdx() {
		return count( $this->_aEntities ) + 1;
	}
	
	//
	public function formatTestName( $oTest ) {
		return Geko_Inflector::humanize( Geko_Inflector::underscore(
			substr_replace( $oTest->getName(), '', 0, 4 )
		) );
	}
	
	//
	public function formatMessage( $e ) {
				
		$sMsg = preg_replace_callback(
			'/<(boolean|string|integer):(.*?)>/', array( $this, 'formatMessageTag' ),
			$e->getMessage()
		);
		
		return $sMsg;
	}
	
	//
	public function formatMessageTag( $aRegs ) {
		
		$sType = $aRegs[ 1 ];
		$sValue = $aRegs[ 2 ];
		
		if ( '' === $sValue ) $sValue = '&#8220;&#8221;';
		
		return '<span class="' . $sType . '">' . $sValue . '</span>';
	}
	
	//
	public function setResult( $oResult ) {
		$this->_oResult = $oResult;
		return $this;
	}
	
	
	
	//// PHPUnit_Framework_TestListener methods
	
	//
	public function startTestSuite( PHPUnit_Framework_TestSuite $oSuite ) {
		
		if ( !$this->_oSuite ) $this->_oSuite = $oSuite;
		
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $oSuite->getName()
		);
	}

	//
	public function startTest( PHPUnit_Framework_Test $oTest ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $this->formatTestName( $oTest )
		);
	}
	
	//
	public function addError( PHPUnit_Framework_Test $oTest, Exception $e, $fTime ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $this->formatTestName( $oTest ),
			'message' => $this->formatMessage( $e ),
			'time' => $fTime,
			'status' => self::STAT_ERROR
		);
	}
	
	//
	public function addFailure( PHPUnit_Framework_Test $oTest, PHPUnit_Framework_AssertionFailedError $e, $fTime ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $this->formatTestName( $oTest ),
			'message' => $this->formatMessage( $e ),
			'time' => $fTime,
			'status' => self::STAT_ERROR
		);
	}
	
	//
	public function addIncompleteTest( PHPUnit_Framework_Test $oTest, Exception $e, $fTime ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $this->formatTestName( $oTest ),
			'message' => $this->formatMessage( $e ),
			'time' => $fTime,
			'status' => self::STAT_ERROR
		);
	}
	
	//
	public function addSkippedTest( PHPUnit_Framework_Test $oTest, Exception $e, $fTime ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $this->formatTestName( $oTest ),
			'message' => $this->formatMessage( $e ),
			'time' => $fTime,
			'status' => self::STAT_ERROR
		);
	}
	
	//
	public function endTest( PHPUnit_Framework_Test $oTest, $fTime ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $this->formatTestName( $oTest ),
			'time' => $fTime
		);
	}
	
	//
	public function endTestSuite( PHPUnit_Framework_TestSuite $oSuite ) {
		$this->_aEntities[] = array(
			'idx' => $this->getIdx(),
			'section' => $this->getSection( __METHOD__ ),
			'title' => $oSuite->getName()
		);	
	}
	
	//
	public function outputTable() {		
		?>
		<table class="geko_phpunit messages">
			<thead>
				<tr>
					<th>Index</th>
					<th>Section</th>
					<th>Title</th>
					<th>Message</th>
					<th>Time</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $this as $oItem ):
					
					$sClass = '';
					
					if ( self::STAT_ERROR == $oItem->getStatus() ) {
						$sClass = ' class="error" ';
					}
					
					?><tr <?php echo $sClass; ?> >
						<td class="id"><?php $oItem->echoId(); ?></td>
						<td class="section"><?php $oItem->echoSection(); ?></td>
						<td class="title"><?php $oItem->echoTitle(); ?></td>
						<td class="message"><?php $oItem->echoMessage(); ?></td>
						<td class="time"><?php $oItem->echoTime(); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>	
		<?php
	}
	
	//
	public function __toString() {
		return Geko_String::fromOb( array( $this, 'outputTable' ) );
	}
	
}

