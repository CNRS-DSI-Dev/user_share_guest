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
     * Get guest list. If an uid is set, return only one guest. 
     *
     * @param  string   $uid
     * @param  int      $limit
     * @param  int      $offset
     * @return array
     */
    public function getGuests($uid = null,  $limit = null, $offset = null) {
        $data = array();
        $sql = 'SELECT ug.* FROM ' . self::TABLE_USER_GUEST . ' AS ug';

        if (!empty($uid) || !empty($uid_sharer)) {

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
     * Save a guest share on db
     * @param  string $shareType  
     * @param  string $itemType   
     * @param  string $itemSource
     * @param  string $fileTarget 
     * @param  string $shareWith  
     * @param  string $uidOwner   
     * @param  string $sharedBy   
     * @param  string $permissions
     * @return int
     */
    public function saveGuestShare($shareType, $itemType, $itemSource, $fileTarget, $shareWith, $uidOwner, $sharedBy, $permissions) {
        $qb = $this->db->getQueryBuilder();
        $qb->insert('share')
        ->setValue('share_type', $qb->createNamedParameter($shareType))
        ->setValue('item_type', $qb->createNamedParameter($itemType))
        ->setValue('item_source', $qb->createNamedParameter($itemSource))
        ->setValue('file_source', $qb->createNamedParameter($itemSource))
        ->setValue('file_target', $qb->createNamedParameter($fileTarget))
        ->setValue('share_with', $qb->createNamedParameter($shareWith))
        ->setValue('uid_owner', $qb->createNamedParameter($uidOwner))
        ->setValue('uid_initiator', $qb->createNamedParameter($sharedBy))
        ->setValue('permissions', $qb->createNamedParameter($permissions));

        $qb->execute();
        $id = $qb->getLastInsertId();

        return (int)$id;
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


    /**********
      OTHER
    **********/

    /**
     * Return the number of sharers for a guest
     *
     * @param  string $uid
     * @return int
     */
    public function countSharesToGuest($uid) {
        $sql = 'SELECT count(share_with) as count FROM ' . self::TABLE_SHARE . ' WHERE share_with = ?';
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
}
