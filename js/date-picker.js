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
    }
    
    if ( jQuery( '#bw-image' ).length >= 1 ) {
        // Uploading files
        var file_frame;
        jQuery( '.upload_image_button' ).live( 'click', function( event ){
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.open();
                return;
            }
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' ),
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });
            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                jQuery( '#bw-image' ).val(attachment.url);
            });
            // Finally, open the modal
            file_frame.open();
          });
          
          jQuery( '#default-image' ).click( function() {
            var deflt = jQuery( '#default-image' ).attr( 'data-default-image' );
            jQuery( '#bw-image' ).val( deflt );
          });
          
          jQuery( '#disable-image' ).click( function() {
            var element = jQuery( '#disable-image' );
            var flag = jQuery( '#default-image' ).prop( 'disabled' );
            if ( flag ) {
                jQuery( '#default-image' ).prop( 'disabled', false );
                jQuery( '#bw-image' ).prop( 'disabled', false );
                jQuery( '#select-image' ).prop( 'disabled', false );
                jQuery( '#disable-img' ).val( '1' );
                element.val( 'Disable Image' );
            } else {
                jQuery( '#bw-image' ).prop( 'disabled', true );
                jQuery( '#default-image' ).prop( 'disabled', true );
                jQuery( '#select-image' ).prop( 'disabled', true );
                jQuery( '#disable-img' ).val( '0' );
                element.val( 'Enable Image' );
            }
          });

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
    }

    jQuery( '.nav-tab-wrapper > a' ).click( function() {
        jQuery( '.fade' ).hide();
        jQuery( '.nav-tab-wrapper > a' ).removeClass( 'nav-tab-active' );
        jQuery( this ).addClass( 'nav-tab-active' );
        jQuery( '.table' ).addClass( 'ui-tabs-hide' );
        var item_clicked = jQuery( this ).attr( 'href' );
        jQuery( item_clicked ).removeClass( 'ui-tabs-hide' );
        return false;
    } );
});