<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Allow users to switch between themes.
 *
 * @package org.zenmagick.plugins.themeSwitcher
 * @author DerManoMann
 * @version $Id: zm_theme_switch.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class ZMThemeSwitcherPlugin extends Plugin implements ZMRequestHandler {
    const SESS_THEME_KEY = 'themeId';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Theme Switcher', 'Allow users to select a theme');
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
    public function initRequest($request) {
        ZMEvents::instance()->attach($this);

        $session = $request->getSession();
        if (null != ($themeId = $request->getParameter('themeId'))) {
            $session->setValue(self::SESS_THEME_KEY, $themeId);
        }

        if (null != ($themeId = $session->getValue(self::SESS_THEME_KEY))) {
            Runtime::setThemeId($themeId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $request = $args['request'];
        $contents = $args['contents'];

        if (false !== strpos($contents, zm_l10n_get('Switch theme: '))) {
            // already done, do not change
            return null;
        }

        $defaultConfig = null;
        if (!ZMSettings::exists('plugins.themeSwitcher.themes')) {
            // iterate over all themes and build default config
            $defaultConfig = '';
            foreach (ZMThemes::instance()->getThemeInfoList() as $themeInfo) {
                $defaultConfig .= $themeInfo->getThemeId().':'.$themeInfo->getName().',';
            }
        }
        $themes = explode(',', ZMSettings::get('plugins.themeSwitcher.themes', $defaultConfig));
        $links = '';
        foreach ($themes as $themeInfo) {
            if (!ZMLangUtils::isEmpty(trim($themeInfo))) {
                // themeId:name
                $details = explode(':', $themeInfo);
                if (2 > count($details)) {
                    // default
                    $details[1] = $details[0];
                }
                if (!empty($links)) {
                    $links .= '&nbsp;|&nbsp;';
                }
                $links .= '<a href="'.$request->getToolbox()->net->url(null, 'themeId='.$details[0], $request->isSecure(), false).'">'.$details[1].'</a>';
            }
        }
        if (!ZMLangUtils::isEmpty($links)) {
            $switch =  '<div id="style-switcher" style="text-align:right;padding:2px 8px;">' . zm_l10n_get('Switch theme: ') . $links . '</div>';
            $contents =  preg_replace('/(<body[^>]*>)/', '\1'.$switch, $contents, 1);
        }

        $args['contents'] = $contents;
        return $args;
    }

}

?>
