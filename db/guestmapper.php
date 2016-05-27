<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Share_Guest\Db;

use \OCP\IDb;
use \OCP\IL10N;
use \OCP\AppFramework\Db\Mapper;

class GuestMapper extends Mapper {

    const TABLE_USER_GUEST = '*PREFIX*user_guest';
    const TABLE_GUEST_SHARER = '*PREFIX*guest_sharer';

    protected $l;

    public function __construct(IDb $db,  IL10N $l) {
        $this->l = $l;

        parent::__construct($db, 'user_guest');
    }

    /**********
      GET
    **********/

    /**
     * Get guest list. If an uid is set, return only one guest. If sharer's uid set, return his list of guest. Both can be combine
     *
     * @param  string   $uid
     * @param  string   $uid_sharer
     * @param  int      $limit
     * @param  int      $offset
     * @return array
     */
    public function getGuests($uid = null, $uid_sharer = null, $limit = null, $offset = null) {
        $data = array();
        $sql = 'SELECT ug.* FROM ' . self::TABLE_USER_GUEST . ' AS ug';

        if (!empty($uid) || !empty($uid_sharer)) {

            if (!empty($uid_sharer)) {
                $sql .= ' INNER JOIN ' . self::TABLE_GUEST_SHARER . ' AS gs ON ug.uid = gs.uid_guest AND gs.uid_sharer = ?';
                $data[] = $uid_sharer;
            }

            if (!empty($uid)) {
                $sql .= ' WHERE ug.uid = ?';
                $data[] = $uid;
            }
        }
        return $this->findEntities($sql, $data, $limit, $offset);
    }



    /**********
      SAVE
    **********/

    /**
     * Save a guest
     *
     * @param  string   $uid
     * @return Guest
     */
    public function createGuest($uid) {

        if (!filter_var($uid, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception($this->l->t('Error : invalid mail.'));
            return false;
        }

        // if a guest already exists, we abort the saving
        if ($this->getGuests($uid)) {
            return false;
        }

        $guest = new Guest();
        $guest->setUid($uid);
        $guest->setPassword('guest');
        $guest->setIsActive(false);

        $this->insert($guest);
        return $guest;

    }

    public function saveGuestSharer($uid, $uid_sharer) {
        $sql = 'INSERT INTO ' . self::TABLE_GUEST_SHARER . ' VALUES (?, ?)';
        $this->execute($sql, array($uid_sharer, $uid));
    }

    /**********
      UPDATE
    **********/

    /**********
      DELETE
    **********/

    /**
     * Delete a guest
     *
     * @param  string   $uid
     */
    public function deleteGuest($uid) {
        $sql = 'DELETE FROM ' . self::TABLE_USER_GUEST . ' WHERE uid = ?';
        $this->execute($sql, array($uid));
    }

    /**
     * Delete an association sharer / guest
     *
     * @param  string   $uid
     * @param  string   $uid_sharer
     */
    public function deleteSharerGuest($uid, $uid_sharer) {
        $sql = 'DELETE FROM ' . self::TABLE_GUEST_SHARER . ' WHERE uid_guest = ? AND uid_sharer = ?';
        $this->execute($sql, array($uid, $uid_sharer));
    }

    /**********
      OTHER
    **********/

    public function countSharers($uid) {
        $sql = 'SELECT count(uid_sharer) FROM ' . self::TABLE_GUEST_SHARER . 'WHERE uid_guest = ?';
        return $this->execute($sql, array($uid));
    }
}
