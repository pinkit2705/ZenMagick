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


define('ZM_TABLE_PRODUCT_ASSOCIATION_TYPES', ZM_DB_PREFIX . 'zm_product_association_types');
define('ZM_TABLE_PRODUCT_ASSOCIATIONS', ZM_DB_PREFIX . 'zm_product_associations');


/**
 * Service class for product associations
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_product_associations
 * @version $Id$
 */
class ProductAssociations extends ZMService {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function ProductAssociations() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if required tables are installed.
     *
     * @return boolean <code>true</code> if installed, <code>false</code> if not.
     */
    function isInstalled() {
        $db = $this->getDB();
        // check for existence
        $results = $db->Execute("show tables");

        $count = 0;
        while (!$results->EOF) {
            $table = array_pop($results->fields);
            if (ZM_TABLE_PRODUCT_ASSOCIATION_TYPES == $table || ZM_TABLE_PRODUCT_ASSOCIATIONS == $table) {
                ++$count;
            }
            $results->MoveNext();
        }

        return 2 == $count;
    }


    /**
     * Set up all product association types as defines.
     *
     * <p>Names are build following these rules:</p>
     * <ol>
     *  <li>replace dash ('-') and space (' ') with underscores ('_')</li>
     *  <li>convert to uppercase</li>
     *  <li>prefix with <code>ZM_PA_</code></li>
     * </ol>
     *
     */
    function prepareAssociationTypes() {
        $db = $this->getDB();
        $sql = "select * from " . ZM_TABLE_PRODUCT_ASSOCIATION_TYPES;
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            $type = $results->fields['association_type'];
            $name = $results->fields['association_type_name'];
            $name = str_replace('-', '_', $name);
            $name = str_replace(' ', '_', $name);
            $name = strtoupper($name);
            $name = 'ZM_PA_'.$name;
            define($name, $type);
            $results->MoveNext();
        }
    }

    /**
     * Get product associations for the given product and type.
     *
     * @param int productId The source product id.
     * @param int type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ProductAssociation</code> instances.
     */
    function getProductAssociationsForProdctId($productId, $type, $all=false) {
    global $zm_products;

        $dateLimit = '';
        if (!$all) {
            $dateLimit = ' and start_date <= now() and (end_date > now() or end_date is NULL) ';
        }
        $db = $this->getDB();
        $sql = "select distinct * from " . ZM_TABLE_PRODUCT_ASSOCIATIONS . "
                where source_product_id = :productId
                and association_type =:type" . $dateLimit . "
                order by sort_order asc";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":type", $type, "integer");

        $associations = array();
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            $associations[] = $this->_newProductAssociation($results->fields);
            $results->MoveNext();
        }

        return $associations;
    }

    /**
     * Get associated products for the given category.
     *
     * @param int categoryId The category.
     * @param int type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ProductAssociation</code> instances.
     */
    function getProductAssociationsForCategoryId($categoryId, $type, $all=false) {
    global $zm_products;

        $associations = array();

        $productIds = $zm_products->getProductIdsForCategoryId($categoryId);
        if (0 == count($productIds)) {
            return $associations;
        }

        $dateLimit = '';
        if (!$all) {
            $dateLimit = ' and start_date <= now() and (end_date > now() or end_date is NULL) ';
        }
        $db = $this->getDB();
        $sql = "select distinct * from " . ZM_TABLE_PRODUCT_ASSOCIATIONS . "
                where source_product_id in (:productIdList)
                and association_type =:type" . $dateLimit . "
                order by sort_order asc";
        $sql = $this->bindValueList($sql, ":productIdList", $productIds, "integer");
        $sql = $db->bindVars($sql, ":type", $type, "integer");

        $results = $db->Execute($sql);
        while (!$results->EOF) {
            $associations[] = $this->_newProductAssociation($results->fields);
            $results->MoveNext();
        }

        return $associations;
    }

    /**
     * Get associated products for the given shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param int type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ProductAssociation</code> instances.
     */
    function getProductAssociationsForShoppingCart(&$shoppingCart, $type, $all=false) {

        $associations = array();
        $productIds = array();
        foreach ($shoppingCart->getItems() as $item) {
            $productIds[] = $item->getId();
        }

        if (0 == count($productIds)) {
            return $associations;
        }

        $dateLimit = '';
        if (!$all) {
            $dateLimit = ' and start_date <= now() and (end_date > now() or end_date is NULL) ';
        }
        $db = $this->getDB();
        $sql = "select distinct * from " . ZM_TABLE_PRODUCT_ASSOCIATIONS . "
                where source_product_id in (:productIdList)
                and association_type =:type" . $dateLimit . "
                order by sort_order asc";
        $sql = $this->bindValueList($sql, ":productIdList", $productIds, "integer");
        $sql = $db->bindVars($sql, ":type", $type, "integer");

        $results = $db->Execute($sql);
        while (!$results->EOF) {
            $associations[] = $this->_newProductAssociation($results->fields);
            $results->MoveNext();
        }

        return $associations;
    }

    /**
     * Create new product association.
     */
    function &_newProductAssociation($fields) {
        $productAssociation = $this->create("ProductAssociation");
        $productAssociation->id_ = $fields['association_id'];
        $productAssociation->type_ = $fields['association_type'];
        $productAssociation->sourceId_ = $fields['source_product_id'];
        $productAssociation->targetId_ = $fields['target_product_id'];
        $productAssociation->startDate_ = $fields['start_date'];
        $productAssociation->endDate_ = zm_is_empty($fields['end_date']) ? null : $fields['end_date'];
        $productAssociation->defaultQty_ = $fields['default_quantity'];
        $productAssociation->sortOrder_ = $fields['sort_order'];
        return $productAssociation;
    }

}

?>
