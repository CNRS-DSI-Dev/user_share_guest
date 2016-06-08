<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Middleware;

use OCP\AppFramework\Middleware;

class GuestMiddleware extends Middleware {

    public function __construct() {
       // echo "test";exit();
    }

    public function beforeController($controller, $methodName, $output) {
        echo '<pre>';
        var_dump($controller, $methodName);
        exit();
    }
}
