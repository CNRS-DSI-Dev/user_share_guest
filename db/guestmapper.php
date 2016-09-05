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
    const TABLE_SHARE = '*PREFIX*share';
    const SHARE_GUEST_STATUT = 0;
    private $accepeted_keys;

    protected $l;

    public function __construct(IDb $db,  IL10N $l) {
        $this->l = $l;
        $this->accepeted_keys = array('accepted', 'is_active', 'date_expiration','token');
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

    /**
     * Get guest to delete
     *
     */
    public function getGuestsExpiration() {
        $sql  = 'SELECT * FROM ' . self::TABLE_USER_GUEST . ' WHERE date_expiration < NOW()';
        return $this->findEntities($sql, array());
    }

    public function getGuestsSharer($param = array()) {
        $sql = 'SELECT * FROM ' . self::TABLE_GUEST_SHARER;
        $cond = array();
        $data = array();
        $where = '';
        if (!empty($param)) {
            foreach ($param as $k => $v) {
                $cond[] = $k . '= ?';
                $data[] = $v;
            }
            $where = ' WHERE ' . implode(', ', $param);
        }
        return $this->execute($sql . $where, $data)->fetchAll();
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
    public function createGuest($uid, $token) {

        $guest = new Guest();
        $guest->setUid($uid);
        $guest->setAccepted(false);
        $guest->setIsActive(false);
        $guest->setToken($token);
        $guest->setDateCreation(date("Y-m-d H:i:s"));

        $this->insert($guest);

        return $guest;
    }

    /**
     * Save an association sharer / guest
     *
     * @param  string $uid
     * @param  string $uid_sharer
     * @param  string $itemType
     * @param  string $itemSource
     */
    public function saveGuestSharer($uid, $uid_sharer, $itemType, $itemSource) {
        $sql = 'INSERT INTO ' . self::TABLE_GUEST_SHARER . ' VALUES (?, ?, ?, ?)';
        $this->execute($sql, array($uid_sharer, $uid, $itemType, $itemSource));
    }

    /**********
      UPDATE
    **********/

    /**
     * Update guest's informations
     *
     * @param  string $uid
     * @param  array  $data
     */
    public function updateGuest($uid, $data = array()) {

        if (empty($data)) {
            return false;
        }

        $sql = 'UPDATE ' . self::TABLE_USER_GUEST . ' SET';
        $data_update = array();
        $update = '';
        foreach ($data as $k => $v) {
            if (in_array($k, $this->accepeted_keys)) {
                if ($v != 'NOW()') {
                    $update .= ' ' . $k . ' = ?,';
                    $data_update[] = $v;
                } else {
                    $update .= ' ' . $k . ' = NOW(),';
                }
            }
        }

        $sql .= substr($update, 0, -1) . ' WHERE uid = ?';
        $data_update[] = $uid;
        $this->execute($sql, $data_update);
    }

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
     * @param  string $uid
     * @param  string $uid_sharer
     * @param  string $itemType
     * @param  string $itemSource
     */
    public function deleteSharerGuest($uid, $uid_sharer, $itemType, $itemSource) {
        $sql = 'DELETE FROM ' . self::TABLE_GUEST_SHARER . ' WHERE uid_guest = ? AND uid_sharer = ? AND item_type = ? AND item_source = ?';
        $this->execute($sql, array($uid, $uid_sharer, $itemType, $itemSource));
    }

    /**********
      OTHER
    **********/

    /**
     * Return the number of sharers for a guest
     *
     * @param  string $uid
     * @return int
     */
    public function countSharers($uid) {
        $sql = 'SELECT count(uid_sharer) as count FROM ' . self::TABLE_GUEST_SHARER . ' WHERE uid_guest = ?';
        $result = $this->execute($sql, array($uid))->fetch();
        return intval($result['count']);
    }

    /**
     * Update the statut of the guest's share
     *
     * @param  string $uid
     * @param  string $uid_sharer
     */

    public function updateGuestShareStatut($uid, $uid_sharer) {
        $sql = 'UPDATE ' . self::TABLE_SHARE . ' SET share_type = ' . self::SHARE_GUEST_STATUT . ' WHERE share_with = ? AND uid_owner = ?';
        $this->execute($sql, array($uid, $uid_sharer));
    }

    /**
     * Check if the guest accepted the invitation
     *
     * @param  string  $uid
     * @return Guest
     */
    public function hasGuestAccepted($uid) {
        $sql = 'SELECT * FROM ' . self::TABLE_USER_GUEST . ' WHERE uid = ? AND accepted = 1';
        $result = $this->findEntities($sql, array($uid));
        return (!empty($result));
    }

    /**
     * Check if the guest is active
     *
     * @param  string  $uid
     * @return Guest
     */
    public function isGuestActive($uid) {
        $sql = 'SELECT * FROM ' . self::TABLE_USER_GUEST . ' WHERE uid = ? AND is_active = 1';
        $result = $this->findEntities($sql, array($uid));
        return (!empty($result));
    }

    /**
     * Check the token's validity
     *
     * @param  string $uid
     * @param  string $token
     * @return Guest
     */
    public function verifyGuestToken($uid, $token) {
        $sql = 'SELECT * FROM ' . self::TABLE_USER_GUEST . ' WHERE uid = ? AND token = ?';
        return $this->findEntities($sql, array($uid, $token));
    }

    /**
     * Delete all data about guest exept user's data
     * @param  string $uid
     */
    public function cleanGuest($uid) {
        $sql = 'DELETE FROM ' . self::TABLE_GUEST_SHARER . ' WHERE uid_guest = ?';
        $this->execute($sql, array($uid));
        $this->deleteGuest($uid);
    }
}
