$(document).ready(function() {
    $('#set_guest_password').submit(function(e) {
        e.preventDefault();
        $('.notification').html('');
        var uid = $('input[name=uid]').val();
        var password = $('input[name=password]').val();
        var passwordconfirm = $('input[name=passwordconfirm]').val();
        $.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/user_share_guest/accept'),
            dataType: 'json',
            data: {uid: uid, password: password, passwordconfirm: passwordconfirm},
            async: false,
            success: function(resp) {
                if (resp == null) {
                    $('.notification').html(t('user_share_guest', 'Error : please, check password\'s validity'));
                    $('input[name=password]').val('');
                    $('input[name=passwordconfirm]').val('');
                } else if(resp.status == 'error') {
                    $('.notification').html(t('user_share_guest', resp.msg));
                    $('input[name=password]').val('');
                    $('input[name=passwordconfirm]').val('');
                } else {
                    OC.redirect(OC.generateUrl('apps/files'));
                }
            }
        });
    });
});
