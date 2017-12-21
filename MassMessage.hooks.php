<?php

/**
 * Hooks!
 */

namespace MediaWiki\MassMessage;

use SpecialPage;
use OutputPage;
use Parser;
use Skin;
use EchoEvent;
use User;

class MassMessageHooks {

	/**
	 * Hook to load our parser function
	 * @param Parser &$parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'target',
			'MediaWiki\\MassMessage\\MassMessageHooks::outputParserFunction' );
		return true;
	}

	/**
	 * Main parser function for {{#target:User talk:Example|en.wikipedia.org}}
	 * Prepares the human facing output
	 * Hostname is optional for local delivery
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

		// Use a message so wikis can customize the output
		if ( $wgAllowGlobalMessaging ) {
			$msg = wfMessage( 'massmessage-target' )
				->params( $data['site'], $wgScript, $data['title'] )->plain();
		} else {
			$msg = wfMessage( 'massmessage-target-local' )->params( $data['title'] )->plain();
		}

		return [ $msg, 'noparse' => false ];
	}

	/**
	 * Reads the parser function and extracts the data from it
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
	 * @param array &$reservedUsernames
	 * @return bool
	 */
	public static function onUserGetReservedNames( &$reservedUsernames ) {
		global $wgMassMessageAccountUsername;
		$reservedUsernames[] = $wgMassMessageAccountUsername;
		return true;
	}

	/**
	 * If someone is trying to rename the bot, don't let them.
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
	 * Add a row with the number of queued messages to Special:Statistics
	 * @param array &$extraStats
	 * @return bool
	 */
	public static function onSpecialStatsAddExtra( &$extraStats ) {
		$extraStats['massmessage-queued-count'] = MassMessage::getQueuedCount();
		return true;
	}

	/**
	 * Add the number of queued messages to &meta=siteinfo&siprop=statistics
	 * @param array &$result
	 * @return bool
	 */
	public static function onAPIQuerySiteInfoStatisticsInfo( &$result ) {
		$result['queued-massmessages'] = MassMessage::getQueuedCount();
		return true;
	}

	/**
	 * Makes the messenger sender exempt from IP blocks no matter what
	 * Called only if the context is a MassMessage job (bug 69381)
	 * @see bug 58237
	 * @param User $user
	 * @param array &$rights
	 * @return bool
	 */
	public static function onUserGetRights( User $user, array &$rights ) {
		if ( $user->getId() === MassMessage::getMessengerUser()->getId() ) {
			$rights[] = 'ipblock-exempt';
		}
		return true;
	}

	/**
	 * Echo!
	 * @param EchoEvent $event
	 * @return bool
	 */
	public static function onBeforeEchoEventInsert( EchoEvent $event ) {
		// Don't spam a user with mention notifications if it's a MassMessage
		if ( ( $event->getType() == 'mention' || $event->getType() == 'flow-mention' ) &&
				$event->getAgent() && // getAgent() can return null
				$event->getAgent()->getId() == MassMessage::getMessengerUser()->getId() ) {
			return false;
		}

		return true;
	}

	/**
	 * Override the Edit tab for delivery lists
	 * @param SkinTemplate &$sktemplate
	 * @param array &$links
	 * @return bool
	 */
	public static function onSkinTemplateNavigation( &$sktemplate, &$links ) {
		$title = $sktemplate->getTitle();
		if ( $title->hasContentModel( 'MassMessageListContent' )
			&& array_key_exists( 'edit', $links['views'] )
		) {
			// Get the revision being viewed, if applicable
			$request = $sktemplate->getRequest();
			$direction = $request->getVal( 'direction' );
			$diff = $request->getVal( 'diff' );
			$oldid = $request->getInt( 'oldid' ); // Guaranteed to be an integer, 0 if invalid
			if ( $direction === 'next' && $oldid > 0 ) {
				$next = $title->getNextRevisionId( $oldid );
				$revId = ( $next ) ? $next : $oldid;
			} elseif ( $direction === 'prev' && $oldid > 0 ) {
				$prev = $title->getPreviousRevisionId( $oldid );
				$revId = ( $prev ) ? $prev : $oldid;
			} elseif ( $diff !== null ) {
				if ( ctype_digit( $diff ) ) {
					$revId = (int)$diff;
				} elseif ( $diff === 'next' && $oldid > 0 ) {
					$next = $title->getNextRevisionId( $oldid );
					$revId = ( $next ) ? $next : $oldid;
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
		return true;
	}

	/**
	 * Add scripts and styles
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$title = $out->getTitle();
		if ( $title->exists() && $title->hasContentModel( 'MassMessageListContent' ) ) {
			$out->addModuleStyles( 'ext.MassMessage.content.nojs' );
			if ( $out->getRevisionId() === $title->getLatestRevId()
				&& $title->quickUserCan( 'edit', $out->getUser() )
			) {
				$out->addModuleStyles( 'ext.MassMessage.content' );
				$out->addModules( 'ext.MassMessage.content.js' );
			} else {
				$out->addModuleStyles( 'ext.MassMessage.content.noedit' );
			}
		}
		return true;
	}

	/**
	 * Mark the messenger account's email as confirmed in job runs (bug 73061)
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
	 * @param array &$tags
	 * @return bool
	 */
	public static function onRegisterTags( &$tags ) {
		$tags[] = 'massmessage-delivery';
		return true;
	}
}
