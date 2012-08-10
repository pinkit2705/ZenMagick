<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

// @todo this is too naive. do better
if (!defined('SEND_EMAILS')) {
    return;
}

$config['disable_delivery'] = SEND_EMAILS != 'true';

// @todo we should make this available with the rest of the email parameters.
if (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && '' != DEVELOPER_OVERRIDE_EMAIL_ADDRESS) {
    $config['delivery_address'] =  DEVELOPER_OVERRIDE_EMAIL_ADDRESS;
}

$transport = EMAIL_TRANSPORT;
if ('PHP' == $transport) {
    $config['transport'] = 'mail';
}
if (in_array($transport, array('sendmail', 'sendmail-f', 'Qmail'))) {
    $config['transport'] = 'sendmail';
};
if ('smtp.gmail.com' == EMAIL_SMTPAUTH_MAIL_SERVER) {
    $config['transport'] = 'gmail';
}
if (in_array($transport, array('smtp', 'smtpauth'))) {
    $config['transport'] = 'smtp';
    $config['host'] = EMAIL_SMTPAUTH_MAIL_SERVER;
    $config['port'] = false;
    if ('' != ($port = EMAIL_SMTPAUTH_MAIL_SERVER_PORT)) {
        $config['port'] = $port;
    }
    if (in_array($port, array(465, 587))) {
        $config['encryption'] = 'ssl';
    }
}

if ('' != trim(EMAIL_SMTPAUTH_MAILBOX)) {
    $config['username'] =  EMAIL_SMTPAUTH_MAILBOX;
    $config['password'] =  EMAIL_SMTPAUTH_PASSWORD;

}
$container->loadFromExtension('swiftmailer', $config);