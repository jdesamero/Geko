<?php

// command line process class

//
class Geko_Proc
{
	
	protected $_iTimeStart = 0;
	protected $_iTimeEnd = 0;
	
	protected $_sStartMsg = 'Start!!!';
	
	
	
	//
	public function start() {
		
		$this->_iTimeStart = time();
		
		$this->output( 'start', $this->_sStartMsg );
		
	}
	
	
	//
	public function finish() {
		
		// finish
		$this->output( 'finish', 'Done!!!' );
		
		$this->_iTimeEnd = time();
		
		$fMinsDuration = ( $this->_iTimeEnd - $this->_iTimeStart ) / 60;
		
		// elapsed time
		$this->output(
			'elapsed_time',
			sprintf( 'Elapsed time: %f mins', $fMinsDuration ),
			array(
				'start' => $this->_iTimeStart,
				'end' => $this->_iTimeEnd,
				'mins' => $fMinsDuration
			)
		);
		
	}
	
	
	//
	public function output( $sKey, $sMsg, $mData = NULL ) {
		
		printf( "%s: %s\n", $sKey, $sMsg );
		
	}
	
	
}


