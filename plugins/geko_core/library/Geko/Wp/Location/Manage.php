<?php

//
class Geko_Wp_Location_Manage extends Geko_Wp_Options_Manage
{
	protected static $aCache = array();
	
	protected $_iObjectId = 0;
	protected $_sObjectType;
	protected $_sLocationSubType = '';
	
	protected $_aFields = array(
		'street_number',
		'street_name',
		'street_direction',
		'address_line_1',
		'address_line_2',
		'address_line_3',
		'city',
		'province_id',
		'country_id',
		'continent_id',
		'postal_code',
		'latitude',
		'longitude',
		'lat_offset',
		'long_offset'
	);
		
	protected $_aFieldLabels = array(
		'street_number' => 'Street Number',
		'street_name' => 'Street Name',
		'street_direction' => 'Street Direction',
		'address_line_1' => 'Address Line 1',
		'address_line_2' => 'Address Line 2',
		'address_line_3' => 'Address Line 3',
		'city' => 'City',
		'province_id' => 'Province',
		'country_id' => 'Country',
		'continent_id' => 'Continent',
		'postal_code' => 'Postal Code',
		'latitude' => 'Latitude',
		'lat_offset' => 'Latitude Offset',
		'longitude' => 'Longitude',
		'long_offset' => 'Longitude Offset'
	);
	
	protected $_aFieldDescriptions = array();
	
	protected static $bCalledInstall = FALSE;
	
	protected $_bHasDisplayMode = FALSE;
	
	
	
	
	//// methods
	
	//
	public function affix() {
		
		global $wpdb;
		
		Geko_Wp_Options_MetaKey::init();
		
		//// register tables
		
		// address
		
		$sTableName = 'geko_location_address';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'a' )
			->fieldBigInt( 'address_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'object_id' )
			->fieldSmallInt( 'objtype_id' )
			->fieldSmallInt( 'subtype_id' )
			->fieldVarChar( 'street_number', array( 'size' => 32 ) )
			->fieldVarChar( 'street_name', array( 'size' => 256 ) )
			->fieldVarChar( 'street_direction', array( 'size' => 64 ) )
			->fieldVarChar( 'address_line_1', array( 'size' => 256 ) )
			->fieldVarChar( 'address_line_2', array( 'size' => 256 ) )
			->fieldVarChar( 'address_line_3', array( 'size' => 256 ) )
			->fieldVarChar( 'city', array( 'size' => 256, 'key' ) )
			->fieldInt( 'province_id', array( 'unsgnd', 'key' ) )
			->fieldVarChar( 'postal_code', array( 'size' => 32, 'key' ) )
			->fieldFloat( 'latitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'lat_offset', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'longitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'long_offset', array( 'size' => '10,7', 'sgnd' ) )
			->indexKey( 'obj_id_type', array( 'object_id', 'objtype_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		// province
		
		$sTableName1 = 'geko_location_province';
		Geko_Wp_Db::addPrefix( $sTableName1 );
		
		$oSqlTable1 = new Geko_Sql_Table();
		$oSqlTable1
			->create( $wpdb->$sTableName1, 'p' )
			->fieldInt( 'province_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'province_name', array( 'size' => 256 ) )
			->fieldVarChar( 'province_abbr', array( 'size' => 16 ) )
			->fieldInt( 'country_id', array( 'unsgnd', 'key' ) )
			->fieldInt( 'rank', array( 'unsgnd' ) )
		;
		
		$this->addTable( $oSqlTable1, FALSE );
		
		
		// country
		
		$sTableName2 = 'geko_location_country';
		Geko_Wp_Db::addPrefix( $sTableName2 );
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( $wpdb->$sTableName2, 'c' )
			->fieldInt( 'country_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'country_name', array( 'size' => 256 ) )
			->fieldVarChar( 'country_abbr', array( 'size' => 16 ) )
			->fieldInt( 'continent_id', array( 'unsgnd', 'key' ) )
			->fieldInt( 'rank', array( 'unsgnd' ) )
		;
		
		$this->addTable( $oSqlTable2, FALSE );
		
		
		// continent
		
		$sTableName3 = 'geko_location_continent';
		Geko_Wp_Db::addPrefix( $sTableName3 );

		$oSqlTable3 = new Geko_Sql_Table();
		$oSqlTable3
			->create( $wpdb->$sTableName3, 't' )
			->fieldTinyInt( 'continent_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'continent_name', array( 'size' => 256 ) )
			->fieldVarChar( 'continent_abbr', array( 'size' => 16 ) )
			->fieldTinyInt( 'rank', array( 'unsgnd' ) )
		;
		
		$this->addTable( $oSqlTable3, FALSE );
		
		
		// geocache
		
		$sTableName4 = 'geko_location_geocache';
		Geko_Wp_Db::addPrefix( $sTableName4 );

		$oSqlTable4 = new Geko_Sql_Table();
		$oSqlTable4
			->create( $wpdb->$sTableName4, 'g' )
			->fieldVarChar( 'geo_key', array( 'size' => 64, 'notnull', 'prky' ) )
			->fieldFloat( 'latitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'longitude', array( 'size' => '10,7', 'sgnd' ) )
		;
		
		$this->addTable( $oSqlTable4, FALSE );
		
		
		return $this;
	}
	
	
	//
	public function affixAdmin() {
		wp_enqueue_script( 'geko_wp_location' );
		return $this;
	}
	
	
	// create table
	public function install() {
		
		global $wpdb;
		
		Geko_Wp_Options_MetaKey::install();
		
		$this->createTable( $this->getPrimaryTable() );
		
		$this->createTable( $wpdb->geko_location_province );
		$this->createTable( $wpdb->geko_location_country );
		$this->createTable( $wpdb->geko_location_continent );
		$this->createTable( $wpdb->geko_location_geocache );
		
		return $this;		
	}
	
	
	
	
	//
	public function addAdminHead( $oPlugin = NULL ) {
		
		parent::addAdminHead( $oPlugin );
		
		$oManage = $this->getManage( $oPlugin );
		$sPrefix = $this->getPrefixForDoc( $oPlugin );
		
		if ( $oManage->isDisplayMode( 'add|edit' ) ):
			
			$aJsonParams = array(
				'prefix' => $sPrefix
			);
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					
					$.gekoWpLocation( oParams );
					
				} );
				
			</script><?php
			
		endif;
		
		return $this;
	}
	
	//
	public function attachPage() { }
	
	
	//
	public function initEntities( $oMainEnt = NULL, $aParams = array() ) {
		
		if ( !$this->_oCurrentEntity && $this->_iObjectId ) {
			
			$aParams[ 'object_id' ] = $this->_iObjectId;
			
			if ( $this->_sObjectType ) {
				$aParams[ 'object_type' ] = $this->_sObjectType;
			}
			
			if ( $this->_sLocationSubType ) {
				$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sLocationSubType );
			}
			
			$this->_oCurrentEntity = call_user_func(
				array( $this->_sEntityClass, 'getOne' ), $aParams, FALSE
			);
			
			if ( $this->_oCurrentEntity->isValid() ) {
				$this->_iCurrentEntityId = $this->_oCurrentEntity->getId();
			}
			
		}
		
		return $this;
	}
	
	
	
	
	
	//// accessors
	
	//
	public function getObjectType( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getObjectType();
		}
		return $this->_sObjectType;
	}

	//
	public function getLocationSubType( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getLocationSubType();
		}
		return $this->_sLocationSubType;
	}
	
	// return a prefix
	public function getPrefix( $oPlugin = NULL ) {
		return ( $oPlugin ) ? $oPlugin->getPrefix() : parent::getPrefix( $oPlugin );
	}
	
	
	//
	public function getSectionLabel( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getSectionLabel();
		}
		return $this->_sSectionLabel;
	}

