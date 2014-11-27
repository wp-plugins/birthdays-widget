=== Birthdays Widget ===
Plugin Name: Birthdays Widget
Plugin URI: http://wordpress.org/plugins/birthdays-widget/
Description: Birthdays widget plugin produces a widget which displays a customizable happy birthday image and wish to your clients/users.
Author: lion2486, Sudavar
Version: 1.6.5
Author URI: http://codescar.eu 
Contributors: lion2486, Sudavar
Donate link: https://www.paypal.com/gr/cgi-bin/webscr?cmd=_flow&SESSION=Rxb14ltcz8y8NfgafCdykAi4liOMv6F4qTihJEStzyBstHV2Eube-Yz49g4&dispatch=5885d80a13c0db1f8e263663d3faee8d66f31424b43e9a70645c907a6cbd8fb4
Tags: widget, birthdays, custom birthday list, WordPress User birthday, birthday calendar
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: 1.6.5
Text Domain: birthdays-widget
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Birthdays widget plugin produces a widget which displays a customizable happy birthday image and wish to your clients/users.

== Description ==

Birthdays Widget allows to add your custom birthday-list and display a custom message in a widget only when it's necessary.
WordPress Users can also have a birthdays field, or you can even draw their birthday date from another user metafield. 

Features:

* **Integration with WordPress User Profile and Registration Form**
* **Customizable message and image**
* **Export to CSV file**
* **Import/Restore from CSV**
* **Greek & English Languages**

Some use our plugin as an announcement tool, as you can modify the message and the image shown in widget.

**Your ratings mean a lot to us. If you like our work please consider leaving a review.**

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `birthdays-widget` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add your birthdays List in Birthdays Widget Settings page
4. Add the widget to your sidebar.

== Screenshots ==

1. Birthdays List Page
2. Widget Preview
3. Options Page

== Changelog ==

= 1.6.5 =
* Added button in birthdays list, to add image to birthday user
* Securing text input variables

= 1.6.4 =
* A small bugfix

= 1.6.3 =
* New template: monthly calendar
* New template: list of names next to images
* Added emails of users in birthdays list
* Added user images in birthdays list, WP Users can draw their image from Gravatar
* Fixed update procedure, by saving plugin's version in db options and checking
* Fixed bug with date_from_profile not been initialized
* Added option: width of images in list template
* Clearer integration of HTML with PHP where possible

= 1.6.0 =
* Multisite Support (Enable/Disable Network functionality)

= 1.5.9 =
* Nicer and friendlier options page

= 1.5.8 =
* Option to disable comma (,) between the names.

= 1.5.7 =
* Small bugfix with comma (,) not showing correctly, thanks to [dlm80](https://wordpress.org/support/profile/dlm80)

= 1.5.6 =
* Small bugfix with shortcode.js not loading

= 1.5.5 =
* Added [DataTables](http://datatables.net/) jQuery plugin for our birthday list, kudos for their work
* Cleaner approach with Javascript and CSS files. Registering scripts and styles, enqueuing them where needed

= 1.5.4 =
* Added shortcode [birthdays class="" img_width=""] which appears in WordPress editor (thanks to: http://wordpress.stackexchange.com/questions/72394/how-to-add-a-shortcode-button-to-the-tinymce-editor)
* Corrected a problem with the comma (,) between the names of people having birthday
* Javascript enabling datepicker, in separate file
* Fixed problem with duplicate $args[ 'before_widget' ], thanks to @blewis1510

= 1.5.3 =
* Small bugfix with widget image selected from the options page.

= 1.5.2 =
* Added the choice to Admin whether to: 1) Save Birthdays in our table or 2) Draw Birthday Date from a specified WP User meta field or 3) Disable Birthdays for WP Users
* Configured a better option page
* All options are saved in one variable, an array
* Added field to customize birthday wish
* Added option to disable image
* Added option to display a widget title
* Export Problem Solved

= 1.5.1 =
* WP User's meta key selected as user's name is not saved in our table, it is fetched every time so it can undergo changes
* Future day can not be selected as birthday date
* Fixed error in registration form
* Added widget's image width option

= 1.5 =
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
