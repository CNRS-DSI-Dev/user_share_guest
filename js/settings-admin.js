$(document).ready(function() {

	// ajout d'un domaine
	$('#usershareguest-form-domains').submit(function(e) {
		e.preventDefault();
		var $domain = $('#usershareguestdomain');
		if ($domain.val() == '') {
			return false;
		}
		$('#usershareguest-form-domains .securitywarning').html('');
		$.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/user_share_guest/adddomain'),
            dataType: 'json',
            data: {domain: $domain.val()},
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    $('#usershareguest-form-domains .securitywarning').html(t('user_share_guest', resp.msg));
                } else {
                    $('#usershareguest-form-domains ul').append('<li>' + $domain.val() + '<span class="guestDelete ui-icon" data-domain="' + $domain.val() + '"> x </span></li>');
                    $domain.val('');
                }
            }
        });
	});

	// suppression d'un domaine
	$(document).on('click', '#usershareguest-form-domains .guestDelete', function() {
		var $this = $(this);
		var domain = $this.data('domain');
		$.ajax({
            type: 'GET',
            url: OC.generateUrl('apps/user_share_guest/deletedomain'),
            dataType: 'json',
            data: {domain: domain},
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    $('#usershareguest-form-domains .securitywarning').html(t('user_share_guest', resp.msg));
                } else {
                    $this.parent().remove();
                }
            }
        });
	});
});
