=== Just Image Optimizer ===
Contributors: aprokopenko
Plugin Name: Just Image Optimizer
Version: 1.1
Description: Compress image files, improve performance and boost your SEO rank using Google Page Speed Insights compression and optimization.
Tags: image, resize, optimize, optimise, compress, performance, optimisation, optimise JPG, pictures, optimizer, Google Page Speed
Author: JustCoded
Author URI: https://justcoded.com
Requires at least: 4.4
Tested up to: 4.9.4
Requires PHP: >=5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

Just Image Optimizer use Google Page Speed Insights API to compress image files, improve performance and boost your SEO rank.

== Description ==

It's the only plugin that will help you pass Google Page Speed image size optimization test. Furthermore, it compresses image file size, so you get a performance boost and improve your page rank in Google.

The plugin uses Google Page Speed Insights API to optimize images. All you need is a Google console account and an API key.

Image Optimization runs in the background on Google servers, so the site will keep its performance intact. There are no special server requirements.

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

= 1.1 =
* Added compatibility with Just Responsive Images plugin (v1.5+)
* Added compatibility with Regenerate Thumbnails plugin (v3+)

= 1.0 =
* Upgraded optimization logic to continuosly optimizing images with several tries, due to unstable Google Page Speed API responses.
* Improved Log.

= 0.9 =
* First beta version of the plugin. We still work on compatibility with 3rd party plugins and WordPress MultiSite installation.
