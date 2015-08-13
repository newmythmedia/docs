<?php namespace Myth\Docs;

class SimplePresenter implements PresenterInterface {

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
		foreach ($xml->xpath('//a') as $link) {
			$link = $this->reformatAnchor($link);
		}

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

		// Save the corrected href
		$link['href'] = $href;

		return $link;
	}

	//--------------------------------------------------------------------


}