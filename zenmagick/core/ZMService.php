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


/**
 * Base service class.
 *
 * <p>A service can be something like a <code>DAO</code>, providing access
 * to database data or any other sort of service that a controller or request handler
 * might want to use. Examples for services that are not <code>DAO</code> are {@link org.zenmagick.service.ZMMessages ZMMessages}
 * or {@link org.zenmagick.service.ZMPlugins ZMPlugins}.</p>
 *
 * @author mano
 * @package org.zenmagick
 * @version $Id$
 * @deprecated Use methods from <code>ZMDbUtils</code> instead.
 */
class ZMService extends ZMObject {

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
     * Bind a list of values to a given SQL query.
     *
     * <p>Converts the values in the given array into a comma separated list of the specified type.</p>
     *
     * @param string sql The sql query to work on.
     * @param string bindName The name to bind the list to.
     * @param array values An array of values.
     * @param string type The value type; default is 'string'
     * @return string The sql with <code>$bindName</code> replaced with a properly formatted value list.
     */
    public static function bindValueList($sql, $bindName, $values, $type='string') {
        return ZMDbUtils::bindValueList($sql, $bindName, $values, $type);
    }

    /**
     * Bind object to a given SQL query.
     *
     * <p>This is based on introspection/reflection on the given object and the available
     * <code>getXXX()</code> or <code>isXXX()</code> methods.</p>
     * <p>SQL label must follow the listed convenctions:</p>
     * <ul>
     *  <li>label start with the prefix '<code>:</code>'</li>
     *  <li>label match the objetcs <code>getXXX()</code> method excl the <code>get</code> prefix</li>
     *  <li>label are suffixed with the data type with a semicolon '<code>;</code>' as separator</li>
     * </ul>
     *
     * <p>Examples:</p>
     * <ul>
     *  <li><code>:firstName;string</code> - maps to the <code>getFirstName()</code> method; data type string</li>
     *  <li><code>:dob;date</code> - maps to the <code>getDob()</code> method; data type date</li>
     *  <li><code>:newsletterSubscriber;integer</code> - maps to the <code>isNewsletterSubscriber()</code> method; data type integer</li>
     * </ul>
     *
     * @param string sql The sql to work on.
     * @param mixed obj The data object instance.
     * @param boolean isRead Optional flag to indicate read or write; default is <code>true</code> for reads.
     * @return string The updated SQL query.
     */
    public static function bindObject($sql, $obj, $isRead=true) {
        return ZMDbUtils::bindObject($sql, $obj, $isRead);
    }

    /**
     * Bind custom fields to a given sql query.
     * 
     * @param string sql The sql to work on.
     * @param mixed obj The data object instance.
     * @param string table The table name.
     * @param string valueMarker The string to be replaced with custom values; default is <em>:customFields</em>.
     * @return string The updated SQL query.
     */
    public static function bindCustomFields($sql, $obj, $table, $valueMarker=':customFields') {
        return ZMDbUtils::bindCustomFields($sql, $obj, $table, $valueMarker);
    }

    /**
     * Get the setting name for custom fields for the given table name.
     *
     * @param string table The table name.
     * @return string The name of the ZenMagick setting to be used to lookup
     *  custom fields for the table.
     */
    public static function getCustomFieldKey($table) {
        return ZMDbUtils::getCustomFieldKey($table);
    }

    /**
     * Get a SQL field list of custom fields for the given table.
     *
     * @param string table The table name.
     * @param string prefix Optional fieldname prefix; default is blank <em>''</em>.
     * @return string A field list or empty string.
     */
    public static function getCustomFieldsSQL($table, $prefix='') {
        return ZMDbUtils::getCustomFieldsSQL($table, $prefix);
    }

    /**
     * Get a field list of custom fields for the given table.
     *
     * <p>The returned list of field information consists of two element arrays. The
     * first element is the field name and the second the field type.</p>
     *
     * @param string table The table name.
     * @return array A list of field lists (may be empty).
     */
    public static function getCustomFields($table) {
        return ZMDbUtils::getCustomFields($table);
    }

    /**
     * Create model and populate using the given data and field map.
     *
     * @param string clazz The model class.
     * @param array data The data (keys are object property names)
     * @param array fieldMap The field mapping; default is <code>null</code> which will default to this service <code>fieldMap_</code>.
     * @return mixed The model instance.
     */
    public static function map2obj($clazz, $data, $fieldMap=null) {
        return ZMDbUtils::map2obj($clazz, $data, $fieldMap);
    }

}

?>
