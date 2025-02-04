bs.util.registerNamespace( 'ext.bluespice.rssfeeder.ui.panel' );

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel = function ( cfg ) {
	ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.setup();
};

OO.inheritClass( ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel, OO.ui.PanelLayout );

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setup = function () {
	const heading = document.createElement( 'h2' );
	heading.innerText = mw.msg( 'bs-rssfeeder-pagetext' );

	const setupRecentChangesPanel = this.setupRecentChangesPanel();
	const setupFollowOwnPanel = this.setupFollowOwnPanel();
	const setupFollowPagePanel = this.setupFollowPagePanel();
	const setupNamespaceFeedPanel = this.setupNamespaceFeedPanel();
	const setupCategoryPanel = this.setupCategoryPanel();
	const setupWatchlistPanel = this.setupWatchlistPanel();

	this.$element.append(
		heading,
		setupRecentChangesPanel.$element,
		setupFollowOwnPanel.$element,
		setupFollowPagePanel.$element,
		setupNamespaceFeedPanel.$element,
		setupCategoryPanel.$element,
		setupWatchlistPanel.$element
	);
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setupRecentChangesPanel = function () {
	const panel = this.basePanel(
		'bs-rssfeeder-recent-changes',
		'bs-rssstandards-desc-rc'
	);

	const checkbox = new OO.ui.CheckboxInputWidget();
	const checkboxLayout = new OO.ui.FieldLayout( checkbox, {
		align: 'inline',
		label: mw.msg( 'bs-rssfeeder-rcunique-checkbox' )
	} );

	const button = new OO.ui.ButtonWidget( {
		label: mw.msg( 'bs-rssfeeder-submit' ),
		flags: [ 'progressive' ]
	} );

	button.on( 'click', () => {
		const url = mw.util.getUrl( 'Special:RSSFeeder', {
			Page: 'recentchanges',
			u: mw.config.get( 'wgUserName' ),
			rc_unique: checkbox.isSelected() ? 1 : 0 // eslint-disable-line camelcase
		} );

		window.location.href = url;
	} );

	const buttonLayout = new OO.ui.FieldLayout( button, {
		align: 'inline'
	} );

	panel.$element.append(
		checkboxLayout.$element,
		buttonLayout.$element
	);

	return panel;
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setupFollowOwnPanel = function () {
	const panel = this.basePanel(
		'bs-rssstandards-title-own',
		'bs-rssstandards-desc-own'
	);

	const checkbox = new OO.ui.CheckboxInputWidget();
	const checkboxLayout = new OO.ui.FieldLayout( checkbox, {
		align: 'inline',
		label: mw.msg( 'bs-rssfeeder-rcunique-checkbox' )
	} );

	const button = new OO.ui.ButtonWidget( {
		label: mw.msg( 'bs-rssfeeder-submit' ),
		flags: [ 'progressive' ]
	} );

	button.on( 'click', () => {
		const url = mw.util.getUrl( 'Special:RSSFeeder', {
			Page: 'followOwn',
			u: mw.config.get( 'wgUserName' ),
			rc_unique: checkbox.isSelected() ? 1 : 0 // eslint-disable-line camelcase
		} );

		window.location.href = url;
	} );

	const buttonLayout = new OO.ui.FieldLayout( button, {
		align: 'inline'
	} );

	panel.$element.append(
		checkboxLayout.$element,
		buttonLayout.$element
	);

	return panel;
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setupFollowPagePanel = function () {
	const panel = this.basePanel(
		'bs-rssstandards-title-page',
		'bs-rssstandards-desc-page'
	);

	const titleInputWidget = new OOJSPlus.ui.widget.TitleInputWidget();
	const titleInputLayout = new OO.ui.FieldLayout( titleInputWidget, {
		label: mw.msg( 'bs-rssstandards-title-page' )
	} );

	const button = new OO.ui.ButtonWidget( {
		label: mw.msg( 'bs-rssfeeder-submit' ),
		flags: [ 'progressive' ]
	} );

	button.on( 'click', () => {
		const url = mw.util.getUrl( 'Special:RSSFeeder', {
			Page: 'followPage',
			p: titleInputWidget.getValue(),
			u: mw.config.get( 'wgUserName' )
		} );

		window.location.href = url;
	} );

	const buttonLayout = new OO.ui.FieldLayout( button, {
		align: 'inline'
	} );

	panel.$element.append(
		titleInputLayout.$element,
		buttonLayout.$element
	);

	return panel;
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setupNamespaceFeedPanel = function () {
	const panel = this.basePanel(
		'bs-ns',
		'bs-rssstandards-desc-ns'
	);

	const namespaceInputWidget = new OOJSPlus.ui.widget.NamespaceInputWidget();
	const namespaceInputLayout = new OO.ui.FieldLayout( namespaceInputWidget, {
		label: mw.msg( 'bs-ns' )
	} );

	const checkbox = new OO.ui.CheckboxInputWidget();
	const checkboxLayout = new OO.ui.FieldLayout( checkbox, {
		align: 'inline',
		label: mw.msg( 'bs-rssfeeder-rcunique-checkbox' )
	} );

	const button = new OO.ui.ButtonWidget( {
		label: mw.msg( 'bs-rssfeeder-submit' ),
		flags: [ 'progressive' ]
	} );

	button.on( 'click', () => {
		const url = mw.util.getUrl( 'Special:RSSFeeder', {
			Page: 'namespace',
			ns: namespaceInputWidget.getValue(),
			u: mw.config.get( 'wgUserName' ),
			rc_unique: checkbox.isSelected() ? 1 : 0 // eslint-disable-line camelcase
		} );

		window.location.href = url;
	} );

	const buttonLayout = new OO.ui.FieldLayout( button, {
		align: 'inline'
	} );

	panel.$element.append(
		namespaceInputLayout.$element,
		checkboxLayout.$element,
		buttonLayout.$element
	);

	return panel;
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setupCategoryPanel = function () {
	const panel = this.basePanel(
		'bs-rssstandards-title-cat',
		'bs-rssstandards-desc-cat'
	);

	const categoryInputWidget = new OOJSPlus.ui.widget.CategoryInputWidget();
	const categoryInputLayout = new OO.ui.FieldLayout( categoryInputWidget, {
		label: mw.msg( 'bs-rssstandards-title-cat' )
	} );

	const checkbox = new OO.ui.CheckboxInputWidget();
	const checkboxLayout = new OO.ui.FieldLayout( checkbox, {
		align: 'inline',
		label: mw.msg( 'bs-rssfeeder-rcunique-checkbox' )
	} );

	const button = new OO.ui.ButtonWidget( {
		label: mw.msg( 'bs-rssfeeder-submit' ),
		flags: [ 'progressive' ]
	} );

	button.on( 'click', () => {
		const categoryInput = categoryInputWidget.getValue();
		const colonPosition = categoryInput.indexOf( ':' );
		const category = colonPosition !== -1 ?
			categoryInput.slice( Math.max( 0, colonPosition + 1 ) ) :
			categoryInput;

		const url = mw.util.getUrl( 'Special:RSSFeeder', {
			Page: 'category',
			cat: category,
			u: mw.config.get( 'wgUserName' ),
			rc_unique: checkbox.isSelected() ? 1 : 0 // eslint-disable-line camelcase
		} );

		window.location.href = url;
	} );

	const buttonLayout = new OO.ui.FieldLayout( button, {
		align: 'inline'
	} );

	panel.$element.append(
		categoryInputLayout.$element,
		checkboxLayout.$element,
		buttonLayout.$element
	);

	return panel;
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.setupWatchlistPanel = function () {
	const panel = this.basePanel(
		'bs-rssstandards-title-watch',
		'bs-rssstandards-desc-watch'
	);

	const dropdownWidget = new OO.ui.DropdownWidget( {
		menu: {
			items: [
				new OO.ui.MenuOptionWidget( {
					data: 1,
					label: mw.message( 'bs-rssstandards-link-text-watch', 1 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 3,
					label: mw.message( 'bs-rssstandards-link-text-watch', 3 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 5,
					label: mw.message( 'bs-rssstandards-link-text-watch', 5 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 7,
					label: mw.message( 'bs-rssstandards-link-text-watch', 7 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 14,
					label: mw.message( 'bs-rssstandards-link-text-watch', 14 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 30,
					label: mw.message( 'bs-rssstandards-link-text-watch', 30 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 60,
					label: mw.message( 'bs-rssstandards-link-text-watch', 60 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 90,
					label: mw.message( 'bs-rssstandards-link-text-watch', 90 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 180,
					label: mw.message( 'bs-rssstandards-link-text-watch', 180 ).text()
				} ),
				new OO.ui.MenuOptionWidget( {
					data: 365,
					label: mw.message( 'bs-rssstandards-link-text-watch', 365 ).text()
				} )
			]
		}
	} );
	dropdownWidget.getMenu().selectItemByData( 1 );

	const dropdownLayout = new OO.ui.FieldLayout( dropdownWidget, {
		label: mw.msg( 'bs-rssstandards-title-watch' )
	} );

	const checkbox = new OO.ui.CheckboxInputWidget();
	const checkboxLayout = new OO.ui.FieldLayout( checkbox, {
		align: 'inline',
		label: mw.msg( 'bs-rssfeeder-rcunique-checkbox' )
	} );

	const button = new OO.ui.ButtonWidget( {
		label: mw.msg( 'bs-rssfeeder-submit' ),
		flags: [ 'progressive' ]
	} );

	button.on( 'click', () => {
		const url = mw.util.getUrl( 'Special:RSSFeeder', {
			Page: 'watchlist',
			u: mw.config.get( 'wgUserName' ),
			days: dropdownWidget.getMenu().findSelectedItem().getData(),
			rc_unique: checkbox.isSelected() ? 1 : 0 // eslint-disable-line camelcase
		} );

		window.location.href = url;
	} );

	const buttonLayout = new OO.ui.FieldLayout( button, {
		align: 'inline'
	} );

	panel.$element.append(
		dropdownLayout.$element,
		checkboxLayout.$element,
		buttonLayout.$element
	);

	return panel;
};

ext.bluespice.rssfeeder.ui.panel.SpecialRSSFeederPanel.prototype.basePanel = function ( headingMessageKey, descriptionMessageKey ) {
	const panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	const line = document.createElement( 'hr' );

	const heading = document.createElement( 'h3' );
	heading.className = 'special-rssfeeder-heading';
	heading.innerText = mw.msg( headingMessageKey ); // eslint-disable-line mediawiki/msg-doc

	const description = new OO.ui.LabelWidget( {
		label: mw.message( descriptionMessageKey ).text() // eslint-disable-line mediawiki/msg-doc
	} );

	panel.$element.append(
		line,
		heading,
		description.$element
	);

	return panel;
};
