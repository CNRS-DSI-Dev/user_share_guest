<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

    $l = $_['overwriteL10N'];
    $data = $_['data'];

    foreach ($data as $share_with => $datashare) {
        echo '<br/> - ' . print_unescaped($share_with) . ' : <br/>';
        p($l->t('(Last activity : %s )', $datashare['activity'])) . '<br/>';
        p($l->t('Here is the summary of the guest accounts that you created during the year : '));
        echo "<br/>";
        foreach ($datashare as $values) {
            echo print_unescaped($values['item_source']) . ' (' . print_unescaped($values['item_type']) . ')<br/>';
        }
    }
?>

--
<?php p($theme->getName() . ' - ' . $theme->getSlogan()); ?>
<?php print_unescaped("\n".$theme->getBaseUrl());
