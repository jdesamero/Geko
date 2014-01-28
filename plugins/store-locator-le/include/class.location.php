<?php
/**
 * Store Locator Plus location interface and management class.
 *
 * Make a location an in-memory object and handle persistence via data I/O to the MySQL tables.
 *
 * @package StoreLocatorPlus\Location
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 *
 * @property int $id
 * @property string $store          the store name
 * @property string $address
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $country
 * @property string $latitude
 * @property string $longitude
 * @property string $tags
 * @property string $description
 * @property string $email
 * @property string $url
 * @property string $hours
 * @property string $phone
 * @property string $fax
 * @property string $image
 * @property boolean $private
 * @property string $neat_title
 * @property int $linked_postid
 * @property string $pages_url
 * @property boolean $pages_on
 * @property string $option_value
 * @property datetime $lastupdated
 * @property mixed[] $settings - the deserialized option_value field
 *
 * @property mixed[] $pageData - the related store_page custom post type properties.
 * @property-read string $pageType - the custom WordPress page type of locations
 * @property-read string $pageDefaultStatus - the default page status
 *
 * @property-read string $dbFieldPrefix - the database field prefix for locations
 * @property-read string[] $dbFields - an array of properties that are in the db table
 *
 * @property SLPlus $plugin - the parent plugin object
 */
class SLPlus_Location {

    //-------------------------------------------------
    // Properties
    //-------------------------------------------------

    // Our database fields
    //

    /**
     * Unique location ID.
     * 
     * @var int $id
     */
    private $id;
    private $store;
    private $address;
    private $address2;
    private $city;
    private $state;
    private $zip;
    private $country;
    private $latitude;
    private $longitude;
    private $tags;
    private $description;
    private $email;
    private $url;
    private $hours;
    private $phone;
    private $fax;
    private $image;
    private $private;
    private $neat_title;
    private $linked_postid;
    private $pages_url;
    private $pages_on;
    private $option_value;
    private $lastupdated;

    // The database map
    //
    private $dbFields = array(
            'id',
            'store',
            'address',
            'address2',
            'city',
            'state',
            'zip',
            'country',
            'latitude',
            'longitude',
            'tags',
            'description',
            'email',
            'url',
            'hours',
            'phone',
            'fax',
            'image',
            'private',
            'neat_title',
            'linked_postid',
            'pages_url',
            'pages_on',
            'option_value',
            'lastupdated'
        );

    /**
     * The deserialized option_value field. This can be augmented by multiple add-on packs.
     *
     * Tagalong adds:
     *  array[] ['store_categories']
     *       int[] ['stores']
     *
     * @var mixed[] $attributes
     */
    private $attributes;

    /**
     * The related store_page custom post type properties.
     *
     * WordPress Standard Custom Post Type Features:
     *   int    ['ID']          - the WordPress page ID
     *   string ['post_type']   - always set to this.PageType
     *   string ['post_status'] - current post status, 'draft', 'published'
     *   string ['post_title']  - the title for the page
     *   string ['post_content']- the page content, defaults to blank
     *
     * Store Pages adds:
     *    post_content attribute is loaded with auto-generated HTML content
     *
     * Tagalong adds:
     *    mixed[] ['tax_input'] - the custom taxonomy values for this location
     *
     * @var mixed[] $pageData
     */
    private $pageData;

    // Assistants for this class
    //
    private $dbFieldPrefix      = 'sl_';
    private $pageType           = 'store_page';
    private $pageDefaultStatus  = 'draft';
    private $plugin;

    //-------------------------------------------------
    // Methods
    //-------------------------------------------------

    /**
     * Initialize a new location
     *
     * @param mixed[] $params - a named array of the plugin options.
     */
    public function __construct($params) {
        foreach ($params as $property=>$value) {
            $this->$property = $value;
        }
    }

