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
 * A order total line.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model.order
 * @version $Id$
 */
class ZMOrderTotal extends ZMModel {
    var $name_;
    var $value_;
    var $amount_;
    var $type_;


    /**
     * Create new total.
     *
     * @param string name The total name.
     * @param string value The total value.
     * @param float amount The total amount.
     * @param string type The total type.
     */
    function ZMOrderTotal($name, $value, $amount, $type) {
        parent::__construct();

        $this->name_ = $name;
        $this->value_ = $value;
        $this->amount_ = $amount;
        $this->type_ = $type;
    }

    /**
     * Create new total.
     *
     * @param string name The total name.
     * @param string value The total value.
     * @param float amount The total amount.
     * @param string type The total type.
     */
    function __construct($name, $value, $amount, $type) {
        $this->ZMOrderTotal($name, $value, $amount, $type);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the order total name.
     *
     * @return string The order total name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the order total value.
     *
     * @return string The formatted order total value.
     */
    function getValue() { return $this->value_; }

    /**
     * Get the order total amount.
     *
     * @return float The order total amount.
     */
    function getAmount() { return $this->amount_; }

    /**
     * Get the order total type.
     *
     * @return string The order total type.
     */
    function getType() { return $this->type_; }

}

?>
