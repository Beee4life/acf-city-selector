# ACF City Selector Field

Welcome to the City Selector plugin, which is an extension for [Advanced Custom Fields](http://www.advancedcustomfields.com). This is not a stand-alone plugin, you'll need ACF for it.

### Version

0.2

### Installation

1. Copy the `acf-city-selector` folder into your `wp-content/plugins` folder.
2. Activate the `ACF City Selector` plugin via the plugins admin page.
3. Create a new field via ACF and select the `City Selector` type (listed in the Choice section).
4. (optional) Import new cities with help of the included excel sheet.
5. Please refer to the description for more info regarding the field type settings.

### Description

This plugin allows you to select a city, based on country and province/state in an ACF Field Group.

![Screenshot ACF City Selector](http://beee4life.github.com/beee4life.github.io/images/screenshot-acf-city-selector.png)

It creates a new 'field type' for you to choose when you're creating an ACF Field Group. If you click '+ add field' in a Field Group, you will find a new option (category: "Choice") to choose called `City Selector`.

* Add the field.
* Choose any name you want.
* Choose any key you want.
* Select whether to show labels above the input fields (default = yes)
* Save/publish the Field Group.

### Usage

3 values are stored in an array: 

    array(3) {
      ["countryCode"]=>
      string(2) "NL"
      ["stateCode"]=>
      string(5) "NL-NH"
      ["cityName"]=>
      string(9) "Amsterdam"
    }

The reason why the country is prefixed in the storage is because there can be states/provinces which use the same abbreviation. You won't notice this, since we format this value on return.

We override the return value so you get more return info and properly formatted (stateCode). 5 values are returned:

    array(5) {
      ["countryCode"]=>
      string(2) "NL"
      ["stateCode"]=>
      string(5) "NH"
      ["cityName"]=>
      string(9) "Amsterdam"
      ["stateName"]=>
      string(13) "Noord-Holland"
      ["countryName"]=>
      string(11) "Netherlands"
    }

Echo it as follows:

    $city_selector = get_field('field_name');
    echo 'I live in ' . $city_selector['cityName'];
    echo 'which is in ' . city_selector['stateName'] . ' (' . city_selector['stateCode'] . ')'; 
    echo ' which lies in the country: ' . $city_selector['country'] . ' (' . $city_selector['countryCode'] . ')';

This outputs:

    "I live in Amsterdam which is in the state Noord-Holland (NH) which lies in the country Netherlands (NL)".
        
### Impact

The plugin adds a database table named `{$wpdb->prefix}cities` upon plugin activation and imports cities from 3 different countries.

### Cities

The plugin comes with all cities in the Benelux (Belgium, Netherlands, Luxembourg) pre-installed.

You can also add more countries yourself, through SQL or CSV import. There's a simple excel sheet included in the plugin and can be found in the `import` folder. With this sheet, you can easily create an SQL insert statement or a CSV data set.

The explanation on how to do this, can be found on the first tab/sheet of the excel file.

We have created several country packages (csv files) especially for this plugin. These files are ready to go and can be imported without right away. These will be made available for a small fee soon, through the [ACFCS website](http://acfcs.berryplasman.com).

### Compatibility

This ACF field type is compatible/tested with ACF 4 (Free) as well as ACF 5 (Pro).

### Contents

The plugin contains the following languages:
* php
* javascript / jquery
* sql
* css

### Tested on

* Wordpress 4.9.2
* Advanced Custom Fields 4.4.12
* Advanced Custom Fields Pro 5.6.6

#### To Do

* [ ] - Test on Mac Firefox
* [ ] - Test on Mac Safari
* [ ] - Test on PC Chrome
* [ ] - Test on PC Firefox
* [ ] - Test on PC Safari
* [ ] - Test on iPhone Chrome
* [ ] - Test on iPhone Safari
* [ ] - Test on iPad Chrome
* [ ] - Test on iPad Safari

### Support

If you need support, please turn to [Github](https://github.com/Beee4life/acf-city-selector/issues).

### Remove donation notice

If you want to remove the donation box in ACF, add the following line to functions.php:
`add_filter('remove_acfcs_donate_nag', '__return_true');`

### Website

http://acfcs.berryplasman.com (not for support)

### Disclaimer

This plugin is not 100% finished yet. It most likely won't break anything but use caution, just in case. 

### Credit

I got the idea for this plugin through [Fabrizio Sabato](https://github.com/fab01) who used it a bit differently, which can ben seen [here](http://www.deskema.it/en/articles/multi-level-country-state-city-cascading-select-wordpress).

Since I couldn't fix the Javascript for this plugin, [Jarah de Jong](https://github.com/inquota) took care of it.

### Changelog

0.2
Added database collation
