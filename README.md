# Kntnt Style Editor

WordPress plugin that creates a CSS-file that can be edited through the administration user interface.

## Description

This lightweight plugin uses WordPress built-in code editor to allow you edit CSS. The CSS is saved to a file, which is globally enqueued to be shown at the frontend.

## Installation

[Download latest version](https://github.com/Kntnt/kntnt-style-editor/releases/latest) to your computer, and [upload it to WordPress](https://wordpress.org/support/article/managing-plugins/#manual-upload-via-wordpress-admin).

You can also install it with [*GitHub Updater*](https://github.com/afragen/github-updater), which gives you the additional benefit of keeping the plugin up to date the usual way. Please see its [wiki](https://github.com/afragen/github-updater/wiki) for more information.

## Frequently Asked Questions

### Where is the editor?

Look for *Kntnt Style Editor* in the *Appearance* menu.

### How does this plugin work?

When the editor content is saved, it is saved as an option in the WordPress database and exported to the file. When the editor is loading its content, the content is loaded from the database and not the file. Thus, the CSS editor is not editing the file directly. Therefore, it is possible to delete the file; it will be re-created when saving the CSS next time.

### Why is this plugin needed?

Since WordPress 4.7, you can add custom CSS in the customizer. But that has two consequences:

1. Additional CSS added through a theme's customizer is only available for that particular theme. In contrast, additional CSS added through this plugin is available independent of the theme.
1. Additional CSS added through a theme's customizer is usually inlined into the HTML-file and not saved to a separate CSS-file. Thus, the added CSS is not subject to WordPress usual processing of CSS in files. Consequently, it will not be minified and combined by a cache plugin that can't extract inline CSS. That could hurt your site's performance. In contrast, additional CSS added through this plugin is stored as a file that WordPress will process as any other CSS file.

### Does this plugin affect performance?

No. Since the CSS is stored in a static file, there is no extra database queries or anything else that takes time. The only time data is retrieved from or saved to the database is when you work in the editor.

### How do I know if there is a new version?

This plugin is currently [hosted on GitHub](https://github.com/kntnt/kntnt-style-editor); one way would be to ["watch" the repository](https://docs.github.com/en/github/managing-subscriptions-and-notifications-on-github/about-notifications#notifications-and-subscriptions).

If you prefer WordPress to nag you about an update and let you update from within its administrative interface (i.e. the usual way), you must [download *GitHub Updater*](https://github.com/afragen/github-updater/releases/latest) to your computer and [upload it to WordPress and activate it](https://github.com/afragen/github-updater/wiki/Installation#upload). Please see its [wiki](https://github.com/afragen/github-updater/wiki) for more information.

### How can I get help?

If you have questions about the plugin and cannot find an answer here, start by looking at [issues](https://github.com/kntnt/kntnt-style-editor/issues) and [pull requests](https://github.com/kntnt/kntnt-style-editor/pulls). If you still cannot find the answer, please ask in the plugin's [issue tracker](https://github.com/kntnt/kntnt-style-editor/issues) at Github.

### How can I report a bug?

If you have found a potential bug, please report it on the plugin's [issue tracker](https://github.com/kntnt/kntnt-style-editor/issues) at Github.

### How can I contribute?

Contributions to the code or documentation are much appreciated.

If you are unfamiliar with Git, please date it as a new issue on the plugin's [issue tracker](https://github.com/kntnt/kntnt-style-editor/issues) at Github.

If you are familiar with Git, please make a pull request.

## Changelog

### 1.0.2

* Improved how the editor is shown. 
* Minor updates to the README file.
* Added install.php to make sure the CSS isn't auto-loaded from the wp_option table.
* Added uninstall.php to delete this plugin option and created file.

### 1.0.1

* Minor refactoring of code.
* Improved documentation.

### 1.0.0

* The initial release.
* A fully functional plugin.