<?php
/************************************************************************
*
* file: CSL-license_class.php
*
* Handle the license management subsystem for WPCSL-Generic.
*
* Process the license keys, validating them against the license server.
*
************************************************************************/

class wpCSL_license__slplus {    

    /**------------------------------------
     ** CONSTRUCTOR
     **/
    function __construct($params) {
        
        // Defaults
        //

        // Set by incoming parameters
        //
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
        
        // Override incoming parameters
        
    }

    /**------------------------------------
     ** method: check_license_key()
     **
     ** Currently only checks for an existing license key (PayPal
     ** transaction ID).
     **/
    function check_license_key($theSKU='', $isa_package=false, $usethis_license='', $force = false) {

        // The forced license needed for plugins with no main license
        // but licensed packages
        //
        if (!$isa_package && ($usethis_license == '')) {
            $usethis_license = get_option($this->prefix . '-license_key','');
        }

        // Don't check to see if the license is valid if there is
        // no supplied license key
        if ($usethis_license == '') {
            return false;
        }

        // The SKU
        //
        if ($theSKU == '') {
            $theSKU = $this->sku;
        }

        // Save the current date and retrieve the last time we checked
        // with the server.
        if (!$isa_package) {
            $last_lookup = get_option($this->prefix.'-last_lookup');
            update_option($this->prefix.'-last_lookup', time());
        } else {
            $last_lookup = get_option($this->prefix.'-'.$theSKU.'-last_lookup');
            update_option($this->prefix.'-'.$theSKU.'-last_lookup', time());
        }

        // Only check every 3 days.
        $date_differential = (3 * 24 * 60 * 60);

        if (!$force && ($last_lookup + $date_differential) > time() ) {
            return $this->AmIEnabled($isa_package, $theSKU);
        }

        // HTTP Handler is not set fail the license check
        //
        if (!isset($this->http_handler)) { return false; }

        // Build our query string from the options provided
        //  
        $query_string = http_build_query(
            array(
                'id' => $usethis_license,
                'siteurl' => get_option('siteurl'),
                'sku' => $theSKU,
                'checkpackage' => $isa_package ? 'true' : 'false',
                'advanced' => 'true'
            )
        );
        
        // Places we check the license
        //
        $csl_urls = array(
            'http://www.charlestonsw.com/paypal/valid_transaction.php?',
            );

        // Check each server until all fail or ONE passes
        //
        $response = null;
        foreach ($csl_urls as $csl_url) {
            $result = $this->http_handler->request(
                            $csl_url . $query_string,
                            array('timeout' => 10)
                            );
            
            if ($this->parent->http_result_is_ok($result) ) {
                $response = json_decode($result['body']);
            }

            // If response is still a bool... and false... we have a problem...
            if (is_null($response) || !is_object($response)) {
                continue;
            }
            
            // If we get a true response record it in the DB and exit
            //
            if ($response->result) {
                
                //.............
                // Licensed
                // main product
                if (!$isa_package) { 
                    update_option($this->prefix.'-purchased',true); 
            
                // add on package
                } else {
                    update_option($this->prefix.'-'.$theSKU.'-isenabled',true);
                    
                    // Local version info for this package is empty, set it
                    //
                    if (get_option($this->prefix.'-'.$theSKU.'-version') == '') {                        
                            update_option($this->prefix.'-'.$theSKU.'-version',$response->latest_version);
                            update_option($this->prefix.'-'.$theSKU.'-version-numeric',$response->latest_version_numeric);
                            
                    // Local version is not empty,                         
                    // Make sure we never downgrade the user's version
                    //
                    } else if ($response->effective_version_numeric > (int)get_option($this->prefix.'-'.$theSKU.'-version-numeric')) {
                            update_option($this->prefix.'-'.$theSKU.'-version',$response->effective_version);
                            update_option($this->prefix.'-'.$theSKU.'-version-numeric',$response->effective_version_numeric);
                    }             
                }

                update_option($this->prefix.'-'.$theSKU.'-latest-version',$response->latest_version);
                update_option($this->prefix.'-'.$theSKU.'-latest-version-numeric',$response->latest_version_numeric);
                return true;
            }
        }

        // Handle possible server disconnect
        if (is_null($response)) {
            return $this->AmIEnabled($isa_package, $theSKU);
        }

        //.............
        // Not licensed
        return false;
    }

