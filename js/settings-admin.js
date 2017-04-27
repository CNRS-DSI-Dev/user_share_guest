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

    // enregistrement des données
    
    $('#usershareguest-form').submit(function(e) {
        
        e.preventDefault();
        var days = $('#usershareguestinputday').val();
        
        $.ajax({
            type: 'POST',
            url: OC.generateUrl('apps/user_share_guest/saveadmin'),
            dataType: 'json',
            data: {days: days},
            async: false,
            success: function(resp) {
                if (resp.status == 'error') {
                    OCdialogs.info(resp.msg, t('user_share_guest', 'Input error'), function() {}, true);                
                } else {
                    OCdialogs.info(t('user_share_guest', 'Data saved'), t('user_share_guest', 'Share to a guest'));      
                }
            }
        });
    });

    // déclenchement manuel des crons
    $(document).on('click', '.guest_launcher', function(e) {
        e.preventDefault();
        var link = $(this).data('link');
        $.ajax({
            type: 'GET',
            url: link,
            dataType: 'json',
            async: true,
            data:{},
            success: function(resp) {
                if (resp.msg) {
                    OCdialogs.info(t('user_share_guest', resp.msg), '');
                } else {
                    OCdialogs.info(t('user_share_guest', 'Process done'), '');
                }
            }
        });
        /*OCdialogs.confirm(
            t('user_share_guest', 'Confirm action ?'),
            t('user_share_guest', 'Share to a guest'),
            function(ok) {
                if (ok) {
                    
                }
            },
            true);*/
    });
});
