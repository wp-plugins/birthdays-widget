jQuery( document ).ready( function() {
    jQuery( '.delete_link' ).click( function() {
        var tmp = jQuery( '#delete-msg' ).html();
        return confirm( tmp );
    } );
    if ( jQuery( '#birthday_date' ).length >= 1 ) {
        jQuery( '#birthday_date' ).datepicker( {
            changeMonth: true,
            changeYear: true,
            maxDate: "+0D",
            "dateFormat" : "dd-mm-yy"
        } );
        jQuery( '#ui-datepicker-div' ).hide();
    }
    if ( jQuery( '#birthday_table' ).length >= 1 ) {
        jQuery( '#birthday_table' ).DataTable( {
            stateSave: true,
            "lengthMenu": [ 15, 30, 100 ],
            "columnDefs": [ { "orderable": false, "targets": 3 } ],
            "stripeClasses": [ 'alternate', '' ],
            "processing": true,
            "deferRender": true
        } );
        jQuery( document ).tooltip( {
            items: ".list-image",
            content: function() {
                var element = jQuery( 'a', this );
                if ( element.length >= 1 ) {
                    return '<img src="'+element.attr( 'href' )+'" alt="User\'s Image" style="width: 200px;" />';
                }
            },
            show: {
                effect: "slideDown",
                delay: 250
            },
            open: function (event, ui) {
                ui.tooltip.addClass( 'birthday-list-tooltip' );
            }
        } );
    }
    
    if ( jQuery( '.bw-image' ).length >= 1 ) {
        // Uploading files
        var file_frame;
        jQuery( '.upload_image_button' ).live( 'click', function( event ) {
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.url_input = jQuery( this ).attr( 'data-input' );
                file_frame.open();
                return;
            }
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Please select an image:',
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' ),
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });
            file_frame.url_input = jQuery( this ).attr( 'data-url-input' );
            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                jQuery( '#'+file_frame.url_input ).val( attachment.id );
                jQuery( '#'+file_frame.url_input+'_preview' ).attr( 'src', attachment.url );
            });
            // Finally, open the modal
            file_frame.open();
        } );
        jQuery( '.default-image' ).click( function() {
            var deflt = jQuery( this ).attr( 'data-default-image' );
            var url_input = jQuery( this ).attr( 'data-url-input' );
            jQuery( '#'+url_input ).val( deflt );
            jQuery( '#'+url_input+'_preview' ).attr( 'src', deflt );
            //jQuery( this ).siblings( '.bw-image' ).val(  );
        } );
        jQuery( '.disable-image' ).click( function() {
            var element = jQuery( this );
            var flag = jQuery( this ).siblings( '.default-image' ).prop( 'disabled' );
            if ( flag ) {
                jQuery( this ).siblings( '.default-image' ).prop( 'disabled', false );
                jQuery( this ).siblings( '.bw-image' ).prop( 'disabled', false );
                jQuery( this ).siblings( '.select-image' ).prop( 'disabled', false );
                jQuery( this ).siblings( '.disable-img' ).val( '1' );
                element.val( 'Disable Image' );
            } else {
                jQuery( this ).siblings( '.bw-image' ).prop( 'disabled', true );
                jQuery( this ).siblings( '.default-image' ).prop( 'disabled', true );
                jQuery( this ).siblings( '.select-image' ).prop( 'disabled', true );
                jQuery( this ).siblings( '.disable-img' ).val( '0' );
                element.val( 'Enable Image' );
            }
        } );
    }
    if ( jQuery( '.color_field' ).length >=1 ) {
        jQuery( document ).ready( function($) {
            $( '.color_field' ).wpColorPicker();
        } );
    }
    jQuery( '#second_color' ).change( function() {
        jQuery( '.birthdays_hidden' ).toggleClass( 'hidden' );
    } );
    jQuery( '#wp_users_export' ).click( function() {
        var elem = jQuery( '#birthdays-export-button' );
        
        if( jQuery( this ).prop( 'checked' ) ) {
            elem.attr( 'href', elem.attr( 'href' ) + '&wp_users=yes' );
        } else {
            elem.attr( 'href', elem.attr( 'data-orig-link' ) );
        }
    } );
    jQuery( '.opt_item' ).click( function() {
        /* Unselect all other items */
        jQuery( '.opt_item' ).removeClass( 'opt_item_selected' );
        /* Handle the select element for birthday date meta field */
        var slc = jQuery( this ).find( 'select[name="birthdays_date_meta_field"]' );                    
        /*
         * If the select element is not inside the current option item,
         * then disable the select item otherwise enable it
        */
        if ( slc.length == 0 )
            jQuery( 'select[name="birthdays_date_meta_field"]' ).prop( 'disabled', true );
        else 
            slc.prop( 'disabled', false );
        /* Make the item selected */
        jQuery( this ).addClass( 'opt_item_selected' );
        /* Select current radio button */
        var elm = jQuery( this ).find( 'input:first' );
        elm.prop( 'checked', true );
    } );
    jQuery( '.nav-tab-wrapper > a' ).click( function() {
        jQuery( '.fade' ).hide();
        jQuery( '.nav-tab-wrapper > a' ).removeClass( 'nav-tab-active' );
        jQuery( this ).addClass( 'nav-tab-active' );
        jQuery( '.table' ).addClass( 'ui-tabs-hide' );
        var item_clicked = jQuery( this ).attr( 'href' );
        jQuery( item_clicked ).removeClass( 'ui-tabs-hide' );
        return false;
    } );

    if ( jQuery( '.birthdays-widget' ).length >= 1 ) {
        jQuery( document ).tooltip( {
            items: ".birthday_element",
            content: function() {
                var element = jQuery( 'a', this );
                if ( element.length >= 1 ) {
                    var str = '<img src="'+element.attr( 'href' )+'" alt="User\'s Image" style="width: 200px;" />';
                    if ( element.attr( 'data-age' ) )
                        str += '<br /><span class="birthday_age" >'+element.attr( 'data-age' )+'</span>';
                    return str;
                }
            },
            show: {
                effect: "slideDown",
                delay: 250
            },
            open: function (event, ui) {
                ui.tooltip.addClass( 'birthday-list-tooltip' );
            }
        } );
    }
} );


