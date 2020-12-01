Ext.onReady( function() {
	Ext.Loader.setPath(
		'BS.RSSStandards',
		mw.config.get('wgScriptPath') + '/extensions/BlueSpiceRSSFeeder/resources/BS.RSSStandards'
	);
	bs.util.registerNamespace( 'bs.rssfeeder.handler' );

	bs.rssfeeder.handler = {
		namespace: function() {
			return Ext.get( 'selFeedNs' ).dom.value;
		},
		watchlist: function() {
			return Ext.get('selFeedWatch').dom.value;
		},
		category: function() {
			return Ext.get('selFeedCat').dom.value;
		},
		page: function() {
			if ( !combo.getValue() ) {
				return null;
			}
			if ( combo.getValue().data.feedUrl ) {
				return combo.getValue().data.feedUrl;
			}
		}
	};

	var handleUniqueRCCheckbox = function( feedId, link ) {
		if ( link && feedId && Ext.get( 'RcUnique_' + feedId ) ) {
			if ( Ext.get( 'RcUnique_' + feedId ).dom.checked ) {
				link = link + '&rc_unique=1';
			}
		}
		return link;
	};

	var buttons = $( '#RSSFeederForm' ).find( 'button' ),
		callbacks = mw.config.get( 'bsRSSFeederFeedCallbacks' ),
		combo = Ext.create( 'BS.RSSStandards.form.field.TitleWithUrlCombo', {
			renderTo: 'divFeedPage'
		} );

	buttons.on( 'click', function( e ) {
		var id = e.target.id;
		var link =  $( this ).val();
		if ( callbacks.hasOwnProperty( id ) ) {
			link = bs.util.runCallback( callbacks[id] );
		}
		link = handleUniqueRCCheckbox( id, link );
		if ( link ) {
			location.href = link;
		}
	} );
} );
