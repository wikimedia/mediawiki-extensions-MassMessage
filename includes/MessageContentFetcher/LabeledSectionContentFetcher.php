<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\MassMessage\LanguageAwareText;
use Status;

/**
 * Fetches content from labeled sections
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class LabeledSectionContentFetcher {
	/**
	 * Returns labeled sections from the given page
	 *
	 * @param string $content
	 * @return string[]
	 */
	public function getSections( string $content ): array {
		preg_match_all(
			'~<section[^>]+begin\s*=\s*([^ /]+)[^>]+>(.*?)<section[^>]+end\s*=\s*\\1~s',
			$content,
			$matches
		);

		return array_unique( $matches[1] );
	}

	/**
	 * Get content from a labeled section
	 *
	 * @param LanguageAwareText $content
	 * @param string $label
	 * @return Status
	 */
	public function getContent( LanguageAwareText $content, string $label ): Status {
		$wikitext = $content->getWikitext();
		// I looked into LabeledSectionTransclusion and it is not reusable here without a lot of
		// rework -NL
		$matches = [];
		$label = preg_quote( $label, '~' );
		$ok = preg_match_all(
			"~<section[^>]+begin\s*=\s*{$label}[^>]+>.*?<section[^>]+end\s*=\s*{$label}[^>]+>~s",
			$wikitext,
			$matches
		);
		if ( $ok < 1 ) {
			return Status::newFatal( 'massmessage-page-section-invalid' );
		}
		// Include section tags for backwards compatibility.
		// https://phabricator.wikimedia.org/T254481#6865334
		// In case there are multiple sections with same label, there will be multiple wrappers too.
		// Because LabelsedSectionTransclusion supports that natively, I see no reason to try to
		// simplify it to include only one wrapper.
		$sectionContent = new LanguageAwareText(
			trim( implode( "", $matches[0] ) ),
			$content->getLanguageCode(),
			$content->getLanguageDirection()
		);
		return Status::newGood( $sectionContent );
	}
}
