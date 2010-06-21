<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

    // will be modified by admin patch
    $zmLanguagesBefore = true;

    if ($zmLanguagesBefore) {
        // initial setup loading languages first
        require_once dirname(dirname(__FILE__)) . "/init_languages.php";
    }

    // init ZenMagick
    include DIR_FS_CATALOG."zenmagick/init.php";

    if (!$zmLanguagesBefore) {
        // once all i18n is patched, this line will be used
        require dirname(__FILE__) . "/init_languages.php";
    }

    // load original init script
    require dirname(dirname(__FILE__)) . "/init_templates.php";
