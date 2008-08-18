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
 * Plugin to allow cron like execution of <code>ZMCronJob</code> classes.
 *
 * @package org.zenmagick.plugins.zm_cron
 * @author DerManoMann
 * @version $Id$
 */
class zm_cron extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('CronJobs', 'Allows to configure and execute cron jobs', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Trigger', 'image', 'false', 'Enable image trigger', 'zen_cfg_select_option(array(\'true\',\'false\'),');
        $this->addConfigValue('Image trigger pages', 'triggerPages', 'index', 'List of pages (separated by comma \',\') to be used for imger trigger');
        $this->addConfigValue('Missed run policy', 'missedRuns', 'ignore', 'Select what should happen when one or more runs have been missed', 'zen_cfg_select_option(array(\'ignore\',\'catch-up\'),');
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing UnitTestCase
            ZMLoader::instance()->addPath($this->getPluginDir().'tests/');
            $tests->addTest('TestZMCronParser');
        }
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        if ($this->isEnabled() && ZMTools::asBoolean($this->get('image'))) {
            $pages = $this->get('triggerPages');
            if (empty($pages) || ZMTools::inArray(ZMRequest::getPageName(), $pages)) {
                $slash = ZMSettings::get('isXHTML') ? '/' : '';
                $img = '<div><img src="'.ZMToolbox::instance()->net->url('cron_image', '', false, false).'" alt=""'.$slash.'></div>';
                $contents = preg_replace('/<\/body>/', $img . '</body>', $contents, 1);
            }
        }

        return $contents;
    }

    /**
     * Run cron.
     *
     * <p>This method is used by all methods to execute cron jobs.</p>
     *
     * <p>All output is captured and logged.</p>
     */
    public function runCron() {
        ob_start();
        $folder = $this->getPluginDir();
        $cron = ZMLoader::make('ZMCronJobs', $folder.'/etc/crontab.txt', $folder.'etc/cronhistory.txt');
        if ($cron->isTimeToRun()) {
            // update timestamp to stop other instances from running
            $cron->updateTimestamp();
            foreach ($cron->getJobs(false, ZMTools::asBoolean($this->get('missedRuns'))) as $job) {
                $cron->runJob($job);
            }
        }
        ZMObject::log('ZMCron: '.ob_get_clean(), ZM_LOG_DEBUG);
    }

}

?>
