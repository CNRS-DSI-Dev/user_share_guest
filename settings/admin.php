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
\OCP\Util::addScript('user_share_guest','settings-admin');
\OCP\Util::addStyle('user_share_guest','style');

$app = new User_Share_Guest;
$c = $app->getContainer();
$urlGenerator = $c->query('ServerContainer')->getURLGenerator();

// récupération des variables
$appConfig = \OC::$server->getAppConfig();
$l = $app->getContainer()->query('L10N');
$days = $appConfig->getValue('user_share_guest', 'user_share_guest_days', 5);
$stats = $appConfig->getValue('user_share_guest', 'user_share_guest_stats', '');
$domains = $appConfig->getValue('user_share_guest', 'user_share_guest_domains', '');

$last_stat = $appConfig->getValue('user_share_guest', 'user_share_guest_last_stat', 0);
$last_verif = $appConfig->getValue('user_share_guest', 'user_share_guest_last_verif', 0);
$last_clean = $appConfig->getValue('user_share_guest', 'user_share_guest_last_clean', 0);

$tmpl = new \OCP\Template($c->query('AppName'), 'settings-admin');
$tmpl->assign('requesttoken', \OCP\Util::callRegister());
$tmpl->assign('usershareguest-days', $days);
$tmpl->assign('usershareguest-stats', $stats);
$tmpl->assign('usershareguest-domains', $domains);

// récupération des liens à appeler pour lancer manuellement les crons
$url_stat = $urlGenerator->linkToRoute('user_share_guest.guest.launch_stat');
$url_verif = $urlGenerator->linkToRoute('user_share_guest.guest.launch_verif');
$url_clean = $urlGenerator->linkToRoute('user_share_guest.guest.launch_clean');

$tmpl->assign('usershareguest-link-stat', $url_stat);
$tmpl->assign('usershareguest-last-stat', $last_stat);
$tmpl->assign('usershareguest-link-clean', $url_clean);
$tmpl->assign('usershareguest-last-clean', $last_clean);
$tmpl->assign('usershareguest-link-verif', $url_verif);
$tmpl->assign('usershareguest-last-verif', $last_verif);

return $tmpl->fetchPage();
