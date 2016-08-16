<?php

/**
 * ownCloud - User Share Guest
 *
 * @author ShareGuest Bordage-Gorry <ShareGuest.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 *
 * HOOKS :
 *
 * post_guestsetpassword
 *
 */

namespace OCA\User_Share_Guest\Controller;

use \OCA\User_Share_Guest\Db\Guest;
use \OCA\User_Share_Guest\Db\GuestMapper;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\TemplateResponse;

class PageController extends Controller
{

    protected $l;
    protected $guestMapper;
    protected $userId;
    protected $userManager;
    protected $urlGenerator;

    public function __construct($appName, $request, $l, GuestMapper $guestMapper, $userId, $userManager, $urlGenerator)
    {
        parent::__construct($appName, $request);
        $this->l = $l;
        $this->guestMapper = $guestMapper;
        $this->userId = $userId;
        $this->userManager = $userManager;
        $this->urlGenerator = $urlGenerator;

    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     *
     * @param string $uid
     * @param string $token
     *
     * @return \OCP\AppFramework\Http\TemplateResponse
     */
    public function confirm($uid, $token)
    {
        if (!$this->guestMapper->verifyGuestToken($uid, $token) || $this->guestMapper->hasGuestAccepted($uid)) {
            \OC_Util::redirectToDefaultPage();
            exit();
        }
        $templateName = 'public';
        $parameters = array('l' => $this->l, 'uid' => $uid);
        return new TemplateResponse($this->appName, $templateName, $parameters, 'guest');
    }
}
