Ext.define( 'BS.RSSFeeder.RSSPortletConfig', {
	extend: 'BS.portal.PortletConfig',
	showItemCount: true,

	afterInitComponent: function() {
		this.tfRSSUrl = Ext.create( 'Ext.form.TextField',{
			fieldLabel: mw.message( 'bs-extjs-rssfeeder-rss-title' ).plain(),
			labelAlign: 'right'
		});

		this.items.unshift( this.tfRSSUrl );
		this.callParent(arguments);
	},
	setConfigControlValues: function( cfg ) {
		this.setTitle( mw.message( 'bs-extjs-portal-config' ).plain()+' '+cfg.title );
		this.tfTitle.setValue( cfg.title );
		this.sfHeight.setValue( cfg.height );
		this.sfCount.setValue( cfg.portletItemCount );
		this.tfRSSUrl.setValue( cfg.rssurl );
		this.callParent( arguments );
	},
	//Can be overriden by subclasses to allow additional config data
	getConfigControlValues: function() {
		this.callParent( arguments );
		return {
			title: this.tfTitle.getValue(),
			height: this.sfHeight.getValue(),
			portletItemCount: this.sfCount.getValue(),
			portletTimeSpan: this.cbTimeSpan.getValue(),
			rssurl: this.tfRSSUrl.getValue()
		};
	}
});