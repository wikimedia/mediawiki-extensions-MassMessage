<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\RequestProcessing;

use MediaWiki\MassMessage\MassMessage;
use MediaWiki\MassMessage\UrlHelper;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserIdentity;
use Status;
use Title;
use WikiMap;
use function wfMessage;

/**
 * Parses request submitted by user for sending a mass message
 * @author Abijeet Patro
 * @since 2021.12
 * @license GPL-2.0-or-later
 */
class MassMessageRequestParser {
	public function parseRequest( array $data, UserIdentity $user ): Status {
		// Trim all the things!
		foreach ( $data as $k => $v ) {
			if ( is_string( $v ) ) {
				$data[$k] = trim( $v );
			}
		}

		$status = new Status();
		if ( $data['subject'] === '' ) {
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

			$data['comment'] = [ $user->getName(), WikiMap::getCurrentWikiId(), $url ];
		} else { // $spamlist contains a message key for an error message
			$status->fatal( $spamlist );
		}

		$data['page-message'] = $data['page-message'] ?? '';
		$data['page-section'] = $data['page-section'] ?? '';
		$data['message'] = $data['message'] ?? '';

		// Check and fetch the page message
		$pageMessage = null;
		if ( $data['page-message'] !== '' ) {
			$pageMessageStatus = MassMessage::getContent(
				$data['page-message'],
				WikiMap::getCurrentWikiId(),
				$data['page-section']
			);

			if ( $pageMessageStatus->isOK() ) {
				$pageMessage = $pageMessageStatus->getValue();
				if ( $pageMessage === '' ) {
					$status->fatal( 'massmessage-page-message-empty', $data['page-message'] );
				}
			} else {
				$status->merge( $pageMessageStatus );
			}
		}

		if ( $data['message'] === '' && $pageMessage === null ) {
			$status->fatal( 'massmessage-empty-message' );
		}

		$footer = wfMessage( 'massmessage-message-footer' )->inContentLanguage()->plain();
		if ( trim( $footer ) ) {
			// Only add the footer if it is not just whitespace
			$data['message'] .= "\n" . $footer;
		}

		if ( $status->isOK() ) {
			$status->setResult(
				true,
				new MassMessageRequest(
					$spamlist,
					$data['subject'],
					$data['page-message'],
					$data['page-section'],
					$data['message'],
					$data['comment']
				)
			);
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
			return 'massmessage-spamlist-invalid'; // Interwiki redirect or non-existent page.
		}
		$spamlist = $target;

		$contentModel = $spamlist->getContentModel();

		if ( $contentModel !== 'MassMessageListContent'
			&& $contentModel !== CONTENT_MODEL_WIKITEXT
			|| $contentModel === 'MassMessageListContent'
			&& !MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionByTitle( $spamlist )
				->getContent( SlotRecord::MAIN )
				->isValid()
		) {
			return 'massmessage-spamlist-invalid';
		}

		return $spamlist;
	}
}
