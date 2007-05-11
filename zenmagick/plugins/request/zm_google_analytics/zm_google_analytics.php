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
 *
 * $Id$
 */
?>
<?php


/**
 * Plugin providing functionallity to add Goggle Analytics code to the store.
 *
 * <p>This plugin is based on features of the 'Simple Google Analytics' mod'
 * by Dayne Larsen (info@barracudaproductions.com) and 
 * Andrew Berezin (andrew@ecommerce-service.com).</p>
 *
 * <p>For more about Google Analytics see https://www.google.com/support/analytics/bin/answer.py?answer=27203.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins.zm_google_analytics
 * @version $Id$
 */
class zm_google_analytics extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function zm_google_analytics() {
        parent::__construct('Google Analytics', 'Adds Google Analytics.');
        $this->setKeys(array('uacct', 'affiliation',/*'target', */ 'identifier', "debug"));
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_google_analytics();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Google Analytics Account', 'uacct', 'UA-XXXXXX-X', 'Your Google Analytics account number');
        $this->addConfigValue('Google Analytics Account', 'affiliation', '', 'Optional partner or store affiliation');
        /*
        $this->addConfigValue('Target Address', 'target', 'customers',
            'Which order adress (City/State/Country) should be used to correlate the transaction with?',
            'zen_cfg_select_option(array(\'Customer\', \'Delivery\', \'Billing\'),');
         */
        $this->addConfigValue('Configure product identifier', 'identifier', 'ProductId', 'Select whether to use productId or model to identify products',
           'zen_cfg_select_option(array(\'ProductId\', \'Model\'),');

        $this->addConfigValue('Debug', "debug", 'Disabled', 'Generate code, but make inactive.',
          'zen_cfg_select_option(array(\''.'Enabled'.'\', \''.'Disabled'.'\'), ');
    }

    /**
     * Get an optional page contents filter from this plugin.
     *
     * <p><strong>NOTE:</strong> Page filter are affected by the setting
     * <em>isEnableOB</em>, as without output buffering this functionallity can't
     * be realised.</p>
     *
     * @return ZMPluginFilter A <code>ZMPluginFilter</code> instance or <code>null</code> if
     *  not supported.
     */
    function getPageFilter() {
        return new zm_google_analytics_page_filter($this);
    }

}

/**
 * Page filter that adds Google analytics code to all pages.
 */
class zm_google_analytics_page_filter extends ZMPluginFilter {
    var $plugin_;
    var $eol_;


    /**
     * Create new instance.
     *
     * @param zm_google_analytics plugin The google analytics instance.
     */
    function zm_google_analytics_page_filter(&$plugin) {
        parent::__construct();

        $this->plugin_ =& $plugin;
        $this->eol_ = "\n";
    }

    /**
     * Create new instance.
     *
     * @param zm_google_analytics plugin The google analytics instance.
     */
    function __construct(&$plugin) {
        $this->zm_google_analytics_page_filter($plugin);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Apply this filter to the given contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function applyFilter($contents) {
        $trackerCode = $this->getTrackerCode();
        $checkoutCode = $this->getCheckoutCode();
        return preg_replace('/<\/body>/', $trackerCode . $checkoutCode. ' </body>', $contents, 1);
    }


    /**
     * Check for debug flag.
     */
    function isDebug() {
        return 'Enabled' == $this->plugin_->get('debug');
    }

    /**
     * Format the generic tracking code.
     *
     * @return string The tracking code.
     */
    function getTrackerCode() {
    global $zm_request;

        if ($zm_request->isSecure()) {
            $url = "https://ssl.google-analytics.com/urchin.js";
        } else {
            $url = "http://www.google-analytics.com/urchin.js";
        }

        $code = '<script src="' . $url . '" type="text/javascript"></script>' . $this->eol_;
        $code .= '<script type="text/javascript">';
        $code .= '_uacct = "' . $this->plugin_->get('uacct') . '";';
        if ($this->isDebug()) {
            $code .= '//';
        }
        $code .= 'urchinTracker(); </script>' . $this->eol_;

        return $code;
    }

    /**
     * Format the checkout order tracking code.
     *
     * @return string The order tracking code or empty string if not applicable.
     */
    function getCheckoutCode() {
    global $zm_request, $zm_order, $zm_categories;

        if ('checkout_success' != $zm_request->getPageName()) {
            return '';
        }

        $address = $zm_order->hasShippingAddress() ? $zm_order->getShippingAddress() : $zm_order->getBillingAddress();
        $country = $address->getCountry();
        // totals
        $total = $zm_order->getOrderTotal('total', true);
        $totalValue = number_format($total->getAmount(), 2, '.', '');
        $tax = $zm_order->getOrderTotal('tax', true);
        $taxValue = number_format($tax->getAmount(), 2, '.', '');
        $shipping = $zm_order->getOrderTotal('shipping', true);
        $shippingValue = number_format($shipping->getAmount(), 2, '.', '');

        $code = '<form style="display:none;" name="utmform"><textarea id="utmtrans">' . $this->eol_;
        // UTM:T|[order-id]|[affiliation]|[total]|[tax]|[shipping]|[city]|[state]|[country]
        $code .= 'UTM:T|'.$zm_order->getId().'|'.$this->plugin_->get('affiliation').'|'.$totalValue.'|'.$taxValue.'|'.
            $shippingValue.'|'.$address->getCity().'|'.$address->getState().'|'.$country->getIsoCode3() . $this->eol_;

        //UTM:I|[order-id]|[sku/code]|[productname]|[category]|[price]|[quantity]
        foreach ($zm_order->getOrderItems() as $orderItem) {
            $identifier = 'Model' == $this->plugin_->get('identifier') ? $orderItem->getModel() : $orderItem->getProductId();
            $category = $zm_categories->getDefaultCategoryForProductId($orderItem->getProductId());
            $price = number_format($orderItem->getCalculatedPrice(), 2, '.', '');
            $code .= 'UTM:I|'.$zm_order->getId().'|'.$identifier.'|'.$orderItem->getName().'|'.$category->getName().'|'.$price.'|'.$orderItem->getQty() .$this->eol_;
        }

        $code .= '</textarea></form>' . $this->eol_;
        $code .= '<script type="text/javascript">';
        if ($this->isDebug()) {
            $code .= '//';
        }
        $code .= '__utmSetTrans();</script>' . $this->eol_;
        
        return $code;
    }

}

?>
