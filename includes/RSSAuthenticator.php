<?php

use BlueSpice\RSSFeeder\RSSTokenProvider;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;
use MediaWiki\User\User;

class RSSAuthenticator {
	public const TOKEN_SALT = 'rss_salt';
	/**
	 * @var WebRequest
	 */
	protected $request;

	/**
	 * @var RequestContext
	 */
	protected $context;

	/**
	 * @var RSSTokenProvider
	 */
	protected $tokenProvider;

	/**
	 * @param WebRequest $request
	 * @param IContextSource $context
	 * @param RSSTokenProvider $tokenProvider
	 */
	public function __construct( WebRequest $request, IContextSource $context, RSSTokenProvider $tokenProvider ) {
		$this->request = $request;
		$this->context = $context;
		$this->tokenProvider = $tokenProvider;
	}

	/**
	 * Tries to log in user based on username and token
	 * given in the request
	 *
	 * @return bool
	 */
	public function logInUser() {
		$userName = $this->request->getVal( 'u', '' );
		if ( !$userName ) {
			return false;
		}

		$requestToken = $this->request->getVal( 'h', '' );
		if ( !$requestToken ) {
			return false;
		}

		$user = MediaWikiServices::getInstance()->getUserFactory()
			->newFromName( $userName );
		if ( $user instanceof User == false || $user->getId() == 0 ) {
			// User does not exist
			return false;
		}

		if ( !$this->tokenProvider->isValidToken( $requestToken, $user ) ) {
			return false;
		}

		$user->setCookies();
		$this->context->setUser( $user );
		return true;
	}
}
