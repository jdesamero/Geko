<?php

//
class Gloc_Bootstrap extends Geko_Wp_Bootstrap
{
	
	
	
}



$aDebug = FALSE;
// $aDebug = TRUE;

/* /
$aDebug = array(
	// 'enable' => '/::start/'
	// 'enable' => '/^Geko_Bootstrap::run/'
	// 'enable' => '/_Bootstrap/'
);
/* */




$oBoot = Gloc_Bootstrap::getInstance();

$oBoot->config( array(
	
	// 'error' => FALSE,
	'debug' => $aDebug,
	
	'role.mng' => TRUE,
	
	'emsg.mng' => TRUE,
	
	'user' => TRUE,
	// 'user.mng' => TRUE,
	'user.rewrite' => TRUE,
	'user.photo' => TRUE,
	// 'user.security' => TRUE,
	
	// 'cat.alias' => TRUE,
	// 'cat.tmpl' => TRUE,
	// 'cat.posttmpl' => TRUE,
	
	// 'post.meta' => TRUE,
	// 'page.meta' => TRUE
	
) )->init()->run();



