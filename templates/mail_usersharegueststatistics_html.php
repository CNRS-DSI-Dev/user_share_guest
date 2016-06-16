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
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td>
            <table cellspacing="0" cellpadding="0" border="0" width="600px">
                <tr>
                    <td bgcolor="<?php p($theme->getMailHeaderColor());?>" width="20px">&nbsp;</td>
                    <td bgcolor="<?php p($theme->getMailHeaderColor());?>">
                        <img src="<?php p(OC_Helper::makeURLAbsolute(image_path('user_deletion_request', 'logo-mail.gif'))); ?>" alt="<?php p($theme->getName()); ?>"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="20px">&nbsp;</td>
                    <td style="font-weight:normal; font-size:0.8em; line-height:1.2em; font-family:verdana,'arial',sans;">
                        <?php p($l->t('Here is the summary of the guest accounts that you created during the year : ')); ?>
                        <?php foreach ($data as $share_with => $datashare) :
                        ?>
                        <strong><?php print_unescaped($share_with); ?></strong>
                        <ul>
                        <?php
                            foreach ($datashare as $share) :
                            ?>
                                <li><?php print_unescaped($share['item_source']); ?> (<?php print_unescaped($share['item_type']); ?>)</li>
                            <?php
                            endforeach;
                        ?>
                        </ul>
                        <?php
                        endforeach;
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="20px">&nbsp;</td>
                    <td style="font-weight:normal; font-size:0.8em; line-height:1.2em; font-family:verdana,'arial',sans;">--<br>
                        <?php p($theme->getEntity()); ?> -
                        <?php p($theme->getSlogan()); ?>
                        <br><a href="<?php p($theme->getDocBaseUrl()); ?>"><?php p($theme->getDocBaseUrl());?></a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
