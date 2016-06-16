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
    private $session;

    public function __construct($guestMapper, $userManager, $session) {
        $this->guestMapper = $guestMapper;
        $this->userManager = $userManager;
        $this->session = $session;
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
            //$this->guestMapper->saveGuestSharer($params['uid_guest'], $params['uid_sharer'], $params['item_type'], $params['item_source']);
            $this->guestMapper->updateGuestShareStatut($uid, $data['uidOwner']);
        }
    }

    public function postLogin ($user) {
        /*var_dump($this->guestMapper->getGuests($user->getUid()));exit();
        if ($this->guestMapper->getGuests($user->getUid()) && !$this->guestMapper->isGuestActive()) {
            \OCP\User::logout();
            \OC_Util::redirectToDefaultPage();
            exit();
       }*/
    }
}
