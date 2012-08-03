=== Vimeography ===
Contributors: iamdavekiss
Tags: vimeo, video, videos, gallery, vimeography, colorbox, media, player, playlist, showcase, skins, themes, video gallery
Requires at least: 3.3
Tested up to: 3.4.1
Stable tag: 0.6.9.2
License: MIT

The easiest way to create beautiful Vimeo galleries on your Wordpress blog.

== Description ==

Vimeography is a Wordpress plugin that allows you to create beautiful, custom video galleries in 30 seconds, tops!

A quick overview:
[http://vimeo.com/44555634](http://vimeo.com/44555634 "http://vimeo.com/44555634")

For more information, check out [vimeography.com](http://vimeography.com/ "vimeography.com")

Some amazing features:

* Automatically add videos uploaded to a Vimeo user account, channel, album or group
* Easily insert galleries on a page, post or template with the gallery helper or shortcode
* Set a featured video to appear as the first video in your gallery
* Change your gallery's appearance with custom themes
* Built-in caching for quick page loads
* Create unlimited galleries

Make your gallery stand out with our custom themes!
[http://vimeography.com/themes](http://vimeography.com/themes "vimeography.com/themes")

For the latest updates, follow us!
[http://twitter.com/vimeography](http://twitter.com/vimeography "twitter.com/vimeography")

== Installation ==

1. Upload `vimeography.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Help! My theme doesn't look right! =

Okay, deep breath. More than likely, it is another plugin causing this issue. See if you can pinpoint which one by disabling your plugins, one by one, and really determining if you need it. If that task sounds daunting, try disabling plugins that are used for photo galleries, minifying scripts, widgets, or otherwise alter your blog's appearance.

= Why don’t you support YouTub/MetaHall/Flacker/PreschoolHumor? =

Like many other video professionals, we believe that Vimeo is a beautiful website complete with clean design, a supportive community and a straightforward API. This makes Vimeo a great choice for professional looking portfolios. Yes, there are other crummier sites that may also do the job, but that’s like forcing down chicken nuggets for dinner when you could be having baked scallops and a caprese appetizer. Vimeo only; enough said.

= How do I add my Vimeography gallery to a post or page? =

Easy! All you have to do is type `[vimeography id="#"]`, where `#` is replaced by the ID number of your gallery.

= Where do I find the ID number of my Vimeography gallery? =

Each gallery’s ID number is located next to the gallery’s title in the first column on the edit galleries page.

= Can I override my Vimeography gallery settings in the shortcode? =

Sure thing! You can define all of the properties found in the admin panel right in your shortcode as well. Try using one, any, or all of the following parameters:
`[vimeography id="3" theme="thumbs" featured="28380190" source="http://vimeo.com/channels/staffpicks" limit="60" cache="3600"]`

= Can I add the Vimeography gallery to my theme’s sidebar/footer/header etc.? =

Yes, but you’ll need some PHP knowledge to do it! Open the file you want to add the gallery to, and type `<?php do_shortcode('[vimeography id="#"]'); ?>`, where `#` is replaced by the ID number of your gallery.

= Can I change the colors/layout of my Vimeography theme? =

Not yet! All themes are set in stone, but we do have plans to add a custom css editor.

= What features do you have planned in future versions of Vimeography? =

- Vimeography Pro
- Theme Customization (colors, width, thumbnail size)
- Custom CSS Editor
- Define width and height of container in shortcode

== Screenshots ==

1. Create a gallery in 2 easy steps.
2. Preview your gallery and customize its appearance.
3. Manage your galleries with a simple interface.
4. Get new styles by installing gallery themes.

== Changelog ==
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
= 0.6.8 =
This update prevents your purchased themes from being publically accessible.

= 0.5 =
This is the first public release of Vimeography.