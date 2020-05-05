# ACF City Selector Field

Welcome to the City Selector plugin, which is an extension for [Advanced Custom Fields](https://www.advancedcustomfields.com). This is not a stand-alone plugin, you'll need ACF for it.

## Index
- [Version](#version)
- [Description](#description)
- [Installation](#installation)
- [Usage](#usage)
- [Impact](#impact)
- [Cities](#cities)
- [Hooks](#hooks)
- [Compatibility](#compatibility)
- [Tested on](#tested)
- [Remove donation notice](#removedonation)
- [Support](#support)
- [Website](#website)
- [Disclaimer](#disclaimer)
- [To do](#todo)
- [Credit](#credit)
- [Changelog](#changelog)

<a name="version"></a>
### Version

0.13

<a name="description"></a>
### Description

This plugin allows you to select a city, based on country and province/state in an ACF Field Group.

![Screenshot ACF City Selector](https://beee4life.github.com/beee4life.github.io/images/screenshot-acf-city-selector.png)

It creates a new `field type` for you to choose when you're creating an ACF Field Group. If you click '+ add field' in a Field Group, you will find a new option (category: "Choice") to choose called `City Selector`.

* Add the field.
* Select whether to show labels above the input fields (default = yes).
* Save/publish the Field Group.

<a name="installation"></a>
### Installation

1. Download the [latest release](https://github.com/Beee4life/acf-city-selector/archive/master.zip).
1. Copy the `acf-city-selector` folder into your `wp-content/plugins` folder.
1. Activate the `ACF City Selector` plugin via the plugins admin page.
1. Create a new field via ACF and select the `City Selector` type (listed in the Choice section).
1. Select if you want to show labels
1. Select if you want a default country
1. (optional) Import new cities with help of the included excel sheet.

<a name="usage"></a>
### Usage

When the field is used a single field, 3 values are stored in an array: 

```php
array(3) {
  ["countryCode"]=>
  string(2) "NL"
  ["stateCode"]=>
  string(5) "NL-NH"
  ["cityName"]=>
  string(9) "Amsterdam"
}
```

When the field is used in repeater field, the values are stored in a multidimensional array:

```php 
array(2) {
  [0]=>
  array(3) {
    ["countryCode"]=>
    string(2) "BE"
    ["stateCode"]=>
    string(5) "BE-BR"
    ["cityName"]=>
    string(10) "Anderlecht"
  }
  [1]=>
  array(3) {
    ["countryCode"]=>
    string(2) "NL"
    ["stateCode"]=>
    string(5) "NL-FL"
    ["cityName"]=>
    string(6) "Almere"
  }
}
```

The reason why the country is prefixed in the storage is because there can be states/provinces which use the same abbreviation. You won't notice this, since this value is formatted on return.

The return value gets overridden so you get 'more return info' and properly formatted (stateCode). 5 values are returned:
```php
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
```

Echo it as follows:

```php
$city_selector = get_field('field_name');
echo 'I live in ' . $city_selector['cityName'];
echo 'which is in ' . city_selector['stateName'] . ' (' . city_selector['stateCode'] . ')'; 
echo ' which lies in the country: ' . $city_selector['country'] . ' (' . $city_selector['countryCode'] . ')';
```

This outputs:

```
"I live in Amsterdam which is in the state Noord-Holland (NH) which lies in the country Netherlands (NL)".
```
        
<a name="impact"></a>
### Impact

The plugin adds a database table named `{$wpdb->prefix}cities` upon plugin activation and imports cities from 3 different countries.

<a name="cities"></a>
### Cities

The plugin comes with all cities in the Benelux (Belgium, Netherlands, Luxembourg) pre-installed.

You can also add more countries yourself, through SQL or CSV import. There's a simple excel sheet included in the plugin and can be found in the `import` folder. With this sheet, you can easily create an SQL insert statement or a CSV data set.

The explanation on how to do this, can be found on the first tab/sheet of the excel file.

There will be several country packages (csv files) available (soon), especially for this plugin. These packages can be imported as is. These will be made available soon, through the [ACFCS website](https://acfcs.berryplasman.com).

<a name="hooks"></a>
### Hooks

There are a few hooks available to add your own custom actions. 

* acfcs_after_success_verify - hooks after successful csv verification
* acfcs_after_success_import - hooks after successful csv import
* acfcs_after_success_import_raw - hooks after successful raw csv import
* acfcs_after_success_import_be - hooks after importing preset country Belgium
* acfcs_after_success_import_lu - hooks after importing preset country Luxembourg
* acfcs_after_success_import_nl - hooks after importing preset country Netherlands
* acfcs_after_success_nuke - hooks after truncating the table

More can be expected.

<a name="compatibility"></a>
### Compatibility

This ACF field type is compatible/tested with ACF 5 (Pro). It's slightly tested with the free version, but we won't be putting any more time in it. Just buy the Pro version. It's worth every penny !

<a name="tested"></a>
### Tested on

* Wordpress 5.4.1
* Advanced Custom Fields 4.4.12
* Advanced Custom Fields Pro 5.8.9

<a name="removedonation"></a>
### Remove donation notice

If you want to remove the donation box in ACF, add the following line to functions.php:
`add_filter('acfcs_remove_donate_nag', '__return_true');`

<a name="support"></a>
### Support

If you need support, please turn to [Github](https://github.com/Beee4life/acf-city-selector/issues).

<a name="website"></a>
### Website

[acfcs.berryplasman.com](https://acfcs.berryplasman.com)

<a name="disclaimer"></a>
### Disclaimer

This plugin is not 100% finished yet. It won't break anything but be on the look out, just in case.

The default country setting works for the following situations: 
* in a single field
* in a repeater field
* in a group

This plugin doesn't work (yet) in the following situations:
* multiple single fields on 1 page
* as a repeater field in groups
* as a single field inside a flexible content block
* as a repeater field inside a flexible content block

This plugin hasn't been tested yet in the following situations: 
* as a repeater field on a user page
* most front-end usage (except single use)
* with the Gutenberg editor (and don't hold your breath either)

<a name="todo"></a>
### TODO

The things on our 'to do list' to tackle soon (beside aforementioned situations) are the folllowing things, but not necessary in this order:
- change length of state code to 3 characters
- add select2 to dropdowns (including a search like with a post object field) 
- setting a default country

<a name="credit"></a>
### Credit

I got the idea for this plugin through [Fabrizio Sabato](https://github.com/fab01) who used it a bit differently, which can ben seen [here](http://www.deskema.it/en/articles/multi-level-country-state-city-cascading-select-wordpress).

Since I couldn't fix the Javascript for this plugin, [Jarah de Jong](https://github.com/inquota) took care of the JS basics.

<a name="changelog"></a>
### Changelog

0.14
* added the option to set a default country (for single fields/in groups/in repeaters)

0.13
* Forgot to change version

0.12
* Hotfix to remove an incorrect SQL statement

0.11
* Fixed select values in admin state search
* Added natural sorting for French 'arrondisements'

0.10
* Made the field available on user pages
* Dropped inclusion for v4.

0.10-beta
* Made the field available in repeaters
* Made the field available in groups

0.9
* Added a search page to manuallly remove cities from the database

0.8
* Fix incorrect version
* Removed deprecated filter contextual_help

0.7
* Change indentation from spaces to tabs

0.6
* Translate more strings
* Fix import errors for Luxembourg
* DRY import code

0.5
* Fix unescaped characters on import

0.4
* Internationalised all cities/states/countries

0.3
* Added hooks for import/delete actions

0.2
* Added database collation
