<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest;

use \OCA\User_Share_Guest\App\User_Share_Guest;

$app = new User_Share_Guest();
$c = $app->getContainer();

/**
 * register personnal settings section
 */

$c->query('GuestHooks')->register();

/**
 *  register personnal scripts
 */
\OCP\Util::addStyle('user_share_guest','style');
\OCP\Util::addScript('user_share_guest','script');

/**
 * register cron
 */

\OCP\Backgroundjob::addRegularTask('\OCA\User_Share_Guest\Cron\GuestCron', 'verify');
\OCP\Backgroundjob::addRegularTask('\OCA\User_Share_Guest\Cron\GuestCron', 'statitstics');

/**
 * register settings
 */
\OCP\App::registerAdmin($c->query('AppName'), 'settings/admin');

$userId = $c->query('UserId');
$guestMapper = $c->query('GuestMapper');

$data = $c->query('L10N')->t('Error : invalid mail.');

// déconnection si plus de partage


/**
 * redirection if the current user is a guest
 */
if ($guestMapper->getGuests($userId)) {
    $app_url = strstr($_SERVER['PHP_SELF'], '/apps/');
    if($app_url) {
        $app_url = substr($app_url, 6);
        if(strstr($app_url, '/')) {
            $app_url = substr($app_url, 0, strpos($app_url, '/'));
        }
        $config = $c->query('Config');

        $forbidden_apps = $config->getSystemValue('user_share_guest_forbidden_apps');
        $redirect = false;


        foreach ($forbidden_apps as $app) {
            if ($app == $app_url) {
                $redirect = true;
            }
        }
        // cas ajax
        if (strstr($_SERVER['PHP_SELF'], '/ajax/')) {
            $redirect = false;
        }

        if ($redirect) {
            $urlGenerator = $c->query('ServerContainer')->getURLGenerator();
            $url = $urlGenerator->linkTo('user_share_guest','index.php');
            $url = $urlGenerator->getAbsoluteURL($url);
            header('Location: ' . $url);
            exit();
        }

    }

    // js particulier pour la partie setting
    if (strstr($_SERVER['PHP_SELF'], '/settings/')) {
        \OCP\Util::addScript('user_share_guest','settings');
    }


}
