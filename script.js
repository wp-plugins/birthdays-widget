//jQuery.ready(function() {
	jQuery.post(ratingsL10n.admin_ajax_url, { 'action' : 'get_birthdays' }, function( data ){
		jQuery( '#birthday' ).html( showNames( data ) );
	});
//});