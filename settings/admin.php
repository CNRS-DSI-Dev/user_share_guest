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


$appConfig = \OC::$server->getAppConfig();
$l = $app->getContainer()->query('L10N');
$error_days = '';
$error_stats = '';
$days = $appConfig->getValue('user_share_guest', 'user_share_guest_days', 5);
$stats = $appConfig->getValue('user_share_guest', 'user_share_guest_stats', '01/01');
$domains = $appConfig->getValue('user_share_guest', 'user_share_guest_domains', '');

// saving data
if (!empty($_POST)) {
    \OCP\Util::callCheck();
    \OCP\Util::writeLog($c->query('AppName'), 'Changing app config', 1);
    if (isset($_POST['usershareguest-days']) && is_numeric(trim($_POST['usershareguest-days']))) {
        $appConfig->setValue('user_share_guest', 'user_share_guest_days', intval($_POST['usershareguest-days']));
        \OCP\Util::writeLog($c->query('AppName'), 'Deletion\'s delay setted', 1);
        $days = trim($_POST['usershareguest-days']);
    } else {
        $error_days = $l->t('Input error, please enter a whole number.');
    }
    $reg_stat = '/([0-9]{2})\/([0-9]{2})/';
    if (isset($_POST['usershareguest-stats']) && preg_match($reg_stat, $_POST['usershareguest-stats'])) {
    	$val_stats = explode('/', $_POST['usershareguest-stats'] );
    	$d = intval($val_stats[0]);
    	$m = intval($val_stats[1]);
    	$y = 2015;
    	if (checkdate($m, $d, $y)) {
    		$appConfig->setValue('user_share_guest', 'user_share_guest_stats', $_POST['usershareguest-stats']);
            \OCP\Util::writeLog($c->query('AppName'), 'Statistics\'s date setted', 1);
	        $stats = trim($_POST['usershareguest-stats']);
    	} else {
    		$error_stats = $l->t('Input error, please enter a correct date.');
    	}
    } else {
    	$error_stats = $l->t('Input error, please enter a correct date.');
    }
    if (empty($error_stats)) {
        \OCP\Util::writeLog($c->query('AppName'), 'App config setted', 1);
    }
}

$tmpl = new \OCP\Template($c->query('AppName'), 'settings-admin');
$tmpl->assign('requesttoken', \OCP\Util::callRegister());
$tmpl->assign('usershareguest-days', $days);
$tmpl->assign('usershareguest-stats', $stats);
$tmpl->assign('usershareguest-domains', $domains);
$tmpl->assign('usershareguest-error-days', $error_days);
$tmpl->assign('usershareguest-error-stats', $error_stats);


$url_stat = $urlGenerator->linkToRoute('user_share_guest.guest.launch_stat');
$url_verif = $urlGenerator->linkToRoute('user_share_guest.guest.launch_verif');
$url_clean = $urlGenerator->linkToRoute('user_share_guest.guest.launch_clean');

$tmpl->assign('usershareguest-link-stat', $url_stat);
$tmpl->assign('usershareguest-link-verif', $url_verif);
$tmpl->assign('usershareguest-link-clean', $url_clean);


return $tmpl->fetchPage();
