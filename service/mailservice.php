<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2015 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Service;

use \OCP\IL10N;
use \OCP\IConfig;

/**
 * Send mail on hook trigger
 */
Class MailService
{
    protected $appName;
    protected $l;
    protected $config;

    public function __construct($appName, IL10N $l, IConfig $config, )
    {
        $this->appName = $appName;
        $this->l = $l;
        $this->config = $config;
    }

    public function sendMailGuest($uid)
    {
        $reason = trim(nl2br(strip_tags(stripslashes($_POST['deletion_reason']))));
        if (empty($reason) || $reason === '') {
            return false;
        }

        // User modification, add to rejected group
        $user = $this->userManager->get($requesterUid);

        $userGroups = $this->groupManager->getUserGroupIds($user);

        // get the user's exclusion group.
        $configGroups = $this->config->getSystemValue('deletion_account_request_exclusion_groups');
        if (is_array($configGroups)) {
            foreach($configGroups as $groupKey => $groupValue) {
                if (in_array($groupKey, $userGroups)) {
                    if ($this->groupManager->groupExists($groupValue)) {
                        $group = $this->groupManager->get($groupValue);
                    }
                    else {
                        $group = $this->groupManager->createGroup($groupValue);
                    }
                    break;
                }
            }
        }

        // if $group unset, we use the default value
        if (empty($group)) {
            $val = $this->config->getSystemValue('deletion_account_request_default_exclusion_group');
            if ($this->groupManager->groupExists($val)) {
                $group = $this->groupManager->get($val);
            }
            else {
                $group = $this->groupManager->createGroup($val);
            }
        }

        $group->addUser($user);

        // get the admin's mail
        $configMails = $this->config->getSystemValue('deletion_account_request_admin_emails');
        if (is_array($configMails)) {
            foreach($configMails as $mailKey => $mailValue) {
                if (in_array($mailKey, $userGroups)) {
                    $toAddress = $toName =  $mailValue;
                    break;
                }
            }
        }

        // if $toAdress unset, we use the default value
        if (empty($toAdress)) {
            $toAddress = $toName = $this->config->getSystemValue('deletion_account_request_default_admin_email');
        }

        // Mail part
        $theme = new \OC_Defaults;
        $subject = (string)$this->l->t('Request for deleting account: %s', array($requesterUid));

        // generate the content
        $html = new \OCP\Template($this->appName, "mail_userdeletion_html", "");
        $html->assign('overwriteL10N', $this->l);
        $html->assign('requesterUid', $requesterUid);
        $html->assign('reason', $reason);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, "mail_userdeletion_text", "");
        $alttext->assign('overwriteL10N', $this->l);
        $alttext->assign('requesterUid', $requesterUid);
        $alttext->assign('reason', $reason);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');

        //sending
        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $htmlMail, $fromAddress, $fromName, 1, $altMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('user_account_actions', "Can't send mail for user creation: " . $e->getMessage(), \OCP\Util::ERROR);
        }

        // logout and redirect
        \OCP\User::logout();
        \OC_Util::redirectToDefaultPage();
        exit();
    }

}
