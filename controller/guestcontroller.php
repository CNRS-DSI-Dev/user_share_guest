<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Controller;

use \OCP\AppFramework\APIController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IL10N;
use \OCA\User_Share_Guest\Db\GuestMapper;
use \OCA\User_Share_Guest\Db\Guest;

class GuestController extends APIController
{

    protected $guestMapper;
    protected $userManager;
    protected $userId;

    const PERMISSION_GUEST = 1;

    public function __construct($appName, IRequest $request, IL10N $l, GuestMapper $guestMapper, $userId, $userManager) {
        parent::__construct($appName, $request, 'GET, POST');
        $this->l = $l;
        $this->guestMapper = $guestMapper;
        $this->userId = $userId;
        $this->userManager = $userManager;
    }

    /**
     * Create a guest
     *
     * @NoAdminRequired
     *
     * @param string $uid   Guest's uid
     * @throws \Exception
     */
    public function create($data, $itemType, $itemSource, $itemSourceName) {
        $data = strip_tags(stripslashes($data));
        $list_uid = explode(',', str_replace(' ', '', trim($data)));
        foreach($list_uid as $uid) {
            try {
                $guest = $this->guestMapper->createGuest($uid);
                if (!$this->userManager->userExists($uid)) {
                    $this->userManager->createUser($uid, uniqid());
                }
            } catch (\Exception $e) {
                $response = new JSONResponse();
                return array(
                    'status' => 'error',
                    'data' => array(
                        'msg' => $e->getMessage(),
                    ),
                );
            }
            $this->guestMapper->saveGuestSharer($uid, $this->userId);
            try {
                \OCP\Share::shareItem(
                    $itemType,
                    $itemSource,
                    0,
                    $uid,
                    self::PERMISSION_GUEST,
                    $itemSourceName,
                    null
                );
            } catch (\Exception $e) {
                $response = new JSONResponse();
                return array(
                    'status' => 'error',
                    'data' => array(
                        'msg' => $e->getMessage(),
                    ),
                );
            }
        }
        return array(
            'status' => 'success',
            'data' => array(
                'list' => $list_uid,
            ),
        );
    }

    /**
     * Sharer's guests list
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     */

    public function listGuests() {
        try {
            $list = $this->guestMapper->getGuests(null, $this->userId);
        } catch(Exception $e) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $e->getMessage(),
                ),
            );
        }

        $formated_list= array();
        foreach ($list as $guest) {
            $formated_list[] = array(
                'uid' => $guest->getUid(),
                'is_active' =>$guest->getIsActive()
            );
        }

        return array(
            'status' => 'success',
            'data' => array(
                'list' => $formated_list
            ),
        );
    }

    /**
     * Delete an association sharer/guest. Is the guest have no more sharer, he is deleted
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $uid  Sharer's identifier
     */

    public function delete($uid, $itemType, $itemSource) {

        try {
            $request = $this->guestMapper->deleteSharerGuest($uid, $this->userId);
            \OCP\Share::unshare(
                $itemType,
                $itemSource,
                -1,
                $uid
            );
            if($this->guestMapper->countSharers($uid) === 0) {
                $this->guestMapper->deleteGuest($uid);
            }
        }
        catch(\Exception $e) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $e->getMessage(),
                ),
            );
        }

        return array(
            'status' => 'success',
            'data' => array(
                'msg' => $this->l->t('Share deleted'),
            ),
        );
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $uid  Sharer's identifier
     */
    public function test($data) {

        \OCP\Share::shareItem(
            'folder',
            3,
            0,
            'coucou@coucou.com',
            self::PERMISSION_GUEST,
            'test',
            null
        );
        echo "FIN";exit();
    }

}


// @NoAdminRequired
// @NoCSRFRequired
