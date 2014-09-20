<?php

class MassMessageSubmitJobTest extends MediaWikiTestCase {

	/**
	 * @covers MassMessageSubmitJob::getJobs
	 * @dataProvider provideGetJobs
	 */
	public function testGetJobs( $data, $pages ) {
		$params = array( 'data' => $data, 'pages' => $pages );
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
				$this->assertInstanceOf( 'MassMessageJob', $job );
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

		$this->assertEquals( count( $pages), $count );
	}

	public static function provideGetJobs() {
		$data = array(
			'some data' => 'some value',
			'other key' => 'other value'
		);

		$pages = array( array(
			'wiki' => 'enwiki',
			'title' => 'Foo baz',
		) );

		return array(
			array( $data, $pages ),
			array( $data, $pages + array( array( 'wiki' => 'dewiki', 'title' => 'Baz foo' ) ) ),
			array( $data + array( 'message' => 'rar' ), $pages + array( array( 'wiki' => 'zzwiki', 'title' => 'Title!' ) ) ),
		);
	}
}
