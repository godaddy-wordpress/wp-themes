<?php

use PHPUnit\Framework\TestCase;

class Tests extends TestCase
{
	public static $manifest     = __DIR__ . '/manifest.json';
	public static $manifest_min = __DIR__ . '/manifest.min.json';
	public static $themes       = [];

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
		$this->assertTrue( $manifest === $manifest_min, 'Manifests do not match' );

		self::$themes = $manifest;
	}

	public function testThemeData()
	{
		array_walk( self::$themes, function ( $data ) {
			$this->assertTrue( is_array( $data ), 'Theme data is not an array' );
			$this->assertCount( 4, $data, 'Theme data array does not contain exactly 4 items' );

			$this->assertNotEmpty( $data['theme'], 'Theme slug does not exist' );
			$this->assertTrue( ctype_lower( str_replace( '-', '', $data['theme'] ) ), 'Theme slug is not lowercase' );

			$this->assertNotEmpty( $data['new_version'], 'Theme version does not exist' );
			$this->assertRegExp( '/^([\w\.\-]+)$/', $data['new_version'], 'Theme version format is invalid' );

			$this->assertNotEmpty( $data['url'], 'Theme URL does not exist' );
			$this->assertRegExp( '/^(https?):\/\/[^\s\/$.?#].[^\s]*$/i', $data['url'], 'Theme URL format is invalid' );
			$this->assertContains( $data['theme'], $data['url'], 'Theme URL does not contain the theme slug' );

			$this->assertNotEmpty( $data['package'], 'Theme package URL does not exist' );
			$this->assertRegExp( '/^(https?):\/\/[^\s\/$.?#].[^\s]*$/i', $data['package'], 'Theme package URL format is invalid' );
			$this->assertContains( "/godaddy/wp-{$data['theme']}-theme/", $data['package'], 'Theme package URL does not contain the theme repo' );
			$this->assertContains( "/v{$data['new_version']}/", $data['package'], 'Theme package URL does not contain the theme version' );
			$this->assertStringEndsWith( "/{$data['theme']}.zip", $data['package'], 'Theme package URL does not point to a ZIP file of the theme slug' );
		} );
	}

	public function testUrlsReachable()
	{
		array_walk( self::$themes, function ( $data ) {
			$headers = get_headers( $data['url'] );
			$this->assertContains( '200 OK', $headers[0], 'Theme URL is unreachable' );
		} );
	}

	public function testPackgesReachable()
	{
		array_walk( self::$themes, function ( $data ) {
			$headers = get_headers( $data['package'] );
			$this->assertContains( '302 Found', $headers[0], 'Theme package URL is unreachable' );
		} );
	}
}