    /**
     * Create or update the custom store_page page type for this location.
     *
     * @return int $linke_postid - return the page ID linked to this location.
     */
    public function crupdate_Page() {
        $this->plugin->debugMP('slp.main','msg','location.crupdate_Page()','',null,null,true);
        
        $crupdateOK = false;

        // Setup the page properties.
        //
        $this->set_PageData();

        // Update an existing page.
        //
        if ($this->linked_postid > 0) {
            $touched_pageID = wp_update_post($this->pageData);
            $crupdateOK = ($touched_pageID > 0);
            $this->plugin->debugMP('slp.main','pr','updated existing page '.$touched_pageID,$this->pageData,null,null,true);


        // Create a new page.
        } else {
            $touched_pageID = wp_insert_post($this->pageData, true);
            $crupdateOK = !is_wp_error($touched_pageID);
            $this->plugin->debugMP('slp.main','msg','location.crupdate_Page()','insert post',__FILE__,__LINE__);
        }

        // Ok - we are good...
        //
        if ($crupdateOK) {

            // Debugging Output (if flag turned on)
            //
            $header = 
                    'location.crupdate_Page() page # ' . 
                    $touched_pageID . 
                    (($touched_pageID != $this->linked_postid) ? ' Created':' Updated')
                    ;
           $this->plugin->debugMP('slp.main','pr',$header,$this->pageData,__FILE__,__LINE__);

            // If we created a page or changed the page ID,
            // set it in our location property and make it
            // persistent.
            //
            if ($touched_pageID != $this->linked_postid) {
                $this->linked_postid = $touched_pageID;
                $this->MakePersistent();
                $this->plugin->debugMP('slp.main','msg','Make new linked post ID ' . $this->linked_postid . ' persistent.');
            }


        // We got an error... oh shit...
        //
        } else {
            $this->plugin->notifications->add_notice('error','Could not create or update the custom page for this location.');
            $this->plugin->debugMP('slp.main','pr','location.crupdate_Page() failed',(is_object($touched_pageID)?$touched_pageID->get_error_messages():''));
        }


        return $this->linked_postid;
    }

    /**
     * Fetch a location property from the valid object properties list.
     *
     * $currentLocation = new SLPlus_Location();
     * print $currentLocation->id;
     * 
     * @param mixed $property - which property to set.
     * @return null
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }

    public function debugProperties() {
        $output='currentLocation.Properties are:<br/>';
        foreach ($this->dbFields as $property) {
            $output .= $property . ' = ' . $this->$property . '<br/>';
        }
        $this->plugin->debugMP('slp.main','msg',$output);
    }

    /**
     * Return the values for each of the persistent properties of this location.
     *
     * @param string $property name of the persistent property to get, defaults to 'all' = array of all properties
     * @return mixed the value the property or a named array of all properties (default)
     */
    public function get_PersistentProperty($property='all') {
        $persistentData = array_reduce($this->dbFields,array($this,'mapPropertyToField'));
        return (($property==='all')?$persistentData:(isset($persistenData[$property])?$persistenData[$property]:null));
    }

    /**
     * Setup the data for the current page, run through augmentation filters.
     *
     * This method applies the slp_location_page_attributes filter.
     *
     * Using that filter allows other parts of the system to change or augment
     * the data before we create or update the page in the WP database.
     *
     * @return mixed[] WordPress custom post type property array
     */
    public function set_PageData() {

        // We have an existing page
        // should feed a wp_update_post not wp_insert_post
        //
        if ($this->linked_postid > 0) {
            $this->pageData = array(
                'ID'            => $this->linked_postid,
            );
           $this->plugin->debugMP('slp.main',
                   'msg',
                   'location.set_PageData()',
                   ' pre-existing post ID ' . $this->linked_postid . '<br/>' .
                   '   this pageType ' . $this->pageType . '<br/>' .
                   '   this pageDefaultStatus ' . $this->pageDefaultStatus . '<br/>' .
                   '   this store ' . $this->store
                   ,__FILE__,__LINE__
                   );

        // No page yet, default please.
        //
        } else {
            $this->pageData = array(
                'ID'            => '',
                'post_type'     => $this->pageType,
                'post_status'   => $this->pageDefaultStatus,
                'post_title'    => $this->store,
                'post_content'  => ''
            );
           $this->plugin->debugMP('slp.main',
                   'msg',
                   'location.set_PageData()',
                   ' new post ID ' . $this->linked_postid . '<br/>' .
                   '   this pageType ' . $this->pageType . '<br/>' .
                   '   this pageDefaultStatus ' . $this->pageDefaultStatus . '<br/>' .
                   '   this store ' . $this->store
                   ,__FILE__,__LINE__
                   );
        }

        // Apply our location page data filters.
        // This is what allows add-ons to tweak page data.
        //
        $this->pageData = apply_filters('slp_location_page_attributes', $this->pageData);

        // Debugging
        $this->plugin->debugMP('slp.main','pr','location.set_PageData() post-filter',$this->pageData,__FILE__,__LINE__);

        return $this->pageData;
    }

