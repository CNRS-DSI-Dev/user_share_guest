$(document).ready(function() {
    var oldShowDropdown = OC.Share.showDropDown;
    var itemType = '';
    var itemSource = '';
    var itemSourceName = '';

    OC.Share.showDropDown = function(itemType, itemSource, appendTo, link, possiblePermissions, filename) {
        oldShowDropdown(itemType, itemSource, appendTo, link, possiblePermissions, filename);

        $('#shareWithList li').each(function() {
            var that = $(this);
            if(that.data('share-type') == '-1') {
                that.remove();
            }
        });

        var data = OC.Share.loadListGuests(itemType, itemSource);
        var html = '<div id="guest" class="guestShare">';
        html += '<span class="icon-loading-small hidden"></span>';
        html += '<input type="checkbox" name="guestCheckbox" id="guestCheckbox" value="1" />';
        html += '<label for="guestCheckbox">' + t('user_share_guest', 'Guest link') + '</label>';
        html += '<form id="guestForm">';
        html += '<label for="guestInput">' + t('user_share_guest', 'Guest mail') + '</label>';
        html += '<input type="text" name="guestInput" id="guestInput"/>';
        html += '<span class="icon-loading-small hidden" id="guestLoad"></span>';
        html += '<input type="submit" name="guestSubmit" id="guestSubmit" value="' + t('user_share_guest', 'Guest submit') + '"/>';
        html += '</form>';
        html += '<ul id="guestList">';

        for (var i = 0; i < data.length; i++) {
            var guest = data[i];
            var class_active = 'class="not-active"';
            if (guest.is_active == '1' ) {
                class_active = '';
            }
            html += '<li ' + class_active + '>' + guest.uid + '<span class="guestDelete ui-icon" data-guest-uid ="' + guest.uid + '"> x </span></li>';
            $('#shareWithList li[title="' + guest.uid + '"]').remove();
        }

        html += '</ul>';
        html += '</div>';
        $('#dropdown').append(html);
        if (data.length > 0) {
            $('#dropdown #guestCheckbox').trigger('click');
        }

        $('#shareWith').autocomplete({source: function(search, response) {
                var $loading = $('#dropdown .shareWithLoading');
                $loading.removeClass('hidden');
                $.get(OC.filePath('core', 'ajax', 'share.php'), { fetch: 'getShareWith', search: search.term.trim(), itemShares: OC.Share.itemShares }, function(result) {
                    $loading.addClass('hidden');
                    if (result.status == 'success' && result.data.length > 0) {
                        var data = result.data;
                        $.ajax({
                            type: 'GET',
                            url: OC.generateUrl('apps/user_share_guest/is_guest'),
                            data: {data: result.data},
                            datatype: 'json',
                            async: false,
                            success : function(resp) {
                                if (resp.status == 'error') {
                                    generatePopinGuest(resp.data.msg, false);
                                    return false;
                                }
                                data = resp.data;
                            }
                        });
                    }
                    if (data.length > 0) {
                        $( "#shareWith" ).autocomplete( "option", "autoFocus", true );
                        response(data);
                    } else {
                        response();
                    }
                });
            }
        });
    }

    OC.Share.loadListGuests = function(itemType, itemSource) {
        var list;
        $.ajax({
            type: 'GET',
            url: OC.generateUrl('apps/user_share_guest/list'),
            data:{itemType: itemType, itemSource: itemSource},
            dataType: 'json',
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    generatePopinGuest(resp.data.msg, false);
                    return false;
                }
                list = resp.data.list;
            }
        });
        return list;
    }

    OC.Share.createShareGuest = function(data, itemType, itemSource, itemSourceName) {
        $.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/user_share_guest/create'),
            dataType: 'json',
            data: {uid: data, itemType: itemType, itemSource:itemSource, itemSourceName, itemSourceName},
            async: false,
            success: function(resp) {
                $('#guestSubmit').show();
                $('#guestLoad').addClass('hidden');
                if (resp.status == 'error') {
                    generatePopinGuest(resp.data.msg, false);
                    return false;
                }
                var user = resp.data.user;

                if (user.is_guest) {
                    var html = '';
                    var class_active = 'class="not-active"';
                    if (user.is_active == '1' ) {
                        class_active = '';
                    }
                    html += '<li ' + class_active + '>' + user.uid + '<span class="guestDelete ui-icon" data-guest-uid ="' + user.uid + '"> x </span></li>';
                    $('#guestList').append(html);
                } else {
                    OC.Share.addShareWith(0, user.uid, user.uid, false, 31, false, false);
                }
                $('#guestInput').val('');
            }
        });
    }

    OC.Share.deleteGuest = function(uid, itemType, itemSource, elem) {
        $.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/user_share_guest/delete'),
            dataType: 'json',
            data: {uid: uid, itemType: itemType, itemSource: itemSource},
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    generatePopinGuest(resp.data.msg, false);
                    return false;
                }
                elem.parent('li').remove();
            }
        });
    }

    OC.Share.hideGuest = function() {
        $('#guestForm').hide('blind');
        $('#guestList').hide('blind');
    }

    OC.Share.showGuest = function() {
        $('#guestForm').show('blind');
        $('#guestList').show('blind');
    }

    $(document).on('change', '#dropdown #guestCheckbox', function() {
        if (this.checked) {
            OC.Share.showGuest();
        } else {
            OC.Share.hideGuest();
        }
    });

    $(document).on('click', '#dropdown #guestSubmit', function(event) {
        event.preventDefault();
        var $dropDown = $('#dropdown');
        itemType = $dropDown.data('item-type');
        itemSource = $dropDown.data('item-source');
        itemSourceName = $dropDown.data('item-source-name');
        var data = $('#guestInput').val();
        if (data == '') {
            return false;
        }
        $.ajax({
            type: 'GET',
            url: OC.generateUrl('apps/user_share_guest/is_guest_creation'),
            dataType: 'json',
            data: {uid: data},
            async: false,
            success: function(resp) {
                if (resp.data.exist == false) {
                    var txt = t('user_share_guest', 'Attention, you well took knowledge of the risks regarding the sharing of file to a guest account.');
                    generatePopinGuest(txt, true)
                } else {
                    $('#guestSubmit').hide();
                    $('#guestLoad').removeClass('hidden');
                    setTimeout(launchCreateGuest, 0);
                }
            }
        });
    });

    function launchCreateGuest() {
        var data = $('#guestInput').val();

        OC.Share.createShareGuest(data, itemType, itemSource, itemSourceName);
    }

    $(document).on('click', '#dropdown #guestList .guestDelete', function() {
        var $dropDown = $('#dropdown');
        $elem = $(this);
        itemType = $dropDown.data('item-type');
        itemSource = $dropDown.data('item-source');
        OC.Share.deleteGuest($elem.data('guest-uid'), itemType, itemSource, $elem);
    });



    function generatePopinGuest(txt, addbutton) {
        var html = '<div id="calque-shareguest-popin"><div id="shareguest-popin">';
        html += '<span class="close">X</span>';
        html += '<p>' + txt + '</p>';
        if (addbutton) {
            html += '<button class="validate">Valider</button> <button class="cancel">Annuler</button>'
        }
        html += '</div></div>';
        $('body').append(html);

        $('#calque-shareguest-popin, #calque-shareguest-popin .close, #calque-shareguest-popin .cancel').click(function(){
            event.stopPropagation();
            $('#calque-shareguest-popin').remove();
        });

        $('#calque-shareguest-popin .validate').click(function(event){
            $('#calque-shareguest-popin').remove();
            event.stopPropagation();
            $('#guestSubmit').hide();
            $('#guestLoad').removeClass('hidden');
            setTimeout(launchCreateGuest, 0);
        })
    }

});
