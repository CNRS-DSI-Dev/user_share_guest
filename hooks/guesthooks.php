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

    public function __construct($guestMapper) {
            $this->guestMapper = $guestMapper;
    }

    /**
     * Hooks registration
     *
     * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
     * @copyright 2015 CNRS DSI / GLOBALIS media systems
     *
     */
    public function register() {
       \OCP\Util::connectHook('OCP\Share', 'post_shared', $this, 'replaceShareStatut');
    }

    public function replaceShareStatut ($data) {
        $uid = $data['shareWith'];
        $guest = $this->guestMapper->getGuests($uid);
        if (!empty($guest)) {
            $this->guestMapper->updateGuestShareStatut($uid, $data['uidOwner']);
        }
    }
}
