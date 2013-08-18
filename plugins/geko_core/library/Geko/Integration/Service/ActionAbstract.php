<?php

//
abstract class Geko_Integration_Service_ActionAbstract
	extends Geko_Integration
	implements Geko_Integration_Service_ActionInterface
{
	abstract public static function exec( $aParams );
}


