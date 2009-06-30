<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Database table mapping *service*.
 *
 * <p>This class will read and parse file(s) containing model-database mappings. The code can
 * map a single model class to a database table. Mappings are configured using nested arrays and
 * wll be parsed into something <code>ZMDatabase</code> implementations can understand and use.</p>
 *
 * <p>A simple mapping could look like this:</p>
 * <code><pre>
 *   'products_to_categories' => array(
 *       'productId' => 'column=products_id;type=integer;key=true',
 *       'categoryId' => 'column=categories_id;type=integer;key=true',
 *    ),
 * </pre></code>
 *
 * <p>Each mapping has the table name as key (without any prefix) and an array of field mappings as value.</p>
 * <p>Each entry maps a single model property to a table column. The key is the model property name (starting lowercase).
 * Available <em>*attributes*</em> are:/p>
 * <dl>
 *  <dt>column</dt>
 *  <dd>The column in the table this model property is mapped to.</dd>
 *  <dt>type</dt>
 *  <dd>The data type. Supported types are:<br>
 *   <ul>
 *    <li>integer - an integer</li>
 *    <li>boolean - a boolean</li>
 *    <li>string - a string</li>
 *    <li>float - a float</li>
 *    <li>date - a date in the format <em>'yyyy-mm-dd'<em> (as defined by <code>ZM_DATE_FORMAT</code>)</li>
 *    <li>datetime - a date and time value in the format <em>'yyyy-mm-dd hh:ii:ss'<em> (as defined by <code>ZM_DATETIME_FORMAT</code>)</li>
 *    <li>blob - binary data</li>
 *   </ul>
 *  </dd>
 *  <dt>key</dt>
 *  <dd>If set to <code>true</code> this field is part of a table key.</dd>
 *  <dt>auto</dt>
 *  <dd>Indicates that this column is an auto-increment column, so new model instances will be updated with the new value on create.</dd>
 * </dl>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.database
 * @version $Id: ZMDbTableMapper.php 2182 2009-04-28 03:21:16Z dermanomann $
 */
class ZMDbTableMapper extends ZMObject {
    /** Mapping of native data types to API types. */
    public static $NATIVE_TO_API_TYPEMAP = array(
        'char' => 'string',
        'varchar' => 'string',
        'tinyint' => 'integer',
        'smallint' => 'integer',
        'mediumint' => 'integer',
        'int' => 'integer',
        'bigint' => 'integer',
        'int unsigned' => 'integer',
        'decimal' => 'float',
        'real' => 'float',
        'text' => 'string',
        'tinytext' => 'string',
        'mediumtext' => 'string',
        'mediumblob', 'blob'
    );

    const CACHE_KEY = "ZMDbTableMapper::mappings";
    private $tableMap_;
    private $cache_;
    private $isCached_;
    private $tablePrefix_;

    /**
     * Create a new instance.
     *
     * @param string configFolder The folder that contains the mapping files.
     */
    function __construct() {
        parent::__construct();
        $this->tableMap_ = array();
        $this->isCached_ = false;
        $this->tablePrefix_ = ZMSettings::get('zenmagick.core.database.tablePrefix', '');
        $this->cache_ = ZMCaches::instance()->getCache('services', array('cacheTTL' => 300));
        if (ZMSettings::get('zenmagick.core.database.mappings.cache.enabled', true)) {
            if (false !== ($cachedMap = $this->cache_->lookup(self::CACHE_KEY))) {
                $this->tableMap_ = $cachedMap;
                $this->isCached_ = true;
            } else {
                $this->loadMappingFile();
            }
            ZMEvents::instance()->attach($this);
        } else {
            $this->loadMappingFile();
        }
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('DbTableMapper');
    }


    /**
     * Refresh cache if empty.
     */
    public function onZMInitDone() {
        if (ZMSettings::get('zenmagick.core.database.mappings.cache.enabled', true) && !$this->isCached()) {
            $this->updateCache(ZMRuntime::getDatabase());
        }
    }

    /**
     * Load mappings from file.
     */
    protected function loadMappingFile() {
        // load from file
        //XXX: make nicer
        eval('$mappings = '.file_get_contents(ZMRuntime::getInstallationPath().'/'.ZMSettings::get('zenmagick.core.database.mappings.file')));
        foreach ($mappings as $table => $mapping) {
            $this->tableMap_[$table] = $this->parseTable($mapping);
        }
    }

    /**
     * Check if cached mappings are used.
     *
     * @return boolean <code>true</code> if mappings have been loaded from cache.
     */
    public function isCached() {
        return $this->isCached_;
    }

    /**
     * Create/update cache.
     *
     * @param ZMDatabase database The database.
     */
    public function updateCache($database) {
        if ($this->isCached_) {
            $this->cache_->remove(self::CACHE_KEY);
            $this->isCached_ = false;
        }
        $cachedMap = array();
        foreach (array_keys($this->tableMap_) as $table) {
            $cachedMap[$table] = $this->getMapping($table, $database);
        }
        $this->cache_->save($cachedMap, self::CACHE_KEY);
        $this->isCached_ = false;
    }

    /**
     * Parse mapping for a single table.
     *
     * @param array mapping The mapping.
     * @return array The parsed mapping.
     */
    protected function parseTable($mapping) {
        $defaults = array('key' => false, 'auto' => false, 'custom' => false);
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
     * @param ZMDatabase database The database.
     * @return array The mapping or <code>null</code>.
     */
    public function getMapping($tables, $database) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }
        foreach ($tables as $ii => $table) {
            $tables[$ii] = str_replace($this->tablePrefix_, '', $table);
        }

