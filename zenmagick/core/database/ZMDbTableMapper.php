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

define('ZM_DB_MAPPING_FILE', dirname(__FILE__).'/db_mappings.txt');


/**
 * Database table mapping *service*.
 *
 * @author mano
 * @package org.zenmagick.database
 * @version $Id$
 */
class ZMDbTableMapper {
    private $tableMap;


    /*
    key: the data key/property name
     =>
      column: table column name
      type: type as expected by ZMDatabase
      key: if field is key for updates
      auto: if field is auto_increment field, so insert id will be set after insert
     */


    /**
     * Create a new instance.
     *
     * @param string configFolder The folder that contains the mapping files.
     */
    function __construct() {
        //TODO: load from setting, avoid __FILE__ because of core.php!!
        eval('$mappings = '.file_get_contents(ZM_DB_MAPPING_FILE));
        $this->tableMap = array();
        foreach ($mappings as $table => $mapping) {
            $this->tableMap[$table] = $this->parseTable($mapping);
        }
    }

    /**
     * Parse mapping for a single table.
     *
     * @param array mapping The mapping.
     * @return array The parsed mapping.
     */
    protected function parseTable($mapping) {
        $defaults = array('key' => false, 'auto' => false);
        $tableInfo = array();
        foreach ($mapping as $property => $info) {
            $arr = array();
            parse_str(str_replace(';', '&', $info), $arr);
            $tableInfo[$property] = array_merge($defaults, $arr);
            $tableInfo[$property]['property'] = $property;
            // handle boolean values
            foreach ($tableInfo[$property] as $name => $value) {
                if ('false' == $value) {
                    $tableInfo[$property][$name] = false;
                } else if ('true' == $value) {
                    $tableInfo[$property][$name] = true;
                } 
            }
        }

        return $tableInfo;
    }

    /**
     * Get a table map.
     *
     * @param mixed tables Either a single table or array of table names.
     * @return array The mapping or <code>null</code>.
     */
    public function getMapping($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }
        foreach ($tables as $ii => $table) {
            $tables[$ii] = str_replace(ZM_DB_PREFIX, '', $table);
        }

        $mappings = array();
        foreach (array_reverse($tables) as $table) {
            if (!isset($this->tableMap[$table])) {
                return null;
            }
            //TODO: do only once?
            // add the current custom fields
            $tableMap = $this->addCustomFields($this->tableMap[$table], $table);
            $mappings = array_merge($mappings, $tableMap);
        }

        return $mappings;
    }
    /**
     * Handle mixed mapping values.
     *
     * @param mixed mapping The field mappings or table name.
     * @return array A mapping or <code>null</code>.
     */
    public function ensureMapping($mapping) {
        if (!is_array($mapping)) {
            // table name
            return $this->getMapping($mapping);
        }
        // either mapping or table list
        return is_array($mapping[0]) ? $mapping : $this->getMapping($mapping);
    }

    /**
     * Set the mapping for the given table.
     *
     * <p><strong>NOTE:</strong> This will silently override mappings for existing tables.</p>
     *
     * @param string table The (new) table.
     * @param array The new mapping.
     */
    public function setMappingForTable($table, $mapping) {
        $this->tableMap[$table] = $this->parseTable($mapping);
    }

    /**
     * Get the setting name for custom fields for the given table name.
     *
     * @param string table The table name.
     * @return string The name of the ZenMagick setting to be used to lookup
     *  custom fields for the table.
     */
    protected function getCustomFieldKey($table) {
        $table = str_replace(ZM_DB_PREFIX, '', $table);
        return 'sql.'.$table.'.customFields';
    }

    /**
     * Add a field list of custom fields for the given table.
     *
     * @param array mapping The existing mapping.
     * @param string table The table name.
     * @return array The updated mapping
     */
    protected function addCustomFields($mapping, $table) {
        $setting = ZMSettings::get($this->getCustomFieldKey($table));
        if (!empty($setting)) {
            foreach (explode(',', $setting) as $field) {
                if (!empty($field)) {
                    $fieldInfo = explode(';', trim($field));
                    $mapping[$fieldInfo[0]] = array('column' => $fieldInfo[0], 'type' => $fieldInfo[1], 'property' => $fieldInfo[0]);
                }
            }
        }

        return $mapping;
    }

}

?>
