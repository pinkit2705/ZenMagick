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

<?php zm_add_product_form($zm_product->getId(), 1) ?>
  <div>  
    <div id="pinfo">
      <?php $imageInfo = $zm_product->getImageInfo() ?>
      <a href="<?php zm_product_href($zm_product->getId()) ?>"><?php zm_product_image($zm_product) ?></a>
      <?php echo $zm_product->getDescription(); ?>
    </div>
    <strong><?php zm_format_currency($zm_product->getPrice()); ?></strong>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Add to cart") ?>" /></div>

    <p id="author">
      <?php zm_l10n("Review by: %s", $zm_review->getAuthor()) ?>
      <?php $rtext = zm_l10n_get("%s of 5 stars!", $review->getRating()) ?>
      <span id="stars">
        <?php echo zm_image('stars_'.$review->getRating().'.gif', $rtext) ?>
        <?php echo $rtext ?>
      </span>
    </p>
    <div id="rlongtext">
        <?php echo $zm_review->getText() ?>
    </div>
  </div>
</form>
