<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php



/**
 * Plugin adding a Google Maps based store locator.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_google_store_locator
 * @version $Id$
 */
class zm_google_store_locator extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Store Locator', 'Google Maps Store Locator.', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Google Maps storefront key', 'storeKey', '', 'Your Google Maps key for the storefront');
        $this->addConfigValue('Google Maps admin key', 'adminKey', '', 'Your Google Maps key for the admin page');
        $this->addConfigValue('Store Location', 'location', '37.4419, -122.1419', 'The store location (Lat,Lng)');
        $this->addConfigValue('Zoom Level', 'zoom', '13', 'The initial zoom level');
        $this->addConfigValue('Marker Text', 'marker_text', ZMSettings::get('storeName'), 'Optional text for the store marker',
            'widget@TextAreaFormWidget#name=marker_text');
        $this->addConfigValue('Add Controls', 'controls', 'true', 'Enable/disable map controls',
            'widget@BooleanFormWidget#name=controls&default=true&label=Enable controls&style=checkbox');
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        $this->addMenuItem('store_locator', zm_l10n_get('Store Locator'), 'zm_store_locator_admin');
        if (function_exists('zm_pretty_links_set_mapping')) {
            //XXX: get rid of?
            zm_pretty_links_set_mapping('store_locator');
        }
        // subscribe to events to set the JS onload event
        ZMEvents::instance()->attach($this);
    }

    /**
     * Update theme once init is done.
     */
    function onZMInitDone($args) {
        // TODO: replace with code injection at bottom of page
        Runtime::getTheme()->getThemeInfo()->setPageEventHandler('onload', 'store_locator', "load_locator_map()");
    }

}

?>
