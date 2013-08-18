<?php

//
class Geko_Wp_Location_Meta extends Geko_Wp_Options_Meta
{



	//// init
	
	//
	public function affix()
	{
		Geko_Wp_Db::addPrefix('geko_location_meta');
		Geko_Wp_Db::addPrefix('geko_location_meta_members');
		
		return $this;
	}
	
	// create table
	public function install()
	{
		Geko_Wp_Options_MetaKey::install();
		
		
		$sSql = "
			CREATE TABLE %s
			(
				ameta_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				address_id BIGINT UNSIGNED,
				meta_key VARCHAR(255),
				meta_value LONGTEXT,
				PRIMARY KEY(ameta_id),
				KEY address_id(address_id),
				KEY meta_key(meta_key)
			)
		";
		
		Geko_Wp_Db::createTable( 'geko_location_meta', $sSql );
							
		$sSql = '
			CREATE TABLE %s
			(
				ameta_id BIGINT UNSIGNED,
				member_id BIGINT UNSIGNED,
				member_value LONGTEXT,
				flags LONGTEXT,
				KEY ameta_id(ameta_id),
				KEY member_id(member_id)
			)
		';
		
		Geko_Wp_Db::createTable( 'geko_location_meta_members', $sSql );
		
		
		return $this;
	}
	



}


