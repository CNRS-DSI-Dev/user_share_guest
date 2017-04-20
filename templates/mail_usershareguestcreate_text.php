<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

    $l = $_['overwriteL10N'];
    p($l->t('Hello,'));
    p($l->t('The user %s wish you invite and share files with you on MyCore.', array($_['sharerUid'])));
    p($l->t('Accept the invitation : ')) . ' ' . $_['accountUrl'];
	p($l->t('Your login is the e-mail address by which you have been contacted'));
    
?>


--
<?php p($theme->getName() . ' - ' . $theme->getSlogan()); ?>
<?php print_unescaped("\n".$theme->getBaseUrl());
