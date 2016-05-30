$(document).ready(function() {
    var oldShowDropdown = OC.Share.showDropDown;

    OC.Share.showDropDown = function(itemType, itemSource, appendTo, link, possiblePermissions, filename) {
        oldShowDropdown(itemType, itemSource, appendTo, link, possiblePermissions, filename);

        $('#shareWithList li').each(function() {
            var that = $(this);
            if(that.data('share-type') == '-1') {
                that.remove();
            }
        });

        var data = OC.Share.loadListGuests();
        var html = '<div id="guest" class="guestShare">';
        html += '<span class="icon-loading-small hidden"></span>';
        html += '<input type="checkbox" name="guestCheckbox" id="guestCheckbox" value="1" />';
        html += '<label for="guestCheckbox">' + t('user_share_guest', 'Guest link') + '</label>';
        html += '<form id="guestForm">';
        html += '<label for="guestInput">' + t('user_share_guest', 'Guest mail') + '</label>';
        html += '<input type="text" name="guestInput" id="guestInput"/>';
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
        }

        html += '</ul>';
        html += '</div>';
        $('#dropdown').append(html);
        if (data.length > 0) {
            $('#dropdown #guestCheckbox').trigger('click');
        }

    }

    OC.Share.loadListGuests = function() {
        var list;
        $.ajax({
            type: 'GET',
            url: OC.generateUrl('apps/user_share_guest/list'),
            dataType: 'json',
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    alert(resp.data.msg)
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
            data: {data: data, itemType: itemType, itemSource:itemSource, itemSourceName, itemSourceName},
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    alert(resp.data.msg)
                    return false;
                }
                var list = resp.data.list;
                var html = '';
                for (var i = 0; i < list.length; i++) {
                    var guest = list[i];
                    html += '<li class="not-active">' + guest + '<span class="guestDelete ui-icon" data-guest-uid ="' + guest + '"> x </span></li>';
                }
                $('#guestList').append(html);
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
                    alert(resp.data.msg)
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
        var itemType = $dropDown.data('item-type');
        var itemSource = $dropDown.data('item-source');
        var itemSourceName = $dropDown.data('item-source-name');
        var data = $('#guestInput').val();
        if (data == '') {
            return false;
        }
        OC.Share.createShareGuest(data, itemType, itemSource, itemSourceName);
    });

    $(document).on('click', '#dropdown #guestList .guestDelete', function() {
        var $dropDown = $('#dropdown');
        var $elem = $(this);
        var itemType = $dropDown.data('item-type');
        var itemSource = $dropDown.data('item-source');
        OC.Share.deleteGuest($elem.data('guest-uid'), itemType, itemSource, $elem);
    });
});
