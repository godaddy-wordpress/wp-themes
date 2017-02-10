<?php

use PHPUnit\Framework\TestCase;

class Tests extends TestCase
{
	public static $manifest     = __DIR__ . '/manifest.json';
	public static $manifest_min = __DIR__ . '/manifest.min.json';
	public static $themes       = [];

	public function testFilesExist()
	{
		$this->assertTrue( is_readable( self::$manifest ) );
		$this->assertTrue( is_readable( self::$manifest_min ) );
	}

	public function testFileContents()
	{
		$json     = (string) file_get_contents( self::$manifest );
		$json_min = (string) file_get_contents( self::$manifest_min );

		$this->assertJson( $json );
		$this->assertJson( $json_min );

		$manifest     = json_decode( $json, true );
		$manifest_min = json_decode( $json_min, true );

		$this->assertNotEmpty( $manifest );
		$this->assertNotEmpty( $manifest_min );
		$this->assertTrue( $manifest === $manifest_min );

		self::$themes = $manifest;
	}

	public function testThemeData()
	{
		array_walk( self::$themes, function ( $data ) {
			$this->assertTrue( is_array( $data ) );
			$this->assertCount( 4, $data );

			$this->assertNotEmpty( $data['theme'] );
			$this->assertTrue( ctype_lower( str_replace( '-', '', $data['theme'] ) ) );

			$this->assertNotEmpty( $data['new_version'] );
			$this->assertRegExp( '/^([\w\.\-]+)$/', $data['new_version'] );

			$this->assertNotEmpty( $data['url'] );
			$this->assertRegExp( '/^(https?):\/\/[^\s\/$.?#].[^\s]*$/i', $data['url'] );
			$this->assertContains( $data['theme'], $data['url'] );

			$this->assertNotEmpty( $data['package'] );
			$this->assertRegExp( '/^(https?):\/\/[^\s\/$.?#].[^\s]*$/i', $data['package'] );
			$this->assertContains( "/godaddy/wp-{$data['theme']}-theme/", $data['package'] );
			$this->assertContains( "/v{$data['new_version']}/", $data['package'] );
			$this->assertStringEndsWith( "/{$data['theme']}.zip", $data['package'] );
		} );
	}

	public function testUrlsExist()
	{
		array_walk( self::$themes, function ( $data ) {
			$response = get_headers( $data['url'] );
			$this->assertContains( '200 OK', $response[0] );
		} );
	}

	public function testPackgesExist()
	{
		array_walk( self::$themes, function ( $data ) {
			$response = get_headers( $data['package'] );
			$this->assertContains( '302 Found', $response[0] );
		} );
	}
}
