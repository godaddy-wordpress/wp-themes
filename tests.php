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

	public function testFilesExist()
	{
		$this->assertTrue( is_readable( self::$manifest ), 'Manifest file is not readable' );
		$this->assertTrue( is_readable( self::$manifest_min ), 'Minified manifest file is not readable' );
	}

	public function testFileContents()
	{
		$json     = (string) file_get_contents( self::$manifest );
		$json_min = (string) file_get_contents( self::$manifest_min );

		$this->assertJson( $json, 'Manifest is not valid JSON' );
		$this->assertJson( $json_min, 'Minified manifest is not valid JSON' );

		$manifest     = json_decode( $json, true );
		$manifest_min = json_decode( $json_min, true );

		$this->assertNotEmpty( $manifest, 'Manifest is empty' );
		$this->assertNotEmpty( $manifest_min, 'Minified manifest is empty' );
		$this->assertEquals( $manifest, $manifest_min, 'Manifests do not match' );
	}

	/**
	 * @dataProvider provideThemeData
	 */
	public function testThemeData( $data )
	{
		$this->assertTrue( is_array( $data ), 'Theme data is not an array' );
		$this->assertCount( 6, $data, 'Theme data array does not contain exactly 6 items' );

		$this->assertNotEmpty( $data['theme'], 'Theme slug does not exist' );
		$this->assertTrue( ctype_lower( str_replace( '-', '', $data['theme'] ) ), 'Theme slug is not lowercase' );

		$this->assertNotEmpty( $data['new_version'], 'Theme version does not exist' );
		$this->assertRegExp( '/^([\w\.\-]+)$/', $data['new_version'], 'Theme version format is invalid' );

		$this->assertNotEmpty( $data['url'], 'Theme demo URL does not exist' );
		$this->assertRegExp( '/^(https):\/\/[^\s\/$.?#].[^\s]*$/i', $data['url'], 'Theme demo URL format is invalid' );

		$this->assertNotEmpty( $data['package'], 'Theme package URL does not exist' );
		$this->assertRegExp( '/^(https):\/\/[^\s\/$.?#].[^\s]*$/i', $data['package'], 'Theme package URL format is invalid' );
		$this->assertRegExp( sprintf( '/(wordpress\.org\/theme\/%1$s\.%2$s\.zip|releases\/download\/%2$s\/%1$s\.zip)$/i', preg_quote( $data['theme'] ), preg_quote( $data['new_version'] ) ), $data['package'], 'Theme package URL does not point to a versioned ZIP file of the theme slug' );

		$this->assertNotEmpty( $data['screenshot'], 'Theme screenshot URL does not exist' );
		$this->assertRegExp( '/^(https):\/\/[^\s\/$.?#].[^\s]*$/i', $data['screenshot'], 'Theme screenshot URL format is invalid' );
		$this->assertStringContainsString( $data['theme'], $data['screenshot'], 'Theme screenshot URL does not contain the theme slug' );
		$this->assertStringEndsWith( "screenshot.png", $data['screenshot'], 'Theme screenshot URL does not point to a `screenshot.png` file' );

		$this->assertNotEmpty( $data['name'], 'Theme name does not exist' );
		$this->assertEquals( strtolower( str_replace( ' ', '-', $data['name'] ) ), $data['theme'], 'Theme name and slug are not similar' );
	}

	/**
	 * @dataProvider provideThemeData
	 */
	public function testUrlReachable( $data )
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
		$headers = get_headers( $data['url'] );
		$this->assertStringContainsString( '200 OK', $headers[0], 'Theme demo URL is unreachable' );
	}

	/**
	 * @dataProvider provideThemeData
	 */
	public function testPackgeReachable( $data )
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
		$headers = get_headers( $data['package'] );
		$this->assertRegExp( '/(200 OK|302 Found)/', $headers[0], 'Theme package URL is unreachable' );
	}

	/**
	 * @dataProvider provideThemeData
	 */
	public function testScreenshotReachable( $data )
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
		$headers = get_headers( $data['screenshot'] );
		$this->assertRegExp( '/(200 OK|302 Found)/', $headers[0], 'Theme screenshot URL is unreachable' );
	}
}
