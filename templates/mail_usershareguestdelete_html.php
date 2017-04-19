<?php

/**
 * ownCloud - User Share Guest
 *
 * @author Victor Bordage-Gorry <victor.bordage-gorry@globalis-ms.com>
 * @copyright 2016 CNRS DSI / GLOBALIS media systems
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

 $l = $_['overwriteL10N']; ?>

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
                        <p><?php p($l->t('Hello,')); ?></p>
                        <p><?php p($l->t('No more sharing on Mycore is connected to your guest account, the account has therefore been deleted.'));?></p>
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
