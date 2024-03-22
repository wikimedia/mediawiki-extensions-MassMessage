<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\RequestProcessing;

use MediaWiki\MassMessage\Services;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MediaWiki\WikiMap\WikiMap;
use function wfMessage;

/**
 * Parses request submitted by user for sending a mass message
 * @author Abijeet Patro
 * @since 2021.12
 * @license GPL-2.0-or-later
 */
class MassMessageRequestParser {
	/**
	 * @param array $data
	 * @param UserIdentity $user
	 * @return Status
	 */
	public function parseRequest( array $data, UserIdentity $user ): Status {
		// Trim all the things!
		foreach ( $data as $k => $v ) {
			if ( is_string( $v ) ) {
				$data[$k] = trim( $v );
			}
		}

		$status = new Status();
		$currentWikiId = WikiMap::getCurrentWikiId();

		$data['page-message'] ??= '';
		$data['page-message-section'] ??= '';
		$data['page-subject-section'] ??= '';
		$data['message'] ??= '';
		$data['subject'] ??= '';

		if ( $data['subject'] === '' && $data['page-subject-section'] === '' ) {
			$status->fatal( 'massmessage-empty-subject' );
		}

		$spamlist = self::getSpamlist( $data['spamlist'] );
		if ( $spamlist instanceof Title ) {
			// Prep the HTML comment message
			if ( $spamlist->inNamespace( NS_CATEGORY ) ) {
				$url = $spamlist->getFullURL();
			} else {
				$url = $spamlist->getFullURL(
					[ 'oldid' => $spamlist->getLatestRevID() ],
					false,
					PROTO_CANONICAL
				);
			}

			$data['comment'] = [ $user->getName(), $currentWikiId, $url ];
		} else {
			// $spamlist contains a message key for an error message
			$status->fatal( $spamlist );
			// Set dummy values in order to continue validation
			$spamlist = Title::newMainPage();
			$data['comment'] = [];
		}

		$footer = wfMessage( 'massmessage-message-footer' )->inContentLanguage()->plain();
		if ( trim( $footer ) ) {
			// Only add the footer if it is not just whitespace
			$data['message'] .= "\n" . $footer;
		}

		$request = new MassMessageRequest(
			$spamlist,
			$data['subject'],
			$data['page-message'],
			$data['page-message-section'],
			$data['page-subject-section'],
			$data['message'],
			$data['comment']
		);

		$pageMessageBuilderResult = null;
		if ( $request->hasPageMessage() ) {
			$pageMessageBuilder = Services::getInstance()->getPageMessageBuilder();
			$pageMessageBuilderResult = $pageMessageBuilder->getContent(
				$request->getPageMessage(),
				$request->getPageMessageSection(),
				$request->getPageSubjectSection(),
				$currentWikiId
			);

			if ( !$pageMessageBuilderResult->isOK() ) {
				$status->merge( $pageMessageBuilderResult->getStatus() );
			}
		}

		if ( !$request->hasMessage() && !$pageMessageBuilderResult ) {
			$status->fatal( 'massmessage-empty-message' );
		}

		if ( $status->isOK() ) {
			$status->setResult( true, $request );
		}

		return $status;
	}

	/**
	 * Parse and normalize the spamlist
	 * @param string $title
	 * @return Title|string string will be a error message key
	 */
	private static function getSpamlist( string $title ) {
		$spamlist = Title::newFromText( $title );

		// Simply return the title if it is a category
		if ( $spamlist !== null && $spamlist->inNamespace( NS_CATEGORY ) ) {
			return $spamlist;
		}

		if ( $spamlist === null || !$spamlist->exists() ) {
			return 'massmessage-spamlist-doesnotexist';
		}

		// Page exists, follow a redirect if possible
		$target = UrlHelper::followRedirect( $spamlist );
		if ( $target === null || !$target->exists() ) {
			// Interwiki redirect or non-existent page.
			return 'massmessage-spamlist-invalid';
		}
		$spamlist = $target;

		$contentModel = $spamlist->getContentModel();

		if ( ( $contentModel !== 'MassMessageListContent' && $contentModel !== CONTENT_MODEL_WIKITEXT )
			|| ( $contentModel === 'MassMessageListContent' && !MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionByTitle( $spamlist )
				->getContent( SlotRecord::MAIN )
				->isValid()
			)
		) {
			return 'massmessage-spamlist-invalid';
		}

		return $spamlist;
	}
}
