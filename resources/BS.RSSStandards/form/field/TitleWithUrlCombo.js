Ext.define('BS.RSSStandards.form.field.TitleWithUrlCombo', {
	extend: 'BS.form.field.TitleCombo',
	requires: [ 'BS.RSSStandards.model.Title' ],
	width: 400,
	style: {
		padding: '1px'
	},
	makeStore: function() {
		var store = new BS.store.BSApi({
			apiAction: 'bs-rss-standards-pages-store',
			proxy: {
				extraParams: {
					options: Ext.encode({
						returnQuery: true
					})
				}
			},
			model: 'BS.RSSStandards.model.Title',
			groupField: 'type',
			remoteSort: true,
			remoteFilter: true,
			autoLoad: true
		});
		return store;
	}
});