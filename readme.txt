=== Vimeography: Vimeo Video Gallery WordPress Plugin ===
Contributors: iamdavekiss, videogallery
Tags: video gallery, gallery, video, vimeo, vimeo gallery
Requires at least: 3.3
Tested up to: 5.2
Stable tag: 2.0.10
License: GPL-3.0

The easiest way to create beautiful Vimeo video galleries on your WordPress site.

== Description ==

#### 80+ 5-star Reviews! Vimeography is the Best and Most Powerful Video Gallery Plugin for Membership and Course Sites. ★★★★★

http://vimeo.com/44555634

More than 10,000 websites use Vimeography to show their Vimeo videos on their own WordPress website.

Vimeography is a free WordPress plugin that allows you to create your own Netflix style website with beautiful, custom Vimeo video galleries in 30 seconds, tops!

Our video gallery plugin is used to power **over 10,000 video membership, course, and portfolio websites.**

For more information, check out [vimeography.com](https://vimeography.com/?utm_source=wpdotorg&utm_medium=description "vimeography.com")

Some amazing features:

* Automatically add videos uploaded to a Vimeo user account, channel, or album to your video gallery
* Easily insert video galleries on a page, post or template with one click
* Set a featured video to appear as the first video in your Vimeo video gallery
* Change your Vimeo video gallery's appearance with custom themes
* Tweak your video gallery theme's look with the appearance editor
* Control the video gallery width using pixels or percentages
* Built-in caching for quick page loads

> #### Vimeography Themes
> Make your gallery stand out with our custom video gallery themes! All themes come with one year of updates and support.
>
> Check our all of our video gallery designs at [https://vimeography.com/themes](https://vimeography.com/themes?utm_source=wpdotorg&utm_medium=description "vimeography.com/themes")

#### Want more features?

[Check out Vimeography Pro](https://vimeography.com/pro?utm_source=wpdotorg&utm_medium=repo "Vimeography Pro") for additional features:

* Create searchable galleries
* Create shareable links to videos in your collection
* Show unlimited videos
* Enable download links for your videos
* Show hidden videos, create auto-playing playlists, custom sorting and more!
* Supports Vimeo video interaction tools (end screens, cards, email capture)

== Installation ==

1. Upload `vimeography.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= How can I show hidden Vimeo videos? =

If you would like to show videos that are hidden from Vimeo, you will need [Vimeography Pro.](https://vimeography.com/add-ons/vimeography-pro/?utm_source=wpdotorg&utm_medium=faq)

The way that it works is you mark your videos as hidden from Vimeo and then place them in a Public album. You then use that album URL as the source for your video gallery. The videos won't appear anywhere on Vimeo, but will still be accessible by the Vimeography WordPress plugin.

= Help! My video gallery doesn't look right! =

First of all, don't worry! I promise you that we can get it looking right. This can be caused by a multitude of things, so try the following (in the order of appearance):

* Check your browser's javascript console to see if there are any errors that may be causing this issue.
* Try surrounding your Vimeography shortcode in `[raw][/raw]` tags.
* Make sure that your WordPress theme is not including multiple versions of jQuery and is using the latest version.
* Make sure that your theme is calling the `wp_footer()` function so that all of the Vimeography stylesheets are printed.
* Try disabling other plugins that are used for photo galleries, minifying scripts, widgets, or otherwise alter your site's appearance, one by one, and really determining if you need it.

= I'm getting an error: This Vimeography gallery does not have a theme assigned to it. =

Make sure that you have the latest version of the Vimeography theme plugin that you are using installed and that it is activated. Then, go to the gallery editor and select that theme under the Appearance tab. If you're still having problems, reach out on the Vimeography contact page.

= I'm having trouble installing a video gallery theme. =

Vimeography themes are treated as WordPress plugins. Make sure you're installing the Vimeography theme on the "Plugins" page in your WordPress dashboard.

= How do I get the latest updates for my video gallery themes? =

If you purchased your video gallery theme at vimeography.com, you should have received an email receipt containing an license key for your theme. Enter that license code on the "Manage Licenses" page of the Vimeography plugin. Once done, you will be subscribed to theme updates which will automatically be delivered to your WordPress installation, just like any other plugin.

= How do I add my Vimeography video gallery to a post or page? =

Easy! All you have to do is type `[vimeography id="#"]`, where `#` is replaced by the ID number of your Vimeo video gallery.

= Where do I find the ID number of my Vimeography video gallery? =

Each video gallery’s ID number is located next to the video gallery’s title in the first column on the edit galleries page.

= Can I override my Vimeography gallery settings in the shortcode? =

Sure thing! You can define all of the properties found in the admin panel right in your shortcode as well. Try using one, any, or all of the following parameters:
`[vimeography id="3" theme="thumbs" featured="http://vimeo.com/28380190" source="http://vimeo.com/channels/staffpicks" limit="60" cache="3600" width="600px"]`

= Can I add the Vimeography video gallery to my theme’s sidebar/footer/header etc.? =

Yes, but you’ll need some PHP knowledge to do it! Open the file you want to add the video gallery to, and type `<?php do_shortcode('[vimeography id="#"]'); ?>`, where `#` is replaced by the ID number of your gallery.

= Can I change the look of my Vimeography theme? =

Heck yeah! Use the appearance editor to change your video gallery theme's style so that it matches your site perfectly.

= Can I override my Vimeography theme template with some custom code? =

Sure, why not. Here's an example:

`
<?php

  function my_custom_harvestone_thumbnail() {
    ob_start();
  ?>
    <script type="text/x-template" id="vimeography-harvestone-thumbnail">
      <figure class="swiper-slide vimeography-thumbnail">
        <router-link class="vimeography-link" :to="this.query" exact exact-active-class="vimeography-link-active">
          <img class="vimeography-thumbnail-img" :src="thumbnailUrl" :title="video.name" />
        </router-link>
      </figure>
    </script>
  <?php
    echo ob_get_clean();
  }

  add_action('admin_head', 'my_custom_harvestone_thumbnail');
  add_action('wp_head', 'my_custom_harvestone_thumbnail');
`

Vimeography will load your template for the defined module instead of the default one for your theme.

== Screenshots ==

1. Create a Vimeo video gallery in 30 seconds, tops!
2. User-friendly tutorials to get you up and running.
3. Perfect gallery themes for every application.
4. Gain additional features with Vimeography PRO.

== Changelog ==
= 2.0.10 =
* [Update] Mark compatibility with WordPress 5.2
* [Update] Allow Vimeo Showcase links to be used as the source for your gallery

= 2.0.9 =
* [Update] Mark compatibility with WordPress 5.1
* [Tweak] Improve error message when site cannot access video collection due to privacy
* [Fix] Corrected script dependencies when using Gutenberg editor

= 2.0.8 =
* [Fix] Ignore rate limit response header until Vimeo settles on a format.

= 2.0.7 =
* [Fix] Hotfix for parsing Vimeo's new rate limit header format

= 2.0.6 =
* [Fix] Hotfix for incorrect version during request.

= 2.0.5 =
* [New] You can now programatically capture exceptions with the vimeography.exception.report action (https://github.com/davekiss/vimeography/commit/06fc7bd69f5681b84d6dc1bdb5c4982251771240)
* [Fix] Improved compatibility with older versions of Safari
* [Tweak] Return user account data in API requests to Vimeo
* [Tweak] Upgrade Vimeo.js version to 2.6

= 2.0.4 =
* [Tweak] Allow validation of album urls in /manage/albums/xxx format (https://github.com/davekiss/vimeography/commit/b38633554e4da532dde9ea86f85e3c4b1ac606d4)
* [Tweak] Autoprefix Harvestone styles using postcss-cssnext (https://github.com/davekiss/vimeography/commit/b37d713391765ca82c9a2f79a6afc577d395f8e6)
* [Fix] Correct padding applied to player on loadVideo (https://github.com/davekiss/vimeography/commit/56c2eea7ba9911579db14f547a88a932fb92ed5a)

= 2.0.3 =
* [New] Vimeography is now compatible with Gutenberg! Try inserting your gallery as a block.
* [New] Vimeography CURL request default parameters can now be modified using the vimeography.request.curl_defaults filter.
* [Fix] We've added a URLSearchParams polyfill to Harvestone for Edge and IE support

= 2.0.2 =
* [Fix] Only set custom CSS stylesheet dependencies if they actually exist (900edad)
* [Fix] Disable strong check against namespace bool value during custom appearance changes. (ea2c592)

= 2.0.1 =
* [New] Added Video ID classnames to each thumbnail in the Harvestone theme. (https://github.com/davekiss/vimeography/commit/d7aedee90a25f938d047fe208766371a6a148fbd)
* [Fix] Ensure Vimeo receives the embeddable parameter during the request by default. (https://github.com/davekiss/vimeography/commit/6145d466147447357a67675464b0a4a8a9ee0914)
* [Fix] Redirect to Step 1 on the Welcome screen after plugin activation (https://github.com/davekiss/vimeography/commit/87c66619672d3d90084f1a0dd8c30f4d7257e2f4)
* [Fix] Pass all shortcode attributes to the `vimeography.gallery.settings` filter. (https://github.com/davekiss/vimeography/commit/94562623d1f317d89fe4a0b6359d11288e62eb42)

= 2.0.0 =

* [New] Introducing Harvestone, our new default Vimeography theme!
* [New] Vimeography themes are now rendered with Vue.js instead of Mustache.php
* [New] Vimeography theme development is now supported by the Webpack build process.
* [New] Complete rewrite of the Vimeography request and rendering engine for better control flow.
* [New] Added a brand new tutorial onboarding process when the plugin is first installed.
* [New] You can now skip Vimeography's cache entirely by adding ?vimeography_nocache=1 to the url.
* [New] You can now set the galleries to show per page on the Gallery List page in the admin.
* [New] Add styles for any Vimeography errors that occur during render.
* [New] Rewrote the Vimeography cache class, introducing several new filters
* [New] Added the `vimeography.gallery.wrapper_class` filter so you can apply custom CSS classes to your gallery wrappers.
* [New] You can override all of Vimeography's component templates with your own custom layout definition. (See the FAQ!)
* [New] You can now insert a Vimeography element within WPBakery Page Builder (formally Visual Composer)
* [Tweak] Replaced the Vimeography icon in the menu with our new logo.
* [Tweak] Swapped out the Vimeography Pro demo video with a new shorter video.
* [Tweak] Vimeography gallery data is now preserved after the plugin is uninstalled.
* [Tweak] Updated the plugin screenshots on the plugin homepage.
* [Tweak] Harvestone is now the default theme gallery used during new galleries.
* [Tweak] Vimeography now observes gallery visibility to allow hiding galleries on page load.
* [Tweak] Video limit is now enforced after cache is set rather than before
* [Tweak] Rewrote copy in several locations

= 1.5.3 =
* [Fix] Fixed the Endpoint 1/videos is not valid error

= 1.5.1 =
* [New] Added a request field filter to prevent Vimeo rate limit errors.
* [New] Gallery settings are now run through the `vimeography/gallery-settings` filter before displaying a gallery. This allows for modifying gallery settings programatically while rendering a gallery.
* [Tweak] Admin notices are now hidden on the new gallery page.
* [Tweak] Added a required PHP version header of 5.3 (note: 5.3 has been the required PHP version for quite some time, this is not a new requirement)
* [Fix] Added some code to auto-handle cURL proxy response headers.
* [Fix] Fixed an issue where license keys could sometimes not be removed.
* [Fix] Ensure capabilities are filtered on admin page render.
* [Fix] Update the Vimeography Pro installation instructions to avoid an error.

= 1.5 =
* [New] Refreshed the Vimeography video gallery creation process
* [New] You can now specify which gallery theme you would like to use when creating a new video gallery!
* [New] Added `tags` as a valid Vimeo video gallery collection
* [New] Added `vimeography.capabilities.menu` filter to allow adjusting permissions to manage Vimeography
* [Fix] Corrected an issue where duplicating a video gallery could sometimes create multiple copies
* [Tweak] Removed plugin deactivation behavior when performing the core plugin upgrade
* [Tweak] Refreshed plugin update notification row styles

= 1.4.1 =
* [Tweak] Updated 403 error message to include more information

= 1.4 =
* [New] Thanks to improvements in the Vimeo API, Vimeography is now faster than ever!
* [Tweak] Updated the Vimeo API authentication method
* [Tweak] Implemented response field filters for quicker API response times

= 1.3.3 =
* [Fix] Ensure multisite installations each use their own cache files.
* [Fix] Add better error messages for Vimeo connection errors.

= 1.3.2 =
* [New] Vimeography now supports WordPress multisite installations!
* [New] You can now search for a gallery based on its gallery ID number
* [Fix] Added a check to make sure any license keys are up to date with accurate information.
* [Fix] If you enter a license key on the licenses page and it fails, you'll get more details as to why.
* [Tweak] Ensure some remote calls are made over https
* [Tweak] Changed the 'View themes' button to read 'Switch themes'
* [Tweak] Updated the EDD_SL_Plugins_Updater class to latest version.

= 1.3.1 =
* [New] Added a Welcome screen that is shown when the plugin is first activated.
* [New] Finally gave the Help page the love it deserved. Now, it is helpful!
* [New] Watch Vimeography tutorial videos right on the Help page. Pluginception!

= 1.3 =
* [New] The gallery appearance editor received a fresh coat of paint
* [New] Added a "vimeography/cache-videos" filter that runs just before videos are saved
* [New] Added a localized JS variable containing gallery data to be used by the theme
* [New] Rewrote the Utilities and Pagination javascript classes for supporting multiple galleries per page
* [Fix] Manually wrap the default Bugsauce theme in a fitvids container to prevent flash during load
* [Fix] Update the appearance editor to use VeinJS instead of jQuery CSS manipulation
* [Fix] Add a check to ensure the cURL PHP library is installed before requesting videos
* [Fix] Updated the Froogaloop library to support HSTS (h/t Brad Dougherty)
* [Fix] Bridge thumbnails sometimes were hidden on smaller devices. Begone, bug!
* [Tweak] Take a different approach on loading admin pages and scripts
* [Tweak] Rename the "admin/view" folder to "admin/controllers"

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
= 1.5.1 = 
IMPORTANT: Before performing this update, please be sure you have installed the latest versions of any Vimeography themes that you have purchased.

= 1.5 =
After this update, Vimeography will no longer disable any add-on plugins while upgrades are being performed. Please make sure your add-on plugins are updated to the latest version, or, are deactivated while updating Vimeography.

= 1.4 =
This update removes unnecessary response fields from the Vimeo API. If you've customized your Vimeography theme to use response fields other than what now ships with Vimeography, make sure to add them using the `vimeography.request.fields` WordPress filter.

= 1.3.2 =
There is also an update available for Vimeography Pro. If you have Vimeography Pro installed, make sure to update it as well. The available version is marked as 1.0.1

= 1.3 =
There is also an update available for Vimeography Pro. If you have Vimeography Pro installed, make sure to update it as well. The available version is marked as 1.0

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