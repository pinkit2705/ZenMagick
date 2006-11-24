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
 * Features.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMFeatures {
    // db access
    var $db_;
    var $features_;
    var $productFeatures_;
    var $featureTypes_;


    // create new instance
    function ZMFeatures() {
    global $zm_runtime;
        $this->db_ = $zm_runtime->getDB();
        $this->features_ = array();
        $this->productFeatures_ = array();
        $this->featureTypes_ = null;
    }

    // create new instance
    function __construct() {
        $this->ZMFeatures();
    }

    function __destruct() {
    }


    function _loadFeatures() {
    global $zm_request;
        if (null != $this->features_)
            return;

        $sql = "select f.feature_id, f.feature_type_id, f.language_id, f.feature_name, f.feature_description,
                f.hidden
                from " .ZM_TABLE_FEATURES . " f
                where f.language_id = :languageId";
        $sql = $this->db_->bindVars($sql, ':languageId', $zm_request->getLanguageId(), 'integer');

        $results = $this->db_->Execute($sql);

        $this->features_ = array();
        while (!$results->EOF) {
            $feature = new ZMFeature();
            $feature->id_ = $results->fields['feature_id'];
            $feature->type_ = $results->fields['feature_type_id'];
            $feature->name_ = $results->fields['feature_name'];
            $feature->description_ = $results->fields['feature_description'];
            $feature->hidden_ = $results->fields['hidden'];
            $this->features_[$feature->id_] = $feature;
            $results->MoveNext();
        }
        ksort($this->features_);

        return;
    }

    function _loadFeatureTypes() {
        if (null != $this->featureTypes_)
            return;

        $query = "select feature_type_id, feature_type
                  from " . ZM_TABLE_FEATURE_TYPES;

        $results = $this->db_->Execute($query);

        $this->featureTypes_ = array();
        while (!$results->EOF) {
            $this->featureTypes_[$results->fields['feature_type_id']] = $results->fields['feature_type'];
            $results->MoveNext();
        }

        return;
    }

    // get a list of all features
    function getFeatureList() {
        $this->_loadFeatures();

        return $this->features_;
    }

    // get feature types
    function getFeatureTypes() {
        $this->_loadFeatureTypes();

        $types = array();
        foreach ($this->featureTypes_ as $id => $name) {
            $types[$id] = new ZMIdNamePair($id, $name);
        }

        return $types;
    }

    // get feature type for id
    function getFeatureTypeForId($id) {
        $this->_loadFeatureTypes();

        $type = null;
        if (array_key_exists($id, $this->featureTypes_)) {
            $type = new ZMIdNamePair($id, $this->featureTypes_[$id]);
        }

        return $type;
    }

    // get feature for id
    function getFeatureForId($id) {
        $this->_loadFeatures();

        $feature = null;
        if (array_key_exists($id, $this->features_)) {
            $feature = $this->features_[$id];
        }

        return $feature;
    }

    // remove feature for the given id
    function removeFeatureForId($featureId) {
        $sql = "delete from " . ZM_TABLE_FEATURES . "
                where feature_id = :featureId";
        $sql = $this->db_->bindVars($sql, ':featureId', $featureId, 'integer');

        $this->db_->Execute($sql);
        $this->featureDescriptions_ = null;
        return true;
    }

    // add feature
    function addFeature($type, $languageId, $name, $description, $hidden=false) {
        $sql = "insert into " . ZM_TABLE_FEATURES . "
                (feature_type_id, language_id, feature_name, feature_description, hidden)
                values (:type, :languageId, :name, :description, :hidden)";
        $sql = $this->db_->bindVars($sql, ':type', $type, 'integer');
        $sql = $this->db_->bindVars($sql, ':languageId', $languageId, 'integer');
        $sql = $this->db_->bindVars($sql, ':name', $name, 'string');
        $sql = $this->db_->bindVars($sql, ':description', $description, 'string');
        $sql = $this->db_->bindVars($sql, ':hidden', $hidden, 'integer');

        $this->db_->Execute($sql);
        return true;
    }

    // update feature
    function updateFeature($featureId, $languageId, $name, $description, $hidden) {
        $sql = "update " . ZM_TABLE_FEATURES . "
                set feature_name = :name,
                    feature_description = :description,
                    hidden = :hidden
                where feature_id = :featureId
                and language_id = :languageId";
        $sql = $this->db_->bindVars($sql, ':name', $name, 'string');
        $sql = $this->db_->bindVars($sql, ':description', $description, 'string');
        $sql = $this->db_->bindVars($sql, ':hidden', $hidden, 'integer');
        $sql = $this->db_->bindVars($sql, ':featureId', $featureId, 'integer');
        $sql = $this->db_->bindVars($sql, ':languageId', $languageId, 'integer');

        $this->db_->Execute($sql);
        // invalidate cache
        $this->features_ = null;
        return true;
    }

    // add feature for product
    function addFeatureForProduct($productId, $featureId, $value, $index=1) {
        $sql = "insert into " . ZM_TABLE_PRODUCT_FEATURES . "
                (product_id, feature_id, feature_index_id, feature_value)
                values (:productId, :featureId, :index, :value)";
        $sql = $this->db_->bindVars($sql, ':productId', $productId, 'integer');
        $sql = $this->db_->bindVars($sql, ':featureId', $featureId, 'integer');
        $sql = $this->db_->bindVars($sql, ':index', $index, 'integer');
        $sql = $this->db_->bindVars($sql, ':value', $value, 'string');

        $this->db_->Execute($sql);
        return true;
    }

    // remove feature for product
    function removeFeatureForProduct($productId, $featureId, $index=null) {
        $sql = "delete from " . ZM_TABLE_PRODUCT_FEATURES . "
                where product_id = :productId
                and feature_id = :featureId";
        $sql = $this->db_->bindVars($sql, ':productId', $productId, 'integer');
        $sql = $this->db_->bindVars($sql, ':featureId', $productId, 'integer');
        if (null != $index) {
            $sql .= " and feature_index_id = " . $index;
        }
        $this->db_->Execute($sql);
        return true;
    }

    // update feature for product
    function updateFeatureForProduct($productId, $featureId, $oldIndex, $value, $index) {
    //echo $productId.",".$featureId.",".$value.",".$index;
        $sql = "update " . ZM_TABLE_PRODUCT_FEATURES . "
                set feature_value = :value,
                    feature_index_id = :index
                where product_id = :productId
                and feature_id = :featureId
                and feature_index_id = :oldIndex";
        $sql = $this->db_->bindVars($sql, ':value', $value, 'string');
        $sql = $this->db_->bindVars($sql, ':index', $index, 'integer');
        $sql = $this->db_->bindVars($sql, ':productId', $productId, 'integer');
        $sql = $this->db_->bindVars($sql, ':featureId', $featureId, 'integer');
        $sql = $this->db_->bindVars($sql, ':oldIndex', $oldIndex, 'integer');

        $this->db_->Execute($sql);
        // invalidate cache
        unset($this->productFeatures_[$productId]);
        return true;
    }

    // get features for id
    function getFeaturesForProductId($productId) {
        if (array_key_exists($productId, $this->productFeatures_))
            return $this->productFeatures_[$productId];

        $sql = "select f.product_feature_id, f.feature_id, f.feature_index_id, f.feature_value
                  from " . ZM_TABLE_PRODUCT_FEATURES . " f
                  where f.product_id = :productId
                  order by f.feature_id, f.feature_index_id";
        $sql = $this->db_->bindVars($sql, ':productId', $productId, 'integer');
        $results = $this->db_->Execute($sql);

        $this->_loadFeatures();

        $features = array();
        $feature = null;
        $currentFeatureId = -1;
        while (!$results->EOF) {
            if ($results->fields['feature_id'] != $currentFeatureId) {
                if (null != $feature) {
                    $features[$feature->getName()] = $feature;
                }
                $feature = new ZMFeature();
            }
            $feature->id_ = $results->fields['feature_id'];
            $tmp = $this->features_[$feature->id_];
            $feature->type_ = $tmp->type_;
            $feature->name_ = $tmp->name_;
            $feature->description_ = $tmp->description_;
            $feature->hidden_ = $tmp->hidden_;
            $feature->values_[$results->fields['feature_index_id']] =  $results->fields['feature_value'];

            $currentFeatureId = $results->fields['feature_id'];
            $results->MoveNext();
        }
        if (null != $feature) {
            // last one
            $features[$feature->getName()] = $feature;
        }

        $this->productFeatures_[$productId] = $features;
        return $features;
    }

}

?>
