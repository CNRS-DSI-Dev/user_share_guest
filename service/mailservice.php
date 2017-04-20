<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Service;

use \OCP\IL10N;

Class MailService
{
    protected $appName;
    protected $l;

    protected $userManager;
    protected $userId;
    protected $urlGenerator;

    public function __construct($appName, $userId, IL10N $l, $userManager, $urlGenerator)
    {
        $this->appName = $appName;
        $this->userId = $userId;
        $this->l = $l;
        $this->userManager = $userManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendMailGuestCreate($uid, $token) {
        
        //$uid = 'victor.bordage-gorry@globalis-ms.com';
        // Mail part
        $theme = new \OC_Defaults;
        $subject = (string)$this->l->t('A user of MyCore wish you invite and share files with you');
        $parameter = array('token' => $token, 'uid' => $uid);

        $url = $this->getLink('core.lost.resetform', $uid, $token);

        // generate the content
        $html = new \OCP\Template($this->appName, 'mail_usershareguestcreate_html', '');
        $html->assign('overwriteL10N', $this->l);
        $html->assign('sharerUid', $this->userId);
        $html->assign('accountUrl', $url);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, 'mail_usershareguestcreate_text', '');
        $alttext->assign('overwriteL10N', $this->l);
        $alttext->assign('sharerUid', $this->userId);
        $alttext->assign('accountUrl', $url);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');
        $toAddress = $uid;

        $toName = $uid;

        //sending
        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $altMail, $fromAddress, $fromName, 1, $htmlMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Can\t send mail for guest\'s invitation : ' . $e->getMessage(), \OCP\Util::ERROR);
        }
    }

    public function sendMailGuestDelete($uid) {
        
        //$uid = 'victor.bordage-gorry@globalis-ms.com';
        // Mail part
        $theme = new \OC_Defaults;
        $subject = (string)$this->l->t('Your guest account has been deleted');

        // generate the content
        $html = new \OCP\Template($this->appName, 'mail_usershareguestdelete_html', '');
        $html->assign('overwriteL10N', $this->l);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, 'mail_usershareguestdelete_text', '');
        $alttext->assign('overwriteL10N', $this->l);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');
        $toAddress = $uid;

        $toName = $uid;

        //sending
        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $altMail, $fromAddress, $fromName, 1, $htmlMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Can\'t send mail for guest\'s deletion : ' . $e->getMessage(), \OCP\Util::ERROR);
        }
    }

    public function sendMailGuestInactive($uid, $date) {
        //$uid = 'victor.bordage-gorry@globalis-ms.com';
        // Mail part
        $theme = new \OC_Defaults;
        $subject = (string)$this->l->t('Your guest account on Mycore is inactive');

        // generate the content
        $html = new \OCP\Template($this->appName, 'mail_usershareguestinactive_html', '');
        $html->assign('overwriteL10N', $this->l);
        $html->assign('date', $date);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, 'mail_usershareguestinactive_text', '');
        $alttext->assign('overwriteL10N', $this->l);
        $alttext->assign('date', $date);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');
        $toAddress = $uid;

        $toName = $uid;

        //sending
        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $altMail, $fromAddress, $fromName, 1, $htmlMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Can\'t send mail for guest\'s deletion : ' . $e->getMessage(), \OCP\Util::ERROR);
        }
    }

    public function sendMailGuestStatistics($mail, $data) {

        //$mail = 'victor.bordage-gorry@globalis-ms.com';
        // Mail part
        $theme = new \OC_Defaults;
        $subject = (string)$this->l->t('Mycore - Guests accounts created statistics');

        // generate the content
        $html = new \OCP\Template($this->appName, 'mail_usersharegueststatistics_html', '');
        $html->assign('overwriteL10N', $this->l);
        $html->assign('data', $data);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, 'mail_usersharegueststatistics_text', '');
        $alttext->assign('overwriteL10N', $this->l);
        $html->assign('data', $data);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');
        $toAddress = $mail;

        $toName = $mail;

        //sending
        try {
            \OCP\Util::sendMail($mail, $mail, $subject, $altMail, $fromAddress, $fromName, 1, $htmlMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog($this->appName, 'Can\'t send mail for guest\'s statistics : ' . $e->getMessage(), \OCP\Util::ERROR);
        }
    }

    protected function getLink($route, $user, $token){
        $parameters = array(
            'token' => $token,
            'userId' => $user
        );
        $link = $this->urlGenerator->linkToRoute($route, $parameters);

        return $this->urlGenerator->getAbsoluteUrl($link);
    }
}
