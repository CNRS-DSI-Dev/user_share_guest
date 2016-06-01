<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Hooks;

class GuestHooks {

    private $guestMapper;
    private $userManager;

    public function __construct($guestMapper, $userManager) {
        $this->guestMapper = $guestMapper;
        $this->userManager = $userManager;
    }

    /**
     * Hooks registration
     *
     * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
     * @copyright 2016 CNRS DSI / GLOBALIS media systems
     *
     */
    public function register() {
        $myself = $this;
        \OCP\Util::connectHook('OCP\Share', 'post_shared', $this, 'postShared');

        $this->userManager->listen('\OC\User', 'postLogin', function(\OC\User\User $user) use ($myself) {
            return $this->postLogin($user);
        });
    }

    public function postShared ($data) {
        $uid = $data['shareWith'];
        $guest = $this->guestMapper->getGuests($uid);
        if (!empty($guest)) {
            $this->guestMapper->updateGuestShareStatut($uid, $data['uidOwner']);
        }
    }

    public function postLogin ($user) {
        if (!$this->guestMapper->hasGuestAccepted() && $this->guestMapper->getGuests($user->getUid())) {
            \OCP\User::logout();
            \OC_Util::redirectToDefaultPage();
            exit();
       }
    }
}
