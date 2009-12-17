<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Subscriptions.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @author DerManoMann
 * @version $Id$
 */
class zm_subscriptions extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Subscriptions', 'Allow users to subscribe products/orders', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);

        // the new prices and customer flag
        $customFields = array(
            'orders' => array(
                'is_subscription;boolean;subscription',
                'is_subscription_canceled;boolean;subscriptionCanceled',
                'subscription_next_order;datetime;nextOrder',
                'subscription_schedule;string;schedule',
                'subscription_order_id;integer;subscriptionOrderId'
            )
        );
        foreach ($customFields as $table => $fields) {
            foreach ($fields as $field) {
                ZMSettings::append('zenmagick.core.database.sql.'.$table.'.customFields', $field, ',');
            }
        }
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
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install.sql")), $this->messages_);

        $this->addConfigValue('Qualifying amount', 'minAmount', '0', 'The minimum amount to qualify for a subscription');
        $this->addConfigValue('Minimum orders', 'minOrders', '0', 'The minimum number of orders before the subscription can be canceled');
        $this->addConfigValue('Cancel dealline', 'cancelDeadline', '0', 'Days before the next order the user can cancel the subscription');
        $this->addConfigValue('Admin notification email address', 'adminEmail', ZMSettings::get('storeEmail'),
            'Email address for admin notifications (use store email if empty)');
        $this->addConfigValue('Subscription comment', 'subscriptionComment', true, 'Create subscription comment on original order',
            'widget@BooleanFormWidget#name=subscriptionComment&default=true&label=Add comment');
        $this->addConfigValue('Order history', 'orderHistory', true, 'Create subscription order history on schedule',
            'widget@BooleanFormWidget#name=orderHistory&default=true&label=Create schedule history');
        $this->addConfigValue('Shipping Address', 'addressPolicy', 'order', 'use either the original shipping addres, or the current default address',
            'widget@SelectFormWidget#name=addressPolicy&default=order&options='.urlencode('order=Order Address&account=Account Address'));
        $this->addConfigValue('Order status', 'orderStatus', '2', 'Order status for subscription orders',
            'widget@OrderStatusSelectFormWidget#name=orderStatus&default=2');
        $this->addConfigValue('Schedule offset', 'scheduleOffset', '0',
            'Optional offset (in days) to schedule subscription earlier that actually required');
        $this->addConfigValue('Customer cancel', 'customerCancel', false, 'Allow customers to cancel subscriptions directly',
            'widget@BooleanFormWidget#name=customerCancen&default=false&label=Allow customer cancel');
    }

    /**
     * {@inheritDoc}
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        ZMEvents::instance()->attach($this);

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing ZMTestCase
            ZMLoader::instance()->addPath($this->getPluginDirectory().'tests/');
            $tests->addTest('TestSubscriptions');
        }

        // if zm_cron available, load cron job
        if (null != ZMPlugins::instance()->getPluginForId('zm_cron')) {
            // add class path only now to avoid errors due to missing ZMCronJob
            ZMLoader::instance()->addPath($this->getPluginDirectory().'cron/');
        }

        // set mappings and permissions of custom pages
        ZMSacsManager::instance()->setMapping('cancel_subscription', ZMZenCartUserSacsHandler::REGISTERED);
        ZMUrlMapper::instance()->setMappingInfo(null, array('viewId' => 'cancel_subscription', 'view' => 'account', 'viewDefinition' => 'RedirectView'));
        ZMSacsManager::instance()->setMapping('subscription_request', ZMZenCartUserSacsHandler::REGISTERED);
        ZMUrlMapper::instance()->setMappingInfo('subscription_request', array('viewId' => 'success', 'view' => 'subscription_request', 'viewDefinition' => 'RedirectView'));

        // set up request form validation
        ZMValidator::instance()->addRules('subscription_request', array(
            array('ListRule', 'type', array_keys($this->getRequestTypes())),
            array('RequiredRule', 'message', zm_l10n_get("Please enter a message")),
        ));

        // add admin page
        $this->addMenuItem('zm_subscriptions', zm_l10n_get('Subscriptions'), 'ZMSubscriptionAdminController');
    }

    /**
     * Event handler to pick up subscription checkout options.
     */
    public function onZMInitDone($args=array()) {
        if ('checkout_shipping' == ZMRequest::instance()->getRequestId() && 'POST' == ZMRequest::instance()->getMethod()) {
            if (ZMLangUtils::asBoolean(ZMRequest::instance()->getParameter('subscription'))) {
                ZMRequest::instance()->getSession()->setValue('subscription_schedule', ZMRequest::instance()->getParameter('schedule'));
            } else {
                ZMRequest::instance()->getSession()->removeValue('subscription_schedule');
            }
        }
        if ('checkout_success' == ZMRequest::instance()->getRequestId()) {
            ZMRequest::instance()->getSession()->removeValue('subscription_schedule');
        }
    }

    /**
     * Check if the given cart can be used as subscription.
     *
     * @param ZMShoppingCart cart The cart.
     * @return boolean <code>true</code> if the cart qualifies for a subscription.
     */
    public function qualifies($cart) {
        return $this->get('minAmount') <= $cart->getTotal();
    }

    /**
     * Check if customer can cancel subscriptions directly.
     *
     * @return boolean <code>true</code> if direct cancel is allowed.
     */
    public function isCustomerCancel() {
        return $this->get('customerCancel');
    }

    /**
     * Check if currently subscription is selected.
     *
     * @return string The subscription schedule key or <code>null</code>.
     */
    public function getSelectedSchedule() {
        $schedule = ZMRequest::instance()->getSession()->getValue('subscription_schedule');
        return empty($schedule) ? null : $schedule;
    }

    /**
     * Get all available request types as map.
     * 
     * <p>This can be configured/changed via the setting <em>plugins.zm_subscriptions.request.types</em>.</p>
     *
     * @return array Hash map of schedule key => name.
     */
    public function getRequestTypes() {
        $defaults = array(
                'cancel' => zm_l10n_get("Cancel Subscription"),
                'enquire' => zm_l10n_get("Enquire order status"),
                'other' => zm_l10n_get("Other"),
        );
        return ZMSettings::get('plugins.zm_subscriptions.request.types', $defaults);
    }

    /**
     * Get all available schedules as map.
     * 
     * <p>This can be configured/changed via the setting <em>plugins.zm_subscriptions.schedules</em>.</p>
     *
     * @return array Hash map of schedule key => name.
     */
    public function getSchedules() {
        $defaults = array(
            '1w' => array('name' => 'Weekly', 'active' => true),
            '10d' => array('name' => 'Every 10 days', 'active' => true),
            '4w' => array('name' => 'Every four weeks', 'active' => true),
            '1m' => array('name' => 'Once a month', 'active' => true)
        );
        return ZMSettings::get('plugins.zm_subscriptions.schedules', $defaults);
    }

    /**
     * Order created event handler.
     */
    public function onZMCreateOrder($args=array()) {
        if ($args['source'] instanceof ZMUpdateSubscriptionsCronJob) {
            // do not process orders created by our cron job
            return;
        }

        $orderId = $args['orderId'];
        if (null != ($schedule = $this->getSelectedSchedule())) {
            $sql = "UPDATE " . TABLE_ORDERS . "
                    SET subscription_next_order = DATE_ADD(date_purchased, INTERVAL " . self::schedule2SQL($schedule) . "),
                      is_subscription = :subscription, is_subscription_canceled = :subscriptionCanceled, subscription_schedule = :schedule
                    WHERE orders_id = :orderId";
            $args = array('orderId' => $orderId, 'subscription' => true, 'subscriptionCanceled' => false, 'schedule' => $schedule);
            Runtime::getDatabase()->update($sql, $args, TABLE_ORDERS);

            if (ZMLangUtils::asBoolean($this->get('subscriptionComment'))) {
                if (null != ($order = ZMOrders::instance()->getOrderForId($orderId))) {
                    $status = ZMLoader::make('OrderStatus');
                    $status->setOrderStatusId($order->getOrderStatusId());
                    $status->setOrderId($order->getId());
                    $status->setCustomerNotified(false);
                    $schedules = $this->getSchedules();
                    $status->setComment(zm_l10n_get('Subscription: %s', $schedules[$schedule]['name']));
                    ZMOrders::instance()->createOrderStatusHistory($status);
                }
            }
        }
    }

    /**
     * Convert UI schedule value into something useful for SQL.
     *
     * @param string schedule The schedule value.
     * @param int factor Optional factor to get multiple of the single interval; default is <em>1</em>.
     * @return string A string that can be used in SQL <em>DATE_ADD</em>.
     */
    public static function schedule2SQL($schedule, $factor=1) {
        $schedule = preg_replace('/[^0-9dwmy]/', '', $schedule); 
        $schedule = str_replace(array('d', 'w', 'm', 'y'), array(' DAY', ' WEEK', ' MONTH', ' YEAR'), $schedule);
        if (1 < $factor) {
            // multiply
            $bits = explode(' ', $schedule);
            $schedule = ($factor * (int)$bits[0]) . ' ' . $bits[1];

        }
        return $schedule;
    }

    /**
     * Get the scheduled orderIds for a given subscription order id.
     *
     * @param int orderId The original subscription order.
     * @return array List of order ids.
     */
    public function getScheduledOrderIdsForSubscriptionOrderId($orderId) {
        $sql = "SELECT orders_id
                FROM " . TABLE_ORDERS . "
                WHERE subscription_order_id = :subscriptionOrderId";
        $results = array();
        foreach (Runtime::getDatabase()->query($sql, array('subscriptionOrderId' => $orderId), TABLE_ORDERS) as $result) {
            $results[] = $result['orderId'];
        }

        return $results;
    }

    /**
     * Get the minimum last order date for a subscription (the earliest cancel date).
     *
     * @param int orderId The original subscription order id.
     * @return string The date or <code>null</code> (if not canceled).
     */
    public function getMinLastOrderDate($orderId) {
        $order = ZMOrders::instance()->getOrderForId($orderId);

        // let's find out how many more orders need to be shipped to pass the minOrders restriction
        $scheduledOrderIds = $this->getScheduledOrderIdsForSubscriptionOrderId($orderId);
        $missing = $this->get('minOrders') - count($scheduledOrderIds);
        if (0 >= $missing) {
            // in the case of old orders this should be the date of the last actually shipped order
            return $order->getNextOrder();
        }
        // multiply the schedule by missing
        $distance = self::schedule2SQL($order->getSchedule(), $missing);

        // use SQL to calculate the last date
        $sql = "SELECT DATE_ADD(subscription_next_order, INTERVAL " . $distance . ") as subscription_next_order
                FROM " . TABLE_ORDERS . "
                WHERE orders_id = :orderId";
        $result = Runtime::getDatabase()->querySingle($sql, array('orderId' => $orderId), TABLE_ORDERS);

        return $result['nextOrder'];
    }

}

?>
