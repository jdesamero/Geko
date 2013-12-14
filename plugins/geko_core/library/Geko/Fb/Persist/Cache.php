<?php

//
class Geko_Fb_Persist_Cache extends Geko_Fb_Persist
{
	//
	protected $_sPersistTable = 'fb_geko_cache';
	protected $_sPersistVarsTable = 'fb_geko_cache_vars';
	protected $_sExpireInterval = 'INTERVAL 14 DAY';
	
}