        $mappings = array();
        foreach (array_reverse($tables) as $table) {
            if (empty($table)) {
                continue;
            }
            if (!array_key_exists($table, $this->tableMap_) && ZMSettings::get('zenmagick.core.database.mappings.autoMap.enabled', true)) {
                //XXX: refresh cache?
                ZMLogging::instance()->log('creating dynamic mapping for table name: '.$table, ZMLogging::DEBUG);
                $rawMapping = self::buildTableMapping($table, $database);
                $this->setMappingForTable(str_replace($this->tablePrefix_, '', $table), $rawMapping);
            }

            if ($this->isCached_) {
                // assume all is good
                $tableMap = $this->tableMap_[$table];
            } else {
                // add the current custom fields at runtime as they might change
                $tableMap = $this->addCustomFields($this->tableMap_[$table], $table);
            }
            $mappings = array_merge($mappings, $tableMap);
        }

        return $mappings;
    }

    /**
     * Generate a database mapping for the given table.
     *
     * @param string table The table name.
     * @param ZMDatabase database The database.
     * @param boolean print Optional flag to also print the mapping in a form that can be used
     *  to cut&paste into a mapping file; default is <code>false</code>.
     * @return array The mapping.
     */
    public function buildTableMapping($table, $database, $print=false) {
        // check for prefix
        if (null === ($tableMetaData = $database->getMetaData($table))) {
            // try adding the prefix
            $table = $this->tablePrefix_.$table;
            if (null === ($tableMetaData = $database->getMetaData($table))) {
                return null;
            }
        }

        $mapping = array();
        ob_start();
        echo "'".str_replace($this->tablePrefix_, '', $table)."' => array(\n";
        $first = true;
        foreach ($tableMetaData as $column) {
            $type = preg_replace('/(.*)\(.*\)/', '\1', $column['type']);
            if (array_key_exists($type, self::$NATIVE_TO_API_TYPEMAP)) {
                $type = self::$NATIVE_TO_API_TYPEMAP[$type];
            }

            $line = 'column=' . $column['name'] . ';type=' . $type;
            if ($column['key']) {
                $line .= ';key=true';
            }
            if ($column['autoIncrement']) {
                $line .= ';auto=true';
            }
            $mapping[$column['name']] = $line;
            if (!$first) {
                echo ",\n";
            }
            echo "    '" . $column['name'] . "' => '" . $line . "'";
            $first = false;
        }
        echo "\n),\n";

        $text = ob_get_clean();

        if ($print) {
            echo $text;
        }

        return $mapping;
    }

    /**
     * Handle mixed mapping values.
     *
     * <p>If enabled (via setting 'isEnableDBAutoMapping'), mappings for unknown tables will be build
     * on demand.</p>
     *
     * @param mixed mapping The field mappings or table name.
     * @param ZMDatabase database Optional database; default is <code>null</code> to use the default.
     * @return array A mapping or <code>null</code>.
     */
    public function ensureMapping($mapping, $database) {
        if (!is_array($mapping)) {
            // table name
            $table = $mapping;
            $mapping = $this->getMapping($table, $database);
            if (null === $mapping && ZMSettings::get('zenmagick.core.database.mappings.autoMap.enabled', true)) {
                //XXX: refresh cache?
                ZMLogging::instance()->log('creating dynamic mapping for table name: '.$table, ZMLogging::DEBUG);
                $rawMapping = self::buildTableMapping($table, $database);
                $this->setMappingForTable(str_replace($this->tablePrefix_, '', $table), $rawMapping);
                $mapping = $this->getMapping($table, $database);
            }
            return $mapping;
        }
        // either mapping or table list
        return (0 < count($mapping) && is_array($mapping[0])) ? $mapping : $this->getMapping($mapping, $database);
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
        $this->tableMap_[$table] = $this->parseTable($mapping);
    }

    /**
     * Get field info map about custom fields for the given table.
     *
     * <p>The returned array is a map with the property as key and an info map as value.</p>
     *
     * @param string table The table name.
     * @return array A map of custom field details (if any)
     */
    public function getCustomFieldInfo($table) {
        $customFieldKey = 'zenmagick.core.database.sql.'.str_replace($this->tablePrefix_, '', $table).'.customFields';
        $setting = ZMSettings::get($customFieldKey);
        if (empty($setting)) {
            return array();
        }

        $customFields = array();
        foreach (explode(',', $setting) as $field) {
            // process single fields
            if (!empty($field)) {
                $info = explode(';', trim($field));
                $fieldId = (count($info) > 2 ? $info[2] : $info[0]);
                $customFields[$fieldId] = array('column' => $info[0], 'type' => $info[1], 'property' => $fieldId);
            }
        }

        return $customFields;
    }

    /**
     * Add a field list of custom fields for the given table.
     *
     * @param array mapping The existing mapping.
     * @param string table The table name.
     * @return array The updated mapping
     */
    protected function addCustomFields($mapping, $table) {
        $defaults = array('key' => false, 'auto' => false, 'custom' => true);
        foreach ($this->getCustomFieldInfo($table) as $fieldId => $fieldInfo) {
            // merge in defaults
            $mapping[$fieldId] = array_merge($defaults, $fieldInfo);
        }

        return $mapping;
    }

    /**
     * Get a list of all available tables.
     *
     * @return array List of table names.
     */
    public function getTableNames() {
        return array_keys($this->tableMap_);
    }

}

?>
