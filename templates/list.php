<?php
    \OCP\Util::addStyle('files','files');
?>

<header>
    <div id="header">
        <a href="<?php print_unescaped(link_to('', 'index.php')); ?>"
            title="" id="owncloud">
            <div class="logo-icon svg"></div>
        </a>
        <div id="logo-claim" style="display:none;"><?php p($theme->getLogoClaim()); ?></div>
        <div id="settings" class="svg">
            <span id="expand" tabindex="0" role="link">
                <span id="expandDisplayName"><?php  p(trim($_['user_displayname']) != '' ? $_['user_displayname'] : $_['user_uid']) ?></span>
                <img class="svg" alt="" src="<?php print_unescaped(image_path('', 'actions/caret.svg')); ?>" />
            </span>
            <div id="expanddiv">
            <ul>
                <li>
                    <a id="info" target="_blank" href="<?php print_unescaped(OC_Config::getValue('custom_knowledgebaseurl',''));?>">
                        <img class="svg" alt="" src="<?php echo OCP\Util::imagePath('settings','help.svg'); ?>" />
                        <?php p($l->t('Help'));?>
                    </a>
                </li>
                <li>
                    <a id="logout" <?php print_unescaped(OC_User::getLogoutAttribute()); ?>>
                        <img class="svg" alt="" src="<?php print_unescaped(image_path('', 'actions/logout.svg')); ?>" />
                        <?php p($l->t('Log out'));?>
                    </a>
                </li>
            </ul>
            </div>
        </div>
    </div>
</header>
<div id="content-wrapper">
    <div id="content">
        <div id="app-content">
            <div id="app-content-sharingin" class="viewcontainer">
                <div id="controls">
                    <div id="file_action_panel"></div>
                </div>
                <div id='notification'></div>

                <div id="emptycontent" class="hidden"></div>

                <input type="hidden" name="dir" value="" id="dir">

                <table id="filestable">
                    <thead>
                        <tr>
                            <th id='headerName' class=" column-name">
                                <div id="headerName-container">
                                    <label for="select_all_files"></label>
                                    <a class="name sort columntitle" data-sort="name"><span><?php p($l->t( 'Name' )); ?></span><span class="sort-indicator"></span></a>
                                    <span id="selectedActionsList" class="selectedActions">
                                        <a href="" class="download">
                                            <img class="svg" alt="Download"
                                                 src="<?php print_unescaped(OCP\image_path("core", "actions/download.svg")); ?>" />
                                            <?php p($l->t('Download'))?>
                                        </a>
                                    </span>
                                </div>
                            </th>
                            <th id="headerDate" class="column-mtime">
                                <a id="modified" class="columntitle" data-sort="mtime"><span><?php p($l->t( 'Modified' )); ?></span><span class="sort-indicator"></span></a>
                                    <span class="selectedActions"><a href="" class="delete-selected">
                                        <?php p($l->t('Delete'))?>
                                        <img class="svg" alt="<?php p($l->t('Delete'))?>"
                                             src="<?php print_unescaped(OCP\image_path("core", "actions/delete.svg")); ?>" />
                                    </a></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="fileList">

                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
    \OCP\Util::addScript('files','app');
    \OCP\Util::addScript('files','files');
    \OCP\Util::addScript('files','fileactions');
    \OCP\Util::addScript('files','navigation');
    \OCP\Util::addScript('files','filesummary');
    \OCP\Util::addScript('files','breadcrumb');
    \OCP\Util::addScript('files','keyboardshortcuts');
    \OCP\Util::addScript('files','filelist');

    \OCP\Util::addScript('files_sharing','share');
    \OCP\Util::addScript('files_sharing','app');
    \OCP\Util::addScript('files_sharing','sharedfilelist');

    \OCP\Util::addScript('user_share_guest','share_list');
?>