    /**
     * Make the location data persistent.
     *
     * Write the data to the locations table in WordPress.
     */
    public function MakePersistent() {
        $dataToWrite = array_reduce($this->dbFields,array($this,'mapPropertyToField'));

        // Location is set, update it.
        //
        if ($this->id > 0) {
            unset($dataToWrite['sl_id']);
            if(!$this->plugin->db->update($this->plugin->database['table_ns'],$dataToWrite,array('sl_id' => $this->id))) {
                $this->plugin->notifications->add_notice(
                        'warning',
                        sprintf(__('Could not update %s, location id %d.','csa-slplus'),$this->store,$this->id)
                        );
            }

        // No location, add it.
        //
        } else {
            if (!$this->plugin->db->insert($this->plugin->database['table_ns'],$dataToWrite)) {
                $this->plugin->notifications->add_notice(
                        'warning',
                        sprintf(__('Could not add %s as a new location','csa-slplus'),$this->store)
                        );
            }
            
            // Set our location ID to be the newly inserted record!
            //
            $this->id = $this->plugin->db->insert_id;
        }
    }

    /**
     * Return a named array that sets key = db field name, value = location property
     *
     * @param string $property - name of the location property
     * @return mixed[] - key = string of db field name, value = location property value
     */
    private function mapPropertyToField($result, $property) {
        $result[$this->dbFieldPrefix.$property]=$this->$property;
        return $result;
    }

    /**
     * Set a location property in the valid object properties list to the given value.
     *
     * $currentLocation = new SLPlus_Location();
     * $currentLocation->store = 'My Place';
     *
     * @param mixed $property
     * @param mixed $value
     * @return \SLPlus_Location
     */
    public function __set($property,$value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
        return $this;
    }

    /**
     * Set our location properties via a named array containing the data.
     *
     * Used to set our properties based on the MySQL SQL fetch to ARRAY_A method.
     *
     * Assumes the properties all start with 'sl_';
     *
     * @param type $locationData
     * @return boolean
     */
    public function set_PropertiesViaArray($locationData) {

        // If we have an array, assume we are on the right track...
        if (is_array($locationData)) {

            // Go through the named array and extract our properties.
            //
            foreach ($locationData as $field => $value) {

                // Get rid of the leading field prefix (usually sl_)
                //
                $property = str_replace($this->dbFieldPrefix,'',$field);

                // Set our property value
                //
                $this->$property = $value;
            }

            // Deserialize the option_value field
            //
            $this->attributes = maybe_unserialize($this->option_value);

            // Debugging Output
            //
            $this->plugin->debugMP('slp.main','pr','location.set_PropertiesViaArray()',$locationData,__FILE__,__LINE__);

            return true;
        }

        // Debugging Output
        //
        $this->plugin->debugMP('slp.main','msg','location.set_PropertiesViaArray()','ERROR: location data not in array format.',__FILE__,__LINE__);
        return false;
    }


    /**
     * Load a location from the database.
     *
     * Only re-reads database if the location ID has changed.
     *
     * @param int $locationID - ID of location to be loaded
     * @return SLPlus_Location $this - the location object
     */
    public function set_PropertiesViaDB($locationID) {
        // Our current ID does not match, load new location data from DB
        //
        if ($this->id != $locationID) {
            $this->set_PropertiesViaArray(
                    $this->plugin->db->get_row(
                        $this->plugin->db->prepare(
                                $this->plugin->database['query']['selectall'] .
                                $this->plugin->database['query']['whereslid'],
                                $locationID
                        ),
                        ARRAY_A
                    )
                );
        }
        return $this;
    }
}
