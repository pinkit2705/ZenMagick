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

<?php zm_form(FILENAME_GV_SEND, 'action=process') ?>
    <div>
        <input type="hidden" name="send_name" value="<?php echo $zm_account->getFullName() ?>" />
        <input type="hidden" name="to_name" value="<?php echo $zm_gvreceiver->getName() ?>" />
        <input type="hidden" name="email" value="<?php echo $zm_gvreceiver->getEmail() ?>" />
        <input type="hidden" name="amount" value="<?php echo $zm_gvreceiver->getAmount() ?>" />
        <input type="hidden" name="message" value="<?php echo $zm_gvreceiver->getMessage() ?>" />
    </div>
    <fieldset>
        <legend><?php zm_l10n("Confirm Send Gift Certificate") ?></legend>

        <p class="note"><?php zm_l10n("You are about to post a Gift Certificate worth %s to %s whose email address is %s.",
          zm_format_currency($zm_gvreceiver->getAmount(), false), $zm_gvreceiver->getName(), $zm_gvreceiver->getEmail()) ?>
        </p>

        <fieldset>
            <legend><?php zm_l10n("Your message") ?></legend>
            <p>
              <?php zm_l10n(zm_setting('gvSendMainMessage'), $zm_gvreceiver->getName(), zm_format_currency($zm_gvreceiver->getAmount(), false), $zm_account->getFullName()) ?>
              <?php if ($zm_gvreceiver->hasMessage()) { ?>
                <br/>
                <?php zm_l10n(zm_setting('gvSendPersonalMessage'), $zm_account->getFirstName()); ?>
                <?php echo $zm_gvreceiver->getMessage() ?>
              <?php } ?>
            </p>
        </fieldset>

        <p class="note"><?php zm_l10n("If these details are not correct, you may edit your message by clicking the edit button.") ?></p>
    </fieldset>

    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send Gift Certificate") ?>" /></div>
    <div><input type="submit" class="btn" name="edit" value="<?php zm_l10n("Edit") ?>" /></div>
</form>

<p>
    <strong><?php zm_l10n("This message is included with all emails sent from this site:") ?></strong><br />
    <?php echo str_replace('-', '', EMAIL_ADVISORY) ?>
</p>
