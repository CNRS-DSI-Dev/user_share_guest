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

use \OCA\User_Share_Guest\lib\TemplateLayoutShareGuest;

class TemplateShareGuest extends \OCP\Template {

    private $renderas;
    private $path;
    private $headers = array();

    public function fetchPage() {
        $data = \OC\Template\Base::fetchPage();

        $this->renderas = 'user';

        $page = new TemplateLayoutShareGuest($this->renderas, $this->app);

        // Add custom headers
        $page->assign('headers', $this->headers, false);
        foreach(\OC_Util::$headers as $header) {
            $page->append('headers', $header);
        }

        $page->assign( "content", $data, false );
        return $page->fetchPage();
    }
}
