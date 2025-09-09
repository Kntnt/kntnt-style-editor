<?php

declare( strict_types = 1 );

namespace Kntnt\Style_Editor;

/**
 * Main editor functionality for the CSS Style Editor.
 *
 * Handles the admin page registration, rendering, form processing, CSS sanitization,
 * and file saving operations. Also includes CSS minification functionality.
 *
 * @package Kntnt\Style_Editor
 * @since   2.0.0
 */
final class Editor {

	/**
	 * A simple function to minify a CSS string.
	 *
	 * Removes comments, unnecessary whitespace, and optimizes formatting
	 * for smaller file sizes while maintaining CSS functionality.
	 *
	 * @param string $css The CSS code to be minified.
	 *
	 * @return string The minified CSS code.
	 * @since 2.0.0
	 */
	public static function minifier( string $css ): string {

		// Remove all CSS comments (/* ... */) including multi-line comments
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

		// Remove line breaks and tabs for compact output
		$css = str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $css );

		// Remove spaces around CSS syntax characters for compactness
		$css = preg_replace( '/\s*([{}:;,])\s*/', '$1', $css );

		// Collapse multiple spaces into single spaces (needed for some CSS values)
		$css = preg_replace( '/\s+/', ' ', $css );

		// Remove unnecessary trailing semicolons before closing braces
		$css = str_replace( ';}', '}', $css );

