<?php

ini_set( 'display_errors', 1 );
// ini_set( 'scream.enabled', 1 );
error_reporting( E_ALLÊ^ÊE_NOTICE );
// error_reporting( E_ALL );

// ---------------------------------------------------------------------------------------------- //

function geko_render_template() {
	include( TEMPLATEPATH . '/render.php' );
}

// ---------------------------------------------------------------------------------------------- //

define( 'ABS_WP_URL_ROOT', str_replace( 'http://' . $_SERVER[ 'SERVER_NAME' ], '', Geko_Wp::getUrl() ) );

// ---------------------------------------------------------------------------------------------- //

// form transformation hooks

Geko_PhpQuery_FormTransform_Plugin_File::setDefaultFileDocRoot( substr( ABSPATH, 0, strlen( ABSPATH ) - 1 ) );
Geko_PhpQuery_FormTransform_Plugin_File::setDefaultFileUrlRoot( Geko_Wp::getUrl() );

Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_File' );
Geko_PhpQuery_FormTransform::registerPlugin( 'Geko_PhpQuery_FormTransform_Plugin_RowTemplate' );

// ---------------------------------------------------------------------------------------------- //

// adds the hooks: admin_head, admin_body_header, admin_body_footer
// adds the filters: admin_page_source
Geko_Wp_Admin_Hooks::init();

Geko_Wp_Hooks::init();

Geko_Wp_Hooks::attachGekoHookActions(
	'theme_head_late',
	'theme_body_header',
	'theme_body_footer',
	'admin_head',
	'admin_body_header',
	'admin_body_footer'
);

Geko_Wp_Hooks::attachGekoHookFilters(
	'theme_page_source',
	'admin_page_source'
);

$aRoleTypes = Geko_Wp_Role_Types::getInstance()
	->register( 'Geko_Wp_User_RoleType' )
;

Geko_Wp_Role_Manage::getInstance()->init();

Geko_Wp_User_Rewrite::getInstance()->init();
Geko_Wp_User_Photo::getInstance()->init();

// ---------------------------------------------------------------------------------------------- //

// Gloc_User_Manage::getInstance()->init();
// Gloc_Post_Meta::getInstance()->init();
Gloc_Page_Meta::getInstance()->init();


