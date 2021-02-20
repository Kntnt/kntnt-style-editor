<?php


namespace Kntnt\Style_Editor;


class Editor_Page {

	private static $capability = 'edit_theme_options';

	public function run() {
		$page_title = __( 'Kntnt Style Editor', 'kntnt-style-editor' );
		$menu_title = __( 'Style Editor', 'kntnt-style-editor' );
		add_submenu_page( 'themes.php', $page_title, $menu_title, static::$capability, Plugin::ns(), [ $this, 'add_editor_page' ] );
		Plugin::is_using( 'Logger' ) && Plugin::debug( 'Added the submenu page.' );
	}

	public function add_editor_page() {

		// Abort if current user has not permission to access the page.
		if ( ! current_user_can( static::$capability ) ) {
			Plugin::is_using( 'Logger' ) && Plugin::error( "Unauthorized use. User lacks the capability '%s'", static::$capability );
			wp_die( __( 'Unauthorized use.', 'kntnt' ) );
		}

		// Update options if the option page is saved.
		if ( $_POST ) {

			$opt = isset( $_POST[ Plugin::ns() ] ) ? ( $_POST[ Plugin::ns() ] ) : [];

			// The need for stripslashes() despite that Magic Quotes were
			// deprecated already in PHP 5.4 is due to WordPress backward
			// compatibility. WordPress roll their won version of "magic
			// quotes" because too much core and plugin code have come to
			// rely on the quotes being there. Jeez…
			$opt = stripslashes_deep( $opt );

			// Keep other options that are not settings.
			$opt = array_merge( Plugin::option( null, [] ), $opt );

			// Update options.
			$this->update_options( $opt );

		}

		// Render the option page.
		$this->render_page();


	}

	/** @noinspection PhpUnusedLocalVariableInspection */
	private function render_page() {

		// Variables that will be visible for the settings-page template.
		$ns = Plugin::ns();
		$id = 'css';
		$value = Plugin::option( 'css' );
		$description = __( 'The CSS above will be loaded on every page at the frontend.', 'kntnt-style-editor' );
		$submit = __( 'Save CSS', 'kntnt-style-editor' );

		// Render editor page.
		/** @noinspection PhpIncludeInspection */
		include Plugin::plugin_dir( 'includes/editor-page.php' );
		Plugin::is_using( 'Logger' ) && Plugin::debug( 'Rendered the submenu page.' );

	}

	private function update_options( array $opt ) {

		// Abort if the form's nonce is not correct or expired.
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], Plugin::ns() ) ) {
			Plugin::is_using( 'Logger' ) && Plugin::error( "Unauthorized use. Failed verify nonce.", static::$capability );
			wp_die( __( 'Nonce failed.', 'kntnt' ) );
		}

		// Save inputted values.
		update_option( Plugin::ns(), $opt );

		// Success notification
		$this->notify_success();

		// Logging
		Plugin::is_using( 'Logger' ) && Plugin::debug( 'Options saved: %s', $opt );

	}

	private function notify_success() {
		$message = __( 'Successfully saved settings.', 'kntnt' );
		$this->notify_admin( $message, 'success' );
	}

	private function notify_admin( $message, $type ) {
		echo "<div class=\"notice notice-$type is-dismissible\"><p>$message</p></div>";
	}

}