( ( $ ) => {

	$( () => {
		const $container = $( '#bs-rssfeeder-special-rssfeeder-container' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel();

		$container.append( panel.$element );
	} );

} )( jQuery );
