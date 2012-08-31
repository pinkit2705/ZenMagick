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

use ZenMagick\Http\Sacs\SacsManager;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test SACS manager
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestSacsManager extends TestCase {

    /**
     * Get a sacs manager.
     */
    protected function getSacsManager() {
        $sacsManager = new SacsManager();
        $sacsManager->load($this->getTestsBaseDirectory().'/http/config/user_role_sacs_mappings.yaml');
        return $sacsManager;
    }

    /**
     * Test default mapping.
     */
    public function testDefaultMapping() {
        $sacsManager = $this->getSacsManager();
        $this->assertEqual(array('secure' => true, 'roles' => array('admin')), $sacsManager->getDefaultMapping());
        // default
        $this->assertTrue($sacsManager->getMappingValue('login', 'secure'));
        // explicit
        $this->assertFalse($sacsManager->getMappingValue('logoff', 'secure'));
        // not mapped at all, so default
        $this->assertEqual(array('admin'), $sacsManager->getMappingValue('foo', 'roles'));
    }

    /**
     * Test set mapping.
     */
    public function testSetMapping() {
        $sacsManager = $this->getSacsManager();
        $sacsManager->setMapping('foo', array('secure' => false));
        $this->assertFalse($sacsManager->getMappingValue('foo', 'secure'));
    }

    /**
     * Test requires security.
     */
    public function testRequiresSecurity() {
        $sacsManager = $this->getSacsManager();
        $this->assertTrue($sacsManager->requiresSecurity('login'));
    }

}
