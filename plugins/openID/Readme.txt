A plugin to allow users to login using OpenID.
The plugin uses the excellent library openidenabled by JanRain (http://www.openidenabled.com/)
(version 2.1.3, but without the various store implementations and server code)
The current version is based on the code found at: http://sourcecookbook.com/en/recipes/60/janrain-s-php-openid-library-fixed-for-php-5-3-and-how-i-did-it
The changes are required for PHP5.3 compatibility.


Installation
============
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Optionally, configure a list of accepted OpenID provider (separated by  a pipe symbol '|').


Usage
=====
To enable users to use their OpenID the following two templates need customization:

login.php
The login view needs a new form to allow users to enter and submit their OpenID.
NOTE: The form id and form fields are expected to have the shown names/ids. Do not change!

Example:

  <h3><?php _vzm("Use OpenID") ?></h3>
  <?php  $form->open('openID', '', true, array('id'=>'openid_login')) ?>
    <fieldset>
      <div>
        <label for="openid"><?php _vzm("OpenID") ?></label>
        <input type="hidden" name="action" value="initAuth" /> 
        <input type="text" id="openid" name="openid" /> 
        <input type="submit" class="btn" value="<?php _vzm("Login") ?>" />
      </div>
    </fieldset>
  </form>


account_edit.php
Here a new field to enter an OpenID needs to be added:

Example:

  <tr>
      <td><?php _vzm("OpenID") ?></td>
      <td><input type="text" name="openid" value="<?php $html->encode($account->get('openid')) ?>" /></td>
  </tr>


The plugin will ensure that an OpenID can not be assigned to more than one account. Once a user has verfied that
he owns the provided OpenID, the associated user  will be logged in.


File modifications / SQL
========================
* The plugin does not modify any existing files.
* Two new tables will be created
* The customers table will be extended with a new column 'openid'
* A new validation ruleset will be added dynamically to make the openid field required.
* A new validation rule will be added to the account_edit validation rules to ensure that an
  OpenID can't be assigned to more than one account.
