<?php namespace Myth\Docs;

use League\CommonMark\CommonMarkConverter;

/**
 * Class Collection
 *
 * Represents a single "collection" of documentation files. A collection
 * will typically represent one part of documentation. Your site may have
 * more than one set of documentation.
 *
 * @package Myth\Docs
 */
class Collection {

	/**
	 * The title of this collection.
	 * Used in Menus so should be short.
	 * @var
	 */
	public $title;

	/**
	 * Short description of collection.
	 * @var
	 */
	public $tagline;

	/**
	 * Is this collection visible in
	 * the overall page?
	 *
	 * @var bool
	 */
	protected $visible = true;

	/**
	 * Any files to ignore the contents of.
	 *
	 * @var array
	 */
	protected $ignore_files = [];

	/**
	 * Any folders to ignore the contents of.
	 * @var array
	 */
	protected $ignore_folders = [];

	/**
	 * Where the docs are found.
	 * @var string
	 */
	protected $docs_directory = 'docs';

	/**
	 * Maps file extensions to the type
	 * to use.
	 *
	 * @var array
	 */
	protected $parsers = [
		'md'   => 'markdown'
	];

	//--------------------------------------------------------------------

	public function __construct($config)
	{
	    $this->setup($config);
	}

	//--------------------------------------------------------------------

	/**
	 * Locates the page, parses it, and returns it.
	 *
	 * @param $page
	 * @return string|null
	 */
	public function getPage($page)
	{
		$output = '';

		$page = trim($page, '/');

		if (empty($page))
		{
			$page = 'index';
		}

		$path = $this->docs_directory .'/'. $page;

		$file = $this->getFileInfo($path);

		if (is_null($file))
		{
			return null;
		}

		$output = $this->convertPage($file);

		return $output;
	}

	//--------------------------------------------------------------------



	//--------------------------------------------------------------------
	// Protected Methods
	//--------------------------------------------------------------------

	/**
	 * Parses our config object to get our needed settings.
	 *
	 * @param $config
	 */
	protected function setup($config)
	{
		$this->title = ! empty($config->title) ? $config->title : null;

		$this->tagline = ! empty($config->tagline) ? $config->tagline : null;

		$this->docs_directory = ! empty($config->docs_directory) ? realpath($config->docs_directory) : null;

		if (isset($config->visible)) $this->visible = (bool)$config->visible;

		if (! empty($config->ignore->files)) $this->ignore_files = (array)$config->ignore->files;
		if (! empty($config->ignore->folders)) $this->ignore_folders = (array)$config->ignore->folders;
	}

	//--------------------------------------------------------------------

	/**
	 * Tries to find the file and returns basic info about it, like
	 * the path, filename, and file extension.
	 *
	 * @param $path
	 * @return null
	 */
	protected function getFileInfo($path)
	{
		$files = glob($path .'.*');

		if (! is_array($files) || (is_array($files) && ! count($files)) )
		{
			return null;
		}

		$file = null;

		foreach ($files as $f)
		{
			$info = pathinfo($f);

			if (! array_key_exists( $info['extension'], $this->parsers ))
			{
				continue;
			}

			$file = $info;

			$file['parser'] = $this->parsers[ $info['extension'] ];

			unset($info);
			break;
		}

		return $file;
	}

	//--------------------------------------------------------------------

	/**
	 * Given a file info array will convert the file
	 * and return it.
	 *
	 * @param array $file
	 * @return null|string
	 */
	public function convertPage(array $file)
	{
	    if (empty($file['basename']))
	    {
		    return null;
	    }

		$path = $file['dirname'] .'/'. $file['basename'];

		$output = file_get_contents($path);

		if (!empty($output))
		{
			switch ($file['parser'])
			{
				case 'markdown':
					$converter = new CommonMarkConverter();
					$output = $converter->convertToHtml($output);
					break;
			}
		}

		return $output;
	}

	//--------------------------------------------------------------------


}