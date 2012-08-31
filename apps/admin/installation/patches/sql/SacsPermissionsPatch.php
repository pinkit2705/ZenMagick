<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\apps\admin\installation\patches\sql;

use ZenMagick\base\Runtime;
use ZenMagick\apps\admin\installation\patches\SQLPatch;


/**
 * Patch to create the sacs permission tables.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SacsPermissionsPatch extends SQLPatch {
    var $sqlFiles_ = array(
        "/apps/admin/installation/etc/sacs_perms_install.sql"
    );

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('sacsPermissions');
        $this->label_ = 'Create new table to store custom admin access rules';
        $this->setTables('sacs_permissions');
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return !$this->tablesExist();
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $baseDir = Runtime::getInstallationPath();
        // do only interactive
        if ($force || $this->isOpen()) {
            $status = true;
            foreach ($this->sqlFiles_ as $file) {
                $sql = file($baseDir.$file);
                $status |= $this->_runSQL($sql);
            }
            return $status;
        }
        return true;
    }
}
