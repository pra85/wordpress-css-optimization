=== CSS Optimization ===
Contributors: o10n
Donate link: https://github.com/o10n-x/
Tags: css, critical css, async, minify, editor, concat, minifier, concatenation, optimization, optimize, combine, merge, cache
Requires at least: 4.0
Requires PHP: 5.4
Tested up to: 4.9.4
Stable tag: 0.0.30
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced CSS optimization toolkit. Critical CSS, minification, concatenation, async loading, advanced editor, CSS Lint, Clean CSS (professional), beautifier and more.

== Description ==

This plugin is a toolkit for professional CSS optimization.

The plugin provides in a complete solution for CSS code optimization, CSS delivery optimization (async CSS loading) and Critical CSS management.

The plugin provides many unique innovations including conditional Critical CSS, timed CSS loading and/or rendering based on `requestAnimationFrame` with frame target, `requestIdleCallback`, element scrolled into view or a Media Query.

The plugin enables to render and unrender stylesheets based on a Media Query or element scrolled in and out of viewport enabling to optimize the CSS for individual devices (e.g. save +100kb of CSS on mobile devices or based on the [save-data header](https://developers.google.com/web/updates/2016/02/save-data)). 

With debug modus enabled, the browser console will show detailed information about the CSS loading and rendering process including a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for an insight in the CSS loading performance of any given configuration.

The plugin contains an advanced CSS editor with CSS Lint, Clean-CSS code optimization and CSS Beautifier. The editor can be personalized with more than 30 themes.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).

**This plugin is a beta release.**

Documentation is available on [Github](https://github.com/o10n-x/wordpress-css-optimization/tree/master/docs).

== Installation ==

### WordPress plugin installation

1. Upload the `css-optimization/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the plugin settings page.
4. Configure CSS Optimization settings. Documentation is available on [Github](https://github.com/o10n-x/wordpress-css-optimization/tree/master/docs).

== Screenshots ==
1. CSS Code Optimization
2. CSS Delivery Optimization
3. Critical CSS Management
4. CSS Editor
5. Above The Fold Optimization


== Changelog ==

= 0.0.30 =
* Bugfix: External script proxy capture client not compatible with regex match.
* Added: proxy capture example.

= 0.0.29 =
* Core update (see changelog.txt)

= 0.0.28 =
* Fix: JSON profile editor template not uploaded to WordPress.

= 0.0.27 =
* Convert critical CSS conditions to new JSON format.

= 0.0.26 =
* Bugfix: Critical CSS minify not working.
* Added: JSON profile editor (backup and restore plugin config)
* Improved critical CSS condition cache.

= 0.0.25 =
* Bugfix: Critical CSS file index not working correctly after adding a file.
* Bugfix: Critical CSS file sort not saving new sort order.
* Improved Critical CSS add file form.

= 0.0.24 =
* Bugfix: potential CssMin.php conflicts when using a different minify plugin.

= 0.0.23 =
* Core update (see changelog.txt)

= 0.0.21 =
* Bugfix: concatenated stylesheets not loaded with async loading disabled.
* Improved: strip CDATA from concatenated inline CSS.
* Added: option to process `@import` links.
* Added: option to rebase relative paths in CSS using [Net_URL2](http://pear.php.net/package/Net_URL2/).

= 0.0.20 =
* Bugfix: uninstaller (@jone11)
* Improved Travis CI build test.
* Added Ruby RSpec + Capybara unit tests.

= 0.0.19 =
* Added: documentation.

= 0.0.18 =
* Bugfix: new conditional critical css arguments parameter.

= 0.0.17 =
* Added: example critical CSS condition JSON.
* Updated JSON schema for critical CSS conditions (core 0.0.16)

= 0.0.16 =
* Bugfix: settings link on plugin index.

= 0.0.15 =
* Core update (see changelog.txt)

= 0.0.14 =
* Added: Critical CSS editor compatibility with old ABTF plugin.

= 0.0.13 =
* Bugfix: Timed loading/exec not working on iphone when using localStorage.

= 0.0.12 =
* Core update (see changelog.txt)

= 0.0.10 =

* Bugfix: localStorage client module not loaded for individual script based timed loading/exec config.

= 0.0.9 =

* Bugfix: Critical CSS drag/drop sorting broken by Closure Compiler.

= 0.0.8 =
Core update (see changelog.txt)

= 0.0.7 =

Added: cache management in admin menu.

= 0.0.6 = 

Bugfix/improvement: Async Config Filter load and render timing.

= 0.0.5 =

Bugfix: HTTP/2 Server Push not applied to async loaded stylesheets.
Bugfix: Global localStorage settings not applied to async loaded stylesheets.
Bugfix: localStorage `HEAD` background update.

= 0.0.4 =

Core update (see changelog.txt)

= 0.0.3 =

Added: Critical CSS Widget in admin bar menu.

= 0.0.2 =

Added: minify critical CSS using [PHP CssMin](https://code.google.com/archive/p/cssmin/).

= 0.0.1 =

Beta release. Please provide feedback on [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).

== Upgrade Notice ==

None.