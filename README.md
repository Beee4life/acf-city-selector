# ACF City Selector

Welcome to the City Selector plugin, which is an extension for [Advanced Custom Fields](https://www.advancedcustomfields.com). This is not a stand-alone plugin, you'll need ACF (active) for it to run.

- [Version](#version)
- [Description](#description)
- [Impact](#impact)
- [Installation](#installation)
- [Setup](#setup)
- [Usage](#usage)
- [Cities](#cities)
- [Actions](#actions)
- [Filters](#filters)
- [Functions](#functions)
- [Compatibility](#compatibility)
- [Tested on](#tested)
- [Support](#support)
- [Website](#website)
- [Disclaimer](#disclaimer)
- [Credit](#credit)
- [Changelog](#changelog)

<a name="version"></a>
### Version

1.4.0 - released xx.04.21

<a name="description"></a>
### Description

This plugin allows you to select a city, based on country and province/state in an ACF Field.

![Screenshot ACF City Selector](https://beee4life.github.com/beee4life.github.io/images/screenshot-acf-city-selector.png)

It creates a new `field type` for you to choose when you're creating an ACF Field Group.

<a name="impact"></a>
### Impact

The plugin adds a database table named `{$wpdb->prefix}cities` upon plugin activation and imports cities from 2 different countries.

<a name="installation"></a>
### Installation

1. Download the [latest release zip file](https://github.com/Beee4life/acf-city-selector/releases/latest).
1. In your WordPress admin, go to Plugins -> Add New
1. Click Upload Plugin
1. Upload the zip file that you just downloaded.
1. Activate the `ACF City Selector` plugin via the plugins page.

If you use a composer file to add any plugins/libraries. Add the following to your composer.json:

```
  "repositories": [
    {
      "type": "composer",
      "url": "https://wpackagist.org"
    }
  ]
```

Then run `composer require "wpackagist-plugin/acf-city-selector"` 

or add this to the `require` section by hand:

```
"wpackagist-plugin/acf-city-selector": "^1.0",
```

<a name="setup"></a>
### Setup

1. Create a new field via ACF and select the `City Selector` type (listed in the Choice section).
1. Select if you want to show labels (default = yes)
1. Select if you want to use select2 (default = no)
1. Select if you want a default country (default = none)
1. (optional) Import new cities with help of the included Excel sheet.
1. (optional) Import new cities by csv (available on the website).

<a name="usage"></a>
### Usage

When the field is used by a single field, 3 values are stored in an array: 

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

When the field is used in a repeater field, the values are stored in a multidimensional array:

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

The reason why the state is prefixed (with the country code) in the database is because there can be states/provinces which use the same abbreviation as in another country. You won't notice this, since this value is formatted on return.

The return value gets overridden, so you get 'more return info' and a properly formatted (stateCode). 5 values are returned:
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
        
<a name="cities"></a>
### Cities

The plugin comes with all cities in Belgium and the Netherlands pre-installed.

You can also add more countries yourself, through SQL or CSV import. There's a simple Excel sheet included in the plugin and can be found in the `import` folder. With this sheet, you can easily create an SQL insert statement or a CSV data set.

The explanation on how to do this, can be found on the first tab/sheet of the excel file.

There are a few country packages (csv files) available. These packages can be imported as is. These are available through the [ACFCS website](https://acf-city-selector.com).

<a name="actions"></a>
### Actions

There are a few actions available to add your own custom actions. 

Find all actions [here](https://acf-city-selector.com/documentation/actions/).

<a name="filters"></a>
### Filters

Find all filters [here](https://acf-city-selector.com/documentation/filters/).

<a name="functions"></a>
### Functions

A few custom functions are available for you to easily retrieve data.

Find all functions and their info [here](https://acf-city-selector.com/documentation/functions/).

<a name="compatibility"></a>
### Compatibility

This ACF field type is compatible/tested with ACF 5 (Pro). It's slightly tested with the free version (v4), but we won't be putting any (more) time in it. Just buy the Pro version. It's worth every cent !

<a name="tested"></a>
### Tested with

* [X] Wordpress 5.7.1
* [X] Advanced Custom Fields Pro 5.9.5
* [X] Advanced Custom Fields 4.4.12

<a name="support"></a>
### Support

If you need support, please turn to [Github](https://github.com/Beee4life/acf-city-selector/issues). It's faster than the Wordpress support.

<a name="website"></a>
### Website

[acf-city-selector.com](https://acf-city-selector.com)

<a name="disclaimer"></a>
### Disclaimer

The plugin works in the following situations: 
* in a single field
* in a repeater field
* in a group
* in a flexible content block
* in an accordion field
* as a cloned field
* on taxonomy terms
* on settings pages

The plugin does NOT work properly yet in the following situations: 
* when multiple instances of the field are used in 1 group/on 1 post

It might have some twitches with taxonomies, but need some more testing.

Sometimes the loading of states/cities, takes a few seconds... Don't know why yet...
This seems to be very random and unpredictable.

<a name="credit"></a>
### Credit

I got the idea for this plugin through [Fabrizio Sabato](https://github.com/fab01) who used it a bit differently, which can ben seen [here](http://www.deskema.it/en/articles/multi-level-country-state-city-cascading-select-wordpress).

[Jarah de Jong](https://github.com/inquota) helped me out with some JS at the start and [John McDonald](https://github.com/mrjohnmc) did the German translations.

<a name="changelog"></a>
### Changelog

1.4.0
* escape attributes in dropdowns

1.3.1
* fix styling which was overriding the styling of other messages
* remove function from uninstall.php which prevented deleting of plugin

1.3.0
* fix non-showing errors on verify csv file
* show all errors, instead of just first encountered
* fix dismiss error button

1.2.0
* don't pre-load cities on country change
* fix help tab which overrides other plugins' help tabs
* stripslash searched value (admin)
* update default csv (fixed some typos with 's and 't)

1.1.0
* fix typos + capitalization

1.0.0 - first release in WP repo
* prefix javascript function names

0.35.0
* escape js value

0.34.0
* escape user inputs

0.33.0
* code refactoring according to (most) Wordpress standards

0.32.0
* add acfcs_upload_folder filter
* improve code by making more 'smaller' functions
* import preset countries from csv instead of php
* remove some unnecessary hooks

0.31.1
* fix version number

0.31.0
* change default delimiter from `,` tot `;`.
* change import sheet from `,` tot `;`.
* fixed non-working max lines setting on import
* added Japan and South Korea country files
* extended Spain country file

0.30.0
* messed up release with version numbers

0.29.0
* added a fix for select2 in repeaters/flexible content blocks 
* added a fix for incorrect escaping which caused incorrect ordering in names starting with a `'`.
* added new function as fallback for `acfcs_get_country_name()`
* added China, New Zealand, Aruba and Curaçao country files
* removed flag assets from plugin
* changed URLs to new website domain

0.28.0
* added select2 option
* changed hide labels filter as fallback for select2 
* added new country packages on the website

0.28.0-beta1
* added a new option: "state/province + city" (for when a default country is set)
* added a transient for cities per state 
* added 3 new filters to override field labels 
* added a new filter to override showing of field labels 
* (re-)added a check for database version to prevent unnecessary table updates 

See older changelogs on the [website](https://acf-city-selector.com/documentation/changelog/).
