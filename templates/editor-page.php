<?php
/**
 * Template for the Kntnt Style Editor page.
 *
 * Renders the main CSS editor interface with CodeMirror integration.
 * Includes form handling, nonce security, and user instructions.
 *
 * @package Kntnt\Style_Editor
 * @since   2.0.0
 *
 * @var string $css_content The current CSS content from database.
 * @var string $page_slug   The plugin slug for form processing.
 */

// Security check - ensure this file is called from WordPress
defined( 'WPINC' ) || die;
?>
<div class="wrap kntnt-style-editor-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php do_action( 'admin_notices' ); ?>

    <!-- CSS Editor Form -->
    <form method="post" action="">
		<?php wp_nonce_field( $page_slug . '_nonce' ); ?>

        <!-- CodeMirror Editor Container -->
        <div class="editor-container">
            <textarea id="kntnt-css-editor" name="css_content"><?php echo esc_textarea( $css_content ); ?></textarea>
        </div>

        <!-- User Instructions -->
        <p class="description">
			<?php esc_html_e( 'The CSS saved here will be loaded on every page on the frontend.', 'kntnt-style-editor' ); ?>
        </p>

        <!-- Save Button -->
		<?php submit_button( __( 'Save CSS', 'kntnt-style-editor' ) ); ?>
    </form>
</div>