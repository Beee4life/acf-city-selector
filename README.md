# ACF City Selector Field

Welcome to the City Selector plugin, which is an extension for [Advanced Custom Fields](http://www.advancedcustomfields.com). This is not a stand-alone plugin, you'll need ACF for it.

### Version

0.1 (beta)

## Here's how it works

### Description

This plugin allows you to select a city, based on country and provence/state in an ACF Field Group.

![Screenshot ACF City Selector](http://beee4life.github.com/beee4life.github.io/images/screenshot-acf-city-selector.jpg)

It creates a new 'field type' for you to choose when you're creating an ACF Field Group. If you click '+ add field' in a Field Group, you will get a new option to choose called `City Selector`.

### Disclaimer

This plugin is not finished yet, hence why only the develop branch is active. Using it, is at your own risk. If you do, keep the following in mind.

* You have to set the city each time you update, since it doesn't load stored values (yet) or you can place it behind conditional logic to avoid storing empty values
* If you deactivate the plugin and reactivate it again, all cities are inserted again (but there's a truncate table option now).

### Tested on

* Wordpress 4.8
* Advanced Custom Fields Pro 5.5.14

### Support

Support is welcome since I haven't fixed a few issues. You're welcome to fork it and create a pull request.

### Credit

I got the idea for this plugin through [Fabrizio Sabato](https://github.com/fab01) who used it a bit differently, which can ben seen [here](http://www.deskema.it/en/articles/multi-level-country-state-city-cascading-select-wordpress).

### Changelog

Nothing yet, still developing...
