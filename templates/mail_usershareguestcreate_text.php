<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

    $l = $_['overwriteL10N'];
    print_unescaped($l->t("Hello,") . "\n\n");
    print_unescaped($l->t("The user %s wish you invite and share files with you on MyCore.", array($_['sharerUid'])) . "\n");
    print_unescaped($l->t('Accept the invitation : ') . $_['accountUrl'] . "\n");
	print_unescaped($l->t("Your login is the e-mail address by which you have been contacted") . "\n");

?>


--
<?php p($theme->getName() . ' - ' . $theme->getSlogan()); ?>
<?php print_unescaped("\n".$theme->getBaseUrl());