    /**------------------------------------
     ** method: AmIEnabled
     ** Parameters: $isa_package = is it a package or the main product
     **             $theSKU = the sku of the product
     ** Returns: True if enabled/purchased, false if not
     **/
    function AmIEnabled($isa_package, $theSKU) {
        if (!$isa_package) {
                return get_option($this->prefix.'-purchased',false);

                // add on package
            } else {
                return get_option($this->prefix.'-'.$theSKU.'-isenabled',false);
            }
    }

    /**------------------------------------
     ** method: check_product_key()
     **
     **/
    function check_product_key() {
        
        // If main product is not licensed (denoted by has_package=true)
        // and we are not checking a package, pretend we are licensed
        // and get out of here.
        //
        if ($this->has_packages) {
            return true;
        }
        
        if (get_option($this->prefix.'-purchased') != '1') {
            if (get_option($this->prefix.'-license_key') != '') {
                update_option($this->prefix.'-purchased', $this->check_license_key());
            }

            if (get_option($this->prefix.'-purchased') != '1') {
                if (isset($this->notifications)) {
                    $this->notifications->add_notice(
                        2,
                        __("You have not provided a valid license key for this plugin. " .
                            "Until you do so, it will only display content for Admin users." 
                            ,WPCSL__slplus__VERSION
                            ),
                        "options-general.php?page={$this->prefix}-options#product_settings"
                    );
                }
            }
        }

        return (isset($notices)) ? $notices : false;
    }

    /**------------------------------------
     ** method: initialize_options()
     **
     **/
    function initialize_options() {
        register_setting($this->prefix.'-settings', $this->prefix.'-license_key');
        register_setting($this->prefix.'-settings', $this->prefix.'-purchased');
        
        if (($this->has_packages) && is_array($this->packages))  {
            foreach ($this->packages as $aPackage) {
                $aPackage->initialize_options_for_admin();
            }
        }            
    }
    
    /**------------------------------------
     ** method: add_licensed_package()
     **
     ** Add a package object to the license object.
     **
     ** Packages are components that have their own license keys to be
     ** activated, but are always related to a parent product with a valid
     ** license.
     **
     **/
    function add_licensed_package($params) {
        
        // If we don't have a package name or SKU get outta here
        //
        if (!isset($params['name']) || !isset($params['sku'])) return;

        // Setup the new package only if it was not setup before
        //
        if (!isset($this->packages[$params['name']])) {
            $this->packages[$params['name']] = new wpCSL_license_package__slplus(
                array_merge(
                    $params,
                    array(
                        'prefix' => $this->prefix,
                        'parent' => $this
                        )
                    )
            );
        } 
   }
    
}


/****************************************************************************
 **
 ** class: wpCSL_license_package__slplus
 **
 **/
class wpCSL_license_package__slplus {

    public $active_version = 0;
    public $force_enabled = false;
    
    /**------------------------------------
     **/
    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
        
        // Register these settings
        //
        $this->enabled_option_name = $this->prefix.'-'.$this->sku.'-isenabled';
        $this->lk_option_name      = $this->prefix.'-'.$this->sku.'-lk';
         
        // If the isenabled flag is not explicitly passed in,
        // set this package to the pre-saved enabled/disabled setting from wp_options
        // which will return false if never set before
        //
        $this->isenabled = ($this->force_enabled || get_option($this->enabled_option_name));        
        
        // Set our license key property
        //
        $this->license_key = get_option($this->lk_option_name);
        
        // Set our active version (what we are licensed for)
        //
        $this->active_version =  (isset($this->force_version)?$this->force_version:get_option($this->prefix.'-'.$this->sku.'-latest-version-numeric'));
    }
    
    
    /**------------------------------------
     ** method: initialize_options_for_admin
     **
     ** Initialize the admin option settings.
     **/
    function initialize_options_for_admin() {
        register_setting($this->prefix.'-settings', $this->lk_option_name);
    }
    
    function isenabled_after_forcing_recheck() {
        // Now attempt to license ourselves, make sure we license as
        // siblings (second param) in order to properly set all of the
        // required settings.
        if (!$this->isenabled) {

            // License is OK - mark it as such
            //
            $this->isenabled = $this->parent->check_license_key($this->sku, true, get_option($this->lk_option_name));
            update_option($this->enabled_option_name,$this->isenabled);
            $this->active_version =  get_option($this->prefix.'-'.$this->sku.'-latest-version-numeric');
        }

        // Attempt to register the parent if we have one
        $this->parent->check_license_key($this->sku, true);

        return $this->isenabled;
    }
}
