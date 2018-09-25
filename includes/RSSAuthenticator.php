<?php

class RSSAuthenticator {
	const TOKEN_SALT = 'rss_salt';
	/**
	 *
	 * @var \WebRequest
	 */
	protected $request;

	/**
	 *
	 * @var \RequestContext
	 */
	protected $context;

	public function __construct( \WebRequest $request, \IContextSource $context ) {
		$this->request = $request;
		$this->context = $context;
	}

	/**
	 * Tries to log in user based on username and token
	 * given in the request
	 *
	 * @return boolean
	 */
	public function logInUser() {
		$userName = $this->request->getVal( 'u', '' );
		if( !$userName ) {
			return false;
		}

		$requestToken = $this->request->getVal( 'h', '' );
		if( !$requestToken ) {
			return false;
		}

		$user = \User::newFromName( $userName );
		if( $user instanceof \User == false || $user->getId() == 0 ) {
			//User does not exist
			return false;
		}

		$userToken = $user->getToken( /*static::TOKEN_SALT*/ );
		if( $userToken != $requestToken ) {
			return false;
		}

		$user->setCookies();
		$this->context->setUser( $user );
		return true;
	}
}
