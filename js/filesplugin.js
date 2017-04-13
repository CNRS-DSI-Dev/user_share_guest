/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function() {
    OCA.UserShareGuest = _.extend({}, OCA.UserShareGuest);
    if (!OCA.UserShareGuest) {
        /**
         * @namespace
         */
        OCA.UserShareGuest = {};
    }

    /**
     * @namespace
     */
    OCA.UserShareGuest.FilesPlugin = {
        ignoreLists: [
            'files_trashbin',
            'files.public'
        ],

        /**
         * 
         * Initialize the UserFilesRestore plugin.
         *
         * @param {OCA.Files.FileList} fileList file list to be extended
         */
        attach: function(fileList) {
            if (this.ignoreLists.indexOf(fileList.id) >= 0) {
                return;
            }

            //fileList.registerTabView(new OCA.UserShareGuest.UserShareGuestTabView());

        } 
    };
})();

OC.Plugins.register('OCA.Files.FileList', OCA.UserShareGuest.FilesPlugin);
