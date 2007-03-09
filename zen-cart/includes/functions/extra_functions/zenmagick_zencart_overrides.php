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
 *
 * $Id$
 */
?>
<?php

if (!function_exists('zen_href_link')) {

    /**
     * zen_href_link wrapper that delegates to either the default Zenmagick implementation or the Ultimate SEO
     * version of it.
     */
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        $seoEnabled = defined('SEO_ENABLED') ? SEO_ENABLED : false;
        if ($seoEnabled && function_exists('zen_href_link_seo')) {
            return zen_href_link_seo($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
        } else {
            return _zm_build_href($page, $params, $transport == 'SSL', false);
        }
    }

}

?>
