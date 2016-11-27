# ACF City Selector Field

Welcome to the City Selector plugin, which is an extension for [Advanced Custom Fields](http://www.advancedcustomfields.com). This is not a stand-alone plugin, you'll need ACF for it.

### Version

0.1 (beta)

## Here's how it works

### Description

This plugin allows you to select a city, based on country and provence/state in an ACF Field Group.

It creates a new 'field type' for you to choose when you're creating an ACF Field Group. If you click '+ add field' in a Field Group, you will get a new option to choose called `City Selector`.

Add it, give it a name and add a unique field name. We recommend `$wpdb->prefix_city_selector`.

Update/save the Field Group.

### Impact

This plugin adds one database table named `$wpdb->prefix_cities`.

### Cities

The plugin comes pre-installed with all cities in the Benelux (Belgium, Netherlands, Luxembourg) when it reaches version 1.0. You can also add more countries yourself, through a simple excel sheet which is included in the plugin and can be found in the import folder.

The explanation on how to do this, can be found on the first tab/sheet of the excel file.

### Compatibility

This ACF field type is compatible/tested with ACF 5.

I didn't look at the compatibility for ACF 4. Don't assume it will work, just assume it will break your site and leave it until it's compatible. I don't take any responsibility for it :)

### Disclaimer

Loading the stored values when editing is still missing... This is a javascript/jquery thing, which is not my thing :) If you have a solution, fork it and send me a pull request.

### Tested on

* Wordpress 4.6.1.
* Advanced Custom Fields Pro 5.4.8

#### To Do
* [X] - Store values properly
* [X] - Validate values upon empty
* [ ] - Load values when editing
* [X] - Drop tables on plugin deactivation
* [X] - Add Dutch cities/provences
* [ ] - Add Belgian cities/provences
* [ ] - Add Luxembourg cities/provences
* [ ] - Add German cities/provences
* [X] - Drop tables on plugin deactivation
* [ ] - Test on Mac Chrome
* [ ] - Test on Mac Firefox
* [ ] - Test on Mac Safari
* [ ] - Test on PC Chrome
* [ ] - Test on PC Firefox
* [ ] - Test on PC Safari
* [ ] - Test on iPhone Safari
* [ ] - Test on iPad Safari
* [ ] - Test on PC ie7

### Installation

1. Copy the `acf-city-selector` folder into your `wp-content/plugins` folder.
2. Activate the `ACF City Selector` plugin via the plugins admin page.
3. Create a new field via ACF and select the `City Selector` type (listed in the Choice section).
4. (optional) Import new cities with help of the included excel sheet.
5. Please refer to the description for more info regarding the field type settings.

### Contents

The plugin contains the following languages:
* php
* javascript / jquery
* css

### Support

If you run into bugs or have questions, please post them on [Github](https://github.com/Beee4life/acf-city-selector/issues).

### Credit

I got the idea for this plugin through [Fabrizio Sabato](https://github.com/fab01) who was giving support in the issue list of another ACF plugin  on Github, which can be found [here](http://www.deskema.it/en/articles/multi-level-country-state-city-cascading-select-wordpress).

### Changelog

Nothing yet, still developing.
