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
    protected $userId;

    public function __construct($appName, IRequest $request, IL10N $l, GuestMapper $guestMapper, $userId) {
        parent::__construct($appName, $request, 'GET, POST');
        $this->l = $l;
        $this->guestMapper = $guestMapper;
        $this->userId = $userId;
    }

    /**
     * Create a guest
     *
     * @NoAdminRequired
     *
     * @param string $uid   Guest's uid
     * @throws \Exception
     */
    public function create($uid) {
        try {
            $guest = $this->guestMapper->createGuest($uid);
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

        return array(
            'status' => 'success',
            'data' => array(
                'uid' => $uid,
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

    public function delete($uid) {
        echo $uid;exit();
        try {
            $request = $this->guestMapper->deleteSharerGuest($uid, $this->userId);
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

}


// @NoAdminRequired
// @NoCSRFRequired
