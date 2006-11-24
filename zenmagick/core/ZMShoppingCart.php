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
 */
?>
<?php


/**
 * Shopping cart.
 * <p>This is assuming a properly configured zen cart.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMShoppingCart {
    var $db_;
    var $cart_;
    var $zenTotals_;
    var $payments_;


    // create new instance
    function ZMShoppingCart() {
    global $zm_runtime;
        $this->db_ = $zm_runtime->getDB();
        $this->refresh();
        $this->zenTotals_ = null;
        $this->payments_ = null;
        // header_php.php
        //XXX: $this->cart_->get_products(true);
    }

    // create new instance
    function __construct() {
        $this->ZMShoppingCart();
    }

    function __destruct() {
    }


    function refresh() { $this->cart_ = zm_get_zen_cart(); }

    // getter/setter
    function isEmpty() { return 0 == $this->getSize(); }
    function getSize() { return isset($this->cart_) ? count($this->cart_->get_products()) : 0; }
    function getWeight() { return $this->cart_->show_weight(); }

    function isGVOnly() { return $this->cart_->gv_only(); }
    function freeProductsCount() { return $this->cart_->in_cart_check('product_is_free','1'); }
    function virtualProductsCount() { return $this->cart_->in_cart_check('products_virtual','1'); }
    function freeShippingCount() { return $this->cart_->in_cart_check('product_is_always_free_shipping','1'); }
    function isVirtual() { return $_SESSION['sendto'] == false; }

    function getItems() {
        $zenItems = $this->cart_->get_products();
        $items = array();
        foreach ($zenItems as $zenItem) {
            $item = new ZMShoppingCartItem($this, $zenItem);
            array_push($items, $item);
        }
        return $items;
    }

    function getTotal() { return $this->cart_->show_total(); }

    function _getItemAttributes($item) {
    global $zm_request;
        // collect attribute values for same attribute
        $attributesLookup = array();

        if (!isset($item->zenItem_['attributes']) || !is_array($item->zenItem_['attributes']))
            return $attributesLookup;

        // load attributes
        foreach ($item->zenItem_['attributes'] as $option => $type) {
            $sql = "select popt.products_options_name, poval.products_options_values_name,
                        pa.options_values_price, pa.price_prefix
                    from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                       " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    where pa.products_id = :productId
                    and pa.options_id = :option
                    and pa.options_id = popt.products_options_id
                    and pa.options_values_id = '" . $type . "'
                    and pa.options_values_id = poval.products_options_values_id
                    and popt.language_id = :languageId
                    and poval.language_id = :languageId";
            $sql = $this->db_->bindVars($sql, ":productId", $item->getId(), "integer");
            $sql = $this->db_->bindVars($sql, ":option", $option, "integer");
            $sql = $this->db_->bindVars($sql, ":productId", $zm_request->getLanguageId(), "integer");

            $results = $this->db_->Execute($sql);

            $name = $results->fields['products_options_name'];
            if (array_key_exists($name, $attributesLookup)) {
                $atname = $attributesLookup[$name];
            } else {
                $atname = str_replace(' ', '', $name);
                $$atname = new ZMAttribute($option, $name, null);
                $attributesLookup[$name] = $atname;
            }

            $value = $results->fields['products_options_values_name'];
            if ($type == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
                // text is user input
                $value = $item->zenItem_['attributes_values'][$option];
            }
            $attributeValue = &new ZMAttributeValue($type, $value);

            $attributeValue->pricePrefix_ = $results->fields['options_values_price'];
            $attributeValue->price_ = $results->fields['options_values_price'];
            array_push($$atname->values_, $attributeValue);
        }
        $attributes = array();
        foreach ($attributesLookup as $name => $atname) {
            array_push($attributes, $$atname);
        }

        return $attributes;
    }

    function getComment() { return isset($_SESSION['comments']) ?  $_SESSION['comments'] : ''; }
    function getShippingMethodId() { return (isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id'])) ? $_SESSION['shipping']['id'] : null; }
    function getPaymentMethodId() { return isset($_SESSION['payment']) ? $_SESSION['payment'] : null; }
    function getShippingMethod() {
        $order = new order();
        return array_key_exists('shipping_method', $order->info) ? $order->info['shipping_method'] : null;
    /*
        $id = $this->getShippingMethodId();
        if (null == $id)
            return null;

        $shipping = new ZMShipping();
        return $shipping->getShippingMethodForId($id);
    */
    }
    function getPaymentType() {
        $payments = new ZMPayments();
        return $payments->getSelectedPaymentType();
        /*
        $zenModules = $payments->getZenModules();
        print_r($zenModules->selected_module);
        print_r($GLOBALS[$zenModules->selected_module]);
        $mod = $GLOBALS[$zenModules->selected_module];
        print_r($mod->confirmation());
        */
    /*
        $payment_modules = new payment($_SESSION['payment']);
        $payment_modules->update_status();
        if (is_array($payment_modules->modules)) {
            $payment_modules->pre_confirmation_check();
        }

        $method = null;
        if (is_array($payment_modules->modules)) {
            if (is_object($GLOBALS[$payment_modules->selected_module]) && ($GLOBALS[$payment_modules->selected_module]->enabled) ) {
                $method = new ZMPaymentMethod(); 
                print_r($this->selected_module);
            }
        }
        */

        $class =& $_SESSION['payment'];
        return $GLOBALS[$class]->title;
    /*
        $id = $this->getPaymentMethodId();
        if (null == $id)
            return null;

        $payments = $this->getPaymentTypes();
        return array_key_exists($id, $payments) ? $payments[$id] : null;
    */
    }

    function hasShippingAddress() { return zm_not_null($_SESSION['sendto']); }
    function hasBillingAddress() { return zm_not_null($_SESSION['billto']); }

    function getShippingAddress() {
    global $zm_addresses;
        return $zm_addresses->getAddressForId($_SESSION['sendto']);
    }

    function getBillingAddress() {
    global $zm_addresses;
        return $zm_addresses->getAddressForId($_SESSION['billto']);
    }


    // get the action URL for the actual order form
    function getOrderFormURL() {
    global $$_SESSION['payment'];
        $url = zm_secure_href(FILENAME_CHECKOUT_PROCESS, '', false);
        if (isset($$_SESSION['payment']->form_action_url)) {
            $url = $$_SESSION['payment']->form_action_url;
        }
        return $url;
    }

    // get the order form form elements
    function getOrderFormContent($echo=true) {
        $payments = $this->_getPayments();
        $zenModules = $payments->getZenModules();
        $content = $zenModules->process_button();

        if ($echo) echo $content;
        return $content;
    }
    

    function _getZenTotals() {
    global $order_total_modules;
        if (null == $this->zenTotals_) {
            $this->zenTotals_ = $order_total_modules;
            if (!isset($order_total_modules)) {
                require_once(DIR_WS_CLASSES . 'order_total.php');
                $zenTotals = new order_total();
            }
            require_once(DIR_WS_CLASSES . 'order.php');
            $GLOBALS['order'] = new order;
            $this->zenTotals_->process();
        }

        return $this->zenTotals_;
    }


    // get shopping cart totals
    function getTotals() {
        $zenTotals = $this->_getZenTotals();
        $totals = array();
        foreach ($zenTotals->modules as $module) {
            $class = str_replace('.php', '', $module);
            $output = $GLOBALS[$class]->output;
            $type = substr($class, 3);
            //$size = sizeof($output);
            //echo "m:".$module." ".$size." ".$type."<br>";

            foreach ($output as $zenTotal) {
                //print_r($zenTotal);
                //echo "t:".$zenTotal."<br>";
                array_push($totals, new ZMOrderTotal($zenTotal['title'], $zenTotal['text'], $type));
            }
        }
        return $totals;
    }

    function _getPayments() {
        if (null == $this->payments_) {
            $this->payments_ = new ZMPayments();
        }
        return $this->payments_;
    }

    // JS validation code as provided by the payment modules
    function getPaymentsJavaScript($echo=true) {
        $payments = $this->_getPayments();
        $js = $payments->getPaymentsJavaScript(false);

        // strip invalid script attribute
        $js = str_replace(' language="javascript"', '', $js);

        if ($echo) echo $js;
        return $js;
    }

    // available payment types
    function getPaymentTypes() {
        $payments = $this->_getPayments();
        return $payments->getPaymentTypes();
    }

    // available credit options
    function getCreditTypes() {
        // looks suspiciously like getPaymentTypes in ZMPayments...
        $zenTotals = $this->_getZenTotals();
        $zenTypes = $zenTotals->credit_selection();
        $creditTypes = array();
        foreach ($zenTypes as $zenType) {
            $creditType = new ZMPaymentType($zenType['id'], $zenType['module']);
            if (isset($zenType['credit_class_error'])) {
                $creditType->error_ = $zenType['credit_class_error'];
            }
            if (isset($zenType['fields'])) {
                foreach ($zenType['fields'] as $zenField) {
                    $creditType->addField(new ZMPaymentField($zenField['title'], $zenField['field']));
                }
            }
            array_push($creditTypes, $creditType);
            //echo "<pre>"; print_r($zenType); echo "</pre>";
        }

        return $creditTypes;
    }
    
}

?>
