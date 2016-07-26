<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Db;

use \OCP\AppFramework\Db\Entity;

class Guest extends Entity {
    protected $uid;
    protected $accepted;
    protected $isActive;
    protected $dateCreation;
    protected $dateExpiration;
    protected $token;
}
