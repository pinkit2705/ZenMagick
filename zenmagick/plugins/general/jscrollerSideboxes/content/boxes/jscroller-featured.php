<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php $products = ZMProducts::instance()->getFeaturedProducts($request->getCategoryId(), 
          MAX_RANDOM_SELECT_FEATURED_PRODUCTS, false, $session->getLanguageId()); ?>
<?php if (0 < count($products)) { ?>
    <h3><a href="<?php echo $net->url(FILENAME_FEATURED_PRODUCTS) ?>"><?php zm_l10n("[More]") ?></a><?php zm_l10n("Featured") ?></h3>
    <div id="sb_featured" class="box">
        <?php if (1 == count($products)) { $product = $products[0]; ?>
            <p><?php echo $html->productImageLink($product) ?></p>
            <p><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($product->getName()) ?></a></p>
            <?php $offers = $product->getOffers(); ?>
            <p><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
        <?php } else { ?>
            <?php // TODO: fix ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $this->asUrl('style_jscroller.css') ?>">
            <script src="<?php echo $this->asUrl('jscroller2-1.5.js') ?>"></script>
            <?php ob_start(); foreach ($products as $product) { ?>
                <p>
                <?php echo $html->productImageLink($product, $request->getCategoryId()) ?><br>
                <a href="<?php echo $net->product($product->getId(), $request->getCategoryId()) ?>"><?php echo $html->encode($product->getName()) ?></a><br>
                <?php echo $macro->productPrice($product) ?>
                </p>
            <?php } $featured = ob_get_clean(); ?>
            <div class="scroller_container">
                <div class="jscroller2_up jscroller2_speed-20 jscroller2_mousemove"><?php echo $featured ?></div>
                <div class="jscroller2_up_endless"><?php echo $featured ?></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
