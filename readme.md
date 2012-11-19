# Limit Upload
Limit the number of uploads on posts

## Description
Limit the number of uploads from images/attachments on posts/pages and custom post types

## Installation
### Requirements
 * WordPress (also Multisite) version 3.3 and later (tested at 3.4)
 * PHP 5.3

### Installation
1. Unpack the download-package
1. Upload the file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Ready

### Usage
Current have the plugin no settings in the backend of WordPress - a problem of time from my site.

But you can simply change the values in the main file `limit-image-upload.php`. See in the method `start_limit_image_upload()` and change the values on the var `$args` for your requirements.

## Screenshots
 * [Message for to much uploads in WordPress 3.4](https://github.com/bueltge/Limit-Upload/blob/master/screenshot-1.png)
 * [Reduce the tabs on media uploader in WordPress 3.4](https://github.com/bueltge/Limit-Upload/blob/master/screenshot-2.png)

## Other Notes
### Licence
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

### Translations
The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the .pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Linux, Mac OS X, Windows) or the fine plugin [Codestyling Localization](http://wordpress.org/extend/plugins/codestyling-localization/) for WordPress.

### Contact & Feedback
The plugin is designed and developed by me ([Frank Bültge](http://bueltge.de))

Please let me know if you like the plugin or you hate it or whatever ... Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there. So please be patient with me and let me know of typos or grammatical farts. Thanks
