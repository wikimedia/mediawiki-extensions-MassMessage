<?php
declare( strict_types = 1 );

namespace MediaWiki\MassMessage\MessageContentFetcher;

use MediaWiki\Config\SiteConfiguration;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\MassMessage\LanguageAwareText;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;

/**
 * Fetches content from a remote wiki
 * @author Abijeet Patro
 * @since 2022.01
 * @license GPL-2.0-or-later
 */
class RemoteMessageContentFetcher {
	/** @var HttpRequestFactory */
	private $httpRequestFactory;
	/** @var SiteConfiguration */
	private $siteConfiguration;

	/**
	 * @param HttpRequestFactory $requestFactory
	 * @param SiteConfiguration $siteConfiguration
	 */
	public function __construct( HttpRequestFactory $requestFactory, SiteConfiguration $siteConfiguration ) {
		$this->httpRequestFactory = $requestFactory;
		$this->siteConfiguration = $siteConfiguration;
	}

	/**
	 * Fetch remote content and return Status object. The Status value contains a LanguageAwareText object
	 * @param string $pageTitle
	 * @param string $wikiId
	 * @return Status
	 */
	public function getContent( string $pageTitle, string $wikiId ): Status {
		$apiUrl = $this->getApiEndpoint( $wikiId );
		if ( !$apiUrl ) {
			return Status::newFatal(
				'massmessage-page-message-wiki-not-found',
				$wikiId,
				$pageTitle
			);
		}

		$queryParams = [
			'action' => 'query',
			'format' => 'json',
			'prop' => 'info|revisions',
			'rvprop' => 'content',
			'rvslots' => 'main',
			'titles' => $pageTitle,
			'formatversion' => 2,
		];

		$options = [
			'method' => 'GET',
			'timeout' => 15,
		];

		$apiUrl .= '?' . http_build_query( $queryParams );
		$req = $this->httpRequestFactory->create( $apiUrl, $options, __METHOD__ );

		$status = $req->execute();
		if ( !$status->isOK() ) {
			// FIXME: Formatting is broken here, needs to be improved.
			return Status::newFatal(
				"massmessage-page-message-fetch-error-in-wiki",
				$wikiId,
				$pageTitle,
				$status->getMessage()->text()
			);
		}

		$json = $req->getContent();
		$response = json_decode( $json, true );
		if ( $response === null ) {
			return Status::newFatal(
				"massmessage-page-message-parsing-error-in-wiki",
				$wikiId,
				$pageTitle,
				json_last_error_msg()
			);
		}

		return $this->parseQueryApiResponse( $response, $wikiId, $pageTitle, $json );
	}

	/**
	 * @param array $response
	 * @param string $wikiId
	 * @param string $pageTitle
	 * @param string $json
	 * @return Status
	 */
	private function parseQueryApiResponse(
		array $response,
		string $wikiId,
		string $pageTitle,
		string $json
	): Status {
		// Example response:
		// {
		//   "batchcomplete": true,
		//   "query": {
		//     "pages": [ {
		//       "pageid": 11285354,
		//       "ns": 0,
		//       "title": "Tech/News/2021/12",
		//       "contentmodel": "wikitext",
		//       "pagelanguage": "en",
		//       "pagelanguagehtmlcode": "en",
		//       "pagelanguagedir": "ltr",
		//       "touched": "2021-03-23T06:05:06Z",
		//       "lastrevid": 21247464,
		//       "length": 4585,
		//       "revisions": [ {
		//         "slots": {
		//           "main": {
		//             "contentmodel": "wikitext",
		//             "contentformat": "text/x-wiki",
		//             "content": "[...]"
		//           }
		//         }
		//       } ]
		//     } ]
		//   }
		// }

		$pages = $response['query']['pages'] ?? [];
		if ( isset( $response['error']['info'] ) || count( $pages ) !== 1 ) {
			return Status::newFatal(
				'massmessage-page-message-parse-invalid-in-wiki',
				$wikiId,
				$pageTitle,
				$response['error']['info'] ?? $json
			);
		}

		// Take first and only one out of the list
		$page = current( $pages );

		if ( isset( $page['missing'] ) ) {
			// Page was not found
			return Status::newFatal(
				'massmessage-page-message-not-found-in-wiki',
				$wikiId,
				$pageTitle
			);
		}

		$content = new LanguageAwareText(
			$page['revisions'][0]['slots']['main']['content'],
			$page['pagelanguage'],
			$page['pagelanguagedir']
		);

		return Status::newGood( $content );
	}

	/**
	 * @param string $wiki
	 * @return string|null
	 */
	private function getApiEndpoint( string $wiki ): ?string {
		$this->siteConfiguration->loadFullData();

		$siteFromDB = $this->siteConfiguration->siteFromDB( $wiki );
		[ $major, $minor ] = $siteFromDB;

		if ( $major === null ) {
			return null;
		}

		$configOpts = [ 'lang' => $minor, 'site' => $major ];
		$server = $this->siteConfiguration->get(
			'wgServer',
			$wiki,
			null,
			$configOpts
		);
		$scriptPath = $this->siteConfiguration->get(
			'wgScriptPath',
			$wiki,
			null,
			$configOpts
		);

		$urlUtils = MediaWikiServices::getInstance()->getUrlUtils();
		$apiPath = $urlUtils->expand( $server . $scriptPath . '/api.php', PROTO_INTERNAL );

		return $apiPath;
	}
}
