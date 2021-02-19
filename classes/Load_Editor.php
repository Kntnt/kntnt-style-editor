<?php


namespace Kntnt\Style_Editor;


class Load_Editor {

	public function run() {
		$settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
		wp_localize_script( 'jquery', 'cm_settings', $settings );
		wp_enqueue_script( 'kntnt-style-editor.js', Plugin::plugin_url( 'js/kntnt-style-editor.js' ), [ 'jquery' ], Plugin::version(), true );
		Plugin::debug( 'Enqueued CodeMirror with following settings: %s', $settings );
	}

}