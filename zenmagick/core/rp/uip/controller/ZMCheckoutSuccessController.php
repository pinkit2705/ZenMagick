<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Request controller for checkout success page.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCheckoutSuccessController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMCheckoutSuccessController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCheckoutSuccessController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_orders;

        $zm_crumbtrail->addCrumb("Checkout", zm_secure_href(FILENAME_CHECKOUT_SHIPPING, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        $orders = $zm_orders->getOrdersForAccountId($zm_request->getAccountId(), 1);
        $this->exportGlobal("zm_order", $orders[0]);
        $this->exportGlobal("zm_account", $zm_request->getAccount());

        if (zm_setting('isLogoffGuestAfterOrder') && $zm_request->isGuest()) {
            $session = $zm_request->getSession();
            $session->clear();
        }

        return $this->findView();
    }

}

?>
