$(document).ready(function() {
    _.defer(function() {
        var FileList = OCA.Sharing.App.initSharingIn($('#app-content-sharingin'));

        FileList.reload = function() {
            this.showMask();
            if (this._reloadCall) {
                this._reloadCall.abort();
            }
            this._reloadCall = $.ajax({
                 url: OC.generateUrl('apps/user_share_guest/share_list_user'),
                type: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('OCS-APIREQUEST', 'true');
                }
            });
            var callBack = this.reloadCallback.bind(this);
            return this._reloadCall.then(callBack, callBack);
        },
        FileList.reloadCallback = function(result) {
            delete this._reloadCall;
            this.hideMask();

            this.$el.find('#headerSharedWith').text(
                t('files_sharing', this._sharedWithUser ? 'Shared by' : 'Shared with')
            );

            if (result.status == 'success' && result.data) {

                this.setFiles(this._makeFilesFromShares(result.data.list));
            }
            else {
                // TODO: error handling
            }
        },

        delete FileList.fileActions.actions.all.Restore;
        delete FileList.fileActions.actions.all.Delete;
        delete FileList.fileActions.actions.all.Share;

        FileList.reload();
        OCA.Sharing.Util.initialize(FileList.fileActions);
    });
});
