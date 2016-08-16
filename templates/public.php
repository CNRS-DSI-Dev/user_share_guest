<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

?>
<div id="guestConfirm">

    <p><?php p($l->t('Set your password to confirm the creation of your account')); ?></p>
    <p class="notification"></p>
    <form action="" method="POST" id="set_guest_password">
        <input type="hidden" name="uid" value="<?= $_['uid'] ?>"/>
        <div class="grouptop">
            <label class="infield" for="password"><?php p($l->t('Set your password')); ?></label>
            <input type="password" name="password" id="password" placeholder="<?php p($l->t('Set your password')); ?>" required="1" />
            <img class="svg password-icon" src="/victor/cnrs_mycore/depot/core/img/actions/password.svg" alt="">
        </div>
        <div class="groupbottom">
            <label class="infield" for="passwordconfirm"><?php p($l->t('Confirm your password')); ?></label>
            <input type="password" name="passwordconfirm" id="passwordconfirm" placeholder="<?php p($l->t('Confirm your password')); ?>" required="1" />
            <img class="svg password-icon" src="/victor/cnrs_mycore/depot/core/img/actions/password.svg" alt="">
        </div>
        <input type="submit" id="submit" class="primary" value="<?php p($l->t('Validate')); ?>" />
    </form>
</div>

<?php
\OCP\Util::addScript('user_share_guest','public');

