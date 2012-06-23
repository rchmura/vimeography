=== Vimeography ===
Contributors: iamdavekiss
Tags: vimeo, videos, gallery
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 0.5.3
License: MIT

The easiest way to create beautiful Vimeo galleries on your Wordpress blog.

== Description ==

Vimeography is a Wordpress plugin that allows you to create beautiful, custom video galleries in 30 seconds, tops! 

For more information, check out [vimeography.com](http://vimeography.com/ "vimeography.com")

Some amazing features:

* Automatically add videos uploaded to a Vimeo user account, channel, album or group
* Easily insert galleries on a page, post or template with the gallery helper or shortcode
* Set a featured video to appear as the first video in your gallery
* Change your gallery's appearance with custom themes
* Built-in caching for quick page loads
* Create unlimited galleries

== Installation ==

1. Upload `vimeography.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why don’t you support YouTub/MetaHall/Flacker/PreschoolHumor? =

Like many other video professionals, we believe that Vimeo is a beautiful website complete with clean design, a supportive community and a straightforward API. This makes Vimeo a great choice for professional looking portfolios. Yes, there are other crummier sites that may also do the job, but that’s like forcing down chicken nuggets for dinner when you could be having baked scallops and a caprese appetizer. Vimeo only; enough said.

= How do I add my Vimeography gallery to a post or page? =

Easy! All you have to do is type `[vimeography id="#"]`, where `#` is replaced by the ID number of your gallery.

= Where do I find the ID number of my Vimeography gallery? =

Each gallery’s ID number is located next to the gallery’s title in the first column on the edit galleries page.

= Can I override my Vimeography gallery settings in the shortcode? =

Sure thing! You can define all of the properties found in the admin panel right in your shortcode as well. Try using one, any, or all of the following parameters:
`[vimeography id="3" theme="thumbs" featured="28380190" source="channel" limit="60" named="staffpicks" cache="3600"]`

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

1. Create a gallery in 3 easy steps.
2. Preview your gallery and customize its appearance.
3. Manage your galleries with a simple interface.
4. Get new styles by installing gallery themes.

== Changelog ==
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

= 0.5 =
This is the first public release of Vimeography.