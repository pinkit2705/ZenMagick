<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

<fieldset>
  <legend><?php echo _zm('How did you hear about us') ?></legend>
  <p>
    <label for="source" ><?php echo _zm('Please select a source:') ?></label>
    <?php echo $form->idpSelect('source', $howDidYouHearSources, $registration->getSourceId()) ?>
  </p>

  <?php if ($howDidYouHear->isDisplayOther()) { ?>
    <p>
      <label for="source_other" ><?php echo _zm('(if "Other" please specify):') ?></label>
      <input type="text" name="source_other" id="source_other" value="<?php echo ($howDidYouHear->isRequired() ? '*' : '') ?>">
    </p>
  <?php } ?>
</fieldset>
