<?php

/**
 * ownCloud - User Share Guest
 *
 * @author ShareGuest Bordage-Gorry <ShareGuest.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 *
 */

namespace OCA\User_Share_Guest\lib;

class TemplateLayoutShareGuest extends \OC_TemplateLayout {
    protected static $versionHash;

    public function __construct( $renderas, $appid = '' ) {
        // Decide which page we show

        if( $renderas == 'user' ) {
            \OC_Template::__construct( 'user_share_guest', 'layout.userguest' );
            if(in_array(\OC_APP::getCurrentApp(), array('settings','admin', 'help'))!==false) {
                $this->assign('bodyid', 'body-settings');
            }else{
                $this->assign('bodyid', 'body-user');
            }

            // Update notification
            if(\OC_Config::getValue('updatechecker', true) === true) {
                $data=\OC_Updater::check();
                if(isset($data['version']) && $data['version'] != '' and $data['version'] !== Array() && \OC_User::isAdminUser(\OC_User::getUser())) {
                    $this->assign('updateAvailable', true);
                    $this->assign('updateVersion', $data['versionstring']);
                    $this->assign('updateLink', $data['web']);
                } else {
                    $this->assign('updateAvailable', false); // No update available or not an admin user
                }
            } else {
                $this->assign('updateAvailable', false); // Update check is disabled
            }

            // Add navigation entry
            $this->assign( 'application', '', false );
            $this->assign( 'appid', $appid );
            $navigation = \OC_App::getNavigation();
            $this->assign( 'navigation', $navigation);
            $this->assign( 'settingsnavigation', \OC_App::getSettingsNavigation());
            foreach($navigation as $entry) {
                if ($entry['active']) {
                    $this->assign( 'application', $entry['name'] );
                    break;
                }
            }
            $user_displayname = \OC_User::getDisplayName();
            $this->assign( 'user_displayname', $user_displayname );
            $this->assign( 'user_uid', \OC_User::getUser() );
            $this->assign( 'appsmanagement_active', strpos(\OC_Request::requestUri(), \OC_Helper::linkToRoute('settings_apps')) === 0 );
            $this->assign('enableAvatars', \OC_Config::getValue('enable_avatars', true));
        } else if ($renderas == 'guest' || $renderas == 'error') {
            parent::__construct('core', 'layout.guest');
        } else {
            parent::__construct('core', 'layout.base');
        }

        if(empty(self::$versionHash)) {
            self::$versionHash = md5(implode(',', \OC_App::getAppVersions()));
        }

        $useAssetPipeline = $this->isAssetPipelineEnabled();
        if ($useAssetPipeline) {
            $this->append( 'jsfiles', \OC_Helper::linkToRoute('js_config', array('v' => self::$versionHash)));
            $this->generateAssets();
        } else {
            // Add the js files
            $jsfiles = self::findJavascriptFiles(\OC_Util::$scripts);
            $this->assign('jsfiles', array(), false);
            if (\OC_Config::getValue('installed', false) && $renderas!='error') {
                $this->append( 'jsfiles', \OC_Helper::linkToRoute('js_config', array('v' => self::$versionHash)));
            }
            foreach($jsfiles as $info) {
                $web = $info[1];
                $file = $info[2];
                $this->append( 'jsfiles', $web.'/'.$file . '?v=' . self::$versionHash);
            }

            // Add the css files
            $cssfiles = self::findStylesheetFiles(\OC_Util::$styles);
            $this->assign('cssfiles', array());
            foreach($cssfiles as $info) {
                $web = $info[1];
                $file = $info[2];

                $this->append( 'cssfiles', $web.'/'.$file . '?v=' . self::$versionHash);
            }
        }
    }

    /**
     * @param array $files
     * @return string
     */
    private static function hashFileNames($files) {
        foreach($files as $i => $file) {
            $files[$i] = self::convertToRelativePath($file[0]).'/'.$file[2];
        }

        sort($files);
        // include the apps' versions hash to invalidate the cached assets
        $files[]= self::$versionHash;
        return hash('md5', implode('', $files));
    }

    /**
     * @return bool
     */
    private function isAssetPipelineEnabled() {
        // asset management enabled?
        $useAssetPipeline = \OC_Config::getValue('asset-pipeline.enabled', false);
        if (!$useAssetPipeline) {
            return false;
        }

        // assets folder exists?
        $assetDir = \OC::$SERVERROOT . '/assets';
        if (!is_dir($assetDir)) {
            if (!mkdir($assetDir)) {
                \OCP\Util::writeLog('assets',
                    "Folder <$assetDir> does not exist and/or could not be generated.", \OCP\Util::ERROR);
                return false;
            }
        }

        // assets folder can be accessed?
        if (!touch($assetDir."/.oc")) {
            \OCP\Util::writeLog('assets',
                "Folder <$assetDir> could not be accessed.", \OCP\Util::ERROR);
            return false;
        }
        return $useAssetPipeline;
    }
}
