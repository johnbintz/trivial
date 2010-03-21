# Trivial - the ultra-lightweight Web framework for PHP

## Installation

Installation is done via RubyGems:

`gem install trivial`

A new binary is created, `trivialize`.

## Creating a new site

`cd` to the directory where you want your new site and type:

`trivialize my-new-site`

A directory called `my-new-site` will be created with the site structure in place. An example `content/index.html` and `views/application.inc` will also be installed.

## The request process

When a request comes in to that directory for a file that doesn't exist, trivial
does the following (for the examples, the request was for `about_us/contact.html` and the default `$layout` value is `"application"`):

* The `content` folder is checked for a `.html` file that matches the path (`content/about_us/contact.html`). If it exists, the contents of the file are pulled into the global `$content` variable.
* The `actions` folder is checked for two files:
	* `actions/application.inc`
	* `actions/about_us/contact.inc`
	Each found file is included into the program, potentially modifying `$content` or `$layout`.
* The `views` folder is checked for two files:
	* `views/about_us/contact.inc`
	* `views/application.inc`
	Each found file is included into the program, including `$content` where specified and outputting back into `$content`.
* The value of `$content` is output to the visitor.

## Styles and scripts

Styles and scripts can be searched for in a structured way. The included `views/application.inc` gives an example as to how this works.
