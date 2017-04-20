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

    private $appName;
    private $guestMapper;
    private $userManager;
    private $session;
    private $l;

    public function __construct($appName, $guestMapper, $userManager, $session, $l) {
        $this->appName = $appName;
        $this->guestMapper = $guestMapper;
        $this->userManager = $userManager;
        $this->session = $session;
        $this->l = $l;
    }

    /**
     * Hooks registration
     *
     * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
     * @copyright 2016 CNRS DSI / GLOBALIS media systems
     *
     */
    public function register($session) {
        $myself = $this;
        \OCP\Util::connectHook('OCP\Share', 'post_shared', $this, 'postShared');
        \OCP\Util::connectHook('\OC\User', 'postSetPassword', $this, 'postPwdSetted');
        $session->listen('\OC\User', 'postDelete', function ($user) {
            $this->postDeleteUser($user);
        });
        \OCP\Util::connectHook('\OC\User', 'preDelete', $this, 'preDeleteUser');
        
    }

    public function postShared ($data) {
        \OCP\Util::writeLog($this->appName, $this->l->t('hook post shared actived'), 1);
        $uid = $data['shareWith'];
        $guest = $this->guestMapper->getGuests($uid);
        if (!empty($guest)) {
            $this->guestMapper->saveGuestSharer($data['shareWith'], $data['uidOwner'], $data['itemType'], $data['itemSource']);
            $this->guestMapper->updateGuestShareStatut($uid, $data['uidOwner']);
        }
    }

    public function postPwdSetted($params) {
        \OCP\Util::writeLog($this->appName, $this->l->t('Activation guest account'), 1);
        $uid = $params['uid'];

        $guest = $this->guestMapper->getGuests($uid);
        $isActive = $this->guestMapper->isGuestActive($uid);
        if (!empty($guest) && $isActive === false) {
            $this->guestMapper->updateGuest($uid, array('accepted' => 1, 'is_active' => 1));
            \OCP\Util::writeLog($this->appName, $this->l->t('Guest\'s password setted and account activated'), 1);
            if (!\OCA\User_Share_Guest\Controller\GuestController::isAccountReseda($uid)) {
                $filesystem = \OC\Files\Filesystem::init($uid, '/');
                \OC\Files\Filesystem::unlink($uid . '/files/welcome.txt');
            } else {
                $this->guestMapper->deleteGuest($uid);
            }
        }
    }

    public function postDeleteUser($user) {
        $uid = $user->getUid();
        $guest = $this->guestMapper->getGuests($uid);
        if ($guest) {
            $this->guestMapper->deleteGuest($uid);
        }
    }
}