	//
	public function getManage( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getManage();
		}
		return $this;
	}
		
	
	
	//
	public function getFields( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getFields();
		}
		return $this->_aFields;
	}
	
	//
	public function setFields( $aFields ) {
		$this->_aFields = $aFields;
		return $this;
	}
	
	//
	public function setFieldLabels( $aFieldLabels, $bOverride = TRUE ) {
		if ( $bOverride ) {
			$this->_aFieldLabels = array_merge( $this->_aFieldLabels, $aFieldLabels );
		} else {
			$this->_aFieldLabels = $aFieldLabels;
		}
		return $this;
	}
	
	//
	public function getFieldLabels( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getFieldLabels();
		}
		return $this->_aFieldLabels;
	}
	
	//
	public function setFieldDescriptions( $aFieldDescriptions ) {
		$this->_aFieldDescriptions = $aFieldDescriptions;
		return $this;
	}
	
	//
	public function getFieldDescriptions( $oPlugin = NULL ) {
		if ( is_a( $oPlugin, 'Geko_Wp_Options_Plugin' ) ) {
			return $oPlugin->getFieldDescriptions();
		}
		return $this->_aFieldDescriptions;
	}
	
	
	
	//// utility methods
	
	//
	public function getCities( $bUnsetCache = FALSE ) {
		
		global $wpdb;

		if ( $bUnsetCache ) {
			unset( self::$aCache[ 'cities' ] );
		}
		
		if ( !isset( self::$aCache[ 'cities' ] ) ) {
			self::$aCache[ 'cities' ] = $wpdb->get_results( "
				SELECT			DISTINCT
								a.city,
								a.province_id
				FROM			$wpdb->geko_location_address a
				WHERE			( 0 != a.province_id ) AND 
								( a.province_id IS NOT NULL ) AND 
								( '' != TRIM( a.city ) ) AND 
								( a.city IS NOT NULL )
			" );
		}
		
		return self::$aCache[ 'cities' ];
	}
	
	//
	public function getProvinces( $bUnsetCache = FALSE ) {
		
		global $wpdb;
		
		if ( $bUnsetCache ) {
			unset( self::$aCache[ 'provinces' ] );
		}
		
		if ( !isset( self::$aCache[ 'provinces' ] ) ) {
			
			$aHash = array();
			$aProvinces = $wpdb->get_results( "
				SELECT			*
				FROM			$wpdb->geko_location_province
				ORDER BY		province_name
			" );
			
			foreach ( $aProvinces as $oProvince ) {
				$aHash[ $oProvince->province_id ] = $oProvince;
			}
			
			self::$aCache[ 'provinces' ] = $aHash;
		}
		
		return self::$aCache[ 'provinces' ];
	}
	
	//
	public function getProvincePair() {
	
		if ( !isset( self::$aCache[ 'province_pair' ] ) ) {
			
			self::$aCache[ 'province_pair' ] = array();
			$aProvs = $this->getProvinces();
			
			foreach ( $aProvs as $oProv ) {
				self::$aCache[ 'province_pair' ][ $oProv->province_id ] = $oProv->province_name;
			}
		}
		
		return self::$aCache[ 'province_pair' ];
	}
	
	//
	public function getProvinceNameLookup() {
	
		if ( !isset( self::$aCache[ 'province_name_lookup' ] ) ) {
			
			self::$aCache[ 'province_name_lookup' ] = array();
			$aProvs = $this->getProvinces();
			
			foreach ( $aProvs as $oProv ) {
				self::$aCache[ 'province_name_lookup' ][ strtolower( $oProv->province_name ) ] = $oProv->province_id;
			}
		}
		
		return self::$aCache[ 'province_name_lookup' ];
	}
	
	//
	public function getProvinceAbbrLookup() {
	
		if ( !isset( self::$aCache[ 'province_abbr_lookup' ] ) ) {
			
			self::$aCache[ 'province_abbr_lookup' ] = array();
			$aProvs = $this->getProvinces();
			
			foreach ( $aProvs as $oProv ) {
				self::$aCache[ 'province_abbr_lookup' ][ strtolower( $oProv->province_abbr ) ] = $oProv->province_id;
			}
		}
		
		return self::$aCache[ 'province_abbr_lookup' ];
	}
	
	//
	public function getProvinceName( $mKey ) {
		
		// $mKey can be id or province abbr
		if ( preg_match( '/^[0-9]+$/', $mKey ) ) {
			// look up by id
			$iProvId = $mKey;
		} else {
			// look up by abbr
			$aProvAbbr = $this->getProvinceAbbrLookup();
			$iProvId = $aProvAbbr[ strtolower( $mKey ) ];
		}
		
		$aProvId = $this->getProvinces();
		return $aProvId[ $iProvId ]->province_name;
	}
	
	
		
	//
	public function getCountries( $bUnsetCache = FALSE ) {
		
		global $wpdb;
		
		if ( $bUnsetCache ) {
			unset( self::$aCache[ 'countries' ] );
		}
		
		if ( !isset( self::$aCache[ 'countries' ] ) ) {
			
			$aHash = array();
			$aCountries = $wpdb->get_results( "
				SELECT			*
				FROM			$wpdb->geko_location_country
				ORDER BY		rank,
								country_name
			" );
			
			foreach ( $aCountries as $oCountry ) {
				$aHash[ $oCountry->country_id ] = $oCountry;
			}
			
			self::$aCache[ 'countries' ] = $aHash;
		}
		
		return self::$aCache[ 'countries' ];
	}
	
	//
	public function getCountryPair() {
	
		if ( !isset( self::$aCache[ 'country_pair' ] ) ) {
			
			self::$aCache[ 'country_pair' ] = array();
			$aCountries = $this->getCountries();
			
			foreach ( $aCountries as $oCountry ) {
				self::$aCache[ 'country_pair' ][ $oCountry->country_id ] = $oCountry->country_name;
			}
		}
		
		return self::$aCache[ 'country_pair' ];
	}
	
	//
	public function getContinents( $bUnsetCache = FALSE ) {
		
		global $wpdb;
		
		if ( $bUnsetCache ) {
			unset( self::$aCache[ 'continents' ] );
		}
		
		if ( !isset( self::$aCache[ 'continents' ] ) ) {
			
			$aHash = array();			
			$aContinents = $wpdb->get_results( "
				SELECT			*
				FROM			$wpdb->geko_location_continent
				ORDER BY		continent_name
			" );
			
			foreach ( $aContinents as $oContinent ) {
				$aHash[ $oContinent->continent_id ] = $oContinent;
			}
			
			self::$aCache[ 'continents' ] = $aHash;
		}
		
		return self::$aCache[ 'continents' ];
	}
	
	//
	public function getContinentPair() {
	
		if ( !isset( self::$aCache[ 'continent_pair' ] ) ) {
			
			self::$aCache[ 'continent_pair' ] = array();
			$aContinents = $this->getContinents();
			
			foreach ( $aContinents as $oContinent ) {
				self::$aCache[ 'continent_pair' ][ $oContinent->continent_id ] = $oContinent->continent_name;
			}
		}
		
		return self::$aCache[ 'continent_pair' ];
	}
	
	//
	public function lookupProvinceCountry( $iProvinceId ) {
		
		$aRes = array( '', '' );
		
		$aProvinces = $this->getProvinces();
		if ( $oProvince = $aProvinces[ $iProvinceId ] ) {
			$aRes[ 0 ] = $oProvince->province_name;
			$aCountries = $this->getCountries();
			if ( $oCountry = $aCountries[ $oProvince->country_id ] ) {
				$aRes[ 1 ] = $oCountry->country_name;
			}
		}
		
		return $aRes;
	}
	
	
	
	//
	public function getGmap() {
		
		if ( !isset( self::$aCache[ 'gmap' ] ) ) {
			self::$aCache[ 'gmap' ] = new Geko_Google_Map();
		}
		
		return self::$aCache[ 'gmap' ];
	}
	
	// hash the given address and see if it's in the geko_geolocation_cache table
	// if not, look it up and store, otherwise retrieve the cached value
	public function getCoordinates( $sAddress ) {
		
		global $wpdb;
		
		$sHash = md5( $sAddress );
		
		$sQuery = "
			SELECT 				g.latitude,
								g.longitude
			FROM				$wpdb->geko_location_geocache g
			WHERE				g.geo_key = '$sHash'
		";
		
		if ( $oRes = $wpdb->get_row( $sQuery ) ) {
			
			return array( $oRes->latitude, $oRes->longitude );	
			
		} else {
			
			$aCoords = $this
				->getGmap()
				->query( $sAddress )
				->getCoordinates()
			;
			
			// insert into cache
			$wpdb->insert(
				$wpdb->geko_location_geocache,
				array(
					'geo_key' => $sHash,
					'latitude' => $aCoords[ 0 ],
					'longitude' => $aCoords[ 1 ]
				)
			);
			
			return $aCoords;
		}
		
	}
	
	//
	public function getPrimaryTable() {
		
		if ( $this->_sInstanceClass != __CLASS__ ) {
			$oMng = Geko_Singleton_Abstract::getInstance( __CLASS__ );
			return $oMng->getPrimaryTable();
		}
		
		return parent::getPrimaryTable();
	}
	
	
	
	//// load
	
	//
	public function getStoredOptions( $oPlugin = NULL ) {
		
		$oCurrentEntity = $this->getCurrentEntity( $oPlugin );
		$aFields = $this->getFields( $oPlugin );
		$sPrefix = $this->getPrefixForDoc( $oPlugin );
		
		if ( $oCurrentEntity && $oCurrentEntity->isValid() ) {
			
			foreach ( $aFields as $sField ) {
				if ( $oCurrentEntity->hasEntityProperty( $sField ) ) {
					$sPostKey = $sPrefix . $sField;
					$aRet[ $sPostKey ] = $oCurrentEntity->getEntityPropertyValue( $sField );
				}
			}
			
		}
		
		return $aRet;
	}
	
	
	
	
	
	//// output functions
	
	
	//
	public function outputCitySelectHtml( $sFormId = 'city_list', $sEmptyValLabel = NULL, $oPlugin ) {
		
		global $wpdb;
		
		if ( NULL === $sEmptyValLabel ) {
			$aFieldLabels = $this->getFieldLabels( $oPlugin );
			$sEmptyValLabel = 'Select a ' . $aFieldLabels[ 'city' ];
		}
		
		?>
		<select id="<?php echo $sFormId; ?>" name="<?php echo $sFormId; ?>" class="location_sel city">
			<?php if ( $sEmptyValLabel ): ?>
				<option value="" class="default"><?php echo $sEmptyValLabel; ?></option>
			<?php endif; ?>
			<?php foreach ( $this->getCities() as $oCity ): ?>
				<option value="<?php echo htmlspecialchars( $oCity->city ); ?>" class="province-<?php echo $oCity->province_id; ?>"><?php echo $oCity->city; ?></option>
			<?php endforeach; ?>
		</select>		
		<?php
	}
	
	//
	public function outputProvinceSelectHtml( $sFormId = 'province_id', $sEmptyValLabel = NULL, $oPlugin ) {
		
		global $wpdb;
		
		if ( NULL === $sEmptyValLabel ) {
			$aFieldLabels = $this->getFieldLabels( $oPlugin );
			$sEmptyValLabel = 'Select a ' . $aFieldLabels[ 'province_id' ];
		}
		
		?>
		<select id="<?php echo $sFormId; ?>" name="<?php echo $sFormId; ?>" class="location_sel province">
			<?php if ( $sEmptyValLabel ): ?>
				<option value="" class="default"><?php echo $sEmptyValLabel; ?></option>
			<?php endif; ?>
			<?php foreach ( $this->getProvinces() as $oProvince ): ?>
				<option value="<?php echo $oProvince->province_id; ?>" class="country-<?php echo $oProvince->country_id; ?>"><?php echo $oProvince->province_name; ?></option>
			<?php endforeach; ?>
		</select>		
		<?php
	}
	
	//
	public function outputCountrySelectHtml( $sFormId = 'country_id', $sEmptyValLabel = NULL, $oPlugin ) {
		
		global $wpdb;
		
		if ( NULL === $sEmptyValLabel ) {
			$aFieldLabels = $this->getFieldLabels( $oPlugin );
			$sEmptyValLabel = 'Select a ' . $aFieldLabels[ 'country_id' ];
		}
		
		?>	
		<select id="<?php echo $sFormId; ?>" name="<?php echo $sFormId; ?>" class="location_sel country">
			<?php if ( $sEmptyValLabel ): ?>
				<option value="" class="default"><?php echo $sEmptyValLabel; ?></option>
			<?php endif; ?>
			<?php foreach ( $this->getCountries() as $oCountry ): ?>
				<option value="<?php echo $oCountry->country_id; ?>" class="continent-<?php echo $oCountry->continent_id; ?>"><?php echo $oCountry->country_name; ?></option>
			<?php endforeach; ?>
		</select>		
		<?php
	}
	
	//
	public function outputContinentSelectHtml( $sFormId = 'continent_id', $sEmptyValLabel = NULL ) {
		
		global $wpdb;
		
		if ( NULL === $sEmptyValLabel ) {
			$sEmptyValLabel = 'Select a ' . $this->_aFieldLabels[ 'continent_id' ];
		}
		
		?>
		<select id="<?php echo $sFormId; ?>" name="<?php echo $sFormId; ?>" class="location_sel continent">
			<?php if ( $sEmptyValLabel ): ?>
				<option value="" class="default"><?php echo $sEmptyValLabel; ?></option>
			<?php endif; ?>
			<?php foreach ( $this->getContinents() as $oContinent ): ?>
				<option value="<?php echo $oContinent->continent_id; ?>"><?php echo $oContinent->continent_name; ?></option>
			<?php endforeach; ?>
		</select>		
		<?php
	}
	
	
	
	//
	public function formFieldRow( $sField, $sLabel, $sDescription, $oPlugin = NULL ) {
		?>
		<p>
			<label class="main"><?php echo $sLabel; ?></label>
			<?php $this->formField( $sField ); ?>
			<?php if ( $sDescription ): ?>
				<label class="description"><?php echo $sDescription; ?></label>
			<?php endif; ?>
		</p>
		<?php	
	}
	
	//
	public function formField( $sField, $oPlugin = NULL ) {
		if ( 'province_id' == $sField ):
			$this->outputProvinceSelectHtml( $sField, NULL, $oPlugin );
		elseif ( 'country_id' == $sField ):
			$this->outputCountrySelectHtml( $sField, NULL, $oPlugin );
		elseif ( 'continent_id' == $sField ):
			$this->outputContinentSelectHtml( $sField, NULL, $oPlugin );
		else:
			$sDisabled = ( ( 'latitude' == $sField ) || ( 'longitude' == $sField ) ) ? 'disabled="disabled"' : '';
			?><input id="<?php echo $sField; ?>" name="<?php echo $sField; ?>" type="text" class="text" value="" <?php echo $sDisabled; ?> /><?php
		endif;
	}
	
	//
	public function formFields( $oPlugin = NULL ) {
		
		$aFields = $this->getFields( $oPlugin );
		$aFieldLabels = $this->getFieldLabels( $oPlugin );
		$aFieldDescriptions = $this->getFieldDescriptions( $oPlugin );
		
		$oCurrentEntity = $this->getCurrentEntity( $oPlugin );
		
		if ( $oCurrentEntity ) do_action( 'admin_geko_location_main_fields', $oCurrentEntity, 'pre' );
		
		foreach ( $aFields as $sField ) {
			$this->formFieldRow( $sField, $aFieldLabels[ $sField ], $aFieldDescriptions[ $sField ], $oPlugin );	
		}
		
		if ( $oCurrentEntity ) do_action( 'admin_geko_location_main_fields', $oCurrentEntity, 'main' );
		
	}
	
	//
	public function subMainFieldTitles( $oPlugin = NULL ) {
		
		$aFields = $this->getFields( $oPlugin );
		$aFieldLabels = $this->getFieldLabels( $oPlugin );
		
		foreach ( $aFields as $sField ):
			?><th><?php echo $aFieldLabels[ $sField ]; ?></th><?php
		endforeach;
		
	}
	
	//
	public function subMainFieldColumns( $oPlugin = NULL ) {
		
		$aFields = $this->getFields( $oPlugin );
		
		foreach ( $aFields as $sField ):
			?><td><?php $this->formField( $sField, $oPlugin ); ?></td><?php
		endforeach;
		
	}
	
	
	
	//// form processing/injection methods
	
	// plug into the add category form
	public function setupFields( $oPlugin = NULL ) {
		
		$aParts = $this->extractParts( $oPlugin );
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			
			$sLabel = Geko_String::sw( '<label for="%s$1">%s$0</label>', $aPart[ 'label' ], $aPart[ 'name' ] );
			$sFieldGroup = Geko_String::sw( '%s<br />', $aPart[ 'field_group' ] );
			
			$sFields .= '
				<tr class="form-field"' . $sRowId . '>
					<th>' . $sLabel . '</th>
					<td>
						' . $sFieldGroup . '
						' . Geko_String::sw( '<span class="description">%s</span>', $aPart[ 'description' ] ) . '
					</td>
				</tr>
			';
		}
		
		return $sFields;
	}
	
	//
	public function changeDoc( $oDoc ) {
		$oDoc[ 'input.text' ]->addClass( 'regular-text' );
	}
	
	// function outputForm( $oPlugin ) or function outputForm( $oEntity, Section, $oPlugin )
	public function outputForm( $mArg1 = NULL, $mArg2 = NULL, $mArg3 = NULL ) {

		if ( is_a( $mArg1, 'Geko_Wp_Options_Plugin' ) ) {
			$oPlugin = $mArg1;
		} elseif ( is_a( $mArg3, 'Geko_Wp_Options_Plugin' ) ) {
			$oPlugin = $mArg3;
		}
		
		?>
		<h3><?php echo $this->getSectionLabel( $oPlugin ); ?></h3>
		<?php $this->preFormFields( $oPlugin ); ?>
		<table class="form-table">
			<?php echo $this->setupFields( $oPlugin ); ?>
		</table>
		<?php
	}
	
	
	
	
	
	// save the data
	public function save( $aParams, $sMode = 'insert', $aVals = NULL, $oPlugin = NULL ) {
		
		global $wpdb;
		
		$aKeys = array();
		
		// prepare params
		
		$sObjectType = $this->getObjectType( $oPlugin );
		$sLocationSubType = $this->getLocationSubType( $oPlugin );
		$aFieldList = $this->getFields( $oPlugin );
		$sPrefix = $this->getPrefixForDoc( $oPlugin );
		
		if ( !$aParams[ 'object_id' ] ) $aParams[ 'object_id' ] = $this->_iObjectId;
		if ( !$aParams[ 'object_type' ] ) $aParams[ 'object_type' ] = $sObjectType;
		
		if ( $aParams[ 'object_type' ] ) {
			$aParams[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'object_type' ] );
		}
		
		if ( $aParams[ 'sub_type' ] && !$sLocationSubType ) {
			$sLocationSubType = $aParams[ 'sub_type' ];
		}
		
		if ( $sLocationSubType ) {
			$aParams[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $sLocationSubType );
		}
		
		$bUpdate = FALSE;
		if ( 'update' == $sMode ) {
			
			$oSql = new Geko_Sql_Select();
			$oSql
				->field( 1, 'test' )
				->from( $wpdb->geko_location_address )
			;
			
			if ( $aParams[ 'subtype_id' ] ) {
				$oSql->where( 'subtype_id = ?', $aParams[ 'subtype_id' ] );
				$aKeys[ 'subtype_id' ] = $aParams[ 'subtype_id' ];
			}
			
			if ( $aParams[ 'address_id' ] ) {
				
				$oSql->where( 'address_id = ?', $aParams[ 'address_id' ] );
				
				if ( $wpdb->get_var( strval( $oSql ) ) ) {
					$aKeys[ 'address_id' ] = $aParams[ 'address_id' ];
					$bUpdate = TRUE;
				}
			
			}
			
			if ( $aParams[ 'object_id' ] && $aParams[ 'objtype_id' ] ) {
				
				$oSql
					->where( 'object_id = ?', $aParams[ 'object_id' ] )
					->where( 'objtype_id = ?', $aParams[ 'objtype_id' ] )
				;
				
				if ( $wpdb->get_var( strval( $oSql ) ) ) {
					$aKeys[ 'object_id' ] = $aParams[ 'object_id' ];
					$aKeys[ 'objtype_id' ] = $aParams[ 'objtype_id' ];
					$bUpdate = TRUE;
				}
				
			}
			
		}
		
		// data
		if ( NULL === $aVals ) {
			
			// use $_POST array for values, minding the prefix
			$aVals = array();
			
			// get list of fields with expected values
			$aFields = array_merge(
				array_diff(
					$aFieldList,
					array( 'country_id', 'continent_id', 'latitude', 'longitude' )
				),
				array( 'province_name', 'province_abbr' )
			);
			
			foreach ( $aFields as $sField ) {
				$sPostKey = $sPrefix . $sField;
				if ( isset( $_POST[ $sPostKey ] ) ) {
					$aVals[ $sField ] = stripslashes( $_POST[ $sPostKey ] );
				}
			}
			
		}
		
		// if specified, resolve province id using name or abbr
		if ( !$aVals[ 'province_id' ] ) {
			
			if ( $aVals[ 'province_name' ] ) {
				$aProvNameLookup = $this->getProvinceNameLookup();
				$aVals[ 'province_id' ] = $aProvNameLookup[ strtolower( $aVals[ 'province_name' ] ) ];
			} elseif ( $aVals[ 'province_abbr' ] ) {
				$aProvAbbrLookup = $this->getProvinceAbbrLookup();
				$aVals[ 'province_id' ] = $aProvAbbrLookup[ strtolower( $aVals[ 'province_abbr' ] ) ];			
			}
			
			unset( $aVals[ 'province_name' ] );
			unset( $aVals[ 'province_abbr' ] );
		}
		
		// geolocation
		if (
			in_array( 'latitude', $this->_aFields ) && 
			in_array( 'longitude', $this->_aFields )
		) {
			
			//// assemble the full address to use in query
			$aMapQuery = array();
			
			// address
			if ( $sAddress = trim( $aVals[ 'street_number' ] . ' ' . $aVals[ 'street_name' ] ) ) {
				$aMapQuery[] = $sAddress;
			} elseif ( $sAddress = trim( $aVals[ 'address_line_1' ] ) ) {
				$aMapQuery[] = $sAddress;
			}
			
			if ( $sAddress2 = trim( $aVals[ 'address_line_2' ] ) ) $aMapQuery[] = $sAddress2;
			if ( $sAddress3 = trim( $aVals[ 'address_line_3' ] ) ) $aMapQuery[] = $sAddress3;
			
			// city
			if ( $sCity = trim( $aVals[ 'city' ] ) ) $aMapQuery[] = $sCity;
			
			// province and country
			if ( $iProvinceId = $aVals[ 'province_id' ] ) {
				list( $sProvince, $sCountry ) = $this->lookupProvinceCountry( $iProvinceId );
				if ( $sProvince = trim( $sProvince ) ) $aMapQuery[] = $sProvince;
				if ( $sCountry = trim( $sCountry ) ) $aMapQuery[] = $sCountry;				
			}
			
			// postal code
			if ( $sPostalCode = trim( $aVals[ 'postal_code' ] ) ) $aMapQuery[] = $sPostalCode;
			
			// assemble
			$sMapQuery = trim( implode( ', ', $aMapQuery ) );
			
			if ( $sMapQuery ) {
				
				$oGmap = $this->getGmap();
				$oRes = $oGmap->query( $sMapQuery );
				if ( $aCoords = $oRes->getCoordinates() ) {
					$aVals[ 'latitude' ] = $aCoords[ 0 ];
					$aVals[ 'longitude' ] = $aCoords[ 1 ];
				}
				
			}
			
		}
		
		if ( $bUpdate ) {
			
			$wpdb->update( $wpdb->geko_location_address, $aVals, $aKeys );
			
		} else {
			
			// insert
			$aVals[ 'object_id' ] = $aParams[ 'object_id' ];
			$aVals[ 'objtype_id' ] = $aParams[ 'objtype_id' ];
			if ( $aParams[ 'subtype_id' ] ) $aVals[ 'subtype_id' ] = $aParams[ 'subtype_id' ];
			
			$wpdb->insert( $wpdb->geko_location_address, $aVals );
		}
		
	}
	
	//
	public function delete( $oPlugin = NULL ) {
		
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare(
			"	DELETE FROM			$wpdb->geko_location_address
				WHERE				( object_id = %d ) AND 
									( objtype_id = %d )
			",
			$this->_iObjectId,
			Geko_Wp_Options_MetaKey::getId( $this->_sObjectType )
		) );
		
	}
	
	
	//// sub crud methods
	
	//
	public function doSubAddAction( $oMainEnt, $aParams, $oPlugin = NULL ) {
		$aParams[ 'object_id' ] = $oMainEnt->getId();
		$this->save( $aParams, 'insert', NULL, $oPlugin );
	}
	
	//
	public function doSubEditAction( $oMainEnt, $oUpdMainEnt, $aParams, $oPlugin = NULL ) {
		$aParams[ 'object_id' ] = $oUpdMainEnt->getId();
		$this->save( $aParams, 'update', NULL, $oPlugin );
	}
	
	//
	public function doSubDelAction( $oMainEnt, $aParams, $oPlugin = NULL ) {
		$this->_iObjectId = $oMainEnt->getId();
		$this->delete( $oPlugin );
	}
	
	
	
	//// helpers
	
	//
	public function populateDb( $bFull = TRUE ) {
		
		$aOnlyProvince = NULL;
		$aOnlyCountry = NULL;
		$aOnlyContinent = NULL;
		
		if ( !$bFull ) {
			
			// populate only with countries defined in Geko_Geography_CountryState
			$aCountries = Geko_Geography_CountryState::get();
			$aOnlyCountry = array_keys( $aCountries );
			
			// populate only with continents applicable to above
			$aOnlyContinent = array();
			foreach ( $aOnlyCountry as $sCountryAbbr ) {
				$aOnlyContinent[] = Geko_Geography_Country::getContinentCodeFromCountry( $sCountryAbbr );
			}
			
		}
		
		$this
			->populateContinentTable( $aOnlyContinent )
			->populateCountryTable( $aOnlyCountry )
			->populateProvinceTable( $aOnlyProvince )
		;
		
		return $this;
	}
	
	//
	public function populateProvinceTable( $aOnly = NULL, $bHasCountry = TRUE ) {

		global $wpdb;
		
		$sTable = $wpdb->geko_location_province;
		$this->resetTable( $sTable );
		
		$aCountryDb = $this->getCountries( TRUE );
		$aCountryAbbrHash = array();
		foreach ( $aCountryDb as $oRow ) {
			$aCountryAbbrHash[ $oRow->country_abbr ] = $oRow->country_id;
		}
		
		// provinces
		$aProvData = array();
		$aCountries = Geko_Geography_CountryState::get();
		foreach ( $aCountries as $sCountryAbbr => $aCountry ) {
			$aProvinces = $aCountry[ 'states' ];
			foreach ( $aProvinces as $sProvAbbr => $sProvName ) {
				
				if (
					( is_array( $aOnly ) ) && 
					( !in_array( $sProvAbbr, $aOnly ) )
				) continue;
				
				$iCountryId = $aCountryAbbrHash[ $sCountryAbbr ];
				
				if ( $bHasCountry && !$iCountryId ) continue;

				$aProvData[] = array(
					'province_name' => $sProvName,
					'province_abbr' => $sProvAbbr,
					'country_id' => $iCountryId
				);
				
			}
		}
		
		foreach ( $aProvData as $aData ) {
			$wpdb->insert(
				$sTable,
				$aData,
				array( '%s', '%s', '%d' )
			);
		}
		
		return $this;
	}
	
	//
	public function populateCountryTable( $aOnly = NULL, $bHasContinent = TRUE ) {
		
		global $wpdb;
		
		$sTable = $wpdb->geko_location_country;
		$this->resetTable( $sTable );
		
		$aContinentDb = $this->getContinents( TRUE );
		$aContinentAbbrHash = array();
		foreach ( $aContinentDb as $oRow ) {
			$aContinentAbbrHash[ $oRow->continent_abbr ] = $oRow->continent_id;
		}
		
		// countries
		$aCountryData = array();
		$aContinents = Geko_Geography_Country::get();
		foreach ( $aContinents as $sContinentAbbr => $aContinent ) {
			$aCountries = $aContinent[ 'countries' ];
			foreach ( $aCountries as $sCountryAbbr => $sCountryName ) {
				
				if (
					( is_array( $aOnly ) ) && 
					( !in_array( $sCountryAbbr, $aOnly ) )
				) continue;
				
				$iContinentId =  $aContinentAbbrHash[ $sContinentAbbr ];
				
				if ( $bHasContinent && !$iContinentId ) continue;
				
				$aCountryData[] = array(
					'country_name' => $sCountryName,
					'country_abbr' => $sCountryAbbr,
					'continent_id' => $iContinentId
				);
				
			}
		}
		
		foreach ( $aCountryData as $aData ) {
			$wpdb->insert(
				$sTable,
				$aData,
				array( '%s', '%s', '%d' )
			);
		}
		
		return $this;
	}
	
	// $aOnly can be an array of continent codes
	public function populateContinentTable( $aOnly = NULL ) {
		
		global $wpdb;
		
		$sTable = $wpdb->geko_location_continent;
		$this->resetTable( $sTable );
		
		// continents
		$aContinentData = array();
		$aContinents = Geko_Geography_Continent::get();
		foreach ( $aContinents as $sContinentAbbr => $sContinentName ) {
			if (
				( NULL === $aOnly ) ||
				(
					( is_array( $aOnly ) ) && 
					( in_array( $sContinentAbbr, $aOnly ) )
				)
			) {
				$aContinentData[] = array(
					'continent_name' => $sContinentName,
					'continent_abbr' => $sContinentAbbr
				);
			}
		}
		
		foreach ( $aContinentData as $aData ) {
			$wpdb->insert(
				$sTable,
				$aData,
				array( '%s', '%s' )
			);
		}
		
		return $this;
	}
	
	
	////// rail functionality
	
	//
	public function layoutEnqueue( $oPlugin = NULL ) {
		wp_enqueue_script( 'geko_wp_location' );
		return parent::layoutEnqueue( $oPlugin );
	}
	
	//
	public function layoutHeadLate() {
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oLayoutParams = $.oGekoLayoutParams;
				
				var mode = oLayoutParams.form.mode;
				
				if ( ( 'add' == mode ) || ( 'edit' == mode ) ) {
					$.gekoWpLocation();
				}
				
			} );
			
		</script>
		<?php
		return parent::layoutHeadLate( $oPlugin );
	}
	
	//
	public function getDetailFields( $oPlugin = NULL ) {
		
		// add some auto fields
		$aDetailFields = array(
			'object_id' => array(
				'auto' => TRUE
			),
			'objtype_id' => array(
				'auto' => TRUE
			),
			'subtype_id' => array(
				'auto' => TRUE
			)
		);
		
		$aFields = $this->getFields( $oPlugin );
		$aFieldLabels = $this->getFieldLabels( $oPlugin );
		
		foreach ( $aFields as $sField ) {
			$aFieldParams = array( 'title' => $aFieldLabels[ $sField ] );
			if ( in_array( $sField, array( 'province_id', 'country_id', 'continent_id' ) ) ) {
				
				$aFieldParams[ 'type' ] = 'select';
				
				$aFieldParams[ 'empty_choice' ] = array(
					'label' => 'Select a ' . $aFieldLabels[ $sField ],
					'atts' => array(
						'class' => 'default'
					)
				);
				
				if ( 'province_id' == $sField ) {
					
					$aChoices = array();
					foreach ( $this->getProvinces() as $oProvince ) {
						$aChoices[ $oProvince->province_id ] = array(
							'label' => $oProvince->province_name,
							'atts' => array(
								'class' => 'country-' . $oProvince->country_id
							)
						);
					}
					
				} elseif ( 'country_id' == $sField ) {
					
					$aChoices = array();
					foreach ( $this->getCountries() as $oCountry ) {
						$aChoices[ $oCountry->country_id ] = array(
							'label' => $oCountry->country_name,
							'atts' => array(
								'class' => 'continent-' . $oCountry->continent_id
							)
						);
					}
					
					$aFieldParams[ 'auto_db' ] = TRUE;
					
				} elseif ( 'continent_id' == $sField ) {
					
					$aChoices = $this->getContinentPair();
					$aFieldParams[ 'auto_db' ] = TRUE;
				}
				
				$aFieldParams[ 'choices' ] = $aChoices;
			}
			$aDetailFields[ $sField ] = $aFieldParams;
		}
				
		return $this->_formatFields( $aDetailFields );
	}


	// plugin hook
	public function getDetailEntity( $oParEnt, $oPlugin = NULL ) {
		
		if ( $oParEnt ) {
			
			$sQueryClass = $this->_sQueryClass;
			
			$sObjectType = $this->getObjectType( $oPlugin );
			$sLocationSubType = $this->getLocationSubType( $oPlugin );
			
			$aParams = array(
				'object_id' => $oParEnt->getId(),
				'objtype_id' => Geko_Wp_Options_MetaKey::getId( $sObjectType ),
				'subtype_id' => Geko_Wp_Options_MetaKey::getId( $sLocationSubType )
			);
			
			$aEntities = new $sQueryClass( $aParams, FALSE );
			
			if ( $aEntities->getTotalRows() == 1 ) {
				return $aEntities->getOne();
			}
		
		}
		
	}
	
	
	//
	public function modifyDetailValues( $aPostVals, $oPlugin = NULL ) {
		
		if ( $oManage = $this->getManage( $oPlugin ) ) {
			$aPostVals[ 'object_id' ] = $oManage->getTargetEntityId();
		}
		
		$sObjectType = $this->getObjectType( $oPlugin );
		$aPostVals[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $sObjectType );

		$sLocationSubType = $this->getLocationSubType( $oPlugin );
		$aPostVals[ 'subtype_id' ] = Geko_Wp_Options_MetaKey::getId( $sLocationSubType );
		
		return $aPostVals;
	}
	
	//
	public function checkDetailValues( $aPostVals, $oPlugin = NULL ) {
		
		if ( $aPostVals[ 'object_id' ] ) return TRUE;
		
		return FALSE;
	}
	
	
	
}


