<?php
namespace OCA\User_Share_Guest\Cron;

use \OCA\User_Share_Guest\App\User_Share_Guest;

class CronGuest extends \OC\BackgroundJob\TimedJob {

    protected function run($argument) {
        $app = new User_Share_Guest();
        $container = $app->getContainer();
        $container->query('GuestController')->verifyInactive();
        $container->query('GuestController')->clean();
    }
}
