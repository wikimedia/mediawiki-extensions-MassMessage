<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\MassMessage;

use MediaWiki\Api\Hook\APIQuerySiteInfoStatisticsInfoHook;
use MediaWiki\ChangeTags\Hook\ChangeTagsListActiveHook;
use MediaWiki\ChangeTags\Hook\ListDefinedTagsHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\RejectParserCacheValueHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\Hook\UserGetReservedNamesHook;
use MediaWiki\User\User;
use Skin;
use WikiPage;

/**
 * Hooks!
 */

class MassMessageHooks implements
	ParserFirstCallInitHook,
	APIQuerySiteInfoStatisticsInfoHook,
	UserGetReservedNamesHook,
	SkinTemplateNavigation__UniversalHook,
	BeforePageDisplayHook,
	ListDefinedTagsHook,
	ChangeTagsListActiveHook,
	RejectParserCacheValueHook
{

	/**
	 * Hook to load our parser function.
	 *
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'target', [ __CLASS__, 'outputParserFunction' ] );
	}

	/**
	 * Main parser function for {{#target:User talk:Example|en.wikipedia.org}}.
	 * Prepares the human facing output.
	 * Hostname is optional for local delivery.
	 *
	 * @param Parser $parser
	 * @param string $page
	 * @param string $site
	 * @return array
	 */
	public static function outputParserFunction( Parser $parser, $page, $site = '' ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		$parser->addTrackingCategory( 'massmessage-list-category' );

		$data = MassMessage::processPFData( $page, $site );
		if ( isset( $data['error'] ) ) {
			return $data;
		}

		// Use a message so wikis can customize the output.
		if ( $config->get( 'AllowGlobalMessaging' ) ) {
			$msg = wfMessage( 'massmessage-target' )
				->params( $data['site'], $config->get( MainConfigNames::Script ), $data['title'] )->plain();
		} else {
			$msg = wfMessage( 'massmessage-target-local' )->params( $data['title'] )->plain();
		}

		return [ $msg, 'noparse' => false ];
	}

	/**
	 * Reads the parser function and extracts the data from it.
	 *
	 * @param Parser $parser
	 * @param string $page
	 * @param string $site
	 * @return string
	 */
	public static function storeDataParserFunction( Parser $parser, $page, $site = '' ) {
		$data = MassMessage::processPFData( $page, $site );
		if ( isset( $data['error'] ) ) {
			// Output doesn't matter
			return '';
		}
		$output = $parser->getOutput();
		$current = $output->getExtensionData( 'massmessage-targets' );
		if ( !$current ) {
			$output->setExtensionData( 'massmessage-targets', [ $data ] );
		} else {
			$output->setExtensionData( 'massmessage-targets',
				array_merge( $current, [ $data ] ) );
		}
		return '';
	}

	/**
	 * Add our username to the list of reserved ones
	 *
	 * @param array &$reservedUsernames
	 */
	public function onUserGetReservedNames( &$reservedUsernames ) {
		$reservedUsernames[] = MediaWikiServices::getInstance()->getMainConfig()->get( 'MassMessageAccountUsername' );
	}

	/**
	 * Add the number of queued messages to &meta=siteinfo&siprop=statistics.
	 *
	 * @param array &$result
	 */
	public function onAPIQuerySiteInfoStatisticsInfo( &$result ) {
		$result['queued-massmessages'] = MassMessage::getQueuedCount();
	}

	/**
	 * Override the Edit tab for delivery lists.
	 *
	 * @param \SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$title = $sktemplate->getTitle();
		if ( $title->hasContentModel( 'MassMessageListContent' )
			&& array_key_exists( 'edit', $links['views'] )
		) {
			// Get the revision being viewed, if applicable
			$request = $sktemplate->getRequest();
			$direction = $request->getVal( 'direction' );
			$diff = $request->getVal( 'diff' );
			// getInt is guaranteed to return an integer, 0 if invalid
			$oldid = $request->getInt( 'oldid' );
			$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
			$oldRev = $revisionLookup->getRevisionById( $oldid );
			if ( $direction === 'next' && $oldRev ) {
				$next = $revisionLookup->getNextRevision( $oldRev );
				$revId = $next ? $next->getId() : $oldid;
			} elseif ( $direction === 'prev' && $oldRev ) {
				$prev = $revisionLookup->getPreviousRevision( $oldRev );
				$revId = $prev ? $prev->getId() : $oldid;
			} elseif ( $diff !== null ) {
				if ( ctype_digit( $diff ) ) {
					$revId = (int)$diff;
				} elseif ( $diff === 'next' && $oldRev ) {
					$next = $revisionLookup->getNextRevision( $oldRev );
					$revId = $next ? $next->getId() : $oldid;
				} else {
					// diff is 'prev' or gibberish
					$revId = $oldid;
				}
			} else {
				$revId = $oldid;
			}

			$query = ( $revId > 0 ) ? 'oldid=' . $revId : '';
			$links['views']['edit']['href'] = SpecialPage::getTitleFor(
				'EditMassMessageList', $title
			)->getFullUrl( $query );
		}
	}

	/**
	 * Add scripts and styles.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$title = $out->getTitle();
		if ( $title->exists() && $title->hasContentModel( 'MassMessageListContent' ) ) {
			$permManager = MediaWikiServices::getInstance()->getPermissionManager();
			if ( $out->getRevisionId() === $title->getLatestRevId()
				&& $permManager->quickUserCan( 'edit', $out->getUser(), $title )
			) {
				$out->addBodyClasses( 'mw-massmessage-editable' );
			}
		}
	}

	/**
	 * Hook: RejectParserCacheValue
	 *
	 * Reject old cache entries that don't contain our "ext.MassMessage.content"
	 * module.
	 *
	 * @param ParserOutput $parserOutput
	 * @param WikiPage $wikiPage
	 * @param ParserOptions $parserOptions
	 * @return bool
	 */
	public function onRejectParserCacheValue( $parserOutput, $wikiPage,
		$parserOptions
	): bool {
		if ( $wikiPage->getTitle()->hasContentModel( 'MassMessageListContent' ) &&
			!in_array( 'ext.MassMessage.content', $parserOutput->getModules() )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Mark the messenger account's email as confirmed in job runs (T75061).
	 *
	 * @param User $user
	 * @param bool &$confirmed
	 * @return bool
	 */
	public static function onEmailConfirmed( User $user, &$confirmed ) {
		if ( $user->getId() === MassMessage::getMessengerUser()->getId() ) {
			$confirmed = true;
			// Skip further checks
			return false;
		}
		return true;
	}

	/**
	 * Register the change tag for MassMessage delivery
	 *
	 * @param array &$tags
	 */
	public function onListDefinedTags( &$tags ) {
		$this->addRegisterTags( $tags );
	}

	/**
	 * Register the change tag for MassMessage delivery
	 *
	 * @param array &$tags
	 */
	public function onChangeTagsListActive( &$tags ) {
		$this->addRegisterTags( $tags );
	}

	/**
	 * @param array &$tags
	 */
	private function addRegisterTags( &$tags ) {
		$tags[] = 'massmessage-delivery';
	}
}
