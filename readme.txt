=== Smallerik File Browser ===
Contributors: smallerik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Y9YQWWCLEC67G
Tags: file browser repository personal-area
Requires at least: 3.0
Tested up to: 3.8.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin enables (authorized) users to embed a file repository inside a standard Wordpress page or post. File repositories can be user-dependent.

== Description ==

The Smallerik File Browser plugin allows you to transform a normal WordPress page or post into a simple but fully functional file browser.
Files and folders within specified repositories can be browsed and, depending on user-based access rights, managed according to customizable access levels.

Using this plugin only requires a page/post shortcode (see Installation section) named [wpfib]. However, there are a lot of features to explore.
For example, if you want the same post or page to display a different repository for each user viewing it, you can add a command option specifying that the repository should be user-dependent.
You achieve this by simply writing: [wpfib repo='USERBOUND'] inside the post or page. This will create a separate folder for each user from the same post or page.

As an example, this arrangement is useful if you intend to provide file access in a school website, where pupils can access their own repository where to upload homework and download corrections or other personal files.
The teacher could access all areas by setting a repository to the top folder (above all personal areas).
In a different context, a project team leader could share files with people involved in the project, perhaps even giving different access levels to each team member.

You can easily customize the plugin behavior or appearance by modifying how to display files and folders or the overall look & feel of the repository.

Most parameters have an equivalent override option, so that you can modify one or more parameters' values on a specific repository. 
For example you can have a 'static' repository on one page and a user-dependent repository on another one.

== Installation ==

The plugin is installed and activated like any other Wordpress plugin. Once this is done, all you need to do to see it in action is create a new post or page (or edit an existing one) and write inside the following command (technically, it is a shortcode):

[wpfib]

The post/page will now display the contents of the top level repository folder, as specified in the General options of the plugin.
If you have left the default settings after activation, all files and folders of the repository will be stored inside a folder named wp-file-browser-top under your Wordpress installation.
Smallerik File Browser will create this folder when first accessing the repository.
Please notice that the repository will only be displayed when the article is viewed directly and not if this is displayed in a list of articles (such as when using a blog mode).

== Screenshots ==

1. Using a shortcode to display a file repository

== Changelog ==

= Version 1.1 =

* Fixed call by reference error on new versions of PHP
* Better display of action icons that appeared very small on certain templates

= Version 1.0 =

* Initial revision

== Upgrade Notice ==

= Version 1.1 =
This version fixes a fatal error Call By Reference on PHP from version 5.4

