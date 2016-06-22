<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 *
 * HOOKS :
 *
 * pre_createguest
 * post_createguest
 * pre_addguestlist
 * post_guestlist
 * pre_deleteguestshare
 * post_deleteguestshare
 * pre_guestdelete
 * post_guestdelete
 *
 */

namespace OCA\User_Share_Guest\Controller;

use \OCP\AppFramework\APIController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IL10N;
use \OCA\User_Share_Guest\Db\GuestMapper;
use \OCA\User_Share_Guest\Db\Guest;

class GuestController extends APIController {

    protected $appName;
    protected $guestMapper;
    protected $userManager;
    protected $userId;
    protected $mailService;

    const PERMISSION_GUEST = 1;
    const SHARE_TYPE_GUEST = 0;

    public function __construct($appName, IRequest $request, IL10N $l, GuestMapper $guestMapper, $userId, $userManager, $mailService) {
        parent::__construct($appName, $request, 'GET, POST');
        $this->appName = $appName;
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
        if (!filter_var($uid, FILTER_VALIDATE_EMAIL)) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $this->l->t('Error : invalid mail.'),
                ),
            );
        }
        $params = array(
            'uid_guest' => $uid,
            'uid_sharer' => $this->userId,
            'item_type' => $itemType,
            'item_source' => $itemSource,
            'item_source_name' => $itemSourceName,
            'valid' => true
        );

        \OC_Hook::emit('OCA\User_Share_Guest', 'pre_createguest', array('data' => &$params));

        $is_active = true;
        $is_guest = false;
        try {

            // user exist verification
            $user = $this->userManager->get($params['uid_guest']);
            $guest = $this->guestMapper->getGuests($params['uid_guest']);

            if (empty($user) && empty($guest)) {
                if ($params['valid'] == true) {
                    $user = $this->userManager->createUser($params['uid_guest'], uniqid());
                } else {
                    $response = new JSONResponse();
                    return array(
                        'status' => 'error',
                        'data' => array(
                            'msg' => $this->l->t('Error : you can\'t create guest account.'),
                        ),
                    );
                }

                // Resada verification
                $user_resada = 0;
                if(!$user_resada) {

                    // guest verification
                    if (empty($guest)) {
                        $token = $this->generateToken($uid);
                        $guest = $this->guestMapper->createGuest($params['uid_guest'], $token);
                        \OC_Preferences::setValue($params['uid_guest'], 'files', 'quota', '0 GB');
                        $this->mailService->sendMailGuestCreate($params['uid_guest'], $token);
                        \OCP\Util::writeLog($this->appName, $this->l->t('Guest accounts created : ') . $params['uid_guest'], 1);
                    } else {

                        // update expiration date to default value
                        $date = mktime(00, 00, 00, date('m'), date('d') + $days, date('Y'));
                        $data = array(
                            'date_expiration' => date('Y-m-d H:i:s', $date),
                            'is_active' => true
                        );
                        $this->guestMapper->updateGuest($params['uid_guest'], $data);
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
        if (isset($guest)) {
            if(is_array($guest)) {
                $is_active = $guest[0]->getIsActive();
            } else {
                $is_active = $guest->getIsActive();
            }

            $is_guest = true;
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
            \OCP\Util::writeLog($this->appName, $this->l->t('Error when creating a guest account : ') . $e->getMessage(), 1);
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $e->getMessage(),
                ),
            );
        }

        $params['is_active'] = $is_active;
        $params['is_guest'] = $is_guest;
        \OC_Hook::emit('OCA\User_Share_Guest', 'post_createguest', array('data' => $params));

        return array(
            'status' => 'success',
            'data' => array(
                'user' => array(
                    'uid' => $params['uid_guest'],
                    'is_active' => $is_active,
                    'is_guest' => $is_guest
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
        $formated_list = array();
        try {
            foreach ($list as $key => $share) {
                if ($guest = $this->guestMapper->getGuests($share['share_with'])) {
                    $params = array(
                        'uid' => $guest[0]->getUid(),
                        'is_active' => $guest[0]->getIsActive()
                    );
                    \OC_Hook::emit('OCA\User_Share_Guest', 'pre_addguestlist', array('data' => &$params, 'guest' => $guest));
                    $formated_list[] = $params;
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

        \OC_Hook::emit('OCA\User_Share_Guest', 'post_guestlist', array('formated_list' => &$formated_list));
        return array(
            'status' => 'success',
            'data' => array(
                'list' => $formated_list
            ),
        );
    }

    /**
     * Delete an association sharer/guest.
     * If the guest have no more sharer, his account get an expiration date (defined by admin or 5 by default)
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $uid  Sharer's identifier
     * @param string $itemType
     * @param string $itemSource
     */

    public function delete($uid, $itemType, $itemSource) {
        $params = array(
            'uid_guest' => $uid,
            'uid_sharer' => $this->userId,
            'item_type' => $itemType,
            'item_source' => $itemSource,
            'valid' => true,
            'guest_expiration' => false
        );

        \OC_Hook::emit('OCA\User_Share_Guest', 'pre_deleteguestshare', array('data' => &$params));

        if ($params['valid'] == false) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $this->l->t('Error : you can\'t delete guest account.'),
                ),
            );
        }
        try {
            $appConfig = \OC::$server->getAppConfig();
            $request = $this->guestMapper->deleteSharerGuest($params['uid_guest'], $params['uid_sharer'], $params['item_type'], $params['item_source']);
            \OCP\Share::unshare(
                $params['item_type'],
                $params['item_source'],
                self::SHARE_TYPE_GUEST,
                $params['uid_guest']
            );
            if($this->guestMapper->countSharers($uid) === 0) {

                // set expiration date for guest's account
                $days = $appConfig->getValue('user_share_guest', 'user_share_guest_days', 5);
                $date = mktime(00, 00, 00, date('m'), date('d') + $days, date('Y'));
                $data = array(
                    'date_expiration' => date('Y-m-d H:i:s', $date),
                    'is_active' => false
                );
                $this->guestMapper->updateGuest($uid, $data);
                $params['guest_expiration'] = date('Y-m-d H:i:s', $date);
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
        \OC_Hook::emit('OCA\User_Share_Guest', 'post_deleteguestshare', array('data' => $params));
        return array(
            'status' => 'success',
            'data' => array(
                'msg' => $this->l->t('Share deleted'),
            ),
        );
    }

    /**
     * Guest's share list (to delete)
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $path
     * @param string $sort
     * @param string $sortDirection
     */
    public function shareList($path, $sort, $sortDirection) {
        try {
            $dir = \OC\Files\Filesystem::normalizePath($dir);
            $dirInfo = \OC\Files\Filesystem::getFileInfo($dir);
            $data = array();
            $permissions = $dirInfo->getPermissions();
            $sortAttribute = isset($_GET['sort']) ? $_GET['sort'] : 'name';
            $sortDirection = isset($_GET['sortdirection']) ? ($_GET['sortdirection'] === 'desc') : false;

            // make filelist

            $files = \OCA\Files\Helper::getFiles($dir, $sortAttribute, $sortDirection);
            $data['directory'] = $dir;
            $data['files'] = \OCA\Files\Helper::formatFileInfos($files);
            $data['permissions'] = $permissions;
            for ($i = 0; $i < count($data['files']); $i++) {
                if(!isset($data['files'][$i]['shareOwner'])){
                    unset($data['files'][$i]);
                }
            }
        } catch (Exception $e) {
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
            'data' => $data
        );
    }

    /**
     * Allows to check whether to create a guest or not
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param  string  $uid
     *
     */
    public function isGuestCreation($uid) {

        try {
            $exist = $this->accountExist($uid);
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
                'exist' => $exist
            )
        );
    }

    /**
     * Check if users are guest
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param  array  $data
     *
     */
    public function isGuest($data) {

        $final = array();
        try {
            foreach($data as $values) {
                if(!$this->guestMapper->getGuests($values['label'])) {
                    $final[] = $values;
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
            'data' => $final
        );
    }

    /**
     * Function removing guest accounts expired
     *
     * @return boolean
     */
    public function clean() {
        $this->userManager->removeListener('\OC\User', 'postDelete');
        $guests  = $this->guestMapper->getGuestsExpiration();
        if (empty($guests)) {
            return false;
        }
        try {
            foreach($guests as $guest) {
                $delete = true;
                \OC_Hook::emit('OCA\User_Share_Guest', 'pre_guestdelete', array('guest' => $guest, 'delete' => &$delete));
                if ($delete == false) {
                    continue;
                }
                $uid = $guest->getUid();
                //if (!$this->guestMapper->countSharers($uid)) {
                    \OC_User::deleteUser($uid);
                    $this->guestMapper->deleteGuest($guest->getUid());
                    \OCP\Util::writeLog($this->appName, $this->l->t('Guest account deleted : ') . $guest->getUid(), 1);
                    $this->mailService->sendMailGuestDelete($uid);
                    \OC_Hook::emit('OCA\User_Share_Guest', 'post_guestdelete', array('guest' => $guest));
                /*} else {
                    $date = mktime(00, 00, 00, 12, 31, 9999);
                    $this->guestMapper->updateGuest($uid, array('date_expiration' => date('Y-m-d H:i:s', $date)));
                }*/
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        \OCP\Util::writeLog($this->appName, $this->l->t('Inactives guest accounts cleaning completed.'), 1);
        return true;
    }

    /**
     * Function checking the activity of the guest accounts.
     * In the case of long periods of inactivity, an expiration date is applied to the account
     *
     * @return boolean
     */
    public function verifyInactive() {
        $guests  = $this->guestMapper->getGuests();
        if (empty($guests)) {
            return false;
        }
        try {
            foreach ($guests as $guest) {
                $user = $this->userManager->get($guest->getUid());
                $interval = time() - $user->getLastLogin();
                if ($interval / 86400 >= 30 || 1 == 1) { // inactive for a month
                    $date = mktime(00, 00, 00, date('m') - 3, date('d'), date('Y'));
                    $this->guestMapper->updateGuest($guest->getUid(), array('date_expiration' => date('Y-m-d H:i:s', $date)));
                    $this->mailService->sendMailGuestInactive($guest->getUid(), date('d/m/Y', $date));
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        \OCP\Util::writeLog($this->appName, $this->l->t('Guest accounts verification completed.'), 1);
        return true;
    }

    /**
     * Generate and send an email the statistics of users guest accounts
     *
     */
    public function generateStatistics() {
        $data = $this->guestMapper->getGuestsSharer();
        if (empty($data)) {
            \OCP\Util::writeLog($this->appName, $this->l->t('No guest account, statistics generation aborted.'), 1);
            return false;
        }
        $final = array();
        $preferences = new \OC\Preferences(\OC_DB::getConnection());
        foreach($data as $share) {
            $uid_sharer = $share['uid_sharer'];
            $mail = $preferences->getValue($uid_sharer, 'settings', 'email');
            if (empty($mail)) {
                \OCP\Util::writeLog($this->appName, $this->l->t(sprintf('Statistics generation : %s haven\'t email adress.', $uid_sharer)), 3);
                continue;
            }
            $user = $this->userManager->get($mail);
            $final[$mail][$share['uid_guest']][] = array(
                'item_type' => $share['item_type'],
                'item_source' => $share['item_source'],
                'activity' => date('d/m/Y', $user->getLastLogin())
            );

        }

        foreach ($final as $mail => $data) {
            $this->mailService->sendMailGuestStatistics($mail, $data);
        }

        \OCP\Util::writeLog($this->appName, $this->l->t('Guest accounts statistics generated.'), 1);
    }

    /**
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $itemSource
     */
    public function test($data = '') {
        //echo "<pre>";
        $this->verifyInactive();
        $this->clean();
        //$data = $this->listGuests('file', 44);

        //$this->create('victor.bordage-gorry@globalis-ms.com', 'file', 44, 'test partage.txt');
        /*
        $dir = '/test';

        $dir = \OC\Files\Filesystem::normalizePath($dir);
        $dirInfo = \OC\Files\Filesystem::getFileInfo($dir);
        $data = array();
        $permissions = $dirInfo->getPermissions();
        $sortAttribute = isset($_GET['sort']) ? $_GET['sort'] : 'name';
        $sortDirection = isset($_GET['sortdirection']) ? ($_GET['sortdirection'] === 'desc') : false;

        // make filelist

        $files = \OCA\Files\Helper::getFiles($dir, $sortAttribute, $sortDirection);
        $data['directory'] = $dir;
        $data['files'] = \OCA\Files\Helper::formatFileInfos($files);
        $data['permissions'] = $permissions;
        for ($i = 0;$i < count($data['files']); $i++) {
            if(!isset($data['files'][$i]['shareOwner'])){
                unset($data['files'][$i]);
            }
        }
        var_dump($data);*/
        echo "FIN";exit();
    }


    /**
     * Generate unique token
     *
     * @param  string $uid
     * @return string
     */
    private function generateToken($uid) {
        return base64_encode(uniqid() . $uid);
    }

    /**
     * Check if the uid already exists on Mycore or Labintel
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
