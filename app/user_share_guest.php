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
use \OCA\User_Share_Guest\Controller\GuestController;
use \OCA\User_Share_Guest\Controller\PageController;
use \OCA\User_Share_Guest\Db\GuestMapper;
use \OCA\User_Share_Guest\Hooks\GuestHooks;
use \OCA\User_Share_Guest\Service\MailService;
//use \OCA\User_Share_Guest\Middleware\GuestMiddleware;

class User_Share_Guest extends App {

    public function __construct(array $urlParams=array()) {

        parent::__construct('user_share_guest', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('GuestController', function($c){
            return new GuestController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('L10N'),
                $c->query('GuestMapper'),
                $c->query('UserId'),
                $c->query('UserManager'),
                $c->query('MailService')
            );
        });

        $container->registerService('PageController', function($c){
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('L10N'),
                $c->query('GuestMapper'),
                $c->query('UserId'),
                $c->query('UserManager'),
                $c->query('ServerContainer')->getURLGenerator()
            );
        });

        /**
         * Database Layer
         */
        $container->registerService('GuestMapper', function($c) {
            return new GuestMapper(
                $c->query('ServerContainer')->getDb(),
                $c->query('L10N')
            );
        });

        /**
         * Hooks
         */

        $container->registerService('GuestHooks', function($c){
            return new GuestHooks(
                $c->query('GuestMapper'),
                $c->query('UserManager'),
                $c->query('Session')
            );
        });

        /**
         * Services
         */
        $container->registerService('MailService', function($c){
            return new MailService(
                $c->query('AppName'),
                $c->query('UserId'),
                $c->query('L10N'),
                $c->query('Config'),
                $c->query('UserManager'),
                $c->query('ServerContainer')->getURLGenerator()
            );
        });

        /**
         * Middleware
         */

        /*
        $container->registerService('GuestMiddleware', function($c){
            return new GuestMiddleware();
        });

        $container->registerMiddleware('GuestMiddleware');
        */

        /**
         * Core
         */
        $container->registerService('UserId', function($c) {
            return \OCP\User::getUser();
        });

        $container->registerService('L10N', function($c) {
            return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });

        $container->registerService('UserManager', function($c) {
            return $c->query('ServerContainer')->getUserManager();
        });

        $container->registerService('Config', function($c) {
            return $c->query('ServerContainer')->getConfig();
        });

        $container->registerService('Session', function($c) {
            return $c->query('ServerContainer')->getSession();
        });

    }
}
