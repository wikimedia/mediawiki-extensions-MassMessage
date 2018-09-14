<?php

namespace MediaWiki\MassMessage;

use ApiBase;
use Status;

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

		$status = new Status();
		MassMessage::verifyData( $data, $status );
		if ( !$status->isOK() ) {
			$this->dieStatus( $status );
		}

		$count = MassMessage::submit( $this->getUser(), $data );

		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			[ 'result' => 'success', 'count' => $count ]
		);
	}

	public function getAllowedParams() {
		return [
			'spamlist' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			],
			'subject' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			],
			'message' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			],
			'token' => null,
		];
	}

	public function mustBePosted() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function isWriteMode() {
		return true;
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 *
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=massmessage&spamlist=Signpost%20Spamlist&subject=New%20Signpost' .
			'&message=Please%20read%20it&token=TOKEN'
				=> 'apihelp-massmessage-example-1',
		];
	}

	/**
	 * @return array
	 */
	public function getHelpUrls() {
		return [ 'https://www.mediawiki.org/wiki/Extension:MassMessage/API' ];
	}

}
