<?php namespace Myth\Docs;

class Builder {

	/**
	 * Overall site title
	 * @var
	 */
	protected $title;

	/**
	 * Overall site title
	 * @var
	 */
	protected $site_title;

	/**
	 * Doc Collections
	 * @var array
	 */
	protected $collections = [];

	/**
	 * The name of the default collection to show
	 * if none is specified.
	 * @var
	 */
	protected $default_collection;

	/**
	 * The name of the currently active collection.
	 * @var
	 */
	protected $active_collection;

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

	/**
	 * The first portion of any URI's generated.
	 * Must be supplied by the user.
	 *
	 * @var
	 */
	protected $base_url;


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

		// Strip the collection name from the page name
		$page = str_ireplace($collection, '', $page);

		$collection = $this->collections[$collection];
		$collection->setBaseURL($this->base_url);
		$content = $collection->getPage($page);

		/*
		 * Grab our template file.
		 */
		$template_path = dirname(__FILE__) .'/themes/'. $this->theme .'/'. $this->main_layout .'.php';

		// Compile data to make available to the view
		$data = [
			'title' => $this->title,
		    'site_name' => $this->site_title,
		    'collection_names' => array_keys($this->collections),
		    'sidebar' => $this->buildCollectionMenu($collection)
		];
		extract($data);

		ob_start();

		include($template_path);

		$output = ob_get_contents();
		@ob_end_clean();

		/*
		 * Insert our page.
		 */
		$output = str_ireplace('{contents}', $content, $output);

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
		/*
		 * Grab our template file.
		 */
		$template_path = dirname(__FILE__) .'/themes/'. $this->theme .'/'. $this->nav_layout .'.php';

		// Compile data to make available to the view
		$data = [
			'title' => $this->title,
			'site_name' => $this->site_title,
		    'links' => $collection->getLinks(),
		    'builder' => $this
		];
		extract($data);

		ob_start();

		include($template_path);

		$output = ob_get_contents();
		@ob_end_clean();

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Given a URI string, will determine what the active collection is
	 * by comparing each URI segment with the names of our collections.
	 *
	 * @param $uri
	 * @return null
	 */
	public function determineActiveCollection( $uri )
	{
		$segments = explode('/', $uri);

		// We know that our first segment won't be a collection name
		array_shift($segments);

		if (! count($segments) || empty($segments[0]) )
		{
			return null;
		}

		$collection_names = array_keys($this->collections);

		foreach ($segments as $s)
		{
			if (in_array(strtolower($s), $collection_names ) )
			{
				$this->active_collection = $s;

			    return $s;
			}
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Stores our base URL to use when generating links.
	 *
	 * @param $url
	 * @return $this
	 */
	public function setBaseURL($url)
	{
	    $this->base_url = $url;

		return $this;
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

		// Default Collection
		$this->default_collection = ! empty($config->default_collection) ? $config->default_collection : null;

		// Title
		$this->title = ! empty($config->title) ? $config->title : null;

		// Site Title
		$this->site_title = ! empty($config->site_title) ? $config->site_title : null;

		// Theme
		$this->theme = ! empty($config->theme) ? $config->theme : 'default';

		// Layout
		$this->main_layout = ! empty($config->main_layout) ? $config->main_layout : 'main';

		// Nav Layout
		$this->nav_layout = ! empty($config->nav_layout) ? $config->nav_layout : 'nav';

		// Search Results Layout
		$this->search_results_layout = ! empty($config->search_results_layout) ? $config->search_results_layout : 'search_results';
	}

	//--------------------------------------------------------------------


}