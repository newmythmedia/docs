<?php

include 'vendor/autoload.php';

use \Myth\Docs\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase {

	protected $config;

	//--------------------------------------------------------------------

	public function __construct()
	{
	    $config = [
		    'title' => 'Test Title',
	        'theme' => 'default',
	        'main_layout' => 'main',
	        'nav_layout' => 'nav',
	        'search_results_layout' => 'search_results',
	        'collections' => [
		        'developer' => [
			        'title' => 'Docs Docs',
			        'tagline' => 'myth:Docs Documentation',
					'visible' => true,
					'docs_directory' => 'docs'
		        ]
	        ]
	    ];

		$config['collections']['developer'] = (object)$config['collections']['developer'];
		$this->config = (object)$config;
	}

	//--------------------------------------------------------------------


	public function testCrashesOnNoConfigPassed()
	{
	    $this->setExpectedException('\InvalidArgumentException');

		$b = new Builder();
	}
	
	//--------------------------------------------------------------------

	public function testCrashesOnConfigNotObject()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$b = new Builder([]);
	}

	//--------------------------------------------------------------------

	public function testCrashesOnNoCollections()
	{
		$this->setExpectedException('\RuntimeException');

		$config = new stdClass();

		$b = new Builder($config);
	}

	//--------------------------------------------------------------------
	
	public function testSavesCollectionsOnStart()
	{
	    $b = new Builder($this->config);

		$this->assertTrue(is_array($b->collections));
		$this->assertEquals('Myth\Docs\Collection', get_class($b->collections['developer']));
	}
	
	//--------------------------------------------------------------------

	public function testSavesThemesOnStart()
	{
		$b = new Builder($this->config);

		$this->assertEquals($this->config->theme, $b->theme);
	}

	//--------------------------------------------------------------------

	public function testSavesLayoutsOnStart()
	{
		$b = new Builder($this->config);

		$this->assertEquals($this->config->main_layout, $b->main_layout);
		$this->assertEquals($this->config->nav_layout, $b->nav_layout);
		$this->assertEquals($this->config->search_results_layout, $b->search_results_layout);
	}

	//--------------------------------------------------------------------
}