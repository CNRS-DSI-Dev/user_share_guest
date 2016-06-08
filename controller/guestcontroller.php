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

class GuestController extends APIController {

    protected $guestMapper;
    protected $userManager;
    protected $userId;
    protected $mailService;

    const PERMISSION_GUEST = 1;
    const SHARE_TYPE_GUEST = 5;

    public function __construct($appName, IRequest $request, IL10N $l, GuestMapper $guestMapper, $userId, $userManager, $mailService) {
        parent::__construct($appName, $request, 'GET, POST');
        $this->l = $l;
        $this->guestMapper = $guestMapper;
        $this->userId = $userId;
        $this->userManager = $userManager;
        $this->mailService = $mailService;
    }

    /**
     * Create a guest
     *
     * @NoAdminRequired
     *
     * @param  string $uid
     * @param  string $itemType
     * @param  string $itemSource
     * @param  string $itemSourceName
     * @throws \Exception
     */

    public function create($uid, $itemType, $itemSource, $itemSourceName) {
        $this->userManager->removeListener('\OC\User', 'postCreateUser');

        try {
            // @TODO : LABINTEL
            if (!$this->accountExist($uid)) {
                $token = $this->generateToken($uid);
                $this->userManager->createUser($uid, uniqid());
                $guest = $this->guestMapper->createGuest($uid, $token);
            } else {
                $guest = $this->guestMapper->getGuests($uid);
                $guest = $guest[0];
                $token = $guest->getToken();
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
        if ($guest) {
            $this->guestMapper->saveGuestSharer($uid, $this->userId, $itemType, $itemSource);
            $this->mailService->sendMailGuest($uid, $token);
        }
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

        return array(
            'status' => 'success',
            'data' => array(
                'guest' => array(
                    'uid' => $guest->getuid(),
                    'is_active' => $guest->getIsActive()
                ),
            ),
        );
    }

    /**
     * Sharer's guests list
     *
     * @param  string $itemType
     * @param  string $itemSource
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     */

    public function listGuests($itemType, $itemSource) {
        try {
            $list = \OC\Share\Share::getItems(
                $itemType,
                $itemSource,
                self::SHARE_TYPE_GUEST,
                null,
                $this->userId
            );
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
        try {
            foreach ($list as $key => $share) {
                if ($guest = $this->guestMapper->getGuests($share['share_with'])) {
                    $formated_list[] = array(
                        'uid' => $guest[0]->getUid(),
                        'is_active' => $guest[0]->getIsActive()
                    );
                }
            }
        } catch(Exception $e) {
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
     * @param string $itemType
     * @param string $itemSource
     */

    public function delete($uid, $itemType, $itemSource) {
        $this->userManager->removeListener('\OC\User', 'postDelete');
        try {
            $request = $this->guestMapper->deleteSharerGuest($uid, $this->userId, $itemType, $itemSource);
            \OCP\Share::unshare(
                $itemType,
                $itemSource,
                self::SHARE_TYPE_GUEST,
                $uid
            );
            if($this->guestMapper->countSharers($uid) === 0) {
                \OC_User::deleteUser($uid);
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
     */
    public function shareList() {
        try {
            $data = \OC\Share\Share::getItems(
                'file',
                null,
                self::SHARE_TYPE_GUEST,
                $this->userId,
                null
            );
            foreach ($data as &$share) {
                if ($share['item_type'] === 'file') {
                    $share['mimetype'] = \OC_Helper::getFileNameMimeType($share['file_target']);
                    if (\OC::$server->getPreviewManager()->isMimeSupported($share['mimetype'])) {
                        $share['isPreviewAvailable'] = true;
                    }
                }
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

        return array(
            'status' => 'success',
            'data' => array(
                'list' => $data
            ),
        );
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param  string  $data
     *
     */
    public function isGuestCreation($uid) {

        try {
            $creation = !$this->accountExist($uid);
        } catch (\Exception $e) {
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
                'creation' => $creation
            )
        );
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $itemSource
     */
    public function test($data = '') {
        //$data = $this->listGuests('file', 44);

        $this->create('victor.bordage-gorry@globalis-ms.com', 'file', 44, 'test partage.txt');
        echo "FIN";exit();
    }


    private function generateToken($uid) {
        return base64_encode(uniqid() . $uid);
    }

    /**
     *
     *
     * @param  string $uid
     * @return boolean
     */
    private function accountExist($uid) {
        if($this->userManager->userExists($uid)) {
            return true;
        }
        // @TODO : Labintel
        return false;
    }
}
