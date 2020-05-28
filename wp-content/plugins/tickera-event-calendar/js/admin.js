jQuery( document ).ready( function( $ ) {
    $( '#tc-calendar-shortcode .color-option' ).live( 'click', function() {
        var selected = $( this ).find( 'input[name="color_scheme"]' ).val();
        $( '#admin_color_' + selected ).prop( 'checked', true );
        //$( this ).siblings( 'input[name="admin_color"]' ).prop( 'checked', true );
    } );
} );
