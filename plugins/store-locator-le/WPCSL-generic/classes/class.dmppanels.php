<?php

if (!class_exists('DMPPanelWPCSLMain')) {
    class DMPPanelWPCSLMain               extends DebugMyPluginPanel {
        function __construct() {
            parent::__construct('WPCSL');
        }
    }
}

if (!class_exists('DMPPanelWPCSLSettings')) {
    class DMPPanelWPCSLSettings        extends DebugMyPluginPanel {
        function __construct() {
            parent::__construct('WPCSL Settings');
        }
    }
}