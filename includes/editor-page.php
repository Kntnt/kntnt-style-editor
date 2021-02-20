<form id="<?php echo $ns ?>" method="post" action="<?php echo esc_html( admin_url( 'themes.php?page=kntnt-style-editor' ) ); ?>">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <textarea id="<?php echo $id ?>" name="<?php echo "{$ns}[$id]" ?>"><?php echo $value; ?></textarea>
    <p class="description"><?php echo $description; ?></p>
	<?php submit_button( $submit ); ?>
	<?php wp_nonce_field( 'kntnt-style-editor' ); ?>
</form>
