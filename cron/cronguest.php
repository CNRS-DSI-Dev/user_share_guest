<?php
namespace OCA\User_Share_Guest\Cron;

use \OCA\User_Share_Guest\AppInfo\User_Share_Guest;

class CronGuest {

    public static function verify() {
        \OCP\Util::writeLog('test', 'coucou', 1);
        $app = new User_Share_Guest();
        $container = $app->getContainer();
        $container->query('GuestController')->verifyInactive();
        $container->query('GuestController')->clean();
    }

    public static function statistics() {
        $appConfig = \OC::$server->getAppConfig();
        $day_stat = $appConfig->getValue('user_share_guest', 'user_share_guest_stats', '01/01');
        if (date('d/m') ==  $day_stat) {
            $app = new User_Share_Guest();
            $container = $app->getContainer();
            $container->query('GuestController')->generateStatistics();
        }
    }
