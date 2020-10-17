<?php

namespace BlueSpice\RSSFeeder\Hook\BSDashboardsAdminDashboardPortalConfig;

use BlueSpice\Dashboards\Hook\BSDashboardsAdminDashboardPortalConfig;

class AddConfigs extends BSDashboardsAdminDashboardPortalConfig {

	protected function doProcess() {
		$this->portalConfig[0][] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => $this->msg( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'https://blog.bluespice.com/feed/'
			],
			'modules' => 'ext.bluespice.rssFeeder',
		];
	}

}
