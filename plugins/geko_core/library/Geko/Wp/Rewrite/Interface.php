<?php

//
interface Geko_Wp_Rewrite_Interface
{
	public function init();
	public function generateRewriteRules();
	public function queryVars( $aVars );
	public function templateRedirect();
}


