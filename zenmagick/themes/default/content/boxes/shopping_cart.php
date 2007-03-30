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

<?php if (!$zm_cart->isEmpty() && !zm_is_checkout_page()) { ?>
    <h3><?php zm_l10n("Shopping Cart") ?></h3>
    <div id="sb_cart" class="box">
    <?php foreach ($zm_cart->getItems() as $item) { ?>
        <?php echo $item->getQty(); ?> x <a href="<?php zm_product_href($item->getId()) ?>"><?php echo $item->getName(); ?></a><br />
    <?php } ?>
    <hr/>
    <p><?php zm_format_currency($zm_cart->getTotal()) ?></p>
    </div>
<?php } ?>
