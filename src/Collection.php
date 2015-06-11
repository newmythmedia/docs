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

	protected $base_url;

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

	/**
	 * Scans the collection's directories and builds a map
	 * of the folders and files.
	 */
	public function getLinks($dir=null)
	{
		$result = array();

		if (empty($dir))
		{
			$dir = $this->docs_directory;
		}

		$cdir = scandir($dir);
		foreach ($cdir as $key => $value)
		{
			if (! in_array($value, array(".","..",".DS_Store")))
			{
				if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
				{
					$result[ $this->prepareLinkName($value) ] = $this->getLinks($dir . DIRECTORY_SEPARATOR . $value);
				}
				else
				{
					$result[] = $this->prepareLinkName($value);
				}
			}
		}

		return $result;
	}

	//--------------------------------------------------------------------

	public function setBaseURL($url)
	{
	    $this->baseURL = $url;
	}

	//--------------------------------------------------------------------



	//--------------------------------------------------------------------
	// Protected Methods
	//--------------------------------------------------------------------

	/**
	 * Cleans up a link text so that it's ready to be displayed.
	 *
	 *      - Removes any number + _ prefix (01_)
	 *      - Converts underscore to spaces
	 *      - unwords
	 *
	 * @param $str
	 */
	protected function prepareLinkName($str)
	{
		if (empty($str)) return $str;

		// Strip numeric prefixes
		$str = preg_replace('/^[0-9]+_/', '', $str);

		// Remove file suffix
		$str = str_replace('.md', '', $str);

		// Convert underscore to space
//		$str = str_replace('_', ' ', $str);

		// Change case
//		$str = ucwords( strtolower($str) );

		return $str;
	}

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