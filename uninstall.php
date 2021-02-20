<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || die;

delete_option( 'kntnt-style-editor' );

$upload_dir = wp_upload_dir()['basedir'];
@unlink("$upload_dir/kntnt-style-editor/kntnt-style-editor.css");
@rmdir("$upload_dir/kntnt-style-editor");