		// Clean up any remaining whitespace at start/end
		return trim( $css );

	}

	/**
	 * Registers the editor page in the WordPress admin menu.
	 *
	 * Adds a submenu page under "Appearance" with the appropriate capability
	 * requirement and callback to render the editor interface.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function register_editor_page(): void {
		add_submenu_page( 'themes.php', __( 'Kntnt Style Editor', 'kntnt-style-editor' ), __( 'Style Editor', 'kntnt-style-editor' ), Plugin::get_capability(), Plugin::get_slug(), [ $this, 'render_editor_page' ] );
	}

	/**
	 * Renders the main editor page interface.
	 *
	 * Handles both GET requests (display form) and POST requests (process form submission).
	 * Includes capability checking and template variable preparation.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function render_editor_page(): void {

		// Verify user has permission to edit CSS
		if ( ! current_user_can( Plugin::get_capability() ) ) {
			wp_die( __( 'Unauthorized use.', 'kntnt-style-editor' ) );
		}

		// Handle form submission if this is a POST request
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$this->handle_save();
		}

		// Prepare template variables for the editor page
		$template_vars = [
			'page_slug' => Plugin::get_slug(),
			'css_content' => Plugin::get_option()['css'] ?? '',
		];

		// Load and render the editor template
		$this->load_template( 'editor-page.php', $template_vars );

	}

	/**
	 * Handles the CSS save operation from form submission.
	 *
	 * Processes the posted CSS content, sanitizes it, saves to database,
	 * saves to static file, and triggers appropriate admin notices.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	private function handle_save(): void {

		// Verify nonce for security
		check_admin_referer( Plugin::get_slug() . '_nonce' );

		// Get and process the submitted CSS content
		$css_content = $_POST['css_content'] ?? '';
		$sanitized_css = Plugin::get_instance()->sanitizer->sanitize( stripslashes( $css_content ) );

		// Save CSS to database (preserves original formatting)
		Plugin::set_option( [ 'css' => $sanitized_css ] );

		// Save CSS to static file (potentially minified)
		$file_saved = $this->save_css_to_file( $sanitized_css );

		if ( $file_saved ) {
			// Trigger action for cache clearing and other integrations
			do_action( 'kntnt-style-editor-saved', $sanitized_css );
			add_action( 'admin_notices', [ $this, 'render_success_notice' ] );
		}
		else {
			add_action( 'admin_notices', [ $this, 'render_error_notice' ] );
		}

	}

	/**
	 * Renders a success notice for successful CSS save operations.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function render_success_notice(): void {
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html__( 'CSS saved successfully.', 'kntnt-style-editor' ) );
	}

	/**
	 * Renders an error notice when CSS file save operation fails.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function render_error_notice(): void {
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html__( 'Failed to save CSS file. Please check file permissions.', 'kntnt-style-editor' ) );
	}

	/**
	 * Saves CSS content to a dedicated file in the uploads directory.
	 *
	 * Uses WordPress Filesystem API for secure file operations. Applies minification
	 * filter if available, otherwise uses built-in minifier. Creates directory
	 * structure if needed.
	 *
	 * @param string $css_content The CSS content to be saved.
	 *
	 * @return bool True on success, false on failure.
	 * @since 2.0.0
	 */
	private function save_css_to_file( string $css_content ): bool {

		global $wp_filesystem;

		// Initialize WordPress Filesystem API
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			error_log( 'Kntnt Style Editor: Failed to initialize WP_Filesystem' );
			return false;
		}

		// Get target directory and file paths
		$target_dir = Plugin::get_css_dir();
		$target_file = Plugin::get_css_path();

		// Ensure we have valid paths before proceeding
		if ( empty( $target_dir ) || empty( $target_file ) ) {
			error_log( 'Kntnt Style Editor: Upload directory not available' );
			return false;
		}

		// Create target directory if it doesn't exist
		if ( ! $wp_filesystem->is_dir( $target_dir ) ) {
			if ( ! $wp_filesystem->mkdir( $target_dir, FS_CHMOD_DIR ) ) {
				error_log( 'Kntnt Style Editor: Failed to create directory: ' . $target_dir );
				return false;
			}
		}

		// Verify directory is writable
		if ( ! $wp_filesystem->is_writable( $target_dir ) ) {
			error_log( 'Kntnt Style Editor: Directory not writable: ' . $target_dir );
			return false;
		}

		// Apply CSS processing (minification filter or built-in minifier)
		if ( has_filter( 'kntnt-style-editor-minimize' ) ) {
			// Use custom filter if available
			$css_content = apply_filters( 'kntnt-style-editor-minimize', $css_content );
		}
		else {
			// Use built-in minifier as default
			$css_content = self::minifier( $css_content );
		}

		// Write CSS content to file with proper permissions
		$result = $wp_filesystem->put_contents( $target_file, $css_content, FS_CHMOD_FILE );
		if ( ! $result ) {
			error_log( 'Kntnt Style Editor: Failed to write CSS file: ' . $target_file );
			return false;
		}

		// Clear cached file information to ensure fresh data on next request
		Assets::clear_file_cache();

		return true;

	}

	/**
	 * Loads and includes a template file with provided variables.
	 *
	 * Safely extracts variables into local scope and includes the template
	 * file if it exists. Uses EXTR_SKIP to prevent overwriting existing variables.
	 *
	 * @param string $template_file The template filename to load.
	 * @param array  $vars          Associative array of variables to extract for the template.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	private function load_template( string $template_file, array $vars = [] ): void {

		// Extract variables into local scope for template use
		extract( $vars, EXTR_SKIP );

		// Build full path to template file
		$template_path = Plugin::get_plugin_dir() . 'templates/' . $template_file;

		// Include template if it exists
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}

	/**
	 * Adds a link to the style editor in the WordPress admin bar.
	 *
	 * Only visible to users with the capability to edit theme options.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 *
	 * @return void
	 * @since 2.2.0
	 */
	public function add_admin_bar_link( \WP_Admin_Bar $wp_admin_bar ): void {

		// Ensure user has the correct permissions
		if ( ! current_user_can( Plugin::get_capability() ) ) {
			return;
		}

		// Add the admin bar node
		$wp_admin_bar->add_node( [
			'id' => Plugin::get_slug(),
			'title' => __( 'Style Editor', 'kntnt-style-editor' ),
			'href' => admin_url( 'themes.php?page=' . Plugin::get_slug() ),
			'meta' => [
				'class' => Plugin::get_slug() . '-admin-bar',
			],
		] );
	}

}