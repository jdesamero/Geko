<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE ^ E_WARNING );
// error_reporting( E_ALL );

require_once( '../../../config.inc.php' );

require_once( GEKO_CORE_ROOT . '/standalone.inc.php' );

Geko_Loader::addIncludePaths( GEKO_TEMPLATE_PATH . '/library' );


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Singleton/Once</title>
	<style type="text/css">
		
	</style>
</head>

<body>

<h1>Singleton/Once</h1>

<?php





/* */

//
class Fruit extends Geko_Singleton_Abstract
{
	
	//
	public function start() {
		
		parent::start();
		
		Geko_Once::run( sprintf( '%s::announce', __CLASS__ ), array( $this, 'announceMe' ), array( 'Fruit' ) );
		
	}
	
	//
	public function announceMe( $sMe, $iReps = 0 ) {
		echo sprintf( '%sI am %s!<br />', str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $iReps ), $sMe );
	}
	
}

//
class Apple extends Fruit {

	//
	public function start() {
		
		parent::start();
		
		Geko_Once::run( sprintf( '%s::announce', __CLASS__ ), array( $this, 'announceMe' ), array( 'Apple', 1 ) );
		
	}

}


//
class Macintosh extends Apple {

	//
	public function start() {
		
		parent::start();
		
		Geko_Once::run( sprintf( '%s::announce', __CLASS__ ), array( $this, 'announceMe' ), array( 'Macintosh', 2 ) );
		
	}

}

//
class GrannySmith extends Apple {

	//
	public function start() {
		
		parent::start();
		
		Geko_Once::run( sprintf( '%s::announce', __CLASS__ ), array( $this, 'announceMe' ), array( 'Granny Smith', 2 ) );
		
	}

}



//
class Pear extends Fruit {

	//
	public function start() {
		
		parent::start();
		
		Geko_Once::run( sprintf( '%s::announce', __CLASS__ ), array( $this, 'announceMe' ), array( 'Pear', 1 ) );
		
	}
	
	//
	public function init( $sKind = 'Kronk', $iNumSeeds = 10 ) {
		
		echo sprintf( 'Kind of pear: %s, num seeds: %d<br />', $sKind, $iNumSeeds );
		
		return parent::init();
	}
	
	//
	public function reStart() {
		
		parent::reStart();
		
		Geko_Once::unregister( sprintf( '%s::announce', __CLASS__ ) );
	}
	
}

/* */






/* /

//
class Fruit extends Geko_Singleton_Abstract
{
	
	//
	public function start() {
		
		parent::start();
		
		$this->announceMe( 'Fruit' );
		
	}
	
	//
	public function announceMe( $sMe, $iReps = 0 ) {
		echo sprintf( '%sI am %s!<br />', str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $iReps ), $sMe );
	}
	
}

//
class Apple extends Fruit {

	//
	public function start() {
		
		parent::start();

		$this->announceMe( 'Apple', 1 );
		
	}

}


//
class Macintosh extends Apple {

	//
	public function start() {
		
		parent::start();
		
		$this->announceMe( 'Macintosh', 2 );
		
	}

}

//
class GrannySmith extends Apple {

	//
	public function start() {
		
		parent::start();
		
		$this->announceMe( 'Granny Smith', 2 );
		
	}

}



//
class Pear extends Fruit {

	//
	public function start() {
		
		parent::start();
		
		$this->announceMe( 'Pear', 1 );
		
	}

}

/* */






/* /
$oApple = Apple::getInstance();
$oApple->init();
$oApple->init();

echo '<br /><br />';
/* */


$oMac = Macintosh::getInstance();
$oMac->init();
$oMac->init();

echo '<br /><br />';

$oGran = GrannySmith::getInstance();
$oGran->init();
$oGran->init();

echo '<br /><br />';

$oPear = Pear::getInstance();
$oPear->init();
$oPear->init();
$oPear->reInit( 'Bosc', 2 );

echo '<br /><br />';

Geko_Once::debug();

?>



</body>

</html>