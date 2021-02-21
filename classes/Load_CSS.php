<?php


namespace Kntnt\Style_Editor;


class Load_CSS {

	public function run() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_style' ], 9999 );
	}

	public function enqueue_style() {
		$url = Plugin::upload_url( 'kntnt-style-editor/kntnt-style-editor.css' );
		wp_enqueue_style( 'kntnt-style-editor.css', $url, [], Plugin::version() );
		Plugin::debug( 'Enqueued %s', $url );
	}

}
