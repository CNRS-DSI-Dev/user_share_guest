<?php

/**
 * ownCloud - User Share Guest Request
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

$app->getContainer()->query('GuestHooks')->register();

\OCP\Util::addStyle('user_share_guest','style');
\OCP\Util::addScript('user_share_guest','script');
