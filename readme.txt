=== Vimeography ===
Contributors: iamdavekiss
Tags: vimeo, video, videos, gallery, vimeography, media, player, playlist, showcase, skins, themes, video gallery
Requires at least: 3.3
Tested up to: 4.0.1
Stable tag: 1.2.8
License: GPL3

The easiest way to create beautiful Vimeo galleries on your WordPress site.

== Description ==

Vimeography is a WordPress plugin that allows you to create beautiful, custom video galleries in 30 seconds, tops!

A quick overview:
http://vimeo.com/44555634

For more information, check out [vimeography.com](http://vimeography.com/ "vimeography.com")

Some amazing features:

* Automatically add videos uploaded to a Vimeo user account, channel, album or group
* Easily insert galleries on a page, post or template with the gallery helper or shortcode
* Set a featured video to appear as the first video in your gallery
* Change your gallery's appearance with custom themes
* Tweak your theme's look with the appearance editor
* Control the gallery width using pixels or percentages
* Built-in caching for quick page loads
* Create unlimited galleries

For even more control over your galleries, including unlimited videos, custom sorting, hidden collections and more, check out [Vimeography Pro!](http://vimeography.com/pro "Vimeography Pro")


Make your gallery stand out with our custom themes!
[http://vimeography.com/themes](http://vimeography.com/themes "vimeography.com/themes")

For the latest updates, follow us!
[http://twitter.com/vimeography](http://twitter.com/vimeography "twitter.com/vimeography")

== Installation ==

1. Upload `vimeography.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Help! My theme doesn't look right! =

First of all, don't worry! I promise you that we can get it looking right. This can be caused by a multitude of things, so try the following (in the order of appearance):

* Try surrounding your Vimeography shortcode in `[raw][/raw]` tags.
* Make sure that your WordPress theme is not including multiple versions of jQuery and is using the latest version.
* Make sure that your theme is calling the `wp_footer()` function so that all of the Vimeography stylesheets are printed.
* Try disabling other plugins that are used for photo galleries, minifying scripts, widgets, or otherwise alter your site's appearance, one by one, and really determining if you need it.

= I'm getting an error: This Vimeography gallery does not have a theme assigned to it. =

Make sure that you have the latest version of the Vimeography theme plugin that you are using installed and that it is activated. Then, go to the gallery editor and select that theme under the Appearance tab. If you're still having problems, reach out on the Vimeography contact page.

= I'm getting an error: Vimeography error: the plugin did not retrieve data from the Vimeo API! connect() timed out! =

This usually means that Vimeo is having some network issues. Follow @Vimeo on twitter for updates. If Vimeo is not reporting any issues, you'll need to contact your host and ask why you are unable to connect to Vimeo's IP address. It may be that your host has blocked server access via a firewall.

= I'm having trouble installing a theme. =

Vimeography themes are now treated at WordPress plugins. Make sure you're installing the Vimeography theme on the "Plugins" page.

= How do I get the latest updates for my themes? =

If you purchased your theme at vimeography.com, you should have received an email receipt containing an activation code for your theme. Enter that activation code on the "Manage Activations" page of the Vimeography plugin. Once done, you will be subscribed to theme updates which will automatically be delivered to your WordPress installation, just like any other plugin.

= Why don’t you support YouTub/MetaHall/Flacker/PreschoolHumor? =

Like many other video professionals, I believe that Vimeo is a beautiful website complete with clean design, a supportive community and a straightforward API. This makes Vimeo a great choice for professional looking portfolios. Yes, there are other crummier sites that may also do the job, but that’s like forcing down squid nuggets for dinner when you could be having baked scallops and a caprese appetizer. Vimeo only; enough said.

= How do I add my Vimeography gallery to a post or page? =

Easy! All you have to do is type `[vimeography id="#"]`, where `#` is replaced by the ID number of your gallery.

= Where do I find the ID number of my Vimeography gallery? =

Each gallery’s ID number is located next to the gallery’s title in the first column on the edit galleries page.

= Can I override my Vimeography gallery settings in the shortcode? =

Sure thing! You can define all of the properties found in the admin panel right in your shortcode as well. Try using one, any, or all of the following parameters:
`[vimeography id="3" theme="thumbs" featured="http://vimeo.com/28380190" source="http://vimeo.com/channels/staffpicks" limit="60" cache="3600" width="600px"]`

= Can I add the Vimeography gallery to my theme’s sidebar/footer/header etc.? =

Yes, but you’ll need some PHP knowledge to do it! Open the file you want to add the gallery to, and type `<?php do_shortcode('[vimeography id="#"]'); ?>`, where `#` is replaced by the ID number of your gallery.

= Can I change the look of my Vimeography theme? =

Heck yeah! Use the appearance editor to change your theme's style so that it matches your site perfectly.

= Can I override my Vimeography theme template with some custom code? =

Sure, why not. Simply copy your theme's .mustache file(s) into a new folder located at `wp-content/themes/[my_wordpress_theme]/vimeography/[my_vimeography_theme]` or `wp-content/themes/[my_wordpress_theme]/vimeography/[my_vimeography_theme]/partials` and hack away. Vimeography will load that template instead of the default one for your theme.

== Screenshots ==

1. Create a gallery in 30 seconds, tops!
2. Preview your gallery and customize its appearance.
3. Manage your galleries with a simple interface.
4. Get new styles by installing gallery themes.

== Changelog ==
= 1.2.8 =
* [New] Added a search box to the gallery list page. You can now find a gallery by its name. YUSSS
* [New] Added schema.org markup to the default theme to improve SEO
* [Tweak] Changed the "Manage Activations" menu item to "Manage Licenses"
* [Tweak] Reworked the licensing page to show more information about your product licenses
* [Tweak] Added a link to learn more about Vimeo Pro on the Vimeography Pro page

= 1.2.7 =
* [New] Vimeography Pro now supports download links in galleries! Learn more at http://vimeography.com/pro
* [Tweak] Update the Vimeography Pro product description
* Note: Must be a Vimeo Pro member. Save $10 on Vimeo Pro through 10/31 at http://vimeography.com/vimeo-pro

= 1.2.6 =
* [New] Refreshed the installation instructions for Vimeography Pro
* [Tweak] Wrap the Vimeography class in a class_exists check

= 1.2.5 =
* [Fix] Prevent galleries without videos from attempting to display and being cached
* [Fix] Output better error messages if videos are marked as private and cannot be displayed
* [Fix] Update plugin tests to ensure a passing grade
* Are you a developer with ideas for improving this plugin? Fork me on github! https://github.com/davekiss/vimeography

= 1.2.4 =
* [New] Added support for playlists with Vimeography Pro v0.8 - see http://vimeography.com/pro
* [New] The Vimeography Bugsauce theme now supports playlists with Vimeography Pro.
* [Fix] Setting the background color in the appearance editor sometimes failed with specific themes. Not anymore!

= 1.2.3 =
* [New] Added support for playlists with Vimeography Pro v0.8 - see http://vimeography.com/pro
* [Fix] Duplicate gallery nonce was not being set, causing it to do nothing. Now it works!
* [Fix] The featured video sometimes was processed twice with extra spacing in the description. Not anymore!
* [Fix] The scrollbars in the gallery editor sometimes showed, even when they didn't need to. Not anymore!
* [Tweak] Changed some words on the Vimeography Pro page

= 1.2.2 =
* [New] Added support for gallery template overrides
* [New] Added German translation (thanks to Suentke Remmers)
* [New] Added Spanish translation (thanks to Andrew Kurtis - http://www.webhostinghub.com/)
* [New] Added partial Italian translation (thanks to Giuseppe Pignataro)
* [Fix] Make sure assets aren't loading again during Vimeography Pro paging requests

= 1.2.1 =
* [New] The gallery list page has been totally reworked
* [New] You can now sort galleries by date created, theme applied, and gallery name
* [New] Duplicating a gallery now prompts for a new gallery name and source, if desired
* [New] Duplicating a gallery now also copies that galleries appearance customizations
* [Fix] Theme customizations scrollbar will now scroll to bottom
* [Tweak] Make sure the bugsauce thumbnails don't have any padding

= 1.2.0.4 =
* [Fix] Reset saved activation key indexes when adding or removing a key
* [Fix] Video data will not be cached if returned as not modified from Vimeo
* [Tweak] Only load the Vimeo PHP library if it doesn't already exist

= 1.2.0.3 =
* Added jQuery Slick [MIT] to shared assets library
* Fixed an issue where activation keys with dashes wouldn't save
* Fixed an issue where gallery appearance changes wouldn't save in some cases

= 1.2.0.2 =
* Added a fix for users running PHP <= 5.2, which doesn't support namespaces

= 1.2.0.1 =
* Added a fix for users running PHP <= 5.2, which doesn't support namespaces

= 1.2 =
* Updated Vimeography's filesystem methods to be compatible with WP Filesystem.
* Introduced a vimeography/edit-video/[video_id] filter to allow for video data adjustments.
* Fixed an issue where custom CSS changes could be overwritten on plugin updates.
* Fixed an issue where Vimeography Activation Keys may not be saved properly.
* Fixed an issue that may have prevented some cache files from being loaded properly.
* Updated Vimeo errors to be handled more gracefully.
* Bugsauce no longer needs to be installed as a separate plugin.
* You can safely remove the Vimeography Bugsauce plugin if you have it installed.
* Galleries now ignore videos that are still transcoding, processing or uploading.
* Fixed the gallery creation date formatting in the gallery quickview.
* Updated gallery message styling to match the new WordPress admin styling.

= 1.1.9 =
* Fancybox will now be included in themes outside of the WordPress repository. Sorry about that!

= 1.1.8 =
* Added basic i18n support. Vimeography can now be translated into the language of your choice!
* Fixed an issue that prevented the appearance editor from saving reliably.
* Added cache busting to CSS files that were made from the appearance editor.
* Added error handling for 401 and 500 errors during the Vimeo API request.
* Fixed the "installed themes" scrollbar

= 1.1.7 =
* Fixed an upgrade bug that affected some users.
* Updates to the Bugsauce theme.
* Removed some old, unused javascript.

= 1.1.6 =
* Introducing: Vimeography Pro!
* Check the Vimeography Pro page out for more details.
* Compatibility with WordPress 3.8
* You can now remove activation keys from the Manage Activations page.
* Fixed the sticky menu issue
* Added local Froogaloop copy for HTTPS installs
* Updated oEmbed calls to HTTPS
* Removed duplicate activation keys and prevent them in the future.
* Fixed an issue that prevented some users from being able to get the latest theme updates.
* Fixed a weird issue with duration rounding (Hat Tip: Clark Bilorusky)
* Changed the error when a gallery doesn't have a theme assigned.
* Fixed an issue where the Vimeography menu was showing to non-admin users
* Updated documentation and README

= 1.1.5 =
* Improved Bugsauce compatibility with Vimeography Pro
* Fixed an issue that prevented gallery working on secure sites.

= 1.1.4 =
* Fixed a bug that prevented long Vimeo collection names from being saved.
* Fixed a bug that deactivated other Vimeography plugins after updating Vimeography.

= 1.1.3 =
* Fixed dumb Bugsauce bug
* Changed "My Themes" to "Manage Activations", a place to see and control all activations keys.
* Updated help files
* Updated pro page to actually make sense.
* Fixed weird white space in menu

= 1.1.2 =
* Now supporting, Vimeography Pro!
* Updates to the Bugsauce theme.
* Added more Javascript goodness.

= 1.1.1 =
- Fixed featured video bug with contextual players
- Added new javascript assets
- Fixed caching bug when updating settings
- Fixed an admin javascript error
- Made a peanut butter and jelly sandwich

= 1.1 =
- Better validation for Vimeo URLs
- Fixed the wonky scrollbar in the admin panel
- Improved compatibility with IE9
- Fixed a rare caching issue
- Added new error messages

= 1.0.9 =
* Maintenance release. Better support for Pro. It has been awhile, but it's almost here!
* Added loaders for Vimeography Pro
* Added check to make sure theme exists before loading
* Added IDs to any featured embeds
* Fixed a bug where deleting a gallery could cause a cache error

= 1.0.8 =
* Cleaning out the ol' trunk.
* Adding some more common CSS. Trust me, it's good stuff.

= 1.0.7 =
* Reintroduced the "Number of Videos" setting.
* Updated the Vimeography utilities so embeds work more reliably.

= 1.0.6 =
* Hey, cut me a break. This code has been private for 5 months, so thanks for all the bug reports!
* Updater should be rock solid.

= 1.0.5 =
* Fixed a bug where the updater would not work properly.

= 1.0.4 =
* Added a notification message for themes that aren't activated to receive updates.
* Updated theme assets

= 1.0.3 =
* Fixed an issue where users could no longer delete galleries
* Fixed an issue where some users would get endpoint errors

= 1.0.2 =
* Fixed an issue where some users experienced an strstr() error.

= 1.0.1 =
* Fixed an issue with featured videos.

= 1.0 =
* Now using Vimeo's new API!
* Themes are now standard WordPress plugins and are able to be updated.
* Entire plugin was reworked, should be much quicker and more reliable.
* Added linkifying to descriptions. URLs in descriptions are now clickable.
* Added new colorpicker
* Updated Bugsauce to Theme Plugin
* Updated theme dependencies
* Fixed Menu Jumpiness
* Even better cache control
* Removed video limiting setting

= 0.9.3 =
* Added new caching engine.
* Fixed a caching issue with galleries overridden by shortcode.
* Updated help documentation.

= 0.9.2 =
* Updated fancybox and colorbox for jQuery 1.9 compatibility

= 0.9.1 =
* Code refactor for performance and organization.
* Updated admin CSS
* Updated help and readme files
* Increased Vimeo API timeout to 10 seconds.
* Added l10n compatible strings.
* Added Fancybox for future themes.

= 0.9 =
* New! Introduced theme customization controls. You can now edit the theme and gallery appearance!
* New! The theme list in the gallery editor now has a fresh look.
* NOTE: If you purchased a theme prior to this release, you will be emailed an updated version in the coming weeks so that you can take advantage of this feature.

= 0.8.3 =
* Fixed an issue where the video count setting occasionally failed.
* Added developer bundle.

= 0.8.2 =
* Fixed the theme installation issue, hopefully for good!

= 0.8.1 =
* Fixed an issue where theme installation occasionally failed.
* Updated readme files and tutorial videos.
* Added new themes.

= 0.8 =
* New! Gallery editor has a new intuitive UI.
* New! Featured video now accepts a video URL.
* Updated help files, introduction video and screenshots.
* Updated flexslider to v2.1.
* Updated admin javascript plugins.
* Fixed an issue where theme installation occasionally failed.

= 0.7 =
* New! Galleries now automatically fit the width of their containers.
* New! Added an admin setting to control each gallery's width (theme update required).
* New! Added a shortcode setting to control each gallery's width (theme update required).
* New! Quickview allows you to see all of your gallery settings from the gallery list page.
* Updated bugsauce theme.
* Updated admin styles.

= 0.6.9.2 =
* Updated Bugsauce theme.
* Updated jQuery inclusion method.
* Updated Vimeography helpers file.
* Added several new themes.

= 0.6.9.1 =
* Updated theme asset indexing preferences.
* Fixed an issue where shortcode cache wasn't stored properly.
* Fixed a template namespacing issue.

= 0.6.9 =
* Added expander.js to common theme assets.
* Updated Bugsauce theme display reliability.
* Updated theme folder indexing preferences.
* Fixed an issue where videos were cached regardless of setting.

= 0.6.8.1 =
* Fixed an issue where themes may display incorrectly.

= 0.6.8 =
* Updated theme security methods.

= 0.6.7 =
* Added installed theme versions to Edit Galleries page.
* Updated readme file.

= 0.6.6 =
* Updated admin Javascript code.
* Added installed theme versions to My Themes page.

= 0.6.5 =
* Fixed an issue where the installation of a new theme may fail.

= 0.6.4 =
* Fixed an issue where the WP_Filesystem failed to load.

= 0.6.3 =
* Fixed an issue where upgrading the plugin may not have copied over theme assets.

= 0.6.2 =
* Added colorbox.js to common theme assets.

= 0.6.1 =
* Updated shortcode caching methods.

= 0.6 =
* Added a "Refresh Now" button to advanced settings.
* Added an "Active Theme" column to the gallery list page.
* Added several new themes.
* Updated the "New Gallery" page to make creating galleries even easier!
* Updated the internal shortcode function.
* Updated the database structure and database-related functions.
* Updated first screenshot.
* Fixed an issue where videos from groups may not have displayed properly.

= 0.5.7 =
* Actually fixed an issue where the gallery theme shortcode could not override the default settings.

= 0.5.6 =
* Fixed an issue where the installed theme thumbnails would not load properly.
* Fixed an issue where the gallery theme shortcode could not override the default settings.

= 0.5.5 =
* Fixed an issue where the Bugsauce images would not load properly.

= 0.5.4 =
* Fixed an issue where plugin activation cause error messages.
* Updated the Bugsauce theme.
* Added a common theme assets folder.

= 0.5.3 =
* Fixed an issue where responsive videos didn't size properly in Bugsauce theme.
* Fixed an issue where slider thumbnails behaved unexpectly in Bugsauce theme.
* Fixed an issue where the active theme was not shown as in use.
* Fixed an issue where the featured video was not properly set.
* Changed the screenshots to show more detail.
* Added reliable theme installation methods.

= 0.5.2 =
* Screenshots are now shown on the WordPress plugin page.
* Updates to the Bugsauce theme.

= 0.5.1 =
* Move themes folder to wp-uploads.
* Changed default theme to Bugsauce.
* Switched to flexslider.

= 0.5 =
* First public release.

== Upgrade Notice ==
= 1.2 =
There is also an update available for Vimeography Pro. If you have Vimeography Pro installed, make sure to update it as well. The available version is marked as 0.7

= 1.1.7 =
Make sure to verify that your Vimeography themes are activated after updating.

= 1.1.2 =
This version is the minimum required version for Vimeography Pro.

= 1.0 =
If you've purchased a theme in the past, check your email! Make sure you install the new Vimeography theme plugin before updating! You may also want to back up any HTML or CSS customizations you may have made to your bugsauce theme.

= 0.6.8 =
This update prevents your purchased themes from being publically accessible.

= 0.5 =
This is the first public release of Vimeography.