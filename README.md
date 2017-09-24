# ACF City Selector Field

Welcome to the City Selector plugin, which is an extension for [Advanced Custom Fields](http://www.advancedcustomfields.com). This is not a stand-alone plugin, you'll need ACF for it.

### Version

0.1 (beta)

## Here's how it works

### Description

This plugin allows you to select a city, based on country and provence/state in an ACF Field Group.

![Screenshot ACF City Selector](http://beee4life.github.com/beee4life.github.io/images/screenshot-acf-city-selector.jpg)

It creates a new 'field type' for you to choose when you're creating an ACF Field Group. If you click '+ add field' in a Field Group, you will get a new option to choose called `City Selector`.

Add it, give it a name and add a unique field name. We recommend something like `$wpdb->prefix_city_selector`.

Update/save the Field Group.

### Usage

The values are stored in an array, containing 3 values, which looks like this:

    array(3) {
      ["countryCode"]=>
      string(2) "NL"
      ["stateCode"]=>
      string(5) "NL-NH"
      ["cityNameAscii"]=>
      string(9) "Amsterdam"
    }

Echo as follows:

    $city_selector = get_field('field_name');
    echo 'I live in ' . $city_selector['cityNameAscii'];
    echo 'which is in ' . substr( $city_selector['stateCode'], 3 ); // this strips the country code and the dash

### Impact

* The plugin adds a database table named `$wpdb->prefix_cities` upon plugin activation.
* The plugin removes the database table named `$wpdb->prefix_cities` upon plugin deletion through the admin panel. Deleting by FTP won't delete the table !

### Cities

The plugin will come with all cities in the Benelux (Belgium, Netherlands, Luxembourg) pre-installed, when it reaches version 1.0. Now only all cities in the Netherlands are included.

You can also add more countries yourself, through a simple excel sheet which is included in the plugin and can be found in the import folder.

The explanation on how to do this, can be found on the first tab/sheet of the excel file.

### Compatibility

This ACF field type is compatible/tested with ACF 5.

I didn't look at the compatibility for ACF 4, nor will I any time soon. Don't assume it will work, it won't. Just leave it until it's compatible or update to v5/Pro.

### Disclaimer

This plugin is not finished yet. Using it, is at your own risk. If you do, keep the following in mind.

* You have to set the city each time you update, since it doesn't load stored values (yet)

* If you deactivate the plugin and reactivate it again, all cities are inserted again (but there's a truncate table option now).

### Tested on

* Wordpress 4.8.2.
* Advanced Custom Fields Pro 5.6.2

#### To Do
* [X] - Store values properly
* [X] - Validate values upon empty
* [X] - Drop tables on plugin deletion
* [X] - Add settings page
* [X] - Add settings page option to truncate table
* [X] - Add Dutch cities/provences
* [X] - Add Belgian cities/provences (in progress)
* [X] - Add Luxembourg cities/provences
* [X] - Test on Mac Chrome
* [ ] - Load values when editing post
* [ ] - Prevent values being inserted again when plugin is re-activated (without being deleted)
* [ ] - Return state/provence name
* [ ] - Add lazy/fancy loading (ajax) - optional
* [ ] - Add settings page option to import countries from CSV
* [ ] - Add translations for English country names - optional
* [ ] - Test on Mac Firefox
* [ ] - Test on Mac Safari
* [ ] - Test on PC Chrome
* [ ] - Test on PC Firefox
* [ ] - Test on PC Safari
* [ ] - Test on iPhone Chrome
* [ ] - Test on iPhone Safari
* [ ] - Test on iPad Chrome
* [ ] - Test on iPad Safari

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
* sql
* css

### Remove donation notice

If you want to remove the donation box in ACF, add the following line to functions.php:
`add_filter('remove_donate_nag', '__return_true');`

### Support

Support is welcome since I haven't fixed a few issues. You're welcome to fork it and create a pull request.

### Credit

I got the idea for this plugin through [Fabrizio Sabato](https://github.com/fab01) who used it a bit differently, which can ben seen [here](http://www.deskema.it/en/articles/multi-level-country-state-city-cascading-select-wordpress).

### Changelog

Nothing yet, still developing...
