<?php

namespace MediaWiki\MassMessage;

use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Compute a dedupe hash based on the message subject and contents for
 * each message delivery job, and store it in ct_params alongside the
 * massmessage-delivery change tag. When delivering a message, check
 * whether there is another MassMessage delivery within the past 5 page
 * revisions with an identical dedupe hash, and skip the delivery if
 * there is.
 *
 * Notes:
 * - This relies on direct database queries since ct_params is not exposed
 * 	 through the API.
 * - This only works for wikitext talk pages since we don't attach the
 *   change tag for either Flow or LQT.
 */
class DedupeHelper {

	private const RECENT_REVISIONS_LIMIT = 5;

	/**
	 * Get the dedupe hash corresponding to a MassMessageJob
	 *
	 * @param string $subject
	 * @param string $message
	 * @param ?LanguageAwareText $pageSubject
	 * @param ?LanguageAwareText $pageMessage
	 * @return string
	 */
	public static function getDedupeHash(
		string $subject,
		string $message,
		?LanguageAwareText $pageSubject,
		?LanguageAwareText $pageMessage
	): string {
		$pageSubjectText = $pageSubject !== null ? $pageSubject->getWikitext() : '';
		$pageMessageText = $pageMessage !== null ? $pageMessage->getWikitext() : '';
		return md5( $subject . $message . $pageSubjectText . $pageMessageText );
	}

	/**
	 * For the given title, check if any of the most recent RECENT_REVISIONS_LIMIT revisions is a
	 * MassMessage delivery for the same message.
	 *
	 * @param Title $title
	 * @param string $dedupeHash
	 * @return bool
	 */
	public static function hasRecentlyDeliveredDuplicate( Title $title, string $dedupeHash ): bool {
		$services = MediaWikiServices::getInstance();

		$changeTagId = $services->getChangeTagDefStore()->acquireId( 'massmessage-delivery' );

		// Connect to the primary to avoid issues with replication lag.
		$dbw = $services->getDBLoadBalancerFactory()->getPrimaryDatabase();
		$res = $dbw->newSelectQueryBuilder()
			->select( 'ct_params' )
			->from( 'revision' )
			->leftJoin( 'change_tag', null, [ 'ct_rev_id = rev_id', 'ct_tag_id' => $changeTagId ] )
			->where( [ 'rev_page' => $title->getArticleID() ] )
			->orderBy( 'rev_timestamp', SelectQueryBuilder::SORT_DESC )
			->limit( self::RECENT_REVISIONS_LIMIT )
			->caller( __METHOD__ )
			->fetchResultSet();

		foreach ( $res as $row ) {
			if ( $row->ct_params === null ) {
				continue;
			}
			$params = FormatJson::decode( $row->ct_params, true );
			if ( $dedupeHash === ( $params['dedupe_hash'] ?? null ) ) {
				return true;
			}
		}
		return false;
	}
}
