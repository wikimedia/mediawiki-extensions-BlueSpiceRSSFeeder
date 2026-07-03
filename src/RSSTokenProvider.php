<?php

namespace BlueSpice\RSSFeeder;

use MediaWiki\User\User;
use MediaWiki\User\UserOptionsLookup;
use MediaWiki\User\UserOptionsManager;

class RSSTokenProvider {

	/**
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param UserOptionsManager $userOptionsManager
	 */
	public function __construct(
		private readonly UserOptionsLookup $userOptionsLookup,
		private readonly UserOptionsManager $userOptionsManager
	) {
	}

	/**
	 * @param User $user
	 * @return string
	 */
	public function getRSSToken( User $user ): string {
		$token = $this->userOptionsLookup->getOption( $user, 'watchlisttoken' );
		if ( !$token ) {
			$token = $user->resetTokenFromOption( 'watchlisttoken' );
			$this->userOptionsManager->setOption( $user, 'watchlisttoken', $token );
			$this->userOptionsManager->saveOptions( $user );

		}

		return $token;
	}

	/**
	 * @param string $token
	 * @param User $user
	 * @return bool
	 */
	public function isValidToken( string $token, User $user ): bool {
		return $this->getRSSToken( $user ) === $token;
	}
}
