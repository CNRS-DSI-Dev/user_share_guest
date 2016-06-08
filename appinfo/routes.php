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
        // GUEST CONTROLLER
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
            'name' => 'guest#share_list',
            'url' => '/share_list_user',
            'verb' => 'GET',
        ),
        array(
            'name' => 'guest#is_guest_creation',
            'url' => '/is_guest_creation',
            'verb' => 'GET',
        ),
        array(
            'name' => 'guest#test',
            'url' => '/test/{data}',
            'verb' => 'GET',
        ),
        // PAGE CONTROLLER
        array(
            'name' => 'page#confirm',
            'url' => '/confirm/{uid}/{token}',
            'verb' => 'GET'
        ),
        array(
            'name' => 'page#accept',
            'url' => '/confirm/{uid}/{token}',
            'verb' => 'POST'
        ),
        array(
            'name' => 'page#share_list',
            'url' => '/share_list',
            'verb' => 'GET'
        ),
    ),
));
