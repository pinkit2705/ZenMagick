<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

<?php $form->open(FILENAME_ACCOUNT_EDIT, "action=process", true, array('id'=>'edit_account', 'onsubmit'=>'return validate(this);')) ?>
    <fieldset>
        <legend><?php zm_l10n("My Account") ?></legend>
        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                   <th id="label"></th>
                   <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (ZMSettings::get('isAccountGender')) { ?>
                    <tr>
                        <td><?php zm_l10n("Title") ?><span>*</span></td>
                        <td>
                            <input type="radio" id="male" name="gender" value="m"<?php $form->checked('m', $zm_account->getGender()) ?> />
                            <label for="male"><?php zm_l10n("Mr.") ?></label>
                            <input type="radio" id="female" name="gender" value="f"<?php $form->checked('f', $zm_account->getGender()) ?> />
                            <label for="female"><?php zm_l10n("Ms.") ?></label>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("First Name") ?><span>*</span></td>
                    <td><input type="text" name="firstname" value="<?php $html->encode($zm_account->getFirstName()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Last Name") ?><span>*</span></td>
                    <td><input type="text" name="lastname" value="<?php $html->encode($zm_account->getLastName()) ?>" /></td>
                </tr>
                <?php if (ZMSettings::get('isAccountDOB')) { ?>
                    <tr>
                        <td><?php zm_l10n("Date of Birth") ?><span>*</span></td>
                        <td><input type="text" name="dob" value="<?php $date->shortDate($zm_account->getDOB()) ?>" /> <?php zm_l10n("Format: %s;&nbsp;(e.g: %s)", UI_DATE_FORMAT, UI_DATE_FORMAT_SAMPLE) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email_address" value="<?php $html->encode($zm_account->getEmail()) ?>" /></td>
                </tr>
                <?php if (ZMSettings::get('isAccountNickname')) { ?>
                    <tr>
                        <td><?php zm_l10n("Nickname") ?></td>
                        <td><input type="text" name="nick" value="<?php $html->encode($zm_account->getNickName()) ?>" /></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php zm_l10n("Telephone Number") ?><span>*</span></td>
                    <td><input type="text" name="telephone" value="<?php $html->encode($zm_account->getPhone()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php zm_l10n("Fax Number") ?></td>
                    <td><input type="text" name="fax" value="<?php $html->encode($zm_account->getFax()) ?>" /></td>
                </tr>
                 <tr>
                    <td><?php zm_l10n("E-Mail Format") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="html" name="email_format" value="HTML"<?php $form->checked('HTML', $zm_account->getEmailFormat(), 'HTML') ?> />
                        <label for="html"><?php zm_l10n("HTML") ?></label>
                        <input type="radio" id="text" name="email_format" value="TEXT"<?php $form->checked('TEXT', $zm_account->getEmailFormat(), 'TEXT', true) ?> />
                        <label for="text"><?php zm_l10n("Text") ?></label>
                    </td>
                </tr>
                <tr class="legend">
                    <td colspan="2"><?php zm_l10n("<span>*</span> Mandatory fields") ?></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Update") ?>" /></div>
</form>
