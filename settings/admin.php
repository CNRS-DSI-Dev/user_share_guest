<?php
/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest;

use OCA\User_Share_Guest\App\User_Share_Guest;

\OC_Util::checkAdminUser();

$app = new User_Share_Guest;
$c = $app->getContainer();

$appConfig = \OC::$server->getAppConfig();
$l = $app->getContainer()->query('L10N');
$error = '';
$days = $appConfig->getValue('user_share_guest', 'user_share_guest_days', 5);

// saving data
if (!empty($_POST)) {
    if (isset($_POST['usershareguest-days']) && is_numeric(trim($_POST['usershareguest-days']))) {
        $appConfig->setValue('user_share_guest', 'user_share_guest_days', intval($_POST['usershareguest-days']));
        $days = trim($_POST['usershareguest-days']);
    } else {
        $error = $l->t('Input error, please enter a whole number.');
    }
}

$tmpl = new \OCP\Template($c->query('AppName'), 'settings-admin');
$tmpl->assign('usershareguest-days', $days);
$tmpl->assign('usershareguest-error', $error);

return $tmpl->fetchPage();
