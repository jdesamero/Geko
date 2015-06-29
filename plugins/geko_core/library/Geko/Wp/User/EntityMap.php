<?php

//
class Geko_Wp_User_EntityMap extends Geko_Entity_Map
{
	
	protected $_aFieldMappings = array(
		'id' => 'ID',
		'title' => 'display_name',
		'slug' => 'user_nicename',
		'date_created' => 'user_registered'
	);
	
	
	
}


