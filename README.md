# SixTen Press Isotope

SixTen Press Isotope is a little plugin which makes setting up an isotope based archive easy peasy.

## Description

This plugin doesn't do anything until you tell it to. In your theme or plugin, add this code to an archive to kick start the archive layout:

```php
add_action( 'template_redirect', 'prefix_run_isotope' );
function prefix_run_isotope() {
	if ( function_exists( 'sixtenpress_do_isotope' ) ) {
		sixtenpress_do_isotope();
	}
}
```


## Requirements
* WordPress 4.3, tested up to 4.4
* Genesis Framework (templates and widget will not work with other themes, although post type and metaboxes will work with any theme)

## Installation

### Upload

1. Download the latest tagged archive (choose the "zip" option).
2. Go to the __Plugins -> Add New__ screen and click the __Upload__ tab.
3. Upload the zipped archive directly.
4. Go to the Plugins screen and click __Activate__.

### Manual

1. Download the latest tagged archive (choose the "zip" option).
2. Unzip the archive.
3. Copy the folder to your `/wp-content/plugins/` directory.
4. Go to the Plugins screen and click __Activate__.

Check out the Codex for more information about [installing plugins manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Git

Using git, browse to your `/wp-content/plugins/` directory and clone this repository:

`git clone git@bitbucket.org:sixtenpress/sixtenpress-isotope.git`

Then go to your Plugins screen and click __Activate__.

## Frequently Asked Questions

## Changelog

### 1.0.0
* initial release on bitbucket

## Credits

Built by [Robin Cornett](http://robincornett.com/)