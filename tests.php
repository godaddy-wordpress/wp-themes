<?php

use PHPUnit\Framework\TestCase;

class Tests extends TestCase
{
	public static $manifest     = __DIR__ . '/manifest.json';
	public static $manifest_min = __DIR__ . '/manifest.min.json';

	public static function provideThemeData()
	{
		return array_map(
			function ( $v ) {
				return [ $v ];
			},
			json_decode( file_get_contents( self::$manifest ), true )
		);
	}

	/**
	 * @dataProvider provideThemeData
	 */
	public function testThemeExistsOnWpOrg( $theme_slug )
	{

		// Skip SSL validation.
		stream_context_set_default(
			[
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			]
		);

		$headers = get_headers( "https://downloads.wordpress.org/theme/${theme_slug}.latest-stable.zip" );

		$this->assertRegExp( '/(200 OK|302 Found)/', $headers[0], 'Theme package URL is unreachable' );

	}
}
