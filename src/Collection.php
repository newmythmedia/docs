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

    /**
     * Availalbe versions.
     *
     * @var array
     */
    protected $versions = [];

    /**
     * The current version string.
     * Matches folder name.
     *
     * @var
     */
    protected $current_version;

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

        if (strpos($page, 'index.php') === 0)
        {
            $page = str_replace('index.php', '', $page);
            $page = trim($page, '/');
        }

		if (empty($page))
		{
			$page = '';
			$name = 'index';
		}

		// Ensure that we can get numbered filenames
		$pos = strrpos($page, '/');
		if ($pos)
		{
			$name = substr($page, $pos + 1);
			$page = substr($page, 0, $pos);
		}

		$path = $this->docs_directory . $page;

		$file = $this->getFileInfo($path, $name);

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

	public function getVersions()
	{
	    return $this->versions;
	}

	//--------------------------------------------------------------------

	public function getCurrentVersion()
	{
	    return $this->current_version;
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

//		$this->docs_directory = ! empty($config->docs_directory) ? realpath($config->docs_directory) : null;
        $this->docs_directory = $this->determineDocsDirectory( $config->docs_directory );

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
	protected function getFileInfo($path, $filename)
	{
		$path = $this->getRealPath($path);

		if (! is_dir($path))
		{
			throw new \Exception($path .' is not a valid directory.');
		}

		$file = $this->getRealFilename($path, $filename);

		if (empty($file)) return null;

		$fileinfo = null;

		$info = pathinfo($file);

		if (! array_key_exists( $info['extension'], $this->parsers ))
		{
			return null;
		}

		$fileinfo = $info;

		$fileinfo['parser'] = $this->parsers[ $info['extension'] ];

		return $fileinfo;
	}

	//--------------------------------------------------------------------

	protected function getRealPath($path)
	{
		if (is_dir($path)) return $path;

		// Remove the last element so we can glob the folder...
		$last = substr($path, strrpos($path, '/') + 1);

		if (! empty($last))
		{
			$path = str_replace($last, '[0-9][0-9]_'.$last, $path);
		}

		$list = glob($path .'*');

		if (empty($list)) return null;

		return realpath($list[0]);
	}

	//--------------------------------------------------------------------

	protected function getRealFilename($path, $filename)
	{
		foreach ([$filename, '[0-9][0-9]_'. $filename] as $file)
		{
			$list = glob($path .'/'. $file .'*');

			if (is_array($list) && ! empty($list))
			{
				return $list[0];
			}
		}

		return null;
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

    /**
     * Scans the given folder, determines available and current versions,
     * and sets the main doc folder.
     *
     * @param $root_path
     */
    protected function determineDocsDirectory($root_path)
    {
        $root_path = ! empty($root_path) ? realpath($root_path) : null;

        if (empty($root_path))
        {
            return null;
        }

        $versions = $this->scanForVersions($root_path);

        if (! empty($versions))
        {
            $this->versions = $versions;

            // Set a default current version here.
            $current = array_keys($versions);
            $current = array_pop($current);

            $this->current_version = $current;
        }

        // @todo - determine the current version from the URI

        return rtrim($root_path, '/') .'/v'. $this->current_version .'/';
    }

    //--------------------------------------------------------------------

    /**
     * Scans a directory for any folders with starting the letter
     * 'v' followed by a number. These are considered to be versions
     * of the docs.
     *
     * @param $path
     */
    public function scanForVersions($path)
    {
        $files = glob("{$path}/*");

        if (empty($files)) return null;

        $versions = [];

        foreach ($files as $name)
        {
            $name = trim( str_replace($path, '', $name), '/');

            if (strpos($name, 'v') === 0 && is_numeric(substr($name, 1, 1)))
            {
                $versions[substr($name, 1)] = $path .'/'. $name .'/';
            }
        }

        ksort($versions);

        return $versions;
    }

    //--------------------------------------------------------------------



}