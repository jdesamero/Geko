<?php

//
class GekoX_Wp_Initialize extends Geko_Singleton_Abstract
{
	const ENQUEUE_SCRIPT = 0;
	const ENQUEUE_STYLE = 1;
	const ENQUEUE_ACTION = 2;	
	
	const ADD = 0;
	const ADD_ADMIN = 1;
	const ADD_THEME = 2;
	
	
	protected $_sInstanceClass;
	
	protected $_aEnqueue = array();
	
	
	
	//
	protected function __construct() {
		
		parent::__construct();
		
		$this->_sInstanceClass = get_class( $this );
	}
	
	
	
	//
	public function init() {
		
		add_action( 'init', array( $this, 'initAction' ) );
		
		return $this;
	}
	
	
	//
	public function initAction() {
		
		$this->add();
		
		if ( !is_admin() ) {
			$this->addTheme();
		}
		
		if ( is_admin() ) {
			$this->addAdmin();
		}
		
		return $this;
	}
	
	
	//
	public function add() {
		
		return $this
			->enqueue( self::ADD, self::ENQUEUE_SCRIPT )
			->enqueue( self::ADD, self::ENQUEUE_STYLE )
			->enqueue( self::ADD, self::ENQUEUE_ACTION )
		;
	}
	
	//
	public function addAdmin() {
	
		return $this
			->enqueue( self::ADD_ADMIN, self::ENQUEUE_SCRIPT )
			->enqueue( self::ADD_ADMIN, self::ENQUEUE_STYLE )
			->enqueue( self::ADD_ADMIN, self::ENQUEUE_ACTION )
		;
	}
	
	//
	public function addTheme() {
	
		return $this
			->enqueue( self::ADD_THEME, self::ENQUEUE_SCRIPT )
			->enqueue( self::ADD_THEME, self::ENQUEUE_STYLE )
			->enqueue( self::ADD_THEME, self::ENQUEUE_ACTION )
		;
	}
	
	
	//// accessors
	
	//
	public function addToQueue( $mValue, $iAction, $iQueue ) {
		$this->_aEnqueue[ $iAction ][ $iQueue ] = $mValue;
		return $this;
	}
	
	//
	public function enqueueScript( $mValue, $iAction ) {
		return $this->addToQueue( $mValue, $iAction, self::ENQUEUE_SCRIPT );
	}

	//
	public function enqueueStyle( $mValue, $iAction ) {
		return $this->addToQueue( $mValue, $iAction, self::ENQUEUE_STYLE );
	}
	
	//
	public function enqueueAction( $mValue, $iAction ) {
		return $this->addToQueue( $mValue, $iAction, self::ENQUEUE_ACTION );
	}
	
	
	//// helpers
	
	//
	public function enqueue( $iAction, $iQueue ) {
		
		if ( is_array( $aEnqueue = $this->_aEnqueue[ $iAction ][ $iQueue ] ) ) {
			
			if ( self::ENQUEUE_SCRIPT == $iQueue ) {
				
				foreach ( $aEnqueue as $sScript ) {
					wp_enqueue_script( $sScript );
				}
				
			} elseif ( self::ENQUEUE_STYLE == $iQueue ) {
				
				foreach ( $aEnqueue as $sStyle ) {
					wp_enqueue_style( $sStyle );
				}
				
			} else {
				
				foreach ( $aEnqueue as $aAction ) {
					call_user_func_array( 'add_action', $aAction );
				}
				
			}
			
		}
		
		return $this;
	}
	
	
}


