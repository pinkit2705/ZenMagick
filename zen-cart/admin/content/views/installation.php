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
 * $Id: zmInstallation.php 2647 2009-11-27 00:30:20Z dermanomann $
 */
?>
<?php

    // locale
    $patchLabel = array(
        "adminMenu" => "Install ZenMagick admin menu",
        "themeSupport" => "Patch zen-cart to enable ZenMagick request handling (aka ZenMagick themes)",
        "noThemeSupport" => "Patch zen-cart to use ZenMagick <strong>without</strong> ZenMagick themes",
        "themeDummies" => "Create admin dummy files for all installed ZenMagick themes",
        "sideboxDummies" => "Create dummy files for all (side)boxes of the current ZenMagick theme",
        "i18nSupport" => "Disable zen-cart's <code>zen_date_raw</code> function in favour of a ZenMagick implementation",
        "linkGeneration" => "Disable zen-cart's <code>zen_href_link</code> function in favour of a ZenMagick implementation",
        "email" => "Disable zen-cart's <code>zen_mail</code> function in favour of a ZenMagick implementation",
        "eventProxy" => "Patch zen-cart to activate the ZenMagick event proxy service (required for some emails and guest checkout!)",
        "customerEdit" => "Patch zen-cart to allow editing customers where email also exists as guest account",
        "couponAdminMail" => "Patch zen-cart to allow use of ZenMagick email templates for coupon admin mail",

        "rewriteBase" => "Update RewriteBase value in .htaccess (pretty links, SEO)",

        "dynamicAdmin" => "Disable zen-cart admin header/footer (use zmAdmin.php instead of index.php)",

        "sqlConfig" => "Setup ZenMagick config groups and initial values",
        "sqlToken" => "Create the database table used by the token service"
    );

    $coreCompressor = new ZMCoreCompressor();
    $installer = new ZMInstallationPatcher();
    $obsolete = zm_get_obsolete_files();
    $needRefresh = false;

    // install
    if (null != $request->getParameter('update')) {
        $group = $request->getParameter('update');
        foreach ($installer->getPatches($group) as $id => $patch) {
            $formId = 'patch_'.$group.'_'.$patch->getId();
            if ($patch->isOpen() && $patch->getId() == $request->getParameter($formId)) {
                // open and selected
                $needRefresh = true;
                $status = $patch->patch(true);
                ZMMessages::instance()->addAll($patch->getMessages());
                if ($status) {
                    ZMMessages::instance()->success("'".$patchLabel[$patch->getId()]."' installed successfully");
                } else {
                    ZMMessages::instance()->error("Could not install '".$patchLabel[$patch->getId()]."'");
                }
            } else if (!$patch->isOpen() && null == $request->getParameter($formId)) {
                // installed and not selected
                if ($patch->canUndo()) {
                    $needRefresh = true;
                    $status = $patch->undo();
                    ZMMessages::instance()->addAll($patch->getMessages());
                    if ($status) {
                        ZMMessages::instance()->success("Uninstalled '".$patchLabel[$patch->getId()]."' successfully");
                    } else {
                        ZMMessages::instance()->error("Could not uninstall '".$patchLabel[$patch->getId()]."'");
                    }
                }
            }
        }
    }

    // delete
    if (null != $request->getParameter('obsolete')) {
        foreach ($request->getParameter('obsolete') as $file) {
            if (is_file($file)) {
                unlink($file);
            } else if (is_dir($file)) {
                rmdir($file);
            }
        }
        $needRefresh = true;
    }

    // update core.php
    if (isset($_POST)) {
        $didGenerate = false;
        $coreCompressor->setDebug(!ZMSettings::get('isStripCore'));
        if (array_key_exists('singleCore', $_POST) && !$coreCompressor->isEnabled()) {
            // allow for more time to run tests
            set_time_limit(300);
            $coreCompressor->packFiles(ZMSettings::get('isStripCore'));
            $didGenerate = true;
        }
        if (array_key_exists('singleCoreGenerate', $_POST)) {
            // allow for more time to run tests
            set_time_limit(300);
            $coreCompressor->packFiles(ZMSettings::get('isStripCore'));
            $didGenerate = true;
        }

        if ($coreCompressor->hasErrors()) {
            foreach ($coreCompressor->getErrors() as $msg) {
                ZMMessages::instance()->error($msg);
            }
        } else if ($didGenerate) {
            ZMMessages::instance()->success("Succsesfully (re-)generated core.php");
        }

        if (array_key_exists('optimize', $_POST) && !array_key_exists('singleCore', $_POST)) {
            $coreCompressor->disable();
            ZMMessages::instance()->msg("Disabled usage of core.php");
        }
    }

    /**
     * Show patch group.
     */
    function _zm_patch_group($groupId, $patchLabel, $checkall=true) {
        $installer = new ZMInstallationPatcher();
        foreach ($installer->getPatches($groupId) as $id => $patch) {
            // check dependencies
            $unfulfilled = array();
            foreach ($patch->dependsOn() as $dId) {
                $dPatch = $installer->getPatchForId($dId);
                if ($dPatch->isOpen()) {
                    array_push($unfulfilled, $dPatch->getId());
                }
            }
            foreach ($unfulfilled as $dId) {
                ?><p class="error"><?php zm_l10n("Depends on: '%s'", $patchLabel[$dId]) ?></p><?php
            }
            if (!$patch->isReady() && $patch->isOpen()) {
              ?><p class="error"><?php echo $patch->getPreconditionsMessage() ?></p><?php
            }
            ?><input type="checkbox"
                id="<?php echo $patch->getId() ?>" name="patch_<?php echo $groupId ?>_<?php echo $patch->getId() ?>"
                value="<?php echo $patch->getId() ?>"
                <?php if (!$patch->isOpen()) { ?>checked="checked" <?php } ?>
                <?php if (!$patch->canUndo() && !$patch->isOpen()) { ?>disabled="disabled" <?php } ?>>
              <label for="<?php echo $patch->getId() ?>">
                  <?php echo $patchLabel[$patch->getId()] ?>
              </label>
              <br><?php
        } ?>
        <input type="checkbox" class="all" id="<?php echo $groupId ?>_all" name="<?php echo $groupId ?>_all" value="" onclick="sync_all(this, 'patch_<?php echo $groupId ?>_')">
        <label for="<?php echo $groupId ?>_all"><?php zm_l10n("Select/Unselect All") ?></label><br>
        <div class="submit">
            <input type="submit" value="<?php zm_l10n("Update") ?>">
        </div>
    <?php }

    if ($needRefresh) {
        $request->redirect($toolbox->admin->url(null, '', true));
    }

