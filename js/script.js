$(document).ready(function() {
    var oldShowDropdown = OC.Share.showDropDown;

    OC.Share.showDropDown = function(itemType, itemSource, appendTo, link, possiblePermissions, filename) {
        oldShowDropdown(itemType, itemSource, appendTo, link, possiblePermissions, filename);

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
            html += '<li ' + class_active + '>' + guest.uid + '<span class="guestDelete ui-icon" data-guest-id ="' + guest.uid + '"> x </span></li>';
        }

        html += '</ul>';
        html += '</div>';
        $('#dropdown').append(html);

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

    OC.Share.createGuest = function(uid) {
        $.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/user_share_guest/create'),
            dataType: 'json',
            data: {uid: uid},
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    alert(resp.data.msg)
                    return false;
                }
                var html = '<li class="not-active">' + resp.data.uid + '<span class="guestDelete ui-icon" data-guest-id ="' + resp.data.uid + '"> x </span></li>';
                $('#guestList').append(html);
                $('#guestInput').val('');
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
        var data = $('#guestInput').val();
        if (data == '') {
            return false;
        }

        OC.Share.createGuest(data);

    });

    $(document).on('click', '#dropdown #guestList .guestDelete', function() {
        var $elem = $(this);
        console.log($elem.data('guest-id'));
        $elem.parent('li').remove();
    });
});
