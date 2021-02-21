<?php

/**
 * Plugin main file.
 *
 * @wordpress-plugin
 * Plugin Name:       Kntnt Style Editor
 * Plugin URI:        https://github.com/Kntnt/kntnt-style-editor
 * GitHub Plugin URI: https://github.com/Kntnt/kntnt-style-editor
 * Description:       Creates a CSS-file that can be edited through the administration user interface.
 * Version:           1.0.4
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Requires PHP:      7.1
 */


namespace Kntnt\Style_Editor;

// Uncomment following line to debug this plugin.
// define( 'KNTNT_STYLE_EDITOR_DEBUG', true );

require 'autoload.php';

defined( 'WPINC' ) && new Plugin;