?>
    <script type="text/javascript">
      // select/unselect all
      function sync_all(box, name) {
        var boxes = document.getElementsByTagName('input');
        for (var ii=0; ii<boxes.length; ++ii) {
          if (0 == boxes[ii].name.indexOf(name) && !boxes[ii].disabled) {
            boxes[ii].checked = box.checked;
          }
        }
      }
    </script>

<div id="b_installation">
  <h2><?php zm_l10n("ZenMagick Installation") ?> <a class="btn" href=""><?php zm_l10n("Refresh Page") ?></a></h2>

  <form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zm_user_confirm('Update File Patches?');">
    <fieldset class="patches">
      <legend><?php zm_l10n("ZenMagick File Patches") ?></legend>
      <input type="hidden" name="update" value="file">
      <?php _zm_patch_group('file', $patchLabel) ?>
    </fieldset>
  </form>

  <form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zm_user_confirm('Update SQL Patches?');">
    <fieldset class="patches">
      <legend><?php zm_l10n("ZenMagick SQL Extensions") ?></legend>
      <input type="hidden" name="update" value="sql">
      <?php _zm_patch_group('sql', $patchLabel) ?>
      <div class="submit">
        <strong>NOTE:</strong> It is <strong>strongly</strong> recommended to backup your database before appying/reverting SQL patches.
      </div>
    </fieldset>
  </form>

  <form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zm_user_confirm('Update selected optimisations?\n(This might take a while...)');">
    <fieldset id="optimisation">
    <legend><?php zm_l10n("Optimising ZenMagick") ?></legend>
        <input type="hidden" id="optimize" name="optimize" value="x">
        <?php $checked = $coreCompressor->isEnabled() ? ' checked="checked"' : ''; ?>
        <input type="checkbox" id="singleCore" name="singleCore" value="x"<?php echo $checked ?>>
        <label for="singleCore"><?php zm_l10n("Use single core.php file"); ?></label>
        <?php if ($coreCompressor->isEnabled()) { ?>
            <input type="checkbox" id="singleCoreGenerate" name="singleCoreGenerate" value="x">
            <label for="singleCoreGenerate"><?php zm_l10n("Regenerate core.php"); ?></label>
        <?php } ?>
        <br>
        <p><?php zm_l10n("This option will compress all files under lib and all <strong>installed</strong> plugins into a single 
        file <code>core.php</code>.
        If you install/uninstall plugins or make any other changes to the core directory you'll need to regenerate <code>core.php</code> in
        order to make these changes become active.") ?></p>
        <div class="submit"><input type="submit" value="<?php zm_l10n("Update") ?>"></div>
    </fieldset>
  </form>

  <form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zm_user_confirm('Delete selected files?');">
    <fieldset id="obsolete">
    <legend><?php zm_l10n("Remove obsolete ZenMagick files") ?></legend>
      <?php if (0 == count($obsolete)) { ?>
      <h3><?php zm_l10n("Congratulations - Your installation appears to be clean!") ?></h3>
      <?php } else { ?>
        <p>This is a list of file <em>ZenMagick</em> considers to be obsolete. The files are not used by ZenMagick any more,
          and unless you have modified them, or are sure that you need them they can safely be removed.</p>
        <p><strong>There might be items on this list that need to be removed manually (for example, directories that are not empty).</strong></p>
        <?php $ii = 0; foreach ($obsolete as $file) { $name = zm_mk_relative($file); ?>
          <input type="checkbox" id="obsolete-<?php echo $ii ?>" name="obsolete[]" value="<?php echo $file ?>">
          <label for="obsolete-<?php echo $ii ?>"><?php echo $name ?></label><br>
        <?php ++$ii; } ?>
        <input type="checkbox" class="all" id="oall" name="oall" value="" onclick="sync_all(this, 'obsolete')">
        <label for="oall"><?php zm_l10n("Select/Unselect All") ?></label><br>
        <div class="submit"><input type="submit" value="<?php zm_l10n("Remove") ?>"></div>
      <?php } ?>
    </fieldset>
  </form>
</div>
