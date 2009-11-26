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
 * Example plugin to illustrate a few key points of the ZenMagick plugin architecture.
 *
 * @package org.zenmagick.plugins.examplePlugin
 * @author DerManoMann
 * @version $Id: sample_plugin.php 2488 2009-07-24 08:54:22Z dermanomann $
 */
class ZMPluginExamplePlugin extends Plugin implements ZMRequestHandler {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ZenMagick Example Plugin', 'This is the ZenMagick Example Plugin');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Plugin true/false', 'rq1key1', 'true', 'Select true or false', 
            'widget@BooleanFormWidget#name=rq1key1&default=false&label=Select true or false&style=radio');
        $this->addConfigValue('Plugin text config', 'rq1key2', 'doh', 'Some text');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        /*
         * this is the place to do init stuff other than just setting up the infrastructure
         */
      
        // set up as event subscriber
        ZMEvents::instance()->attach($this);

        // add admin page
        $this->addMenuItem('ExamplePluginAdmin', zm_l10n_get('Example Plugin Admin Page'), 'ExamplePluginAdmin');
    }

    /**
     * {@inheritDoc}
     */
    public function initRequest($request) {
        //var_dump($request);
    }

    /**
     * As zco subscriber all methods that match a zen-cart zco event (see <code>ZMEvents</code> for more details)
     * will be called (back) automatically when subscribed...
     */
    public function onNotifyHeaderStartIndex($args) {
        echo "Start of Zen Cart's index page event callback in " . $this->getName() . " ...<br>";
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];

        if ('login' == ZMRequest::instance()->getRequestId()) {
            $args['contents'] = preg_replace('/<\/h1>/', ' (modified by ' . $this->getName() . ')</h1>', $contents, 1);
        }

        return $args;
    }

}

?>
