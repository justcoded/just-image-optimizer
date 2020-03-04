=== Just Image Optimizer ===
Contributors: aprokopenko
Plugin Name: Just Image Optimizer
Version: 1.1.2
Description: Compress image files, improve performance and boost your SEO rank using Google Page Speed Insights compression and optimization.
Tags: image, resize, optimize, optimise, compress, performance, optimisation, optimise JPG, pictures, optimizer, Google Page Speed
Author: JustCoded
Author URI: https://justcoded.com
Requires at least: 4.5
Tested up to: 5.0.1
Requires PHP: >=5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

Just Image Optimizer uses Google Page Speed Insights API to compress image files, improve performance and boost your SEO rank.

== Description ==

It's the only plugin that will help you pass Google Page Speed image size optimization test. Furthermore, it compresses image file size, so you get a performance boost and improve your page rank in Google.

The plugin uses Google Page Speed Insights API to optimize images. All you need is a Google console account and an API key.

Image Optimization runs in the background on Google servers, so the site will keep its performance intact. There are no special server requirements.

= Requirements =

* Site should be available for Google Page Speed test
* PHP 7

= Issues tracker =

If you have any feedback or ideas, please raise an issue in our GitHub repository:
https://github.com/justcoded/just-image-optimizer/issues

= Plugin compatibility =

Plugin is compatible with such plugins:

* [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/)
* [Just Responsive Images](https://wordpress.org/plugins/just-responsive-images/)

In the upcoming releases, we plan to add compatibility with WordPress MultiSite installation.

== Installation ==

1. Upload `just-image-optimizer` plugin to your `/wp-content/plugins/` directory.
2. Activate the plugin through 'Plugins' menu in WordPress.
3. Configure your API key and desired settings via the `Media -> Just Image Optimizer` settings page.
4. Done!

== Frequently Asked Questions ==

= My image optimization is always "0.00% saved" =

* Please open plugin Settings page. It will check for plugin requirements on your server.
* If you don't have any errors on Settings page, then please check logs. You should see that plugin try to optimize at least 1 attachment.
* Next step is to check that Optimize request URL is accessible by Google Page Speed (just copy it and try to test it with the service).
* If nothing help, please write to us on Github with screenshots of your Settings page, Log page and last Log details page.

= Can I revert original images quality? =

To revert original quality of the images, you can use [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) plugin.

Just regenerate a single image or all images at once to create new images from the source.

== Screenshots ==

1. Optimization service Connect options.
2. Plugin Settings page.
3. Plugin Dashboard with optimization statistics.
4. Media library optimization info.

== Upgrade Notice ==

No special actions are required during the upgrade.

== Changelog ==

= 1.1.3 =
* Update: Skip log if no attachments found.

= 1.1.2 =
* New: Added notice if site is not available online.
* New: Added notice if PHP version is below 7.0.
* Bug fix: Dashboard image size statistics fatal error. 

= 1.1.1 =
* New: Added notice if wp-content is not writable, cause it's required for storing files.
* Bug fix: Fake cron run without plugin settings saved. 

= 1.1 =
* Added compatibility with Just Responsive Images plugin (v1.5+)
* Added compatibility with Regenerate Thumbnails plugin (v3+)

= 1.0 =
* Upgraded optimization logic to continuosly optimizing images with several tries, due to unstable Google Page Speed API responses.
* Improved Log.

= 0.9 =
* First beta version of the plugin. We still work on compatibility with 3rd party plugins and WordPress MultiSite installation.
