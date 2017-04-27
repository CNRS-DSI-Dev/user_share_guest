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

use \OCA\User_Share_Guest\Db\Guest;
use \OCA\User_Share_Guest\Db\GuestMapper;
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IL10N;
use \OCP\IRequest;
use OC\Files\Cache\Cache;

class GuestController extends ApiController
{

    protected $appName;
    protected $guestMapper;
    protected $userManager;
    protected $userId;
    protected $mailService;
    protected $config;
    protected $shareManager;
    protected $federatedShareProvider;

    const PERMISSION_GUEST = 1;
    const SHARE_TYPE_GUEST = 0;

    /**
     * Initialization
     * @param string                 $appName
     * @param IRequest               $request
     * @param IL10N                  $l
     * @param GuestMapper            $guestMapper
     * @param string                 $userId
     * @param object                 $userManager
     * @param object                 $mailService
     * @param \OCP\Share\IManager    $shareManager
     */
    public function __construct($appName, IRequest $request, IL10N $l, GuestMapper $guestMapper, $userId, $userManager, $mailService, $config, $shareManager)
    {
        parent::__construct($appName, $request, 'GET, POST');
        $this->appName = $appName;
        $this->l = $l;
        $this->guestMapper = $guestMapper;
        $this->userId = $userId;
        $this->userManager = $userManager;
        $this->mailService = $mailService;
        $this->config = $config;
        $this->shareManager = $shareManager;
    }

