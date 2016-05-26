$(document).ready(function() {
    var oldShowDropdown = OC.Share.showDropDown;

    OC.Share.showDropDown = function(itemType, itemSource, appendTo, link, possiblePermissions, filename) {
        oldShowDropdown(itemType, itemSource, appendTo, link, possiblePermissions, filename);

        var data = [
            {'id' : 1, 'mail' : 'test 1'},
            {'id' : 2, 'mail' : 'test 2'},
            {'id' : 3, 'mail' : 'test 3'}
        ];

        // insertAfter
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
            var invit = data[i];
            html += '<li>' + invit.mail + '<span class="guestDelete ui-icon" data-guest-id ="' + invit.id + '"> x </span></li>';
        }

        html += '</ul>';
        html += '</div>';
        $('#dropdown').append(html);
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
        var $dropDown = $('#dropdown');
        var itemType = $dropDown.data('item-type');
        var itemSource = $dropDown.data('item-source');
        var itemSourceName = $dropDown.data('item-source-name');
        var $loading = $dropDown.find('#link .icon-loading-small');
        var $button = $(this);

        if (this.checked) {
            OC.Share.showGuest();
        } else {
            OC.Share.hideGuest();
        }
    });

    $(document).on('click', '#dropdown #guestSubmit', function(event) {
        event.preventDefault();
        var data = $('#guestInput').val();
        var html = '<li>' + data + '<span class="guestDelete ui-icon" data-guest-id ="' + 9 + '"> x </span></li>';
        $("#guestList").append(html);
    });

    $(document).on('click', '#dropdown #guestList .guestDelete', function() {
        var $elem = $(this);
        console.log($elem.data('guest-id'));
        $elem.parent('li').remove();
    });
});
