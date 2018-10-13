<?php

namespace MediaWiki\MassMessage;

use MediaWikiTestCase;

class MassMessageSubmitJobTest extends MediaWikiTestCase {

	/**
	 * @covers \MediaWiki\MassMessage\MassMessageSubmitJob::getJobs
	 * @dataProvider provideGetJobs
	 */
	public function testGetJobs( $data, $pages ) {
		$params = [ 'data' => $data, 'pages' => $pages ];
		$job = new MassMessageSubmitJob(
			$this->getMock( 'Title' ),
			$params
		);

		$jobsByTarget = $job->getJobs();
		$count = 0;
		foreach ( $jobsByTarget as $wiki => $jobs ) {
			/** @var MassMessageJob $job */
			foreach ( $jobs as $job ) {
				$count++;
				$this->assertInstanceOf( MassMessageJob::class, $job );
				$params = $job->getParams();
				foreach ( $data as $key => $val ) {
					$this->assertArrayHasKey( $key, $params );
					$this->assertEquals( $val, $params[$key] );
				}

				$found = false;
				foreach ( $pages as $info ) {
					if ( $info['wiki'] === $wiki && $info['title'] === $params['title'] ) {
						$found = true;
						break;
					}
				}

				$this->assertTrue( $found );
			}
		}

		$this->assertEquals( count( $pages ), $count );
	}

	public static function provideGetJobs() {
		$data = [
			'some data' => 'some value',
			'other key' => 'other value'
		];

		$pages = [ [
			'wiki' => 'enwiki',
			'title' => 'Foo baz',
		] ];

		return [
			[ $data, $pages ],
			[ $data, $pages + [ [ 'wiki' => 'dewiki', 'title' => 'Baz foo' ] ] ],
			[
				$data + [ 'message' => 'rar' ],
				$pages + [ [ 'wiki' => 'zzwiki', 'title' => 'Title!' ] ]
			],
		];
	}
}