    /**
     * Create a guest
     *
     * @NoAdminRequired
     *
     * @param  string $uid
     * @param  string $itemType
     * @param  string $itemSource
     * @throws \Exception
     */
    public function create($uid, $itemType, $itemSource)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('initialization creation guest') . $uid, 1);
        $appConfig = \OC::$server->getAppConfig();
        $domains_serialized = $appConfig->getValue('user_share_guest', 'user_share_guest_domains', '');
        $domain = array();
        $dns = false;
        if ($domains_serialized !== '') {
            $allowed_domains = array_values(unserialize($domains_serialized));
            $domain = substr($uid, strpos($uid, '@') + 1);
            $dns = dns_get_record($domain);
        }

        if (!filter_var($uid, FILTER_VALIDATE_EMAIL) || ($dns && !in_array($domain, $allowed_domains))) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => 'Error : invalid mail.',
                ),
            );
        }
        $params = array(
            'uid_guest' => $uid,
            'uid_sharer' => $this->userId,
            'item_type' => $itemType,
            'item_source' => $itemSource,
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

                    // guest verification
                    $token = $this->generateToken($uid);
                    $guest = $this->guestMapper->createGuest($params['uid_guest'], $token);
                    $this->initGuestDir($params['uid_guest']);
                    $this->config->setUserValue($params['uid_guest'], 'files', 'quota', '0 GB');
                    \OCP\Util::writeLog($this->appName, $this->l->t('Guest and user accounts created : ') . $params['uid_guest'], 1);
                    $user = $this->userManager->createUser($params['uid_guest'], uniqid());
                } else {
                    $response = new JSONResponse();
                    return array(
                        'status' => 'error',
                        'data' => array(
                            'msg' => 'Error : you can\'t create guest account.',
                        ),
                    );
                }
            } else if (empty($user)) {
                $this->initGuestDir($params['uid_guest']);
                $this->config->setUserValue($params['uid_guest'], 'files', 'quota', '0 GB');
                \OCP\Util::writeLog($this->appName, $this->l->t('Guest accounts created : ') . $params['uid_guest'], 1);
                $user = $this->userManager->createUser($params['uid_guest'], uniqid());
            }
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
        if (!empty($guest)) {
            if(is_array($guest)) {
                $is_active = $guest[0]->getIsActive();
                $token = $guest[0]->getToken();
            } else {
                $is_active = $guest->getIsActive();
                $token = $guest->getToken();
            }

            $this->config->setUserValue($params['uid_guest'], 'settings', 'email', $params['uid_guest']);
            $this->config->setUserValue($params['uid_guest'], 'owncloud', 'lostpassword', time() . ':' . $token); // on récupère le comportement et la génération du token de lostpassword
            \OCP\Util::writeLog($this->appName, $this->l->t('Send mail creation guest'), 1);
            $this->mailService->sendMailGuestCreate($params['uid_guest'], $token);

            if ($is_active) {
                // update expiration date to default value
                $date = mktime(00, 00, 00, 12, 31, 9999);
                $data = array(
                    'date_expiration' => '9999-12-31 00:00:00',
                    'is_active' => $is_active
                );
                $this->guestMapper->updateGuest($params['uid_guest'], $data);
            }
            $is_guest = true;
        }
        try {
            // Share creation
            \OCP\Util::writeLog($this->appName, $this->l->t('Set share with guest'), 1);
            $owner = \OC_User::getUser();
            $filePathComplete = \OC\Files\Filesystem::getPath($itemSource);
            $fileTarget = substr($filePathComplete, strrpos($filePathComplete, '/') +1);
            $this->guestMapper->saveGuestShare(0, $itemType, $itemSource, $fileTarget, $uid, $owner, $owner, self::PERMISSION_GUEST);
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
    public function listGuests($itemType, $itemSource)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('initialization guests list'), 1);
        try {
            $list = \OC\Share\Share::getItems(
                $itemType,
                $itemSource,
                self::SHARE_TYPE_GUEST,
                null,
                $this->userId
            );
            \OCP\Util::writeLog($this->appName, $this->l->t('Guests list retrieved'), 1);
        } catch(Exception $e) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $e->getMessage(),
                ),
            );
        }
        \OCP\Util::writeLog($this->appName, $this->l->t('formating guests list '), 1);
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

    public function delete($uid, $itemType, $itemSource)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('initialization delete guest'), 1);
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
                    'is_active' => false,
                    'accepted' => false
                );
                $this->guestMapper->updateGuest($uid, $data);
                $params['guest_expiration'] = date('Y-m-d H:i:s', $date);
                \OCP\Util::writeLog($this->appName, $this->l->t('Expiration date set') . '(' . $date . ')', 1);
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
                'msg' => 'Share deleted',
            ),
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


    ///

    /**
     * Check if users are guest
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param  array  $data
     *
     */
    public function isGuest($data)
    {
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
     */
    public function clean()
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('Initialization cleaning guest'), 1);
        $this->userManager->removeListener('\OC\User', 'postDelete');
        $guests  = $this->guestMapper->getGuestsExpiration();

        $count_delete = 0;
        if (empty($guests)) {     
            return array(
                'status' => 'success',
                'msg' => $this->l->t('No guest account to delete'),
            );
        }
        try {
            foreach ($guests as $guest) {
                $delete = true;
                \OC_Hook::emit('OCA\User_Share_Guest', 'pre_guestdelete', array('guest' => $guest, 'delete' => &$delete));
                $uid = $guest->getUid();
                if ($delete == false) {
                    continue;
                }
                $user = $this->userManager->get($uid);
                if (!$this->guestMapper->countSharesToGuest($uid)) {
                    $user->delete();
                    $this->guestMapper->deleteGuest($uid);
                    \OCP\Util::writeLog($this->appName, $this->l->t('Guest account deleted : ') . $guest->getUid(), 1);
                    $this->mailService->sendMailGuestDelete($uid);
                    \OC_Hook::emit('OCA\User_Share_Guest', 'post_guestdelete', array('guest' => $guest));
                    ++$count_delete;
                } else {
                    $date = mktime(00, 00, 00, 12, 31, 9999);
                    $this->guestMapper->updateGuest($uid, array('date_expiration' => date('Y-m-d H:i:s', $date)));
                }
            }
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'msg' => $e->getMessage(),
            );
        }

        \OCP\Util::writeLog($this->appName, $this->l->t('Inactives guest accounts cleaning completed.'), 1);

        return array(
            'status' => 'success',
            'msg' => $this->l->t('Process done : %s guest accounts deleted', $count_delete),
        );
    }



    /**
     * Function checking the activity of the guest accounts.
     * In the case of long periods of inactivity, an expiration date is applied to the account
     *
     * @return array  $data
     */
    public function verifyInactive()
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('Initialization inactivity checking'), 1);
        $guests  = $this->guestMapper->getGuests();
        $appconfig = \OC::$server->getAppConfig();
        if (empty($guests)) {
            return false;
        }
        try {
            foreach ($guests as $guest) {
                
                if($guest->getDateExpiration() !== '9999-12-31 00:00:00') {
                    continue;
                }
                $user = $this->userManager->get($guest->getUid());

                if ($guest->getIsActive() && $user && $user->getLastLogin()) {
                    $interval = time() - $user->getLastLogin();
                } else {
                    $interval = time() - strtotime($guest->getDateCreation());
                }

                $limit_days = $appconfig->getValue('user_share_guest', 'user_share_guest_days', '30'); // inactive for a month (default)
                if ($interval / (60*60*24) >= $limit_days) { 
                    $date = mktime(00, 00, 00, date('m') + 1, date('d'), date('Y')); // 1 mois de délai avant la suppression du compte
                    $this->guestMapper->updateGuest($guest->getUid(), array('date_expiration' => date('Y-m-d H:i:s', $date)));
                    $this->mailService->sendMailGuestInactive($guest->getUid(), date('d/m/Y', $date));
                }
            }
        } catch (Exception $e) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'msg' => $e->getMessage(),
            );
        }
        
        \OCP\Util::writeLog($this->appName, $this->l->t('Guest accounts verification completed.'), 1);
        return array(
            'status' => 'success',
            'msg' => $this->l->t('Process done'),
        );
    }

    /**
     * Generate and send an email the statistics of users guest accounts
     */
    public function generateStatistics()
    {

        $data = $this->guestMapper->getGuests();

        if (empty($data)) {
            \OCP\Util::writeLog($this->appName, $this->l->t('No guest account, statistics generation aborted.'), 1);
            return false;
        }
        $final = array();
        
        foreach ($data as $guest) {
            $uid_guest = $guest->getUid();
            $shares = \OCP\Share::getItemsSharedWithUser('file', $uid_guest);

            foreach ($shares as $s) {
                $mail_sharer = $this->config->getUserValue($s['uid_owner'], 'settings', 'email');
                if (empty($mail_sharer)) {
                    \OCP\Util::writeLog($this->appName, $this->l->t(sprintf('Statistics generation : %s haven\'t email adress.', $uid_sharer)), 3);
                    continue;
                }
                $user = $this->userManager->get($uid_guest);

                if ($user->getLastLogin()) {
                    $activity = date('d/m/Y', $user->getLastLogin());
                } else {
                    $activity = date('d/m/Y', strtotime($guest->getDateCreation()));
                }
                $final[$mail_sharer][$uid_guest]['files'][] = array(
                    'item_type' => $s['item_type'],
                    'item_source' => $s['file_target']
                );
                $final[$mail_sharer][$uid_guest]['activity'] = $activity;
            }           
        }
        foreach ($final as $mail_sharer => $data) {
            $this->mailService->sendMailGuestStatistics($mail_sharer, $data);
        }
        \OCP\Util::writeLog($this->appName, $this->l->t('Guest accounts statistics generated.'), 1);
        return array(
            'status' => 'success',
            'msg' => $this->l->t('Process done'),
        );
    }

    /**
     * @NoAdminRequired
     * @PublicPage
     *
     * @param string $uid
     * @param string $password
     * @param string $passwordconfirm
     */
    public function accept($uid, $password, $passwordconfirm)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('Initialization password setting'), 1);
        $error = '';
        if ($password !== $passwordconfirm) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'msg' => 'Passwords are different, please check your entry'
            );
        }
        if ($error === '') {
            \OC_User::setPassword($uid, $password);
            $this->guestMapper->updateGuest($uid, array('accepted' => 1, 'is_active' => 1));
            \OCP\Util::writeLog($this->appName, $this->l->t('Guest\'s password set', 1));
            \OC_User::login($uid, $password);
            \OC_Hook::emit('OCA\User_Share_Guest', 'post_guestsetp    margin: 0;assword', array('uid' => $uid, 'password' => $password));
            if (!GuestController::isAccountReseda($uid)) {
                $filesystem = \OC\Files\Filesystem::init($uid, '/');
                \OC\Files\Filesystem::unlink($uid . '/files/welcome.txt');
            } else {
                $this->guestMapper->deleteGuest($uid);
            }
        } else {
            $response = new JSONResponse();
            return array(
                'status' => 'error'
            );
        }

        return array(
            'status' => 'success'
        );
    }

    public function addDomain ($domain)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('Initialization creation domain'), 1);
        $appConfig = \OC::$server->getAppConfig();
        $domains_serialized = $appConfig->getValue('user_share_guest', 'user_share_guest_domains', '');
        $domains = unserialize($domains_serialized);
        if (empty($domains) || !empty($domains) && !in_array($domain, array_values($domains))) {
            $domains[] = $domain;
            $appConfig->setValue('user_share_guest', 'user_share_guest_domains', serialize($domains));
            return array(
                'status' => 'success'
            );
        }
        $response = new JSONResponse();
        return array(
            'status' => 'error',
            'msg' => 'Domain already registered.',
        );
    }

    public function deleteDomain ($domain)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('Initialization deletion domain'), 1);
        $appConfig = \OC::$server->getAppConfig();
        $domains_serialized = $appConfig->getValue('user_share_guest', 'user_share_guest_domains', '');
        $domains = unserialize($domains_serialized);
        if (!empty($domains) && in_array($domain, array_values($domains))) {
            $key = array_search($domain, $domains);
            unset($domains[$key]);
            $appConfig->setValue('user_share_guest', 'user_share_guest_domains', serialize($domains));
            return array(
                'status' => 'success'
            );
        }
        $response = new JSONResponse();
        return array(
            'status' => 'error',
            'msg' => 'The domain to be deleted does not exist',
        );
    }
    /**
     * Generate unique token
     *
     * @param  string $uid
     * @return string
     */
    private function generateToken($uid)
    {
        return hash('sha256', \OCP\Util::generateRandomBytes(30));
    }

    /**
     * Check if the uid already exists on Mycore or Labintel
     *
     * @param  string $uid
     * @return boolean
     */
    private function accountExist($uid)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('verification user\'s existence'), 1);
        if($this->userManager->userExists($uid)) {
            return true;
        } elseif ($this::isAccountReseda($uid)) {
            return true;
        }
        return false;
    }

    /**
     * Check if guest's email is in Reseda
     *
     * @param  string  $uid
     * @return boolean
     */
    public static function isAccountReseda($uid)
    {
        $url = 'https://webservices.dsi.cnrs.fr/services/eairef/v1/users/v1/count.json?limit=1&query=' . json_encode([ 'mail' => $uid ]);
        $connect = 'mycore:4HR2jJAtUbH6xPYsnTXB';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERPWD, $connect);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $result = json_decode($result);
        if ($result->count > 0) {
            \OCP\Util::writeLog('user_share_guest', 'account reseda', 1);
        } else {
            \OCP\Util::writeLog('user_share_guest', 'not account reseda', 1);
        }
        return $result->count;
    }

    /**
     * Generate new guest's directories
     *
     * @param  string $uid
     */
    private function initGuestDir($uid)
    {
        \OCP\Util::writeLog($this->appName, $this->l->t('generation new guest directory'), 1);
        $view = new \OC\Files\View('/' . $uid);
        if (!$view->is_dir('files')) {
            $view->mkdir('files');
        }
        if (!$view->is_dir('files_trashbin')) {
            $view->mkdir('files_trashbin');
        }
        if (!$view->is_dir('files_trashbin/files')) {
            $view->mkdir('files_trashbin/files');
        }
    }


    /**
     * Save data set in administration
     * 
     * @throws \Exception
     */
    public function saveAdmin($days) {

        \OCP\Util::writeLog($this->appName, 'Changing app config', 1);
        $appConfig = \OC::$server->getAppConfig();
        $error = [];
        $days = trim($days);

        if (isset($days) && is_numeric($days) && $days > 0) {
            $appConfig->setValue('user_share_guest', 'user_share_guest_days', intval(trim($days)));
            \OCP\Util::writeLog($this->appName, 'Deletion\'s delay set', 1);
        } else {
            $error[] = $this->l->t('Please enter a positve integer for the inactive period');
        }

        $response = new JSONResponse();
        if (empty($error)) {
            \OCP\Util::writeLog($this->appName, 'App config set', 1);
            return array(
                'status' => 'succes',
            );
        } else {
            return array(
                'status' => 'error',
                'msg' => implode(" - " , $error),
            );
        }
    }


    /**
     * launch guest's account statistics
     *
     * @NoAdminRequired
     *
     * @throws \Exception
     */
    public function launchStat () {
        $appConfig = \OC::$server->getAppConfig();
        $appConfig->setValue('user_share_guest', 'user_share_guest_last_stat', time());
        $response = new JSONResponse();       
        return $this->generateStatistics();
    }  

    /**
     * launch guest's account verification and cleaning
     *
     * @throws \Exception
     */
    public function launchVerif () {
        $appConfig = \OC::$server->getAppConfig();
        $appConfig->setValue('user_share_guest', 'user_share_guest_last_verif', time());
        $response = new JSONResponse();       
        return $this->verifyInactive();
        
    }

    /**
     * launch guest's account cleaning
     *
     * @throws \Exception
     */
    public function launchClean () {
        $appConfig = \OC::$server->getAppConfig();
        $appConfig->setValue('user_share_guest', 'user_share_guest_last_clean', time());
        $response = new JSONResponse();       
        return $this->clean();
        
    }
}
