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

$application = new User_Share_Guest();

$application->registerRoutes($this, array(
    'routes' => array(
        array(
            'name' => 'guest#create',
            'url' => '/create',
            'verb' => 'POST',
        ),
        array(
            'name' => 'guest#delete',
            'url' => '/delete',
            'verb' => 'POST',
        ),
        array(
            'name' => 'guest#list_guests',
            'url' => '/list',
            'verb' => 'GET',
        ),
        array(
            'name' => 'guest#test',
            'url' => '/test/{data}',
            'verb' => 'GET',
        ),
    ),
));
