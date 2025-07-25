# Kntnt Style Editor

[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2+-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Requires PHP: 8.3+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Requires WordPress: 6.8+](https://img.shields.io/badge/WordPress-6.8+-blue.svg)](https://wordpress.org)

A WordPress plugin that provides a simple, high-performance way to add custom CSS globally across your site.

## Description

Kntnt Style Editor is a lightweight and performance-oriented plugin that allows you to add and edit custom CSS through a clean, modern editor in the WordPress admin area. The CSS is saved to a static file, ensuring it is loaded efficiently by browsers and can be cached by performance plugins without impacting server load.

This approach offers a significant advantage over the theme customizer's "Additional CSS" feature, which often inlines CSS directly into the HTML. By using a dedicated file, this plugin ensures your custom styles are theme-independent and optimized for speed.

### Key Features

* **Performance First:** Saves CSS to a static `.css` file, which is better for caching and performance than inline styles.
* **Automatic Minification:** CSS is automatically minified when saved to the static file while preserving original formatting in the editor.
* **Customizable Processing:** Use the `kntnt-style-editor-minimize` filter to customize how CSS is processed before saving to file.
* **Theme Independent:** Your custom styles remain active even if you switch themes.
* **Modern Editor:** Utilizes the powerful, built-in WordPress code editor (CodeMirror) for a great editing experience with syntax highlighting.
* **Developer Friendly:** Includes WordPress hooks for cache-clearing integrations and CSS processing customization.
* **Integration Ready:** Implements the `css_class_manager_filtered_class_names` filter, automatically making your custom CSS classes available to plugins like [CSS Class Manager](https://wordpress.org/plugins/css-class-manager/).
* **Clean and Secure:** Built with modern PHP 8.3, a clean architecture, and follows WordPress security best practices.
* **Self-Updating:** The plugin automatically checks for new versions on GitHub. When a new release is available, you can update directly from the WordPress admin panel, ensuring you always have the latest features and fixes.

## Installation

1. [Download the latest release ZIP file](https://github.com/Kntnt/kntnt-style-editor/releases/latest/download/kntnt-style-editor.zip).
2. In your WordPress admin panel, go to **Plugins → Add New**.
3. Click **Upload Plugin** and select the downloaded ZIP file.
4. Activate the plugin.

## Usage

1. Navigate to **Appearance → Style Editor** in your WordPress admin panel.
2. Add your custom CSS code into the editor.
3. Click **Save CSS**.

Your styles will be saved to a file located in `wp-content/uploads/kntnt-style-editor/` and automatically enqueued on the frontend of your site. The CSS in the database editor remains unchanged, while the static file is automatically minified for optimal performance.

### Integration with CSS Class Manager

[CSS Class Manager](https://wordpress.org/plugins/css-class-manager/) is a plugin that enhances the WordPress block editor by providing an advanced autocomplete control for adding CSS classes to blocks. It simplifies the process of applying multiple utility classes and helps organize your styling workflow.

This plugin automatically makes your custom CSS classes available to CSS Class Manager. To specify which classes should appear in the autocomplete list, add a special `@class-manager` tag inside any CSS comment block (`/* ... */`).

**Markup:**
`@class-manager class-name | An optional description for the class`

**Example:**

```css
/* @class-manager flex-row | Flex row with standard gap. */
.flex-row {
  display: flex;
  gap: 1rem;
}

/* @class-manager warning-box | Prominent box for warnings. */
.warning-box {
  border: 1px solid red;
  padding: 1rem;
  background-color: #fee;
}
```

In this example, `flex-row` and `warning-box` will be registered with CSS Class Manager.

### Developer Hooks

#### Action: `kntnt-style-editor-saved`

This action is triggered immediately after the CSS has been successfully saved to the database and the file. Caching plugins can use this hook to purge their caches.

**Example:**

```php
add_action('kntnt-style-editor-saved', function(string $css_content) {
    // Check if a caching function exists and call it.
    if (function_exists('my_cache_plugin_clear_cache')) {
        my_cache_plugin_clear_cache();
    }
});
```

#### Filter: `kntnt-style-editor-minimize`

This filter allows you to customize how CSS is processed before being saved to the static file. The original CSS in the editor remains unchanged.

**Parameters:**

- `$css` (string) - The original CSS content from the editor

**Returns:**

- (string) - The processed CSS content to be saved to the file

**Examples:**

```php
// Disable minification completely
add_filter('kntnt-style-editor-minimize', function($css) {
    return $css; // Return unchanged
});

// Use a custom minification library
add_filter('kntnt-style-editor-minimize', function($css) {
    if (class_exists('CustomMinifier')) {
        return CustomMinifier::minify($css);
    }
    return $css;
});

// Custom processing before minification
add_filter('kntnt-style-editor-minimize', function($css) {
    // Remove custom comments
    $css = str_replace('/* DEBUG */', '', $css);
    
    // Apply built-in minification
    return \Kntnt\Style_Editor\Editor::minifier($css);
});
```

**Default Behavior:**
If no filter is applied, the plugin automatically minifies CSS using its built-in minifier that removes comments, unnecessary whitespace, and optimizes formatting for smaller file sizes.

## Frequently Asked Questions

**Why use this plugin instead of the Customizer's "Additional CSS"?**

1. **Performance:** This plugin saves CSS to a static file, which can be cached by browsers and performance plugins. The Customizer often adds CSS inline, which can slow down page rendering and is harder to cache.
2. **Theme Independence:** Styles added with this plugin are not tied to your theme. They will persist even if you change themes, saving you from migrating your custom CSS.

**How does the plugin work internally?**

When you save, the CSS is written to the `wp_options` table in the database and simultaneously saved to a static file in your `wp-content/uploads` directory. The frontend of your site loads the static file, ensuring no database queries are needed to serve the styles. The editor loads its content from the database option, while the static file contains minified CSS for optimal performance.

**How can I get help or report a bug?**

Please visit the plugin's [issue tracker on GitHub](https://github.com/kntnt/kntnt-style-editor/issues) to ask questions, report bugs, or view existing discussions.

**How can I contribute?**

Contributions are welcome! Please feel free to fork the repository and submit a pull request on GitHub.

## Changelog

### 2.1.2

* **Fix:** Use Plugin URI as GitHub URI.

### 2.1.1

* **Fix:** Bumped version number of the plugin heading.

### 2.1.0

* **Feature:** Added a built-in, automatic update mechanism. The plugin now checks for new releases on GitHub and provides update notifications directly in the WordPress admin panel.

### 2.0.0

* **Major Refactor:** Complete rewrite of the plugin with a modern, object-oriented architecture inspired by `kntnt-popup`.
* **Enhancement:** Now requires PHP 8.3 and WordPress 6.8.
* **Feature:** Added automatic CSS minification for the static file while preserving original formatting in the editor.
* **Feature:** Added `kntnt-style-editor-minimize` filter for customizable CSS processing.
* **Enhancement:** Made the built-in CSS minifier publicly accessible via `\Kntnt\Style_Editor\Editor::minifier()`.
* **Feature:** Adds a `do_action('kntnt-style-editor-saved', $css)` hook for cache-clearing and other integrations.
* **Feature:** Implements the `css_class_manager_filtered_class_names` filter to automatically provide classes to compatible plugins.
* **Enhancement:** Improved CSS parser to use a specific `@class-manager` tag for exposing classes to the CSS Class Manager plugin, making the integration more robust and explicit.
* **Enhancement:** Implements `WP_Filesystem` API for secure file operations.
* **Enhancement:** Uses modern JavaScript (ES12) and removes jQuery dependency for admin scripts.
* **Improvement:** Cleaner admin UI and improved code structure for better maintainability and testability.
* **Fix:** CSS file versioning is now based on file modification time for automatic cache busting.
* **Doc:** Updated README.

### 1.0.4

* Delayed enqueuing of the CSS with priority 9999.

### 1.0.3

* Fixed regression bug (style not saved as file).

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
