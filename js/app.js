/**
 * ownCloud - User Files Restore
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2016 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

(function(OCA, OC, OCdialogs, $) {
    if (!OCA.UserShareGuest) {
        /**
         * @namespace
         */
        OCA.UserShareGuest = {};
    }

    /**
     * util
     */
    OCA.UserShareGuest.Util = {
        _restoreFile: function(file, revision, type, freeCreate) {
            return false;
            
            var currentdate = new Date();
            // formating date
            var userdate =  currentdate.getFullYear() + '-';
            if (currentdate.getMonth() + 1 < 10) {
                userdate += '0' + (currentdate.getMonth() + 1) + '-';
            } else {
                userdate += (currentdate.getMonth() + 1) + '-';
            }
            userdate += currentdate.getDate() + ' ' + currentdate.getHours() + ':' + currentdate.getMinutes() + ':' + currentdate.getSeconds();

            $.ajax({
                type: 'POST',
                url: OC.generateUrl('apps/user_files_restore/api/1.0/request'),
                dataType: 'json',
                data: {file: file, version: revision, filetype: type, userdate: userdate},
                async: false,
                success: function(response) {
                    if (response.status === 'error') {
                        if (response.data.msg != '') {
                            OC.Notification.show(response.data.msg);
                        }
                        else {
                            OC.Notification.show( t('user_files_restore', 'Failed to create Restore request for {file}.', {file:file}) );
                        }
                        setTimeout(OC.Notification.hide, 7000);
                    }
                    else if (response.status === 'collision_error') {
                        OCdialogs
                            .message(
                                '____________________________________',
                                t('user_files_restore', 'Restore request'),
                                'info',
                                OCdialogs.OK_BUTTON,
                                function() {
                                    if (freeCreate) {
                                        location.reload();
                                    }
                                },
                                true)
                            .then(function() {
                                var contentDiv = $('div.oc-dialog-content p');
                                var toKeep = JSON.parse(response.data.toKeep);
                                var toCancel = JSON.parse(response.data.toCancel);

                                if (toCancel.length > 0) {
                                    var msg = "<b>" + t('user_files_restore', "Your request collided with previous request(s).") + "</b></br></br>";
                                    msg = msg + t('user_files_restore', "These precedent requests will be automatically cancelled: ") + "</br>";
                                    msg = msg + toCancel.join('</br>') + "</br>";

                                    contentDiv.html(msg);
                                }
                            });
                    }
                    else {
                        OC.Notification.show( t('user_files_restore', 'Request successfully created') );
                        setTimeout(OC.Notification.hide, 7000);

                        $('#dropdown').hide('blind', function() {
                            $('#dropdown').closest('tr').find('.modified:first').html(relative_modified_date(revision));
                            $('#dropdown').remove();
                            $('tr').removeClass('mouseOver');
                        });

                        if (freeCreate) {
                            location.reload();
                        }
                    }
                }
            });
        },

        domReady: function(){
            var self = this;
            
            /*
            this.guestShareView.$el = OC.Share.$el.find('.guestShareView');
            this.guestShareView.render();
            return false;*/


            // if public, don't do anything
            if ($('#isPublic').val()){
                return;
            }

            // allow global restore request
            if ($('#freeCreate input[type=button]')) {
                $('#freeCreate input[type=button]').on('click', function(event) {
                    // ask to confirm
                    event.preventDefault();
                    OCdialogs.confirm(
                        t('user_files_restore', 'Are you sure to CONFIRM this global restoration request? All your files will be overwritten by this restoration.'),
                        t('user_files_restore', 'Confirm global migration request'),
                        self.confirmGlobalRestorationRequest.bind(self),
                        true
                    );
                });

                // $('#freeCreate p.header img').tipsy({html: true });
                // $('#running p.header img').tipsy({html: true });
                $('#done span.errorback span').tipsy({html: true });

                $('#freeCreate p.header img').hover(
                    function() {
                        $('#infos__detail .freeCreate').addClass('highlight');
                    },
                    function() {
                        $('#infos__detail .freeCreate').removeClass('highlight');
                    }
                );
                $('#running p.header img').hover(
                    function() {
                        $('#infos__detail .running .to_highlight').addClass('highlight');
                    },
                    function() {
                        $('#infos__detail .running .to_highlight').removeClass('highlight');
                    }
                );
            }

            // allow cancel a request
            $('#todo').on('click', 'span.cancel', function() {
                var id = $(this).attr('data-id');
                self.cancelRequest(id);
            });
        },

        confirmGlobalRestorationRequest: function(response) {
            if (response == false) {
                return;
            }

            var version = $('#freeCreate option:selected').val();
            this._restoreFile('/', version, 'dir', true);
        },

        cancelRequest: function(idRequest) {
            $.ajax({
                type: 'POST',
                url: OC.generateUrl('apps/user_files_restore/api/1.0/cancel'),
                dataType: 'json',
                data: {id: idRequest},
                async: false,
                success: function(response) {
                    if (response.status === 'error') {
                        OC.Notification.show( t('user_files_restore', 'Failed to cancel Restore request.') );
                        $('p#'+idRequest).addClass('error');
                        setTimeout(function() {
                            $('p#'+idRequest).removeClass('error');
                            OC.Notification.hide();
                        }, 10000);
                    } else {
                        $('p#'+idRequest).remove();
                    }
                }
            });
        }
    }

})(OCA, OC, OCdialogs, jQuery);

