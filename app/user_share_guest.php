<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\App;

use \OCP\AppFramework\App;

class User_Share_Guest extends App {

    public function __construct(array $urlParams=array()) {
        parent::__construct('user_share_guest', $urlParams);

        $container = $this->getContainer();

        /**
         * Core
         */

        $container->registerService('Config', function($c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $container->registerService('L10N', function($c) {
            return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });
        $container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });

        $container->registerService('UserManager', function($c) {
            return $c->query('ServerContainer')->getUserManager();
        });

        $container->registerService('GroupManager', function($c) {
            return $c->query('ServerContainer')->getGroupManager();
        });

    }
}
