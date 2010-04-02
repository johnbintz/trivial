# Trivial - the ultra-lightweight Web framework for PHP

## Installation

Installation is done via RubyGems:

`gem install trivial`

A new binary is created, `trivialize`.

## Creating a new site

`cd` to the directory where you want your new site and type:

`trivialize my-new-site`

A directory called `my-new-site` will be created with the site structure in place. An example `content/index.html` and `views/application.inc` will also be installed.

## Upgrading an existing site

Backup your existing site folder, `cd` to the site folder and type:

`trivialize --upgrade`

The following files will be overwritten:

* `lib/trivial.php`
* `styles/blueprint`

And if they don't exist, the following files will be created:

* `config/trivial.inc`

## The request process

When a request comes in to that directory for a file that doesn't exist, trivial
does the following (for the examples, the request was for `about_us/contact.html` and the default `$layout` value is `"application"`):

* The `content` folder is checked for an `.html` or `.inc` file that matches the path (`content/about_us/contact.html`).
  * If a `.html` file exists, the contents of the file are pulled into the global `$content` variable.
  * If a `.inc` file exists, the file is include()d and the output is placed into the global `$content` variable.
* The `actions` folder is checked for two files:
	* `actions/application.inc`
	* `actions/about_us/contact.inc`
	Each found file is included into the program in that order, potentially modifying `$content` or `$layout`.
* The `views` folder is checked for two files:
	* `views/about_us/contact.inc`
	* `views/application.inc`
	Each found file is included into the program, including `$content` where specified and outputting back into `$content`.
	A layout *must exist* or an error will occur.
* The value of `$content` is output to the visitor.

## Styles and scripts

By default, the provided `views/application.inc` file has three functions for including stylesheets and scripts:

    <head>
      <?php echo blueprint() ?>
      <?php echo scripts() ?>
      <?php echo styles() ?>
    </head>

### Blueprint

The latest [Blueprint CSS Framework](http://blueprintcss.org/) comes with Trivial. It's included into the defaut application.inc layout
with the `blueprint()` function. Blueprint's licence can be found in the `styles/blueprint` directory.

### Other Styles and Scripts

The `scripts()` and `styles()` functions, by default, only search for files named `application.ext` and `name/of/request.ext`.
For the request `contact/about_us.html`, the following JavaScript and CSS files will be searched for, in this order:

* `scripts/application.js`
* `scripts/contact/about_us.js`
* `styles/application.css`
* `styles/contact/about_us.css`

You can incude other scripts and stylesheets in several ways:

#### Globally, before application- and page-specific includes

Inside of `config/trivial.inc`, add the following variable definition:

`$global_head = array('scripts' => array(), 'styles' => array());`

In the `scripts` and `styles` arrays, add the names of the other files to include, not including the extension:

`$global_head = array('scripts' => array('jquery-1.4.2.min'), 'styles' => array('client'));`

These scripts are loaded before the ones listed above, so the new search path becomes:

* `scripts/jquery-1.4.2.min.js`
* `scripts/application.js`
* `scripts/contact/about_us.js`
* `styles/client.css`
* `styles/application.css`
* `styles/contact/about_us.css`

#### Locally, via a view, before application- and page-specific includes

In your view, modify `$global_head` as you would above.

#### Within the layout, without adding new script() or style() tags

Adding additional parameters to the `scripts()` or `styles()` tag works the same as adding items to the `$global_head`:

`<?php echo scripts('jquery-1.4.2.min') ?>`
`<?php echo styles('client') ?>`

Scripts/styles defined in the tag itself are loaded before `$global_head`.

