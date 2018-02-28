=== Just Image Optimizer ===
Contributors: aprokopenko
Plugin Name: Just Image Optimizer BETA
Version: 0.9
Description: Compress image files, improve performance and boost your SEO rank using Google Page Speed Insights compression and optimization.
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

This is the only one plugin, which can help you to pass Google Page Speed image size optimization test. Futhremore, it
compress image file sizes, so you get performance boost and improve your SEO rand in Google.

Plugin uses Google Page Speed Insights API to optimized images. All you need is a Google console account and an API key.

Image Optimization is run in background, on Google servers, so the site won't loose any performance. There are no special server requirements.

= Issues tracker =

If you have any issues or ideas, please raise an issue on our github repository:
https://github.com/justcoded/just-image-optimizer/issues

= Plugins compatibility =

We plan to add compatibility with such plugins in next releases:

* [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/)
* [Just Responsive Images](https://wordpress.org/plugins/just-responsive-images/)

== Installation ==

1. Upload the `just-image-optimizer` plugin to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your API key and desired settings via the `Media -> Just Image Optimizer` settings page.
4. Done!

== Frequently Asked Questions ==

= Can I revert original images quality? =

To revert original quality of the images you can use [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) plugin.
Just regenerate single image or all images at once to create new images from original source.

== Screenshots ==

1. Optimization service Connect options.
2. Plugin Settings page.
3. Plugin Dashboard with optimization statistics.
4. Media library optimization info.

== Upgrade Notice ==

There are no any special actions required during the upgrade.

== Changelog ==

= 0.9 =
* First beta version of the plugin. We still work on compatibility with 3rd party plugins and WordPress MultiSite installation.