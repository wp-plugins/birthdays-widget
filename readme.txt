=== Birthdays Widget ===
Plugin Name: Birthdays Widget
Plugin URI: http://wordpress.org/plugins/birthdays-widget/
Description: Birthdays Widget
Author: lion2486, Sudavar
Version: 1.5
Author URI: http://codescar.eu 
Contributors: lion2486, Sudavar
Tags: widget, birthdays, custom
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: 1.5
Text Domain: birthdays-widget
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Birthdays widget plugin produces a widget for your Wordpress website which 
displays a happy birthday image to your clients/users.

== Description ==

Birthdays Widget allows to add your custom birthday-list of any person you want and 
display it in a widget only when it's necessary.

Some features:

* Integration with WordPress User Profile and Registration Form
* Export to CSV file
* Import/Restore from CSV
* Greek & English Languages

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `birthdays-widget` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add your birthdays List in Birthdays Widget Settings page
4. Add the widget to your sidebar.

== Screenshots ==

1. Settings Page
2. Widget preview

== Changelog ==

= 1.5. =
* Added option to select which WP User's meta data you like to be displayed as a name
* Added option to disable integration with WP User Profile
* All birthdays are now saved in our table, not in wp_usermeta
* Fixed error when admin changed birthdays of other users
* Fixed error when editin a birthday from the birthday-list
* TODO now the meta data you wish to display is inserted in our table so if it's changed it's not altered in our table, must fix this

= 1.4 =
* New plugin's options page 
* Admin can now choose the image shown in widget through WP Media Library at plugin's options page
* Admin can now choose which roles have access to plugin's options (next shall do it with WP capabilities instead)
* Now WP users have birthday date field in their profile and those dates are checked too
* Admin can enable two fields (user's name and birthday date) at new user registration form

= 1.3 =
* Upload support (csv file)
* possible restore-transfer of data

= 1.2 =
* el_GR changed to el

= 1.1 =
* Versioning fix

= 1.0 =
* Seems it works for v1.0

= 0.3 =
* added some screens
* an SVN TAG problem

= 0.2 =
* English Language now is the stable
* Translated to Greek (with l10n tools)
* Some locale fixes for dates
* Ajax disabled for now

= 0.1 =
* First version
* Only Greek language support for now. 
