Ext.onReady( function() {
	Ext.Loader.setPath(
		'BS.RSSStandards',
		mw.config.get('wgScriptPath') + '/extensions/BlueSpiceRSSFeeder/resources/BS.RSSStandards'
	);
	bs.util.registerNamespace( 'bs.rssfeeder.handler' );

	bs.rssfeeder.handler = {
		namespace: function() {
			location.href = Ext.get( 'selFeedNs' ).dom.value;
		},
		watchlist: function() {
			location.href = Ext.get('selFeedWatch').dom.value;
		},
		category: function() {
			location.href = Ext.get('selFeedCat').dom.value;
		},
		page: function() {
			if ( !combo.getValue() ) {
				return;
			}
			var link = combo.getValue().data.feedUrl;
			if ( link ) {
				location.href = link;
			}
		},
		recentchanges: function() {
			if ( Ext.get('rcUnique').dom.checked ) {
				location.href = Ext.get('rcUnique').dom.value;
			} else {
				location.href = Ext.get('rcUnique')
					.dom
					.parentElement
					.querySelector( 'button' )
					.value;
			}
		}
	};

	var buttons = $( '#RSSFeederForm' ).find( 'button' ),
		callbacks = mw.config.get( 'bsRSSFeederFeedCallbacks' ),
		combo = Ext.create( 'BS.RSSStandards.form.field.TitleWithUrlCombo', {
			renderTo: 'divFeedPage'
		} );

	buttons.on( 'click', function( e ) {
		if ( callbacks.hasOwnProperty( id ) ) {
			bs.util.runCallback( callbacks[id] );
			return;
		}
		location.href = $( this ).val();
	} );
} );
