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

<fieldset>
    <legend><?php zm_l10n("Shopping Cart Contents") ?></legend>
    <table cellpadding="0" cellspacing="0" id="cart">
        <tbody>
        <?php foreach ($zm_cart->getItems() as $item) { ?>
            <tr>
                <td class="itm">
                    <?php echo $item->getQty() ?> x <?php $html->encode($item->getName()) ?>
                    <?php if ($item->hasAttributes()) { ?>
                        <br/>
                        <?php foreach ($item->getAttributes() as $attribute) { ?>
                            <p><span class="attr"><?php $html->encode($attribute->getName()) ?>:</span>
                            <?php $first = true; foreach ($attribute->getValues() as $attributeValue) { ?>
                                <?php if (!$first) { ?>, <?php } ?>
                                <span class="atval"><?php $html->encode($attributeValue->getName()) ?></span>
                            <?php $first = false; } ?>
                            </p>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td class="price">
                    <?php $utils->formatMoney($item->getItemTotal()) ?>
                </td>
            </tr>
        <?php } ?>
          <?php
              $totals = $zm_cart->getTotals();
              foreach ($totals as $total) {
                  $tot = '';
                  if ('total' == $total->getType()) {
                      $tot = ' tot';
                  }
                  ?><tr><td class="total"><?php $html->encode($total->getName()) ?></td><td class="price<?php echo $tot ?>"><?php echo $total->getValue() ?></td></tr><?php
              }
          ?>

        </tbody>
    </table>
</fieldset>

<fieldset>
    <legend><?php zm_l10n("Payment Method") ?></legend>
    <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_PAYMENT, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
    <?php $paymentType = $zm_cart->getPaymentType() ?>
    <?php if (null != $paymentType) { ?>
      <h4><?php $html->encode($paymentType->getName()) ?></h4>
      <?php $fields = $paymentType->getFields();
          if (0 < count($fields)) {
              ?><table cellpadding="0" cellspacing="0"><tbody><?php
              foreach ($fields as $field) {
                ?><tr><td><label><?php echo $field->getLabel() ?></label></td><td><?php echo $field->getHTML() ?></td></tr><?php
              }
              ?></table><?php
            }
      ?>
    <?php } ?>
</fieldset>

<?php if (!$zm_cart->isVirtual()) { ?>
    <fieldset>
        <legend><?php zm_l10n("Shipping") ?></legend>
        <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_SHIPPING, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
        <?php $html->encode($zm_cart->getShippingMethod()) ?><br/>
    </fieldset>
<?php } ?>

<fieldset>
    <legend><?php zm_l10n("Address Information") ?></legend>
    <?php if ($zm_cart->hasShippingAddress()) { ?>
        <div class="oadr">
            <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
            <h4><?php zm_l10n("Shipping Address") ?></h4>
            <?php $macro->formatAddress($zm_cart->getShippingAddress()) ?>
        </div>
    <?php } else { ?>
        <div class="oadr">
            <h4><?php zm_l10n("Shipping Address") ?></h4>
            <?php zm_l10n("N/A") ?>
        </div>
    <?php } ?>
    <div class="oadr snd">
        <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
        <h4><?php zm_l10n("Billing Address") ?></h4>
        <?php $macro->formatAddress($zm_cart->getBillingAddress()) ?>
    </div>
</fieldset>

<?php include $zm_subscriptions->getPluginDir().'templates/checkout_confirmation.php'; ?>

<fieldset>
    <legend><?php zm_l10n("Special instructions or comments") ?></legend>
    <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_PAYMENT, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
    <div><?php $html->encode(!ZMTools::isEmpty($zm_cart->getComment()) ? $zm_cart->getComment() : "None") ?></div>
</fieldset>


<?php $form->open($zm_cart->getOrderFormURL(), '', true) ?>
    <?php $zm_cart->getOrderFormContent() ?>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Confirm to order") ?>" /></div>
</form>
