=== ACF City Selector ===
Tags: acf, custom, fields, custom fields, select, country, city, state, province
Contributors: beee
Requires at least: 3.6.0
Requires PHP: 7.0
Tested up to: 6.1.1
Stable tag: 1.10.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a new (ACF) field to select a city depending on country and state/province.

== Description ==

ACF City Selector is an extension for Advanced Custom Fields which creates a new field where you can select a city, depending on country and province/state.

= Added field =

3 select options
* country
* state/province
* city

= Plugin website =

[https://acf-city-selector.com](https://acf-city-selector.com)

== Installation ==

1. Upload 'acf-city-selector' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on 'Add Field' in a Custom Field Group and add the 'City Selector' field
4. Set your preferred settings (optional)
5. Import any additional cities (optional)

== Frequently Asked Questions ==

= Q. I have a question =

A. Please read the FAQ @ [https://acf-city-selector.com/documentation/](https://acf-city-selector.com/documentation/)

== Changelog ==

= 1.10.0 =
* ?

= 1.9.1 =
* fix incorrect version

= 1.9.0 =
* fixed case for country code in states transient
* added wpdb->prepare (where needed)
* reverted version by function due to some people reporting errors

= 1.8.0 =
* fixed preview
* fixed city names with an '
* fixed city names with special characters

= 1.7.0 =
* fixed raw csv import
