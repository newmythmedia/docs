<?php namespace Myth\Docs;

class SimplePresenter implements PresenterInterface {

    protected $current_url;

    protected $site_url;

    protected $collections;

    //--------------------------------------------------------------------

	/**
	 * Runs some post processing routines on the generated page
	 * data that is not specific to the conversion process.
	 * Things like:
	 *
	 *  - adding named anchors to headers
	 *
	 *
	 * @param $str
	 *
	 * @return $str
	 */
	public function postProcessPage($str)
	{
		if (empty($str)) return $str;

		try {
			$xml = new \SimpleXMLElement('<?xml version="1.0" standalone="yes"?><div>' . $str . '</div>');
		}
		catch (\Exception $e)
		{
			echo $e->getMessage();
			return $str;
		}

		/*
         * Rewrite the URLs
         */
		foreach ($xml->xpath('//a') as &$link) {
			$link = $this->reformatAnchor($link);
		}

        /*
         * Add a in-page TOC at the top of the page,
         * and create named anchors within the page content.
         */
        $str = $this->addDocumentMap($str);

		return $str;
	}

	//--------------------------------------------------------------------

	public function reformatAnchor($link)
	{
	    // Grab the href value
		$href = $link->attributes()->href;

		// If the href is null, it's probably a named anchor with no content.
		if (! $href) {
			// Make sure it has an href, else the XML will not close this
			// tag correctly.
			$link['href'] = ' ';

			return $link;
		}

		// Remove any trailing # signs
		$href = rtrim($href, '# ');

        // If the href starts with #, then attach the current_url to it
        if ($href != '' && substr_compare($href, '#', 0, 1) === 0)
        {
            $link['href'] = $this->current_url . $href;
            return $link;
        }

        // If it's a full external path, go on...
        if ((strpos($href, 'http://') !== false || strpos($href, 'https://') !== false) &&
            strpos($href, $this->site_url) === false
        ) {
            $link['target'] = "_blank";
            return $link;
        }

        // If it's a full local path, get rid of it.
        if (strpos($href, $this->site_url) !== false) {
            $href = str_replace($this->site_url, '', $href);
        }

        // Strip out some unnecessary items, just in case they're there.
        if (substr($href, 0, strlen('docs/')) == 'docs/') {
            $href = substr($href, strlen('docs/'));
        }

        // If another 'group' is not already defined at the head of the link
        // then add the current group to it.
        $group_found = false;

        foreach ($this->collections as $name => $obj) {
            if (strpos($href, $name) === 0) {
                $group_found = true;
            }
        }

        if (! $group_found) {
            $href = $this->current_url . '/' . $href;
        }

        // Convert to full site_url
        if (strpos($href, 'http') !== 0) {
            $href = $this->site_url . 'docs/' . ltrim($href, '/ ');
        }

		// Save the corrected href
		$link['href'] = $href;

		return $link;
	}

	//--------------------------------------------------------------------

    /**
     * Modifies the page content by adding:
     *
     *  - An H1 at the top of the page.
     *  - named anchors for all H2/H3
     *  - generate a docmap from H2/H3 and insert it under the new H1
     *
     * @param $str
     * @return mixed
     */
    public function addDocumentMap($str)
    {
        $map = $this->mapDocument($str);

        if (empty($map))
        {
            return $str;
        }

        $list = '<ul>';

        foreach ($map as $item)
        {
            $list .= "<li><a href='{$item['link']}'>{$item['name']}</a>";

            if (! empty($item['items']))
            {
                $list .= "<ul>";

                foreach ($item['items'] as $row)
                {
                    $list .= "<li><a href='{$row['link']}'>{$row['name']}</a>";
                }

                $list .= "</ul>";
            }

            $list .= "</li>";
        }

        $list .= '</ul>';

        $header_end = strpos($str, '</h1>');
        $header_end = ! empty($header_end) ? $header_end + 5 : 0;

        $header = ! empty($header_end) ? substr($str, 0, $header_end) : '<p>In this page:</p>';

        $str = $header . $list ."\n<br/>\n". str_replace($header, '', $str);

        return $str;
    }

    //--------------------------------------------------------------------

    /**
     * Builds a map of the document.
     * @param $str
     */
    protected function mapDocument(&$content)
    {
        if (empty($content))
        {
            return [];
        }

        // If $content already has a wrapping <div> and </div> tags, remove them,
        // since we'll replace them just below.
        if (strpos($content, '<div>') === 0) {
            $content = substr($content, 5);

            // Trailing div also?
            if (substr($content, -6) == '</div>') {
                $content = substr($content, 0, -6);
            }
        }

        try {
            $xml = new \SimpleXMLElement('<?xml version="1.0" standalone="yes"?><div>' . $content . '</div>');
        } catch (\Exception $e) {
            // SimpleXML barfed on us, so send back the un-modified content
            return [];
        }

        $map = [];
        list($map, $content) = $this->extractDocMapAndAddAnchors($content, $xml, $map);

        return $map;
    }

    //--------------------------------------------------------------------

    /**
     * Creates a Document Map based on <h2> and <h3> tags.
     * Also adds named anchors into the $content so the map
     * can link to the content properly.
     *
     * @param $content
     * @param $xml
     * @param $map
     * @return array
     */
    protected function extractDocMapAndAddAnchors(&$content, $xml, $map)
    {
        // Holds the current h2 we're processing
        $current_obj = [];

        $currentChild = 0;

        foreach ($xml->children() as $childType => $line) {
            $currentChild++;

            // If it's an h1 - take the first and make it
            // our page title.
            if ($childType == 'h1' && empty($this->page_title))
            {
                $this->page_title = (string)$line;
            }

            // Make sure that our current object is
            // stored and reset.
            if ($childType == 'h1' || $childType == 'h2') {
                if (count($current_obj)) {
                    $map[] = $current_obj;
                    $current_obj = [];
                }
            }

            if ($childType == 'h2') {
                $name = (string)$line;
                $link = strtolower(str_replace(' ', '_', (string)$line));

                $current_obj['name'] = $name;
                $current_obj['link'] = '#' . $link;
                $current_obj['items'] = [];

                // Insert a named anchor into the $content
                $anchor = '<a name="' . $link . '" id="' . $link . '" ></a>';

                $search = "<h2>{$name}</h2>";

                $content = str_replace($search, $anchor . $search, $content);
            } elseif ($childType == 'h3') {
                // Make sure we have some place to store the items.
                if (! isset($current_obj['items'])) {
                    $current_obj['items'] = [];
                }

                $link = strtolower(str_replace(' ', '_', (string)$line));
                $name = (string)$line;

                $current_obj['items'][] = [
                    'name' => $name,
                    'link' => '#' . $link
                ];

                // Insert a named anchor into the $content
                $anchor = '<a name="' . $link . '" id="' . $link . '" ></a>';

                $search = "<h3>{$name}</h3>";

                $content = str_replace($search, $anchor . $search, $content);
            }

            // Is this the last element? Then close out our current object.
            if (count($xml) == $currentChild) {
                if (count($current_obj)) {
                    $map[] = $current_obj;
                }
            }
        }
        return [$map, $content];
    }
    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Setters
    //--------------------------------------------------------------------

    public function setCurrentURL($url)
    {
        $this->current_url = $url;

        return $this;
    }

    //--------------------------------------------------------------------

    public function setSiteURL($url)
    {
        $this->site_url = $url;

        return $this;
    }

    //--------------------------------------------------------------------

    public function setCollections($collections)
    {
        $this->collections = $collections;

        return $this;
    }

    //--------------------------------------------------------------------



}