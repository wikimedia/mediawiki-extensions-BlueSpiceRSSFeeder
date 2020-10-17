<?php

namespace BlueSpice\RSSFeeder\Hook\BSDashboardsUserDashboardPortalConfig;

use BlueSpice\Dashboards\Hook\BSDashboardsUserDashboardPortalConfig;

class AddConfigs extends BSDashboardsUserDashboardPortalConfig {

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
