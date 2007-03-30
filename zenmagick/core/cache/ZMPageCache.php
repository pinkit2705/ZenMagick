<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Page cache.
 *
 * @author mano
 * @package net.radebatz.zenmagick.cache
 * @version $Id$
 */
class ZMPageCache extends ZMCache {
    var $group_;
    var $config_;
    var $cache_;


    /**
     * Default c'tor.
     */
    function ZMPageCache() {
        parent::__construct('page', array(
            'cacheDir' => zm_setting('pageCacheDir'),
            'lifeTime' => zm_setting('pageCacheTTL')
        ));
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMPageCache();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Controls if the current page is cacheable or not.
     *
     * <p>Evaluation is delegated to the configured strategy function (<em>pageCacheStrategyCallback</em>).</p>
     *
     * @return bool <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    function isCacheable() {
        $callback = zm_setting('pageCacheStrategyCallback');
        if (function_exists($callback)) {
            return $callback();
        }

        return false;
    }

    /**
     * Create unique id for the context of the current request.
     *
     * <p>Depending on whether the user is logged in or not, a user 
     * specifc id will be generated, to avoid session leaks.</p>
     *
     * @return string A cache id.
     */
    function getId() {
    global $zm_runtime, $zm_request;

        return $zm_request->getPageName() . '-' . $zm_request->getQueryString() . '-' . $zm_request->getAccountId() . '-' . $zm_runtime->getLanguageId();
    }

}

?>
