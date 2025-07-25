<?php

declare( strict_types = 1 );

namespace Kntnt\Style_Editor;

/**
 * Manages the enqueueing of CSS and JavaScript assets.
 *
 * Handles loading the custom CSS file on the frontend, adding styles to the block editor,
 * and enqueuing admin assets for the style editor page.
 *
 * @package Kntnt\Style_Editor
 * @since   2.0.0
 */
final class Assets {

	/**
	 * Cached information about the CSS file.
	 *
	 * Contains existence status, file paths, URLs and version information
	 * to avoid repeated file system checks.
	 *
	 * @var array{exists: bool, path: string, url: string, version: int}|null
	 */
	private static ?array $css_file_info = null;

	/**
	 * Enqueues the custom stylesheet on the website's frontend.
	 *
	 * Only enqueues if the CSS file exists and has content. Uses file modification
	 * time as version for cache busting.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function enqueue_frontend_style(): void {

		$file_info = $this->get_css_file_info();

		// Only enqueue if file exists and has content
		if ( $file_info['exists'] ) {
			wp_enqueue_style( Plugin::get_slug() . '-custom', $file_info['url'], [], (string) $file_info['version'] );
		}

	}

	/**
	 * Adds the custom stylesheet to the block editor's preview iframe.
	 *
	 * This ensures that custom styles are visible when editing posts/pages
	 * in the block editor, providing a WYSIWYG experience.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function add_custom_css_to_block_editor(): void {

		$file_info = $this->get_css_file_info();

		// Add editor styles if CSS file exists
		if ( $file_info['exists'] ) {
			add_editor_style( $file_info['url'] );
		}

	}

	/**
	 * Enqueues assets specifically for the admin editor page.
	 *
	 * Loads the CodeMirror CSS editor, admin JavaScript for editor initialization,
	 * and admin-specific CSS styling. Only loads on the plugin's admin page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {

		// Only load assets on this plugin's admin page
		$expected_hook = 'appearance_page_' . Plugin::get_slug();
		if ( $expected_hook !== $hook_suffix ) {
			return;
		}

		// Enqueue WordPress built-in code editor (CodeMirror)
		$editor_settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

		// Enqueue admin JavaScript for editor initialization
		wp_enqueue_script( Plugin::get_slug() . '-admin-js', Plugin::get_plugin_url() . 'js/admin.js', [ 'wp-util' ], Plugin::get_version(), true );

		// Pass CodeMirror settings to JavaScript
		wp_localize_script( Plugin::get_slug() . '-admin-js', 'kntntEditorSettings', [ 'codeEditor' => $editor_settings ] );

		// Enqueue admin-specific CSS styling
		wp_enqueue_style( Plugin::get_slug() . '-admin-css', Plugin::get_plugin_url() . 'css/admin.css', [], Plugin::get_version() );
	}

	/**
	 * Gets cached CSS file information including existence, URL, and version.
	 *
	 * Caches the file information to avoid repeated file system checks during
	 * the same request. Information includes file existence, paths, and modification time.
	 *
	 * @return array{exists: bool, path: string, url: string, version: int} File information array.
	 * @since 2.0.0
	 */
	private function get_css_file_info(): array {

		// Return cached information if available
		if ( self::$css_file_info === null ) {

			// Get file paths from plugin configuration
			$css_file_path = Plugin::get_css_path();
			$css_file_url = Plugin::get_css_url();

			// Check if file exists and has content (filesize > 0)
			$exists = ! empty( $css_file_path ) && file_exists( $css_file_path ) && filesize( $css_file_path ) > 0;

			// Use file modification time as version for cache busting
			$version = $exists ? filemtime( $css_file_path ) : 0;

			// Cache the information for subsequent calls
			self::$css_file_info = [
				'exists' => $exists,
				'path' => $css_file_path,
				'url' => $css_file_url,
				'version' => $version,
			];
		}

		return self::$css_file_info;
	}

	/**
	 * Clears the cached file information.
	 *
	 * Called after file operations (like saving CSS) to ensure fresh data
	 * is loaded on the next file info request.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function clear_file_cache(): void {
		self::$css_file_info = null;
	}

}