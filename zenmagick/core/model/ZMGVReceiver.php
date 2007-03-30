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
 * A gift voucher receiver.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMGVReceiver extends ZMModel {
    var $name_;
    var $email_;
    var $amount_;
    var $message_;


    /**
     * Default c'tor.
     */
    function ZMGVReceiver() {
        parent::__construct();

        $this->name_ = '';
        $this->email_ = '';
        $this->amount_ = 0;
        $this->message_ = '';
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMGVReceiver();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // populate from request
    function populateFromRequest() {
    global $zm_request;
        $this->name_ = $zm_request->getRequestParameter('to_name', '');
        $this->email_ = $zm_request->getRequestParameter('email', '');
        $this->amount_ = $zm_request->getRequestParameter('amount', 0);
        $this->message_ = $zm_request->getRequestParameter('message', '');
    }


    /**
     * Get the receiver name.
     *
     * @return string The receiver name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the receiver email.
     *
     * @return string The receiver email.
     */
    function getEmail() { return $this->email_; }

    /**
     * Get the amount.
     *
     * @return double The amount.
     */
    function getAmount() { return $this->amount_; }

    /**
     * Chkec if there is a message.
     *
     * @return bool <code>true</code> if there is a message, <code>false</code> if not.
     */
    function hasMessage() { return !zm_is_empty($this->message_); }

    /**
     * Get the message.
     *
     * @return string The message.
     */
    function getMessage() { return $this->message_; }

}

?>
