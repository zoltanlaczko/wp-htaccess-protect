jQuery( document ).ready(function(){
	if( jQuery( '#zotya_hp .not-working').length ){
		jQuery( '#zotya_hp input' ).prop( "disabled", true ).css( 'opacity', '0.4' );
	}
});
