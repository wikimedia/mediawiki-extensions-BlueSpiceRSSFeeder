<?php

namespace BlueSpice\RSSFeeder\Hook\BSDashboardsUserDashboardPortalPortlets;

use BlueSpice\Dashboards\Hook\BSDashboardsUserDashboardPortalPortlets;

class AddPortlets extends BSDashboardsUserDashboardPortalPortlets {

	protected function doProcess() {
		$this->portlets[] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => $this->msg( 'bs-rssfeeder-rss' )->plain(),
				'height' => 660,
				'rssurl' => 'https://blog.bluespice.com/feed/'
			],
			'title' => $this->msg( 'bs-rssfeeder-rss' )->plain(),
			'description' => $this->msg( 'bs-rssfeeder-rss-desc' )->plain(),
			'modules' => 'ext.bluespice.rssFeeder',
		];
	}

}
