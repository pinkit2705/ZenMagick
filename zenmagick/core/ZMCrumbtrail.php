<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Crumbtrail.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMCrumbtrail extends ZMObject {
    var $crumbs_;


    /**
     * Default c'tor.
     */
    function ZMCrumbtrail() {
        parent::__construct();

        $this->crumbs_ = array();

        // always add home
        $this->addCrumb("Home", zm_href(FILENAME_DEFAULT, '', false));
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCrumbtrail();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // get the last crumbs name
    function getLastCrumb() {
        return $this->crumbs_[count($this->crumbs_)-1];
    }

    // return crumb for the given index
    function getCrumb($index) {
        return $this->crumbs_[$index];
    }

    // return all crumbs
    function getCrumbs() {
        return $this->crumbs_;
    }

    // add a single element
    function addCrumb($name, $url = null) {
        array_push($this->crumbs_, $this->create("Crumb", $name, $url));
    }


    // add a complete path; i.e. an array containing category ids
    function addCategoryPath($path) {
    global $zm_categories;
        if (null == $path)
            return;

        // categories
        foreach ($path as $catId) {
            $category = $zm_categories->getCategoryForId($catId);          
            $this->addCrumb($category->getName(), zm_href(FILENAME_DEFAULT, $category->getPath(), false));
        }
    }

    // add manufacturer (by id)
    function addManufacturer($manufacturerId) {
    global $zm_manufacturers;
        if (null == $manufacturerId)
            return;

        $manufacturer = $zm_manufacturers->getManufacturerForId($manufacturerId);
        if (null != $manufacturer) {
            $this->addCrumb($manufacturer->getName(), zm_href(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturerId, false));
        }
    }

    // add product (by id)
    function addProduct($productId) {
    global $zm_request, $zm_products;

        if (null == $productId)
            return;

        $product = $zm_products->getProductForId($productId);
        if (null != $product) {
            $this->addCrumb($product->getName(), zm_product_href($productId, false));
        }
    }

}

?>
