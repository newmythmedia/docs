<?php namespace Myth\Docs;

class Builder {

	/**
	 * Overall site title
	 * @var
	 */
	protected $title;

	/**
	 * Doc Collections
	 * @var array
	 */
	protected $collections = [];

	/**
	 * The theme to use.
	 * @var
	 */
	protected $theme;

	/**
	 * The layouts to use.
	 * @var
	 */
	protected $main_layout;
	protected $nav_layout;
	protected $search_results_layout;


	//--------------------------------------------------------------------

	/**
	 * Ensure that our collections are created during instantiation.
	 *
	 * @param null $config
	 */
	public function __construct($config=null)
	{
		if (empty($config) || ! is_object($config))
		{
			throw new \InvalidArgumentException('The config object must be passed when creating a new Builder.');
		}

	    $this->setup($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Wraps a single rendered page in the theme and returns it.
	 *
	 * @param string $collection
	 * @param string $page
	 */
	public function buildPage($page, $collection=null)
	{
		$output = '';

		/*
		 * Grab the correct collection.
		 */
		if (empty($collection) && count($this->collections))
		{
			reset($this->collections);
			$collection = key($this->collections);
		}

		if (empty($collection))
		{
			throw new \InvalidArgumentException('Non-existing or invalid collection called with buildPage.');
		}

		$collection = $this->collections[$collection];

		/*
		 * Grab our template file.
		 */
		$template_path = dirname(__FILE__) .'/themes/'. $this->theme .'/'. $this->main_layout .'.php';

		ob_start();

		include($template_path);

		$output = ob_get_contents();
		@ob_end_clean();

		/*
		 * Insert our page.
		 */
		$output = str_ireplace('{contents}', $collection->getPage($page), $output);

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Scans the folders within a collection and returns the menu
	 *
	 * @param $collection
	 */
	public function buildCollectionMenu($collection)
	{

	}

	//--------------------------------------------------------------------



	public function __get($name)
	{
	    if (isset($this->$name))
	    {
		    return $this->$name;
	    }

		return null;
	}
	
	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Protected Methods
	//--------------------------------------------------------------------

	/**
	 * Parses our config object and populates our collection objects.
	 *
	 * @param $config
	 */
	protected function setup($config)
	{
		// Collections
		if (empty($config->collections))
		{
			throw new \RuntimeException('No doc collections were specified.');
		}

		foreach ($config->collections as $name => $c)
		{
			$this->collections[$name] = new Collection($c);
		}

		// Title
		$this->title = ! empty($config->title) ? $config->title : null;

		// Theme
		$this->theme = ! empty($config->theme) ? $config->theme : null;

		// Layout
		$this->main_layout = ! empty($config->main_layout) ? $config->main_layout : null;

		// Nav Layout
		$this->nav_layout = ! empty($config->nav_layout) ? $config->nav_layout : null;

		// Search Results Layout
		$this->search_results_layout = ! empty($config->search_results_layout) ? $config->search_results_layout : null;
	}

	//--------------------------------------------------------------------


}