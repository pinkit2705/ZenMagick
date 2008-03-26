<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

define('ZM_CACHE_BASE_DIR', DIR_FS_SQL_CACHE.'/zenmagick/');


/**
 * File caching.
 *
 * <p>File caching using <code>PEAR:Cache_Lite</code>.</p>
 *
 * @author mano
 * @package org.zenmagick.cache.provider
 * @version $Id$
 */
class ZMFileCache extends ZMObject implements ZMCache {
    var $group_;
    var $available_;
    var $cache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
    public function init($group, $config) {
        // set these, all others are passed through 'as is'
        $config['automaticSerialization'] = true;
        $config['cacheDir'] = ZM_CACHE_BASE_DIR.$group.'/';
        $this->group_ = $group;
        $this->available_ = $this->_ensureCacheDir($config['cacheDir']);
        $this->cache_ = new Cache_Lite($config);
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() { return $this->available_; }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        return $this->cache_->clean($this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id) {
        return $this->cache_->get($id, $this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id) {
        return $this->cache_->save($data, $id, $this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified() {
        return $this->cache_->lastModified();
    }
    

    /**
     * Ensure the given dir exists and is writeable.
     *
     * @param string dir The cache dir.
     * @return boolean <code>true</code> if the cache dir is usable, <code>false</code> if not.
     */
    private function _ensureCacheDir($dir) {
        zm_mkdir($dir, 755);
        return file_exists($dir) && is_writeable($dir);
    }

}

?>
