<?php

namespace MediaWiki\MassMessage\Api;

use MediaWiki\Api\ApiBase;
use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\RequestProcessing\MassMessageRequestParser;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to send MassMessages.
 *
 * @file
 * @ingroup API
 * @author Kunal Mehta
 * @license GPL-2.0-or-later
 */

class ApiMassMessage extends ApiBase {
	public function execute() {
		$this->checkUserRightsAny( 'massmessage' );

		$data = $this->extractRequestParams();

		// Must provide message or page-message
		$this->requireAtLeastOneParameter( $data, 'message', 'page-message' );

		$requestParser = new MassMessageRequestParser();
		$status = $requestParser->parseRequest( $data, $this->getUser() );

		if ( !$status->isOK() ) {
			$this->dieStatus( $status );
		}

		$count = MassMessage::submit( $this->getUser(), $status->getValue() );

		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			[ 'result' => 'success', 'count' => $count ]
		);
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'spamlist' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'subject' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
			'message' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'page-message' => [
				ParamValidator::PARAM_TYPE => 'string'
			],
			'token' => null,
		];
	}

	/** @inheritDoc */
	public function mustBePosted() {
		return true;
	}

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}

	/** @inheritDoc */
	public function isWriteMode() {
		return true;
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=massmessage&spamlist=Signpost%20Spamlist&subject=New%20Signpost' .
			'&message=Please%20read%20it&token=TOKEN'
				=> 'apihelp-massmessage-example-1',
			'action=massmessage&spamlist=Signpost%20Spamlist&subject=New%20Signpost' .
			'&page-message=Help_Page&token=TOKEN'
				=> 'apihelp-massmessage-example-2',
			'action=massmessage&spamlist=Signpost%20Spamlist&subject=New%20Signpost' .
			'&message=Please%20read%20it&page-message=Help_Page&token=TOKEN'
				=> 'apihelp-massmessage-example-3',
		];
	}

	/**
	 * @return array
	 */
	public function getHelpUrls() {
		return [ 'https://www.mediawiki.org/wiki/Extension:MassMessage/API' ];
	}

}
