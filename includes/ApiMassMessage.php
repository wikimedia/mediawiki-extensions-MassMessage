<?php
/**
 * API module to send MassMessages
 *
 * @file
 * @ingroup API
 * @author Kunal Mehta
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class ApiMassMessage extends ApiBase {
	public function execute() {
		if ( !$this->getUser()->isAllowed( 'massmessage' ) ) {
			$this->dieUsageMsg( 'permissiondenied' );
		}

		$data = $this->extractRequestParams();

		$status = new Status();
		MassMessage::verifyData( $data, $status, $this );
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

	public function getTokenSalt() {
		return '';
	}

	public function isWriteMode() {
		return true;
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return [
			'action=massmessage&spamlist=Signpost%20Spamlist&subject=New%20Signpost' .
			'&message=Please%20read%20it&token=TOKEN'
				=> 'apihelp-massmessage-example-1',
		];
	}

	public function getHelpUrls() {
		return [ 'https://www.mediawiki.org/wiki/Extension:MassMessage/API' ];
	}

}
