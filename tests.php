<?php

class Tests extends PHPUnit_Framework_TestCase
{
	public $manifest = __DIR__ . '/manifest.json';

	public $manifest_min = __DIR__ . '/manifest.min.json';

	public function testExists()
	{
		$this->assertTrue( is_readable( $this->manifest ) );
		$this->assertTrue( is_readable( $this->manifest_min ) );
	}

	public function testJson()
	{
		$this->assertJson( (string) file_get_contents( $this->manifest ) );
		$this->assertJson( (string) file_get_contents( $this->manifest_min ) );
	}

	public function testData()
	{
		$themes = json_decode( (string) file_get_contents( $this->manifest ) );

		foreach ( $themes as $data ) {
			$this->assertCount( 4, (array) $data );
			$this->assertInstanceOf( 'stdClass', $data );

			$this->assertTrue( property_exists( $data, 'theme' ) );
			$this->assertNotEmpty( $data->theme );
			$this->assertTrue( ctype_lower( str_replace( '-', '', $data->theme ) ) );

			$this->assertTrue( property_exists( $data, 'new_version' ) );
			$this->assertRegExp( '/^([\w\.\-]+)$/', $data->new_version );

			$this->assertTrue( property_exists( $data, 'url' ) );
			$this->assertRegExp( '/^(https?):\/\/[^\s\/$.?#].[^\s]*$/i', $data->url );
			$this->assertContains( $data->theme, $data->url );

			$this->assertTrue( property_exists( $data, 'package' ) );
			$this->assertRegExp( '/^(https?):\/\/[^\s\/$.?#].[^\s]*$/i', $data->package );
			$this->assertContains( "/godaddy/wp-{$data->theme}-theme/", $data->package );
			$this->assertContains( "/v{$data->new_version}/", $data->package );
			$this->assertStringEndsWith( "/{$data->theme}.zip", $data->package );
		}
	}

	public function testMatch()
	{
		$manifest     = json_decode( (string) file_get_contents( $this->manifest ), true );
		$manifest_min = json_decode( (string) file_get_contents( $this->manifest_min ), true );

		$this->assertTrue( $manifest === $manifest_min );
	}
}
