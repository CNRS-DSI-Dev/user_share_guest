<?php
namespace OCA\User_Share_Guest\Cron;

use \OCA\User_Share_Guest\AppInfo\User_Share_Guest;

class CronGuest {

    public static function verify() {
        $app = new User_Share_Guest();
        $container = $app->getContainer();
        $container->query('GuestController')->verifyInactive();
        $container->query('GuestController')->clean();
    }

    public static function statistics() {
        $app = new User_Share_Guest();
        $container = $app->getContainer();
        $container->query('GuestController')->generateStatistics();
    }
