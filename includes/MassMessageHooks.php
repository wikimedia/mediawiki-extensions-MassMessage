<?php

namespace MediaWiki\MassMessage;

use EchoEvent;
use MediaWiki\MediaWikiServices;
use OutputPage;
use Parser;
use Skin;
use SpecialPage;
use User;

/**
 * Hooks!
 */

class MassMessageHooks {

	/**
	 * Hook to load our parser function.
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
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
		global $wgScript, $wgAllowGlobalMessaging;

		$parser->addTrackingCategory( 'massmessage-list-category' );

		$data = MassMessage::processPFData( $page, $site );
		if ( isset( $data['error'] ) ) {
			return $data;
		}

		// Use a message so wikis can customize the output.
		if ( $wgAllowGlobalMessaging ) {
			$msg = wfMessage( 'massmessage-target' )
				->params( $data['site'], $wgScript, $data['title'] )->plain();
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
			return ''; // Output doesn't matter
		}
		$output = $parser->getOutput();
		$current = $output->getExtensionData( 'massmessage-targets' );
		if ( !$current ) {
			$output->setExtensionData( 'massmessage-targets', [ $data ] );
		} else {
			$output->setExtensionData( 'massmessage-targets',
				array_merge( $current,  [ $data ] ) );
		}
		return '';
	}

	/**
	 * Add our username to the list of reserved ones
	 *
	 * @param array &$reservedUsernames
	 */
	public static function onUserGetReservedNames( &$reservedUsernames ) {
		global $wgMassMessageAccountUsername;
		$reservedUsernames[] = $wgMassMessageAccountUsername;
	}

	/**
	 * If someone is trying to rename the bot, don't let them.
	 *
	 * @param int $uid
	 * @param string $oldName
	 * @param string $newName
	 * @return bool|string
	 */
	public static function onRenameUserPreRename( $uid, $oldName, $newName ) {
		global $wgMassMessageAccountUsername;
		if ( $oldName == $wgMassMessageAccountUsername ) {
			return wfMessage( 'massmessage-cannot-rename' )->text();
		}
		return true;
	}

	/**
	 * Add the number of queued messages to &meta=siteinfo&siprop=statistics.
	 *
	 * @param array &$result
	 */
	public static function onAPIQuerySiteInfoStatisticsInfo( &$result ) {
		$result['queued-massmessages'] = MassMessage::getQueuedCount();
	}

	/**
	 * Echo!
	 *
	 * @param EchoEvent $event
	 * @return bool
	 */
	public static function onBeforeEchoEventInsert( EchoEvent $event ) {
		// Don't spam a user with mention notifications if it's a MassMessage
		if ( ( $event->getType() === 'mention' || $event->getType() === 'flow-mention' ) &&
				$event->getAgent() && // getAgent() can return null
				$event->getAgent()->getId() == MassMessage::getMessengerUser()->getId() ) {
			return false;
		}
		return true;
	}

	/**
	 * Override the Edit tab for delivery lists.
	 *
	 * @param \SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public static function onSkinTemplateNavigation( \SkinTemplate $sktemplate, &$links ) {
		$title = $sktemplate->getTitle();
		if ( $title->hasContentModel( 'MassMessageListContent' )
			&& array_key_exists( 'edit', $links['views'] )
		) {
			// Get the revision being viewed, if applicable
			$request = $sktemplate->getRequest();
			$direction = $request->getVal( 'direction' );
			$diff = $request->getVal( 'diff' );
			$oldid = $request->getInt( 'oldid' ); // Guaranteed to be an integer, 0 if invalid
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
				} else { // diff is 'prev' or gibberish
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
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$title = $out->getTitle();
		if ( $title->exists() && $title->hasContentModel( 'MassMessageListContent' ) ) {
			$out->addModuleStyles( 'ext.MassMessage.styles' );

			$permManager = MediaWikiServices::getInstance()->getPermissionManager();
			if ( $out->getRevisionId() === $title->getLatestRevId()
				&& $permManager->quickUserCan( 'edit', $out->getUser(), $title )
			) {
				$out->addModules( 'ext.MassMessage.content.js' );
				$out->addBodyClasses( 'mw-massmessage-editable' );
			}
		}
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
			return false; // Skip further checks
		}
		return true;
	}

	/**
	 * Register the change tag for MassMessage delivery
	 *
	 * @param array &$tags
	 */
	public static function onRegisterTags( &$tags ) {
		$tags[] = 'massmessage-delivery';
	}
}
