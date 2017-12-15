Ext.onReady( function() {
	Ext.Loader.setPath(
		'BS.RSSStandards',
		mw.config.get('wgScriptPath') + '/extensions/BlueSpiceRSSFeeder/resources/BS.RSSStandards'
	);

	var combo;

	// TODO SW: make generic
	var buttons = {
		rc: Ext.get('btnFeedRc'),
		own: Ext.get('btnFeedOwn'),
		page: Ext.get('btnFeedPage'),
		ns: Ext.get('btnFeedNs'),
		cat: Ext.get('btnFeedCat'),
		watch: Ext.get('btnFeedWatch')
	};

	buttons.rc.addListener('click', function() {
		location.href = this.dom.value;
	});

	buttons.own.addListener('click', function() {
		location.href = this.dom.value;
	});

	buttons.ns.addListener('click', function() {
		location.href = Ext.get('selFeedNs').dom.value;
	});

	buttons.cat.addListener('click', function() {
		location.href = Ext.get('selFeedCat').dom.value;
	});

	buttons.watch.addListener('click', function() {
		location.href = Ext.get('selFeedWatch').dom.value;
	});

	combo = Ext.create( 'BS.RSSStandards.form.field.TitleWithUrlCombo', {
		renderTo: 'divFeedPage'
	});

	buttons.page.addListener( 'click', function(){
		if ( !combo.getValue() ) {
			return;
		}
		var link = combo.getValue().data.feedUrl;
		if ( link ) {
			location.href = link;
		}
	});
});