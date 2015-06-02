# myth:Docs

This is a simple documentation viewer and generator for PHP, based around plain-text, Markdown files. It was developed after researching a number of the static site and documentation generators for PHP and finding that none of them had the features that I needed, were are focused on these areas: 

- generating static HTML files for distribution with projects
- generating dynamic sites based on the documentation
- real-time search capabilities, based on the source files (not static HTML files)
- collecting multiple doc source directories into a single set of documentation.

**NOTE: This library is currently under heavy construction to be used for the [SprintPHP](http://sprintphp.com) website and eventual integration into Sprint itself.**

## Viewing Full Documentation

Once downloaded, you can view the full documentation suite by running the PHP web server in the root folder, and visiting the site in your web browser. 

```
php -S localhost:8000
```

## Configuration

Configuring your documentation is handled through a JSON object that is passed to the `Myth\Docs\Builder` class upon instantiation. This configuration object allows for complete setup of the documentation, including setting the theme, setting up multiple collections, etc. 

If you are using the provided `/index.php` file, then it will look for that object in the  `/docs.json` file. 

As an example, a simple configuration file might look like: 

```
{
  "title": "My Docs",
  "theme": "default",
  "collections": [
	  "developer": {
		"title": "My docs",
		"tagline": "Isn't Documentation A Great Thing!",
		"visible": true,
		"docs_directory": "docs"
	  }
	]
}
```

The following items are available for your use within the JSON object: 

<table>
<thead>
	<tr>
		<td>Attribute</td>
		<td>Default Value</td>
		<td>Description</td>
	</tr>
</thead>
<tbody>
	<tr>
		<td>title</td>
		<td></td>
		<td>Will be used in the title attribute of the generated HTML</td>
	</tr>
	<tr>
		<td>theme</td>
		<td>default</td>
		<td>The name of the theme's folder to use. </td>
	</tr>
	<tr>
		<td>main_layout</td>
		<td>main</td>
		<td>The theme file that provides the primary page layout for the generated HTML.</td>
	</tr>
	<tr>
		<td>nav_layout</td>
		<td>nav</td>
		<td>The theme file that provides the main navigation structure for the generated HTML.</td>
	</tr>
	<tr>
		<td>search_results_layout</td>
		<td>search_results</td>
		<td>The theme file that provides the search results page for the generated HTML.</td>
	</tr>
	<tr>
		<td>collections</td>
		<td></td>
		<td>An array of collection description objects.</td>
	</tr>
</tbody>
</table>


## Collections
Documentation can be grouped into very large collections of documentation. This allows you to create separate User and Developer documentation using the one configuration file. Each collection has it's own configuration section within the JSON object. 

An example collection might look like: 

```
"developer": {
	"title": "myth:Docs",
	"tagline": "myth:Docs Documentation",
	"visible": true,
	"docs_directory": "./docs"
}
```

The `visible` simply tells whether this collection is visible within the generated source. This allows you to have different collections visible in different environments, or build scripts, and yet manage everything from a single spot. 

The `docs_directory` is the name of the directory that is used to located the containing folder of the documentation files.