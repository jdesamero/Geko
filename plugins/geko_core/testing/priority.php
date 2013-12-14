<?php

ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<!-- <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /> -->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Priority</title>
	<style type="text/css">
		
	</style>
</head>

<body>

<h1>Priority</h1>

<?php

//
class Stuff
{
	
	protected $sMe = 'Stuff';
	protected $sFoo;
	
	//
	public function set( $sFoo ) {
		$this->sFoo = $sFoo;
		return $this;
	}
	
	//
	public function get() {
		return $this->sMe . ' --> ' . $this->sFoo;
	}
	
}

//
class Auth extends Stuff
{

	protected $sMe = 'Stuff_Auth';

}

//
class Layout extends Stuff
{

	protected $sMe = 'Stuff_Layout';

}

//
class Service extends Stuff
{

	protected $sMe = 'Stuff_Service';

}

class Prioritize
{
	
	protected $aStuffs = array();
	
	protected $_bSorted = FALSE;
	
	//
	public function add( $oStuff, $iPriority = 1000, $sKey = NULL ) {
		
		static $i = 0;
		
		if ( !$sKey ) {
			$sKey = get_class( $oStuff );
		}
		
		$this->aStuffs[ $sKey ] = array(
			'route' => $oStuff,
			'priority' => $iPriority,
			'idx' => $i++
		);
		
		return $this;
	}
	
	//
	public function sortOnce() {
		uasort( $this->aStuffs, array( $this, 'sortStuffCmp' ) );
		return $this->aStuffs;
	}
	
	//
	public function sortStuffCmp( $a, $b ) {
		
		$a1 = $a[ 'priority' ];
		$b1 = $b[ 'priority' ];
		
		if ( $a1 == $b1 ) {

			$a2 = $a[ 'idx' ];
			$b2 = $b[ 'idx' ];
			
			if ( $a2 == $b2 ) return 0 ;
			return ( $a2 < $b2 ) ? -1 : 1 ;
		}
		
		return ( $a1 < $b1 ) ? -1 : 1 ;
	}
	
	//
	public function remove( $sKey ) {
		unset( $this->aStuffs[ $sKey ] );
		return $this;
	}
	
	//
	public function show() {
		
		$this->sortOnce();
		// $this->sortOnce();
		
		foreach ( $this->aStuffs as $sKey => $aStuff ) {
			
			$oRoute = $aStuff[ 'route' ];
			$iPriority = $aStuff[ 'priority' ];
			$iIdx = $aStuff[ 'idx' ];
			
			printf( '%s - %s - %s - %d<br />', $sKey, $oRoute->get(), $iPriority, $iIdx );
		}
		
		return $this;
	}
	
}

$oPrioritize = new Prioritize();

$oPrioritize
	->add( new Layout() )
	->add( new Layout(), 1000, 'Layout2' )
	->add( new Auth() )
	->add( new Service() )
	->add( new Service(), 100, 'Service2' )
;

$oPrioritize->show();

echo '<br /><br />';

$oPrioritize->remove( 'Layout2' );

$oPrioritize->show();

?>



</body>

</html>