/**
 * RSSFeeder extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage RSSFeeder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

Ext.define( 'BS.RSSFeeder.RSSPortlet', {
	extend: 'BS.portal.APIPortlet',
	portletConfigClass: 'BS.RSSFeeder.RSSPortletConfig',
	module: 'rssfeeder',
	task: 'getRSS',

	setPortletConfig: function( cfg ) {
		this.rssurl = cfg.rssurl;
		this.callParent(arguments);
	},
	getPortletConfig: function() {
		cfg = this.callParent( arguments );
		cfg.rssurl = this.rssurl;
		return cfg;
	},
	makeData: function() {
		data = {
			'count': this.portletItemCount,
			'url':this.rssurl
		};
		return data;
	}
} );